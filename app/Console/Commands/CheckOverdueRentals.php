<?php

namespace App\Console\Commands;

use App\Models\Bicycle;
use App\Models\Notification;
use App\Models\Rental;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckOverdueRentals extends Command
{
    protected $signature = 'rentals:check-overdue';
    protected $description = 'Check for overdue rentals, flag them, and notify riders and admins';

    public function handle(NotificationService $notificationService): int
    {
       $overdueRentals = Rental::with(['bicycle'])
    ->where('status', Rental::STATUS_ACTIVE)
    ->whereNotNull('expectedEndTime')
    ->where('expectedEndTime', '<=', now())
    ->get();

        if ($overdueRentals->isEmpty()) {
            $this->info('No overdue rentals found.');
            return Command::SUCCESS;
        }

        foreach ($overdueRentals as $rental) {
            $rental->update([
                'status' => Rental::STATUS_OVERDUE,
                'isOverdue' => true,
                'overdueAt' => now(),
            ]);

             // Do not lock immediately when the rental becomes overdue.
            // The grace-period lock command handles the smart lock after
            // the allowed grace period has elapsed.
            
            $notificationService->create(
                $rental->riderId,
                'Rental Overdue',
                "Your rental for bicycle {$rental->bicycleName} (#{$rental->bicycleSerial}) is now overdue. Please return the bicycle immediately.",
                'rental_overdue',
                ['rentalId' => $rental->rentalId]
            );

            $this->info("Rental #{$rental->id} marked as overdue.");
        }

        $adminIds = User::where('role', User::ROLE_ADMIN)->pluck('id')->all();
        if (!empty($adminIds)) {
            $notificationService->createForUsers(
                $adminIds,
                'Overdue Rentals',
                $overdueRentals->count() . ' rental(s) are now overdue and require attention.',
                'rental_overdue'
            );
        }

        $this->info("Processed {$overdueRentals->count()} overdue rental(s).");
        return Command::SUCCESS;
    }
}
