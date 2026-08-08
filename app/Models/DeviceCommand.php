<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCommand extends Model
{
    use HasFactory;

    protected $table = 'device_commands';

    protected $fillable = [
        'bicycleId',
        'command',
        'params',
        'status',
        'issuedBy',
        'executedAt',
        'response',
    ];

    protected function casts(): array
    {
        return [
            'params' => 'array',
            'executedAt' => 'datetime',
        ];
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycleId');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issuedBy');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
