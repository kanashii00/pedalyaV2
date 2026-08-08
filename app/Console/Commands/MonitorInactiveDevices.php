<?php

namespace App\Console\Commands;

use App\Models\Bicycle;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class MonitorInactiveDevices extends Command
{
    protected $signature = 'devices:check-inactive';
    protected $description = 'Check for devices that haven\'t sent heartbeat recently and notify admins';

    public function handle(NotificationService $notificationService): int
    {
        $threshold = now()->subMinutes(5);

        $inactiveDevices = Bicycle::where(function ($query) use ($threshold) {
            $query->whereNull('lastHeartbeat')
                ->orWhere('lastHeartbeat', '<', $threshold);
        })->get();

        if ($inactiveDevices->isEmpty()) {
            $this->info('All devices are active.');
            return Command::SUCCESS;
        }

        $adminIds = User::where('role', User::ROLE_ADMIN)->pluck('id')->all();

        foreach ($inactiveDevices as $bicycle) {
            $lastSeen = $bicycle->lastHeartbeat
                ? $bicycle->lastHeartbeat->diffForHumans()
                : 'never';

            if (!empty($adminIds)) {
                $notificationService->createForUsers(
                    $adminIds,
                    'Inactive Device',
                    "Bicycle {$bicycle->name} (#{$bicycle->serialNumber}) has not sent a heartbeat in {$lastSeen}.",
                    'device_inactive',
                    ['bicycleId' => $bicycle->id]
                );
            }
        }

        $this->info("Found {$inactiveDevices->count()} inactive or never-connected device(s).");
        return Command::SUCCESS;
    }
}
