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
    <div class="col-md-4">
        <x-admin.kpi title="Open Breaches" value="{{ $openBreachCount }}"
                     icon="bi-shield-exclamation" color="var(--danger)" />
    </div>
    <div class="col-md-4">
        <x-admin.kpi title="Unacknowledged Alerts" value="{{ $alerts->where('acknowledged', false)->count() }}"
                     icon="bi-bell" color="var(--warning)" />
    </div>
    <div class="col-md-4">
        <x-admin.kpi title="At-Risk Bicycles" value="{{ $bicycles->count() }}"
                     icon="bi-bicycle" color="var(--brand)" />
    </div>
</div>

{{-- Map of Breach Locations --}}
<x-admin.card title="Breach Locations" :flush="true">
    <div id="theftMap"></div>
</x-admin.card>

{{-- Alerts Table --}}
<x-admin.card title="Theft Alert Log" :flush="true">
    <x-slot name="tools">
        <x-admin.badge type="danger" :label="$alerts->where('acknowledged', false)->count() . ' unread'" />
    </x-slot>
    <div class="admin-table-wrap">
        <div class="admin-table-toolbar">
            <div class="grow"><i class="bi bi-search"></i><input type="text" data-table-search placeholder="Search alerts..."></div>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="sortable">Bicycle <span class="sort-ind"></span></th>
                    <th class="sortable">Location <span class="sort-ind"></span></th>
                    <th class="sortable">Distance from Boundary <span class="sort-ind"></span></th>
                    <th class="sortable">Timestamp <span class="sort-ind"></span></th>
                    <th class="sortable">Acknowledged <span class="sort-ind"></span></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
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
                            </div>
                        </td>
                        <td data-label="Location">
                            @if($lat && $lng)
                                <small>{{ number_format($lat, 5) }}, {{ number_format($lng, 5) }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Distance from Boundary">
                            @if($alert->breachDistance !== null)
                                <span class="fw-semibold text-danger">
                                    {{ number_format($alert->breachDistance, 2) }}m outside
                                </span>
                            @else
                                <span class="text-muted">—</span>
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
                        <td colspan="6" class="text-center">
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
@php
    $breachPoints = ($alerts ?? collect())->filter(function ($a) {
        $loc = is_array($a->gpsLocation) ? $a->gpsLocation : [];
        return isset($loc['lat']) && isset($loc['lng']);
    })->map(function ($a) {
        $loc = is_array($a->gpsLocation) ? $a->gpsLocation : [];
        return [
            'id' => $a->id,
            'bicycle' => $a->bicycle->name ?? 'Unknown',
            'lat' => (float) $loc['lat'],
            'lng' => (float) $loc['lng'],
            'distance' => $a->breachDistance,
            'time' => $a->created_at->toIso8601String(),
            'acknowledged' => (bool) $a->acknowledged,
        ];
    })->values();
@endphp
<script>
(function () {
    var points = {!! $breachPoints->toJson() !!};
    var el = document.getElementById('theftMap');
    if (!el || typeof maplibregl === 'undefined') return;

    var centerLat = 7.0990;
    var centerLng = 125.6470;
    if (points.length > 0) {
        centerLat = points.reduce(function (s, p) { return s + p.lat; }, 0) / points.length;
        centerLng = points.reduce(function (s, p) { return s + p.lng; }, 0) / points.length;
    }

    var map = new maplibregl.Map({
        container: el,
        style: 'https://tiles.openfreemap.org/styles/liberty',
        center: [centerLng, centerLat],
        zoom: 13,
        attributionControl: true
    });

    map.addControl(new maplibregl.NavigationControl(), 'top-right');
    map.addControl(new maplibregl.FullscreenControl(), 'top-right');

    points.forEach(function (loc) {
        var color = loc.acknowledged ? '#2ecc71' : '#e74c3c';
        var m = new maplibregl.Marker({ color: color })
            .setLngLat([loc.lng, loc.lat])
            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(
                '<strong>' + loc.bicycle + '</strong><br>' +
                '<small>' + (loc.distance ? loc.distance.toFixed(2) + 'm outside' : 'N/A') + '</small><br>' +
                '<small>' + new Date(loc.time).toLocaleString() + '</small><br>' +
                '<small>' + (loc.acknowledged ? 'Acknowledged' : 'Pending') + '</small>'
            ))
            .addTo(map);
    });
})();
</script>
@endsection