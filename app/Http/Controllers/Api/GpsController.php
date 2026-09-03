<?php

namespace App\Http\Controllers\Api;

use App\Events\GeofenceAlert;
use App\Events\GpsUpdate;
use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\Geofence;
use App\Models\GpsLog;
use App\Services\GeofenceService;
use App\Services\TheftDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GpsController extends Controller
{
    public function __construct(
        private GeofenceService $geofenceService,
        private TheftDetectionService $theftDetectionService
    ) {}

    public function location(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bicycle_id' => 'required|integer|exists:bicycles,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'altitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric|min:0',
            'battery' => 'nullable|numeric|between:0,100',
            'timestamp' => 'nullable|date',
        ]);

        $bicycle = Bicycle::findOrFail($validated['bicycle_id']);

        $gpsLog = GpsLog::create([
            'bicycleId' => $bicycle->id,
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'speed' => $validated['speed'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'altitude' => $validated['altitude'] ?? null,
            'accuracy' => $validated['accuracy'] ?? null,
            'batteryLevel' => $validated['battery'] ?? null,
            'timestamp' => $validated['timestamp'] ?? now(),
        ]);

        $bicycle->update([
            'currentLat' => $validated['lat'],
            'currentLng' => $validated['lng'],
            'batteryLevel' => $validated['battery'] ?? $bicycle->batteryLevel,
            'lastGpsUpdate' => now(),
        ]);

        $geofence = $this->checkGeofence($bicycle, (float) $validated['lat'], (float) $validated['lng']);

        event(new GpsUpdate(
            $bicycle,
            (float) $validated['lat'],
            (float) $validated['lng'],
            isset($validated['speed']) ? (float) $validated['speed'] : null,
            (int) ($validated['battery'] ?? $bicycle->batteryLevel),
            $bicycle->lockStatus,
            $geofence['level'] ?? null,
            $geofence['distance'] ?? null
        ));

        return response()->json([
            'message' => 'Location recorded',
            'gps_log' => $gpsLog,
            'geofence' => $geofence,
        ]);
    }

    public function track(int $id): JsonResponse
    {
        $bicycle = Bicycle::find($id);

        if (! $bicycle) {
            return response()->json(['message' => 'Bicycle not found'], 404);
        }

        $logs = GpsLog::where('bicycleId', $id)
            ->orderByDesc('timestamp')
            ->limit(1000)
            ->get();

        return response()->json([
            'bicycle_id' => $id,
            'gps_logs' => $logs,
            'count' => $logs->count(),
        ]);
    }

    public function current(int $id): JsonResponse
    {
        $bicycle = Bicycle::find($id);

        if (! $bicycle) {
            return response()->json(['message' => 'Bicycle not found'], 404);
        }

        $latestLog = GpsLog::where('bicycleId', $id)
            ->orderByDesc('timestamp')
            ->first();

        return response()->json([
            'bicycle_id' => $id,
            'location' => $latestLog ? [
                'lat' => (float) $latestLog->lat,
                'lng' => (float) $latestLog->lng,
                'speed' => (float) $latestLog->speed,
                'heading' => (float) $latestLog->heading,
                'altitude' => (float) $latestLog->altitude,
                'accuracy' => (float) $latestLog->accuracy,
                'battery' => (float) $latestLog->batteryLevel,
                'timestamp' => $latestLog->timestamp,
            ] : null,
        ]);
    }

    public function updateGeofence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'center_lat' => 'required|numeric|between:-90,90',
            'center_lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:10|max:50000',
            'shape_type' => 'nullable|string|in:circle,oval_h,oval_v,rectangle,polygon',
            'width' => 'nullable|numeric|min:10|max:100000',
            'height' => 'nullable|numeric|min:10|max:100000',
            'rotation' => 'nullable|numeric|between:0,360',
            'points' => 'nullable|array|min:3',
            'points.*.lat' => 'required_with:points|numeric|between:-90,90',
            'points.*.lng' => 'required_with:points|numeric|between:-180,180',
            'name' => 'nullable|string|max:255',
            'alert_enabled' => 'nullable|boolean',
        ]);

        $shapeType = $validated['shape_type'] ?? 'circle';
        $radius = $validated['radius'] ?? 25;

        $geofence = Geofence::updateOrCreate(
            ['isActive' => true],
            [
                'centerLat' => $validated['center_lat'],
                'centerLng' => $validated['center_lng'],
                'radius' => $radius,
                'shapeType' => $shapeType,
                'width' => $validated['width'] ?? null,
                'height' => $validated['height'] ?? null,
                'rotation' => $validated['rotation'] ?? null,
                'points' => $shapeType === 'polygon' ? ($validated['points'] ?? null) : null,
                'name' => $validated['name'] ?? 'Azuela Cove Riding Zone',
                'alertEnabled' => $validated['alert_enabled'] ?? true,
            ]
        );

        Geofence::where('isActive', true)
            ->where('id', '!=', $geofence->id)
            ->update(['isActive' => false]);

        return response()->json([
            'message' => 'Geofence updated successfully',
            'geofence' => $this->geofenceService->getConfig(),
        ]);
    }

    public function getGeofence(): JsonResponse
    {
        return response()->json([
            'geofence' => $this->geofenceService->getConfig(),
        ]);
    }

    private function checkGeofence(Bicycle $bicycle, float $lat, float $lng): ?array
    {
        $config = $this->geofenceService->getConfig();

        $result = $this->geofenceService->checkPoint($lat, $lng, $config);

        Log::debug('[GpsController] checkGeofence', [
            'bicycleId' => $bicycle->id,
            'lat' => $lat,
            'lng' => $lng,
            'inside' => $result['inside'] ?? null,
            'level' => $result['level'] ?? null,
            'alertEnabled' => $config['alertEnabled'] ?? null,
        ]);

        if ($result['inside']) {
            if (in_array($result['level'] ?? null, ['approaching', 'warning'], true)) {
                $this->recordWarningEvent($bicycle, $lat, $lng, $result);

                event(new GeofenceAlert(
                    $bicycle,
                    $result['level'],
                    (float) $result['distanceToBoundary'],
                    $lat,
                    $lng
                ));

                return $result;
            }

            // Returned inside the safe zone → resolve open theft alert, keep history.
            $this->theftDetectionService->resolveAlertOnReturn($bicycle);

            return $result;
        }

        // Outside / breach → open or update the single active theft alert and
        // synchronise map markers, counters, notifications and the smart lock.
        // This ALWAYS runs when the zone is outside, independent of alertEnabled.
        $this->theftDetectionService->openOrUpdateTheftAlert(
            $bicycle,
            $lat,
            $lng,
            $result['distanceOutside'] ?? null,
            $result
        );

        return $result;
    }

    private function recordWarningEvent(Bicycle $bicycle, float $lat, float $lng, array $result): void
    {
        $lastWarning = Accident::where('bicycleId', $bicycle->id)
            ->where('type', 'geofence_alert')
            ->where('warningLevel', '!=', 'breach')
            ->latest('id')
            ->first();

        if ($lastWarning && $lastWarning->created_at->diffInMinutes(now()) < 15) {
            return;
        }

        Accident::create([
            'bicycleId' => $bicycle->id,
            'type' => 'geofence_alert',
            'severity' => 'minor',
            'gpsLocation' => ['lat' => $lat, 'lng' => $lng],
            'description' => 'Bicycle is approaching the geofence boundary ('.round($result['distanceToBoundary'], 1).'m from boundary).',
            'status' => 'open',
            'acknowledged' => false,
            'alertSent' => true,
            'reportedBy' => 'gps_service',
            'warningLevel' => $result['level'] ?? 'approaching',
            'distanceFromBoundary' => $result['distanceToBoundary'],
        ]);
    }
}
