<?php

namespace App\Services;

use App\Models\Geofence;

class GeofenceService
{
    public function __construct(
        private GeofenceGeometryHelper $geometry
    ) {}

    public function activeGeofence(): ?Geofence
    {
        return Geofence::where('isActive', true)->first();
    }

    public const SHAPE_CIRCLE = 'circle';
    public const SHAPE_OVAL_H = 'oval_h';
    public const SHAPE_OVAL_V = 'oval_v';
    public const SHAPE_RECTANGLE = 'rectangle';
    public const SHAPE_POLYGON = 'polygon';

    public function getConfig(): array
    {
        $geofence = $this->activeGeofence();

        if ($geofence) {
            return $this->configFromGeofence($geofence);
        }

        return [
            'centerLat' => (float) config('services.geofence.center_lat', 7.0990),
            'centerLng' => (float) config('services.geofence.center_lng', 125.6470),
            'radius' => (float) config('services.geofence.default_radius', 500),
            'shapeType' => self::SHAPE_CIRCLE,
            'width' => null,
            'height' => null,
            'rotation' => null,
            'points' => [],
            'alertEnabled' => true,
            'warningThreshold' => (float) config('services.geofence.warning_threshold', 100),
            'id' => null,
        ];
    }

    public function configFromGeofence(Geofence $geofence): array
    {
        return [
            'centerLat' => (float) $geofence->centerLat,
            'centerLng' => (float) $geofence->centerLng,
            'radius' => (float) $geofence->radius,
            'shapeType' => $geofence->shapeType ?? self::SHAPE_CIRCLE,
            'width' => $geofence->width !== null ? (float) $geofence->width : null,
            'height' => $geofence->height !== null ? (float) $geofence->height : null,
            'rotation' => $geofence->rotation !== null ? (float) $geofence->rotation : 0.0,
            'points' => is_array($geofence->points) ? $geofence->points : [],
            'alertEnabled' => (bool) $geofence->alertEnabled,
            'warningThreshold' => $geofence->warningThreshold !== null
                ? (float) $geofence->warningThreshold
                : (float) config('services.geofence.warning_threshold', 100),
            'id' => $geofence->id,
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
        $shapeType = $centerConfig['shapeType'] ?? self::SHAPE_CIRCLE;

        $distance = $this->haversineDistance($lat, $lng, $centerLat, $centerLng);
        $inside = $this->isInside($lat, $lng, $centerConfig);
        $distanceToBoundary = $this->distanceToBoundary($lat, $lng, $centerConfig);
        $warningThreshold = (float) ($centerConfig['warningThreshold'] ?? config('services.geofence.warning_threshold', 100));

        $level = 'safe';
        if (!$inside) {
            $level = 'breach';
        } elseif ($distanceToBoundary <= $warningThreshold * 0.5) {
            $level = 'warning';
        } elseif ($distanceToBoundary <= $warningThreshold) {
            $level = 'approaching';
        }

        return [
            'inside' => $inside,
            'distance' => $distance,
            'distanceToBoundary' => $distanceToBoundary,
            'distanceOutside' => $inside ? 0 : $distanceToBoundary,
            'geofenceRadius' => $radius,
            'shapeType' => $shapeType,
            'center' => ['lat' => $centerLat, 'lng' => $centerLng],
            'level' => $level,
            'warning' => $level !== 'safe',
        ];
    }

    /**
     * Determine whether a point falls inside the configured geofence shape.
     */
    public function isInside(float $lat, float $lng, array $config): bool
    {
        $shapeType = $config['shapeType'] ?? self::SHAPE_CIRCLE;

        return match ($shapeType) {
            self::SHAPE_POLYGON => $this->isInsidePolygon($lat, $lng, $config),
            self::SHAPE_RECTANGLE => $this->geometry->isInRotatedRectangle($lat, $lng, $config),
            self::SHAPE_OVAL_H, self::SHAPE_OVAL_V => $this->geometry->isInEllipse($lat, $lng, $config),
            default => $this->isInsideCircle($lat, $lng, $config),
        };
    }

    private function isInsidePolygon(float $lat, float $lng, array $config): bool
    {
        $points = is_array($config['points'] ?? null) ? $config['points'] : [];

        if (count($points) >= 3) {
            return $this->isPointInPolygon($lat, $lng, $points);
        }

        // Fall back to a circle based on the first point distance if malformed.
        return $this->haversineDistance($lat, $lng, (float) ($config['centerLat'] ?? 0), (float) ($config['centerLng'] ?? 0)) <= (float) ($config['radius'] ?? 500);
    }

    private function isInsideCircle(float $lat, float $lng, array $config): bool
    {
        $centerLat = $config['centerLat'] ?? $config['lat'] ?? 0;
        $centerLng = $config['centerLng'] ?? $config['lng'] ?? 0;
        $radius = (float) ($config['radius'] ?? 500);

        return $this->haversineDistance($lat, $lng, $centerLat, $centerLng) <= $radius;
    }

    /**
     * Shortest distance in meters from a point to the geofence boundary.
     */
    public function distanceToBoundary(float $lat, float $lng, array $config): float
    {
        $vertices = $this->shapeVertices($config);

        if (count($vertices) < 2) {
            $centerLat = $config['centerLat'] ?? $config['lat'] ?? 0;
            $centerLng = $config['centerLng'] ?? $config['lng'] ?? 0;
            return abs($this->haversineDistance($lat, $lng, $centerLat, $centerLng) - (float) ($config['radius'] ?? 500));
        }

        $min = INF;
        $n = count($vertices);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $dist = $this->geometry->distanceToSegment($lat, $lng, $vertices[$j], $vertices[$i]);
            if ($dist < $min) {
                $min = $dist;
            }
        }

        return $min;
    }

    /**
     * Return the boundary of the configured shape as a list of {lat, lng} vertices.
     * Used both for distance calculations and for rendering.
     */
    public function shapeVertices(array $config): array
    {
        $shapeType = $config['shapeType'] ?? self::SHAPE_CIRCLE;
        $centerLat = $config['centerLat'] ?? $config['lat'] ?? 0;
        $centerLng = $config['centerLng'] ?? $config['lng'] ?? 0;

        return match ($shapeType) {
            self::SHAPE_POLYGON => $this->polygonVertices($config, $centerLat, $centerLng),
            self::SHAPE_RECTANGLE => $this->rectangleVertices($config),
            self::SHAPE_OVAL_H, self::SHAPE_OVAL_V => $this->ovalVertices($config),
            default => $this->geometry->sampleCircle($centerLat, $centerLng, (float) ($config['radius'] ?? 500)),
        };
    }

    private function polygonVertices(array $config, float $centerLat, float $centerLng): array
    {
        $points = is_array($config['points'] ?? null) ? $config['points'] : [];

        if (count($points) >= 3) {
            return array_values(array_map(function ($p) {
                return ['lat' => (float) $p['lat'], 'lng' => (float) $p['lng']];
            }, $points));
        }

        // Safe fallback for a malformed polygon.
        return $this->geometry->sampleCircle($centerLat, $centerLng, (float) ($config['radius'] ?? 500));
    }

    private function rectangleVertices(array $config): array
    {
        $width = (float) ($config['width'] ?? $config['radius'] ?? 500);
        $height = (float) ($config['height'] ?? $config['radius'] ?? 500);
        $a = $width / 2;
        $b = $height / 2;
        $theta = deg2rad((float) ($config['rotation'] ?? 0));
        $cos = cos($theta);
        $sin = sin($theta);
        $corners = [[$a, $b], [-$a, $b], [-$a, -$b], [$a, -$b]];
        $result = [];
        foreach ($corners as [$cx, $cy]) {
            $rx = $cx * $cos - $cy * $sin;
            $ry = $cx * $sin + $cy * $cos;
            $result[] = $this->geometry->metersToLatLng($rx, $ry, $config);
        }

        return $result;
    }

    private function ovalVertices(array $config): array
    {
        $width = (float) ($config['width'] ?? $config['radius'] ?? 500);
        $height = (float) ($config['height'] ?? $config['radius'] ?? 500);
        $a = max(1.0, $width / 2);
        $b = max(1.0, $height / 2);

        return $this->geometry->sampleEllipse($a, $b, $config);
    }

    /**
     * Compute a GeoJSON Polygon feature describing the configured shape boundary.
     */
    public function shapeGeoJson(array $config): array
    {
        $vertices = $this->shapeVertices($config);
        $coords = [];
        foreach ($vertices as $v) {
            $coords[] = [(float) $v['lng'], (float) $v['lat']];
        }
        if (!empty($coords)) {
            $coords[] = $coords[0];
        }

        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [$coords],
            ],
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
        return HelperService::calculateDistance($lat1, $lng1, $lat2, $lng2);
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
