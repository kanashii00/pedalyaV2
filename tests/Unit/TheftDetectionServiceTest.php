<?php

namespace Tests\Unit;

use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\Geofence;
use App\Models\GeofenceBreach;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\IoTService;
use App\Services\NotificationService;
use App\Services\TheftDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class TheftDetectionServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    private NotificationService $notificationService;
    private TheftDetectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->notificationService = Mockery::mock(NotificationService::class);
        $this->service = new TheftDetectionService($this->notificationService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_process_location_outside_creates_theft_alert(): void
    {
        $bike = $this->makeBicycle(['currentRider' => null]);
        $admin = $this->makeAdmin();

        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $iotMock = Mockery::mock(IoTService::class);
        $iotMock->shouldReceive('sendCommand')->once();
        $this->app->instance(IoTService::class, $iotMock);

        $result = $this->service->processLocation($bike, 15.0, 121.0, [
            'inside' => false,
            'level' => 'breach',
            'distanceOutside' => 200.0,
            'distanceToBoundary' => 50.0,
        ]);

        $this->assertFalse($result['inside']);
        $this->assertSame('breach', $result['level']);
        $this->assertSame(200.0, $result['distance']);
        $this->assertNotNull($result['alert']);

        $alert = Accident::where('bicycleId', $bike->id)->where('type', 'theft')->first();
        $this->assertNotNull($alert);
    }

    public function test_process_location_inside_resolves_alert(): void
    {
        $bike = $this->makeBicycle();

        Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'theft',
            'severity' => 'moderate',
            'status' => TheftDetectionService::STATUS_OPEN,
            'acknowledged' => false,
        ]);

        GeofenceBreach::create([
            'bicycleId' => $bike->id,
            'lat' => 15.0,
            'lng' => 121.0,
            'distance' => 200,
            'acknowledged' => false,
            'resolvedAt' => null,
        ]);

        $result = $this->service->processLocation($bike, 14.6, 120.98, [
            'inside' => true,
            'level' => 'safe',
            'distanceOutside' => null,
            'distanceToBoundary' => 100.0,
        ]);

        $this->assertTrue($result['inside']);
        $this->assertNotNull($result['alert']);
        $this->assertSame(TheftDetectionService::STATUS_RETURNED, $result['alert']->status);

        $breach = GeofenceBreach::where('bicycleId', $bike->id)->first();
        $this->assertNotNull($breach->resolvedAt);
    }

    public function test_open_or_update_theft_alert_updates_existing(): void
    {
        $bike = $this->makeBicycle();

        $existing = Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'theft',
            'severity' => 'moderate',
            'status' => TheftDetectionService::STATUS_OPEN,
            'acknowledged' => false,
            'gpsLocation' => ['lat' => 14.5, 'lng' => 120.9],
            'breachDistance' => 100,
            'distanceFromBoundary' => 100,
        ]);

        $result = $this->service->openOrUpdateTheftAlert($bike, 14.7, 121.1, 250.0, [
            'inside' => false,
            'level' => 'breach',
        ]);

        $this->assertSame($existing->id, $result->id);
        $this->assertSame(250.0, (float) $result->breachDistance);
    }

    public function test_open_or_update_theft_alert_creates_new_when_none(): void
    {
        $bike = $this->makeBicycle(['currentRider' => null]);

        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $iotMock = Mockery::mock(IoTService::class);
        $iotMock->shouldReceive('sendCommand')->once();
        $this->app->instance(IoTService::class, $iotMock);

        $result = $this->service->openOrUpdateTheftAlert($bike, 14.6, 120.98, 150.0, [
            'inside' => false,
            'level' => 'breach',
        ]);

        $this->assertSame($bike->id, $result->bicycleId);
        $this->assertSame('theft', $result->type);
        $this->assertSame(TheftDetectionService::STATUS_OPEN, $result->status);
    }

    public function test_ensure_active_alert_for_outside_creates_when_missing(): void
    {
        $bike = $this->makeBicycle();

        $result = $this->service->ensureActiveAlertForOutside($bike, 14.7, 121.0, 300.0, [
            'inside' => false,
            'level' => 'breach',
        ]);

        $this->assertSame($bike->id, $result->bicycleId);
        $this->assertSame(TheftDetectionService::STATUS_OPEN, $result->status);
    }

    public function test_ensure_active_alert_for_outside_updates_existing(): void
    {
        $bike = $this->makeBicycle();

        $existing = Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'theft',
            'severity' => 'moderate',
            'status' => TheftDetectionService::STATUS_OPEN,
            'acknowledged' => false,
            'gpsLocation' => ['lat' => 14.5, 'lng' => 120.9],
            'breachDistance' => 100,
            'distanceFromBoundary' => 100,
        ]);

        $result = $this->service->ensureActiveAlertForOutside($bike, 14.8, 121.2, 400.0, [
            'inside' => false,
            'level' => 'breach',
        ]);

        $this->assertSame($existing->id, $result->id);
        $this->assertSame(400.0, (float) $result->breachDistance);
    }

    public function test_sync_breach_record_creates_new_when_none(): void
    {
        $bike = $this->makeBicycle();

        $geofence = Geofence::create([
            'name' => 'Test Zone',
            'centerLat' => 14.6,
            'centerLng' => 120.98,
            'radius' => 500,
            'shapeType' => 'circle',
            'isActive' => true,
            'alertEnabled' => true,
        ]);

        $method = new \ReflectionMethod(TheftDetectionService::class, 'syncBreachRecord');
        $method->invoke($this->service, $bike, 14.6, 120.98, 200.0, [
            'id' => $geofence->id,
            'geofenceId' => $geofence->id,
        ]);

        $breach = GeofenceBreach::where('bicycleId', $bike->id)->first();
        $this->assertNotNull($breach);
        $this->assertSame(200.0, (float) $breach->distance);
        $this->assertSame($geofence->id, $breach->geofenceId);
    }

    public function test_sync_breach_record_updates_existing(): void
    {
        $bike = $this->makeBicycle();

        GeofenceBreach::create([
            'bicycleId' => $bike->id,
            'lat' => 14.5,
            'lng' => 120.9,
            'distance' => 100,
            'acknowledged' => false,
            'resolvedAt' => null,
        ]);

        $method = new \ReflectionMethod(TheftDetectionService::class, 'syncBreachRecord');
        $method->invoke($this->service, $bike, 14.7, 121.0, 250.0, [
            'id' => null,
        ]);

        $breach = GeofenceBreach::where('bicycleId', $bike->id)->first();
        $this->assertSame(250.0, (float) $breach->distance);
    }

    public function test_sync_breach_record_handles_non_scalar_geofence_id(): void
    {
        $bike = $this->makeBicycle();

        $method = new \ReflectionMethod(TheftDetectionService::class, 'syncBreachRecord');
        $method->invoke($this->service, $bike, 14.6, 120.98, 200.0, [
            'id' => ['nested' => 99],
        ]);

        $breach = GeofenceBreach::where('bicycleId', $bike->id)->first();
        $this->assertNotNull($breach);
        $this->assertNull($breach->geofenceId);
    }

    public function test_auto_lock_on_theft_enabled(): void
    {
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);

        SystemSetting::create(['key' => 'auto_lock_on_theft', 'value' => 'true']);

        $iotMock = Mockery::mock(IoTService::class);
        $iotMock->shouldReceive('sendCommand')
            ->once()
            ->with($bike->id, 'lock', Mockery::type('array'));
        $this->app->instance(IoTService::class, $iotMock);

        $method = new \ReflectionMethod(TheftDetectionService::class, 'autoLockOnTheft');
        $method->invoke($this->service, $bike);

        $bike->refresh();
        $this->assertSame(Bicycle::LOCK_LOCKED, $bike->lockStatus);
    }

    public function test_auto_lock_on_theft_disabled(): void
    {
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED, 'lockStatus' => Bicycle::LOCK_UNLOCKED]);

        SystemSetting::create(['key' => 'auto_lock_on_theft', 'value' => 'false']);

        $method = new \ReflectionMethod(TheftDetectionService::class, 'autoLockOnTheft');
        $method->invoke($this->service, $bike);

        $bike->refresh();
        $this->assertSame(Bicycle::LOCK_UNLOCKED, $bike->lockStatus);
    }

    public function test_current_active_alert_returns_null_when_none(): void
    {
        $bike = $this->makeBicycle();

        $result = $this->service->currentActiveAlert($bike);
        $this->assertNull($result);
    }

    public function test_current_active_alert_returns_open_theft(): void
    {
        $bike = $this->makeBicycle();

        $alert = Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'theft',
            'severity' => 'moderate',
            'status' => TheftDetectionService::STATUS_OPEN,
            'acknowledged' => false,
        ]);

        $result = $this->service->currentActiveAlert($bike);
        $this->assertNotNull($result);
        $this->assertSame($alert->id, $result->id);
    }

    public function test_open_or_update_with_current_rider_notifies_rider(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['currentRider' => $rider->id]);

        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());
        $this->notificationService->shouldReceive('create')
            ->once()
            ->with($rider->id, 'Geofence Alert', Mockery::type('string'), 'geofence_alert');

        $iotMock = Mockery::mock(IoTService::class);
        $iotMock->shouldReceive('sendCommand')->once();
        $this->app->instance(IoTService::class, $iotMock);

        $result = $this->service->openOrUpdateTheftAlert($bike, 14.6, 120.98, 150.0, [
            'inside' => false,
            'level' => 'breach',
        ]);

        $this->assertNotNull($result);
    }
}
