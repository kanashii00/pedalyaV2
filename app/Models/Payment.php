<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'rentalId',
        'userId',
        'bicycleId',
        'paymentReference',
        'paymongoPaymentId',
        'paymongoCheckoutUrl',
        'paymentMethod',
        'amount',
        'convenienceFee',
        'totalAmount',
        'currency',
        'status',
        'paymentDetails',
        'billingInfo',
        'paidAt',
        'expiredAt',
        'failureReason',
        'webhookSignature',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'convenienceFee' => 'decimal:2',
            'totalAmount' => 'decimal:2',
            'paymentDetails' => 'array',
            'billingInfo' => 'array',
            'paidAt' => 'datetime',
            'expiredAt' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class, 'rentalId');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycleId');
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class, 'paymentId');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'expired', 'cancelled']);
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'paid' => 'success',
            'pending', 'processing' => 'warning',
            'failed', 'expired', 'cancelled' => 'danger',
            'refunded' => 'info',
            default => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'processing' => 'Processing',
            'failed' => 'Failed',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            default => ucfirst($this->status),
        };
    }

    public function getPaymentMethodLabel(): string
    {
        return match ($this->paymentMethod) {
            'gcash' => 'GCash',
            'maya' => 'Maya',
            'grabpay' => 'GrabPay',
            'card' => 'Credit/Debit Card',
            'online_banking' => 'Online Banking',
            default => ucfirst(str_replace('_', ' ', $this->paymentMethod)),
        };
    }
}