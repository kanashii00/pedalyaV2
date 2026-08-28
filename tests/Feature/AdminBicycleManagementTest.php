<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\DeviceStatus;
use App\Models\MaintenanceRecord;
use App\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class AdminBicycleManagementTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_index_with_filters(): void
    {
        $admin = $this->makeAdmin();
        $this->makeBicycle(['name' => 'Alpha', 'status' => Bicycle::STATUS_AVAILABLE, 'batteryLevel' => 40]);
        $this->makeBicycle(['name' => 'Beta', 'status' => Bicycle::STATUS_RENTED, 'batteryLevel' => 90]);

        $this->actingAs($admin)->get(route('admin.bicycles.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.bicycles.index', ['status' => 'rented', 'battery' => 50, 'search' => 'Beta']))->assertOk();
    }

    public function test_show_and_create_pages(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle();

        $this->actingAs($admin)->get(route('admin.bicycles.show', $bike->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.bicycles.create'))->assertOk();
    }

    public function test_store_creates_bicycle_and_audit_log(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.bicycles.store'), [
                'name' => 'New Bike',
                'model' => 'Mountain',
                'hourlyRate' => 25,
                'currentLat' => 14.6,
                'currentLng' => 120.99,
            ])
            ->assertRedirect(route('admin.bicycles.index'));

        $this->assertDatabaseHas('bicycles', ['name' => 'New Bike', 'status' => Bicycle::STATUS_AVAILABLE]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'bicycle_created']);
    }

    public function test_store_validation_fails_without_name(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.bicycles.store'), ['hourlyRate' => 10])
            ->assertSessionHasErrors('name');
    }

    public function test_update_fields(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['batteryLevel' => 50, 'hourlyRate' => 15]);

        $this->from(route('admin.bicycles.index'))
            ->actingAs($admin)
            ->put(route('admin.bicycles.update', $bike->id), [
                'name' => 'Renamed',
                'hourlyRate' => 20,
                'batteryLevel' => 80,
            ])
            ->assertRedirect(route('admin.bicycles.index'));

        $fresh = $bike->fresh();
        $this->assertSame('Renamed', $fresh->name);
        $this->assertSame(20.0, (float) $fresh->hourlyRate);
        $this->assertSame(80, (int) $fresh->batteryLevel);
    }

    public function test_update_to_maintenance_places_bicycle_in_maintenance(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);

        $this->from(route('admin.bicycles.index'))
            ->actingAs($admin)
            ->put(route('admin.bicycles.update', $bike->id), ['status' => 'maintenance'])
            ->assertRedirect(route('admin.bicycles.index'));

        $fresh = $bike->fresh();
        $this->assertSame(Bicycle::STATUS_MAINTENANCE, $fresh->status);
        $this->assertSame(Bicycle::LOCK_LOCKED, $fresh->lockStatus);
        $this->assertDatabaseHas('maintenance_records', ['bicycleId' => $bike->id]);
    }

    public function test_update_rented_to_maintenance_is_blocked(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);

        $this->from(route('admin.bicycles.index'))
            ->actingAs($admin)
            ->put(route('admin.bicycles.update', $bike->id), ['status' => 'maintenance'])
            ->assertSessionHasErrors('status');

        $this->assertSame(Bicycle::STATUS_RENTED, $bike->fresh()->status);
    }

    public function test_update_from_maintenance_to_available_blocked_when_active_records(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);
        $this->makeMaintenanceRecord([
            'bicycleId' => $bike->id,
            'status' => MaintenanceRecord::STATUS_IN_PROGRESS,
        ]);

        $this->from(route('admin.bicycles.index'))
            ->actingAs($admin)
            ->put(route('admin.bicycles.update', $bike->id), ['status' => 'available'])
            ->assertSessionHasErrors('status');
    }

    public function test_destroy_removed_available_bicycle(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);

        $this->from(route('admin.bicycles.index'))
            ->actingAs($admin)
            ->delete(route('admin.bicycles.destroy', $bike->id))
            ->assertRedirect(route('admin.bicycles.index'));

        $fresh = $bike->fresh();
        $this->assertSame(Bicycle::STATUS_REMOVED, $fresh->status);
        $this->assertNotNull($fresh->removedAt);
    }

    public function test_destroy_blocked_while_in_maintenance(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);

        $this->from(route('admin.bicycles.index'))
            ->actingAs($admin)
            ->delete(route('admin.bicycles.destroy', $bike->id))
            ->assertSessionHasErrors('bicycle');

        $this->assertSame(Bicycle::STATUS_MAINTENANCE, $bike->fresh()->status);
    }

    public function test_destroy_blocked_with_active_rental(): void
    {
        $admin = $this->makeAdmin();
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_ACTIVE]);

        $this->from(route('admin.bicycles.index'))
            ->actingAs($admin)
            ->delete(route('admin.bicycles.destroy', $bike->id))
            ->assertSessionHasErrors('bicycle');
    }

    public function test_lock_sends_remote_command(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);

        $this->from(route('admin.bicycles.index'))
            ->actingAs($admin)
            ->post(route('admin.bicycles.lock', $bike->id), ['action' => 'unlock'])
            ->assertRedirect(route('admin.bicycles.index'));

        $this->assertDatabaseHas('device_commands', ['bicycleId' => $bike->id, 'command' => 'unlock', 'status' => 'pending']);
    }

    public function test_lock_blocked_when_rented(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);

        $this->from(route('admin.bicycles.index'))
            ->actingAs($admin)
            ->post(route('admin.bicycles.lock', $bike->id), ['action' => 'lock'])
            ->assertSessionHasErrors('bicycle');
    }

    public function test_telemetry_returns_json(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle();

        DeviceStatus::create([
            'bicycleId' => $bike->id,
            'type' => 'heartbeat',
            'eventTimestamp' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.bicycles.telemetry', $bike->id))
            ->assertOk()
            ->assertJsonFragment(['bicycleId' => $bike->id]);
    }
}
