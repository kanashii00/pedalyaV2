<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use App\Services\IoTService;
use App\Services\NotificationService;
use App\Services\RentalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RentalController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private IoTService $iotService,
        private RentalService $rentalService
    ) {}

    public function index(Request $request): Response
    {
        $query = Rental::with(['bicycle', 'rider'])
            ->whereIn('status', [Rental::STATUS_ACTIVE, Rental::STATUS_PENDING, Rental::STATUS_OVERDUE]);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('bicycle_id')) {
            $query->where('bicycleId', $request->input('bicycle_id'));
        }

        if ($request->filled('rider_id')) {
            $query->where('riderId', $request->input('rider_id'));
        }

        $rentals = $query->latest()->paginate(20);

        $bicyclesList = Bicycle::orderBy('name')->get();
        $ridersList = User::where('role', User::ROLE_RIDER)->orderBy('name')->get();

        return response()->view('admin.rentals', compact('rentals', 'bicyclesList', 'ridersList'));
    }

    public function history(Request $request): Response
    {
        $query = Rental::with(['bicycle', 'rider'])
            ->whereIn('status', [
                Rental::STATUS_COMPLETED,
                Rental::STATUS_CANCELLED,
                Rental::STATUS_RETURNED,
                Rental::STATUS_EXPIRED,
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('bicycle_id')) {
            $query->where('bicycleId', $request->input('bicycle_id'));
        }

        if ($request->filled('rider_id')) {
            $query->where('riderId', $request->input('rider_id'));
        }

        $rentals = $query->latest()->paginate(20);

        $bicyclesList = Bicycle::orderBy('name')->get();
        $ridersList = User::where('role', User::ROLE_RIDER)->orderBy('name')->get();

        return response()->view('admin.rentals-history', compact('rentals', 'bicyclesList', 'ridersList'));
    }

    public function returns(Request $request): Response
    {
        $query = Rental::with(['bicycle', 'rider'])
            ->whereIn('status', [
                Rental::STATUS_COMPLETED,
                Rental::STATUS_RETURNED,
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('bicycle_id')) {
            $query->where('bicycleId', $request->input('bicycle_id'));
        }

        if ($request->filled('rider_id')) {
            $query->where('riderId', $request->input('rider_id'));
        }

        $rentals = $query->latest()->paginate(20);

        $bicyclesList = Bicycle::orderBy('name')->get();
        $ridersList = User::where('role', User::ROLE_RIDER)->orderBy('name')->get();

        $returnedIds = [Rental::STATUS_COMPLETED, Rental::STATUS_RETURNED];
        $summary = [
            'total' => Rental::whereIn('status', $returnedIds)->count(),
            'totalFee' => (float) Rental::whereIn('status', $returnedIds)->sum('totalFee'),
            'totalDuration' => (int) Rental::whereIn('status', $returnedIds)->sum('durationMinutes'),
            'today' => Rental::whereIn('status', $returnedIds)
                ->whereDate('updated_at', today())
                ->count(),
        ];

        return response()->view(
            'admin.rentals-returns',
            compact('rentals', 'bicyclesList', 'ridersList', 'summary')
        );
    }

    public function show(int $id): Response
    {
        $rental = Rental::with(['bicycle', 'rider'])->findOrFail($id);

        return response()->view('admin.rentals.show', compact('rental'));
    }

    public function approve(int $id): RedirectResponse
    {
        $rental = Rental::with(['bicycle', 'rider'])->findOrFail($id);

        if ($rental->status !== Rental::STATUS_PENDING) {
            return back()->withErrors(['rental' => 'Only pending rentals can be approved.']);
        }

        $bicycle = $rental->bicycle;

        $rental->update([
            'status' => Rental::STATUS_ACTIVE,
            'approvedBy' => auth()->id(),
            'approvedAt' => now(),
        ]);

        if ($bicycle) {
            // Rental approved: bicycle becomes Rented and the smart lock is
            // Unlocked because the rider is now authorized to use it.
            $bicycle->update([
                'status' => Bicycle::STATUS_RENTED,
                'currentRider' => $rental->riderId,
                'currentRentalId' => $rental->id,
                'lockStatus' => Bicycle::LOCK_UNLOCKED,
            ]);

            $this->iotService->sendCommand($bicycle->id, 'unlock', [], auth()->user());
        }

        AuditLog::record('rental_approved', auth()->id(), [
            'rentalId' => $rental->rentalId,
            'previous_status' => Rental::STATUS_PENDING,
            'new_status' => Rental::STATUS_ACTIVE,
        ]);

        $this->notificationService->create(
            $rental->riderId,
            'Rental Approved',
            "Your rental for bicycle {$rental->bicycleName} has been approved. Enjoy your ride!",
            'rental_status',
            ['rentalId' => $rental->rentalId]
        );

        return back()->with('success', 'Rental approved successfully.');
    }

    public function verifyGcashPayment(int $id): RedirectResponse
    {
        $rental = Rental::with(['bicycle', 'rider'])->findOrFail($id);

        if ($rental->status !== 'pending' || $rental->paymentMethod !== 'gcash') {
            return back()->withErrors(['rental' => 'This rental is not a pending GCash payment.']);
        }

        $bicycle = $rental->bicycle;

        $rental->update([
            'status' => 'active',
            'paymentStatus' => 'paid',
            'paidAt' => now(),
            'approvedBy' => auth()->id(),
            'approvedAt' => now(),
        ]);

        if ($bicycle) {
            // Payment verified: bicycle becomes Rented and the smart lock is
            // Unlocked because the rider is now authorized to use it.
            $bicycle->update([
                'status' => Bicycle::STATUS_RENTED,
                'currentRider' => $rental->riderId,
                'currentRentalId' => $rental->id,
                'lockStatus' => Bicycle::LOCK_UNLOCKED,
            ]);

            $this->iotService->sendCommand($bicycle->id, 'unlock', [], auth()->user());
        }

        $rental->rider->increment('totalRentals');

        Payment::where('rentalId', $rental->id)
            ->where('paymentMethod', 'gcash')
            ->update([
                'status' => 'paid',
                'paidAt' => now(),
            ]);

        AuditLog::record('gcash_payment_verified', auth()->id(), [
            'rentalId' => $rental->rentalId,
            'amount' => $rental->totalFee,
            'paymentMethod' => 'gcash',
        ]);

        $this->notificationService->create(
            $rental->riderId,
            'Payment Verified',
            "Your GCash payment for rental {$rental->rentalId} has been verified. Your rental is now active!",
            'rental_status',
            ['rentalId' => $rental->rentalId]
        );

        return back()->with('success', 'GCash payment verified. Rental activated.');
    }

    public function markPaid(int $id): RedirectResponse
    {
        $rental = Rental::with(['rider'])->findOrFail($id);

        // Only ongoing (active/overdue) rentals may be manually marked paid,
        // and only when payment is still pending.
        $markPaidError = $this->markPaidError($rental);

        if ($markPaidError !== null) {
            return back()->withErrors(['rental' => $markPaidError]);
        }

        $rental->update([
            'paymentStatus' => 'paid',
            'paidAt' => now(),
        ]);

        // Keep the Payments module in sync by updating any linked payment
        // record for this rental to Paid (idempotent status/paidAt update).
        Payment::where('rentalId', $rental->id)
            ->where('paymentMethod', 'cash')
            ->update([
                'status' => 'paid',
                'paidAt' => now(),
            ]);

        AuditLog::record('rental_marked_paid', auth()->id(), [
            'rentalId' => $rental->rentalId,
            'amount' => $rental->totalFee,
            'paymentMethod' => 'cash',
            'paidAt' => now()->toDateTimeString(),
        ]);

        $this->notificationService->create(
            $rental->riderId,
            'Payment Confirmed',
            "Your cash payment for rental {$rental->rentalId} has been confirmed and marked as paid.",
            'payment_status',
            ['rentalId' => $rental->rentalId]
        );

        return back()->with('success', 'Rental marked as paid.');
    }

    private function markPaidError(Rental $rental): ?string
    {
        $guards = [
            [! in_array($rental->status, [Rental::STATUS_ACTIVE, Rental::STATUS_OVERDUE], true), 'Only active or overdue rentals can be marked as paid.'],
            [$rental->paymentStatus === 'paid', 'This rental is already marked as paid.'],
            [$rental->paymentMethod === 'gcash', 'GCash payments can only be marked paid after payment confirmation.'],
        ];

        foreach ($guards as $guard) {
            if ($guard[0]) {
                return $guard[1];
            }
        }

        return null;
    }

    public function endRide(int $id): RedirectResponse
    {
        $rental = Rental::with(['bicycle', 'rider'])->findOrFail($id);

        $endRideError = $this->endRideError($rental);

        if ($endRideError !== null) {
            return back()->withErrors(['rental' => $endRideError]);
        }

        $rider = User::find($rental->riderId);

        try {
            // Records the end time, computes the final fee from the elapsed
            // duration, completes the rental, and returns the bicycle to
            // Available with its smart lock re-secured.
            $result = $this->rentalService->returnRental(
                $rental,
                $rider,
                null,
                null,
                $rental->paymentMethod,
                $rental->paymentReference,
                'Ride ended by administrator'
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['rental' => $e->getMessage()]);
        }

        AuditLog::record('rental_ended_by_admin', auth()->id(), [
            'rentalId' => $rental->rentalId,
            'totalFee' => $result['fees']['totalFee'],
            'durationMinutes' => $result['fees']['durationMinutes'],
        ]);

        return back()->with(
            'success',
            'Ride ended successfully. Final fee: ₱'.number_format($result['fees']['totalFee'], 2)
            .' · Bicycle "'.($rental->bicycle->name ?? $rental->bicycleId).'" is now available.'
        );
    }

    private function endRideError(Rental $rental): ?string
    {
        if (! in_array($rental->status, [Rental::STATUS_ACTIVE, Rental::STATUS_OVERDUE], true)) {
            return 'Only active or overdue rides can be ended.';
        }

        if (User::find($rental->riderId) === null) {
            return 'The rider account for this rental no longer exists.';
        }

        return null;
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $rental = Rental::with(['bicycle'])->findOrFail($id);

        if (in_array($rental->status, [Rental::STATUS_COMPLETED, Rental::STATUS_CANCELLED], true)) {
            return back()->withErrors(['rental' => 'This rental cannot be cancelled.']);
        }

        $previousStatus = $rental->status;

        $rental->update([
            'status' => Rental::STATUS_CANCELLED,
            'cancelledBy' => (string) auth()->id(),
            'cancelReason' => $request->input('reason'),
        ]);

        $bicycle = $rental->bicycle;
        if ($bicycle && (string) $bicycle->currentRentalId === (string) $rental->id) {
            // Rental cancelled: bicycle becomes Available and the smart lock
            // is Locked again since nobody is authorized to use it.
            $bicycle->update([
                'status' => Bicycle::STATUS_AVAILABLE,
                'currentRider' => null,
                'currentRentalId' => null,
                'lockStatus' => Bicycle::LOCK_LOCKED,
            ]);

            $this->iotService->sendCommand($bicycle->id, 'lock', [], auth()->user());
        }

        AuditLog::record('rental_cancelled', auth()->id(), [
            'rentalId' => $rental->rentalId,
            'previous_status' => $previousStatus,
            'reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Rental cancelled successfully.');
    }
}
