<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): Response
    {
        $notifications = Notification::with('user')->latest()->paginate(20);
        $users = User::where('role', User::ROLE_RIDER)->orderBy('name')->get();

        return response()->view('admin.notifications', compact('notifications', 'users'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'message'         => ['required', 'string', 'max:1000'],
            'type'            => ['sometimes', 'string', 'max:50'],
            'recipientType'   => ['required', 'string', 'in:broadcast,single,specific,multi'],
            'user_id'         => ['required_if:recipientType,single,specific', 'nullable', 'exists:users,id'],
            'user_ids'        => ['required_if:recipientType,multi', 'nullable', 'array'],
            'user_ids.*'      => ['exists:users,id'],
        ]);

        $title = $validated['title'];
        $message = $validated['message'];
        $type = $validated['type'] ?? 'general';

        if ($validated['recipientType'] === 'broadcast') {
            $userIds = User::where('role', User::ROLE_RIDER)->pluck('id')->all();

            if (!empty($userIds)) {
                $this->notificationService->createForUsers($userIds, $title, $message, $type);
            }

            return response()->json(['message' => 'Notification broadcast to all riders.']);
        }

        $userIds = in_array($validated['recipientType'], ['single', 'specific'], true)
            ? [$validated['user_id']]
            : ($validated['user_ids'] ?? []);

        $this->notificationService->createForUsers($userIds, $title, $message, $type);

        return response()->json(['message' => 'Notification(s) sent successfully.']);
    }
}
