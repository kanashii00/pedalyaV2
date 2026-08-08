<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'userId',
        'title',
        'message',
        'type',
        'read',
        'bicycleId',
        'rentalId',
        'incidentId',
        'readAt',
        'sentBy',
    ];

    protected function casts(): array
    {
        return [
            'read' => 'boolean',
            'created_at' => 'datetime',
            'readAt' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class, 'bicycleId');
    }
}
