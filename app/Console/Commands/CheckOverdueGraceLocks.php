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
            $bicycle = $rental->bicycle;

            if (!$bicycle) {
                $this->warn(
                    "Rental #{$rental->id}: bicycle not found."
                );

                continue;
            }

            if ($bicycle->lockStatus === 'locked') {
                $this->info(
                    "Bicycle #{$bicycle->id} is already locked."
                );

                continue;
            }

            $existingLockCommand = DeviceCommand::where(
                'bicycleId',
                $bicycle->id
            )
                ->where('command', 'lock')
                ->whereIn('status', [
                    'pending',
                    'sent',
                ])
                ->exists();

            if ($existingLockCommand) {
                $this->info(
                    "Bicycle #{$bicycle->id} already has a pending lock command."
                );

                continue;
            }

            $latestHeartbeat = DeviceStatus::where(
                'bicycleId',
                $bicycle->id
            )
                ->where('type', 'heartbeat')
                ->latest('eventTimestamp')
                ->first();

            if (!$latestHeartbeat) {
                $this->warn(
                    "Bicycle #{$bicycle->id}: no heartbeat available. Lock skipped."
                );

                continue;
            }

            if (
                !$latestHeartbeat->eventTimestamp ||
                $latestHeartbeat->eventTimestamp
                    ->lt(now()->subMinutes(2))
            ) {
                $this->warn(
                    "Bicycle #{$bicycle->id}: heartbeat is stale. Lock skipped."
                );

                continue;
            }

            $gps = $latestHeartbeat->gps;

            $speed = is_array($gps)
                ? ($gps['speed'] ?? null)
                : null;

            if ($speed === null) {
                $this->warn(
                    "Bicycle #{$bicycle->id}: speed unavailable. Lock skipped."
                );

                continue;
            }

            $speed = (float) $speed;

            if ($speed > 0.5) {
                $this->info(
                    "Bicycle #{$bicycle->id} is still moving (speed: {$speed}). Lock postponed."
                );

                continue;
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

            $this->info(
                "Safe lock command queued for bicycle #{$bicycle->id}."
            );
        }

        return Command::SUCCESS;
    }
}