<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class AccidentController extends Controller
{
    public function index(): Response
    {
        $incidents = Accident::with(['bicycle', 'rider'])
            ->whereIn('type', ['accident', 'impact_detected'])
            ->latest()
            ->paginate(20);

        return response()->view('admin.accidents', compact('incidents'));
    }

    public function acknowledge(int $id): RedirectResponse
    {
        $incident = Accident::findOrFail($id);

        $incident->update([
            'acknowledged' => true,
            'actionTaken' => 'Acknowledged by administrator',
        ]);

        AuditLog::record('accident_acknowledged', auth()->id(), [
            'accidentId' => $incident->id,
            'type' => $incident->type,
        ]);

        return redirect()->back()->with('success', 'Accident report acknowledged.');
    }
}
