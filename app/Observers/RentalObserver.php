<?php

namespace App\Observers;

use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Support\Facades\DB;

class RentalObserver
{
    /**
     * Automated status update rule.
     *
     * The moment a rental's status is set to "Completed" and its payment
     * status becomes "Paid", the corresponding bicycle is released:
     * status -> Available, lockStatus -> Locked, rider/rental references
     * cleared, and a physical lock command queued for the smart lock.
     *
     * Reacting to the model transition (instead of individual endpoints)
     * guarantees the rule holds for every code path — admin end-ride,
     * rider returns, payment webhooks, or any future writer.
     */
    public function updated(Rental $rental): void
    {
        $settled = in_array($rental->status, [Rental::STATUS_COMPLETED, Rental::STATUS_RETURNED], true)
            && strtolower((string) $rental->paymentStatus) === 'paid';

        if (!$settled) {
            return;
        }

        // Only act when the settled state was just reached, so repeated
        // saves of an already-settled rental do not re-trigger work.
        $wasSettled = in_array($rental->getOriginal('status'), [Rental::STATUS_COMPLETED, Rental::STATUS_RETURNED], true)
            && strtolower((string) $rental->getOriginal('paymentStatus')) === 'paid';

        if ($wasSettled) {
            return;
        }

        // Defer until any open transaction commits so we never act on
        // data that might still roll back (e.g., inside returnRental()).
        DB::afterCommit(function () use ($rental) {
            app(RentalService::class)->settleBicycleForRental($rental);
        });
    }
}
