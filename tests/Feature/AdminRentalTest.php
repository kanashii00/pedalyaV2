<?php

namespace Tests\Feature;

use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\Payment;
use App\Models\Rental;
use App\Services\IoTService;
use App\Services\RentalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class AdminRentalTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->makeAdmin();
    }

    public function test_index_renders_and_filters(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_ACTIVE]);
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_PENDING]);

        $this->actingAs($this->admin)->get(route('admin.rentals.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.rentals.index') . '?status=active&bicycle_id=' . $bike->id . '&rider_id=' . $rider->id . '&date_from=2020-01-01&date_to=2030-01-01')->assertOk();
    }

    public function test_history_renders(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_COMPLETED]);
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_CANCELLED]);

        $this->actingAs($this->admin)->get(route('admin.rentals.history'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.rentals.history') . '?status=completed')->assertOk();
    }

    public function test_show_renders(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_ACTIVE]);

        $this->actingAs($this->admin)->get(route('admin.rentals.show', $rental->id))->assertOk();
    }

    public function test_approve_pending_rental(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_PENDING]);

        $this->mockIot();

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.approve', $rental->id))
            ->assertRedirect(route('admin.rentals.index'))
            ->assertSessionHas('success');

        $this->assertSame(Rental::STATUS_ACTIVE, $rental->fresh()->status);
        $this->assertSame('rented', $bike->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'rental_approved']);
    }

    public function test_approve_non_pending_rejected(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_ACTIVE]);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.approve', $rental->id))
            ->assertSessionHasErrors('rental');
    }

    public function test_verify_gcash_payment(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);
        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_PENDING,
            'paymentMethod' => 'gcash',
            'paymentStatus' => 'pending_verification',
        ]);
        Payment::create([
            'rentalId' => $rental->id,
            'userId' => $rider->id,
            'bicycleId' => $bike->id,
            'paymentReference' => 'GC-123',
            'paymentMethod' => 'gcash',
            'amount' => 15,
            'totalAmount' => 15,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        $this->mockIot();

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.verify-gcash', $rental->id))
            ->assertRedirect(route('admin.rentals.index'));

        $this->assertSame(Rental::STATUS_ACTIVE, $rental->fresh()->status);
        $this->assertSame('paid', $rental->fresh()->paymentStatus);
        $this->assertSame(1, $rider->fresh()->totalRentals);
    }

    public function test_verify_gcash_only_for_pending_gcash(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_ACTIVE, 'paymentMethod' => 'cash']);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.verify-gcash', $rental->id))
            ->assertSessionHasErrors('rental');
    }

    public function test_mark_paid_cash_rental(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_ACTIVE,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.mark-paid', $rental->id))
            ->assertRedirect(route('admin.rentals.index'));

        $this->assertSame('paid', $rental->fresh()->paymentStatus);
    }

    public function test_mark_paid_rejects_gcash(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_ACTIVE,
            'paymentMethod' => 'gcash',
            'paymentStatus' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.mark-paid', $rental->id))
            ->assertSessionHasErrors('rental');
    }

    public function test_mark_paid_rejects_already_paid(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_ACTIVE,
            'paymentMethod' => 'cash',
            'paymentStatus' => 'paid',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.mark-paid', $rental->id))
            ->assertSessionHasErrors('rental');
    }

    public function test_end_ride_completes_rental(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_ACTIVE]);

        $service = Mockery::mock(RentalService::class);
        $service->shouldReceive('markRideEnded')->once()->andReturn([
            'rental' => $rental->fresh(),
        ]);
        $this->app->instance(RentalService::class, $service);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.end-ride', $rental->id))
            ->assertRedirect(route('admin.rentals.index'));

        $this->assertDatabaseHas('audit_logs', ['action' => 'rental_ended_by_admin']);
    }

    public function test_cancel_rental_locks_bicycle(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_ACTIVE]);
        $bike->update(['currentRentalId' => $rental->id]);

        $this->mockIot();

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.cancel', $rental->id), ['reason' => 'test cancel'])
            ->assertRedirect(route('admin.rentals.index'));

        $this->assertSame(Rental::STATUS_CANCELLED, $rental->fresh()->status);
        $this->assertSame('available', $bike->fresh()->status);
    }

    public function test_cancel_completed_rejected(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_COMPLETED]);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.cancel', $rental->id))
            ->assertSessionHasErrors('rental');
    }

    private function mockIot(): void
    {
        $iot = Mockery::mock(IoTService::class);
        $iot->shouldReceive('sendCommand')->andReturn(
            DeviceCommand::create(['bicycleId' => 1, 'command' => 'lock', 'status' => 'pending'])
        );
        $this->app->instance(IoTService::class, $iot);
    }

    public function test_history_filters_by_date_and_bicycle(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_COMPLETED]);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.history') . '?date_from=2020-01-01&date_to=2030-12-31&bicycle_id=' . $bike->id . '&rider_id=' . $rider->id)
            ->assertOk();
    }

    public function test_returns_pending_view(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_AWAITING_RETURN]);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.returns'))
            ->assertOk();
    }

    public function test_returns_processed_view(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_RETURNED]);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.returns') . '?view=processed')
            ->assertOk();
    }

    public function test_returns_filters(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_AWAITING_RETURN]);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.returns') . '?status=awaiting_return&date_from=2020-01-01&date_to=2030-12-31&bicycle_id=' . $bike->id . '&rider_id=' . $rider->id)
            ->assertOk();
    }

    public function test_returns_invalid_view_falls_back_to_pending(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.rentals.returns') . '?view=invalid')
            ->assertOk();
    }

    public function test_end_ride_invalid_status_returns_error(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_COMPLETED]);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.end-ride', $rental->id))
            ->assertSessionHasErrors('rental');
    }

    public function test_end_ride_service_exception_returns_error(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_ACTIVE]);

        $service = Mockery::mock(RentalService::class);
        $service->shouldReceive('markRideEnded')->once()->andThrow(new \RuntimeException('Service failure'));
        $this->app->instance(RentalService::class, $service);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.end-ride', $rental->id))
            ->assertSessionHasErrors('rental');
    }

    public function test_process_return_success(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);
        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_AWAITING_RETURN,
            'endTime' => now()->subHour(),
            'expectedEndTime' => now()->subHour(),
        ]);

        $service = Mockery::mock(RentalService::class);
        $service->shouldReceive('processReturn')->once()->andReturn([
            'rental' => $rental->fresh(),
            'fees' => ['baseFee' => 15, 'overdueFee' => 0, 'finalFee' => 15],
        ]);
        $this->app->instance(RentalService::class, $service);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.returns'))
            ->put(route('admin.rentals.process-return', $rental->id), [
                'condition' => 'good',
                'note' => 'Looks fine',
            ])
            ->assertRedirect(route('admin.rentals.returns'))
            ->assertSessionHas('success');
    }

    public function test_process_return_service_exception_returns_error(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_AWAITING_RETURN,
        ]);

        $service = Mockery::mock(RentalService::class);
        $service->shouldReceive('processReturn')->once()->andThrow(new \RuntimeException('Process failure'));
        $this->app->instance(RentalService::class, $service);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.returns'))
            ->put(route('admin.rentals.process-return', $rental->id), ['condition' => 'good'])
            ->assertSessionHasErrors('rental');
    }

    public function test_cancel_with_matching_bicycle(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);
        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_ACTIVE,
        ]);
        $bike->update(['currentRentalId' => $rental->id]);

        $this->mockIot();

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.cancel', $rental->id), ['reason' => 'Maintenance needed'])
            ->assertRedirect(route('admin.rentals.index'));

        $this->assertSame(Rental::STATUS_CANCELLED, $rental->fresh()->status);
        $this->assertSame(Bicycle::STATUS_AVAILABLE, $bike->fresh()->status);
    }

    public function test_cancel_without_matching_bicycle(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);
        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.index'))
            ->put(route('admin.rentals.cancel', $rental->id), ['reason' => 'Changed mind'])
            ->assertRedirect(route('admin.rentals.index'));

        $this->assertSame(Rental::STATUS_CANCELLED, $rental->fresh()->status);
    }

    public function test_history_view_filters_completed(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_RETURNED]);
        $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id, 'status' => Rental::STATUS_EXPIRED]);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.history') . '?status=returned')
            ->assertOk();
    }
}
