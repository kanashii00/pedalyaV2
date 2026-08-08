<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\MaintenanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MaintenanceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = MaintenanceRecord::with('bicycle');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('bicycleId')) {
            $query->where('bicycleId', $request->input('bicycleId'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $maintenance = $query->latest()->paginate(20);

        $bicycles = Bicycle::where('status', '!=', Bicycle::STATUS_REMOVED)->orderBy('name')->get();

        return response()->view('admin.maintenance', compact('maintenance', 'bicycles'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'bicycleId'     => ['required', 'exists:bicycles,id'],
            'description'   => ['required', 'string', 'max:1000'],
            'type'          => ['required', 'string', 'in:routine,repair,battery,lock_mechanism,gps_module,frame,other'],
            'severity'      => ['nullable', 'string', 'in:low,medium,high,critical'],
            'scheduledDate' => ['nullable', 'date'],
            'estimatedCost' => ['nullable', 'numeric', 'min:0'],
            'technician'    => ['nullable', 'string', 'max:255'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $bicycle = Bicycle::find($validated['bicycleId']);

        $maintenance = MaintenanceRecord::create([
            'bicycleId'     => $validated['bicycleId'],
            'bicycleName'   => $bicycle?->name ?? '',
            'description'   => $validated['description'],
            'type'          => $validated['type'],
            'severity'      => $validated['severity'] ?? 'low',
            'scheduledDate' => $validated['scheduledDate'] ?? null,
            'estimatedCost' => $validated['estimatedCost'] ?? null,
            'technician'    => $validated['technician'] ?? null,
            'notes'         => $validated['notes'] ?? null,
            'status'        => MaintenanceRecord::STATUS_SCHEDULED,
            'createdBy'     => auth()->id(),
        ]);

        if ($bicycle && $bicycle->status === Bicycle::STATUS_AVAILABLE) {
            $bicycle->update(['status' => Bicycle::STATUS_MAINTENANCE]);
        }

        AuditLog::record('maintenance_created', auth()->id(), [
            'maintenanceId' => $maintenance->id,
            'bicycleId' => $validated['bicycleId'],
        ]);

        return back()->with('success', 'Maintenance record created.');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $maintenance = MaintenanceRecord::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:scheduled,in_progress,completed,cancelled'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $updateData = $validated;
        if ($validated['status'] === 'completed') {
            $updateData['completedDate'] = now();

            $bicycle = Bicycle::find($maintenance->bicycleId);
            if ($bicycle && $bicycle->status === Bicycle::STATUS_MAINTENANCE) {
                $bicycle->update([
                    'status' => Bicycle::STATUS_AVAILABLE,
                    'lastMaintenanceDate' => now(),
                    'condition' => 'good',
                ]);
            }
        }

        $maintenance->update($updateData);

        AuditLog::record('maintenance_updated', auth()->id(), [
            'maintenanceId' => $maintenance->id,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Maintenance record updated.');
    }

    public function updateStatus(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $maintenance = MaintenanceRecord::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:scheduled,in_progress,completed,cancelled'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $updateData = $validated;
        if ($validated['status'] === 'completed') {
            $updateData['completedDate'] = now();

            $bicycle = Bicycle::find($maintenance->bicycleId);
            if ($bicycle && $bicycle->status === Bicycle::STATUS_MAINTENANCE) {
                $bicycle->update([
                    'status' => Bicycle::STATUS_AVAILABLE,
                    'lastMaintenanceDate' => now(),
                    'condition' => 'good',
                ]);
            }
        }

        $maintenance->update($updateData);

        AuditLog::record('maintenance_status_updated', auth()->id(), [
            'maintenanceId' => $maintenance->id,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Maintenance status updated.');
    }
}
