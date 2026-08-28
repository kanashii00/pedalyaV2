<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Bicycle;
use App\Models\User;
use App\Services\RentalService;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(
        private PayMongoService $payMongoService,
        private RentalService $rentalService
    ) {}

    public function index(Request $request): Response
    {
        $query = Payment::with(['user', 'rental', 'bicycle', 'refund'])
            ->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('paymentReference', 'like', "%{$search}%")
                  ->orWhere('paymongoPaymentId', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($paymentMethod = $request->get('payment_method')) {
            $query->where('paymentMethod', $paymentMethod);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $payments = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Payment::count(),
            'paid' => Payment::where('status', 'paid')->count(),
            'pending' => Payment::whereIn('status', ['pending', 'processing'])->count(),
            'failed' => Payment::whereIn('status', ['failed', 'expired', 'cancelled'])->count(),
            'totalRevenue' => Payment::where('status', 'paid')->sum('totalAmount'),
            'todayRevenue' => Payment::where('status', 'paid')
                ->whereDate('paidAt', now())->sum('totalAmount'),
        ];

        return response()->view('admin.payments.index', compact('payments', 'stats'));
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'userId' => ['required', 'exists:users,id'],
            'bicycleId' => ['required', 'exists:bicycles,id'],
            'rentalDurationHours' => ['required', 'integer', 'min:1', 'max:12'],
            'paymentMethod' => ['required', Rule::in(['gcash', 'maya', 'grabpay', 'card', 'online_banking'])],
            'amount' => ['required', 'numeric', 'min:1'],
            'convenienceFee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = User::findOrFail($validated['userId']);
        $bicycle = Bicycle::findOrFail($validated['bicycleId']);

        if ($bicycle->status !== 'available') {
            return response()->json([
                'message' => 'Bicycle is not available for rental',
            ], 422);
        }

        $totalAmount = $validated['amount'] + ($validated['convenienceFee'] ?? 0);
        $paymentReference = PayMongoService::generatePaymentReference();

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'userId' => $user->id,
                'bicycleId' => $bicycle->id,
                'paymentReference' => $paymentReference,
                'paymentMethod' => $validated['paymentMethod'],
                'amount' => $validated['amount'],
                'convenienceFee' => $validated['convenienceFee'] ?? 0,
                'totalAmount' => $totalAmount,
                'currency' => 'PHP',
                'status' => 'pending',
                'billingInfo' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '',
                ],
                'metadata' => [
                    'rental_duration_hours' => $validated['rentalDurationHours'],
                    'bicycle_name' => $bicycle->name,
                    'bicycle_serial' => $bicycle->serialNumber,
                ],
            ]);

            // Create checkout session for redirect-based payments
            $redirectMethods = ['gcash', 'maya', 'grabpay', 'online_banking'];
            if (in_array($validated['paymentMethod'], $redirectMethods)) {
                $checkout = $this->payMongoService->createCheckoutSession([
                    'itemName' => "Bicycle Rental - {$bicycle->name}",
                    'amount' => $totalAmount,
                    'currency' => 'PHP',
                    'paymentMethods' => [$validated['paymentMethod']],
                    'successUrl' => route('admin.payments.success', $payment->id),
                    'cancelUrl' => route('admin.payments.cancel', $payment->id),
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'payment_reference' => $paymentReference,
                        'bicycle_id' => $bicycle->id,
                        'user_id' => $user->id,
                        'rental_duration_hours' => $validated['rentalDurationHours'],
                    ],
                    'description' => "Bicycle Rental: {$bicycle->name} for {$validated['rentalDurationHours']} hour(s)",
                ]);

                $payment->update([
                    'paymongoPaymentId' => $checkout['id'],
                    'paymongoCheckoutUrl' => $checkout['attributes']['checkout_url'],
                ]);
            } else {
                // Card payment - create payment intent
                $intent = $this->payMongoService->createPaymentIntent([
                    'amount' => $totalAmount,
                    'currency' => 'PHP',
                    'paymentMethods' => ['card'],
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'payment_reference' => $paymentReference,
                        'bicycle_id' => $bicycle->id,
                        'user_id' => $user->id,
                        'rental_duration_hours' => $validated['rentalDurationHours'],
                    ],
                    'description' => "Bicycle Rental: {$bicycle->name} for {$validated['rentalDurationHours']} hour(s)",
                ]);

                $payment->update([
                    'paymongoPaymentId' => $intent['id'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment initiated successfully',
                'payment' => $payment->load(['user', 'bicycle']),
                'checkoutUrl' => $payment->paymongoCheckoutUrl ?? null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Payment creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to initiate payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Payment $payment): Response
    {
        $payment->load(['user', 'rental', 'bicycle', 'refund']);
        return response()->view('admin.payments.show', compact('payment'));
    }

    public function success(Payment $payment): Response
    {
        $payment->load(['user', 'rental', 'bicycle']);
        return response()->view('admin.payments.success', compact('payment'));
    }

    public function cancel(Payment $payment): Response
    {
        $payment->load(['user', 'bicycle']);
        return response()->view('admin.payments.cancel', compact('payment'));
    }

    public function verify(Payment $payment): JsonResponse
    {
        if (!$payment->paymongoPaymentId) {
            return response()->json(['message' => 'No PayMongo payment ID found'], 400);
        }

        try {
            $redirectMethods = ['gcash', 'maya', 'grabpay', 'online_banking'];
            $paymongoData = in_array($payment->paymentMethod, $redirectMethods)
                ? $this->payMongoService->retrieveCheckoutSession($payment->paymongoPaymentId)
                : $this->payMongoService->retrievePaymentIntent($payment->paymongoPaymentId);

            $paymongoStatus = $paymongoData['attributes']['status'] ?? null;

            $newStatus = match ($paymongoStatus) {
                'paid' => 'paid',
                'processing' => 'processing',
                'failed' => 'failed',
                'expired' => 'expired',
                default => $payment->status,
            };

            if ($newStatus !== $payment->status) {
                $payment->update([
                    'status' => $newStatus,
                    'paymentDetails' => $paymongoData['attributes'],
                    'paidAt' => $newStatus === 'paid' ? now() : $payment->paidAt,
                ]);

                // If paid, create rental and unlock bike
                if ($newStatus === 'paid' && !$payment->rentalId) {
                    $this->rentalService->createRentalFromPaidPayment($payment);
                }
            }

            return response()->json([
                'message' => 'Payment status verified',
                'status' => $newStatus,
                'payment' => $payment->fresh(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Verification failed: ' . $e->getMessage()], 500);
        }
    }

}
