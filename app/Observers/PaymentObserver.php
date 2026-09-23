<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\CacheRegistry;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        CacheRegistry::bumpUserVersion((int) $payment->userId);
    }

    public function updated(Payment $payment): void
    {
        CacheRegistry::bumpUserVersion((int) $payment->userId);
    }

    public function deleted(Payment $payment): void
    {
        CacheRegistry::bumpUserVersion((int) $payment->userId);
    }
}