<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    public const TYPE_RENTAL = 'rental';
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_USAGE = 'usage';
    public const TYPE_MAINTENANCE = 'maintenance';

    protected $table = 'reports';

    protected $fillable = [
        'creator_id',
        'type',
        'title',
        'filters',
        'summary',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'summary' => 'array',
            'data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
