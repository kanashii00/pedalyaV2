<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RiderController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::where('role', 'rider');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('verified')) {
            $query->where('verified', $request->boolean('verified'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phoneNumber', 'like', "%{$search}%");
            });
        }

        $riders = $query->latest()->paginate(20);

        return response()->view('admin.riders', compact('riders'));
    }

    public function create(): View
    {
        return view('admin.riders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'studentId'   => ['nullable', 'string', 'max:50'],
            'email'       => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                \Illuminate\Validation\Rule::unique(User::class, 'email'),
            ],
            'phoneNumber' => ['nullable', 'string', 'max:20'],
            'address'     => ['nullable', 'string', 'max:500'],
            'password'    => ['required', 'confirmed', Password::defaults()],
        ]);

        User::create([
            'name'        => $validated['name'],
            'studentId'   => $validated['studentId'] ?? null,
            'email'       => $validated['email'],
            'phoneNumber' => $validated['phoneNumber'] ?? null,
            'address'     => $validated['address'] ?? null,
            'password'    => Hash::make($validated['password']),
            'role'        => User::ROLE_RIDER,
            'status'      => User::STATUS_ACTIVE,
            'verified'    => false,
            'idUploaded'  => false,
            'totalRentals'=> 0,
            'totalSpent'  => 0,
        ]);

        AuditLog::record('rider_created', auth()->id(), [
            'email' => $validated['email'],
        ]);

        return redirect()->route('admin.riders.index')
            ->with('success', 'Customer "' . $validated['name'] . '" registered successfully.');
    }

    public function verify(Request $request, int $id): RedirectResponse
    {
        $rider = User::where('role', 'rider')->findOrFail($id);

        $validated = $request->validate([
            'approved' => ['required', 'boolean'],
            'reason'   => ['nullable', 'string', 'max:500'],
        ]);

       $idVerification = $rider->idVerification ?? [];

$idVerification = array_merge($idVerification, [
    'status' => $validated['approved']
        ? 'approved'
        : 'rejected',
    'approved' => $validated['approved'],
    'reason' => $validated['reason'] ?? null,
    'verified_at' => now()->toISOString(),
]);

$rider->update([
    'verified' => $validated['approved'],
    'idVerification' => $idVerification,
]);

$notificationService = app(\App\Services\NotificationService::class);

if ($validated['approved']) {
    $notificationService->create(
        $rider->id,
        'Identity Verification Approved',
        'Your identity verification has been approved. You can now start renting bicycles.',
        'verification_approved'
    );
} else {
    $message = 'Your identity verification was rejected. Please upload another valid ID.';

    if (!empty($validated['reason'])) {
        $message .= ' Reason: ' . $validated['reason'];
    }

    $notificationService->create(
        $rider->id,
        'Identity Verification Rejected',
        $message,
        'verification_rejected'
    );
}

        AuditLog::record('rider_verification_' . ($validated['approved'] ? 'approved' : 'rejected'), auth()->id(), [
            'riderId' => $rider->id,
        ]);

        return back()->with('success', 'Rider verification updated.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $rider = User::where('role', 'rider')->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,inactive,suspended,blacklisted'],
        ]);

        if ($validated['status'] !== 'active' &&
            $rider->rentals()->whereIn('status', ['active', 'pending'])->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Cannot change status of rider with active or pending rentals.',
            ]);
        }

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'active') {
            $updates['blacklistReason'] = null;
        }

        $rider->update($updates);

        AuditLog::record('rider_status_updated', auth()->id(), [
            'riderId' => $rider->id,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Rider status updated.');
    }

    public function verified(Request $request): Response
    {
        $query = User::where('role', 'rider')
            ->where('verified', true)
            ->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phoneNumber', 'like', "%{$search}%")
                  ->orWhere('studentId', 'like', "%{$search}%");
            });
        }

        $riders = $query->latest()->paginate(20);

        return response()->view('admin.verified-customers', compact('riders'));
    }

    public function blacklisted(Request $request): Response
    {
        $query = User::where('role', 'rider')
            ->whereIn('status', ['inactive', 'suspended', 'blacklisted']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phoneNumber', 'like', "%{$search}%")
                  ->orWhere('studentId', 'like', "%{$search}%");
            });
        }

        $riders = $query->latest()->paginate(20);

        return response()->view('admin.blacklisted-customers', compact('riders'));
    }

    public function updateBlacklist(Request $request, int $id): RedirectResponse
    {
        $rider = User::where('role', 'rider')->findOrFail($id);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'studentId'       => ['nullable', 'string', 'max:50'],
            'email'           => ['required', 'string', 'lowercase', 'email', 'max:255',
                \Illuminate\Validation\Rule::unique(User::class, 'email')->ignore($rider->id),
            ],
            'phoneNumber'     => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:500'],
            'status'          => ['required', 'string', 'in:active,inactive,suspended,blacklisted'],
            'blacklistReason' => ['nullable', 'string', 'max:1000'],
        ]);

        $rider->update([
            'name'            => $validated['name'],
            'studentId'       => $validated['studentId'] ?? null,
            'email'           => $validated['email'],
            'phoneNumber'     => $validated['phoneNumber'] ?? null,
            'address'         => $validated['address'] ?? null,
            'status'          => $validated['status'],
            'blacklistReason' => $validated['status'] === 'active' ? null : ($validated['blacklistReason'] ?? null),
        ]);

        AuditLog::record('rider_blacklist_updated', auth()->id(), [
            'riderId' => $rider->id,
            'status'  => $validated['status'],
        ]);

        return back()->with('success', 'Customer profile updated.');
    }
}
