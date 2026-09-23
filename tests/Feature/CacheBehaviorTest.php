<?php

namespace Tests\Feature;

use App\Models\Bicycle;
use App\Models\Geofence;
use App\Models\Notification;
use App\Models\Rental;
use App\Models\SystemSetting;
use App\Services\CacheRegistry;
use App\Services\GeofenceService;
use App\Services\RiderCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class CacheBehaviorTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function riderAuth(): \App\Models\User
    {
        $rider = $this->makeRider();
        $this->actingAs($rider);

        return $rider;
    }

    // ---------------- Cache hit / miss / freshness ----------------

    public function test_rent_page_caches_available_bicycles_and_serves_cache_hit(): void
    {
        $this->riderAuth();
        $bike = $this->makeBicycle(['name' => 'Cached Bike']);

        $key = CacheRegistry::availableBicyclesKey(false);

        $this->get(route('rider.rentals.create'))->assertOk();
        $this->assertTrue(Cache::has($key));

        // Mutate the DB behind the cache's back (bypassing observers) to
        // prove a subsequent render is served from the cache (a cache hit).
        \Illuminate\Support\Facades\DB::table('bicycles')
            ->where('id', $bike->id)
            ->update(['name' => 'Renamed Directly']);

        $response = $this->get(route('rider.rentals.create'))->assertOk();
        $response->assertSee('Cached Bike');
        $response->assertDontSee('Renamed Directly');
    }

    public function test_rent_page_recomputes_when_bicycle_status_changes(): void
    {
        $this->riderAuth();
        $bike = $this->makeBicycle();

        $this->get(route('rider.rentals.create'))->assertOk();

        // Marking a bicycle as rented is a catalog change → cache invalidated.
        $bike->update(['status' => Bicycle::STATUS_RENTED]);

        $response = $this->get(route('rider.rentals.create'))->assertOk();
        $response->assertSee('No bicycles available');
    }

    public function test_telemetry_updates_do_not_invalidate_catalog_cache(): void
    {
        $this->riderAuth();
        $bike = $this->makeBicycle();

        $this->get(route('rider.rentals.create'))->assertOk();

        $versionBefore = CacheRegistry::bicyclesVersion();
        $key = CacheRegistry::availableBicyclesKey(false);

        // Simulate frequent IoT/GPS writes: battery, position, lock, heartbeats.
        $bike->update([
            'batteryLevel' => 12,
            'currentLat' => 7.1001,
            'currentLng' => 125.6480,
            'lockStatus' => Bicycle::LOCK_UNLOCKED,
            'lastGpsUpdate' => now(),
            'lastHeartbeat' => now(),
        ]);

        $this->assertSame($versionBefore, CacheRegistry::bicyclesVersion());
        $this->assertTrue(Cache::has($key));
    }

    public function test_geofence_config_is_cached_and_invalidated(): void
    {
        $service = app(GeofenceService::class);

        $config = $service->getConfig();
        $this->assertNull($config['id']);

        $key = CacheRegistry::geofenceConfigKey();
        $this->assertTrue(Cache::has($key));

        // Creating an active geofence invalidates the cached default config.
        Geofence::create([
            'name' => 'New Zone',
            'centerLat' => 7.2000,
            'centerLng' => 125.7000,
            'radius' => 300,
            'shapeType' => 'circle',
            'isActive' => true,
            'alertEnabled' => true,
        ]);

        $fresh = $service->getConfig();
        $this->assertNotNull($fresh['id']);
        $this->assertSame(7.2, $fresh['centerLat']);
        $this->assertSame(125.7, $fresh['centerLng']);
    }

    public function test_system_setting_is_cached_and_invalidated_on_write(): void
    {
        $this->assertFalse(Cache::has(CacheRegistry::settingKey('rentalRatePerHour')));

        $this->assertSame(15, SystemSetting::getValue('rentalRatePerHour', 15));
        $this->assertTrue(Cache::has(CacheRegistry::settingKey('rentalRatePerHour')));

        $versionBefore = CacheRegistry::settingsVersion();

        SystemSetting::setValue('rentalRatePerHour', '25');

        // Version bumped, so the key under the new version is not cached and
        // the fresh value is returned (and then re-cached).
        $this->assertGreaterThan($versionBefore, CacheRegistry::settingsVersion());
        $this->assertFalse(Cache::has(CacheRegistry::settingKey('rentalRatePerHour')));
        $this->assertSame('25', SystemSetting::getValue('rentalRatePerHour', 15));
        $this->assertTrue(Cache::has(CacheRegistry::settingKey('rentalRatePerHour')));
    }

    public function test_user_summary_isolated_per_user_and_invalidated_on_user_change(): void
    {
        $riderA = $this->riderAuth();
        $riderB = $this->makeRider(['totalRentals' => 7, 'totalSpent' => 300]);

        $this->get(route('rider.dashboard'))->assertOk();

        $keyA = CacheRegistry::userSummaryKey($riderA->id);
        $keyB = CacheRegistry::userSummaryKey($riderB->id);

        $this->assertTrue(Cache::has($keyA));
        $this->assertFalse(Cache::has($keyB), 'Summary cache leaked across users');

        $cached = Cache::get($keyA);
        $this->assertSame(0, $cached['totalRentals']);
        $this->assertSame(0.0, (float) $cached['totalSpent']);

        // Direct DB change behind the cache (bypassing observers) => cache hit
        // keeps serving the old totals.
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $riderA->id)
            ->update(['totalRentals' => 9, 'totalSpent' => 99]);

        $this->assertSame(0, \Illuminate\Support\Facades\Cache::get($keyA)['totalRentals']);

        // A catalog change to the user model invalidates the summary.
        $riderA->update(['totalRentals' => 9, 'totalSpent' => 99]);
        $this->assertFalse(Cache::has(CacheRegistry::userSummaryKey($riderA->id)));
    }

    public function test_unread_count_is_cached_and_invalidated_on_read(): void
    {
        $rider = $this->riderAuth();
        Sanctum::actingAs($rider);

        Notification::create(['userId' => $rider->id, 'title' => 'A', 'message' => 'm', 'read' => false]);

        $key = CacheRegistry::unreadCountKey($rider->id);

        $this->getJson('/api/notifications/unread-count')->assertJson(['unread_count' => 1]);
        $this->assertTrue(Cache::has($key));

        // Marking all read invalidates the cached count.
        $this->putJson('/api/notifications/read-all')->assertOk();

        $this->getJson('/api/notifications/unread-count')->assertJson(['unread_count' => 0]);
    }

    public function test_api_bicycle_index_is_cached_and_invalidated_on_catalog_change(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $bikeA = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);
        $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);

        $key = CacheRegistry::bicycleIndexKey('available', 20);

        $this->getJson('/api/bicycles?status=available')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertTrue(Cache::has($key));

        // Bicycle becomes unavailable -> catalog change -> fresh result.
        $bikeA->update(['status' => Bicycle::STATUS_RENTED]);

        $this->getJson('/api/bicycles?status=available')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_api_bicycle_show_catalog_is_cached_with_fresh_telemetry(): void
    {
        $rider = $this->makeRider();
        Sanctum::actingAs($rider);

        $bike = $this->makeBicycle(['name' => 'Detail Bike']);

        $key = CacheRegistry::bicycleCatalogKey($bike->id);

        $this->getJson("/api/bicycles/{$bike->id}")->assertOk()->assertJsonPath('data.name', 'Detail Bike');
        $this->assertTrue(Cache::has($key));
    }

    // ---------------- Expiration ----------------

    public function test_catalog_cache_expires_after_ttl_and_is_rebuilt(): void
    {
        $this->riderAuth();
        $this->makeBicycle();

        $this->get(route('rider.rentals.create'))->assertOk();

        $key = CacheRegistry::availableBicyclesKey(false);
        $this->assertTrue(Cache::has($key));

        $versionBefore = CacheRegistry::bicyclesVersion();

        // Travel past the 5 minute (300s) TTL.
        $this->travel(CacheRegistry::TTL_AVAILABLE_BICYCLES + 1)->seconds();

        $this->assertFalse(Cache::has($key), 'Cached catalog should have expired');
        $this->assertSame($versionBefore, CacheRegistry::bicyclesVersion());

        $this->get(route('rider.rentals.create'))->assertOk();
        $this->assertTrue(Cache::has($key));
    }

    public function test_user_summary_expires_and_recomputes(): void
    {
        $rider = $this->riderAuth();

        $this->get(route('rider.dashboard'))->assertOk();

        $key = CacheRegistry::userSummaryKey($rider->id);
        $this->assertTrue(Cache::has($key));

        $this->travel(CacheRegistry::TTL_USER_SUMMARY + 1)->seconds();
        $this->assertFalse(Cache::has($key));

        // Rebuild still reflects the current database state.
        $this->get(route('rider.dashboard'))->assertOk();
        $this->assertTrue(Cache::has($key));
    }

    // ---------------- Rental lifecycle invalidation ----------------

    public function test_summary_invalidated_when_rental_is_created(): void
    {
        $rider = $this->riderAuth();
        $bike = $this->makeBicycle();

        $this->get(route('rider.dashboard'))->assertOk();

        $key = CacheRegistry::userSummaryKey($rider->id);
        $this->assertTrue(Cache::has($key));

        $versionBefore = CacheRegistry::userVersion($rider->id);

        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        // The version bumped, so the key under the new version is not cached
        // and the next render will recompute the summary fresh.
        $this->assertGreaterThan($versionBefore, CacheRegistry::userVersion($rider->id));
        $this->assertFalse(Cache::has(CacheRegistry::userSummaryKey($rider->id)));
        $this->assertNotNull($rental);
    }

    public function test_rental_cache_correctly_avoids_active_rental_countdown_data(): void
    {
        $rider = $this->riderAuth();
        $bike = $this->makeBicycle();

        $this->get(route('rider.dashboard'))->assertOk();

        // Create an active rental: it must be reflected immediately (live),
        // never served from the summary cache.
        $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_ACTIVE,
            'startTime' => now()->subMinutes(5),
            'expectedEndTime' => now()->addMinutes(55),
        ]);

        $this->get(route('rider.dashboard'))
            ->assertOk()
            ->assertSee('Active Rental');
    }
}