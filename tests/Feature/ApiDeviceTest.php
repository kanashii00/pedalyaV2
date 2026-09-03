<?php

namespace Tests\Feature;

use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\GpsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class ApiDeviceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.device_api_key' => 'test-device-key']);
        config(['broadcasting.default' => 'log']);
    }

    private function deviceHeaders(): array
    {
        return ['X-API-Key' => 'test-device-key'];
    }

    public function test_device_auth_requires_api_key(): void
    {
        $this->postJson('/api/iot/heartbeat', [])->assertStatus(401);
    }

    public function test_device_auth_rejects_invalid_key(): void
    {
        $this->postJson('/api/iot/heartbeat', [], ['X-API-Key' => 'wrong'])->assertStatus(401);
    }

    public function test_heartbeat_processes(): void
    {
        $bike = $this->makeBicycle();

        $this->postJson('/api/iot/heartbeat', [
            'bicycle_id' => $bike->id,
            'device_id' => 'ESP-001',
            'battery' => 80,
            'locked' => true,
        ], $this->deviceHeaders())->assertOk()->assertJsonPath('message', 'Heartbeat processed');
    }

    public function test_heartbeat_requires_valid_bicycle(): void
    {
        $this->postJson('/api/iot/heartbeat', [
            'bicycle_id' => 99999,
            'device_id' => 'ESP-001',
        ], $this->deviceHeaders())->assertStatus(422);
    }

    public function test_accident_report_processes(): void
    {
        $bike = $this->makeBicycle();

        $this->postJson('/api/iot/accident-report', [
            'bicycle_id' => $bike->id,
            'device_id' => 'ESP-001',
            'lat' => 14.6,
            'lng' => 120.99,
            'impact_force' => 15,
        ], $this->deviceHeaders())->assertOk();
    }

    public function test_geofence_alert_processes(): void
    {
        $bike = $this->makeBicycle();

        $this->postJson('/api/iot/geofence-alert', [
            'bicycle_id' => $bike->id,
            'device_id' => 'ESP-001',
            'lat' => 14.6,
            'lng' => 120.99,
            'distance' => 50,
        ], $this->deviceHeaders())->assertOk();
    }

    public function test_device_bicycle_status_returns_commands(): void
    {
        $bike = $this->makeBicycle();
        DeviceCommand::create(['bicycleId' => $bike->id, 'command' => 'lock', 'status' => 'pending']);

        $this->postJson("/api/iot/bicycle/{$bike->id}/status", [], $this->deviceHeaders())
            ->assertOk()
            ->assertJsonPath('bicycle_id', $bike->id);
    }

    public function test_device_bicycle_status_not_found(): void
    {
        $this->postJson('/api/iot/bicycle/99999/status', [], $this->deviceHeaders())->assertStatus(404);
    }

    public function test_acknowledge_command(): void
    {
        $bike = $this->makeBicycle();
        $cmd = DeviceCommand::create(['bicycleId' => $bike->id, 'command' => 'lock', 'status' => 'pending']);

        $this->postJson("/api/iot/bicycle/{$bike->id}/command-ack", [
            'command_id' => $cmd->id,
            'status' => 'executed',
        ], $this->deviceHeaders())
            ->assertOk()
            ->assertJsonPath('message', 'Command acknowledged');

        $this->assertSame('executed', $cmd->fresh()->status);
    }

    public function test_acknowledge_command_wrong_bicycle(): void
    {
        $bike = $this->makeBicycle();
        $other = $this->makeBicycle();
        $cmd = DeviceCommand::create(['bicycleId' => $other->id, 'command' => 'lock', 'status' => 'pending']);

        $this->postJson("/api/iot/bicycle/{$bike->id}/command-ack", [
            'command_id' => $cmd->id,
        ], $this->deviceHeaders())->assertStatus(404);
    }

    public function test_gps_location_records_log(): void
    {
        $bike = $this->makeBicycle();

        $this->postJson('/api/gps/location', [
            'bicycle_id' => $bike->id,
            'lat' => 14.6,
            'lng' => 120.99,
            'speed' => 12,
            'battery' => 75,
        ], $this->deviceHeaders())
            ->assertOk()
            ->assertJsonPath('message', 'Location recorded');

        $this->assertDatabaseHas('gps_logs', ['bicycleId' => $bike->id, 'lat' => 14.6]);
    }

    public function test_gps_location_triggers_geofence_breach(): void
    {
        config(['services.geofence' => ['center_lat' => 15.0, 'center_lng' => 121.0, 'default_radius' => 100, 'warning_threshold' => 50]]);

        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle();

        $this->postJson('/api/gps/location', [
            'bicycle_id' => $bike->id,
            'lat' => 14.0,
            'lng' => 120.0,
        ], $this->deviceHeaders())->assertOk();

        $this->assertDatabaseHas('geofence_breaches', ['bicycleId' => $bike->id]);
        $this->assertDatabaseHas('accidents', ['bicycleId' => $bike->id, 'type' => 'theft']);
    }

    public function test_gps_track_and_current_require_auth(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $bike = $this->makeBicycle();
        GpsLog::create(['bicycleId' => $bike->id, 'lat' => 14.6, 'lng' => 120.99, 'timestamp' => now()]);

        $this->getJson("/api/gps/bicycle/{$bike->id}/track")->assertOk()->assertJsonStructure(['bicycle_id', 'gps_logs', 'count']);
        $this->getJson("/api/gps/bicycle/{$bike->id}/current")->assertOk()->assertJsonPath('bicycle_id', $bike->id);
        $this->getJson('/api/gps/bicycle/99999/track')->assertStatus(404);
        $this->getJson('/api/gps/bicycle/99999/current')->assertStatus(404);
        $this->getJson('/api/gps/geofence')->assertOk();
    }

    public function test_gps_update_geofence_requires_admin(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider, ['*']);
        $this->postJson('/api/gps/geofence', [
            'center_lat' => 15.0, 'center_lng' => 121.0, 'radius' => 200,
        ])->assertForbidden();
    }

    public function test_gps_update_geofence_as_admin(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);

        $this->postJson('/api/gps/geofence', [
            'center_lat' => 15.0, 'center_lng' => 121.0, 'radius' => 200, 'alert_enabled' => true,
        ])->assertOk()->assertJsonPath('message', 'Geofence updated successfully');

        $this->assertDatabaseHas('geofences', ['isActive' => true]);
    }

    public function test_full_status_and_command_endpoints(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);

        $bike = $this->makeBicycle();

        $this->getJson("/api/iot/bicycle/{$bike->id}/status")->assertOk()->assertJsonPath('bicycle_id', $bike->id);
        $this->getJson('/api/iot/bicycle/99999/status')->assertStatus(404);

        $this->postJson("/api/iot/bicycle/{$bike->id}/command", ['command' => 'lock'])
            ->assertStatus(201)
            ->assertJsonPath('message', 'Command queued');

        $this->assertDatabaseHas('device_commands', ['bicycleId' => $bike->id, 'command' => 'lock']);
    }
}
