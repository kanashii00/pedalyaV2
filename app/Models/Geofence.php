<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Geofence extends Model
{
    use HasFactory;

    protected $table = 'geofences';

    protected $fillable = [
        'name',
        'centerLat',
        'centerLng',
        'radius',
        'isActive',
        'alertEnabled',
        'warningThreshold',
    ];

    protected function casts(): array
    {
        return [
            'centerLat' => 'decimal:7',
            'centerLng' => 'decimal:7',
            'radius' => 'decimal:2',
            'isActive' => 'boolean',
            'alertEnabled' => 'boolean',
            'warningThreshold' => 'decimal:2',
        ];
    }

    public function breaches(): HasMany
    {
        return $this->hasMany(GeofenceBreach::class, 'geofenceId');
    }
}
