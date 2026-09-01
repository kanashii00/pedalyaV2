<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\Geofence;
use App\Models\GeofenceBreach;
use App\Models\SystemSetting;
use App\Services\GeofenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GeofenceController extends Controller
{
    public function __construct(
        private GeofenceService $geofenceService
    ) {}

    public function index(Request $request): Response
    {
        $config = $this->geofenceService->getConfig();
        $geofence = Geofence::where('isActive', true)->first();

        $warningThreshold = $geofence?->warningThreshold
            ?? SystemSetting::getValue('geofenceWarningThreshold')
            ?? (int) config('services.geofence.warning_threshold', 100);

        $breaches = GeofenceBreach::with(['bicycle'])
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $bicycles = Bicycle::with(['currentRiderUser'])
            ->where('status', '!=', Bicycle::STATUS_REMOVED)
            ->orderBy('id')
            ->get();

        $stats = ['inside' => 0, 'near' => 0, 'outside' => 0, 'noGps' => 0, 'total' => $bicycles->count()];
        foreach ($bicycles as $bike) {
            if ($bike->currentLat === null || $bike->currentLng === null) {
                $bike->zone = ['level' => 'no-gps', 'distance' => null, 'inside' => null];
                $stats['noGps']++;

                continue;
            }
            $zone = $this->geofenceService->checkPoint((float) $bike->currentLat, (float) $bike->currentLng, $config);
            $bike->zone = $zone;
            $level = $zone['level'];
            if ($level === 'breach') {
                $stats['outside']++;
            } elseif (in_array($level, ['approaching', 'warning'], true)) {
                $stats['near']++;
            } else {
                $stats['inside']++;
            }
        }

        $theftIncidents = Accident::whereIn('type', ['geofence_breach', 'geofence_alert', 'theft'])
            ->with('bicycle')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $lockHistory = DeviceCommand::whereIn('command', ['lock', 'unlock'])
            ->with(['bicycle', 'issuer'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return response()->view('admin.geofence', compact('config', 'geofence', 'warningThreshold', 'breaches', 'bicycles', 'stats', 'theftIncidents', 'lockHistory'));
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'centerLat' => ['required', 'numeric', 'between:-90,90'],
            'centerLng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:10', 'max:50000'],
            'shapeType' => ['nullable', 'string', 'in:circle,oval_h,oval_v,rectangle,polygon'],
            'width' => ['nullable', 'numeric', 'min:10', 'max:100000'],
            'height' => ['nullable', 'numeric', 'min:10', 'max:100000'],
            'rotation' => ['nullable', 'numeric', 'between:0,360'],
            'points' => ['nullable', 'array', 'min:3'],
            'points.*.lat' => ['required_with:points', 'numeric', 'between:-90,90'],
            'points.*.lng' => ['required_with:points', 'numeric', 'between:-180,180'],
            'warningThreshold' => ['nullable', 'numeric', 'min:1', 'max:10000'],
            'alertEnabled' => ['sometimes', 'boolean'],
        ]);

        $shapeType = $validated['shapeType'] ?? 'circle';
        $width = $validated['width'] ?? null;
        $height = $validated['height'] ?? null;
        $rotation = $validated['rotation'] ?? null;
        $points = $validated['points'] ?? null;

        $radius = $validated['radius'] ?? 25;

        if ($shapeType === 'circle' && ($validated['radius'] ?? null) === null) {
            return response()->json(['message' => 'Radius is required for a circular geofence.'], 422);
        }

        $geofence = Geofence::updateOrCreate(
            ['isActive' => true],
            [
                'name' => 'Azuela Cove Riding Zone',
                'centerLat' => $validated['centerLat'],
                'centerLng' => $validated['centerLng'],
                'radius' => $radius ?? 25,
                'shapeType' => $shapeType,
                'width' => $width,
                'height' => $height,
                'rotation' => $rotation,
                'points' => $shapeType === 'polygon' ? $points : null,
                'isActive' => true,
                'alertEnabled' => $request->boolean('alertEnabled', true),
                'warningThreshold' => $validated['warningThreshold'] ?? null,
            ]
        );

        Geofence::where('isActive', true)->where('id', '!=', $geofence->id)->update(['isActive' => false]);

        if (isset($validated['warningThreshold'])) {
            SystemSetting::setValue('geofenceWarningThreshold', (string) $validated['warningThreshold']);
        }

        AuditLog::record('geofence_updated', auth()->id(), [
            'geofenceId' => $geofence->id,
            'centerLat' => $validated['centerLat'],
            'centerLng' => $validated['centerLng'],
            'shapeType' => $shapeType,
            'radius' => $radius,
            'width' => $width,
            'height' => $height,
            'rotation' => $rotation,
            'points' => $points,
            'warningThreshold' => $validated['warningThreshold'] ?? null,
        ]);

        return response()->json([
            'message' => 'Geofence updated successfully.',
            'geofence' => $this->geofenceService->getConfig(),
        ]);
    }
}
