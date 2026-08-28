<?php

namespace Tests\Feature;

use App\Models\Bicycle;
use App\Models\DeviceStatus;
use App\Models\GpsLog;
use App\Models\MaintenanceRecord;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class AdminMaintenanceMonitoringGeofenceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_maintenance_index_active_and_history(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle();
        $this->makeMaintenanceRecord(['bicycleId' => $bike->id, 'status' => MaintenanceRecord::STATUS_SCHEDULED]);
        $this->makeMaintenanceRecord(['bicycleId' => $bike->id, 'status' => MaintenanceRecord::STATUS_COMPLETED]);

        $this->actingAs($admin)->get(route('admin.maintenance.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.maintenance.index', ['status' => 'completed']))->assertOk();
        $this->actingAs($admin)->get(route('admin.maintenance.index', ['status' => 'in_progress', 'bicycleId' => $bike->id, 'date_from' => '2026-01-01', 'date_to' => '2026-12-31']))->assertOk();
    }

    public function test_maintenance_store_creates_record_and_marks_bike_maintenance(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);

        $this->from(route('admin.maintenance.index'))
            ->actingAs($admin)
            ->post(route('admin.maintenance.store'), [
                'bicycleId' => $bike->id,
                'description' => 'Brake check',
                'type' => 'routine',
                'severity' => 'medium',
                'estimatedCost' => 100,
                'technician' => 'Tech A',
            ])
            ->assertRedirect(route('admin.maintenance.index'));

        $fresh = $bike->fresh();
        $this->assertSame(Bicycle::STATUS_MAINTENANCE, $fresh->status);
        $this->assertDatabaseHas('maintenance_records', ['bicycleId' => $bike->id, 'type' => 'routine']);
    }

    public function test_maintenance_store_blocks_duplicate_active_record(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);
        $this->makeMaintenanceRecord(['bicycleId' => $bike->id, 'status' => MaintenanceRecord::STATUS_IN_PROGRESS]);

        $this->from(route('admin.maintenance.index'))
            ->actingAs($admin)
            ->post(route('admin.maintenance.store'), [
                'bicycleId' => $bike->id,
                'description' => 'Again',
                'type' => 'repair',
            ])
            ->assertSessionHasErrors('bicycleId');
    }

    public function test_maintenance_update_status_to_completed(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);
        $record = $this->makeMaintenanceRecord(['bicycleId' => $bike->id, 'status' => MaintenanceRecord::STATUS_SCHEDULED]);

        $this->from(route('admin.maintenance.index'))
            ->actingAs($admin)
            ->put(route('admin.maintenance.update', $record->id), [
                'status' => MaintenanceRecord::STATUS_COMPLETED,
                'notes' => 'Done',
            ])
            ->assertRedirect(route('admin.maintenance.index'));

        $this->assertSame(MaintenanceRecord::STATUS_COMPLETED, $record->fresh()->status);
        $this->assertNotNull($record->fresh()->completedDate);
    }

    public function test_maintenance_update_status_blocked_after_completion(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle();
        $record = $this->makeMaintenanceRecord(['bicycleId' => $bike->id, 'status' => MaintenanceRecord::STATUS_COMPLETED]);

        $this->from(route('admin.maintenance.index'))
            ->actingAs($admin)
            ->post(route('admin.maintenance.updateStatus', $record->id), ['status' => 'in_progress'])
            ->assertSessionHasErrors('status');
    }

    public function test_monitoring_index_and_sections(): void
    {
        $admin = $this->makeAdmin();
        $this->makeBicycle(['currentLat' => 14.6, 'currentLng' => 120.99]);
        $bike2 = $this->makeBicycle(['currentLat' => null, 'currentLng' => null]);

        $this->actingAs($admin)->get(route('admin.monitoring.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.monitoring.index', ['section' => 'locks']))->assertOk();
        $this->actingAs($admin)->get(route('admin.monitoring.index', ['section' => 'invalid']))->assertOk();
    }

    public function test_bicycle_status_index_with_filters(): void
    {
        $admin = $this->makeAdmin();
        $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE, 'lockStatus' => Bicycle::LOCK_LOCKED, 'lastHeartbeat' => now()]);
        $this->makeBicycle(['status' => Bicycle::STATUS_RENTED, 'lockStatus' => Bicycle::LOCK_UNLOCKED, 'lastHeartbeat' => now()->subHours(2)]);

        $this->actingAs($admin)->get(route('admin.bicycles.status'))->assertOk();
        $this->actingAs($admin)->get(route('admin.bicycles.status', ['status' => 'rented', 'lock' => 'unlocked', 'connectivity' => 'offline', 'search' => 'x']))->assertOk();
    }

    public function test_monitoring_bicycle_status_returns_json(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle();
        GpsLog::create(['bicycleId' => $bike->id, 'lat' => 14.6, 'lng' => 120.99, 'timestamp' => now()]);

        $this->actingAs($admin)
            ->getJson(route('admin.monitoring.status', $bike->id))
            ->assertOk()
            ->assertJsonPath('bicycle.id', $bike->id);
    }

    public function test_monitoring_live_returns_json(): void
    {
        $admin = $this->makeAdmin();
        $this->makeBicycle(['currentLat' => 14.6, 'currentLng' => 120.99]);

        $this->actingAs($admin)
            ->getJson(route('admin.monitoring.live'))
            ->assertOk()
            ->assertJsonCount(1, 'bicycles');
    }

    public function test_geofence_index_renders(): void
    {
        $admin = $this->makeAdmin();
        $this->makeBicycle(['currentLat' => 14.6, 'currentLng' => 120.99]);
        $this->makeBicycle(['currentLat' => null, 'currentLng' => null]);

        $this->actingAs($admin)
            ->get(route('admin.geofence.index'))
            ->assertOk();
    }

    public function test_geofence_update_creates_active_geofence(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->putJson(route('admin.geofence.update'), [
                'centerLat' => 14.6,
                'centerLng' => 120.99,
                'radius' => 1000,
                'warningThreshold' => 200,
                'alertEnabled' => true,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Geofence updated successfully.');

        $this->assertDatabaseHas('geofences', ['isActive' => true, 'radius' => 1000]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'geofence_updated']);
    }

    public function test_geofence_update_validation_fails(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->putJson(route('admin.geofence.update'), ['centerLat' => 200, 'centerLng' => 0, 'radius' => 10])
            ->assertStatus(422);
    }
}
