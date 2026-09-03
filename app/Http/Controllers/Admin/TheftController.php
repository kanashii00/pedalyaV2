<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\GeofenceBreach;
use App\Services\GeofenceService;
use App\Services\TheftDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TheftController extends Controller
{
    public function __construct(
        private GeofenceService $geofenceService,
        private TheftDetectionService $theftDetectionService
    ) {}

    public function index(): Response
    {
        // Only bicycles currently outside the boundary (red pins) appear here.
        // Green/orange (inside/near) bicycles are excluded; returned bikes drop
        // off the log but their resolved record stays in the DB for history.
        $alerts = Accident::with(['bicycle.currentRiderUser', 'bicycle.latestGpsLog'])
            ->where('type', TheftDetectionService::TYPE_THEFT)
            ->where('status', TheftDetectionService::STATUS_OPEN)
            ->latest()
            ->paginate(20);

        $geofence = $this->geofenceService->getConfig();

        // Exact same live bicycle feed + zone derivation as the GeoLibre 3D Map.
        $mapBicycles = Bicycle::with(['latestTelemetry', 'latestGpsLog', 'currentRiderUser'])
            ->where('status', '!=', 'removed')
            ->orderBy('id')
            ->get();

        // Reconcile: any bicycle whose computed zone is outside must have a
        // single open theft alert in the log — regardless of how its
        // coordinates were last written.
        $this->reconcileOutsideBicycles($mapBicycles, $geofence);

        $openTheftAlerts = Accident::where('type', TheftDetectionService::TYPE_THEFT)
            ->where('status', 'open')
            ->get()
            ->mapWithKeys(fn (Accident $a) => [
                (string) $a->bicycleId => [
                    'status' => $a->status,
                    'acknowledged' => (bool) $a->acknowledged,
                ],
            ]);

        $bicycles = Bicycle::with('currentRiderUser')
            ->whereIn('status', [Bicycle::STATUS_RENTED, Bicycle::STATUS_MAINTENANCE])
            ->get();

        $openBreachCount = GeofenceBreach::whereNull('resolvedAt')->count();

        return response()->view('admin.theft', compact(
            'alerts',
            'geofence',
            'mapBicycles',
            'openTheftAlerts',
            'bicycles',
            'openBreachCount'
        ));
    }

    public function live(): JsonResponse
    {
        $geofence = $this->geofenceService->getConfig();

        $bicycles = Bicycle::where('status', '!=', 'removed')
            ->orderBy('id')
            ->get();

        // Reconcile outside bicycles before serialising so a red pin on the map
        // always yields an alert row in this live feed.
        $this->reconcileOutsideBicycles($bicycles, $geofence);

        $mapBicycles = [];
        foreach ($bicycles as $bike) {
            if ($bike->currentLat !== null && $bike->currentLng !== null) {
                $zone = $this->geofenceService->checkPoint((float) $bike->currentLat, (float) $bike->currentLng, $geofence);
            } else {
                $zone = ['inside' => null, 'distance' => null, 'level' => 'unknown', 'warning' => false];
            }
            $mapBicycles[] = [
                'id' => $bike->id,
                'name' => $bike->name,
                'current_lat' => $bike->currentLat,
                'current_lng' => $bike->currentLng,
                'status' => $bike->status,
                'battery_level' => $bike->batteryLevel,
                'lock_status' => $bike->lockStatus,
                'last_heartbeat' => $bike->lastHeartbeat,
                'zone_level' => $zone['level'],
                'zone_distance' => $zone['distance'],
            ];
        }

        // Re-query so the just-created/open alert rows are reflected in this response.
        $alerts = Accident::with(['bicycle.currentRiderUser'])
            ->where('type', TheftDetectionService::TYPE_THEFT)
            ->where('status', TheftDetectionService::STATUS_OPEN)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'geofence' => $geofence,
            'bicycles' => $mapBicycles,
            'alerts' => $alerts->map(fn (Accident $a) => [
                'id' => $a->id,
                'bicycleId' => $a->bicycleId,
                'bicycle' => $a->bicycle->name ?? 'Unknown',
                'rider' => $a->bicycle?->currentRiderUser?->name,
                'lat' => $a->gpsLocation['lat'] ?? null,
                'lng' => $a->gpsLocation['lng'] ?? null,
                'distance' => $a->breachDistance ?? $a->distanceFromBoundary,
                'status' => $a->status,
                'acknowledged' => (bool) $a->acknowledged,
                'timestamp' => $a->created_at->toIso8601String(),
            ])->values(),
            'openBreaches' => GeofenceBreach::whereNull('resolvedAt')->count(),
            'unacknowledged' => $alerts->where('acknowledged', false)->count(),
            'atRisk' => Bicycle::whereIn('status', [Bicycle::STATUS_RENTED, Bicycle::STATUS_MAINTENANCE])->count(),
        ]);
    }

    /**
     * For every map bicycle whose computed zone is outside the geofence, ensure
     * a single open theft alert exists for it. This is the read-path self-heal
     * that guarantees red pins always produce a visible database-backed alert
     * row, and never creates duplicates across repeated polling.
     */
    private function reconcileOutsideBicycles($bicycles, array $geofence): void
    {
        foreach ($bicycles as $bike) {
            $lat = $bike->currentLat;
            $lng = $bike->currentLng;
            if ($lat === null || $lng === null) {
                continue;
            }

            $result = $this->geofenceService->checkPoint((float) $lat, (float) $lng, $geofence);
            $bike->zone = $result;

            if (! ($result['inside'] ?? false)) {
                Log::debug('[TheftController] reconcile outside pin -> ensure alert', [
                    'bicycleId' => $bike->id,
                    'lat' => $lat,
                    'lng' => $lng,
                    'level' => $result['level'] ?? null,
                    'distanceOutside' => $result['distanceOutside'] ?? null,
                ]);

                $this->theftDetectionService->ensureActiveAlertForOutside(
                    $bike,
                    (float) $lat,
                    (float) $lng,
                    $result['distanceOutside'] ?? null,
                    $result
                );
            } elseif (in_array($result['level'] ?? null, ['approaching', 'warning'], true)) {
                // Only a true outside (red) pin is a theft; green/orange are not.
                continue;
            }
        }
    }

    public function acknowledge(int $id): RedirectResponse
    {
        $alert = Accident::findOrFail($id);

        $alert->update([
            'acknowledged' => true,
            'actionTaken' => 'Acknowledged by administrator',
        ]);

        GeofenceBreach::where('bicycleId', $alert->bicycleId)
            ->whereNull('resolvedAt')
            ->update(['acknowledged' => true]);

        AuditLog::record('theft_alert_acknowledged', auth()->id(), [
            'accidentId' => $alert->id,
            'type' => $alert->type,
        ]);

        return redirect()->back()->with('success', 'Theft alert acknowledged.');
    }
}
