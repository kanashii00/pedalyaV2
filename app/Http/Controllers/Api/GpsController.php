<?php

namespace App\Http\Controllers\Api;

use App\Events\GeofenceAlert;
use App\Events\GpsUpdate;
use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\Geofence;
use App\Models\GeofenceBreach;
use App\Models\GpsLog;
use App\Models\User;
use App\Services\GeofenceService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GpsController extends Controller
{
    public function __construct(
        private GeofenceService $geofenceService,
        private NotificationService $notificationService
    ) {}

    public function location(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bicycle_id' => 'required|integer|exists:bicycles,id',
            'lat'        => 'required|numeric|between:-90,90',
            'lng'        => 'required|numeric|between:-180,180',
            'speed'      => 'nullable|numeric|min:0',
            'heading'    => 'nullable|numeric|between:0,360',
            'altitude'   => 'nullable|numeric',
            'accuracy'   => 'nullable|numeric|min:0',
            'battery'    => 'nullable|numeric|between:0,100',
            'timestamp'  => 'nullable|date',
        ]);

        $bicycle = Bicycle::findOrFail($validated['bicycle_id']);

        $gpsLog = GpsLog::create([
            'bicycleId'    => $bicycle->id,
            'lat'          => $validated['lat'],
            'lng'          => $validated['lng'],
            'speed'        => $validated['speed'] ?? null,
            'heading'      => $validated['heading'] ?? null,
            'altitude'     => $validated['altitude'] ?? null,
            'accuracy'     => $validated['accuracy'] ?? null,
            'batteryLevel' => $validated['battery'] ?? null,
            'timestamp'    => $validated['timestamp'] ?? now(),
        ]);

        $bicycle->update([
            'currentLat'    => $validated['lat'],
            'currentLng'    => $validated['lng'],
            'batteryLevel'  => $validated['battery'] ?? $bicycle->batteryLevel,
            'lastGpsUpdate' => now(),
        ]);

        $geofence = $this->checkGeofence($bicycle, (float) $validated['lat'], (float) $validated['lng']);

        event(new GpsUpdate(
            $bicycle,
            (float) $validated['lat'],
            (float) $validated['lng'],
            isset($validated['speed']) ? (float) $validated['speed'] : null,
            (int) ($validated['battery'] ?? $bicycle->batteryLevel),
            $bicycle->lockStatus
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

        if (!$bicycle) {
            return response()->json(['message' => 'Bicycle not found'], 404);
        }

        $logs = GpsLog::where('bicycleId', $id)
            ->orderByDesc('timestamp')
            ->limit(1000)
            ->get();

        return response()->json([
            'bicycle_id' => $id,
            'gps_logs'   => $logs,
            'count'      => $logs->count(),
        ]);
    }

    public function current(int $id): JsonResponse
    {
        $bicycle = Bicycle::find($id);

        if (!$bicycle) {
            return response()->json(['message' => 'Bicycle not found'], 404);
        }

        $latestLog = GpsLog::where('bicycleId', $id)
            ->orderByDesc('timestamp')
            ->first();

        return response()->json([
            'bicycle_id' => $id,
            'location'   => $latestLog ? [
                'lat'       => (float) $latestLog->lat,
                'lng'       => (float) $latestLog->lng,
                'speed'     => (float) $latestLog->speed,
                'heading'   => (float) $latestLog->heading,
                'altitude'  => (float) $latestLog->altitude,
                'accuracy'  => (float) $latestLog->accuracy,
                'battery'   => (float) $latestLog->batteryLevel,
                'timestamp' => $latestLog->timestamp,
            ] : null,
        ]);
    }

    public function updateGeofence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'center_lat'    => 'required|numeric|between:-90,90',
            'center_lng'    => 'required|numeric|between:-180,180',
            'radius'        => 'required|numeric|min:10|max:50000',
            'name'          => 'nullable|string|max:255',
            'alert_enabled' => 'nullable|boolean',
        ]);

        $geofence = Geofence::updateOrCreate(
            ['isActive' => true],
            [
                'centerLat'     => $validated['center_lat'],
                'centerLng'     => $validated['center_lng'],
                'radius'        => $validated['radius'],
                'name'          => $validated['name'] ?? 'Azuela Cove Riding Zone',
                'alertEnabled'  => $validated['alert_enabled'] ?? true,
            ]
        );

        Geofence::where('isActive', false)
            ->where('id', '!=', $geofence->id)
            ->update(['isActive' => false]);

        return response()->json([
            'message'  => 'Geofence updated successfully',
            'geofence' => $geofence,
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

        if (!$config['alertEnabled']) {
            return null;
        }

        $result = $this->geofenceService->checkPoint($lat, $lng, $config);

        if (!$result['inside']) {
            $existingBreach = GeofenceBreach::where('bicycleId', $bicycle->id)
                ->whereNull('resolvedAt')
                ->first();

            if (!$existingBreach) {
                $breach = GeofenceBreach::create([
                    'bicycleId'   => $bicycle->id,
                    'geofenceId'  => $config['id'],
                    'lat'         => $lat,
                    'lng'         => $lng,
                    'distance'    => $result['distanceOutside'],
                ]);

                $accident = Accident::create([
                    'bicycleId' => $bicycle->id,
                    'type' => 'geofence_breach',
                    'severity' => 'moderate',
                    'gpsLocation' => ['lat' => $lat, 'lng' => $lng],
                    'description' => 'Bicycle exited the designated riding zone (' . round($result['distanceOutside'], 1) . 'm outside boundary).',
                    'status' => 'open',
                    'acknowledged' => false,
                    'alertSent' => true,
                    'reportedBy' => 'gps_service',
                    'breachDistance' => $result['distanceOutside'],
                    'breachDirection' => 'outside',
                    'warningLevel' => 'breach',
                    'distanceFromBoundary' => $result['distanceOutside'],
                ]);

                $admins = User::where('role', User::ROLE_ADMIN)->pluck('id')->all();
                if (!empty($admins)) {
                    $this->notificationService->createForUsers(
                        $admins,
                        'Geofence Breach Detected',
                        "Bicycle {$bicycle->name} (#{$bicycle->serialNumber}) exited the Azuela Cove riding zone. Potential theft detected.",
                        'geofence_breach',
                        ['bicycleId' => $bicycle->id, 'incidentId' => (string) $accident->id]
                    );
                }

                event(new GeofenceAlert(
                    $bicycle,
                    $result['level'] ?? 'breach',
                    (float) $result['distanceOutside'],
                    $lat,
                    $lng
                ));

                return $result;
            }
        } else {
            GeofenceBreach::where('bicycleId', $bicycle->id)
                ->whereNull('resolvedAt')
                ->update(['resolvedAt' => now()]);
        }

        return $result;
    }
}
