<?php

namespace Tests\Unit;

use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use App\Services\IoTService;
use App\Services\NotificationService;
use App\Services\RentalService;
use Carbon\Carbon;
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

    public function test_start_rental_unverified_user_throws(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Unverified',
            'email' => 'unverified@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => false,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-UV-001',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $this->expectException(\App\Exceptions\RentalException::class);
        $service->startRental($user, $bicycle->id, 60, 'cash');
    }

    public function test_start_rental_active_rental_exists_throws(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Has Active',
            'email' => 'hasactive@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-ACT-001',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        Rental::create([
            'rentalId' => 'REN-ACTIVE-001',
            'riderId' => $user->id,
            'bicycleId' => $bicycle->id,
            'status' => Rental::STATUS_ACTIVE,
            'startTime' => now(),
            'ratePerHour' => 15,
            'totalFee' => 15,
            'durationMinutes' => 60,
            'durationFormatted' => '1h 0m',
            'chargedHours' => 1,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $this->expectException(\App\Exceptions\RentalException::class);
        $service->startRental($user, $bicycle->id, 60, 'cash');
    }

    public function test_start_rental_unavailable_bicycle_throws(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-UNA-001',
            'status' => Bicycle::STATUS_RENTED,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $this->expectException(\App\Exceptions\RentalException::class);
        $service->startRental($user, $bicycle->id, 60, 'cash');
    }

    public function test_start_rental_low_battery_throws(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'User',
            'email' => 'userlow@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-LOW-001',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 10,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $this->expectException(\App\Exceptions\RentalException::class);
        $service->startRental($user, $bicycle->id, 60, 'cash');
    }

    public function test_start_rental_gcash_creates_pending_payment(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')->andReturn(new Notification());

        $iotService = Mockery::mock(IoTService::class);
        $iotService->shouldReceive('sendCommand')->zeroOrMoreTimes();

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'GCash User',
            'email' => 'gcash@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-GC-001',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 20,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $rental = $service->startRental($user, $bicycle->id, 60, 'gcash');

        $this->assertSame('pending_verification', $rental->paymentStatus);
        $this->assertDatabaseHas('payments', [
            'rentalId' => $rental->id,
            'paymentMethod' => 'gcash',
            'status' => 'pending',
        ]);
    }

    public function test_approve_rental_success(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')->once()->andReturn(new Notification());

        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Approve User',
            'email' => 'approve@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $admin = User::create([
            'name' => 'Approve Admin',
            'email' => 'approveadmin@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-APPR-001',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $rental = Rental::create([
            'rentalId' => 'REN-APPR-001',
            'riderId' => $user->id,
            'bicycleId' => $bicycle->id,
            'status' => Rental::STATUS_PENDING,
            'startTime' => now(),
            'ratePerHour' => 15,
            'totalFee' => 15,
            'durationMinutes' => 60,
            'durationFormatted' => '1h 0m',
            'chargedHours' => 1,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $result = $service->approveRental($rental, $admin);

        $this->assertSame(Rental::STATUS_ACTIVE, $result->status);
        $this->assertSame($admin->id, $result->approvedBy);
    }

    public function test_approve_rental_non_pending_throws(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Rider',
            'email' => 'rider-appr@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin2@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-APPR-002',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $rental = Rental::create([
            'rentalId' => 'REN-APPR-002',
            'riderId' => $user->id,
            'bicycleId' => $bicycle->id,
            'status' => Rental::STATUS_ACTIVE,
            'startTime' => now(),
            'ratePerHour' => 15,
            'totalFee' => 15,
            'durationMinutes' => 60,
            'durationFormatted' => '1h 0m',
            'chargedHours' => 1,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $this->expectException(\App\Exceptions\RentalException::class);
        $service->approveRental($rental, $admin);
    }

    public function test_cancel_rental_success(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')->once()->andReturn(new Notification());

        $iotService = Mockery::mock(IoTService::class);
        $iotService->shouldReceive('sendCommand')->once()->andReturn(new DeviceCommand());

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Cancel User',
            'email' => 'cancel@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-CANC-001',
            'status' => Bicycle::STATUS_RENTED,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_UNLOCKED,
            'currentRider' => $user->id,
            'totalRentals' => 1,
        ]);

        $rental = Rental::create([
            'rentalId' => 'REN-CANC-001',
            'riderId' => $user->id,
            'bicycleId' => $bicycle->id,
            'status' => Rental::STATUS_ACTIVE,
            'startTime' => now()->subHour(),
            'ratePerHour' => 15,
            'totalFee' => 15,
            'durationMinutes' => 60,
            'durationFormatted' => '1h 0m',
            'chargedHours' => 1,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $result = $service->cancelRental($rental, $user, 'Test cancel');

        $this->assertSame(Rental::STATUS_CANCELLED, $result->status);
        $this->assertSame('Test cancel', $result->cancelReason);
        $this->assertSame(Bicycle::STATUS_AVAILABLE, $bicycle->fresh()->status);
    }

    public function test_cancel_rental_non_cancellable_throws(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'User',
            'email' => 'usercancel@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-CANC-002',
            'status' => Bicycle::STATUS_RENTED,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_UNLOCKED,
            'totalRentals' => 1,
        ]);

        $rental = Rental::create([
            'rentalId' => 'REN-CANC-002',
            'riderId' => $user->id,
            'bicycleId' => $bicycle->id,
            'status' => Rental::STATUS_COMPLETED,
            'startTime' => now()->subHours(2),
            'ratePerHour' => 15,
            'totalFee' => 30,
            'durationMinutes' => 120,
            'durationFormatted' => '2h 0m',
            'chargedHours' => 2,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $this->expectException(\App\Exceptions\RentalException::class);
        $service->cancelRental($rental, $user);
    }

    public function test_mark_expired_rentals_overdue(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Overdue User',
            'email' => 'overdue@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-OVRD-001',
            'status' => Bicycle::STATUS_RENTED,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_UNLOCKED,
            'totalRentals' => 1,
        ]);

        Rental::create([
            'rentalId' => 'REN-OVRD-001',
            'riderId' => $user->id,
            'bicycleId' => $bicycle->id,
            'status' => Rental::STATUS_ACTIVE,
            'startTime' => now()->subHours(3),
            'expectedEndTime' => now()->subHour(),
            'ratePerHour' => 15,
            'totalFee' => 45,
            'durationMinutes' => 180,
            'durationFormatted' => '3h 0m',
            'chargedHours' => 3,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $count = $service->markExpiredRentalsOverdue();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('rentals', [
            'rentalId' => 'REN-OVRD-001',
            'status' => Rental::STATUS_OVERDUE,
        ]);
    }

    public function test_calculate_fees(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $result = $service->calculateFees(
            now()->subHours(2)->toDateTimeString(),
            now()->toDateTimeString(),
            15.00
        );

        $this->assertArrayHasKey('totalFee', $result);
        $this->assertArrayHasKey('durationMinutes', $result);
        $this->assertArrayHasKey('durationFormatted', $result);
        $this->assertArrayHasKey('chargedHours', $result);
        $this->assertSame(15.00, $result['ratePerHour']);
        $this->assertGreaterThanOrEqual(2, $result['chargedHours']);
    }

    public function test_calculate_fees_with_null_end_time(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $result = $service->calculateFees(
            now()->subHour()->toDateTimeString(),
            null,
            20.00
        );

        $this->assertArrayHasKey('totalFee', $result);
        $this->assertGreaterThan(0, $result['totalFee']);
    }

    public function test_create_rental_from_paid_payment(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);

        $iotService = Mockery::mock(IoTService::class);
        $iotService->shouldReceive('sendCommand')->zeroOrMoreTimes()->andReturn(new DeviceCommand());

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'GCash Rider',
            'email' => 'gcashrider@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-PAY-001',
            'status' => Bicycle::STATUS_AVAILABLE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 0,
        ]);

        $payment = Payment::create([
            'userId' => $user->id,
            'bicycleId' => $bicycle->id,
            'paymentReference' => 'GC-TEST-001',
            'paymentMethod' => 'gcash',
            'amount' => 30.00,
            'totalAmount' => 30.00,
            'currency' => 'PHP',
            'status' => 'paid',
            'metadata' => ['rental_duration_hours' => 2],
        ]);

        $rental = $service->createRentalFromPaidPayment($payment);

        $this->assertSame(Rental::STATUS_ACTIVE, $rental->status);
        $this->assertSame('gcash', $rental->paymentMethod);
        $this->assertSame('paid', $rental->paymentStatus);
        $this->assertSame($payment->id, $payment->fresh()->rentalId);
        $this->assertSame(Bicycle::STATUS_RENTED, $bicycle->fresh()->status);
    }

    public function test_settle_bicycle_for_rental_already_settled_returns_false(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $iotService = Mockery::mock(IoTService::class);

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Settle User',
            'email' => 'settle@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-STL-001',
            'status' => Bicycle::STATUS_MAINTENANCE,
            'hourlyRate' => 15,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_LOCKED,
            'totalRentals' => 1,
        ]);

        $rental = Rental::create([
            'rentalId' => 'REN-STL-001',
            'riderId' => $user->id,
            'bicycleId' => $bicycle->id,
            'status' => Rental::STATUS_RETURNED,
            'startTime' => now()->subHour(),
            'ratePerHour' => 15,
            'totalFee' => 15,
            'durationMinutes' => 60,
            'durationFormatted' => '1h 0m',
            'chargedHours' => 1,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $result = $service->settleBicycleForRental($rental);
        $this->assertFalse($result);
    }

    public function test_process_return_with_overdue_fee(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')->andReturn(new Notification());

        $iotService = Mockery::mock(IoTService::class);
        $iotService->shouldReceive('sendCommand')->andReturn(new DeviceCommand());

        $service = new RentalService($notificationService, $iotService);

        $user = User::create([
            'name' => 'Overdue Return',
            'email' => 'overduereturn@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        $bicycle = Bicycle::create([
            'name' => 'Bike',
            'model' => 'City',
            'serialNumber' => 'BIKE-OVR-001',
            'status' => Bicycle::STATUS_RENTED,
            'hourlyRate' => 20,
            'currentLat' => 14.5995,
            'currentLng' => 120.9842,
            'batteryLevel' => 90,
            'lockStatus' => Bicycle::LOCK_UNLOCKED,
            'currentRider' => $user->id,
            'totalRentals' => 1,
        ]);

        $rental = Rental::create([
            'rentalId' => 'REN-OVR-001',
            'riderId' => $user->id,
            'bicycleId' => $bicycle->id,
            'status' => Rental::STATUS_AWAITING_RETURN,
            'startTime' => now()->subHours(3),
            'expectedEndTime' => now()->subHour(),
            'endTime' => now()->subHour(),
            'ratePerHour' => 20,
            'totalFee' => 0,
            'durationMinutes' => 180,
            'durationFormatted' => '3h 0m',
            'chargedHours' => 3,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $result = $service->processReturn($rental, $user, [
            'returnTime' => now()->toDateTimeString(),
            'condition' => Rental::CONDITION_GOOD,
            'note' => 'Late return',
        ]);

        $this->assertSame(Rental::STATUS_RETURNED, $result['rental']->status);
        $this->assertGreaterThan(0, $result['fees']['overdueFee']);
        $this->assertGreaterThan($result['fees']['baseFee'], $result['fees']['finalFee']);
    }
}
