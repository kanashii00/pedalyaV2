<?php

namespace Tests\Unit;

use App\Models\Bicycle;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_status_helpers(): void
    {
        $payment = $this->makePayment(['status' => 'paid']);
        $this->assertTrue($payment->isPaid());
        $this->assertFalse($payment->isPending());
        $this->assertFalse($payment->isFailed());

        $this->assertTrue($this->makePayment(['status' => 'pending'])->isPending());
        $this->assertTrue($this->makePayment(['status' => 'processing'])->isProcessing());
        $this->assertTrue($this->makePayment(['status' => 'failed'])->isFailed());
        $this->assertTrue($this->makePayment(['status' => 'expired'])->isFailed());
        $this->assertTrue($this->makePayment(['status' => 'refunded'])->isRefunded());
    }

    public function test_get_status_color_and_label(): void
    {
        $this->assertSame('success', $this->makePayment(['status' => 'paid'])->getStatusColor());
        $this->assertSame('warning', $this->makePayment(['status' => 'pending'])->getStatusColor());
        $this->assertSame('danger', $this->makePayment(['status' => 'failed'])->getStatusColor());

        $this->assertSame('Paid', $this->makePayment(['status' => 'paid'])->getStatusLabel());
        $this->assertSame('Pending', $this->makePayment(['status' => 'pending'])->getStatusLabel());
        $this->assertSame('Refunded', $this->makePayment(['status' => 'refunded'])->getStatusLabel());
    }

    public function test_get_payment_method_label(): void
    {
        $this->assertSame('GCash', $this->makePayment(['paymentMethod' => 'gcash'])->getPaymentMethodLabel());
        $this->assertSame('Maya', $this->makePayment(['paymentMethod' => 'maya'])->getPaymentMethodLabel());
        $this->assertSame('GrabPay', $this->makePayment(['paymentMethod' => 'grabpay'])->getPaymentMethodLabel());
        $this->assertSame('Credit/Debit Card', $this->makePayment(['paymentMethod' => 'card'])->getPaymentMethodLabel());
        $this->assertSame('Cash on delivery', $this->makePayment(['paymentMethod' => 'cash_on_delivery'])->getPaymentMethodLabel());
    }

    public function test_relations_cast_arrays(): void
    {
        $rider = $this->makeRider();
        $bike = $this->makeBicycle();
        $rental = $this->makeRental(['riderId' => $rider->id, 'bicycleId' => $bike->id]);

        $payment = $this->makePayment([
            'userId' => $rider->id,
            'bicycleId' => $bike->id,
            'rentalId' => $rental->id,
            'paymentDetails' => ['method' => 'gcash'],
            'billingInfo' => ['name' => 'Test'],
        ]);

        $this->assertSame($rental->id, $payment->rental->id);
        $this->assertSame($rider->id, $payment->user->id);
        $this->assertSame($bike->id, $payment->bicycle->id);
        $this->assertSame(['method' => 'gcash'], $payment->paymentDetails);
        $this->assertSame(['name' => 'Test'], $payment->billingInfo);
    }
}

class RefundModelTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_get_status_color_and_label(): void
    {
        $this->assertSame('success', $this->makeRefund(['status' => 'succeeded'])->getStatusColor());
        $this->assertSame('warning', $this->makeRefund(['status' => 'pending'])->getStatusColor());
        $this->assertSame('danger', $this->makeRefund(['status' => 'failed'])->getStatusColor());

        $this->assertSame('Succeeded', $this->makeRefund(['status' => 'succeeded'])->getStatusLabel());
        $this->assertSame('Failed', $this->makeRefund(['status' => 'failed'])->getStatusLabel());
        $this->assertSame('Custom', $this->makeRefund(['status' => 'custom'])->getStatusLabel());
    }

    public function test_get_reason_label(): void
    {
        $this->assertSame('Customer Request', $this->makeRefund(['reason' => 'customer_request'])->getReasonLabel());
        $this->assertSame('Duplicate Payment', $this->makeRefund(['reason' => 'duplicate_payment'])->getReasonLabel());
        $this->assertSame('Other Reason', $this->makeRefund(['reason' => 'other_reason'])->getReasonLabel());
    }

    protected function makeRefund(array $overrides): Refund
    {
        return Refund::create(array_merge([
            'refundReference' => 'REF-TEST-123',
            'amount' => 50.00,
            'currency' => 'PHP',
            'status' => 'pending',
            'reason' => 'customer_request',
        ], $overrides));
    }
}

class UserModelTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_role_and_status_helpers(): void
    {
        $admin = $this->makeAdmin();
        $rider = $this->makeRider();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isRider());
        $this->assertTrue($rider->isRider());
        $this->assertTrue($rider->isActive());
    }

    public function test_get_initials_attribute(): void
    {
        $user = $this->makeRider(['name' => 'Juan dela Cruz']);
        $this->assertSame('JD', $user->getInitialsAttribute());

        $single = $this->makeRider(['name' => 'Maria']);
        $this->assertSame('M', $single->getInitialsAttribute());
    }

    public function test_password_is_hashed(): void
    {
        $user = $this->makeRider(['password' => 'plainpassword']);

        $this->assertNotSame('plainpassword', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('plainpassword', $user->password));
    }
}

class BicycleModelTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_scopes(): void
    {
        $this->makeBicycle(['status' => Bicycle::STATUS_AVAILABLE]);
        $this->makeBicycle(['status' => Bicycle::STATUS_RENTED]);
        $this->makeBicycle(['status' => Bicycle::STATUS_MAINTENANCE]);

        $this->assertSame(1, Bicycle::available()->count());
        $this->assertSame(1, Bicycle::rented()->count());
        $this->assertSame(1, Bicycle::inMaintenance()->count());
    }

    public function test_zone_attribute(): void
    {
        $bike = $this->makeBicycle();

        $zone = $bike->zone;

        $this->assertArrayHasKey('inside', $zone);
        $this->assertSame('unknown', $zone['level']);
    }
}
