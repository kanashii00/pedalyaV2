<?php

namespace App\Services;

use App\Models\Geofence;

class GeofenceService
{
    public function activeGeofence(): ?Geofence
    {
        return Geofence::where('isActive', true)->first();
    }

    public function getConfig(): array
    {
        $geofence = $this->activeGeofence();

        if ($geofence) {
            return [
                'centerLat' => (float) $geofence->centerLat,
                'centerLng' => (float) $geofence->centerLng,
                'radius' => (float) $geofence->radius,
                'alertEnabled' => (bool) $geofence->alertEnabled,
                'warningThreshold' => $geofence->warningThreshold !== null
                    ? (float) $geofence->warningThreshold
                    : (float) config('services.geofence.warning_threshold', 100),
                'id' => $geofence->id,
            ];
        }

        return [
            'centerLat' => (float) config('services.geofence.center_lat', 7.0990),
            'centerLng' => (float) config('services.geofence.center_lng', 125.6470),
            'radius' => (float) config('services.geofence.default_radius', 500),
            'alertEnabled' => true,
            'warningThreshold' => (float) config('services.geofence.warning_threshold', 100),
            'id' => null,
        ];
    }

    public function checkPointInGeofence(float $lat, float $lng): array
    {
        $config = $this->getConfig();

        return $this->checkPoint($lat, $lng, $config);
    }

    public function checkPoint(float $lat, float $lng, array $centerConfig): array
    {
        $centerLat = $centerConfig['centerLat'] ?? $centerConfig['lat'] ?? 0;
        $centerLng = $centerConfig['centerLng'] ?? $centerConfig['lng'] ?? 0;
        $radius = (float) ($centerConfig['radius'] ?? 500);

        $distance = $this->haversineDistance($lat, $lng, $centerLat, $centerLng);
        $inside = $distance <= $radius;
        $distanceToBoundary = abs($distance - $radius);
        $warningThreshold = (float) ($centerConfig['warningThreshold'] ?? config('services.geofence.warning_threshold', 100));

        $level = 'safe';
        if (!$inside) {
            $level = 'breach';
        } elseif ($distance >= $radius - ($warningThreshold * 0.5)) {
            $level = 'warning';
        } elseif ($distance >= $radius - $warningThreshold) {
            $level = 'approaching';
        }

        return [
            'inside' => $inside,
            'distance' => $distance,
            'distanceToBoundary' => $distanceToBoundary,
            'distanceOutside' => $inside ? 0 : $distance - $radius,
            'geofenceRadius' => $radius,
            'center' => ['lat' => $centerLat, 'lng' => $centerLng],
            'level' => $level,
            'warning' => $level !== 'safe',
        ];
    }

    public function calculateDistanceToBoundary(float $lat, float $lng, array $center, float $radius): array
    {
        $distanceToCenter = $this->haversineDistance($lat, $lng, $center['lat'], $center['lng']);
        $distanceToBoundary = abs($distanceToCenter - $radius);

        return [
            'distanceToCenter' => $distanceToCenter,
            'distanceToBoundary' => $distanceToBoundary,
            'inside' => $distanceToCenter <= $radius,
            'ratio' => $radius > 0 ? round($distanceToCenter / $radius, 4) : 0,
        ];
    }

    public function generateGeofenceWarning(float $distance, float $threshold = 100): array
    {
        $breach = $distance > $threshold;

        if ($distance <= $threshold * 0.5) {
            $level = 'safe';
            $warning = false;
            $message = 'You are within the safe zone.';
            $color = 'green';
        } elseif ($distance <= $threshold * 0.8) {
            $level = 'approaching';
            $warning = true;
            $message = 'You are approaching the boundary.';
            $color = 'yellow';
        } elseif ($distance <= $threshold) {
            $level = 'warning';
            $warning = true;
            $message = 'You are near the boundary limit.';
            $color = 'orange';
        } else {
            $level = 'breach';
            $warning = true;
            $message = 'You have exceeded the geofence boundary!';
            $color = 'red';
        }

        return [
            'level' => $level,
            'warning' => $warning,
            'message' => $message,
            'color' => $color,
            'distance' => $distance,
            'breach' => $breach,
        ];
    }

    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $n = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $latI = (float) $polygon[$i]['lat'];
            $lngI = (float) $polygon[$i]['lng'];
            $latJ = (float) $polygon[$j]['lat'];
            $lngJ = (float) $polygon[$j]['lng'];

            $intersects = ($lngI > $lng) !== ($lngJ > $lng) &&
                $lat < ($latJ - $latI) * ($lng - $lngI) / ($lngJ - $lngI) + $latI;

            if ($intersects) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    public function calculateBoundingBox(float $lat, float $lng, float $radiusMeters): array
    {
        $earthRadius = 6371000;

        $latDelta = rad2deg($radiusMeters / $earthRadius);
        $lngDelta = rad2deg($radiusMeters / ($earthRadius * cos(deg2rad($lat))));

        return [
            'minLat' => $lat - $latDelta,
            'maxLat' => $lat + $latDelta,
            'minLng' => $lng - $lngDelta,
            'maxLng' => $lng + $lngDelta,
        ];
    }
}
