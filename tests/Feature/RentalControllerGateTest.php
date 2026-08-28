<?php

namespace Tests\Feature;

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
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class RentalControllerGateTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_admin_rental_routes_update_bicycle_current_rental_id_consistently(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
        ]);

        $rider = User::create([
            'name' => 'Rider User',
            'email' => 'rider@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike A',
            'model' => 'City',
            'serialNumber' => 'BIKE-A',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 95,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $rental = Rental::create([
            'rentalId' => 'REN-20260828-ABCD',
            'bicycleId' => $bicycle->id,
            'bicycleName' => $bicycle->name,
            'bicycleSerial' => $bicycle->serialNumber,
            'riderId' => $rider->id,
            'riderName' => $rider->name,
            'riderEmail' => $rider->email,
            'status' => Rental::STATUS_PENDING,
            'startTime' => now(),
            'expectedEndTime' => now()->addHour(),
            'ratePerHour' => 15,
            'totalFee' => 15,
            'durationMinutes' => 60,
            'durationFormatted' => '1h 0m',
            'chargedHours' => 1,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'pending',
        ]);

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')->once()->andReturn(new Notification());

        $iotService = Mockery::mock(IoTService::class);
        $iotService->shouldReceive('sendCommand')
            ->twice()
            ->andReturn(new DeviceCommand(), new DeviceCommand());

        $rentalService = Mockery::mock(RentalService::class);
        $rentalService->shouldIgnoreMissing();

        $this->app->instance(NotificationService::class, $notificationService);
        $this->app->instance(IoTService::class, $iotService);
        $this->app->instance(RentalService::class, $rentalService);

        $this->actingAs($admin);

        $this->put(route('admin.rentals.approve', $rental->id))
            ->assertRedirect();

        $this->assertSame($rental->id, (int) Bicycle::findOrFail($bicycle->id)->currentRentalId);
        $this->assertSame(Bicycle::STATUS_RENTED, Bicycle::findOrFail($bicycle->id)->status);

        $this->put(route('admin.rentals.cancel', $rental->id))
            ->assertRedirect();

        $freshBicycle = Bicycle::findOrFail($bicycle->id);
        $this->assertNull($freshBicycle->currentRentalId);
        $this->assertSame(Bicycle::STATUS_AVAILABLE, $freshBicycle->status);
    }

    public function test_api_rental_routes_update_bicycle_current_rental_id_consistently(): void
    {
        $admin = User::create([
            'name' => 'Api Admin',
            'email' => 'api-admin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
        ]);

        $rider = User::create([
            'name' => 'Api Rider',
            'email' => 'api-rider@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike B',
            'model' => 'City',
            'serialNumber' => 'BIKE-B',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 95,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $rental = Rental::create([
            'rentalId' => 'REN-20260828-EFGH',
            'bicycleId' => $bicycle->id,
            'bicycleName' => $bicycle->name,
            'bicycleSerial' => $bicycle->serialNumber,
            'riderId' => $rider->id,
            'riderName' => $rider->name,
            'riderEmail' => $rider->email,
            'status' => Rental::STATUS_PENDING,
            'startTime' => now(),
            'expectedEndTime' => now()->addHour(),
            'ratePerHour' => 15,
            'totalFee' => 15,
            'durationMinutes' => 60,
            'durationFormatted' => '1h 0m',
            'chargedHours' => 1,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'pending',
        ]);

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldIgnoreMissing();

        $iotService = Mockery::mock(IoTService::class);
        $iotService->shouldReceive('sendCommand')
            ->twice()
            ->andReturn(new DeviceCommand(), new DeviceCommand());

        $rentalService = Mockery::mock(RentalService::class);
        $rentalService->shouldIgnoreMissing();

        $this->app->instance(NotificationService::class, $notificationService);
        $this->app->instance(IoTService::class, $iotService);
        $this->app->instance(RentalService::class, $rentalService);

        Sanctum::actingAs($admin, ['*']);

        $this->putJson("/api/rentals/{$rental->id}/approve")
            ->assertOk();

        $this->assertSame($rental->id, (int) Bicycle::findOrFail($bicycle->id)->currentRentalId);
        $this->assertSame(Bicycle::STATUS_RENTED, Bicycle::findOrFail($bicycle->id)->status);

        $this->putJson("/api/rentals/{$rental->id}/cancel")
            ->assertOk();

        $freshBicycle = Bicycle::findOrFail($bicycle->id);
        $this->assertNull($freshBicycle->currentRentalId);
        $this->assertSame(Bicycle::STATUS_AVAILABLE, $freshBicycle->status);
    }
}
