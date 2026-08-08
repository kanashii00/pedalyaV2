<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\DeviceStatus;
use App\Services\IoTService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BicycleController extends Controller
{
    public function __construct(
        private IoTService $iotService
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'serialNumber'  => ['required', 'string', 'max:255', 'unique:bicycles,serialNumber'],
            'model'         => ['nullable', 'string', 'max:255'],
            'hourlyRate'    => ['required', 'numeric', 'min:0'],
            'description'   => ['nullable', 'string', 'max:1000'],
        ]);

        $bicycle = Bicycle::create([
            'name'          => $validated['name'],
            'serialNumber'  => $validated['serialNumber'],
            'model'         => $validated['model'] ?? null,
            'description'   => $validated['description'] ?? null,
            'hourlyRate'    => $validated['hourlyRate'],
            'status'        => Bicycle::STATUS_AVAILABLE,
            'batteryLevel'  => 100,
            'lockStatus'    => 'locked',
            'addedBy'       => auth()->id(),
        ]);

        AuditLog::record('bicycle_created', auth()->id(), ['bicycleId' => $bicycle->id]);

        return back()->with('success', 'Bicycle added successfully.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $bicycle = Bicycle::findOrFail($id);

        $validated = $request->validate([
            'name'          => ['sometimes', 'string', 'max:255'],
            'serialNumber'  => ['sometimes', 'string', 'max:255', "unique:bicycles,serialNumber,{$bicycle->id}"],
            'model'         => ['nullable', 'string', 'max:255'],
            'batteryLevel'  => ['sometimes', 'integer', 'min:0', 'max:100'],
            'status'        => ['sometimes', 'string', 'in:available,rented,maintenance,locked,removed'],
            'hourlyRate'    => ['sometimes', 'numeric', 'min:0'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'currentLat'    => ['nullable', 'numeric'],
            'currentLng'    => ['nullable', 'numeric'],
        ]);

        $bicycle->update($validated);

        AuditLog::record('bicycle_updated', auth()->id(), ['bicycleId' => $bicycle->id]);

        return back()->with('success', 'Bicycle updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $bicycle = Bicycle::findOrFail($id);

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

        $this->iotService->sendCommand($bicycle->id, $validated['action'], [], auth()->user());

        AuditLog::record('bicycle_lock_' . $validated['action'], auth()->id(), [
            'bicycleId' => $bicycle->id,
            'lockStatus' => $validated['action'] === 'lock' ? 'locked' : 'unlocked',
        ]);

        return back()->with('success', "Remote {$validated['action']} command sent to device.");
    }

    public function telemetry(int $id): Response
    {
        $bicycle = Bicycle::findOrFail($id);

        $deviceStatus = DeviceStatus::where('bicycleId', $id)
            ->latest('eventTimestamp')
            ->first();

        return response()->json([
            'bicycle'       => $bicycle,
            'device_status' => $deviceStatus,
        ]);
    }
}
