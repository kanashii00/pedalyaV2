<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Bicycle;
use App\Models\User;
use App\Services\IoTService;
use App\Services\RentalService;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private PayMongoService $payMongoService,
        private IoTService $iotService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('Paymongo-Signature');
        $payload = $request->getContent();

        if (!$this->payMongoService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('PayMongo webhook signature verification failed', [
                'ip' => $request->ip(),
                'signature' => $signature,
            ]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        try {
            $event = $this->payMongoService->parseWebhookEvent($payload);
        } catch (\Exception $e) {
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
            return response()->json(['message' => 'Payment not found']);
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
            $this->createRentalFromPayment($payment);
        }

        Log::info('Payment succeeded and rental created', ['payment_id' => $payment->id]);
        return response()->json(['message' => 'Payment processed']);
    }

    private function handlePaymentFailed(array $data): JsonResponse
    {
        $payment = Payment::where('paymongoPaymentId', $data['id'])->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found']);
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
            return response()->json(['message' => 'Payment not found']);
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
            $this->createRentalFromPayment($payment);
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

    private function createRentalFromPayment(Payment $payment): void
    {
        $metadata = $payment->metadata ?? [];
        $durationHours = (int) ($metadata['rental_duration_hours'] ?? 1);
        $rider = User::find($payment->userId);

        // Payment succeeded: the rental starts now, so the bicycle becomes
        // Rented and its smart lock is Unlocked for the authorized rider.
        $rental = Rental::create([
            'rentalId' => app(RentalService::class)->generateRentalId(),
            'bicycleId' => $payment->bicycleId,
            'bicycleName' => optional(Bicycle::find($payment->bicycleId))->name,
            'bicycleSerial' => optional(Bicycle::find($payment->bicycleId))->serialNumber,
            'riderId' => $payment->userId,
            'riderName' => $rider?->name,
            'riderEmail' => $rider?->email,
            'status' => 'active',
            'startTime' => now(),
            'endTime' => now()->addHours(max($durationHours, 1)),
            'ratePerHour' => $durationHours > 0 ? round($payment->totalAmount / $durationHours, 2) : $payment->totalAmount,
            'totalFee' => $payment->totalAmount,
            'chargedHours' => max($durationHours, 1),
            'durationMinutes' => max($durationHours, 1) * 60,
            'durationFormatted' => $durationHours . 'h 0m',
            'paymentStatus' => 'paid',
            'paymentMethod' => 'gcash',
        ]);

        $payment->update(['rentalId' => $rental->id]);

        $bicycle = Bicycle::find($payment->bicycleId);
        if ($bicycle) {
            $bicycle->update([
                'status' => Bicycle::STATUS_RENTED,
                'currentRider' => $payment->userId,
                'currentRentalId' => $rental->id,
                'lockStatus' => Bicycle::LOCK_UNLOCKED,
                'lastLockAction' => now(),
            ]);

            if ($rider) {
                $this->iotService->sendCommand($bicycle->id, 'unlock', [], $rider);
            }
        }
    }
}