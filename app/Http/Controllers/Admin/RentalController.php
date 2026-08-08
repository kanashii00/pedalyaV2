<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\Rental;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RentalController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): Response
    {
        $query = Rental::with(['bicycle', 'rider']);

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

    public function show(int $id): Response
    {
        $rental = Rental::with(['bicycle', 'rider'])->findOrFail($id);

        return response()->view('admin.rentals.show', compact('rental'));
    }

    public function approve(Request $request, int $id): RedirectResponse
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
            $bicycle->update([
                'status' => Bicycle::STATUS_RENTED,
                'currentRider' => $rental->riderId,
                'currentRentalId' => $rental->rentalId,
                'lockStatus' => 'unlocked',
            ]);
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
        if ($bicycle && $bicycle->currentRentalId === $rental->rentalId) {
            $bicycle->update([
                'status' => Bicycle::STATUS_AVAILABLE,
                'currentRider' => null,
                'currentRentalId' => null,
                'lockStatus' => 'locked',
            ]);
        }

        AuditLog::record('rental_cancelled', auth()->id(), [
            'rentalId' => $rental->rentalId,
            'previous_status' => $previousStatus,
            'reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Rental cancelled successfully.');
    }
}
