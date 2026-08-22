<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_RIDER = 'rider';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_BLACKLISTED = 'blacklisted';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'studentId',
        'email',
        'password',
        'role',
        'status',
        'verified',
        'blacklistReason',
        'idUploaded',
        'idVerification',
        'profilePicture',
        'totalRentals',
        'totalSpent',
        'phoneNumber',
        'address',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'idVerification' => 'array',
            'verified' => 'boolean',
            'idUploaded' => 'boolean',
            'totalRentals' => 'integer',
            'totalSpent' => 'decimal:2',
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'riderId');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'userId');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'userId');
    }

    public function currentRental(): HasOne
    {
        return $this->hasOne(Rental::class, 'riderId')
            ->where('status', 'active')
            ->latestOfMany('startTime');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isRider(): bool
    {
        return $this->role === self::ROLE_RIDER;
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(mb_substr($word, 0, 1));
        }
        return $initials;
    }
}
