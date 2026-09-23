<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Central registry of Pedalya cache keys and TTLs.
 *
 * Cache keys are version-tagged with dedicated counters. When the underlying
 * data changes, the affected counter is bumped, which atomically invalidates
 * every dependent key — without relying on wildcard flushing that only some
 * stores (e.g. Redis) support. The same mechanism works on the current driver
 * (database / file / array) and when Redis is introduced later.
 *
 * Live, time-sensitive data is intentionally never cached here: active rental
 * countdowns, GPS positions, smart-lock state, payment/return status and
 * alert/notification freshness are always read from the database.
 */
class CacheRegistry
{
    /** TTLs in seconds. */
    public const TTL_AVAILABLE_BICYCLES = 300;

    public const TTL_BICYCLE_CATALOG = 600;

    public const TTL_GEOFENCE = 600;

    public const TTL_SETTINGS = 1800;

    public const TTL_USER_SUMMARY = 300;

    public const TTL_UNREAD_COUNT = 60;

    /** Version counter keys. */
    private const VERSION_BICYCLES = 'pedalya:version:bicycles';

    private const VERSION_GEOFENCE = 'pedalya:version:geofence';

    private const VERSION_SETTINGS = 'pedalya:version:settings';

    private const PREFIX_USER_VERSION = 'pedalya:version:user';

    public static function bicyclesVersion(): int
    {
        return (int) Cache::get(self::VERSION_BICYCLES, 0);
    }

    public static function bumpBicyclesVersion(): int
    {
        return self::bump(self::VERSION_BICYCLES);
    }

    public static function geofenceVersion(): int
    {
        return (int) Cache::get(self::VERSION_GEOFENCE, 0);
    }

    public static function bumpGeofenceVersion(): int
    {
        return self::bump(self::VERSION_GEOFENCE);
    }

    public static function settingsVersion(): int
    {
        return (int) Cache::get(self::VERSION_SETTINGS, 0);
    }

    public static function bumpSettingsVersion(): int
    {
        return self::bump(self::VERSION_SETTINGS);
    }

    public static function userVersion(int $userId): int
    {
        return (int) Cache::get(self::userVersionKey($userId), 0);
    }

    public static function userVersionKey(int $userId): string
    {
        return self::PREFIX_USER_VERSION.':'.$userId;
    }

    public static function bumpUserVersion(int $userId): void
    {
        self::bump(self::userVersionKey($userId));
    }

    public static function availableBicyclesKey(bool $locatedOnly = false): string
    {
        return 'pedalya:bicycles:available'
            .($locatedOnly ? ':located' : '')
            .':v'.self::bicyclesVersion();
    }

    public static function bicycleCatalogKey(int $bicycleId): string
    {
        return "pedalya:bicycles:catalog:{$bicycleId}:v".self::bicyclesVersion();
    }

    public static function bicycleIndexKey(string $status, int $perPage): string
    {
        $status = $status !== '' ? $status : 'all';

        return "pedalya:bicycles:index:{$status}:{$perPage}:v".self::bicyclesVersion();
    }

    public static function geofenceConfigKey(): string
    {
        return 'pedalya:geofence:config:v'.self::geofenceVersion();
    }

    public static function settingKey(string $key): string
    {
        return "pedalya:settings:{$key}:v".self::settingsVersion();
    }

    public static function userSummaryKey(int $userId): string
    {
        return "pedalya:users:{$userId}:summary:v".self::userVersion($userId);
    }

    public static function unreadCountKey(int $userId): string
    {
        return "pedalya:users:{$userId}:unread_count:v".self::userVersion($userId);
    }

    /**
     * Increment a version counter persistently.
     *
     * Version counters must never expire (otherwise keys could silently start
     * reusing old values), so they are stored with Cache::forever(). The read-
     * then-write is not atomic, but a lost update is harmless in practice: the
     * TTL on the cached data bounds any staleness.
     */
    private static function bump(string $versionKey): int
    {
        $next = (int) Cache::get($versionKey, 0) + 1;

        Cache::forever($versionKey, $next);

        return $next;
    }
}