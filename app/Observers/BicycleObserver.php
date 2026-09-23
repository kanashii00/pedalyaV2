<?php

namespace App\Observers;

use App\Models\Bicycle;
use App\Services\CacheRegistry;

/**
 * Invalidates the bicycle catalog caches whenever catalog-relevant fields
 * change (status, name, model, rate, condition, …), without invalidating on
 * high-frequency telemetry writes (GPS position, battery level, smart-lock
 * state, heartbeats) that would otherwise create a cache invalidation storm.
 */
class BicycleObserver
{
    /** Fields that affect cached catalog data (rent page / API list). */
    private const CATALOG_FIELDS = [
        'status',
        'name',
        'model',
        'serialNumber',
        'description',
        'hourlyRate',
        'condition',
        'addedBy',
        'removedAt',
        'removedBy',
        'totalRentals',
        'totalDistance',
    ];

    public function created(Bicycle $bicycle): void
    {
        CacheRegistry::bumpBicyclesVersion();
    }

    public function updated(Bicycle $bicycle): void
    {
        if ($this->catalogFieldsChanged($bicycle)) {
            CacheRegistry::bumpBicyclesVersion();
        }
    }

    public function deleted(Bicycle $bicycle): void
    {
        CacheRegistry::bumpBicyclesVersion();
    }

    public function restored(Bicycle $bicycle): void
    {
        CacheRegistry::bumpBicyclesVersion();
    }

    private function catalogFieldsChanged(Bicycle $bicycle): bool
    {
        return array_intersect(array_keys($bicycle->getDirty()), self::CATALOG_FIELDS) !== [];
    }
}