<?php

namespace App\Services;

use App\Models\Bicycle;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Cache access layer for rider-facing catalog + summary data.
 *
 * Only static / slowly-changing data is cached here:
 *   - the available bicycle catalog (rent page + dashboard maps)
 *   - per-bicycle catalog details (no live telemetry, GPS or lock state)
 *   - per-user dashboard summaries (totals + unread count)
 *
 * Live data (active rental, rentals history, GPS position, smart-lock state)
 * is intentionally never cached and always read from the database.
 */
class RiderCacheService
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    public function availableBicycles(): Collection
    {
        $key = CacheRegistry::availableBicyclesKey(false);

        return Cache::remember($key, CacheRegistry::TTL_AVAILABLE_BICYCLES, function () {
            return Bicycle::available()
                ->orderBy('batteryLevel', 'desc')
                ->get();
        });
    }

    public function locatedAvailableBicycles(): Collection
    {
        $key = CacheRegistry::availableBicyclesKey(true);

        return Cache::remember($key, CacheRegistry::TTL_AVAILABLE_BICYCLES, function () {
            return Bicycle::available()
                ->whereNotNull('currentLat')
                ->whereNotNull('currentLng')
                ->get();
        });
    }

    /**
     * Cached catalog entity. Live relations (latestTelemetry, latestGpsLog,
     * lock state, position) are loaded separately by the caller so cached
     * data never goes stale for real-time consumers.
     */
    public function bicycleCatalog(int $bicycleId): ?Bicycle
    {
        $key = CacheRegistry::bicycleCatalogKey($bicycleId);

        return Cache::remember($key, CacheRegistry::TTL_BICYCLE_CATALOG, function () use ($bicycleId) {
            return Bicycle::find($bicycleId);
        });
    }

    /**
     * Per-user dashboard summary. Invalidation is driven by version bumps
     * whenever the user's rentals, notifications or profile-affecting fields
     * change (observers), preventing cross-user data leakage by scoping the
     * key with the user id.
     *
     * @return array{totalRentals:int, totalSpent:float, unreadCount:int}
     */
    public function summary(int $userId): array
    {
        $key = CacheRegistry::userSummaryKey($userId);

        return Cache::remember($key, CacheRegistry::TTL_USER_SUMMARY, function () use ($userId) {
            $user = User::query()->find($userId);

            return [
                'totalRentals' => (int) ($user?->totalRentals ?? 0),
                'totalSpent' => (float) ($user?->totalSpent ?? 0),
                'unreadCount' => $this->notificationService->getUnreadCount($userId),
            ];
        });
    }
}