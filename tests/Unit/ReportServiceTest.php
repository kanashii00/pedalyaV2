<?php

namespace Tests\Unit;

use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\MaintenanceRecord;
use App\Models\Rental;
use App\Models\User;
use App\Services\ReportService;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReportService::class);
    }

    public function test_get_dashboard_stats_returns_all_sections_even_when_empty(): void
    {
        $stats = $this->service->getDashboardStats();

        $this->assertArrayHasKey('bicycles', $stats);
        $this->assertArrayHasKey('rentals', $stats);
        $this->assertArrayHasKey('users', $stats);
        $this->assertArrayHasKey('devices', $stats);
        $this->assertArrayHasKey('alerts', $stats);
        $this->assertArrayHasKey('revenue', $stats);
        $this->assertArrayHasKey('incidents', $stats);
        $this->assertArrayHasKey('monthlyRevenueLabels', $stats);
        $this->assertArrayHasKey('monthlyRevenueData', $stats);
        $this->assertArrayHasKey('monthlyRentalsLabels', $stats);
        $this->assertArrayHasKey('monthlyRentalsData', $stats);
        $this->assertArrayHasKey('weeklyLabels', $stats);
        $this->assertArrayHasKey('weeklyData', $stats);
        $this->assertArrayHasKey('peakLabels', $stats);
        $this->assertArrayHasKey('peakData', $stats);
        $this->assertArrayHasKey('battery', $stats);
        $this->assertArrayHasKey('utilization', $stats);
        $this->assertArrayHasKey('theftTrendData', $stats);
        $this->assertArrayHasKey('accidentTrendData', $stats);
        $this->assertCount(12, $stats['monthlyRevenueLabels']);
        $this->assertCount(24, $stats['peakData']);
        $this->assertCount(7, $stats['weeklyData']);
    }

    public function test_get_dashboard_stats_reflects_existing_data(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED, 'batteryLevel' => 10]);

        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_COMPLETED,
            'totalFee' => 50.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'geofence_breach',
            'severity' => 'critical',
            'acknowledged' => false,
            'status' => 'open',
        ]);

        $this->makeMaintenanceRecord([
            'bicycleId' => $bike->id,
            'status' => MaintenanceRecord::STATUS_IN_PROGRESS,
        ]);

        $stats = $this->service->getDashboardStats();

        $this->assertSame(1, $stats['bicycles']['total']);
        $this->assertSame(0, $stats['bicycles']['available']);
        $this->assertSame(1, $stats['bicycles']['rented']);
        $this->assertSame(1, $stats['bicycles']['lowBattery']);
        $this->assertSame(1, $stats['rentals']['todayTotal']);
        $this->assertSame(1, $stats['rentals']['totalCompleted']);
        $this->assertSame(50.0, $stats['revenue']['today']);
        $this->assertSame(50.0, $stats['revenue']['total']);
        $this->assertSame(1, $stats['alerts']['accidentActive']);
        $this->assertSame(1, $stats['alerts']['maintenanceRequests']);
        $this->assertIsNumeric($stats['utilization']);
    }

    public function test_get_rental_report_filters_and_summary(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();

        $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_COMPLETED,
            'totalFee' => 30.00,
        ]);
        $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_CANCELLED,
            'totalFee' => 0,
        ]);

        $report = $this->service->getRentalReport([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => Rental::STATUS_COMPLETED,
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
        ]);

        $this->assertSame(1, $report['summary']['total']);
        $this->assertSame(1, $report['summary']['completed']);
        $this->assertSame(30.0, $report['summary']['totalRevenue']);
        $this->assertCount(1, $report['data']);
        $this->assertStringStartsWith('RPT-', $report['reportId']);
    }

    public function test_get_rental_report_without_filters(): void
    {
        $report = $this->service->getRentalReport([]);

        $this->assertSame(0, $report['summary']['total']);
        $this->assertArrayHasKey('data', $report);
    }

    public function test_get_revenue_report_by_day(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();

        $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_COMPLETED,
            'totalFee' => 40.00,
            'durationMinutes' => 120,
            'updated_at' => now(),
        ]);

        $report = $this->service->getRevenueReport([], 'day');

        $this->assertSame(40.0, $report['summary']['totalRevenue']);
        $this->assertSame(1, $report['summary']['totalRentals']);
        $this->assertCount(1, $report['data']);
        $this->assertSame(40.0, $report['data']->first()->total_revenue);
        $this->assertSame(120, $report['data']->first()->total_duration_minutes);
    }

    public function test_get_revenue_report_all_groupings(): void
    {
        foreach (['day', 'week', 'month', 'year', 'quarter'] as $groupBy) {
            $report = $this->service->getRevenueReport([], $groupBy);
            $this->assertArrayHasKey('summary', $report);
        }
    }

    public function test_get_revenue_report_with_date_range(): void
    {
        $report = $this->service->getRevenueReport([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ], 'month');

        $this->assertSame(0.0, (float) $report['summary']['totalRevenue']);
    }

    public function test_get_incident_report_filters_and_summary(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();

        Accident::create([
            'bicycleId' => $bike->id,
            'riderId' => $rider->id,
            'type' => 'accident',
            'severity' => 'critical',
            'acknowledged' => false,
            'description' => 'Severe incident',
        ]);
        Accident::create([
            'bicycleId' => $bike->id,
            'riderId' => $rider->id,
            'type' => 'accident',
            'severity' => 'minor',
            'acknowledged' => true,
            'description' => 'Minor fall',
        ]);

        $report = $this->service->getIncidentReport([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'severity' => 'critical',
            'type' => 'accident',
        ]);

        $this->assertSame(1, $report['summary']['total']);
        $this->assertSame(1, $report['summary']['critical']);
        $this->assertSame(0, $report['summary']['theftIncidents']);
        $this->assertCount(1, $report['data']);
    }

    public function test_generate_report_id_is_unique(): void
    {
        $a = $this->service->generateReportId();
        $b = $this->service->generateReportId();

        $this->assertNotSame($a, $b);
        $this->assertStringStartsWith('RPT-', $a);
    }
}
