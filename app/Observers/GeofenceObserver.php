<?php

namespace App\Observers;

use App\Models\Geofence;
use App\Services\CacheRegistry;

class GeofenceObserver
{
    public function created(Geofence $geofence): void
    {
        CacheRegistry::bumpGeofenceVersion();
    }

    public function updated(Geofence $geofence): void
    {
        CacheRegistry::bumpGeofenceVersion();
    }

    public function deleted(Geofence $geofence): void
    {
        CacheRegistry::bumpGeofenceVersion();
    }

    public function restored(Geofence $geofence): void
    {
        CacheRegistry::bumpGeofenceVersion();
    }
}