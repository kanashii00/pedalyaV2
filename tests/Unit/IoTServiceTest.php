<?php

namespace Tests\Unit;

use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\DeviceStatus;
use App\Models\User;
use App\Services\GeofenceService;
use App\Services\IoTService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class IoTServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

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

        $this->service = new IoTService($this->notificationService, $this->geofenceService);
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
        $accident = Accident::where('type', 'geofence_breach')->first();
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
}
