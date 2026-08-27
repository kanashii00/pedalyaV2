<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bicycle extends Model
{
    use HasFactory, SoftDeletes;

    /** Rental / operational state of the bicycle. */
    public const STATUS_AVAILABLE = 'available';

    public const STATUS_RENTED = 'rented';

    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUS_REMOVED = 'removed';

    /** Physical smart-lock state (independent of status). */
    public const LOCK_LOCKED = 'locked';

    public const LOCK_UNLOCKED = 'unlocked';

    protected $table = 'bicycles';

    protected $fillable = [
        'name',
        'model',
        'serialNumber',
        'description',
        'status',
        'hourlyRate',
        'currentLat',
        'currentLng',
        'batteryLevel',
        'lockStatus',
        'qrCode',
        'totalRentals',
        'totalDistance',
        'condition',
        'lastMaintenanceDate',
        'currentRider',
        'currentRentalId',
        'lastGpsUpdate',
        'lastHeartbeat',
        'lastLockAction',
        'lockActionBy',
        'addedBy',
        'removedAt',
        'removedBy',
    ];

    protected $appends = ['zone'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'lastMaintenanceDate' => 'datetime',
            'lastGpsUpdate' => 'datetime',
            'lastHeartbeat' => 'datetime',
            'lastLockAction' => 'datetime',
            'removedAt' => 'datetime',
            'batteryLevel' => 'integer',
            'totalRentals' => 'integer',
            'hourlyRate' => 'decimal:2',
            'currentLat' => 'decimal:7',
            'currentLng' => 'decimal:7',
            'totalDistance' => 'decimal:2',
        ];
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'bicycleId');
    }

    public function gpsLogs(): HasMany
    {
        return $this->hasMany(GpsLog::class, 'bicycleId');
    }

    public function deviceStatuses(): HasMany
    {
        return $this->hasMany(DeviceStatus::class, 'bicycleId');
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'bicycleId');
    }

    public function pendingCommands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class, 'bicycleId');
    }

    public function latestTelemetry()
    {
        return $this->hasOne(DeviceStatus::class, 'bicycleId')->latestOfMany('eventTimestamp');
    }

    public function latestGpsLog()
    {
        return $this->hasOne(GpsLog::class, 'bicycleId')->latestOfMany('timestamp');
    }

    public function currentRiderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'currentRider');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeRented(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RENTED);
    }

    public function scopeInMaintenance(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }

    public function getZoneAttribute(): array
    {
        return ['inside' => null, 'distance' => null, 'level' => 'unknown', 'warning' => false];
    }
}
