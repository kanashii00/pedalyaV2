<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsLog extends Model
{
    use HasFactory;

    protected $table = 'gps_logs';

    protected $fillable = [
        'bicycleId',
        'lat',
        'lng',
        'speed',
        'heading',
        'accuracy',
        'batteryLevel',
        'altitude',
        'satellites',
        'hdop',
        'timestamp',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'speed' => 'decimal:2',
            'heading' => 'decimal:2',
            'accuracy' => 'decimal:2',
            'altitude' => 'decimal:2',
            'hdop' => 'decimal:2',
            'batteryLevel' => 'integer',
            'satellites' => 'integer',
            'timestamp' => 'datetime',
        ];
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycleId');
    }
}
