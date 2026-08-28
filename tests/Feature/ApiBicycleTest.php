<?php

namespace Tests\Feature;

use App\Models\Bicycle;
use App\Models\DeviceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class ApiBicycleTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_nearby_returns_available_bicycles_sorted_by_distance(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE, 'currentLat' => 14.6, 'currentLng' => 120.99]);
        $this->makeBicycle(['status' => Bicycle::STATUS_RENTED, 'currentLat' => 14.6, 'currentLng' => 120.99]);

        $response = $this->getJson('/api/bicycles/nearby?lat=14.6&lng=120.99&radius=1000');

        $response->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonStructure(['bicycles', 'count', 'radius']);
    }

    public function test_nearby_requires_valid_coordinates(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $this->getJson('/api/bicycles/nearby?lat=999&lng=120')->assertStatus(422);
    }

    public function test_index_with_filters(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE, 'model' => 'City']);
        $this->makeBicycle(['status' => Bicycle::STATUS_RENTED, 'model' => 'Mountain']);

        $this->getJson('/api/bicycles?status=available')->assertOk();
        $this->getJson('/api/bicycles?model=City&search=x&per_page=5')->assertOk();
    }

    public function test_show_found_and_not_found(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $bike = $this->makeBicycle();

        $this->getJson("/api/bicycles/{$bike->id}")->assertOk();
        $this->getJson('/api/bicycles/99999')->assertStatus(404);
    }

    public function test_store_creates_bicycle_as_admin(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);

        $this->postJson('/api/bicycles', [
            'name' => 'API Bike',
            'serialNumber' => 'API-SN-001',
            'model' => 'City',
            'hourlyRate' => 20,
            'currentLat' => 14.6,
            'currentLng' => 120.99,
        ])->assertStatus(201)->assertJsonPath('message', 'Bicycle created successfully');

        $this->assertDatabaseHas('bicycles', ['serialNumber' => 'API-SN-001', 'status' => 'available']);
    }

    public function test_update_bicycle_as_admin(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);

        $bike = $this->makeBicycle(['name' => 'Old']);

        $this->putJson("/api/bicycles/{$bike->id}", ['name' => 'New Name', 'hourlyRate' => 30])
            ->assertOk()
            ->assertJsonPath('message', 'Bicycle updated successfully');

        $this->assertSame('New Name', $bike->fresh()->name);
    }

    public function test_destroy_bicycle_as_admin(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);

        $bike = $this->makeBicycle();

        $this->deleteJson("/api/bicycles/{$bike->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Bicycle deleted successfully');

        $this->assertSoftDeleted('bicycles', ['id' => $bike->id]);
    }

    public function test_lock_queues_command_as_admin(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);

        $bike = $this->makeBicycle();

        $this->postJson("/api/bicycles/{$bike->id}/lock", ['locked' => true])
            ->assertOk()
            ->assertJsonPath('message', 'Lock command queued');

        $this->assertDatabaseHas('device_commands', ['bicycleId' => $bike->id, 'command' => 'lock', 'status' => 'pending']);
    }

    public function test_lock_requires_admin_role(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider, ['*']);

        $bike = $this->makeBicycle();

        $this->postJson("/api/bicycles/{$bike->id}/lock", ['locked' => true])->assertForbidden();
    }

    public function test_telemetry_endpoint(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $bike = $this->makeBicycle();
        DeviceStatus::create(['bicycleId' => $bike->id, 'type' => 'heartbeat', 'eventTimestamp' => now()]);

        $this->getJson("/api/bicycles/{$bike->id}/telemetry")->assertOk();
    }
}
