<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Bicycle;
use App\Services\RentalService;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    private const PAYMENT_NOT_FOUND = 'Payment not found';

    public function __construct(
        private PayMongoService $payMongoService,
        private RentalService $rentalService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('Paymongo-Signature');
        $payload = $request->getContent();

        if (empty($signature) || !is_string($signature)) {
            Log::warning('PayMongo webhook signature missing', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        if (!$this->payMongoService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('PayMongo webhook signature verification failed', [
                'ip' => $request->ip(),
                'signature' => $signature,
            ]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        try {
            $event = $this->payMongoService->parseWebhookEvent($payload);
        } catch (\Throwable $e) {
            Log::error('PayMongo webhook parse failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $eventType = $event['event'];
        $data = $event['data'];
        $previousData = $event['previous_data'];

        Log::info('PayMongo webhook received', [
            'event' => $eventType,
            'payment_intent_id' => $data['id'] ?? $data['payment_intent'] ?? null,
        ]);

        return DB::transaction(function () use ($eventType, $data, $previousData) {
            return match ($eventType) {
                'payment_intent.succeeded' => $this->handlePaymentSucceeded($data),
                'payment_intent.failed' => $this->handlePaymentFailed($data),
                'payment_intent.processing' => $this->handlePaymentProcessing($data),
                'checkout_session.completed' => $this->handleCheckoutCompleted($data),
                'checkout_session.expired' => $this->handleCheckoutExpired($data),
                'refund.succeeded' => $this->handleRefundSucceeded($data),
                'refund.failed' => $this->handleRefundFailed($data),
                default => response()->json(['message' => 'Event acknowledged']),
            };
        });
    }

    private function handlePaymentSucceeded(array $data): JsonResponse
    {
        $payment = Payment::where('paymongoPaymentId', $data['id'])->first();

        if (!$payment) {
            Log::warning('Payment not found for succeeded intent', ['intent_id' => $data['id']]);
            return response()->json(['message' => self::PAYMENT_NOT_FOUND]);
        }

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'Already processed']);
        }

        $payment->update([
            'status' => 'paid',
            'paymentDetails' => $data,
            'paidAt' => now(),
        ]);

        if (!$payment->rentalId) {
            $this->rentalService->createRentalFromPaidPayment($payment);
        }

        Log::info('Payment succeeded and rental created', ['payment_id' => $payment->id]);
        return response()->json(['message' => 'Payment processed']);
    }

    private function handlePaymentFailed(array $data): JsonResponse
    {
        $payment = Payment::where('paymongoPaymentId', $data['id'])->first();

        if (!$payment) {
            return response()->json(['message' => self::PAYMENT_NOT_FOUND]);
        }

        $failureReason = $data['attributes']['last_payment_error']['message'] ?? 'Payment failed';

        $payment->update([
            'status' => 'failed',
            'paymentDetails' => $data,
            'failureReason' => $failureReason,
        ]);

        // Ensure bike remains locked
        $bicycle = Bicycle::find($payment->bicycleId);
        if ($bicycle && $bicycle->status !== 'rented') {
            $bicycle->update(['lockStatus' => 'locked']);
        }

        return response()->json(['message' => 'Payment failure recorded']);
    }

    private function handlePaymentProcessing(array $data): JsonResponse
    {
        $payment = Payment::where('paymongoPaymentId', $data['id'])->first();

        if ($payment && $payment->status !== 'processing') {
            $payment->update([
                'status' => 'processing',
                'paymentDetails' => $data,
            ]);
        }

        return response()->json(['message' => 'Processing status recorded']);
    }

    private function handleCheckoutCompleted(array $data): JsonResponse
    {
        $metadata = $data['attributes']['metadata'] ?? [];
        $paymentId = $metadata['payment_id'] ?? null;

        if (!$paymentId) {
            Log::warning('Checkout completed but no payment_id in metadata');
            return response()->json(['message' => 'No payment reference']);
        }

        $payment = Payment::find($paymentId);

        if (!$payment) {
            return response()->json(['message' => self::PAYMENT_NOT_FOUND]);
        }

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'Already processed']);
        }

        $payment->update([
            'status' => 'paid',
            'paymentDetails' => $data,
            'paidAt' => now(),
        ]);

        if (!$payment->rentalId) {
            $this->rentalService->createRentalFromPaidPayment($payment);
        }

        return response()->json(['message' => 'Checkout completed']);
    }

    private function handleCheckoutExpired(array $data): JsonResponse
    {
        $metadata = $data['attributes']['metadata'] ?? [];
        $paymentId = $metadata['payment_id'] ?? null;

        if ($paymentId) {
            $payment = Payment::find($paymentId);
            if ($payment && $payment->status === 'pending') {
                $payment->update([
                    'status' => 'expired',
                    'paymentDetails' => $data,
                    'expiredAt' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Checkout expiration recorded']);
    }

    private function handleRefundSucceeded(array $data): JsonResponse
    {
        $refund = \App\Models\Refund::where('paymongoRefundId', $data['id'])->first();

        if ($refund) {
            $refund->update([
                'status' => 'succeeded',
                'paymongoResponse' => $data,
                'processedAt' => now(),
            ]);

            // Update payment status to refunded
            $payment = $refund->payment;
            if ($payment) {
                $payment->update(['status' => 'refunded']);
            }
        }

        return response()->json(['message' => 'Refund recorded']);
    }

    private function handleRefundFailed(array $data): JsonResponse
    {
        $refund = \App\Models\Refund::where('paymongoRefundId', $data['id'])->first();

        if ($refund) {
            $failureReason = $data['attributes']['failure_reason'] ?? 'Refund failed';
            $refund->update([
                'status' => 'failed',
                'paymongoResponse' => $data,
                'failureReason' => $failureReason,
            ]);
        }

        return response()->json(['message' => 'Refund failure recorded']);
    }
}
