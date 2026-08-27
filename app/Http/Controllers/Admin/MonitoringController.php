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

    public function bicycleStatusIndex(Request $request): Response
    {
        $query = Bicycle::with(['latestTelemetry', 'latestGpsLog', 'currentRiderUser'])
            ->where('status', '!=', 'removed');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('lock')) {
            $query->where('lockStatus', $request->input('lock') === 'locked' ? 'locked' : 'unlocked');
        }

        if ($request->filled('connectivity')) {
            $online = $request->input('connectivity') === 'online';
            $cutoff = now()->subMinutes(5);
            if ($online) {
                $query->where('lastHeartbeat', '>=', $cutoff);
            } else {
                $query->where(function ($q) use ($cutoff) {
                    $q->whereNull('lastHeartbeat')->orWhere('lastHeartbeat', '<', $cutoff);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('serialNumber', 'like', "%{$search}%");
            });
        }

        $bicycles = $query->orderBy('id')->get();

        $summary = [
            'total'      => 0,
            'available'  => 0,
            'rented'     => 0,
            'maintenance'=> 0,
            'locked'     => 0,
            'unlocked'   => 0,
            'online'     => 0,
            'offline'    => 0,
        ];

        $cutoff = now()->subMinutes(5);
        foreach ($bicycles as $bike) {
            $online = $bike->lastHeartbeat !== null && $bike->lastHeartbeat->gt($cutoff);
            $summary['total']++;
            $summary[$bike->status] = ($summary[$bike->status] ?? 0) + 1;
            $bike->lockStatus === 'locked' ? $summary['locked']++ : $summary['unlocked']++;
            $online ? $summary['online']++ : $summary['offline']++;
            $bike->connectivity = $online ? 'online' : 'offline';
        }

        return response()->view('admin.bicycles-status', compact('bicycles', 'summary'));
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
