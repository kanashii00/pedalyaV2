<?php

namespace App\Observers;

use App\Models\User;
use App\Services\CacheRegistry;

/**
 * Invalidates a user's own summary when their profile-affecting state changes
 * (verification, status, totals). High-frequency fields such as last_login_at
 * are ignored so cached summaries are not discarded on every login.
 */
class UserObserver
{
    private const SUMMARY_FIELDS = [
        'name',
        'role',
        'status',
        'verified',
        'idUploaded',
        'idVerification',
        'totalRentals',
        'totalSpent',
    ];

    public function updated(User $user): void
    {
        if (array_intersect(array_keys($user->getDirty()), self::SUMMARY_FIELDS) !== []) {
            CacheRegistry::bumpUserVersion((int) $user->id);
        }
    }

    public function deleted(User $user): void
    {
        CacheRegistry::bumpUserVersion((int) $user->id);
    }

    public function restored(User $user): void
    {
        CacheRegistry::bumpUserVersion((int) $user->id);
    }
}