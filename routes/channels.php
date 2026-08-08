<?php

use App\Models\Bicycle;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('gps.{bicycleId}', function (User $user, int $bicycleId) {
    if ($user->isAdmin()) {
        return true;
    }

    $bicycle = Bicycle::find($bicycleId);

    return $bicycle && (int) $bicycle->currentRider === $user->id;
});

Broadcast::channel('geofence-alerts', function (User $user) {
    return $user->isAdmin();
});
