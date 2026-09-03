<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\MaintenanceRecord;
use App\Models\Rental;
use App\Services\ReportService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(): Response
    {
        $stats = Cache::remember(
            'admin.dashboard.stats',
            now()->addSeconds(30),
            fn () => $this->reportService->getDashboardStats()
        );

        $lowBatteryBicycles = Bicycle::where('status', '!=', 'removed')
            ->where('batteryLevel', '<=', 20)
            ->latest('batteryLevel')
            ->get();

        $maintenanceBicycles = Bicycle::where('status', 'maintenance')
            ->latest()
            ->get();

        $recentRentals = Rental::with(['rider', 'bicycle'])
            ->latest()
            ->limit(6)
            ->get();

        $recentIncidents = Accident::with(['bicycle'])
            ->latest()
            ->limit(6)
            ->get();

        $upcomingMaintenance = MaintenanceRecord::whereIn('status', ['scheduled', 'in_progress'])
            ->with('bicycle')
            ->latest('scheduledDate')
            ->limit(6)
            ->get();

        return response()->view('admin.dashboard', array_merge(['stats' => $stats], compact(
            'lowBatteryBicycles',
            'maintenanceBicycles',
            'recentRentals',
            'recentIncidents',
            'upcomingMaintenance',
        )));
    }
}
