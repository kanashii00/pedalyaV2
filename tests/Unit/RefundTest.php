<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RefundTest extends TestCase
{
    use RefreshDatabase;

    private int $userId;
    private int $paymentId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test-' . uniqid() . '@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'verified' => true,
        ]);
        $this->userId = $user->id;

        $payment = Payment::create([
            'userId' => $user->id,
            'paymentReference' => 'PMT-' . uniqid(),
            'paymentMethod' => 'gcash',
            'amount' => 200.00,
            'totalAmount' => 200.00,
            'currency' => 'PHP',
            'status' => 'paid',
        ]);
        $this->paymentId = $payment->id;
    }

    public function test_get_status_color_succeeded(): void
    {
        $refund = Refund::create([
            'paymentId' => $this->paymentId,
            'userId' => $this->userId,
            'amount' => 100.00,
            'currency' => 'PHP',
            'reason' => 'other',
            'status' => 'succeeded',
            'refundReference' => 'REF-' . uniqid(),
        ]);

        $this->assertSame('success', $refund->getStatusColor());
    }

    public function test_get_status_color_pending(): void
    {
        $refund = Refund::create([
            'paymentId' => $this->paymentId,
            'userId' => $this->userId,
            'amount' => 100.00,
            'currency' => 'PHP',
            'reason' => 'other',
            'status' => 'pending',
            'refundReference' => 'REF-' . uniqid(),
        ]);

        $this->assertSame('warning', $refund->getStatusColor());
    }

    public function test_get_status_color_processing(): void
    {
        $refund = Refund::create([
            'paymentId' => $this->paymentId,
            'userId' => $this->userId,
            'amount' => 100.00,
            'currency' => 'PHP',
            'reason' => 'other',
            'status' => 'processing',
            'refundReference' => 'REF-' . uniqid(),
        ]);

        $this->assertSame('warning', $refund->getStatusColor());
    }

    public function test_get_status_color_failed(): void
    {
        $refund = Refund::create([
            'paymentId' => $this->paymentId,
            'userId' => $this->userId,
            'amount' => 100.00,
            'currency' => 'PHP',
            'reason' => 'other',
            'status' => 'failed',
            'refundReference' => 'REF-' . uniqid(),
        ]);

        $this->assertSame('danger', $refund->getStatusColor());
    }

    public function test_get_status_color_cancelled(): void
    {
        $refund = Refund::create([
            'paymentId' => $this->paymentId,
            'userId' => $this->userId,
            'amount' => 100.00,
            'currency' => 'PHP',
            'reason' => 'other',
            'status' => 'cancelled',
            'refundReference' => 'REF-' . uniqid(),
        ]);

        $this->assertSame('danger', $refund->getStatusColor());
    }

    public function test_get_status_label_all_values(): void
    {
        $statuses = [
            'succeeded' => 'Succeeded',
            'pending' => 'Pending',
            'processing' => 'Processing',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];

        foreach ($statuses as $status => $expected) {
            $refund = Refund::create([
                'paymentId' => $this->paymentId,
                'userId' => $this->userId,
                'amount' => 100.00,
                'currency' => 'PHP',
                'reason' => 'other',
                'status' => $status,
                'refundReference' => 'REF-' . uniqid(),
            ]);

            $this->assertSame($expected, $refund->getStatusLabel(), "Failed for status: {$status}");
        }
    }

    public function test_get_reason_label_all_values(): void
    {
        $reasons = [
            'customer_request' => 'Customer Request',
            'duplicate_payment' => 'Duplicate Payment',
            'fraudulent' => 'Fraudulent',
            'service_not_provided' => 'Service Not Provided',
            'other' => 'Other',
        ];

        foreach ($reasons as $reason => $expected) {
            $refund = Refund::create([
                'paymentId' => $this->paymentId,
                'userId' => $this->userId,
                'amount' => 100.00,
                'currency' => 'PHP',
                'reason' => $reason,
                'status' => 'pending',
                'refundReference' => 'REF-' . uniqid(),
            ]);

            $this->assertSame($expected, $refund->getReasonLabel(), "Failed for reason: {$reason}");
        }
    }

    public function test_refund_belongs_to_payment(): void
    {
        $refund = Refund::create([
            'paymentId' => $this->paymentId,
            'userId' => $this->userId,
            'amount' => 50.00,
            'currency' => 'PHP',
            'reason' => 'other',
            'status' => 'pending',
            'refundReference' => 'REF-' . uniqid(),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $refund->payment());
    }

    public function test_refund_belongs_to_user(): void
    {
        $refund = Refund::create([
            'paymentId' => $this->paymentId,
            'userId' => $this->userId,
            'amount' => 50.00,
            'currency' => 'PHP',
            'reason' => 'other',
            'status' => 'pending',
            'refundReference' => 'REF-' . uniqid(),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $refund->user());
    }
}
