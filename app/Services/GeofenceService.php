<?php

namespace App\Services;

use App\Models\Geofence;

class GeofenceService
{
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

        if ($shapeType === self::SHAPE_POLYGON) {
            $points = is_array($config['points'] ?? null) ? $config['points'] : [];
            if (count($points) >= 3) {
                return $this->isPointInPolygon($lat, $lng, $points);
            }
            // Fall back to a circle based on the first point distance if malformed.
            return $this->haversineDistance($lat, $lng, (float) ($config['centerLat'] ?? 0), (float) ($config['centerLng'] ?? 0)) <= (float) ($config['radius'] ?? 500);
        }

        if ($shapeType === self::SHAPE_RECTANGLE) {
            return $this->isInRotatedRectangle($lat, $lng, $config);
        }

        if ($shapeType === self::SHAPE_OVAL_H || $shapeType === self::SHAPE_OVAL_V) {
            return $this->isInEllipse($lat, $lng, $config);
        }

        // Circle (default)
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
            $dist = $this->distanceToSegment($lat, $lng, $vertices[$j], $vertices[$i]);
            if ($dist < $min) {
                $min = $dist;
            }
        }

        return $min;
    }

    /**
     * Distance in meters from a point to a line segment (given by two vertices).
     */
    private function distanceToSegment(float $lat, float $lng, array $a, array $b): float
    {
        $latBase = $lat;
        $mPerDegLat = 111320.0;
        $mPerDegLng = 111320.0 * cos(deg2rad($latBase));

        $ax = ($a['lng'] - $lng) * $mPerDegLng;
        $ay = ($a['lat'] - $lat) * $mPerDegLat;
        $bx = ($b['lng'] - $lng) * $mPerDegLng;
        $by = ($b['lat'] - $lat) * $mPerDegLat;

        $dx = $bx - $ax;
        $dy = $by - $ay;

        if ($dx === 0.0 && $dy === 0.0) {
            return sqrt($ax * $ax + $ay * $ay);
        }

        $t = ((-$ax) * $dx + (-$ay) * $dy) / ($dx * $dx + $dy * $dy);
        $t = max(0.0, min(1.0, $t));

        $cx = $ax + $t * $dx;
        $cy = $ay + $t * $dy;

        return sqrt($cx * $cx + $cy * $cy);
    }

    /**
     * Test whether a point is inside a rotated rectangle (in local meters).
     */
    private function isInRotatedRectangle(float $lat, float $lng, array $config): bool
    {
        ['x' => $x, 'y' => $y] = $this->localMeters($lat, $lng, $config);
        $width = (float) ($config['width'] ?? $config['radius'] ?? 500);
        $height = (float) ($config['height'] ?? $config['radius'] ?? 500);
        $a = $width / 2;
        $b = $height / 2;
        $theta = deg2rad((float) ($config['rotation'] ?? 0));

        $cos = cos($theta);
        $sin = sin($theta);
        $localX = $x * $cos - $y * $sin;
        $localY = $x * $sin + $y * $cos;

        return abs($localX) <= $a && abs($localY) <= $b;
    }

    /**
     * Test whether a point is inside an axis-aligned ellipse (oval) in local meters.
     */
    private function isInEllipse(float $lat, float $lng, array $config): bool
    {
        ['x' => $x, 'y' => $y] = $this->localMeters($lat, $lng, $config);
        $width = (float) ($config['width'] ?? $config['radius'] ?? 500);
        $height = (float) ($config['height'] ?? $config['radius'] ?? 500);
        $a = max(1.0, $width / 2);
        $b = max(1.0, $height / 2);

        return (($x * $x) / ($a * $a)) + (($y * $y) / ($b * $b)) <= 1;
    }

    /**
     * Convert a geographic point to local (east-west, north-south) meters about the shape center.
     */
    private function localMeters(float $lat, float $lng, array $config): array
    {
        $centerLat = $config['centerLat'] ?? $config['lat'] ?? 0;
        $centerLng = $config['centerLng'] ?? $config['lng'] ?? 0;
        $latRad = deg2rad($centerLat);
        $dLat = $lat - $centerLat;
        $dLng = $lng - $centerLng;

        return [
            'x' => $dLng * (111320.0 * cos($latRad)),
            'y' => $dLat * 111320.0,
        ];
    }

    private function metersToLatLng(float $x, float $y, array $config): array
    {
        $centerLat = $config['centerLat'] ?? $config['lat'] ?? 0;
        $centerLng = $config['centerLng'] ?? $config['lng'] ?? 0;
        $latRad = deg2rad($centerLat);

        return [
            'lat' => $centerLat + ($y / 111320.0),
            'lng' => $centerLng + ($x / (111320.0 * cos($latRad))),
        ];
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

        switch ($shapeType) {
            case self::SHAPE_POLYGON:
                $points = is_array($config['points'] ?? null) ? $config['points'] : [];
                if (count($points) >= 3) {
                    return array_values(array_map(function ($p) {
                        return ['lat' => (float) $p['lat'], 'lng' => (float) $p['lng']];
                    }, $points));
                }
                break;

            case self::SHAPE_RECTANGLE:
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
                    $result[] = $this->metersToLatLng($rx, $ry, $config);
                }
                return $result;

            case self::SHAPE_OVAL_H:
            case self::SHAPE_OVAL_V:
                $width = (float) ($config['width'] ?? $config['radius'] ?? 500);
                $height = (float) ($config['height'] ?? $config['radius'] ?? 500);
                $a = max(1.0, $width / 2);
                $b = max(1.0, $height / 2);
                return $this->sampleEllipse($a, $b, $config);

            case self::SHAPE_CIRCLE:
            default:
                $radius = (float) ($config['radius'] ?? 500);
                return $this->sampleCircle($centerLat, $centerLng, $radius);
        }

        // Safe fallback for a malformed polygon.
        $radius = (float) ($config['radius'] ?? 500);
        return $this->sampleCircle($centerLat, $centerLng, $radius);
    }

    private function sampleCircle(float $centerLat, float $centerLng, float $radius): array
    {
        $points = [];
        $steps = 120;
        for ($i = 0; $i < $steps; $i++) {
            $rad = ($i / $steps) * 2 * M_PI;
            $points[] = $this->metersToLatLng(
                cos($rad) * $radius,
                sin($rad) * $radius,
                ['centerLat' => $centerLat, 'centerLng' => $centerLng]
            );
        }
        return $points;
    }

    private function sampleEllipse(float $a, float $b, array $config): array
    {
        $points = [];
        $steps = 120;
        for ($i = 0; $i < $steps; $i++) {
            $rad = ($i / $steps) * 2 * M_PI;
            $points[] = $this->metersToLatLng(
                cos($rad) * $a,
                sin($rad) * $b,
                $config
            );
        }
        return $points;
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
        if (count($coords) > 0) {
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
