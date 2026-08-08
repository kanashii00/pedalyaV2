<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'type'     => 'nullable|string|max:50',
            'read'     => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $user = $request->user();
        $query = Notification::where('userId', $user->id);

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (isset($validated['read'])) {
            $query->where('read', $validated['read']);
        }

        $notifications = $query->orderByDesc('created_at')
            ->paginate($validated['per_page'] ?? 20);

        return NotificationResource::collection($notifications);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('userId', $request->user()->id)
            ->where('read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function markRead(int $id): JsonResponse
    {
        $notification = Notification::where('userId', request()->user()->id)
            ->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['read' => true, 'readAt' => now()]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        Notification::where('userId', $request->user()->id)
            ->where('read', false)
            ->update(['read' => true, 'readAt' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
