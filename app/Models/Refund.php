<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use SoftDeletes;

    protected $table = 'refunds';

    protected $fillable = [
        'paymentId',
        'userId',
        'refundReference',
        'paymongoRefundId',
        'amount',
        'currency',
        'reason',
        'reasonDetails',
        'status',
        'paymongoResponse',
        'processedAt',
        'failureReason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paymongoResponse' => 'array',
            'processedAt' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'paymentId');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'succeeded' => 'success',
            'pending', 'processing' => 'warning',
            'failed', 'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'succeeded' => 'Succeeded',
            'pending' => 'Pending',
            'processing' => 'Processing',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getReasonLabel(): string
    {
        return match ($this->reason) {
            'customer_request' => 'Customer Request',
            'duplicate_payment' => 'Duplicate Payment',
            'fraudulent' => 'Fraudulent',
            'service_not_provided' => 'Service Not Provided',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->reason)),
        };
    }
}