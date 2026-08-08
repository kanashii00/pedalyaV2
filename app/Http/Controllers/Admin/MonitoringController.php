<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use App\Services\GeofenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MonitoringController extends Controller
{
    public const SECTIONS = ['map', 'gps', 'locks', 'devices'];

    public function __construct(
        private GeofenceService $geofenceService
    ) {}

    public function index(Request $request): Response
    {
        $section = $request->query('section', 'map');
        if (!in_array($section, self::SECTIONS, true)) {
            $section = 'map';
        }

        $bicycles = Bicycle::with(['latestTelemetry', 'latestGpsLog', 'currentRiderUser'])
            ->where('status', '!=', 'removed')
            ->orderBy('id')
            ->get();

        $geofence = $this->geofenceService->getConfig();

        foreach ($bicycles as $bike) {
            if ($bike->currentLat !== null && $bike->currentLng !== null) {
                $bike->zone = $this->geofenceService->checkPoint((float) $bike->currentLat, (float) $bike->currentLng, $geofence);
            } else {
                $bike->zone = ['inside' => null, 'distance' => null, 'level' => 'unknown', 'warning' => false];
            }
        }

        return response()->view('admin.monitoring', compact('bicycles', 'geofence', 'section'));
    }

    public function bicycleStatus(int $id): JsonResponse
    {
        $bicycle = Bicycle::with(['latestTelemetry', 'latestGpsLog'])->findOrFail($id);

        return response()->json([
            'bicycle'       => $bicycle,
            'device_status' => $bicycle->latestTelemetry,
            'gps'           => $bicycle->latestGpsLog,
        ]);
    }

    public function live(): JsonResponse
    {
        $bicycles = Bicycle::where('status', '!=', 'removed')
            ->orderBy('id')
            ->get();

        $geofence = $this->geofenceService->getConfig();

        $data = [];
        foreach ($bicycles as $bike) {
            if ($bike->currentLat !== null && $bike->currentLng !== null) {
                $zone = $this->geofenceService->checkPoint((float) $bike->currentLat, (float) $bike->currentLng, $geofence);
            } else {
                $zone = ['inside' => null, 'distance' => null, 'level' => 'unknown', 'warning' => false];
            }
            $data[] = [
                'id' => $bike->id,
                'name' => $bike->name,
                'status' => $bike->status,
                'current_lat' => $bike->currentLat,
                'current_lng' => $bike->currentLng,
                'battery_level' => $bike->batteryLevel,
                'lock_status' => $bike->lockStatus,
                'last_heartbeat' => $bike->lastHeartbeat,
                'zone_level' => $zone['level'],
                'zone_distance' => $zone['distance'],
            ];
        }

        return response()->json(['bicycles' => $data]);
    }
}
