<?php

namespace Tests\Unit;

use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\DeviceStatus;
use App\Models\SystemSetting;
use App\Services\DeviceCommandService;
use App\Services\GeofenceService;
use App\Services\IoTService;
use App\Services\NotificationService;
use App\Services\TheftDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class IoTServiceTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    private $notificationService;

    private $geofenceService;

    private IoTService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationService = Mockery::mock(NotificationService::class);
        $this->geofenceService = Mockery::mock(GeofenceService::class);

        $this->app->instance(NotificationService::class, $this->notificationService);
        $this->app->instance(GeofenceService::class, $this->geofenceService);

        $this->service = new IoTService(
            $this->notificationService,
            $this->geofenceService,
            new DeviceCommandService
        );
    }

    public function test_process_heartbeat_updates_bicycle_and_creates_status(): void
    {
        $bike = $this->makeBicycle(['batteryLevel' => 60]);

        $this->geofenceService->shouldReceive('checkPointInGeofence')
            ->once()
            ->andReturn(['inside' => true, 'distanceOutside' => 0]);

        $result = $this->service->processHeartbeat([
            'bicycleId' => $bike->id,
            'lat' => 14.6,
            'lng' => 120.99,
            'batteryLevel' => 85,
            'locked' => false,
            'speed' => 5,
            'firmware' => 'v1.2',
        ]);

        $bike->refresh();
        $this->assertSame(85, $bike->batteryLevel);
        $this->assertSame(Bicycle::LOCK_UNLOCKED, $bike->lockStatus);
        $this->assertSame(14.6, (float) $bike->currentLat);
        $this->assertNotNull($bike->lastHeartbeat);
        $this->assertSame(true, $result['received']);
        $this->assertCount(1, DeviceStatus::where('bicycleId', $bike->id)->get());
    }

    public function test_process_heartbeat_with_unknown_bicycle_still_returns_received(): void
    {
        $result = $this->service->processHeartbeat(['bicycleId' => 99999]);

        $this->assertTrue($result['received']);
    }

    public function test_process_heartbeat_detects_impact_and_creates_accident(): void
    {
        $this->makeAdmin();
        $bike = $this->makeBicycle();

        $this->geofenceService->shouldReceive('checkPointInGeofence')
            ->once()
            ->andReturn(['inside' => true, 'distanceOutside' => 0]);
        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $this->service->processHeartbeat([
            'bicycleId' => $bike->id,
            'impact' => 9.5,
            'lat' => 14.6,
            'lng' => 120.99,
        ]);

        $accident = Accident::where('type', 'impact_detected')->first();
        $this->assertNotNull($accident);
        $this->assertSame('critical', $accident->severity);
    }

    public function test_process_accident_report_creates_accident_and_marks_bike_maintenance(): void
    {
        $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);

        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $result = $this->service->processAccidentReport([
            'bicycleId' => $bike->id,
            'impact' => 6.0,
            'lat' => 14.6,
            'lng' => 120.99,
        ]);

        $this->assertSame('major', $result['severity']);
        $this->assertSame('reported', $result['status']);

        $bike->refresh();
        $this->assertSame(Bicycle::STATUS_MAINTENANCE, $bike->status);
        $this->assertNotNull(Accident::find($result['accidentId']));
    }

    public function test_process_accident_report_low_impact_is_minor(): void
    {
        $this->makeAdmin();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);

        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $result = $this->service->processAccidentReport([
            'bicycleId' => $bike->id,
            'impact' => 1.0,
        ]);

        $this->assertSame('minor', $result['severity']);
        $this->assertNotNull(Accident::find($result['accidentId']));
    }

    public function test_process_geofence_alert_notifies_rider_and_admins(): void
    {
        $this->makeAdmin();
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();

        $this->notificationService->shouldReceive('create')
            ->once()
            ->with($rider->id, 'Geofence Alert', Mockery::type('string'), 'geofence_alert');
        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $result = $this->service->processGeofenceAlert([
            'bicycleId' => $bike->id,
            'riderId' => $rider->id,
            'distance' => 150.5,
            'lat' => 14.6,
            'lng' => 120.99,
        ]);

        $this->assertSame('logged', $result['status']);
        $accident = Accident::where('type', 'theft')->first();
        $this->assertSame(150.5, (float) $accident->breachDistance);
    }

    public function test_get_device_status_returns_latest(): void
    {
        $bike = $this->makeBicycle();

        DeviceStatus::create([
            'bicycleId' => $bike->id,
            'type' => 'heartbeat',
            'eventTimestamp' => now()->subMinutes(5),
        ]);
        DeviceStatus::create([
            'bicycleId' => $bike->id,
            'type' => 'heartbeat',
            'eventTimestamp' => now(),
        ]);

        $status = $this->service->getDeviceStatus($bike->id);

        $this->assertNotNull($status);
        $this->assertSame($bike->id, $status->bicycleId);
    }

    public function test_get_device_status_returns_null_when_none(): void
    {
        $this->assertNull($this->service->getDeviceStatus(123));
    }

    public function test_send_command_creates_pending_command(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle();

        $command = $this->service->sendCommand($bike->id, 'lock', ['pin' => '1234'], $admin);

        $this->assertSame('lock', $command->command);
        $this->assertSame('pending', $command->status);
        $this->assertSame(['pin' => '1234'], $command->params);
        $this->assertSame($admin->id, $command->issuedBy);
        $this->assertCount(1, DeviceStatus::where('bicycleId', $bike->id)->where('type', 'command')->get());
    }

    public function test_acknowledge_command_lock_with_rental_keeps_rented(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental([
            'bicycleId' => $bike->id,
            'status' => 'active',
        ]);

        $bike->update(['currentRentalId' => $rental->id, 'status' => Bicycle::STATUS_RENTED]);

        $command = $this->service->sendCommand($bike->id, 'lock', [], $admin);
        $ok = $this->service->acknowledgeDeviceCommand($command->id, 'done', 'executed');

        $this->assertTrue($ok);
        $command->refresh();
        $this->assertSame('executed', $command->status);
        $this->assertNotNull($command->executedAt);

        $bike->refresh();
        $this->assertSame(Bicycle::STATUS_RENTED, $bike->status);
        $this->assertSame('locked', $bike->lockStatus);
    }

    public function test_acknowledge_command_unlock_marks_available_when_no_rental(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['lockStatus' => Bicycle::LOCK_LOCKED]);

        $command = $this->service->sendCommand($bike->id, 'unlock', [], $admin);
        $ok = $this->service->acknowledgeDeviceCommand($command->id, null, 'executed');

        $this->assertTrue($ok);
        $bike->refresh();
        $this->assertSame('unlocked', $bike->lockStatus);
        $this->assertSame(Bicycle::STATUS_AVAILABLE, $bike->status);
    }

    public function test_acknowledge_command_with_invalid_status_defaults_to_executed(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle();

        $command = $this->service->sendCommand($bike->id, 'lock', [], $admin);
        $ok = $this->service->acknowledgeDeviceCommand($command->id, null, 'invalid_status');

        $this->assertTrue($ok);
        $command->refresh();
        $this->assertSame('executed', $command->status);
    }

    public function test_acknowledge_command_failed_status_does_not_touch_bike(): void
    {
        $admin = $this->makeAdmin();
        $bike = $this->makeBicycle(['lockStatus' => Bicycle::LOCK_LOCKED]);

        $command = $this->service->sendCommand($bike->id, 'unlock', [], $admin);
        $ok = $this->service->acknowledgeDeviceCommand($command->id, 'error', 'failed');

        $this->assertTrue($ok);
        $command->refresh();
        $this->assertSame('failed', $command->status);
        $this->assertNull($command->executedAt);

        $bike->refresh();
        $this->assertSame(Bicycle::LOCK_LOCKED, $bike->lockStatus);
    }

    public function test_acknowledge_nonexistent_command_returns_false(): void
    {
        $this->assertFalse($this->service->acknowledgeDeviceCommand(99999));
    }

    public function test_get_pending_commands_returns_only_pending(): void
    {
        $bike = $this->makeBicycle();

        $pending = $this->service->sendCommand($bike->id, 'lock', []);
        DeviceCommand::create(['bicycleId' => $bike->id, 'command' => 'unlock', 'status' => 'executed']);

        $commands = $this->service->getPendingCommands($bike->id);

        $this->assertCount(1, $commands);
        $this->assertSame($pending->id, $commands[0]['id']);
        $this->assertSame('lock', $commands[0]['command']);
    }

    public function test_handle_geofence_check_outside_delegates_to_theft_service(): void
    {
        $this->makeAdmin();
        $bike = $this->makeBicycle();

        $this->geofenceService->shouldReceive('checkPointInGeofence')
            ->once()
            ->andReturn([
                'inside' => false,
                'level' => 'breach',
                'distanceOutside' => 200.0,
                'distanceToBoundary' => 50.0,
            ]);

        $theftMock = Mockery::mock(TheftDetectionService::class);
        $theftMock->shouldReceive('openOrUpdateTheftAlert')
            ->once()
            ->andReturn(Accident::create([
                'bicycleId' => $bike->id,
                'type' => 'theft',
                'status' => 'open',
                'severity' => 'moderate',
                'acknowledged' => false,
            ]));
        $this->app->instance(TheftDetectionService::class, $theftMock);

        $method = new \ReflectionMethod(IoTService::class, 'handleGeofenceCheck');
        $method->invoke($this->service, $bike, 14.6, 121.0);

        $this->assertTrue(true);
    }

    public function test_handle_geofence_check_warning_level_records_warning(): void
    {
        $this->makeAdmin();
        $bike = $this->makeBicycle();

        $this->geofenceService->shouldReceive('checkPointInGeofence')
            ->once()
            ->andReturn([
                'inside' => true,
                'level' => 'approaching',
                'distanceToBoundary' => 25.0,
            ]);

        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $method = new \ReflectionMethod(IoTService::class, 'handleGeofenceCheck');
        $method->invoke($this->service, $bike, 14.6, 121.0);

        $alert = Accident::where('bicycleId', $bike->id)
            ->where('type', 'geofence_alert')
            ->first();
        $this->assertNotNull($alert);
        $this->assertSame('minor', $alert->severity);
    }

    public function test_handle_geofence_check_approaching_records_warning(): void
    {
        $this->makeAdmin();
        $bike = $this->makeBicycle();

        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $geofenceService = Mockery::mock(GeofenceService::class);
        $geofenceService->shouldReceive('checkPointInGeofence')
            ->once()
            ->andReturn([
                'inside' => true,
                'level' => 'approaching',
                'distanceToBoundary' => 25.0,
            ]);
        $this->app->instance(GeofenceService::class, $geofenceService);

        $method = new \ReflectionMethod(IoTService::class, 'handleGeofenceCheck');
        $method->invoke($this->service, $bike, 14.6, 121.0);

        $alert = Accident::where('bicycleId', $bike->id)
            ->where('type', 'geofence_alert')
            ->first();
        $this->assertNotNull($alert);
    }

    public function test_handle_geofence_check_safe_resolves_alert(): void
    {
        $bike = $this->makeBicycle();

        Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'theft',
            'status' => TheftDetectionService::STATUS_OPEN,
            'severity' => 'moderate',
            'acknowledged' => false,
        ]);

        $theftMock = Mockery::mock(TheftDetectionService::class);
        $theftMock->shouldReceive('resolveAlertOnReturn')
            ->once()
            ->andReturn(Accident::where('bicycleId', $bike->id)->first());
        $this->app->instance(TheftDetectionService::class, $theftMock);

        $geofenceService = Mockery::mock(GeofenceService::class);
        $geofenceService->shouldReceive('checkPointInGeofence')
            ->once()
            ->andReturn([
                'inside' => true,
                'level' => 'safe',
                'distanceToBoundary' => 200.0,
            ]);
        $this->app->instance(GeofenceService::class, $geofenceService);

        $method = new \ReflectionMethod(IoTService::class, 'handleGeofenceCheck');
        $method->invoke($this->service, $bike, 14.6, 121.0);

        $this->assertTrue(true);
    }

    public function test_record_warning_event_throttle_prevents_duplicate(): void
    {
        $this->makeAdmin();
        $bike = $this->makeBicycle();

        $accident = Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'geofence_alert',
            'severity' => 'minor',
            'warningLevel' => 'approaching',
            'status' => 'open',
            'acknowledged' => false,
        ]);
        $accident->forceFill(['created_at' => now()->subMinutes(5)])->save();

        $method = new \ReflectionMethod(IoTService::class, 'recordWarningEvent');
        $method->invoke($this->service, $bike, 14.6, 121.0, [
            'level' => 'approaching',
            'distanceToBoundary' => 30.0,
        ]);

        $count = Accident::where('bicycleId', $bike->id)
            ->where('type', 'geofence_alert')
            ->count();
        $this->assertSame(1, $count);
    }

    public function test_record_warning_event_creates_after_throttle(): void
    {
        $this->makeAdmin();
        $bike = $this->makeBicycle();

        $old = Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'geofence_alert',
            'severity' => 'minor',
            'warningLevel' => 'approaching',
            'status' => 'open',
            'acknowledged' => false,
        ]);
        $old->forceFill(['created_at' => now()->subMinutes(20)])->save();

        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $method = new \ReflectionMethod(IoTService::class, 'recordWarningEvent');
        $method->invoke($this->service, $bike, 14.6, 121.0, [
            'level' => 'approaching',
            'distanceToBoundary' => 25.0,
        ]);

        $count = Accident::where('bicycleId', $bike->id)
            ->where('type', 'geofence_alert')
            ->count();
        $this->assertSame(2, $count);
    }

    public function test_process_heartbeat_low_impact_below_threshold(): void
    {
        $bike = $this->makeBicycle();

        $this->geofenceService->shouldReceive('checkPointInGeofence')
            ->once()
            ->andReturn(['inside' => true, 'distanceOutside' => 0]);

        $this->service->processHeartbeat([
            'bicycleId' => $bike->id,
            'impact' => 1.5,
            'lat' => 14.6,
            'lng' => 120.99,
        ]);

        $this->assertDatabaseMissing('accidents', ['bicycleId' => $bike->id, 'type' => 'impact_detected']);
    }

    public function test_process_heartbeat_medium_impact(): void
    {
        $bike = $this->makeBicycle();

        $this->geofenceService->shouldReceive('checkPointInGeofence')
            ->once()
            ->andReturn(['inside' => true, 'distanceOutside' => 0]);

        $this->service->processHeartbeat([
            'bicycleId' => $bike->id,
            'impact' => 5.0,
            'lat' => 14.6,
            'lng' => 120.99,
        ]);

        $accident = Accident::where('bicycleId', $bike->id)->where('type', 'impact_detected')->first();
        $this->assertNotNull($accident);
        $this->assertSame('major', $accident->severity);
    }

    public function test_process_geofence_alert_existing_alert_updates(): void
    {
        $this->makeAdmin();
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();

        Accident::create([
            'bicycleId' => $bike->id,
            'type' => 'theft',
            'status' => 'open',
            'severity' => 'moderate',
            'acknowledged' => false,
            'breachDistance' => 100,
            'gpsLocation' => ['lat' => 14.5, 'lng' => 120.9],
        ]);

        $this->notificationService->shouldReceive('create')
            ->once()
            ->with($rider->id, 'Geofence Alert', Mockery::type('string'), 'geofence_alert');
        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $result = $this->service->processGeofenceAlert([
            'bicycleId' => $bike->id,
            'riderId' => $rider->id,
            'distance' => 200.0,
            'lat' => 14.7,
            'lng' => 121.0,
        ]);

        $this->assertSame('logged', $result['status']);
        $updated = Accident::find($result['alertId']);
        $this->assertSame(200.0, (float) $updated->breachDistance);
    }

    public function test_process_geofence_alert_without_rider(): void
    {
        $this->makeAdmin();
        $bike = $this->makeBicycle();

        $this->notificationService->shouldReceive('createForUsers')
            ->once()
            ->andReturn(collect());

        $result = $this->service->processGeofenceAlert([
            'bicycleId' => $bike->id,
            'distance' => 150.0,
            'lat' => 14.6,
            'lng' => 121.0,
        ]);

        $this->assertSame('logged', $result['status']);
    }
}
