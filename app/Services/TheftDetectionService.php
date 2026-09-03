<?php

namespace App\Services;

use App\Events\GeofenceAlert;
use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\GeofenceBreach;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Centralises the theft / boundary-breach detection pipeline that ties the
 * live GPS feed and the GeoLibre 3D map & saved geofence logic together.
 *
 * Responsibilities:
 *  - Create a single open theft alert per bicycle (never duplicates while the
 *    bike remains outside the geofence).
 *  - Update the existing alert (location / distance / timestamp) until it is
 *    resolved or the bicycle returns inside.
 *  - When the bicycle returns inside the safe zone, update the alert status
 *    while keeping the historical record.
 *  - Keep the map markers, notifications, incident reports, dashboard counters
 *    and the smart lock in sync (events + real persisted state).
 *
 * Uses real GPS coordinates and GeofenceService calculations only — no demo
 * or hardcoded positions.
 */
class TheftDetectionService
{
    public const TYPE_THEFT = 'theft';

    public const STATUS_OPEN = 'open';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Evaluate a single live GPS point against the saved geofence and
     * synchronise the theft alert for the bicycle.
     *
     * @param  array  $geofenceResult  result of GeofenceService::checkPoint()
     * @return array{inside: bool, level: string, distance: float|null, alert: Accident|null}
     */
    public function processLocation(Bicycle $bicycle, float $lat, float $lng, array $geofenceResult): array
    {
        $inside = (bool) ($geofenceResult['inside'] ?? false);
        $level = $geofenceResult['level'] ?? 'safe';
        $distanceOutside = $geofenceResult['distanceOutside'] ?? null;
        $distanceToBoundary = $geofenceResult['distanceToBoundary'] ?? null;

        if (! $inside) {
            $alert = $this->openOrUpdateTheftAlert($bicycle, $lat, $lng, $distanceOutside, $geofenceResult);

            return [
                'inside' => false,
                'level' => $level,
                'distance' => $distanceOutside,
                'alert' => $alert,
            ];
        }

        // Bicycle is back inside (or has no live GPS breach) — resolve any
        // still-open theft alert but keep the record for history.
        $resolved = $this->resolveAlertOnReturn($bicycle);

        return [
            'inside' => true,
            'level' => $level,
            'distance' => $distanceToBoundary,
            'alert' => $resolved,
        ];
    }

    /**
     * Open a theft alert for the bicycle, or update the existing open alert
     * with the latest live position so it never duplicates.
     */
    public function openOrUpdateTheftAlert(
        Bicycle $bicycle,
        float $lat,
        float $lng,
        ?float $distanceOutside,
        array $geofenceResult
    ): Accident {
        // Reuse the currently open theft alert for this bicycle — never create
        // a duplicate active alert while it remains outside the geofence.
        $alert = $this->currentActiveAlert($bicycle);

        if (! $alert) {
            Log::debug('[TheftDetection] CREATING theft alert', [
                'bicycleId' => $bicycle->id,
                'lat' => $lat,
                'lng' => $lng,
                'distanceOutside' => $distanceOutside,
                'level' => $geofenceResult['level'] ?? null,
                'source' => 'geofence_branch',
            ]);

            $alert = $this->createAlertRow($bicycle, $lat, $lng, $distanceOutside);

            $this->notificationService->createForUsers(
                $this->adminIds(),
                'Theft / Boundary Breach Detected',
                "Bicycle {$bicycle->name} (#{$bicycle->serialNumber}) is outside the riding zone. Distance outside: "
                    .round((float) ($distanceOutside ?? 0), 1)
                    .'m. Potential theft detected.',
                'theft',
                ['bicycleId' => $bicycle->id, 'incidentId' => (string) $alert->id]
            );

            if ($bicycle->currentRider) {
                $this->notificationService->create(
                    (int) $bicycle->currentRider,
                    'Geofence Alert',
                    'You have been flagged for leaving the designated riding zone. Please return immediately or the smart lock will be engaged.',
                    'geofence_alert'
                );
            }

            $this->autoLockOnTheft($bicycle);

            event(new GeofenceAlert(
                $bicycle,
                'breach',
                (float) ($distanceOutside ?? 0),
                $lat,
                $lng
            ));
        } else {
            // Update the existing open alert with the latest live position.
            $alert->update([
                'gpsLocation' => ['lat' => $lat, 'lng' => $lng],
                'breachDistance' => $distanceOutside,
                'distanceFromBoundary' => $distanceOutside,
                'status' => self::STATUS_OPEN,
                'description' => 'Bicycle remains outside the riding zone ('.round((float) ($distanceOutside ?? 0), 1).'m outside boundary).',
                'updated_at' => now(),
            ]);

            event(new GeofenceAlert(
                $bicycle,
                'breach',
                (float) ($distanceOutside ?? 0),
                $lat,
                $lng
            ));
        }

        $this->syncBreachRecord($bicycle, $lat, $lng, $distanceOutside, $geofenceResult);

        return $alert->fresh();
    }

    /**
     * Reconcile a map bicycle that is currently outside the geofence so that it
     * ALWAYS has a single open, database-backed theft alert — regardless of which
     * path last wrote the bicycle's coordinates (live GPS, admin edit, API edit,
     * seed, etc.). Idempotent: reuses an existing open alert; never duplicates.
     *
     * This is the read-path self-heal that guarantees a red pin on the shared
     * GeoLibre 3D Map produces a row in the Theft Alert Log.
     */
    public function ensureActiveAlertForOutside(
        Bicycle $bicycle,
        float $lat,
        float $lng,
        ?float $distanceOutside,
        array $geofenceResult
    ): Accident {
        $alert = $this->currentActiveAlert($bicycle);

        if (! $alert) {
            Log::debug('[TheftDetection] RECONCILE creating missing open alert', [
                'bicycleId' => $bicycle->id,
                'lat' => $lat,
                'lng' => $lng,
                'distanceOutside' => $distanceOutside,
                'level' => $geofenceResult['level'] ?? null,
                'source' => 'read_path_reconcile',
            ]);

            $alert = $this->createAlertRow($bicycle, $lat, $lng, $distanceOutside);
        } else {
            $alert->update([
                'gpsLocation' => ['lat' => $lat, 'lng' => $lng],
                'breachDistance' => $distanceOutside,
                'distanceFromBoundary' => $distanceOutside,
                'updated_at' => now(),
            ]);
        }

        return $alert->fresh();
    }

    private function createAlertRow(
        Bicycle $bicycle,
        float $lat,
        float $lng,
        ?float $distanceOutside
    ): Accident {
        return Accident::create([
            'bicycleId' => $bicycle->id,
            'type' => self::TYPE_THEFT,
            'severity' => 'moderate',
            'gpsLocation' => ['lat' => $lat, 'lng' => $lng],
            'description' => 'Bicycle exited the designated riding zone ('.round((float) ($distanceOutside ?? 0), 1).'m outside boundary).',
            'status' => self::STATUS_OPEN,
            'acknowledged' => false,
            'alertSent' => true,
            'reportedBy' => 'gps_service',
            'breachDistance' => $distanceOutside,
            'breachDirection' => 'outside',
            'warningLevel' => 'breach',
            'distanceFromBoundary' => $distanceOutside,
            'actionTaken' => 'Sent for review. Smart lock engaged per auto-lock setting.',
        ]);
    }

    /**
     * When the bicycle returns inside the safe zone, mark the open theft alert
     * as returned/acknowledged for history but keep the record.
     */
    public function resolveAlertOnReturn(Bicycle $bicycle): ?Accident
    {
        $alert = $this->currentActiveAlert($bicycle);

        if (! $alert) {
            return null;
        }

        Log::debug('[TheftDetection] RESOLVING alert on return', [
            'bicycleId' => $bicycle->id,
            'alertId' => $alert->id,
            'status' => self::STATUS_RETURNED,
        ]);

        $alert->update([
            'status' => self::STATUS_RETURNED,
            'actionTaken' => 'Bicycle returned inside the riding zone. Alert kept for history.',
            'acknowledged' => true,
            'updated_at' => now(),
        ]);

        GeofenceBreach::where('bicycleId', $bicycle->id)
            ->whereNull('resolvedAt')
            ->update(['resolvedAt' => now()]);

        return $alert->fresh();
    }

    /**
     * Find the currently open, active theft alert for a bicycle.
     */
    public function currentActiveAlert(Bicycle $bicycle): ?Accident
    {
        return Accident::where('bicycleId', $bicycle->id)
            ->where('type', self::TYPE_THEFT)
            ->where('status', self::STATUS_OPEN)
            ->with('bicycle')
            ->latest('id')
            ->first();
    }

    /**
     * Keep the physical GeofenceBreach record in sync with the alert lifecycle.
     * A single unresolved breach row per bicycle is maintained while outside.
     */
    private function syncBreachRecord(
        Bicycle $bicycle,
        float $lat,
        float $lng,
        ?float $distanceOutside,
        array $geofenceResult
    ): void {
        $breach = GeofenceBreach::where('bicycleId', $bicycle->id)
            ->whereNull('resolvedAt')
            ->first();

        $geofenceId = $geofenceResult['id'] ?? ($geofenceResult['geofenceId'] ?? null);
        $geofenceId = is_scalar($geofenceId) ? $geofenceId : null;

        if (! $breach) {
            GeofenceBreach::create([
                'bicycleId' => $bicycle->id,
                'geofenceId' => $geofenceId,
                'lat' => $lat,
                'lng' => $lng,
                'distance' => $distanceOutside,
                'acknowledged' => false,
                'resolvedAt' => null,
            ]);
        } else {
            $breach->update([
                'lat' => $lat,
                'lng' => $lng,
                'distance' => $distanceOutside,
            ]);
        }
    }

    private function autoLockOnTheft(Bicycle $bicycle): void
    {
        $enabled = filter_var(SystemSetting::getValue('auto_lock_on_theft', true), FILTER_VALIDATE_BOOLEAN);

        if (! $enabled) {
            return;
        }

        app(IoTService::class)->sendCommand($bicycle->id, 'lock', ['reason' => 'geofence_breach']);
        $bicycle->update(['lockStatus' => Bicycle::LOCK_LOCKED]);
    }

    private function adminIds(): array
    {
        return User::where('role', User::ROLE_ADMIN)->pluck('id')->all();
    }
}
