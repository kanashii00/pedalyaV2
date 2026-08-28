<?php

namespace Tests\Feature;

use App\Models\Bicycle;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class AdminPaymentTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.paymongo' => [
                'secret_key' => 'sk_test',
                'public_key' => 'pk_test',
                'webhook_secret' => 'test',
            ],
        ]);
        $this->admin = $this->makeAdmin();
    }

    public function test_index_renders_with_stats_and_filters(): void
    {
        $rider = $this->makeRider();
        $this->makePayment(['userId' => $rider->id, 'status' => 'paid', 'totalAmount' => 100]);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.index'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.payments.index') . '?status=paid&payment_method=gcash&search=zzz&date_from=2020-01-01&date_to=2030-01-01')
            ->assertOk();
    }

    public function test_create_with_gcash_creates_checkout_session(): void
    {
        $rider = $this->makeRider(['phone' => '09171234567']);
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);

        Http::fake([
            '*/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_123',
                    'attributes' => ['checkout_url' => 'https://checkout.example/123'],
                ],
            ], 200),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.create') . '?' . http_build_query([
                'userId' => $rider->id,
                'bicycleId' => $bike->id,
                'rentalDurationHours' => 2,
                'paymentMethod' => 'gcash',
                'amount' => 100,
                'convenienceFee' => 5,
            ]))
            ->assertOk()
            ->assertJsonPath('message', 'Payment initiated successfully')
            ->assertJsonPath('checkoutUrl', 'https://checkout.example/123');

        $this->assertDatabaseHas('payments', ['userId' => $rider->id, 'totalAmount' => 105, 'paymongoCheckoutUrl' => 'https://checkout.example/123']);
    }

    public function test_create_with_card_creates_payment_intent(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);

        Http::fake([
            '*/payment_intents' => Http::response([
                'data' => ['id' => 'pi_123'],
            ], 200),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.create') . '?' . http_build_query([
                'userId' => $rider->id,
                'bicycleId' => $bike->id,
                'rentalDurationHours' => 1,
                'paymentMethod' => 'card',
                'amount' => 50,
            ]))
            ->assertOk()
            ->assertJsonPath('checkoutUrl', null);

        $this->assertDatabaseHas('payments', ['userId' => $rider->id, 'paymongoPaymentId' => 'pi_123']);
    }

    public function test_create_unavailable_bicycle_rejected(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.create') . '?' . http_build_query([
                'userId' => $rider->id,
                'bicycleId' => $bike->id,
                'rentalDurationHours' => 1,
                'paymentMethod' => 'gcash',
                'amount' => 50,
            ]))
            ->assertStatus(422);
    }

    public function test_create_validation_fails_for_unknown_method(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);

        $this->actingAs($this->admin)
            ->getJson(route('admin.payments.create') . '?' . http_build_query([
                'userId' => $rider->id,
                'bicycleId' => $bike->id,
                'rentalDurationHours' => 1,
                'paymentMethod' => 'bogus',
                'amount' => 50,
            ]))
            ->assertStatus(422);
    }

    public function test_show_success_cancel_render(): void
    {
        $rider = $this->makeRider();
        $payment = $this->makePayment(['userId' => $rider->id]);

        $this->actingAs($this->admin)->get(route('admin.payments.show', $payment))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.payments.success', $payment))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.payments.cancel', $payment))->assertOk();
    }

    public function test_verify_marks_paid_and_creates_rental(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);
        $payment = $this->makePayment([
            'userId' => $rider->id,
            'bicycleId' => $bike->id,
            'paymongoPaymentId' => 'cs_verify',
            'paymentMethod' => 'gcash',
            'status' => 'pending',
            'totalAmount' => 100,
        ]);

        Http::fake([
            '*/checkout_sessions/*' => Http::response([
                'data' => ['attributes' => ['status' => 'paid']],
            ], 200),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.verify', $payment))
            ->assertOk()
            ->assertJsonPath('status', 'paid');

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->rentalId);
    }

    public function test_verify_without_paymongo_id_rejected(): void
    {
        $rider = $this->makeRider();
        $payment = $this->makePayment(['userId' => $rider->id, 'paymongoPaymentId' => null]);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.verify', $payment))
            ->assertStatus(400)
            ->assertJsonPath('message', 'No PayMongo payment ID found');
    }
}
