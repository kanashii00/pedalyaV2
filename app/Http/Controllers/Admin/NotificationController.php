<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(): Response
    {
        $notifications = Notification::with('user')->latest()->paginate(20);
        $users = User::where('role', User::ROLE_RIDER)->orderBy('name')->get();
        $unreadCount = $this->notificationService->getUnreadCount(auth()->id());

        return response()->view('admin.notifications', compact('notifications', 'users', 'unreadCount'));
    }

    /**
     * Returns just the notification table (HTML) so the Notifications page can
     * refresh it after a send without a full page reload.
     */
    public function table(Request $request): Response
    {
        $notifications = Notification::with('user')->latest()->paginate(20);

        return response()->view('admin.notifications-table', compact('notifications'));
    }

    /**
     * Store a notification sent by an administrator.
     *
     * AJAX/fetch requests get a clean JSON result; plain form posts are
     * redirected back with a session flash so the browser never lands on a raw
     * JSON payload. Validation failures render inside the existing UI via the
     * standard invalid-feedback markup (or a JSON 422 for AJAX callers).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $userIds = $this->resolveRecipients($validated['recipientType'], $validated);

        if (empty($userIds)) {
            return $this->respond(
                $request,
                'No recipients match this selection.',
                [],
                422,
                back()->withErrors(['recipientType' => 'No recipients match this selection.'])->withInput()
            );
        }

        if ($this->isDuplicate($userIds, $validated['title'], $validated['message'], $validated['type'])) {
            return $this->respond(
                $request,
                'This notification was already sent. Please check the Notifications list before sending again.',
                [],
                409,
                back()->with('error', 'This notification was already sent. Please check the Notifications list before sending again.')->withInput()
            );
        }

        $count = $this->notificationService
            ->createForUsers($userIds, $validated['title'], $validated['message'], $validated['type'])
            ->count();

        return $this->respond(
            $request,
            'Notification sent successfully.',
            ['count' => $count],
            200,
            back()->with('success', "Notification sent successfully to {$count} recipient(s).")
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $this->notificationService->getUnreadCount($request->user()->id),
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $marked = $this->notificationService->markAsRead($id, $request->user()->id);

        return $marked
            ? $this->respond($request, 'Notification marked as read.', [], 200, back()->with('success', 'Notification marked as read.'))
            : $this->respond($request, 'Notification not found.', [], 404, back()->with('error', 'Notification not found.'));
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $updated = $this->notificationService->markAllAsRead($request->user()->id);
        $message = $updated > 0
            ? $updated.' notification(s) marked as read.'
            : 'No unread notifications.';

        return $this->respond(
            $request,
            $message,
            ['unread_count' => 0],
            200,
            back()->with('success', $message)
        );
    }

    /**
     * Keep a single consistent success/failure response for both AJAX and
     * non-AJAX callers so the browser never displays a raw JSON body.
     */
    private function respond(
        Request $request,
        string $message,
        array $extra = [],
        int $status = 200,
        ?RedirectResponse $fallback = null
    ): JsonResponse|RedirectResponse {
        if ($request->wantsJson()) {
            return response()->json(array_merge(['message' => $message], $extra), $status);
        }

        return $fallback ?? back()->with('error', $message);
    }

    private function rules(): array
    {
        $riderRule = Rule::exists('users', 'id')->where('role', User::ROLE_RIDER);

        return [
            'title'          => ['required', 'string', 'max:255'],
            'message'        => ['required', 'string', 'max:1000'],
            'type'           => ['required', 'string', 'in:info,warning,error,success,general'],
            'recipientType'  => ['required', 'string', 'in:broadcast,single,specific,multi'],
            'user_id'        => ['required_if:recipientType,single,specific', 'nullable', 'integer', $riderRule],
            'user_ids'       => ['required_if:recipientType,multi', 'nullable', 'array', 'min:1', 'max:100'],
            'user_ids.*'     => ['integer', $riderRule],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required'         => 'Please enter a title.',
            'title.max'              => 'The title must not exceed 255 characters.',
            'message.required'       => 'Please write a message.',
            'message.max'            => 'The message must not exceed 1000 characters.',
            'type.required'          => 'Please select a notification type.',
            'type.in'                => 'Please select a valid notification type.',
            'recipientType.required' => 'Please choose who should receive this notification.',
            'recipientType.in'       => 'Please choose a valid recipient option.',
            'user_id.required_if'    => 'Please select a user to send to.',
            'user_id.exists'         => 'The selected recipient is invalid.',
            'user_ids.required_if'   => 'Please select at least one recipient.',
            'user_ids.min'           => 'Please select at least one recipient.',
            'user_ids.max'           => 'You can select at most 100 recipients at once.',
            'user_ids.*.exists'      => 'One or more selected recipients are invalid.',
        ];
    }

    /**
     * Resolve the concrete user ids for the chosen recipient type. Broadcast
     * targets every registered rider, single/specific targets exactly one user,
     * and multi targets only the selected users.
     */
    private function resolveRecipients(string $recipientType, array $validated): array
    {
        if ($recipientType === 'broadcast') {
            return User::where('role', User::ROLE_RIDER)->pluck('id')->all();
        }

        if (in_array($recipientType, ['single', 'specific'], true)) {
            return [(int) $validated['user_id']];
        }

        return array_map('intval', $validated['user_ids'] ?? []);
    }

    /**
     * Prevent duplicates from a double-clicked Send button: if the exact same
     * notification was delivered to any of these recipients within the last
     * minute, treat the request as a duplicate.
     */
    private function isDuplicate(array $userIds, string $title, string $message, string $type): bool
    {
        if (empty($userIds)) {
            return false;
        }

        return Notification::whereIn('userId', $userIds)
            ->where('title', $title)
            ->where('message', $message)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subMinute())
            ->exists();
    }
}