<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\DeviceStatus;
use App\Services\GeofenceService;
use App\Services\IoTService;
use App\Services\MaintenanceService;
use App\Services\TheftDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BicycleController extends Controller
{
    public function __construct(
        private IoTService $iotService,
        private MaintenanceService $maintenanceService,
    ) {}

    public function index(Request $request): Response
    {
        $query = Bicycle::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('battery')) {
            $query->where('batteryLevel', '<=', $request->input('battery'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('serialNumber', 'like', "%{$search}%");
            });
        }

        $bicycles = $query->latest()->paginate(20);

        return response()->view('admin.bicycles', compact('bicycles'));
    }

    public function show(int $id): Response
    {
        $bicycle = Bicycle::with(['latestTelemetry', 'latestGpsLog', 'currentRiderUser'])->findOrFail($id);

        return response()->view('admin.bicycles.show', compact('bicycle'));
    }

    public function create(): Response
    {
        $nextSerial = $this->generateSerialNumber();
        $nextQr = $this->generateQrCode();

        return response()->view('admin.bicycles.create', compact('nextSerial', 'nextQr'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'hourlyRate' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'currentLat' => ['nullable', 'numeric'],
            'currentLng' => ['nullable', 'numeric'],
        ]);

        $serialNumber = $this->generateSerialNumber();
        $qrCode = $this->generateQrCode();

        $bicycle = Bicycle::create([
            'name' => $validated['name'],
            'serialNumber' => $serialNumber,
            'model' => $validated['model'] ?? 'Beach Cruiser',
            'description' => $validated['description'] ?? null,
            'hourlyRate' => $validated['hourlyRate'],
            'status' => Bicycle::STATUS_AVAILABLE,
            'batteryLevel' => 100,
            'lockStatus' => 'locked',
            'condition' => 'good',
            'qrCode' => $qrCode,
            'currentLat' => $validated['currentLat'] ?? null,
            'currentLng' => $validated['currentLng'] ?? null,
            'addedBy' => auth()->id(),
        ]);

        AuditLog::record('bicycle_created', auth()->id(), ['bicycleId' => $bicycle->id]);

        if ($bicycle->currentLat !== null && $bicycle->currentLng !== null) {
            $this->reconcilePosition($bicycle);
        }

        return redirect()->route('admin.bicycles.index')->with('success', 'Bicycle "'.$bicycle->name.'" ('.$serialNumber.') added successfully.');
    }

    private function generateSerialNumber(): string
    {
        $year = date('Y');
        $count = Bicycle::where('serialNumber', 'like', "PDY-{$year}-%")->count();
        $seq = str_pad($count + 1, 5, '0', STR_PAD_LEFT);

        return "PDY-{$year}-{$seq}";
    }

    private function generateQrCode(): string
    {
        do {
            $hex = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $qr = "QR-PDY-{$hex}";
        } while (Bicycle::where('qrCode', $qr)->exists());

        return $qr;
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $bicycle = Bicycle::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'serialNumber' => ['sometimes', 'string', 'max:255', "unique:bicycles,serialNumber,{$bicycle->id}"],
            'model' => ['nullable', 'string', 'max:255'],
            'batteryLevel' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'status' => ['sometimes', 'string', 'in:available,rented,maintenance,removed'],
            'hourlyRate' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'currentLat' => ['nullable', 'numeric'],
            'currentLng' => ['nullable', 'numeric'],
        ]);

        $previousStatus = $bicycle->status;
        $newStatus = $validated['status'] ?? $previousStatus;

        if ($newStatus !== $previousStatus) {
            if ($newStatus === 'maintenance' && $previousStatus === 'rented') {
                return back()->withErrors(['status' => 'Cannot move a rented bicycle to maintenance. End the active rental first.']);
            }

            if ($newStatus !== 'maintenance' && $previousStatus === 'maintenance' && ! $this->maintenanceService->canReleaseBicycle($bicycle)) {
                return back()->withErrors(['status' => 'Cannot change status while active maintenance records exist. Complete or cancel them first.']);
            }

            if ($newStatus === 'maintenance') {
                $this->maintenanceService->placeBicycleInMaintenance(
                    $bicycle,
                    'Maintenance initiated from Bicycle Inventory',
                    auth()->id(),
                );

                $validated['lockStatus'] = Bicycle::LOCK_LOCKED;

                AuditLog::record('bicycle_maintenance_auto', auth()->id(), [
                    'bicycleId' => $bicycle->id,
                ]);
            }

            if ($newStatus === 'available' && $previousStatus === 'maintenance') {
                $validated['lockStatus'] = Bicycle::LOCK_LOCKED;
            }
        }

        $bicycle->update($validated);

        if (array_key_exists('currentLat', $validated) || array_key_exists('currentLng', $validated)) {
            $this->reconcilePosition($bicycle);
        }

        AuditLog::record('bicycle_updated', auth()->id(), ['bicycleId' => $bicycle->id]);

        return back()->with('success', 'Bicycle updated successfully.');
    }

    /**
     * Evaluate the geofence whenever a bicycle's coordinates are written so an
     * outside position immediately gets a single open theft alert and an inside
     * position resolves it — keeping red pins on the shared map and the Theft
     * Alert Log in sync no matter which path wrote the position.
     */
    private function reconcilePosition(Bicycle $bicycle): void
    {
        $lat = $bicycle->currentLat;
        $lng = $bicycle->currentLng;
        if ($lat === null || $lng === null) {
            return;
        }

        $geofenceService = app(GeofenceService::class);
        $theftService = app(TheftDetectionService::class);
        $result = $geofenceService->checkPointInGeofence((float) $lat, (float) $lng);

        Log::debug('[AdminBicycle] reconcile position', [
            'bicycleId' => $bicycle->id,
            'lat' => $lat,
            'lng' => $lng,
            'inside' => $result['inside'] ?? null,
            'level' => $result['level'] ?? null,
        ]);

        if (! ($result['inside'] ?? false)) {
            $theftService->ensureActiveAlertForOutside(
                $bicycle,
                (float) $lat,
                (float) $lng,
                $result['distanceOutside'] ?? null,
                $result
            );
        } elseif (! in_array($result['level'] ?? null, ['approaching', 'warning'], true)) {
            $theftService->resolveAlertOnReturn($bicycle);
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $bicycle = Bicycle::findOrFail($id);

        if ($bicycle->status === Bicycle::STATUS_MAINTENANCE) {
            return back()->withErrors(['bicycle' => 'Cannot remove a bicycle that is under maintenance. Complete maintenance first.']);
        }

        if ($bicycle->rentals()->whereIn('status', ['active', 'pending', 'overdue'])->exists()) {
            return back()->withErrors(['bicycle' => 'Cannot remove bicycle with active or pending rentals.']);
        }

        $bicycle->update([
            'status' => Bicycle::STATUS_REMOVED,
            'removedAt' => now(),
            'removedBy' => auth()->id(),
        ]);

        AuditLog::record('bicycle_removed', auth()->id(), ['bicycleId' => $bicycle->id]);

        return back()->with('success', 'Bicycle removed successfully.');
    }

    public function lock(Request $request, int $id): RedirectResponse
    {
        $bicycle = Bicycle::findOrFail($id);

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:lock,unlock'],
        ]);

        if (in_array($bicycle->status, ['rented', 'maintenance'])) {
            return back()->withErrors(['bicycle' => 'Cannot change lock status while bicycle is rented or in maintenance.']);
        }

        $this->iotService->sendCommand($bicycle->id, $validated['action'], [], auth()->user());

        AuditLog::record('bicycle_lock_'.$validated['action'], auth()->id(), [
            'bicycleId' => $bicycle->id,
            'lockStatus' => $validated['action'] === 'lock' ? 'locked' : 'unlocked',
        ]);

        return back()->with('success', "Remote {$validated['action']} command sent to device.");
    }

    public function telemetry(int $id): JsonResponse
    {
        $bicycle = Bicycle::findOrFail($id);

        $deviceStatus = DeviceStatus::where('bicycleId', $id)
            ->latest('eventTimestamp')
            ->first();

        return response()->json([
            'bicycle' => $bicycle,
            'device_status' => $deviceStatus,
        ]);
    }
}
