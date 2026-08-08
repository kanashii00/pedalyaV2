@extends('layouts.admin')

@section('title', 'Accident Reports')

@section('page-header')
    <h1>Accident Reports</h1>
    <p>View and manage accident reports</p>
@endsection

@section('actions')
    <button class="btn-admin btn-admin--secondary" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
    </button>
@endsection

@section('content')
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <div class="grow"><i class="bi bi-search"></i><input type="text" data-table-search placeholder="Search accident reports..."></div>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">Bicycle <span class="sort-ind"></span></th>
                <th class="sortable">Type <span class="sort-ind"></span></th>
                <th class="sortable">Severity <span class="sort-ind"></span></th>
                <th class="sortable">Description <span class="sort-ind"></span></th>
                <th class="sortable">Location <span class="sort-ind"></span></th>
                <th class="sortable">Timestamp <span class="sort-ind"></span></th>
                <th class="sortable">Acknowledged <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $accident)
            <tr>
                <td data-label="Bicycle" class="cell-title">
                    <a href="{{ route('admin.bicycles.show', $accident->bicycleId) }}" style="text-decoration:none;">
                        {{ $accident->bicycle->name ?? $accident->bicycleId }}
                    </a>
                </td>
                <td data-label="Type">{{ ucfirst($accident->type) }}</td>
                <td data-label="Severity">
                    @switch($accident->severity)
                        @case('minor')
                            <x-admin.badge type="info" label="Minor"/>
                            @break
                        @case('moderate')
                            <x-admin.badge type="warning" label="Moderate"/>
                            @break
                        @case('major')
                            <x-admin.badge type="danger" label="Major"/>
                            @break
                        @case('critical')
                            <x-admin.badge type="danger" label="Critical"/>
                            @break
                        @default
                            <x-admin.badge :label="ucfirst($accident->severity)"/>
                    @endswitch
                </td>
                <td data-label="Description" class="text-truncate" style="max-width: 200px;" title="{{ $accident->description }}">
                    {{ $accident->description }}
                </td>
                <td data-label="Location" class="text-truncate" style="max-width: 150px;" title="{{ $accident->gpsLocation ? $accident->gpsLocation['lat'] . ', ' . $accident->gpsLocation['lng'] : '' }}">
                    @if($accident->gpsLocation)
                        {{ $accident->gpsLocation['lat'] }}, {{ $accident->gpsLocation['lng'] }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td data-label="Timestamp"><small>{{ $accident->created_at->format('M d, Y H:i') }}</small></td>
                <td data-label="Acknowledged">
                    @if($accident->acknowledged)
                        <x-admin.badge type="success" label="Acknowledged"/>
                    @else
                        <x-admin.badge type="warning" label="Pending"/>
                    @endif
                </td>
                <td data-label="Actions">
                    <div class="d-flex gap-1">
                        <button class="btn-admin btn-admin--secondary btn-admin--sm" title="View Details"
                            data-id="{{ $accident->id }}"
                            data-bicycle="{{ $accident->bicycle->name ?? $accident->bicycleId }}"
                            data-type="{{ $accident->type }}"
                            data-severity="{{ $accident->severity }}"
                            data-location="{{ $accident->gpsLocation ? $accident->gpsLocation['lat'] . ', ' . $accident->gpsLocation['lng'] : '' }}"
                            data-acknowledged="{{ $accident->acknowledged ? '1' : '0' }}"
                            data-description="{{ $accident->description }}"
                            data-timestamp="{{ $accident->created_at }}"
                            onclick="fillAccident(this); PedalyaModal.open('accidentDetailModal')">
                            <i class="bi bi-eye"></i>
                        </button>
                        @unless($accident->acknowledged)
                        <form action="{{ route('admin.accidents.acknowledge', $accident->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn-admin btn-admin--soft btn-admin--sm" title="Acknowledge"
                                data-confirm="Acknowledge this accident report?">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        @endunless
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">
                    <x-admin.empty-state icon="bi-activity" title="No accident reports found" />
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Detail Modal -->
<div class="admin-modal" id="accidentDetailModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <h3><i class="bi bi-activity me-2"></i>Accident Report Details</h3>
            <button type="button" class="admin-icon-btn" data-modal-close><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="admin-modal__body">
            <div class="admin-form">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Bicycle</label>
                            <p id="modalBicycle" class="mb-0">—</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <p id="modalType" class="mb-0">—</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Severity</label>
                            <p id="modalSeverity" class="mb-0">—</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Timestamp</label>
                            <p id="modalTimestamp" class="mb-0">—</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <p id="modalLocation" class="mb-0">—</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Acknowledged</label>
                            <p id="modalAcknowledged" class="mb-0">—</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <p id="modalDescription" class="mb-0">—</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="admin-modal__foot">
            <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function formatSeverityBadge(severity) {
    const map = {
        'minor': '<span class="badge-admin badge-admin--info">Minor</span>',
        'moderate': '<span class="badge-admin badge-admin--warning">Moderate</span>',
        'major': '<span class="badge-admin badge-admin--danger">Major</span>',
        'critical': '<span class="badge-admin badge-admin--danger">Critical</span>'
    };
    return map[severity] || '<span class="badge-admin badge-admin--neutral">' + severity + '</span>';
}

function fillAccident(btn) {
    document.getElementById('modalBicycle').textContent = btn.dataset.bicycle || '—';
    document.getElementById('modalType').textContent = btn.dataset.type ? btn.dataset.type.charAt(0).toUpperCase() + btn.dataset.type.slice(1) : '—';
    document.getElementById('modalSeverity').innerHTML = formatSeverityBadge(btn.dataset.severity);
    document.getElementById('modalTimestamp').textContent = btn.dataset.timestamp || '—';
    document.getElementById('modalLocation').textContent = btn.dataset.location || '—';
    document.getElementById('modalAcknowledged').innerHTML = btn.dataset.acknowledged === '1'
        ? '<span class="badge-admin badge-admin--success">Yes</span>'
        : '<span class="badge-admin badge-admin--warning">No</span>';
    document.getElementById('modalDescription').textContent = btn.dataset.description || '—';
}
</script>
@endsection