<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeofenceBreach extends Model
{
    use HasFactory;

    protected $table = 'geofence_breaches';

    protected $fillable = [
        'bicycleId',
        'geofenceId',
        'lat',
        'lng',
        'distance',
        'acknowledged',
        'resolvedAt',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'distance' => 'decimal:2',
            'acknowledged' => 'boolean',
            'resolvedAt' => 'datetime',
        ];
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycleId');
    }

    public function geofence(): BelongsTo
    {
        return $this->belongsTo(Geofence::class, 'geofenceId');
    }
}
