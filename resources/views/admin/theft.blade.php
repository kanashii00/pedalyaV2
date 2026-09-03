@extends('layouts.admin')

@section('title', 'Theft Alerts')

@section('styles')
<style>
    #theftMap {
        width: 100%;
        height: 380px;
        border-radius: 14px;
        overflow: hidden;
    }
</style>
@endsection

@section('page-header')
    <h1>Theft Alerts</h1>
    <p>Monitor boundary breaches and potential theft incidents</p>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-4" data-theft-kpi="openBreaches">
        <x-admin.kpi title="Open Breaches" value="{{ $openBreachCount }}"
                     icon="bi-shield-exclamation" color="var(--danger)" />
    </div>
    <div class="col-md-4" data-theft-kpi="unacknowledged">
        <x-admin.kpi title="Unacknowledged Alerts" value="{{ $alerts->where('acknowledged', false)->count() }}"
                     icon="bi-bell" color="var(--warning)" />
    </div>
    <div class="col-md-4" data-theft-kpi="atRisk">
        <x-admin.kpi title="At-Risk Bicycles" value="{{ $bicycles->count() }}"
                     icon="bi-bicycle" color="var(--brand)" />
    </div>
</div>

{{-- GeoLibre 3D Map (shared with Monitoring): same geofence + live bicycle markers --}}
<x-admin.card title="Live GeoLibre 3D Map" :flush="true" bodyClass="p-0 position-relative">
    <x-slot name="tools">
        <small class="text-muted me-3">{{ count($mapBicycles ?? []) }} bicycle(s)</small>
        <small class="text-muted">
            Geofence: <span id="theftGeofenceRadiusText">{{ number_format($geofence['radius'] ?? 0, 0) }}m</span>
            <span id="theftGeofenceAlertBadge">@if(($geofence['alertEnabled'] ?? false))
                <x-admin.badge type="success" label="Alerts ON"/>
            @endif</span>
        </small>
        <button class="btn-admin btn-admin--ghost btn-admin--sm" id="theftCenterMapBtn" title="Center on geofence">
            <i class="bi bi-crosshair"></i>
        </button>
        <button class="btn-admin btn-admin--ghost btn-admin--sm" id="theftRefreshMapBtn" title="Refresh positions">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </x-slot>
    <div id="theftMap"></div>
    <div class="map-legend">
        <div><span class="dot" style="background:#2ecc71;"></span>Inside Zone <span class="legend-count" data-count="safe">0</span></div>
        <div><span class="dot" style="background:#f39c12;"></span>Near Boundary <span class="legend-count" data-count="near">0</span></div>
        <div><span class="dot" style="background:#e74c3c;"></span>Outside Zone <span class="legend-count" data-count="outside">0</span></div>
        <div><span class="dot" style="background:#27ae60;"></span>Geofence Boundary</div>
        <div><span class="dot" style="background:#8e44ad;"></span>&#9888; Has theft alert</div>
    </div>
</x-admin.card>

{{-- Alerts Table --}}
<x-admin.card title="Theft Alert Log" :flush="true">
    <x-slot name="tools">
        <span id="theftUnreadBadge"><x-admin.badge type="danger" :label="$alerts->where('acknowledged', false)->count() . ' unread'" /></span>
    </x-slot>
    <div class="admin-table-wrap">
        <div class="admin-table-toolbar">
            <div class="grow"><i class="bi bi-search"></i><input type="text" data-table-search placeholder="Search alerts..."></div>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="sortable">Bicycle <span class="sort-ind"></span></th>
                    <th class="sortable">Rider <span class="sort-ind"></span></th>
                    <th class="sortable">Location <span class="sort-ind"></span></th>
                    <th class="sortable">Distance from Boundary <span class="sort-ind"></span></th>
                    <th class="sortable">Status <span class="sort-ind"></span></th>
                    <th class="sortable">Timestamp <span class="sort-ind"></span></th>
                    <th class="sortable">Acknowledged <span class="sort-ind"></span></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="theftTableBody">
                @forelse($alerts as $alert)
                    @php
                        $loc = is_array($alert->gpsLocation) ? $alert->gpsLocation : [];
                        $lat = $loc['lat'] ?? null;
                        $lng = $loc['lng'] ?? null;
                    @endphp
                    <tr>
                        <td data-label="Bicycle" class="cell-title">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-bicycle me-2 text-muted"></i>
                                {{ $alert->bicycle->name ?? 'Unknown' }}
                                <small class="text-muted ms-1">#{{ $alert->bicycleId }}</small>
                            </div>
                        </td>
                        <td data-label="Rider">
                            @php $riderName = $alert->bicycle?->currentRiderUser?->name ?? null; @endphp
                            @if($riderName)
                                <small>{{ $riderName }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Location">
                            @if($lat && $lng)
                                <small>{{ number_format($lat, 5) }}, {{ number_format($lng, 5) }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Distance from Boundary">
                            @php $dist = $alert->breachDistance ?? $alert->distanceFromBoundary; @endphp
                            @if($dist !== null)
                                <span class="fw-semibold text-danger">
                                    {{ number_format($dist, 2) }}m outside
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Status">
                            @if($alert->status === 'open')
                                <x-admin.badge type="danger" label="Open" />
                            @elseif($alert->status === 'returned')
                                <x-admin.badge type="success" label="Returned" />
                            @elseif($alert->status === 'resolved')
                                <x-admin.badge type="success" label="Resolved" />
                            @else
                                <x-admin.badge type="secondary" :label="ucfirst($alert->status ?? 'Unknown')" />
                            @endif
                        </td>
                        <td data-label="Timestamp">
                            <small>{{ $alert->created_at->format('M d, Y h:i A') }}</small><br>
                            <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                        </td>
                        <td data-label="Acknowledged">
                            @if($alert->acknowledged)
                                <x-admin.badge type="success" label="Acknowledged" />
                            @else
                                <x-admin.badge type="warning" label="Pending" />
                            @endif
                        </td>
                        <td data-label="Actions">
                            @if(!$alert->acknowledged)
                                <form action="{{ route('admin.theft-alerts.acknowledge', $alert->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn-admin btn-admin--soft btn-admin--sm" title="Acknowledge" data-confirm="Mark this theft alert as acknowledged?">
                                        <i class="bi bi-check-lg me-1"></i>Acknowledge
                                    </button>
                                </form>
                            @else
                                <span class="text-muted"><small>Handled</small></span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            <x-admin.empty-state icon="bi-shield-check" title="No theft alerts" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($alerts, 'links'))
        <div class="admin-table-foot">
            {{ $alerts->withQueryString()->links() }}
        </div>
    @endif
</x-admin.card>
@endsection

@section('scripts')
<script src="{{ asset('js/geolibre-map.js') }}"></script>
<script>
(function () {
    var geofence = {!! json_encode($geofence) !!};
    var bicycles = {!! $mapBicycles->map(fn($b) => [
        'id' => $b->id,
        'name' => $b->name,
        'lat' => $b->currentLat !== null ? (float) $b->currentLat : null,
        'lng' => $b->currentLng !== null ? (float) $b->currentLng : null,
        'status' => $b->status,
        'battery' => $b->batteryLevel,
        'locked' => $b->lockStatus === 'locked',
        'heartbeat' => $b->lastHeartbeat?->toISOString(),
        'zone' => $b->zone['level'] ?? 'unknown',
        'distance' => $b->zone['distance'] ?? null,
    ])->values()->toJson() !!};
    var alertBicycles = {!! json_encode($openTheftAlerts) !!};
    var liveUrl = {!! json_encode(route('admin.theft-alerts.live')) !!};
    var ackUrl = {!! json_encode(route('admin.theft-alerts.acknowledge', '__ID__')) !!};
    var POLL_MS = 30000;

    function statusBadge(status) {
        var map = { open: 'danger', returned: 'success', resolved: 'success' };
        var cls = map[status] || 'secondary';
        return '<span class="badge-admin badge-admin--' + cls + '">' + (status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown') + '</span>';
    }
    function fmtDist(d) {
        return d !== null && d !== undefined ? Number(d).toFixed(2) + 'm outside' : '&mdash;';
    }
    function fmtLatLng(lat, lng) {
        if (lat === null || lat === undefined || lng === null || lng === undefined) return '<span class="text-muted">&mdash;</span>';
        return '<small>' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5) + '</small>';
    }
    function fmtTime(iso) {
        if (!iso) return '<span class="text-muted">&mdash;</span>';
        var d = new Date(iso);
        return '<small>' + d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }) + '</small>';
    }
    function rowHtml(a) {
        return '<tr>' +
            '<td data-label="Bicycle" class="cell-title"><div class="d-flex align-items-center"><i class="bi bi-bicycle me-2 text-muted"></i>' +
                (a.bicycle || 'Unknown') + '<small class="text-muted ms-1">#' + (a.bicycleId || '') + '</small></div></td>' +
            '<td data-label="Rider">' + (a.rider ? '<small>' + a.rider + '</small>' : '<span class="text-muted">&mdash;</span>') + '</td>' +
            '<td data-label="Location">' + fmtLatLng(a.lat, a.lng) + '</td>' +
            '<td data-label="Distance from Boundary"><span class="fw-semibold text-danger">' + fmtDist(a.distance) + '</span></td>' +
            '<td data-label="Status">' + statusBadge(a.status) + '</td>' +
            '<td data-label="Timestamp">' + fmtTime(a.timestamp) + '</td>' +
            '<td data-label="Acknowledged">' + (a.acknowledged
                ? '<span class="badge-admin badge-admin--success">Acknowledged</span>'
                : '<span class="badge-admin badge-admin--warning">Pending</span>') + '</td>' +
            '<td data-label="Actions">' + (a.acknowledged
                ? '<span class="text-muted"><small>Handled</small></span>'
                : '<button type="button" class="btn-admin btn-admin--soft btn-admin--sm" data-ack="' + a.id + '" title="Acknowledge"><i class="bi bi-check-lg me-1"></i>Acknowledge</button>') + '</td>' +
            '</tr>';
    }
    function setKpi(key, value) {
        var n = document.querySelector('[data-theft-kpi="' + key + '"] .kpi__value');
        if (n) n.textContent = value;
    }
    function renderList(data) {
        setKpi('openBreaches', data.openBreaches);
        setKpi('unacknowledged', data.unacknowledged);
        setKpi('atRisk', data.atRisk);
        var badge = document.getElementById('theftUnreadBadge');
        if (badge) badge.innerHTML = '<span class="badge-admin badge-admin--danger">' + data.unacknowledged + ' unread</span>';
        var tbody = document.getElementById('theftTableBody');
        if (tbody) {
            tbody.innerHTML = data.alerts.length
                ? data.alerts.map(rowHtml).join('')
                : '<tr><td colspan="8" class="text-center"><div class="admin-empty"><i class="bi bi-shield-check"></i><h4>No theft alerts</h4></div></td></tr>';
        }
    }
    function acknowledge(id, done) {
        fetch(ackUrl.replace('__ID__', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
            credentials: 'same-origin'
        }).then(function () { if (done) done(); }).catch(function () { if (done) done(); });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-ack]');
        if (!btn) return;
        var id = btn.getAttribute('data-ack');
        acknowledge(id, function () {
            fetch(liveUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(renderList).catch(function () {});
        });
    });

    window.PedalyaGeoLibre.init({
        container: 'theftMap',
        geofence: geofence,
        bicycles: bicycles,
        alertBicycles: alertBicycles,
        liveUrl: liveUrl,
        pollMs: POLL_MS,
        zoom: 15,
        pitch: 55,
        bearing: -15,
        readout: { radius: 'theftGeofenceRadiusText', alertBadge: 'theftGeofenceAlertBadge' },
        buttons: { center: 'theftCenterMapBtn', refresh: 'theftRefreshMapBtn' },
        legendCounts: true,
        onAlertsChange: renderList
    });

    fetch(liveUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(renderList).catch(function () {});
})();
</script>
@endsection
