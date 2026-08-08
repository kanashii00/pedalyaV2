<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentController extends Controller
{
    public function __construct(
        protected RentalService $rentalService,
    ) {}

    public function index(): View
    {
        $bicycles = Bicycle::available()
            ->orderBy('batteryLevel', 'desc')
            ->get();

        return view('rider.rent', compact('bicycles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'bicycleId' => ['required', 'integer', 'exists:bicycles,id'],
        ]);

        $user = $request->user();

        try {
            $this->rentalService->startRental($user, $request->bicycleId);

            return redirect()->route('rider.dashboard')
                ->with('success', 'Rental started successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['bicycleId' => $e->getMessage()]);
        }
    }

    public function history(Request $request): View
    {
        $user = $request->user();

        $rentals = $user->rentals()
            ->with('bicycle')
            ->latest()
            ->paginate(15);

        $totalRentals = $user->totalRentals ?? 0;
        $totalSpent = $user->totalSpent ?? 0;

        $completedRentals = $user->rentals()->where('status', 'completed')->get();
        $totalMinutes = $completedRentals->sum('durationMinutes');
        $totalTime = floor($totalMinutes / 60) . 'h ' . ($totalMinutes % 60) . 'm';

        return view('rider.history', compact('rentals', 'totalRentals', 'totalSpent', 'totalTime'));
    }

    public function returnRental(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        $rental = Rental::where('id', $id)
            ->where('riderId', $user->id)
            ->where('status', Rental::STATUS_ACTIVE)
            ->firstOrFail();

        try {
            $this->rentalService->returnRental(
                $rental,
                $user,
                $request->input('end_lat'),
                $request->input('end_lng'),
                $request->input('payment_method'),
                $request->input('payment_reference'),
                $request->input('notes'),
            );

            return redirect()->route('rider.dashboard')
                ->with('success', 'Rental returned successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['return' => $e->getMessage()]);
        }
    }
}
