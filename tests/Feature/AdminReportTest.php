<?php

namespace Tests\Feature;

use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\Rental;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->makeAdmin();
    }

    public function test_index_renders(): void
    {
        $this->makeBicycle(['name' => 'Report Bike']);
        $this->makeRider(['name' => 'Report Rider']);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.index'))
            ->assertOk();
    }

    public function test_rental_report_returns_json(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_COMPLETED, 'totalFee' => 50]);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.rental'), [
                'date_from' => now()->subDay()->toDateString(),
                'date_to' => now()->addDay()->toDateString(),
                'user_id' => $rider->id,
                'bicycle_id' => $bike->id,
                'status' => Rental::STATUS_COMPLETED,
            ])
            ->assertOk()
            ->assertJsonStructure(['summary', 'data', 'reportId']);
    }

    public function test_revenue_report_returns_json(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_COMPLETED, 'totalFee' => 100, 'updated_at' => now()]);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.revenue'), [
                'date_from' => now()->subDay()->toDateString(),
                'date_to' => now()->addDay()->toDateString(),
                'group_by' => 'day',
            ])
            ->assertOk()
            ->assertJsonStructure(['summary', 'data', 'reportId']);
    }

    public function test_incident_report_returns_json(): void
    {
        $bike = $this->makeBicycle();
        Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'accident',
            'severity' => 'minor',
            'acknowledged' => false,
            'status' => 'open',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.incident'), [
                'date_from' => now()->subDay()->toDateString(),
                'date_to' => now()->addDay()->toDateString(),
                'severity' => 'minor',
                'incident_type' => 'accident',
            ])
            ->assertOk()
            ->assertJsonStructure(['summary', 'data', 'reportId']);
    }

    public function test_export_csv_streams_rental_report(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_COMPLETED, 'totalFee' => 50]);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.export.csv'))
            ->assertOk();
    }

    public function test_export_csv_revenue_and_incident_types(): void
    {
        $bike = $this->makeBicycle();
        Accident::create(['bicycleId' => $bike->id, 'type' => 'accident', 'severity' => 'minor', 'status' => 'open']);

        $this->actingAs($this->admin)->get(route('admin.reports.export.csv', ['type' => 'revenue']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.export.csv', ['type' => 'incident']))->assertOk();
    }

    public function test_export_excel_streams(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.reports.export.excel'))
            ->assertOk();
    }

    public function test_rental_report_error_returns_500(): void
    {
        $service = Mockery::mock(ReportService::class);
        $service->shouldReceive('getRentalReport')->once()->andThrow(new \RuntimeException('DB down'));
        $this->app->instance(ReportService::class, $service);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.rental'))
            ->assertStatus(500);
    }

    public function test_revenue_report_error_returns_500(): void
    {
        $service = Mockery::mock(ReportService::class);
        $service->shouldReceive('getRevenueReport')->once()->andThrow(new \RuntimeException('DB down'));
        $this->app->instance(ReportService::class, $service);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.revenue'))
            ->assertStatus(500);
    }

    public function test_incident_report_error_returns_500(): void
    {
        $service = Mockery::mock(ReportService::class);
        $service->shouldReceive('getIncidentReport')->once()->andThrow(new \RuntimeException('DB down'));
        $this->app->instance(ReportService::class, $service);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.incident'))
            ->assertStatus(500);
    }

    public function test_accident_report_error_returns_500(): void
    {
        $service = Mockery::mock(ReportService::class);
        $service->shouldReceive('getAccidentReport')->once()->andThrow(new \RuntimeException('DB down'));
        $this->app->instance(ReportService::class, $service);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.accident'))
            ->assertStatus(500);
    }

    public function test_customer_report_error_returns_500(): void
    {
        $service = Mockery::mock(ReportService::class);
        $service->shouldReceive('getCustomerReport')->once()->andThrow(new \RuntimeException('DB down'));
        $this->app->instance(ReportService::class, $service);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.customer'))
            ->assertStatus(500);
    }

    public function test_bicycle_report_error_returns_500(): void
    {
        $service = Mockery::mock(ReportService::class);
        $service->shouldReceive('getBicycleReport')->once()->andThrow(new \RuntimeException('DB down'));
        $this->app->instance(ReportService::class, $service);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.bicycle'))
            ->assertStatus(500);
    }

    public function test_theft_report_error_returns_500(): void
    {
        $service = Mockery::mock(ReportService::class);
        $service->shouldReceive('getTheftReport')->once()->andThrow(new \RuntimeException('DB down'));
        $this->app->instance(ReportService::class, $service);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.theft'))
            ->assertStatus(500);
    }

    public function test_export_csv_bicycle_and_theft_types(): void
    {
        $bike = $this->makeBicycle();

        $this->actingAs($this->admin)->get(route('admin.reports.export.csv', ['type' => 'bicycle']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.export.csv', ['type' => 'theft']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.export.csv', ['type' => 'customer']))->assertOk();
    }

    public function test_export_excel_bicycle_and_theft_types(): void
    {
        $this->actingAs($this->admin)->get(route('admin.reports.export.excel', ['type' => 'bicycle']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.export.excel', ['type' => 'theft']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.export.excel', ['type' => 'customer']))->assertOk();
    }

    public function test_export_csv_all_types_covers_build_report_match(): void
    {
        $this->actingAs($this->admin)->get(route('admin.reports.export.csv', ['type' => 'rental']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.export.csv', ['type' => 'accident']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.export.csv', ['type' => 'revenue']))->assertOk();
    }

    public function test_accident_report_returns_json(): void
    {
        $bike = $this->makeBicycle();
        Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'accident',
            'severity' => 'minor',
            'acknowledged' => false,
            'status' => 'open',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.accident'), [
                'date_from' => now()->subDay()->toDateString(),
                'date_to' => now()->addDay()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonStructure(['summary', 'data', 'reportId']);
    }

    public function test_customer_report_returns_json(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.reports.customer'))
            ->assertOk()
            ->assertJsonStructure(['summary', 'data', 'reportId']);
    }

    public function test_bicycle_report_returns_json(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.reports.bicycle'))
            ->assertOk()
            ->assertJsonStructure(['summary', 'data', 'reportId']);
    }

    public function test_theft_report_returns_json(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.reports.theft'))
            ->assertOk()
            ->assertJsonStructure(['summary', 'data', 'reportId']);
    }
}
