<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use App\Models\Rental;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $activeRental = $user->currentRental()
            ->with('bicycle')
            ->first();

        $recentRentals = $user->rentals()
            ->with('bicycle')
            ->latest()
            ->limit(5)
            ->get();

        $totalRentals = $user->totalRentals ?? 0;
        $totalSpent = $user->totalSpent ?? 0;

        $bicycles = Bicycle::available()
            ->whereNotNull('currentLat')
            ->whereNotNull('currentLng')
            ->get();

        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        $geofenceCenter = app(\App\Services\GeofenceService::class)->getConfig();

        return view('rider.dashboard', compact(
            'user',
            'activeRental',
            'recentRentals',
            'totalRentals',
            'totalSpent',
            'bicycles',
            'unreadCount',
            'geofenceCenter',
        ));
    }
}
