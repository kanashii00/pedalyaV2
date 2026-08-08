@extends('layouts.admin')

@section('title', 'Audit Log')

@section('page-header')
    <h1>Audit Log</h1>
    <p>Track system activities and changes</p>
@endsection

@section('actions')
<button type="button" class="btn-admin btn-admin--secondary" onclick="clearFilters()">
    <i class="bi bi-x-lg me-1"></i>Clear Filters
</button>
@endsection

@section('content')
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <form method="GET" action="{{ route('admin.audit-log.index') }}" id="auditFilterForm" class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="d-block text-muted mb-1" style="font-size:11px;font-weight:600;">Action Type</label>
                <select class="form-select form-select-sm" name="action" style="min-width:160px;">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="rental_started" {{ request('action') == 'rental_started' ? 'selected' : '' }}>Rental Started</option>
                    <option value="rental_ended" {{ request('action') == 'rental_ended' ? 'selected' : '' }}>Rental Ended</option>
                    <option value="payment" {{ request('action') == 'payment' ? 'selected' : '' }}>Payment</option>
                    <option value="accident" {{ request('action') == 'accident' ? 'selected' : '' }}>Accident</option>
                    <option value="maintenance" {{ request('action') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="settings_changed" {{ request('action') == 'settings_changed' ? 'selected' : '' }}>Settings Changed</option>
                </select>
            </div>
            <div>
                <label class="d-block text-muted mb-1" style="font-size:11px;font-weight:600;">User</label>
                <select class="form-select form-select-sm" name="userId" style="min-width:160px;">
                    <option value="">All Users</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('userId') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="d-block text-muted mb-1" style="font-size:11px;font-weight:600;">Date From</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div>
                <label class="d-block text-muted mb-1" style="font-size:11px;font-weight:600;">Date To</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn-admin btn-admin--primary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="sortable">Timestamp <span class="sort-ind"></span></th>
                    <th class="sortable">Action <span class="sort-ind"></span></th>
                    <th class="sortable">User <span class="sort-ind"></span></th>
                    <th>Details <span class="sort-ind"></span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $actionBadgeType = match ($log->action) {
                            'created' => 'success',
                            'updated' => 'info',
                            'deleted' => 'danger',
                            'login' => 'info',
                            'logout' => 'success',
                            'rental_started' => 'success',
                            'rental_ended' => 'success',
                            'payment' => 'success',
                            'accident' => 'danger',
                            'maintenance' => 'neutral',
                            'settings_changed' => 'danger',
                            default => 'neutral',
                        };
                        $actionLabel = ucfirst(str_replace('_', ' ', $log->action));
                    @endphp
                    <tr>
                        <td data-label="Timestamp" class="text-nowrap">{{ ($log->timestamp ?? $log->created_at)->format('M d, Y H:i:s') }}</td>
                        <td data-label="Action"><x-admin.badge :type="$actionBadgeType" :label="$actionLabel"/></td>
                        <td data-label="User">{{ $log->user->name ?? 'System' }}</td>
                        <td data-label="Details">
                            @if($log->details)
                                <button class="btn-admin btn-admin--soft btn-admin--sm" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#details-{{ $log->id }}"
                                    aria-expanded="false">
                                    <i class="bi bi-code-slash"></i> View
                                </button>
                                <div class="collapse mt-1" id="details-{{ $log->id }}">
                                    <pre class="bg-dark text-light p-2 rounded mb-0"
                                        style="max-height: 300px; overflow-y: auto; font-size: 0.75rem;">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-admin.empty-state icon="bi-journal-text" title="No audit log entries found" message="System activities will appear here as they happen."/>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($logs, 'links'))
        <div class="admin-table-foot">
            <span>Showing {{ $logs->total() }} records</span>
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function clearFilters() {
    window.location.href = '{{ route("admin.audit-log.index") }}';
}
</script>
@endsection
