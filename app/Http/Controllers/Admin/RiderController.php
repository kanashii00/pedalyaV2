<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

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

    public function verify(Request $request, int $id): RedirectResponse
    {
        $rider = User::where('role', 'rider')->findOrFail($id);

        $validated = $request->validate([
            'approved' => ['required', 'boolean'],
            'reason'   => ['nullable', 'string', 'max:500'],
        ]);

        $rider->update([
            'verified' => $validated['approved'],
            'idVerification' => [
                'approved' => $validated['approved'],
                'reason' => $validated['reason'] ?? null,
                'verified_at' => now()->toISOString(),
            ],
        ]);

        AuditLog::record('rider_verification_' . ($validated['approved'] ? 'approved' : 'rejected'), auth()->id(), [
            'riderId' => $rider->id,
        ]);

        return back()->with('success', 'Rider verification updated.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $rider = User::where('role', 'rider')->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        if ($validated['status'] !== 'active' &&
            $rider->rentals()->whereIn('status', ['active', 'pending'])->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Cannot change status of rider with active or pending rentals.',
            ]);
        }

        $rider->update(['status' => $validated['status']]);

        AuditLog::record('rider_status_updated', auth()->id(), [
            'riderId' => $rider->id,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Rider status updated.');
    }
}
