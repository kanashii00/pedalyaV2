<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\GeofenceService;
use App\Services\RiderCacheService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
        protected RiderCacheService $riderCacheService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Live data — never cached: active rental drives the on-page
        // countdown and battery reading; recent rentals show current status.
        $activeRental = $user->currentRental()
            ->with('bicycle')
            ->first();

        $recentRentals = $user->rentals()
            ->with('bicycle')
            ->latest()
            ->limit(5)
            ->get();

        // Cached data: user dashboard summary + shared bicycle catalog.
        $summary = $this->riderCacheService->summary($user->id);

        $bicycles = $this->riderCacheService->locatedAvailableBicycles();

        $geofenceCenter = app(GeofenceService::class)->getConfig();

        $warningMinutes = (int) SystemSetting::getValue('overdueBuzzerMinutes', 5);

        return view('rider.dashboard', [
            'user' => $user,
            'activeRental' => $activeRental,
            'recentRentals' => $recentRentals,
            'bicycles' => $bicycles,
            'geofenceCenter' => $geofenceCenter,
            'warningMinutes' => $warningMinutes,
            'totalRentals' => $summary['totalRentals'],
            'totalSpent' => $summary['totalSpent'],
            'unreadCount' => $summary['unreadCount'],
        ]);
    }
}
