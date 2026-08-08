<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accident extends Model
{
    use HasFactory;

    protected $table = 'accidents';

    protected $fillable = [
        'bicycleId',
        'type',
        'severity',
        'description',
        'gpsLocation',
        'accelerometerData',
        'impactForce',
        'imageUrl',
        'acknowledged',
        'alertSent',
        'reportedBy',
        'status',
        'breachDistance',
        'breachDirection',
        'actionTaken',
        'warningLevel',
        'distanceFromBoundary',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'gpsLocation' => 'array',
            'accelerometerData' => 'array',
            'location' => 'array',
            'acknowledged' => 'boolean',
            'alertSent' => 'boolean',
            'impactForce' => 'decimal:2',
            'breachDistance' => 'decimal:2',
            'distanceFromBoundary' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycleId');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportedBy');
    }
}
