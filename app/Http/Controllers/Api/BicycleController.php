<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BicycleResource;
use App\Models\Bicycle;
use App\Models\GpsLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BicycleController extends Controller
{
    public function nearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat'    => 'required|numeric|between:-90,90',
            'lng'    => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:100|max:10000',
        ]);

        $lat = $validated['lat'];
        $lng = $validated['lng'];
        $radiusMeters = $validated['radius'] ?? 1000;

        $latDelta = $radiusMeters / 111320;
        $lngDelta = $radiusMeters / (111320 * cos(deg2rad($lat)));

        $bicycles = Bicycle::query()
            ->where('status', 'available')
            ->where('currentLat', '>=', $lat - $latDelta)
            ->where('currentLat', '<=', $lat + $latDelta)
            ->where('currentLng', '>=', $lng - $lngDelta)
            ->where('currentLng', '<=', $lng + $lngDelta)
            ->get()
            ->map(function ($bicycle) use ($lat, $lng) {
                $bicycle->distance = $this->haversine($lat, $lng, $bicycle->currentLat, $bicycle->currentLng);
                return $bicycle;
            })
            ->sortBy('distance')
            ->values();

        return response()->json([
            'bicycles' => BicycleResource::collection($bicycles),
            'count'    => $bicycles->count(),
            'radius'   => $radiusMeters,
        ]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => 'nullable|string|in:available,rented,maintenance,removed',
            'model'  => 'nullable|string',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Bicycle::query();

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['model'])) {
            $query->where('model', $validated['model']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serialNumber', 'like', "%{$search}%");
            });
        }

        $bicycles = $query->orderByDesc('updated_at')
            ->paginate($validated['per_page'] ?? 20);

        return BicycleResource::collection($bicycles);
    }

    public function show(int $id): BicycleResource|JsonResponse
    {
        $bicycle = Bicycle::with(['latestTelemetry', 'latestGpsLog'])->find($id);

        if (!$bicycle) {
            return response()->json(['message' => 'Bicycle not found'], 404);
        }

        return new BicycleResource($bicycle);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'serialNumber' => 'required|string|unique:bicycles,serialNumber',
            'model'        => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'hourlyRate'   => 'nullable|numeric|min:0',
            'currentLat'   => 'nullable|numeric|between:-90,90',
            'currentLng'   => 'nullable|numeric|between:-180,180',
            'batteryLevel' => 'nullable|numeric|between:0,100',
        ]);

        $validated['status'] = 'available';
        $validated['batteryLevel'] = $validated['batteryLevel'] ?? 100;

        $bicycle = Bicycle::create($validated);

        return response()->json([
            'message'  => 'Bicycle created successfully',
            'bicycle'  => new BicycleResource($bicycle),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $bicycle = Bicycle::find($id);

        if (!$bicycle) {
            return response()->json(['message' => 'Bicycle not found'], 404);
        }

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'serialNumber' => 'sometimes|string|unique:bicycles,serialNumber,' . $id,
            'model'        => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'hourlyRate'   => 'nullable|numeric|min:0',
            'currentLat'   => 'nullable|numeric|between:-90,90',
            'currentLng'   => 'nullable|numeric|between:-180,180',
            'batteryLevel' => 'nullable|numeric|between:0,100',
            'status'       => 'nullable|string|in:available,rented,maintenance,removed',
        ]);

        $bicycle->update($validated);

        return response()->json([
            'message'  => 'Bicycle updated successfully',
            'bicycle'  => new BicycleResource($bicycle->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $bicycle = Bicycle::find($id);

        if (!$bicycle) {
            return response()->json(['message' => 'Bicycle not found'], 404);
        }

        $bicycle->delete();

        return response()->json(['message' => 'Bicycle deleted successfully']);
    }

    public function lock(Request $request, int $id): JsonResponse
    {
        $bicycle = Bicycle::find($id);

        if (!$bicycle) {
            return response()->json(['message' => 'Bicycle not found'], 404);
        }

        $validated = $request->validate([
            'locked' => 'required|boolean',
        ]);

        $command = app(\App\Services\IoTService::class)->sendCommand(
            $bicycle->id,
            $validated['locked'] ? 'lock' : 'unlock',
            [],
            $request->user()
        );

        return response()->json([
            'message'  => $validated['locked'] ? 'Lock command queued' : 'Unlock command queued',
            'command'  => $command,
            'bicycle'  => new BicycleResource($bicycle->fresh()),
        ]);
    }

    public function telemetry(int $id): JsonResponse
    {
        $bicycle = Bicycle::with('latestTelemetry')->find($id);

        if (!$bicycle) {
            return response()->json(['message' => 'Bicycle not found'], 404);
        }

        return response()->json([
            'telemetry' => $bicycle->latestTelemetry,
        ]);
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
