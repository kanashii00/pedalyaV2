<?php

namespace Tests\Unit;

use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\Notification;
use App\Models\Rental;
use App\Models\User;
use App\Services\IoTService;
use App\Services\NotificationService;
use App\Services\RentalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class RentalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_start_rental_sets_current_rental_id(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')
            ->once()
            ->andReturn(new Notification());

        $iotService = Mockery::mock(IoTService::class);
        $iotService->shouldReceive('sendCommand')
            ->once()
            ->andReturn(new DeviceCommand());

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Rider One',
            'email' => 'rider1@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike 1',
            'model' => 'City',
            'serialNumber' => 'BIKE-001',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $rental = $service->startRental($user, $bicycle->id, 60, 'cash');

        $this->assertSame(Rental::STATUS_ACTIVE, $rental->status);
        $this->assertEquals($rental->id, Bicycle::findOrFail($bicycle->id)->currentRentalId);
        $this->assertSame(Bicycle::STATUS_RENTED, Bicycle::findOrFail($bicycle->id)->status);
    }

    public function test_return_rental_two_phase_marks_awaiting_then_returned(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')
            ->andReturn(new Notification());

        $iotService = Mockery::mock(IoTService::class);
        $iotService->shouldReceive('sendCommand')
            ->andReturn(new DeviceCommand());

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Rider Two',
            'email' => 'rider2@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike 2',
            'model' => 'City',
            'serialNumber' => 'BIKE-002',
            'status' => Bicycle::STATUS_RENTED,
            'hourlyRate' => 20,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 88,
            'lockStatus' => Bicycle::LOCK_UNLOCKED,
            'currentRider' => $user->id,
            'currentRentalId' => 123,
            'totalRentals' => 4,
        ]);

        $rental = Rental::create([
            'rentalId' => 'REN-20260828-TEST',
            'bicycleId' => $bicycle->id,
            'bicycleName' => $bicycle->name,
            'bicycleSerial' => $bicycle->serialNumber,
            'riderId' => $user->id,
            'riderName' => $user->name,
            'riderEmail' => $user->email,
            'status' => Rental::STATUS_ACTIVE,
            'startTime' => now()->subHours(2),
            'expectedEndTime' => now()->subHour(),
            'ratePerHour' => 20,
            'totalFee' => 0,
            'durationMinutes' => 120,
            'durationFormatted' => '2h 0m',
            'chargedHours' => 2,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $bicycle->update([
            'currentRentalId' => $rental->id,
        ]);

        // Phase 1: rider/admin ends the ride -> awaiting_return, bicycle held.
        $ended = $service->markRideEnded($rental, $user, 14.6000, 120.9850);

        $awaitingRental = $ended['rental'];
        $this->assertSame(Rental::STATUS_AWAITING_RETURN, $awaitingRental->status);
        $this->assertSame(Bicycle::STATUS_RENTED, Bicycle::findOrFail($bicycle->id)->status);

        // Phase 2: administrator confirms the return -> returned, bicycle released.
        $returned = $service->processReturn($rental, $user, [
            'returnTime' => now()->toDateTimeString(),
            'condition' => Rental::CONDITION_GOOD,
            'note' => 'Returned at dock',
        ]);

        $freshBicycle = Bicycle::findOrFail($bicycle->id);
        $freshRental = Rental::findOrFail($rental->id);

        $this->assertSame(Rental::STATUS_RETURNED, $freshRental->status);
        $this->assertSame(Bicycle::STATUS_AVAILABLE, $freshBicycle->status);
        $this->assertNull($freshBicycle->currentRentalId);
        $this->assertNull($freshBicycle->currentRider);
        $this->assertSame($rental->id, $returned['rental']->id);
        $this->assertSame(Rental::CONDITION_GOOD, $freshRental->returnCondition);
        $this->assertSame('paid', $freshRental->paymentStatus);
        $this->assertNotNull(\App\Models\Payment::where('rentalId', $rental->id)->first());
    }
}
