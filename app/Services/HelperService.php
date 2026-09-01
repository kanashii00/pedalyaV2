<?php

namespace App\Services;

use App\Models\User;

class HelperService
{
    public static function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
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

    public static function validateCoordinates($lat, $lng): array
    {
        $errors = [];

        if (!is_numeric($lat) || !is_numeric($lng)) {
            return [
                'valid' => false,
                'lat' => $lat,
                'lng' => $lng,
                'errors' => ['Coordinates must be numeric values'],
            ];
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        if ($lat < -90 || $lat > 90) {
            $errors[] = 'Latitude must be between -90 and 90';
        }

        if ($lng < -180 || $lng > 180) {
            $errors[] = 'Longitude must be between -180 and 180';
        }

        return [
            'valid' => empty($errors),
            'lat' => $lat,
            'lng' => $lng,
            'errors' => $errors,
        ];
    }

    public static function generateId(string $prefix = ''): string
    {
        $unique = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $timestamp = date('Ymd-His');

        return $prefix ? $prefix . '-' . $timestamp . '-' . $unique : $timestamp . '-' . $unique;
    }

    public static function valueOf(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                return $data[$key];
            }
        }

        return null;
    }

    public static function sanitizeUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? null,
            'role' => $user->role ?? null,
            'isVerified' => $user->is_verified ?? false,
            'avatar' => $user->avatar ?? null,
            'createdAt' => $user->created_at ?? null,
        ];
    }
}
