<?php

namespace App\Services;

use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayMongoService
{
    private const UNKNOWN_ERROR = 'Unknown error';

    private string $secretKey;
    private string $publicKey;
    private string $webhookSecret;
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key');
        $this->publicKey = config('services.paymongo.public_key');
        $this->webhookSecret = config('services.paymongo.webhook_secret');
        $this->baseUrl = config('services.paymongo.base_url', 'https://api.paymongo.com/v1');
        $this->timeout = config('services.paymongo.timeout', 30);
    }

    private function getAuthHeader(): string
    {
        return 'Basic ' . base64_encode($this->secretKey . ':');
    }

    private function getClient(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $this->getAuthHeader(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout($this->timeout);
    }

    /**
     * Create a Payment Intent for card payments
     */
    public function createPaymentIntent(array $data): array
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => (int) round($data['amount'] * 100), // Convert to centavos
                    'currency' => $data['currency'] ?? 'PHP',
                    'payment_method_allowed' => $data['paymentMethods'] ?? ['card', 'gcash', 'paymaya', 'grab_pay', 'bpi', 'unionbank'],
                    'payment_method_options' => [
                        'card' => [
                            'request_three_d_secure' => 'any',
                        ],
                    ],
                    'metadata' => $data['metadata'] ?? [],
                    'description' => $data['description'] ?? 'Bicycle Rental Payment',
                ],
            ],
        ];

        $response = $this->getClient()->post("{$this->baseUrl}/payment_intents", $payload);

        if (!$response->successful()) {
            Log::error('PayMongo createPaymentIntent failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new PaymentException('Failed to create payment intent: ' . ($response->json()['errors'][0]['detail'] ?? self::UNKNOWN_ERROR));
        }

        return $response->json()['data'];
    }

    /**
     * Create a Checkout Session for redirect-based payments (GCash, Maya, GrabPay, Online Banking)
     */
    public function createCheckoutSession(array $data): array
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'line_items' => [[
                        'name' => $data['itemName'] ?? 'Bicycle Rental',
                        'amount' => (int) round($data['amount'] * 100),
                        'currency' => $data['currency'] ?? 'PHP',
                        'quantity' => 1,
                    ]],
                    'payment_method_types' => $data['paymentMethods'] ?? ['gcash', 'paymaya', 'grab_pay', 'bpi', 'unionbank'],
                    'success_url' => $data['successUrl'],
                    'cancel_url' => $data['cancelUrl'],
                    'metadata' => $data['metadata'] ?? [],
                    'description' => $data['description'] ?? 'Bicycle Rental Payment',
                    'send_email_receipt' => true,
                    'show_description' => true,
                    'show_line_items' => true,
                ],
            ],
        ];

        $response = $this->getClient()->post("{$this->baseUrl}/checkout_sessions", $payload);

        if (!$response->successful()) {
            Log::error('PayMongo createCheckoutSession failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new PaymentException('Failed to create checkout session: ' . ($response->json()['errors'][0]['detail'] ?? self::UNKNOWN_ERROR));
        }

        return $response->json()['data'];
    }

    /**
     * Retrieve a Payment Intent
     */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        $response = $this->getClient()->get("{$this->baseUrl}/payment_intents/{$paymentIntentId}");

        if (!$response->successful()) {
            throw new PaymentException('Failed to retrieve payment intent');
        }

        return $response->json()['data'];
    }

    /**
     * Retrieve a Checkout Session
     */
    public function retrieveCheckoutSession(string $checkoutSessionId): array
    {
        $response = $this->getClient()->get("{$this->baseUrl}/checkout_sessions/{$checkoutSessionId}");

        if (!$response->successful()) {
            throw new PaymentException('Failed to retrieve checkout session');
        }

        return $response->json()['data'];
    }

    /**
     * Create a Refund
     */
    public function createRefund(array $data): array
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => (int) round($data['amount'] * 100),
                    'payment_intent' => $data['paymentIntentId'],
                    'reason' => $data['reason'] ?? 'customer_request',
                    'metadata' => $data['metadata'] ?? [],
                ],
            ],
        ];

        $response = $this->getClient()->post("{$this->baseUrl}/refunds", $payload);

        if (!$response->successful()) {
            Log::error('PayMongo createRefund failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new PaymentException('Failed to create refund: ' . ($response->json()['errors'][0]['detail'] ?? self::UNKNOWN_ERROR));
        }

        return $response->json()['data'];
    }

    /**
     * Retrieve a Refund
     */
    public function retrieveRefund(string $refundId): array
    {
        $response = $this->getClient()->get("{$this->baseUrl}/refunds/{$refundId}");

        if (!$response->successful()) {
            throw new PaymentException('Failed to retrieve refund');
        }

        return $response->json()['data'];
    }

    /**
     * List Refunds for a Payment Intent
     */
    public function listRefunds(string $paymentIntentId): array
    {
        $response = $this->getClient()->get("{$this->baseUrl}/refunds", [
            'payment_intent' => $paymentIntentId,
        ]);

        if (!$response->successful()) {
            throw new PaymentException('Failed to list refunds');
        }

        return $response->json()['data'];
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('PayMongo webhook secret not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse webhook event
     */
    public function parseWebhookEvent(string $payload): array
    {
        $data = json_decode($payload, true);

        if (!$data) {
            throw new PaymentException('Invalid webhook payload');
        }

        return [
            'event' => $data['data']['attributes']['event'] ?? null,
            'data' => $data['data']['attributes']['data'] ?? null,
            'previous_data' => $data['data']['attributes']['previous_data'] ?? null,
        ];
    }

    /**
     * Generate payment reference
     */
    public static function generatePaymentReference(): string
    {
        return 'PMT-' . date('Ymd') . '-' . strtoupper(Str::random(8));
    }

    /**
     * Generate refund reference
     */
    public static function generateRefundReference(): string
    {
        return 'REF-' . date('Ymd') . '-' . strtoupper(Str::random(8));
    }
}
