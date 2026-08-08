<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'action',
        'userId',
        'details',
        'timestamp',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            if (!$log->timestamp) {
                $log->timestamp = now();
            }
        });
    }

    public static function record(string $action, ?int $userId = null, array $details = []): self
    {
        return self::create([
            'action' => $action,
            'userId' => $userId ?? auth()->id(),
            'details' => $details,
        ]);
    }

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'timestamp' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
