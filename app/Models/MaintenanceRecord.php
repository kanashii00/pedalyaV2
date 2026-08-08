<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'maintenance_records';

    protected $fillable = [
        'bicycleId',
        'bicycleName',
        'type',
        'description',
        'severity',
        'estimatedCost',
        'actualCost',
        'technician',
        'scheduledDate',
        'completedDate',
        'status',
        'notes',
        'createdBy',
    ];

    protected function casts(): array
    {
        return [
            'scheduledDate' => 'datetime',
            'completedDate' => 'datetime',
            'estimatedCost' => 'decimal:2',
            'actualCost' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycleId');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createdBy');
    }
}
