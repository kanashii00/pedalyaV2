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

    public function test_get_rental_report_search_filter(): void
    {
        $rider = $this->makeRider(['name' => 'Search Rider']);
        $bike = $this->makeBicycle();

        $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_COMPLETED,
            'riderName' => 'Search Rider',
            'riderEmail' => 'search@test.com',
            'rentalId' => 'REN-SEARCH-001',
            'totalFee' => 25,
        ]);

        $report = $this->service->getRentalReport(['search' => 'Search Rider']);
        $this->assertSame(1, $report['summary']['total']);
    }

    public function test_get_rental_report_payment_status_filter(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();

        $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_COMPLETED,
            'paymentStatus' => 'paid',
            'totalFee' => 30,
        ]);

        $report = $this->service->getRentalReport(['payment_status' => 'paid']);
        $this->assertSame(1, $report['summary']['total']);
    }

    public function test_get_accident_report_with_filters(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();

        Accident::create([
            'bicycleId' => $bike->id,
            'riderId' => $rider->id,
            'type' => 'accident',
            'severity' => 'major',
            'status' => 'open',
            'acknowledged' => false,
            'description' => 'Crash on Main St',
            'actionTaken' => 'Ambulance called',
            'created_at' => now(),
        ]);

        $report = $this->service->getAccidentReport([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'severity' => 'major',
            'status' => 'open',
            'bicycleId' => $bike->id,
            'riderId' => $rider->id,
            'type' => 'accident',
            'search' => 'Crash',
        ]);

        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('data', $report);
    }

    public function test_get_accident_report_empty(): void
    {
        $report = $this->service->getAccidentReport([]);
        $this->assertSame(0, $report['summary']['total']);
    }

    public function test_get_bicycle_report_with_filters(): void
    {
        $bike = $this->makeBicycle([
            'name' => 'Filter Bike',
            'model' => 'City V2',
            'serialNumber' => 'SN-FILTER-001',
            'status' => Bicycle::STATUS_AVAILABLE,
        ]);

        $report = $this->service->getBicycleReport([
            'status' => Bicycle::STATUS_AVAILABLE,
            'search' => 'Filter Bike',
        ]);

        $this->assertSame(1, $report['summary']['total']);
        $this->assertSame(1, $report['summary']['available']);
    }

    public function test_get_bicycle_report_search_by_model(): void
    {
        $this->makeBicycle(['name' => 'Alpha', 'model' => 'SpeedX', 'serialNumber' => 'SN-AX-001']);
        $this->makeBicycle(['name' => 'Beta', 'model' => 'SpeedX', 'serialNumber' => 'SN-BX-001']);

        $report = $this->service->getBicycleReport(['search' => 'SpeedX']);
        $this->assertSame(2, $report['summary']['total']);
    }

    public function test_get_bicycle_report_search_by_serial(): void
    {
        $this->makeBicycle(['serialNumber' => 'SN-UNIQUE-123']);

        $report = $this->service->getBicycleReport(['search' => 'UNIQUE']);
        $this->assertSame(1, $report['summary']['total']);
    }

    public function test_get_theft_report_with_filters(): void
    {
        $bike = $this->makeBicycle();

        Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'theft',
            'severity' => 'critical',
            'status' => 'open',
            'acknowledged' => false,
            'description' => 'Bike stolen near gate',
            'actionTaken' => 'Reported to police',
            'created_at' => now(),
        ]);

        $report = $this->service->getTheftReport([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => 'open',
            'bicycleId' => $bike->id,
            'search' => 'stolen',
        ]);

        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('data', $report);
    }

    public function test_get_theft_report_empty(): void
    {
        $report = $this->service->getTheftReport([]);
        $this->assertSame(0, $report['summary']['total']);
        $this->assertSame(0, $report['summary']['open']);
    }

    public function test_get_customer_report_with_filters(): void
    {
        $rider = $this->makeRider([
            'name' => 'Filter Customer',
            'email' => 'filter@test.com',
            'phoneNumber' => '09171234567',
            'studentId' => 'S-FILTER',
            'status' => 'active',
            'verified' => true,
        ]);

        $this->makeRental([
            'riderId' => $rider->id,
            'status' => Rental::STATUS_COMPLETED,
            'totalFee' => 45,
        ]);

        $report = $this->service->getCustomerReport([
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => 'active',
            'verified' => '1',
            'search' => 'Filter Customer',
        ]);

        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('data', $report);
    }

    public function test_get_customer_report_empty(): void
    {
        $report = $this->service->getCustomerReport([]);
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('data', $report);
    }

    public function test_get_rental_report_awaiting_return_and_returned_statuses(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();

        $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_AWAITING_RETURN,
            'totalFee' => 0,
        ]);
        $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_RETURNED,
            'totalFee' => 20,
        ]);

        $report = $this->service->getRentalReport([]);

        $this->assertSame(2, $report['summary']['total']);
        $this->assertSame(1, $report['summary']['awaiting_return']);
        $this->assertSame(1, $report['summary']['returned']);
    }
}
