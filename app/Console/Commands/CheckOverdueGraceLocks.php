<?php

namespace App\Console\Commands;

use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\DeviceStatus;
use App\Models\Rental;
use App\Models\User;
use App\Services\IoTService;
use Illuminate\Console\Command;

class CheckOverdueGraceLocks extends Command
{
    protected $signature = 'rentals:check-grace-locks';

    protected $description =
        'Queue safe lock commands for overdue rentals after the 5-minute grace period';

    public function handle(IoTService $iotService): int
    {
        $admin = User::where('role', User::ROLE_ADMIN)
            ->first();

        if (!$admin) {
            $this->error(
                'No administrator account found. Lock commands cannot be issued.'
            );

            return Command::FAILURE;
        }

        $rentals = Rental::with('bicycle')
            ->where('status', Rental::STATUS_OVERDUE)
            ->whereNotNull('overdueAt')
            ->where('overdueAt', '<=', now()->subMinutes(5))
            ->get();

        if ($rentals->isEmpty()) {
            $this->info(
                'No overdue rentals require a lock check.'
            );

            return Command::SUCCESS;
        }

        foreach ($rentals as $rental) {
            $this->processOverdueRental($rental, $iotService, $admin);
        }

        return Command::SUCCESS;
    }

    private function processOverdueRental(Rental $rental, IoTService $iotService, User $admin): void
    {
        $bicycle = $rental->bicycle;

        if (!$bicycle) {
            $this->warn("Rental #{$rental->id}: bicycle not found.");

            return;
        }

        $lockIssue = $this->lockIssue($bicycle);

        if ($lockIssue !== null) {
            [$level, $message] = $lockIssue;
            $this->{$level}($message);

            return;
        }

        $iotService->sendCommand(
            $bicycle->id,
            'lock',
            [
                'reason' => 'overdue_grace_expired',
                'rental_id' => $rental->id,
                'rental_code' => $rental->rentalId,
            ],
            $admin
        );

        $this->info("Safe lock command queued for bicycle #{$bicycle->id}.");
    }

    private function lockIssue(Bicycle $bicycle): ?array
    {
        $latestHeartbeat = $this->latestHeartbeat($bicycle->id);

        $guards = [
            [$bicycle->lockStatus === 'locked', 'info', "Bicycle #{$bicycle->id} is already locked."],
            [$this->hasPendingLockCommand($bicycle->id), 'info', "Bicycle #{$bicycle->id} already has a pending lock command."],
            [$latestHeartbeat === null, 'warn', "Bicycle #{$bicycle->id}: no heartbeat available. Lock skipped."],
            [$latestHeartbeat !== null && $this->isHeartbeatStale($latestHeartbeat), 'warn', "Bicycle #{$bicycle->id}: heartbeat is stale. Lock skipped."],
        ];

        foreach ($guards as $guard) {
            if ($guard[0]) {
                return [$guard[1], $guard[2]];
            }
        }

        $speed = $this->heartbeatSpeed($latestHeartbeat);

        if ($speed === null) {
            return ['warn', "Bicycle #{$bicycle->id}: speed unavailable. Lock skipped."];
        }

        return $speed > 0.5
            ? ['info', "Bicycle #{$bicycle->id} is still moving (speed: {$speed}). Lock postponed."]
            : null;
    }

    private function heartbeatSpeed(DeviceStatus $heartbeat): ?float
    {
        $gps = $heartbeat->gps;
        $speed = is_array($gps) ? ($gps['speed'] ?? null) : null;

        return $speed === null ? null : (float) $speed;
    }

    private function hasPendingLockCommand(int $bicycleId): bool
    {
        return DeviceCommand::where('bicycleId', $bicycleId)
            ->where('command', 'lock')
            ->whereIn('status', ['pending', 'sent'])
            ->exists();
    }

    private function latestHeartbeat(int $bicycleId): ?DeviceStatus
    {
        return DeviceStatus::where('bicycleId', $bicycleId)
            ->where('type', 'heartbeat')
            ->latest('eventTimestamp')
            ->first();
    }

    private function isHeartbeatStale(DeviceStatus $heartbeat): bool
    {
        return !$heartbeat->eventTimestamp
            || $heartbeat->eventTimestamp->lt(now()->subMinutes(2));
    }
}