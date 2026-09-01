<?php

namespace App\Services;

use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\MaintenanceRecord;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;

class ReportService
{
public function getDashboardStats(): array
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $bicycles = $this->bicycleCounts();
        $rentals = $this->rentalCounts();
        $users = $this->userCounts();
        $battery = $this->batteryDistribution();

        $todayTotal = Rental::whereDate('created_at', $today)->count();

        $returnedToday = Rental::where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->count();

        $weeklyRentals = Rental::where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())->count();

        $iotOnline = Bicycle::where('lastHeartbeat', '>=', now()->subMinutes(5))->count();
        $gpsOnline = Bicycle::where('lastGpsUpdate', '>=', now()->subMinutes(5))->count();

        $theftActive = Accident::where('type', 'theft')->where('acknowledged', false)->count();
        $unacknowledgedIncidents = Accident::where('acknowledged', false)->count();
        $activeAlerts = $theftActive + $unacknowledgedIncidents;

        $maintenanceRequests = MaintenanceRecord::whereIn('status', ['scheduled', 'in_progress'])->count();

        $todayRevenue = Rental::where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->sum('totalFee');

        $totalRevenue = Rental::where('status', 'completed')
            ->sum('totalFee');

        $monthlyRevenue = Rental::where('status', 'completed')
            ->where('updated_at', '>=', $monthStart)
            ->sum('totalFee');

        $monthlyRentals = Rental::where('created_at', '>=', $monthStart)->count();

        $monthlyTrends = $this->monthlyTrends();
        $weeklyTrend = $this->weeklyTrend();
        $peakHours = $this->peakHours();
        $incidentTrends = $this->incidentTrends();

        $utilization = ((int) ($bicycles->total ?? 0) > 0)
            ? round(((int) ($rentals->active_count ?? 0) / (int) $bicycles->total) * 100, 1)
            : 0;

        return [
            'bicycles' => [
                'total' => (int) ($bicycles->total ?? 0),
                'available' => (int) ($bicycles->available_count ?? 0),
                'rented' => (int) ($bicycles->rented_count ?? 0),
                'maintenance' => (int) ($bicycles->maintenance_count ?? 0),
                'lowBattery' => (int) ($bicycles->low_battery_count ?? 0),
            ],
            'rentals' => [
                'active' => (int) ($rentals->active_count ?? 0),
                'todayTotal' => (int) $todayTotal,
                'totalCompleted' => (int) ($rentals->completed_count ?? 0),
                'reserved' => (int) ($rentals->reserved_count ?? 0),
                'returnedToday' => $returnedToday,
                'weekly' => $weeklyRentals,
            ],
            'users' => [
                'total' => (int) ($users->total ?? 0),
                'verified' => (int) ($users->verified_count ?? 0),
                'pendingVerification' => (int) ($users->pending_count ?? 0),
            ],
            'devices' => [
                'iotOnline' => $iotOnline,
                'gpsOnline' => $gpsOnline,
                'total' => (int) ($bicycles->total ?? 0),
            ],
            'alerts' => [
                'theftActive' => $theftActive,
                'accidentActive' => $unacknowledgedIncidents,
                'activeAlerts' => $activeAlerts,
                'maintenanceRequests' => $maintenanceRequests,
            ],
            'revenue' => [
                'today' => (float) $todayRevenue,
                'total' => (float) $totalRevenue,
                'monthly' => (float) $monthlyRevenue,
            ],
            'incidents' => [
                'unacknowledged' => $unacknowledgedIncidents,
            ],
            'monthlyRentals' => $monthlyRentals,
            'monthlyRevenueLabels' => $monthlyTrends['monthlyRevenueLabels'],
            'monthlyRevenueData' => $monthlyTrends['monthlyRevenueData'],
            'monthlyRentalsLabels' => $monthlyTrends['monthlyRentalsLabels'],
            'monthlyRentalsData' => $monthlyTrends['monthlyRentalsData'],
            'weeklyLabels' => $weeklyTrend['weeklyLabels'],
            'weeklyData' => $weeklyTrend['weeklyData'],
            'peakLabels' => $peakHours['peakLabels'],
            'peakData' => $peakHours['peakData'],
            'battery' => [
                'low' => (int) ($battery->low ?? 0),
                'mid' => (int) ($battery->mid ?? 0),
                'good' => (int) ($battery->good ?? 0),
                'full' => (int) ($battery->full ?? 0),
            ],
            'utilization' => $utilization,
            'theftTrendData' => $incidentTrends['theftTrendData'],
            'accidentTrendData' => $incidentTrends['accidentTrendData'],
        ];
    }

    private function bicycleCounts(): ?object
    {
        return Bicycle::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available_count,
            SUM(CASE WHEN status = "rented" THEN 1 ELSE 0 END) as rented_count,
            SUM(CASE WHEN status = "maintenance" THEN 1 ELSE 0 END) as maintenance_count,
            SUM(CASE WHEN batteryLevel < 20 THEN 1 ELSE 0 END) as low_battery_count
        ')->first();
    }

    private function rentalCounts(): ?object
    {
        return Rental::selectRaw('
            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as reserved_count
        ')->first();
    }

    private function userCounts(): ?object
    {
        return User::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_count,
            SUM(CASE WHEN verified = 0 THEN 1 ELSE 0 END) as pending_count
        ')->first();
    }

    private function batteryDistribution(): ?object
    {
        return Bicycle::selectRaw('
            SUM(batteryLevel <= 20) as low,
            SUM(batteryLevel > 20 AND batteryLevel <= 50) as mid,
            SUM(batteryLevel > 50 AND batteryLevel <= 80) as good,
            SUM(batteryLevel > 80) as full
        ')->first();
    }

    private function monthlyTrends(): array
    {
        $monthlyRevenueLabels = [];
        $monthlyRevenueData = [];
        $monthlyRentalsLabels = [];
        $monthlyRentalsData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyRevenueLabels[] = $month->format('M');
            $monthlyRentalsLabels[] = $month->format('M');

            $monthRevenue = Rental::where('status', 'completed')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->sum('totalFee');
            $monthlyRevenueData[] = (float) $monthRevenue;

            $monthRentals = Rental::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $monthlyRentalsData[] = (int) $monthRentals;
        }

        return compact('monthlyRevenueLabels', 'monthlyRevenueData', 'monthlyRentalsLabels', 'monthlyRentalsData');
    }

    private function weeklyTrend(): array
    {
        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $weeklyLabels[] = $day->format('D');
            $weeklyData[] = Rental::whereDate('created_at', $day->toDateString())->count();
        }

        return compact('weeklyLabels', 'weeklyData');
    }

    private function peakHours(): array
    {
        $recentRentals = Rental::where('created_at', '>=', Carbon::now()->subDays(7))
            ->get(['created_at']);

        $hourCounts = [];
        foreach ($recentRentals as $recentRental) {
            $hour = (int) $recentRental->created_at->format('H');
            $hourCounts[$hour] = ($hourCounts[$hour] ?? 0) + 1;
        }

        $peakLabels = [];
        $peakData = [];
        for ($h = 0; $h < 24; $h++) {
            $peakLabels[] = Carbon::createFromTime($h, 0)->format('gA');
            $peakData[] = (int) ($hourCounts[$h] ?? 0);
        }

        return compact('peakLabels', 'peakData');
    }

    private function incidentTrends(): array
    {
        $theftTrendData = [];
        $accidentTrendData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $theftTrendData[] = Accident::where('type', 'theft')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $accidentTrendData[] = Accident::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return compact('theftTrendData', 'accidentTrendData');
    }

    public function getRentalReport(array $filters): array
    {
        $query = Rental::with(['rider', 'bicycle']);

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['riderId']) && $filters['riderId'] !== '') {
            $query->where('riderId', $filters['riderId']);
        }
        if (isset($filters['bicycleId']) && $filters['bicycleId'] !== '') {
            $query->where('bicycleId', $filters['bicycleId']);
        }

        $rentals = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total' => $rentals->count(),
            'completed' => $rentals->where('status', 'completed')->count(),
            'cancelled' => $rentals->where('status', 'cancelled')->count(),
            'active' => $rentals->where('status', 'active')->count(),
            'totalRevenue' => $rentals->where('status', 'completed')->sum('totalFee'),
            'averageFee' => $rentals->where('status', 'completed')->avg('totalFee') ?? 0,
        ];

        return [
            'reportId' => $this->generateReportId(),
            'summary' => $summary,
            'data' => $rentals,
        ];
    }

    public function getRevenueReport(array $filters, string $groupBy = 'month'): array
    {
        $query = Rental::where('status', 'completed');

        if (isset($filters['start_date'])) {
            $query->where('updated_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('updated_at', '<=', $filters['end_date']);
        }

        $dateFormat = match ($groupBy) {
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m',
        };

        $rentals = $query->get(['updated_at', 'totalFee', 'durationMinutes']);

        $grouped = [];
        foreach ($rentals as $rental) {
            $period = Carbon::parse($rental->updated_at)->format($dateFormat);
            if (!isset($grouped[$period])) {
                $grouped[$period] = ['total_rentals' => 0, 'total_revenue' => 0, 'total_duration_minutes' => 0];
            }
            $grouped[$period]['total_rentals']++;
            $grouped[$period]['total_revenue'] += (float) $rental->totalFee;
            $grouped[$period]['total_duration_minutes'] += (int) $rental->durationMinutes;
        }

        $data = collect($grouped)
            ->map(function (array $values, string $period) {
                $values['period'] = $period;
                $values['avg_revenue'] = $values['total_rentals'] > 0
                    ? $values['total_revenue'] / $values['total_rentals']
                    : 0;
                return (object) $values;
            })
            ->sortBy('period')
            ->values();

        $summary = [
            'totalRevenue' => $data->sum('total_revenue'),
            'totalRentals' => $data->sum('total_rentals'),
            'averageRevenue' => $data->avg('total_revenue') ?? 0,
            'periods' => $data->count(),
        ];

        return [
            'reportId' => $this->generateReportId(),
            'summary' => $summary,
            'data' => $data,
        ];
    }

    public function getIncidentReport(array $filters): array
    {
        $query = Accident::with(['bicycle', 'rider']);

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        if (isset($filters['severity']) && $filters['severity'] !== '') {
            $query->where('severity', $filters['severity']);
        }
        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        $accidents = $query->orderBy('created_at', 'desc')->get();

        $theftIncidents = $accidents->where('type', 'theft');

        $summary = [
            'total' => $accidents->count(),
            'critical' => $accidents->where('severity', 'critical')->count(),
            'major' => $accidents->where('severity', 'major')->count(),
            'moderate' => $accidents->where('severity', 'moderate')->count(),
            'minor' => $accidents->where('severity', 'minor')->count(),
            'theftIncidents' => $theftIncidents->count(),
            'acknowledged' => $accidents->where('acknowledged', true)->count(),
            'unacknowledged' => $accidents->where('acknowledged', false)->count(),
        ];

        return [
            'reportId' => $this->generateReportId(),
            'summary' => $summary,
            'data' => $accidents,
        ];
    }

    public function generateReportId(): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        return "RPT-{$date}-{$random}";
    }
}
