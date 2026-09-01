<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\DeviceStatus;
use App\Services\IoTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IoTController extends Controller
{
    private const BICYCLE_NOT_FOUND = 'Bicycle not found';

    public function __construct(
        private IoTService $iotService
    ) {}

    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bicycle_id'    => 'required|integer|exists:bicycles,id',
            'device_id'     => 'required|string|max:255',
            'lat'           => 'nullable|numeric|between:-90,90',
            'lng'           => 'nullable|numeric|between:-180,180',
            'speed'         => 'nullable|numeric|min:0',
            'battery'       => 'nullable|numeric|between:0,100',
            'batteryLevel'  => 'nullable|numeric|between:0,100',
            'locked'        => 'nullable|boolean',
            'impact'        => 'nullable|numeric|min:0',
            'accelerometer' => 'nullable|array',
            'firmware'      => 'nullable|string|max:255',
            'uptime'        => 'nullable|integer|min:0',
            'temperature'   => 'nullable|numeric',
            'timestamp'     => 'nullable|date',
        ]);

        $result = $this->iotService->processHeartbeat($validated);

        return response()->json([
            'message' => 'Heartbeat processed',
            'result'  => $result,
        ]);
    }

    public function accidentReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bicycle_id'    => 'required|integer|exists:bicycles,id',
            'device_id'     => 'required|string|max:255',
            'lat'           => 'required|numeric|between:-90,90',
            'lng'           => 'required|numeric|between:-180,180',
            'impact_force'  => 'nullable|numeric|min:0',
            'impact'        => 'nullable|numeric|min:0',
            'accelerometer' => 'nullable|array',
            'timestamp'     => 'nullable|date',
        ]);

        $result = $this->iotService->processAccidentReport($validated);

        return response()->json([
            'message' => 'Accident report processed',
            'result'  => $result,
        ]);
    }

    public function geofenceAlert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bicycle_id'  => 'required|integer|exists:bicycles,id',
            'device_id'   => 'required|string|max:255',
            'lat'         => 'required|numeric|between:-90,90',
            'lng'         => 'required|numeric|between:-180,180',
            'distance'    => 'nullable|numeric|min:0',
            'geofence_id' => 'nullable|integer',
            'timestamp'   => 'nullable|date',
        ]);

        $result = $this->iotService->processGeofenceAlert($validated);

        return response()->json([
            'message' => 'Geofence alert processed',
            'result'  => $result,
        ]);
    }

    public function bicycleStatus(int $id): JsonResponse
    {
        $bicycle = Bicycle::find($id);

        if (!$bicycle) {
            return response()->json(['message' => self::BICYCLE_NOT_FOUND], 404);
        }

        $commands = $this->iotService->getPendingCommands($bicycle->id);

        return response()->json([
            'bicycle_id' => $bicycle->id,
            'status'     => $bicycle->status,
            'battery'    => $bicycle->batteryLevel,
            'lock_status' => $bicycle->lockStatus,
            'commands'   => $commands,
        ]);
    }

    public function bicycleStatusAuth(int $id): JsonResponse
    {
        $bicycle = Bicycle::with(['latestTelemetry', 'latestGpsLog', 'currentRiderUser'])
            ->find($id);

        if (!$bicycle) {
            return response()->json(['message' => self::BICYCLE_NOT_FOUND], 404);
        }

        return response()->json([
            'bicycle_id'  => $bicycle->id,
            'name'        => $bicycle->name,
            'status'      => $bicycle->status,
            'battery'     => $bicycle->batteryLevel,
            'lock_status' => $bicycle->lockStatus,
            'location'    => $bicycle->latestGpsLog ? [
                'lat'       => (float) $bicycle->latestGpsLog->lat,
                'lng'       => (float) $bicycle->latestGpsLog->lng,
                'speed'     => (float) $bicycle->latestGpsLog->speed,
                'heading'   => (float) $bicycle->latestGpsLog->heading,
                'timestamp' => $bicycle->latestGpsLog->timestamp,
            ] : null,
            'telemetry' => $bicycle->latestTelemetry,
            'pending_commands' => $bicycle->pendingCommands()->where('status', 'pending')->count(),
        ]);
    }

    public function acknowledgeCommand(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'command_id' => ['required', 'integer', 'exists:device_commands,id'],
            'result'     => ['nullable', 'string', 'max:1000'],
            'status'     => ['nullable', 'string', 'in:executed,failed,sent'],
        ]);

        $command = DeviceCommand::where('id', $validated['command_id'])
            ->where('bicycleId', $id)
            ->first();

        if (!$command) {
            return response()->json(['message' => 'Command not found for this bicycle'], 404);
        }

        $updated = $this->iotService->acknowledgeDeviceCommand(
            $command->id,
            $validated['result'] ?? null,
            $validated['status'] ?? 'executed'
        );

        return response()->json([
            'message' => $updated ? 'Command acknowledged' : 'Command update failed',
            'command' => $command->fresh(),
        ]);
    }

    public function command(Request $request, int $id): JsonResponse
    {
        $bicycle = Bicycle::find($id);

        if (!$bicycle) {
            return response()->json(['message' => self::BICYCLE_NOT_FOUND], 404);
        }

        $validated = $request->validate([
            'command' => 'required|string|in:lock,unlock,restart,calibrate,disable,enable',
            'params'  => 'nullable|array',
        ]);

        $deviceCommand = $this->iotService->sendCommand(
            $bicycle->id,
            $validated['command'],
            $validated['params'] ?? [],
            $request->user()
        );

        return response()->json([
            'message' => 'Command queued',
            'command' => $deviceCommand,
        ], 201);
    }
}
