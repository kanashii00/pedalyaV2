<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\AuditLog;
use App\Models\Bicycle;
use App\Models\GeofenceBreach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class TheftController extends Controller
{
    public function index(): Response
    {
        $alerts = Accident::with(['bicycle', 'rider'])
            ->whereIn('type', ['geofence_breach', 'geofence_alert'])
            ->latest()
            ->paginate(20);

        $bicycles = Bicycle::whereIn('status', ['rented', 'locked', 'maintenance'])->get();

        $openBreachCount = GeofenceBreach::whereNull('resolvedAt')->count();

        return response()->view('admin.theft', compact('alerts', 'bicycles', 'openBreachCount'));
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
