<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuditController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('userId')) {
            $query->where('userId', $request->input('userId'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->latest()->paginate(30);

        $users = \App\Models\User::orderBy('name')->get();

        return response()->view('admin.audit', compact('logs', 'users'));
    }
}
