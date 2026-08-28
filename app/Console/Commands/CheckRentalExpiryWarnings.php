<?php

namespace App\Console\Commands;

use App\Models\Bicycle;
use App\Models\Rental;
use App\Models\SystemSetting;
use App\Services\IoTService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckRentalExpiryWarnings extends Command
{
    protected $signature = 'rentals:check-expiry-warnings';

    protected $description = 'Fire near-expiry warnings (rider notification + buzzer/LCD command) before active rentals expire';

    public function handle(NotificationService $notificationService, IoTService $iotService): int
    {
        $warningMinutes = (int) SystemSetting::getValue('overdueBuzzerMinutes', 5);

        if ($warningMinutes <= 0) {
            $this->info('Expiry warnings are disabled (overdueBuzzerMinutes <= 0).');
            return Command::SUCCESS;
        }

        $windowEnd = now()->addMinutes($warningMinutes);

        $warnedRentals = Rental::with(['bicycle'])
            ->where('status', Rental::STATUS_ACTIVE)
            ->whereNull('warningSentAt')
            ->whereNotNull('endTime')
            ->where('endTime', '>', now())
            ->where('endTime', '<=', $windowEnd)
            ->get();

        if ($warnedRentals->isEmpty()) {
            $this->info('No rentals need expiry warnings.');
            return Command::SUCCESS;
        }

        foreach ($warnedRentals as $rental) {
            $remaining = max(0, (int) $rental->endTime->diffInSeconds(now()));

            $notificationService->create(
                $rental->riderId,
                'Rental Expiring Soon',
                "Your rental for bicycle {$rental->bicycleName} (#{$rental->bicycleSerial}) ends in approximately "
                    . gmdate('i:s', $remaining)
                    . ". Please return the bicycle on time to avoid overdue charges.",
                'rental_warning',
                ['rentalId' => $rental->id, 'bicycleId' => $rental->bicycleId]
            );

            if ($rental->bicycle) {
                // Queue buzzer + LCD warning commands for the ESP32. These
                // are picked up by the IoT device on its next status poll when
                // hardware is connected, and are otherwise inert (no-op).
                $iotService->sendCommand($rental->bicycle->id, 'buzzer', [
                    'reason' => 'rental_expiring',
                    'rentalId' => $rental->id,
                    'seconds' => $remaining,
                ]);
                $iotService->sendCommand($rental->bicycle->id, 'lcd', [
                    'message' => 'RENTAL ENDS IN ' . gmdate('i:s', $remaining),
                    'rentalId' => $rental->id,
                ]);
            }

            $rental->update(['warningSentAt' => now()]);

            $this->info("Rental #{$rental->id} warned (ends " . $rental->endTime->diffForHumans() . ').');
        }

        $this->info("Processed {$warnedRentals->count()} near-expiry warning(s).");
        return Command::SUCCESS;
    }
}
