<?php

namespace Tests\Unit;

use App\Exceptions\PaymentException;
use App\Services\PayMongoService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayMongoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.paymongo.secret_key' => 'sk_test_123',
            'services.paymongo.public_key' => 'pk_test_123',
            'services.paymongo.webhook_secret' => 'whsec_123',
            'services.paymongo.base_url' => 'https://api.paymongo.com/v1',
            'services.paymongo.timeout' => 30,
        ]);

        $this->service = app(PayMongoService::class);
    }

    public function test_create_payment_intent_success(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/payment_intents' => Http::response([
                'data' => ['id' => 'pi_123', 'attributes' => ['status' => 'awaiting_payment_method']],
            ], 200),
        ]);

        $data = $this->service->createPaymentIntent(['amount' => 100, 'metadata' => ['ref' => 'x']]);

        $this->assertSame('pi_123', $data['id']);
    }

    public function test_create_payment_intent_failure_throws(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/payment_intents' => Http::response([
                'errors' => [['detail' => 'Invalid amount']],
            ], 400),
        ]);

        $this->expectException(PaymentException::class);
        $this->service->createPaymentIntent(['amount' => -1]);
    }

    public function test_create_checkout_session_success(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response([
                'data' => ['id' => 'cs_123', 'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/cs_123']],
            ], 200),
        ]);

        $data = $this->service->createCheckoutSession([
            'amount' => 150,
            'successUrl' => 'https://app.test/success',
            'cancelUrl' => 'https://app.test/cancel',
        ]);

        $this->assertSame('cs_123', $data['id']);
    }

    public function test_create_checkout_session_failure_throws(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response(['errors' => []], 500),
        ]);

        $this->expectException(PaymentException::class);
        $this->service->createCheckoutSession(['amount' => 150, 'successUrl' => 'x', 'cancelUrl' => 'y']);
    }

    public function test_retrieve_payment_intent_success(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/payment_intents/pi_123' => Http::response([
                'data' => ['id' => 'pi_123'],
            ], 200),
        ]);

        $data = $this->service->retrievePaymentIntent('pi_123');

        $this->assertSame('pi_123', $data['id']);
    }

    public function test_retrieve_payment_intent_failure_throws(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/payment_intents/pi_999' => Http::response([], 404),
        ]);

        $this->expectException(PaymentException::class);
        $this->service->retrievePaymentIntent('pi_999');
    }

    public function test_retrieve_checkout_session_success(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_456' => Http::response([
                'data' => ['id' => 'cs_456'],
            ], 200),
        ]);

        $data = $this->service->retrieveCheckoutSession('cs_456');

        $this->assertSame('cs_456', $data['id']);
    }

    public function test_create_refund_success(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/refunds' => Http::response([
                'data' => ['id' => 'rfd_1', 'attributes' => []],
            ], 200),
        ]);

        $data = $this->service->createRefund(['amount' => 50, 'paymentIntentId' => 'pi_123']);

        $this->assertSame('rfd_1', $data['id']);
    }

    public function test_retrieve_refund_success_and_failure(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/refunds/rfd_2' => Http::response(['data' => ['id' => 'rfd_2']], 200),
            'https://api.paymongo.com/v1/refunds/rfd_404' => Http::response([], 404),
        ]);

        $this->assertSame('rfd_2', $this->service->retrieveRefund('rfd_2')['id']);

        $this->expectException(PaymentException::class);
        $this->service->retrieveRefund('rfd_404');
    }

    public function test_list_refunds_success(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/refunds*' => Http::response([
                'data' => [['id' => 'rfd_1'], ['id' => 'rfd_2']],
            ], 200),
        ]);

        $data = $this->service->listRefunds('pi_123');

        $this->assertCount(2, $data);
    }

    public function test_verify_webhook_signature_valid(): void
    {
        $payload = '{"event":"payment.paid"}';
        $signature = hash_hmac('sha256', $payload, 'whsec_123');

        $this->assertTrue($this->service->verifyWebhookSignature($payload, $signature));
        $this->assertFalse($this->service->verifyWebhookSignature($payload, 'wrong'));
    }

    public function test_verify_webhook_signature_without_secret_returns_false(): void
    {
        config(['services.paymongo.webhook_secret' => '']);
        $service = app(PayMongoService::class);

        $this->assertFalse($service->verifyWebhookSignature('payload', 'sig'));
    }

    public function test_parse_webhook_event(): void
    {
        $payload = json_encode([
            'data' => [
                'attributes' => [
                    'event' => 'payment.paid',
                    'data' => ['id' => 'pay_1'],
                    'previous_data' => null,
                ],
            ],
        ]);

        $parsed = $this->service->parseWebhookEvent($payload);

        $this->assertSame('payment.paid', $parsed['event']);
        $this->assertSame(['id' => 'pay_1'], $parsed['data']);
    }

    public function test_parse_webhook_event_invalid_payload_throws(): void
    {
        $this->expectException(PaymentException::class);
        $this->service->parseWebhookEvent('not json');
    }

    public function test_generate_references_have_correct_prefix(): void
    {
        $this->assertStringStartsWith('PMT-', PayMongoService::generatePaymentReference());
        $this->assertStringStartsWith('REF-', PayMongoService::generateRefundReference());
    }
}
