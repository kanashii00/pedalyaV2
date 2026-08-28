<?php

namespace Tests\Feature;

use App\Models\Bicycle;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
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
                'webhook_secret' => 'test-webhook-secret',
            ],
        ]);
        $this->admin = $this->makeAdmin();
    }

    private function webhook(string $event, array $data, array $previous = null): \Illuminate\Testing\TestResponse
    {
        $payload = json_encode([
            'data' => [
                'attributes' => [
                    'event' => $event,
                    'data' => $data,
                    'previous_data' => $previous,
                ],
            ],
        ]);

        $signature = hash_hmac('sha256', $payload, 'test-webhook-secret');

        return $this->actingAs($this->admin)
            ->from('/admin')
            ->postJson(route('admin.payments.webhook'), json_decode($payload, true), [
                'Paymongo-Signature' => $signature,
            ]);
    }

    public function test_invalid_signature_rejected(): void
    {
        $payload = json_encode(['data' => ['attributes' => ['event' => 'payment_intent.succeeded', 'data' => [], 'previous_data' => null]]]);

        $response = $this->actingAs($this->admin)
            ->from('/admin')
            ->postJson(route('admin.payments.webhook'), json_decode($payload, true), [
                'Paymongo-Signature' => 'wrong-signature',
            ]);

        $response->assertStatus(401);
    }

    public function test_missing_signature_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin')
            ->postJson(route('admin.payments.webhook'), ['data' => ['attributes' => ['event' => 'x', 'data' => []]]])
            ->assertStatus(401);
    }

    public function test_payment_intent_succeeded_marks_paid_and_creates_rental(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $intentId = 'pi_' . uniqid();

        $payment = Payment::create([
            'userId' => $rider->id,
            'bicycleId' => $bike->id,
            'paymongoPaymentId' => $intentId,
            'paymentReference' => 'PMT-WEBHOOK',
            'paymentMethod' => 'gcash',
            'amount' => 100.00,
            'totalAmount' => 100.00,
            'currency' => 'PHP',
            'status' => 'processing',
            'metadata' => ['rental_duration_hours' => 1],
        ]);

        $this->webhook('payment_intent.succeeded', [
            'id' => $intentId,
            'type' => 'payment_intent',
        ])->assertJsonPath('message', 'Payment processed');

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->rentalId);
        $this->assertDatabaseHas('rentals', ['id' => $payment->fresh()->rentalId]);
        $this->assertSame('rented', $bike->fresh()->status);
    }

    public function test_payment_intent_succeeded_already_processed(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $intentId = 'pi_' . uniqid();

        $payment = Payment::create([
            'userId' => $rider->id,
            'bicycleId' => $bike->id,
            'paymongoPaymentId' => $intentId,
            'paymentReference' => 'PMT-WEBHOOK-2',
            'paymentMethod' => 'gcash',
            'amount' => 100.00,
            'totalAmount' => 100.00,
            'currency' => 'PHP',
            'status' => 'paid',
        ]);

        $this->webhook('payment_intent.succeeded', ['id' => $intentId])
            ->assertJsonPath('message', 'Already processed');
    }

    public function test_payment_intent_succeeded_payment_not_found(): void
    {
        $this->webhook('payment_intent.succeeded', ['id' => 'pi_nonexistent'])
            ->assertJsonPath('message', 'Payment not found');
    }

    public function test_payment_intent_failed_marks_failed_and_locks_bike(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle(['lockStatus' => Bicycle::LOCK_UNLOCKED]);
        $intentId = 'pi_' . uniqid();

        $payment = Payment::create([
            'userId' => $rider->id,
            'bicycleId' => $bike->id,
            'paymongoPaymentId' => $intentId,
            'paymentReference' => 'PMT-FAIL',
            'paymentMethod' => 'gcash',
            'amount' => 100.00,
            'totalAmount' => 100.00,
            'currency' => 'PHP',
            'status' => 'processing',
        ]);

        $this->webhook('payment_intent.failed', [
            'id' => $intentId,
            'attributes' => ['last_payment_error' => ['message' => 'Card declined']],
        ])->assertJsonPath('message', 'Payment failure recorded');

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertSame('locked', $bike->fresh()->lockStatus);
    }

    public function test_payment_intent_processing_records_status(): void
    {
        $rider = $this->makeRider();
        $intentId = 'pi_' . uniqid();

        Payment::create([
            'userId' => $rider->id,
            'paymongoPaymentId' => $intentId,
            'paymentReference' => 'PMT-PROC',
            'paymentMethod' => 'gcash',
            'amount' => 100.00,
            'totalAmount' => 100.00,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        $this->webhook('payment_intent.processing', ['id' => $intentId])
            ->assertJsonPath('message', 'Processing status recorded');

        $this->assertSame('processing', Payment::where('paymongoPaymentId', $intentId)->first()->status);
    }

    public function test_checkout_session_completed_marks_paid(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();

        $payment = Payment::create([
            'userId' => $rider->id,
            'bicycleId' => $bike->id,
            'paymentReference' => 'PMT-CHECKOUT',
            'paymentMethod' => 'gcash',
            'amount' => 100.00,
            'totalAmount' => 100.00,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        $this->webhook('checkout_session.completed', [
            'attributes' => ['metadata' => ['payment_id' => $payment->id]],
        ])->assertJsonPath('message', 'Checkout completed');

        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_checkout_session_completed_missing_reference(): void
    {
        $this->webhook('checkout_session.completed', ['attributes' => ['metadata' => []]])
            ->assertJsonPath('message', 'No payment reference');
    }

    public function test_checkout_expired_marks_expired(): void
    {
        $rider = $this->makeRider();
        $payment = Payment::create([
            'userId' => $rider->id,
            'paymentReference' => 'PMT-EXP',
            'paymentMethod' => 'gcash',
            'amount' => 100.00,
            'totalAmount' => 100.00,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        $this->webhook('checkout_session.expired', [
            'attributes' => ['metadata' => ['payment_id' => $payment->id]],
        ])->assertJsonPath('message', 'Checkout expiration recorded');

        $this->assertSame('expired', $payment->fresh()->status);
    }

    public function test_refund_succeeded_updates_refund_and_payment(): void
    {
        $rider = $this->makeRider();
        $payment = Payment::create([
            'userId' => $rider->id,
            'paymentReference' => 'PMT-REF-OK',
            'paymentMethod' => 'gcash',
            'amount' => 100.00,
            'totalAmount' => 100.00,
            'currency' => 'PHP',
            'status' => 'paid',
        ]);

        $refund = Refund::create([
            'paymentId' => $payment->id,
            'userId' => $rider->id,
            'paymongoRefundId' => 'rf_' . uniqid(),
            'amount' => 100.00,
            'currency' => 'PHP',
            'status' => 'pending',
            'reason' => 'customer_request',
            'refundReference' => 'REF-' . uniqid(),
        ]);

        $this->webhook('refund.succeeded', ['id' => $refund->paymongoRefundId])
            ->assertJsonPath('message', 'Refund recorded');

        $this->assertSame('succeeded', $refund->fresh()->status);
        $this->assertSame('refunded', $payment->fresh()->status);
    }

    public function test_refund_failed_updates_refund(): void
    {
        $rider = $this->makeRider();
        $payment = Payment::create([
            'userId' => $rider->id,
            'paymentReference' => 'PMT-REF-FAIL',
            'paymentMethod' => 'gcash',
            'amount' => 100.00,
            'totalAmount' => 100.00,
            'currency' => 'PHP',
            'status' => 'paid',
        ]);

        $refund = Refund::create([
            'paymentId' => $payment->id,
            'userId' => $rider->id,
            'paymongoRefundId' => 'rf_' . uniqid(),
            'amount' => 100.00,
            'currency' => 'PHP',
            'status' => 'pending',
            'reason' => 'customer_request',
            'refundReference' => 'REF-' . uniqid(),
        ]);

        $this->webhook('refund.failed', [
            'id' => $refund->paymongoRefundId,
            'attributes' => ['failure_reason' => 'Insufficient funds'],
        ])->assertJsonPath('message', 'Refund failure recorded');

        $this->assertSame('failed', $refund->fresh()->status);
    }

    public function test_unknown_event_acknowledged(): void
    {
        $this->webhook('some.other.event', ['id' => 'x'])
            ->assertJsonPath('message', 'Event acknowledged');
    }
}
