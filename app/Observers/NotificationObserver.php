<?php

namespace App\Observers;

use App\Models\Notification;
use App\Services\CacheRegistry;

/**
 * Keeps per-user unread-count / summary caches fresh whenever a notification
 * is created, read, or removed for any user.
 */
class NotificationObserver
{
    public function created(Notification $notification): void
    {
        CacheRegistry::bumpUserVersion((int) $notification->userId);
    }

    public function updated(Notification $notification): void
    {
        CacheRegistry::bumpUserVersion((int) $notification->userId);
    }

    public function deleted(Notification $notification): void
    {
        CacheRegistry::bumpUserVersion((int) $notification->userId);
    }

    public function restored(Notification $notification): void
    {
        CacheRegistry::bumpUserVersion((int) $notification->userId);
    }
}