<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceStatus extends Model
{
    use HasFactory;

    protected $table = 'device_status';

    protected $fillable = [
        'bicycleId',
        'gps',
        'accelerometer',
        'battery',
        'lockStatus',
        'lcdTimer',
        'rfid',
        'deviceVersion',
        'uptime',
        'command',
        'commandIssuedBy',
        'commandIssuedAt',
        'type',
        'eventTimestamp',
        'status',
        'params',
        'issuedByName',
        'sentAt',
        'acknowledgedAt',
        'completedAt',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'gps' => 'array',
            'accelerometer' => 'array',
            'battery' => 'array',
            'lcdTimer' => 'array',
            'params' => 'array',
            'created_at' => 'datetime',
            'commandIssuedAt' => 'datetime',
            'eventTimestamp' => 'datetime',
            'sentAt' => 'datetime',
            'acknowledgedAt' => 'datetime',
            'completedAt' => 'datetime',
            'uptime' => 'integer',
        ];
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycleId');
    }

    public function commandIssuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commandIssuedBy');
    }
}
