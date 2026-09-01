<?php

namespace App\Services;

class GeofenceGeometryHelper
{
    /**
     * Distance in meters from a point to a line segment (given by two vertices).
     */
    public function distanceToSegment(float $lat, float $lng, array $a, array $b): float
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
    public function isInRotatedRectangle(float $lat, float $lng, array $config): bool
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
    public function isInEllipse(float $lat, float $lng, array $config): bool
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
    public function localMeters(float $lat, float $lng, array $config): array
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

    public function metersToLatLng(float $x, float $y, array $config): array
    {
        $centerLat = $config['centerLat'] ?? $config['lat'] ?? 0;
        $centerLng = $config['centerLng'] ?? $config['lng'] ?? 0;
        $latRad = deg2rad($centerLat);

        return [
            'lat' => $centerLat + ($y / 111320.0),
            'lng' => $centerLng + ($x / (111320.0 * cos($latRad))),
        ];
    }

    public function sampleCircle(float $centerLat, float $centerLng, float $radius): array
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

    public function sampleEllipse(float $a, float $b, array $config): array
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
}
