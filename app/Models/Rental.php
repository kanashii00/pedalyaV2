<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PENDING = 'pending';

    public const STATUS_AWAITING_RETURN = 'awaiting_return';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_EXPIRED = 'expired';

    public const CONDITION_GOOD = 'good';

    public const CONDITION_FAIR = 'fair';

    public const CONDITION_DAMAGED = 'damaged';

    public const CONDITION_NEEDS_MAINTENANCE = 'needs_maintenance';

    protected $table = 'rentals';

    protected $fillable = [
        'rentalId',
        'bicycleId',
        'bicycleName',
        'bicycleSerial',
        'riderId',
        'riderName',
        'riderEmail',
        'status',
        'startTime',
        'endTime',
        'expectedEndTime',
        'startLocation',
        'endLocation',
        'ratePerHour',
        'totalFee',
        'durationMinutes',
        'durationFormatted',
        'chargedHours',
        'totalDistance',
        'paymentStatus',
        'paidAt',
        'isOverdue',
        'overdueAt',
        'paymentMethod',
        'paymentReference',
        'notes',
        'cancelledBy',
        'cancelReason',
        'approvedBy',
        'approvedAt',
        'warningSentAt',
        'returnRequestedAt',
        'returnCondition',
        'returnInspectedBy',
        'returnProcessedAt',
        'returnNote',
        'overdueFee',
        'finalFee',
    ];

    protected function casts(): array
    {
        return [
            'startTime' => 'datetime',
            'endTime' => 'datetime',
            'expectedEndTime' => 'datetime',
            'startLocation' => 'array',
            'endLocation' => 'array',
            'approvedAt' => 'datetime',
            'overdueAt' => 'datetime',
            'warningSentAt' => 'datetime',
            'returnRequestedAt' => 'datetime',
            'returnProcessedAt' => 'datetime',
            'paidAt' => 'datetime',
            'isOverdue' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'ratePerHour' => 'decimal:2',
            'totalFee' => 'decimal:2',
            'totalDistance' => 'decimal:2',
            'durationMinutes' => 'integer',
            'chargedHours' => 'integer',
        ];
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycleId');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'riderId');
    }
}
