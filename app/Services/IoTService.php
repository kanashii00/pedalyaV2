<?php

namespace App\Services;

use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\DeviceStatus;
use App\Models\User;
use Carbon\Carbon;

class IoTService
{
    protected NotificationService $notificationService;
    protected GeofenceService $geofenceService;

    public function __construct(NotificationService $notificationService, GeofenceService $geofenceService)
    {
        $this->notificationService = $notificationService;
        $this->geofenceService = $geofenceService;
    }

    public function processHeartbeat(array $data): array
    {
        $timestamp = Carbon::now();

        $bicycleId = $data['bicycleId'] ?? $data['bicycle_id'] ?? $data['bicycle'] ?? null;
        $bicycle = $bicycleId ? Bicycle::find($bicycleId) : null;

        if ($bicycle) {
            $updateData = [];
            if (isset($data['lat'], $data['lng'])) {
                $updateData['currentLat'] = $data['lat'];
                $updateData['currentLng'] = $data['lng'];
            }
            if (($battery = $this->valueOf($data, ['batteryLevel', 'battery_level', 'battery'])) !== null) {
                $updateData['batteryLevel'] = (int) max(0, min(100, (float) $battery));
            }
            if (array_key_exists('locked', $data)) {
                $updateData['lockStatus'] = $data['locked'] ? 'locked' : 'unlocked';
            }
            $updateData['lastHeartbeat'] = $timestamp;
            $bicycle->update($updateData);

            DeviceStatus::create([
                'bicycleId' => $bicycleId,
                'gps' => isset($data['lat'], $data['lng'])
                    ? ['lat' => $data['lat'], 'lng' => $data['lng'], 'speed' => $data['speed'] ?? $data['velocity'] ?? 0]
                    : null,
                'battery' => $battery !== null ? ['level' => (float) $battery] : null,
                'lockStatus' => $bicycle->lockStatus,
                'deviceVersion' => $data['firmware'] ?? null,
                'uptime' => $data['uptime'] ?? null,
                'type' => 'heartbeat',
                'eventTimestamp' => $timestamp,
            ]);

            $impact = $this->valueOf($data, ['impact', 'impact_force', 'impactForce']);
            if ($impact !== null && (float) $impact > 0) {
                $this->handleImpactDetection($bicycle, (float) $impact, $data);
            }

            if (isset($data['lat'], $data['lng'])) {
                $this->handleGeofenceCheck($bicycle, (float) $data['lat'], (float) $data['lng']);
            }
        }

        return [
            'received' => true,
            'timestamp' => $timestamp->toIso8601String(),
            'commands' => $this->getPendingCommands((int) $bicycleId),
        ];
    }

    public function processAccidentReport(array $data): array
    {
        $bicycleId = $data['bicycleId'] ?? $data['bicycle_id'] ?? null;
        $bicycle = $bicycleId ? Bicycle::find($bicycleId) : null;
        $impact = (float) ($data['impact'] ?? $data['impact_force'] ?? $data['impactForce'] ?? 0);
        $severity = $this->determineSeverity($impact);

        $accident = Accident::create([
            'bicycleId' => $bicycleId,
            'type' => $data['type'] ?? 'accident',
            'severity' => $severity,
            'gpsLocation' => isset($data['lat'], $data['lng']) ? ['lat' => $data['lat'], 'lng' => $data['lng']] : null,
            'accelerometerData' => $data['accelerometer'] ?? $data['accelerometerData'] ?? null,
            'impactForce' => $impact,
            'description' => $data['description'] ?? 'Automatic accident detection via IoT device',
            'status' => 'open',
            'acknowledged' => false,
            'alertSent' => true,
            'reportedBy' => $data['device_id'] ?? 'iot_device',
        ]);

        if ($bicycle) {
            $bicycle->update(['status' => Bicycle::STATUS_MAINTENANCE]);
        }

        $this->notifyAccident($accident, $bicycle, $severity);

        return [
            'accidentId' => $accident->id,
            'severity' => $severity,
            'status' => 'reported',
        ];
    }

    public function processGeofenceAlert(array $data): array
    {
        $bicycleId = $data['bicycleId'] ?? $data['bicycle_id'] ?? null;
        $riderId = $data['riderId'] ?? $data['rider_id'] ?? null;
        $distance = isset($data['distance']) ? (float) $data['distance'] : null;

        $accident = Accident::create([
            'bicycleId' => $bicycleId,
            'type' => 'geofence_breach',
            'severity' => 'moderate',
            'gpsLocation' => isset($data['lat'], $data['lng']) ? ['lat' => $data['lat'], 'lng' => $data['lng']] : null,
            'description' => $data['description'] ?? 'Geofence boundary breached',
            'status' => 'open',
            'acknowledged' => false,
            'alertSent' => true,
            'reportedBy' => $data['device_id'] ?? 'iot_device',
            'breachDistance' => $distance,
        ]);

        if ($riderId) {
            $this->notificationService->create(
                $riderId,
                'Geofence Alert',
                'You have breached the geofence boundary. Please return to the designated area immediately.',
                'geofence_alert'
            );
        }

        $this->notifyAdmins(
            'Geofence Breach Alert',
            "Bicycle {$bicycleId} has breached its geofence boundary."
                . ($distance !== null ? ' Distance outside boundary: ' . round($distance, 1) . 'm.' : '')
        );

        return [
            'alertId' => $accident->id,
            'status' => 'logged',
        ];
    }

    public function getDeviceStatus(int $bicycleId): ?DeviceStatus
    {
        return DeviceStatus::where('bicycleId', $bicycleId)
            ->latest('eventTimestamp')
            ->first();
    }

    public function sendCommand(int $bicycleId, string $command, array $params, User $user): DeviceCommand
    {
        $bicycle = Bicycle::find($bicycleId);

        $deviceCommand = DeviceCommand::create([
            'bicycleId' => $bicycleId,
            'command' => $command,
            'params' => $params ?: null,
            'status' => 'pending',
            'issuedBy' => $user->id,
        ]);

        if ($bicycle) {
            if ($command === 'lock') {
                $bicycle->update([
                    'lockStatus' => 'locked',
                    'status' => Bicycle::STATUS_LOCKED,
                    'lastLockAction' => Carbon::now(),
                    'lockActionBy' => $user->id,
                ]);
            } elseif ($command === 'unlock') {
                $bicycle->update([
                    'lockStatus' => 'unlocked',
                    'status' => Bicycle::STATUS_AVAILABLE,
                    'lastLockAction' => Carbon::now(),
                    'lockActionBy' => $user->id,
                ]);
            }

            DeviceStatus::create([
                'bicycleId' => $bicycleId,
                'command' => $command,
                'params' => $params ?: null,
                'commandIssuedBy' => $user->id,
                'commandIssuedAt' => Carbon::now(),
                'type' => 'command',
                'eventTimestamp' => Carbon::now(),
            ]);
        }

        return $deviceCommand;
    }

    public function acknowledgeDeviceCommand(int $deviceCommandId, ?string $result = null, string $status = 'executed'): bool
    {
        $command = DeviceCommand::find($deviceCommandId);

        if (!$command) {
            return false;
        }

        $command->update([
            'status' => in_array($status, ['executed', 'failed', 'sent'], true) ? $status : 'executed',
            'executedAt' => now(),
            'response' => $result,
        ]);

        return true;
    }

    public function getPendingCommands(int $bicycleId): array
    {
        return DeviceCommand::where('bicycleId', $bicycleId)
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->take(5)
            ->get()
            ->map(fn (DeviceCommand $cmd) => [
                'id' => $cmd->id,
                'command' => $cmd->command,
                'params' => $cmd->params,
                'issued_at' => $cmd->created_at,
            ])
            ->toArray();
    }

    private function handleImpactDetection(Bicycle $bicycle, float $impact, array $data): void
    {
        if ($impact < 2.0) {
            return;
        }

        $severity = $this->determineSeverity($impact);

        Accident::create([
            'bicycleId' => $bicycle->id,
            'type' => 'impact_detected',
            'severity' => $severity,
            'gpsLocation' => isset($data['lat'], $data['lng']) ? ['lat' => $data['lat'], 'lng' => $data['lng']] : null,
            'accelerometerData' => $data['accelerometer'] ?? null,
            'impactForce' => $impact,
            'description' => 'Impact detected with force: ' . $impact . 'g',
            'status' => 'open',
            'acknowledged' => false,
            'reportedBy' => $data['device_id'] ?? 'iot_device',
        ]);

        $this->notifyAdmins(
            'Impact / Accident Detected',
            "Abnormal movement or impact detected on bicycle {$bicycle->id} (force: {$impact}g, severity: {$severity})."
        );
    }

    private function handleGeofenceCheck(Bicycle $bicycle, float $lat, float $lng): void
    {
        $geofenceService = app(GeofenceService::class);
        $result = $geofenceService->checkPointInGeofence($lat, $lng);

        if (!$result['inside']) {
            $this->processGeofenceAlert([
                'bicycleId' => $bicycle->id,
                'lat' => $lat,
                'lng' => $lng,
                'distance' => $result['distanceOutside'] ?? null,
                'riderId' => $bicycle->currentRider,
            ]);
        }
    }

    private function determineSeverity(float $impact): string
    {
        return match (true) {
            $impact >= 8.0 => 'critical',
            $impact >= 5.0 => 'major',
            $impact >= 2.0 => 'moderate',
            default => 'minor',
        };
    }

    private function notifyAccident(Accident $accident, ?Bicycle $bicycle, string $severity): void
    {
        $this->notifyAdmins(
            'Accident Report',
            "Accident detected on bicycle {$accident->bicycleId}. Severity: {$severity}. Immediate attention required."
        );
    }

    private function notifyAdmins(string $title, string $message): void
    {
        $admins = User::where('role', User::ROLE_ADMIN)->pluck('id')->all();

        if (!empty($admins)) {
            $this->notificationService->createForUsers($admins, $title, $message, 'system');
        }
    }

    private function valueOf(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                return $data[$key];
            }
        }

        return null;
    }
}
