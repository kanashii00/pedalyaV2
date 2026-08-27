<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\MaintenanceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MaintenanceController extends Controller
{
    public function index(Request $request): Response
    {
        $statusFilter = $request->input('status');
        $activeStatuses = [MaintenanceRecord::STATUS_SCHEDULED, MaintenanceRecord::STATUS_IN_PROGRESS];
        $historyStatuses = [MaintenanceRecord::STATUS_COMPLETED, MaintenanceRecord::STATUS_CANCELLED];

        $baseQuery = MaintenanceRecord::with('bicycle');

        if ($request->filled('bicycleId')) {
            $baseQuery->where('bicycleId', $request->input('bicycleId'));
        }

        if ($request->filled('date_from')) {
            $baseQuery->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $baseQuery->where('created_at', '<=', $request->input('date_to'));
        }

        if ($statusFilter && in_array($statusFilter, $historyStatuses)) {
            $maintenance = (clone $baseQuery)->whereIn('status', $historyStatuses)->latest()->paginate(20);
            $maintenanceHistory = $maintenance;
            $activeMaintenance = collect();
            $showHistory = true;
        } else {
            $activeQuery = (clone $baseQuery)->whereIn('status', $activeStatuses);
            if ($statusFilter && in_array($statusFilter, $activeStatuses)) {
                $activeQuery->where('status', $statusFilter);
            }
            $activeMaintenance = $activeQuery->latest()->paginate(20);

            $maintenanceHistory = (clone $baseQuery)->whereIn('status', $historyStatuses)->latest()->paginate(10);
            $maintenance = $activeMaintenance;
            $showHistory = false;
        }

        $bicycles = Bicycle::where('status', '!=', Bicycle::STATUS_REMOVED)->orderBy('name')->get();

        return response()->view('admin.maintenance', compact(
            'maintenance',
            'activeMaintenance',
            'maintenanceHistory',
            'bicycles',
            'showHistory',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bicycleId' => ['required', 'exists:bicycles,id'],
            'description' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'string', 'in:routine,repair,battery,lock_mechanism,gps_module,frame,other'],
            'severity' => ['nullable', 'string', 'in:low,medium,high,critical'],
            'scheduledDate' => ['nullable', 'date'],
            'estimatedCost' => ['nullable', 'numeric', 'min:0'],
            'technician' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $bicycle = Bicycle::find($validated['bicycleId']);

        $existing = MaintenanceRecord::where('bicycleId', $validated['bicycleId'])
            ->whereIn('status', [MaintenanceRecord::STATUS_SCHEDULED, MaintenanceRecord::STATUS_IN_PROGRESS])
            ->first();

        if ($existing) {
            return back()->withErrors(['bicycleId' => 'This bicycle already has an active maintenance record (#'.$existing->id.').']);
        }

        $maintenance = MaintenanceRecord::create([
            'bicycleId' => $validated['bicycleId'],
            'bicycleName' => $bicycle?->name ?? '',
            'description' => $validated['description'],
            'type' => $validated['type'],
            'severity' => $validated['severity'] ?? 'low',
            'scheduledDate' => $validated['scheduledDate'] ?? null,
            'estimatedCost' => $validated['estimatedCost'] ?? null,
            'technician' => $validated['technician'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => MaintenanceRecord::STATUS_SCHEDULED,
            'createdBy' => auth()->id(),
        ]);

        if ($bicycle && $bicycle->status !== Bicycle::STATUS_MAINTENANCE) {
            $bicycle->update([
                'status' => Bicycle::STATUS_MAINTENANCE,
                'lockStatus' => Bicycle::LOCK_LOCKED,
            ]);
        }

        AuditLog::record('maintenance_created', auth()->id(), [
            'maintenanceId' => $maintenance->id,
            'bicycleId' => $validated['bicycleId'],
        ]);

        return back()->with('success', 'Maintenance record created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $maintenance = MaintenanceRecord::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:scheduled,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($maintenance->status, [MaintenanceRecord::STATUS_COMPLETED, MaintenanceRecord::STATUS_CANCELLED])) {
            return back()->withErrors(['status' => 'Cannot change the status of a completed or cancelled record.']);
        }

        if ($validated['status'] === 'completed') {
            $validated['completedDate'] = now();
        }

        $maintenance->update($validated);

        AuditLog::record('maintenance_updated', auth()->id(), [
            'maintenanceId' => $maintenance->id,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Maintenance record updated.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $maintenance = MaintenanceRecord::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:scheduled,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($maintenance->status, [MaintenanceRecord::STATUS_COMPLETED, MaintenanceRecord::STATUS_CANCELLED])) {
            return back()->withErrors(['status' => 'Cannot change the status of a completed or cancelled record.']);
        }

        if ($validated['status'] === 'completed') {
            $validated['completedDate'] = now();
        }

        $maintenance->update($validated);

        AuditLog::record('maintenance_status_updated', auth()->id(), [
            'maintenanceId' => $maintenance->id,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Maintenance status updated.');
    }
}
