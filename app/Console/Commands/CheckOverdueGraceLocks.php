<?php

namespace App\Console\Commands;

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

        if ($bicycle->lockStatus === 'locked') {
            $this->info("Bicycle #{$bicycle->id} is already locked.");

            return;
        }

        if ($this->hasPendingLockCommand($bicycle->id)) {
            $this->info("Bicycle #{$bicycle->id} already has a pending lock command.");

            return;
        }

        $latestHeartbeat = $this->latestHeartbeat($bicycle->id);

        if (!$latestHeartbeat) {
            $this->warn("Bicycle #{$bicycle->id}: no heartbeat available. Lock skipped.");

            return;
        }

        if ($this->isHeartbeatStale($latestHeartbeat)) {
            $this->warn("Bicycle #{$bicycle->id}: heartbeat is stale. Lock skipped.");

            return;
        }

        $gps = $latestHeartbeat->gps;
        $speed = is_array($gps) ? ($gps['speed'] ?? null) : null;

        if ($speed === null) {
            $this->warn("Bicycle #{$bicycle->id}: speed unavailable. Lock skipped.");

            return;
        }

        $speed = (float) $speed;

        if ($speed > 0.5) {
            $this->info("Bicycle #{$bicycle->id} is still moving (speed: {$speed}). Lock postponed.");

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