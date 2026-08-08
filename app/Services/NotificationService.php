<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Collection;

class NotificationService
{
    public function create(int $userId, string $title, string $message, string $type, array $extra = []): Notification
    {
        return Notification::create(array_merge([
            'userId' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'read' => false,
        ], $extra));
    }

    public function createForUsers(array $userIds, string $title, string $message, string $type, array $extra = []): Collection
    {
        $notifications = collect();

        foreach ($userIds as $userId) {
            $notifications->push($this->create($userId, $title, $message, $type, $extra));
        }

        return $notifications;
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('userId', $userId)
            ->first();

        if (!$notification) {
            return false;
        }

        $notification->update(['read' => true, 'readAt' => now()]);

        return true;
    }

    public function markAllAsRead(int $userId): int
    {
        return Notification::where('userId', $userId)
            ->where('read', false)
            ->update(['read' => true, 'readAt' => now()]);
    }

    public function getUnreadCount(int $userId): int
    {
        return Notification::where('userId', $userId)
            ->where('read', false)
            ->count();
    }

    public function getUnreadForUser(int $userId): Collection
    {
        return Notification::where('userId', $userId)
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
