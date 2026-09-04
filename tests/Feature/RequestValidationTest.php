<?php

namespace Tests\Feature;

use App\Http\Requests\StoreBicycleRequest;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\UpdateSystemSettingsRequest;
use App\Models\Bicycle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    private int $testBicycleId;

    protected function setUp(): void
    {
        parent::setUp();

        $bicycle = Bicycle::create([
            'name' => 'Test Bicycle',
            'serialNumber' => 'SN-MAINT-001',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15.00,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 80,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $this->testBicycleId = $bicycle->id;
    }

    private function validateRequest($requestClass, array $data): bool
    {
        $request = new $requestClass();
        $validator = Validator::make($data, $request->rules());

        return $validator->passes();
    }

    public function test_store_bicycle_request_valid(): void
    {
        $this->assertTrue($this->validateRequest(StoreBicycleRequest::class, [
            'name' => 'Test Bike',
            'serial_number' => 'SN-001',
        ]));
    }

    public function test_store_bicycle_request_requires_name_and_serial(): void
    {
        $this->assertFalse($this->validateRequest(StoreBicycleRequest::class, []));
    }

    public function test_store_bicycle_request_optional_fields(): void
    {
        $this->assertTrue($this->validateRequest(StoreBicycleRequest::class, [
            'name' => 'Bike',
            'serial_number' => 'SN-002',
            'model' => 'City',
            'description' => 'A bike',
            'hourly_rate' => 15,
            'current_lat' => 14.5995,
            'current_lng' => 120.9842,
            'battery_level' => 80,
        ]));
    }

    public function test_store_bicycle_request_validates_battery_range(): void
    {
        $this->assertFalse($this->validateRequest(StoreBicycleRequest::class, [
            'name' => 'Bike',
            'serial_number' => 'SN-003',
            'battery_level' => 101,
        ]));
    }

    public function test_store_bicycle_request_validates_lat_range(): void
    {
        $this->assertFalse($this->validateRequest(StoreBicycleRequest::class, [
            'name' => 'Bike',
            'serial_number' => 'SN-004',
            'current_lat' => 91,
        ]));
    }

    public function test_store_bicycle_request_validates_lng_range(): void
    {
        $this->assertFalse($this->validateRequest(StoreBicycleRequest::class, [
            'name' => 'Bike',
            'serial_number' => 'SN-005',
            'current_lng' => 181,
        ]));
    }

    public function test_store_maintenance_request_valid(): void
    {
        $this->assertTrue($this->validateRequest(StoreMaintenanceRequest::class, [
            'bicycle_id' => $this->testBicycleId,
            'type' => 'routine',
        ]));
    }

    public function test_store_maintenance_request_requires_bicycle_id_and_type(): void
    {
        $this->assertFalse($this->validateRequest(StoreMaintenanceRequest::class, []));
    }

    public function test_store_maintenance_request_validates_severity(): void
    {
        $this->assertTrue($this->validateRequest(StoreMaintenanceRequest::class, [
            'bicycle_id' => $this->testBicycleId,
            'type' => 'repair',
            'severity' => 'high',
        ]));

        $this->assertFalse($this->validateRequest(StoreMaintenanceRequest::class, [
            'bicycle_id' => $this->testBicycleId,
            'type' => 'repair',
            'severity' => 'invalid',
        ]));
    }

    public function test_store_maintenance_request_optional_fields(): void
    {
        $this->assertTrue($this->validateRequest(StoreMaintenanceRequest::class, [
            'bicycle_id' => $this->testBicycleId,
            'type' => 'repair',
            'description' => 'Needs fix',
            'severity' => 'medium',
            'estimated_cost' => 500,
            'technician' => 'Tech Joe',
            'scheduled_date' => '2026-04-01',
            'notes' => 'Urgent',
        ]));
    }

    public function test_update_system_settings_request_valid(): void
    {
        $this->assertTrue($this->validateRequest(UpdateSystemSettingsRequest::class, []));
    }

    public function test_update_system_settings_request_validates_numeric_fields(): void
    {
        $this->assertTrue($this->validateRequest(UpdateSystemSettingsRequest::class, [
            'geofence_radius' => 500,
            'geofence_alert_enabled' => true,
            'accident_sensitivity' => 75,
            'battery_alert_threshold' => 20,
            'max_rental_hours' => 24,
            'base_fare' => 10,
            'per_minute_rate' => 0.5,
            'deposit_amount' => 100,
            'parking_fee' => 5,
            'late_fee_per_hour' => 10,
            'heartbeat_interval' => 30,
            'gps_update_interval' => 10,
            'maintenance_interval_days' => 30,
            'low_battery_threshold' => 15,
        ]));
    }

    public function test_update_system_settings_request_validates_ranges(): void
    {
        $this->assertFalse($this->validateRequest(UpdateSystemSettingsRequest::class, [
            'accident_sensitivity' => 101,
        ]));

        $this->assertFalse($this->validateRequest(UpdateSystemSettingsRequest::class, [
            'battery_alert_threshold' => 101,
        ]));

        $this->assertFalse($this->validateRequest(UpdateSystemSettingsRequest::class, [
            'max_rental_hours' => 0,
        ]));

        $this->assertFalse($this->validateRequest(UpdateSystemSettingsRequest::class, [
            'heartbeat_interval' => 0,
        ]));
    }
}
