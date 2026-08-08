<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        return view('rider.notifications', compact('notifications', 'unreadCount'));
    }

    public function markRead(Request $request, int $id): RedirectResponse
    {
        $userId = $request->user()->id;

        $this->notificationService->markAsRead($id, $userId);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        $this->notificationService->markAllAsRead($userId);

        return back()->with('success', 'All notifications marked as read.');
    }
}
