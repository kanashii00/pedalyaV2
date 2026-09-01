@extends('layouts.admin')

@php
    $sectionTitles = [
        'map' => 'GeoLibre 3D Map',
        'gps' => 'Live GPS Tracking',
        'locks' => 'Smart Lock Control',
        'devices' => 'IoT Device Monitoring',
    ];
    $sectionSubs = [
        'map' => 'Live 3D map of the fleet',
        'gps' => 'Real-time position updates',
        'locks' => 'Remote smart lock control',
        'devices' => 'Device health and telemetry',
    ];
    $sectionIcons = ['map' => 'bi-map', 'gps' => 'bi-geo-alt', 'locks' => 'bi-lock', 'devices' => 'bi-cpu'];
@endphp

@section('title', ($sectionTitles[$section] ?? 'Monitoring') . ' — Pedalya Admin')

@section('styles')
<style>
    #monitoringMap {
        width: 100%;
        height: 480px;
        border-radius: 14px;
        overflow: hidden;
    }
    .map-legend {
        position: absolute;
        bottom: 12px;
        left: 12px;
        z-index: 2;
        background: rgba(255,255,255,0.98);
        border-radius: 10px;
        padding: 10px 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        font-size: 0.82rem;
        color: #1a1a1a;
        border: 1px solid rgba(0,0,0,0.08);
    }
    .map-legend div {
        display: flex;
        align-items: center;
        margin-bottom: 4px;
    }
    .map-legend div:last-child { margin-bottom: 0; }
    .map-legend .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        flex-shrink: 0;
        border: 2px solid rgba(255,255,255,0.8);
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .section-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .section-tabs a {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        height: 34px;
        padding: 0 13px;
        border-radius: 9px;
        background: var(--surface);
        border: 1px solid var(--border-subtle);
        color: var(--text-2);
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s;
    }
    .section-tabs a:hover { border-color: var(--border-strong); color: var(--text-1); }
    .section-tabs a.active { background: var(--brand-soft); color: var(--brand-strong); border-color: transparent; }
    .zone-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }
    .zone-pill .dot { width: 7px; height: 7px; border-radius: 50%; }

    /* Bike Monitor Card - works in light & dark mode */
    .bike-monitor-card {
        background: var(--surface);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius);
        padding: 16px;
        box-shadow: var(--shadow-card);
        height: 100%;
        transition: transform 0.18s, box-shadow 0.18s, border-color 0.18s;
    }
    .bike-monitor-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-pop); border-color: var(--border-strong); }
    .bike-monitor-card hr { border-color: var(--border-subtle) !important; }
    .bike-status-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    .bike-status-dot.online { background: var(--success); }
    .bike-status-dot.stale { background: var(--warning); }
    .bike-status-dot.offline { background: var(--text-3); }
</style>
@endsection

@section('page-header')
    <h1>{{ $sectionTitles[$section] }}</h1>
    <p>{{ $sectionSubs[$section] }}</p>
@endsection

@section('actions')
<button class="btn-admin btn-admin--secondary btn-admin--sm" id="autoRefreshBtn" onclick="toggleAutoRefresh()">
    <i class="bi bi-arrow-clockwise me-1"></i><span id="refreshLabel">Auto-Refresh: Off</span>
</button>
@endsection

@section('content')
{{-- Section switcher --}}
<div class="section-tabs">
    @foreach($sectionTitles as $key => $title)
        <a href="{{ $key === 'map' ? route('admin.monitoring.index') : route('admin.monitoring.index') . '?section=' . $key }}"
           class="{{ $section === $key ? 'active' : '' }}">
            <i class="bi {{ $sectionIcons[$key] }}"></i> {{ $title }}
        </a>
    @endforeach
</div>

{{-- Map sections (map / gps) --}}
@if(in_array($section, ['map', 'gps']))
<x-admin.card title="{{ $section === 'gps' ? 'Live GPS Tracking Map' : 'Bicycle Locations' }}" bodyClass="p-0 position-relative mb-4">
    <x-slot:tools>
        <small class="text-muted me-3" id="fleetCount">{{ count($bicycles ?? []) }} bicycle(s)</small>
        <small class="text-muted">
            Geofence: {{ number_format($geofence['radius'], 0) }}m
            @if($geofence['alertEnabled'])
                <x-admin.badge type="success" label="Alerts ON"/>
            @endif
        </small>
        <div class="ms-auto d-flex gap-2">
            <button class="btn-admin btn-admin--ghost btn-admin--sm" id="centerMapBtn" title="Center on geofence">
                <i class="bi bi-crosshair"></i>
            </button>
            <button class="btn-admin btn-admin--ghost btn-admin--sm" id="refreshMapBtn" title="Refresh positions">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button class="btn-admin btn-admin--ghost btn-admin--sm" id="fullscreenMapBtn" title="Fullscreen map">
                <i class="bi bi-fullscreen"></i>
            </button>
        </div>
    </x-slot:tools>
    <div id="monitoringMap"></div>
    <div class="map-legend">
        <div><span class="dot" style="background:#2ecc71;"></span>Inside zone</div>
        <div><span class="dot" style="background:#f1c40f;"></span>Near boundary</div>
        <div><span class="dot" style="background:#e74c3c;"></span>Outside zone</div>
        <div><span class="dot" style="background:#27ae60;"></span>Geofence Boundary</div>
    </div>
</x-admin.card>
@endif

@if($section === 'map')
{{-- Bicycle Cards Grid --}}
<div class="row align-items-center mb-3">
    <div class="col">
        <h5 class="mb-0">Fleet Status</h5>
    </div>
    <div class="col-auto">
        <small class="text-muted">Last updated: {{ now()->format('h:i:s A') }}</small>
    </div>
</div>

<div class="row g-3">
    @forelse($bicycles as $bike)
        @php
            $zoneLevel = $bike->zone['level'] ?? 'unknown';
            $zonePill = match ($zoneLevel) {
                'breach' => ['bg' => '#e74c3c', 'label' => 'Outside Zone'],
                'warning', 'approaching' => ['bg' => '#f1c40f', 'label' => 'Near Boundary'],
                'safe' => ['bg' => '#2ecc71', 'label' => 'Inside Zone'],
                default => ['bg' => '#95a5a6', 'label' => 'No GPS'],
            };
        @endphp
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="bike-monitor-card" data-bike-id="{{ $bike->id }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <span class="bike-status-dot {{ ($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 5 ? 'online' : (($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 30 ? 'stale' : 'offline') }}"></span>
                        <span class="fw-semibold ms-2">{{ $bike->name }}</span>
                    </div>
                    <span class="zone-pill" style="background: {{ $zonePill['bg'] }}22; color: {{ $zonePill['bg'] }};">
                        <span class="dot" style="background: {{ $zonePill['bg'] }};"></span>{{ $zonePill['label'] }}
                    </span>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <small class="text-muted d-block">Battery</small>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:5px;">
                                <div class="progress-bar bg-{{ $bike->batteryLevel <= 20 ? 'danger' : ($bike->batteryLevel <= 50 ? 'warning' : 'success') }}"
                                     style="width:{{ $bike->batteryLevel }}%"></div>
                            </div>
                            <small class="fw-semibold">{{ $bike->batteryLevel }}%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Lock</small>
                        @if($bike->lockStatus === 'locked')
                            <span class="text-danger fw-semibold"><i class="bi bi-lock-fill me-1"></i>Locked</span>
                        @else
                            <span class="text-success fw-semibold"><i class="bi bi-unlock me-1"></i>Unlocked</span>
                        @endif
                    </div>
                </div>

                <hr class="my-2" style="border-color:#f0f0f0;">

                <div class="row g-1">
                    <div class="col-6">
                        <small class="text-muted d-block">Last Heartbeat</small>
                        <small class="fw-semibold">{{ $bike->lastHeartbeat?->diffForHumans() ?? 'Never' }}</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">GPS</small>
                        <small class="fw-semibold">
                            @if($bike->currentLat && $bike->currentLng)
                                {{ number_format($bike->currentLat, 4) }}, {{ number_format($bike->currentLng, 4) }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <x-admin.empty-state icon="bi-bicycle" title="No bicycles to monitor"/>
        </div>
    @endforelse
</div>
@endif

@if($section === 'gps')
{{-- Live GPS Tracking table --}}
<div class="row align-items-center mb-3">
    <div class="col"><h5 class="mb-0">Live Position Feed</h5></div>
    <div class="col-auto">
        <small class="text-muted">Center: {{ number_format($geofence['centerLat'], 5) }}, {{ number_format($geofence['centerLng'], 5) }} · Radius {{ number_format($geofence['radius'], 0) }}m</small>
    </div>
</div>
<div class="admin-table-wrap">
    <table class="admin-table" id="gpsFeedTable">
        <thead>
            <tr>
                <th>Bicycle</th>
                <th>Zone</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Speed</th>
                <th>Distance from center</th>
                <th>Last GPS</th>
                <th>Last heartbeat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bicycles as $bike)
                @php
                    $zoneLevel = $bike->zone['level'] ?? 'unknown';
                    $zoneStyle = match ($zoneLevel) {
                        'breach' => ['#e74c3c', 'Outside Zone'],
                        'warning', 'approaching' => ['#f1c40f', 'Near Boundary'],
                        'safe' => ['#2ecc71', 'Inside Zone'],
                        default => ['#95a5a6', 'No GPS'],
                    };
                    $speed = $bike->latestGpsLog?->speed;
                @endphp
                <tr>
                    <td data-label="Bicycle">
                        <div class="d-flex align-items-center gap-2">
                            <span class="bike-status-dot {{ ($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 5 ? 'online' : 'offline' }}"></span>
                            <span class="fw-semibold">{{ $bike->name }}</span>
                        </div>
                    </td>
                    <td data-label="Zone">
                        <span class="zone-pill" style="background: {{ $zoneStyle[0] }}22; color: {{ $zoneStyle[0] }};">
                            <span class="dot" style="background: {{ $zoneStyle[0] }};"></span>{{ $zoneStyle[1] }}
                        </span>
                    </td>
                    <td data-label="Latitude">{{ $bike->currentLat ? number_format((float) $bike->currentLat, 6) : '—' }}</td>
                    <td data-label="Longitude">{{ $bike->currentLng ? number_format((float) $bike->currentLng, 6) : '—' }}</td>
                    <td data-label="Speed">{{ $speed !== null ? number_format((float) $speed, 1) . ' km/h' : '—' }}</td>
                    <td data-label="Distance from center">{{ $bike->zone['distance'] !== null ? number_format($bike->zone['distance'], 0) . ' m' : '—' }}</td>
                    <td data-label="Last GPS">{{ $bike->lastGpsUpdate?->diffForHumans() ?? 'Never' }}</td>
                    <td data-label="Last heartbeat">{{ $bike->lastHeartbeat?->diffForHumans() ?? 'Never' }}</td>
                </tr>
            @empty
                <tr><td colspan="8"><x-admin.empty-state icon="bi-bicycle" title="No bicycles to monitor"/></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

@if($section === 'locks')
{{-- Smart Lock Control --}}
<div class="row align-items-center mb-3">
    <div class="col"><h5 class="mb-0">Smart Lock Control</h5></div>
    <div class="col-auto">
        <small class="text-muted">Send lock / unlock commands to the ESP32 via WebSocket</small>
    </div>
</div>
<div class="row g-3">
    @forelse($bicycles as $bike)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="bike-monitor-card" data-bike-id="{{ $bike->id }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <span class="bike-status-dot {{ ($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 5 ? 'online' : 'offline' }}"></span>
                        <span class="fw-semibold ms-2">{{ $bike->name }}</span>
                    </div>
                    @if($bike->lockStatus === 'locked')
                        <x-admin.badge type="danger" label="Locked"/>
                    @else
                        <x-admin.badge type="success" label="Unlocked"/>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2 mb-2" style="font-size:13px;">
                    <i class="bi {{ $bike->lockStatus === 'locked' ? 'bi-lock-fill text-danger' : 'bi-unlock text-success' }}"></i>
                    <span class="text-muted">Last action:</span>
                    <span class="fw-semibold">{{ $bike->lastLockAction?->diffForHumans() ?? 'Never' }}</span>
                </div>

                <div class="row g-1 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Battery</small>
                        <small class="fw-semibold">{{ $bike->batteryLevel }}%</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">GPS</small>
                        <small class="fw-semibold">{{ $bike->currentLat && $bike->currentLng ? 'Valid' : 'N/A' }}</small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.bicycles.lock', $bike->id) }}" class="flex-grow-1">
                        @csrf
                        <input type="hidden" name="action" value="lock">
                        <button type="submit" class="btn-admin btn-admin--danger btn-admin--block {{ $bike->lockStatus === 'locked' ? 'disabled' : '' }}" {{ $bike->lockStatus === 'locked' ? 'disabled' : '' }}>
                            <i class="bi bi-lock-fill"></i> Lock
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.bicycles.lock', $bike->id) }}" class="flex-grow-1">
                        @csrf
                        <input type="hidden" name="action" value="unlock">
                        <button type="submit" class="btn-admin btn-admin--soft btn-admin--block {{ $bike->lockStatus !== 'locked' ? 'disabled' : '' }}" {{ $bike->lockStatus !== 'locked' ? 'disabled' : '' }}>
                            <i class="bi bi-unlock"></i> Unlock
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <x-admin.empty-state icon="bi-lock" title="No bicycles to control"/>
        </div>
    @endforelse
</div>
@endif

@if($section === 'devices')
{{-- IoT Device Monitoring --}}
<div class="row align-items-center mb-3">
    <div class="col"><h5 class="mb-0">IoT Device Monitoring</h5></div>
    <div class="col-auto">
        <small class="text-muted">Firmware, telemetry and device health</small>
    </div>
</div>
<div class="row g-3">
    @forelse($bicycles as $bike)
        @php
            $tel = $bike->latestTelemetry;
            $battery = is_array($tel?->battery) ? $tel->battery : null;
            $online = ($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 5;
        @endphp
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="bike-monitor-card" data-bike-id="{{ $bike->id }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <span class="bike-status-dot {{ $online ? 'online' : 'offline' }}"></span>
                        <span class="fw-semibold ms-2">{{ $bike->name }}</span>
                    </div>
                    <span class="text-muted small"><i class="bi bi-cpu"></i> {{ $tel?->deviceVersion ?? '—' }}</span>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <small class="text-muted d-block">Battery</small>
                        <small class="fw-semibold">{{ $battery['pct'] ?? $bike->batteryLevel }}%{{ isset($battery['charging']) && $battery['charging'] ? ' ⚡' : '' }}</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Uptime</small>
                        <small class="fw-semibold">{{ $tel?->uptime ? gmdate('H:i:s', $tel->uptime) : '—' }}</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Lock</small>
                        @if($bike->lockStatus === 'locked')
                            <small class="fw-semibold text-danger"><i class="bi bi-lock-fill me-1"></i>Locked</small>
                        @else
                            <small class="fw-semibold text-success"><i class="bi bi-unlock me-1"></i>Unlocked</small>
                        @endif
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">RFID</small>
                        <small class="fw-semibold">{{ $tel?->rfid ? \Illuminate\Support\Str::limit($tel->rfid, 10) : '—' }}</small>
                    </div>
                </div>

                <hr class="my-2" style="border-color:#f0f0f0;">

                <div class="row g-1">
                    <div class="col-6">
                        <small class="text-muted d-block">Last Heartbeat</small>
                        <small class="fw-semibold">{{ $bike->lastHeartbeat?->diffForHumans() ?? 'Never' }}</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Last Telemetry</small>
                        <small class="fw-semibold">{{ $tel?->eventTimestamp?->diffForHumans() ?? 'Never' }}</small>
                    </div>
                </div>

                @if($tel?->command)
                    <div class="mt-2 p-2 rounded" style="background:#f6f7fb; font-size:11.5px;">
                        <strong>Last command:</strong> {{ $tel->command }}
                        <span class="text-muted"> → {{ $tel->result ?? ($tel->status ?? 'sent') }}</span>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <x-admin.empty-state icon="bi-cpu" title="No devices to monitor"/>
        </div>
    @endforelse
</div>
@endif
@endsection

@section('scripts')
@if(in_array($section, ['map', 'gps']))
@php
    $bicycleLocations = collect($bicycles)->map(fn($b) => [
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
    ])->values();
@endphp
<script>
(function () {
    var geofence = {!! json_encode($geofence) !!};
    var bicycles = {!! $bicycleLocations->toJson() !!};

    var el = document.getElementById('monitoringMap');
    if (!el || typeof maplibregl === 'undefined') return;

    var map = new maplibregl.Map({
        container: el,
        style: 'https://tiles.openfreemap.org/styles/liberty',
        center: [geofence.centerLng, geofence.centerLat],
        zoom: 15,
        pitch: 55,
        bearing: -15,
        attributionControl: true
    });

    map.addControl(new maplibregl.NavigationControl(), 'top-right');
    map.addControl(new maplibregl.FullscreenControl(), 'top-right');

    var markers = {};

    var zoneColor = {
        safe: '#2ecc71',
        approaching: '#f1c40f',
        warning: '#f39c12',
        breach: '#e74c3c',
        unknown: '#95a5a6'
    };

    function circlePolygon(lng, lat, radiusMeters, segments) {
        segments = segments || 96;
        var coords = [];
        var earth = 6371000;
        var latRad = lat * Math.PI / 180;
        var lngScale = earth * Math.cos(latRad);
        var latScale = earth;
        for (var i = 0; i <= segments; i++) {
            var rad = (i / segments) * 2 * Math.PI;
            var dLng = (Math.sin(rad) * radiusMeters) / lngScale;
            var dLat = (Math.cos(rad) * radiusMeters) / latScale;
            coords.push([lng + dLng * (180 / Math.PI), lat + dLat * (180 / Math.PI)]);
        }
        coords.push(coords[0]);
        return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [coords] } };
    }

    // Local metre helpers to build the selected geofence shape.
    function metersToLatLngShape(start, x, y) {
        var latRad = start.lat * Math.PI / 180;
        return {
            lng: start.lng + (x / (111320 * Math.cos(latRad))),
            lat: start.lat + (y / 111320)
        };
    }
    function shapeVertices(gf) {
        var start = { lat: gf.centerLat, lng: gf.centerLng };
        var type = gf.shapeType || 'circle';
        var radius = gf.radius || 500;
        var width = gf.width || radius || 500;
        var height = gf.height || radius || 500;
        if (type === 'rectangle') {
            var a = width / 2, b = height / 2;
            var th = (gf.rotation || 0) * Math.PI / 180, cos = Math.cos(th), sin = Math.sin(th);
            var corners = [[a, b], [-a, b], [-a, -b], [a, -b]];
            return corners.map(function (c) {
                var x = c[0] * cos - c[1] * sin;
                var y = c[0] * sin + c[1] * cos;
                var p = metersToLatLngShape(start, x, y);
                return [p.lng, p.lat];
            });
        }
        if (type === 'oval_h' || type === 'oval_v') {
            var a2 = Math.max(1, width / 2), b2 = Math.max(1, height / 2);
            var el = [];
            for (var i = 0; i < 96; i++) {
                var rad = (i / 96) * 2 * Math.PI;
                var p = metersToLatLngShape(start, Math.cos(rad) * a2, Math.sin(rad) * b2);
                el.push([p.lng, p.lat]);
            }
            return el;
        }
        if (type === 'polygon' && gf.points && gf.points.length >= 3) {
            return gf.points.map(function (p) { return [p.lng, p.lat]; });
        }
        // Circle fallback
        var coords = [];
        for (var i2 = 0; i2 < 96; i2++) {
            var r2 = (i2 / 96) * 2 * Math.PI;
            var p2 = metersToLatLngShape(start, Math.cos(r2) * radius, Math.sin(r2) * radius);
            coords.push([p2.lng, p2.lat]);
        }
        return coords;
    }
    function shapeFeature(gf) {
        var verts = shapeVertices(gf);
        if (verts.length) verts.push(verts[0]);
        return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [verts] } };
    }

    function addGeofence() {
        map.addSource('geofence', {
            type: 'geojson',
            data: shapeFeature(geofence)
        });

        map.addLayer({
            id: 'geofence-fill',
            type: 'fill',
            source: 'geofence',
            paint: {
                'fill-color': '#27ae60',
                'fill-opacity': 0.12
            }
        });

        map.addLayer({
            id: 'geofence-outline',
            type: 'line',
            source: 'geofence',
            paint: {
                'line-color': '#1e8449',
                'line-width': 3,
                'line-dasharray': [0, 2, 2, 2],
                'line-opacity': 0.9
            }
        });
    }

    function markerColor(bike) {
        return zoneColor[bike.zone] || zoneColor[bike.status] || '#95a5a6';
    }

    function addMarker(bike) {
        if (!bike.lat || !bike.lng) return;

        var color = markerColor(bike);
        var dist = bike.distance !== null && bike.distance !== undefined
            ? '<br><small>Distance: ' + Math.round(bike.distance) + ' m</small>' : '';
        var marker = new maplibregl.Marker({ color: color, pitchAlignment: 'auto', rotationAlignment: 'auto' })
            .setLngLat([bike.lng, bike.lat])
            .setPopup(new maplibregl.Popup({ offset: 30 }).setHTML(
                '<strong>' + bike.name + '</strong><br>' +
                '<small>Status: ' + bike.status + '</small><br>' +
                '<small>Battery: ' + bike.battery + '%</small><br>' +
                '<small>Lock: ' + (bike.locked ? 'Locked' : 'Unlocked') + '</small>' + dist +
                '<br><small>Last heartbeat: ' + (bike.heartbeat ? new Date(bike.heartbeat).toLocaleString() : 'Never') + '</small>'
            ))
            .addTo(map);

        markers[bike.id] = { marker: marker, bike: bike };
    }

    function updateMarker(bike) {
        if (!bike.lat || !bike.lng) {
            if (markers[bike.id]) {
                markers[bike.id].marker.remove();
                delete markers[bike.id];
            }
            return;
        }
        var isBreach = bike.zone === 'breach' || bike.zone === 'outside';
        var color = isBreach ? '#e74c3c' : markerColor(bike);
        if (markers[bike.id]) {
            markers[bike.id].marker.setLngLat([bike.lng, bike.lat]);
            markers[bike.id].marker.setColor(color);
            // Toggle breach flash class
            var markerEl = markers[bike.id].marker.getElement();
            if (markerEl) {
                if (isBreach) markerEl.classList.add('marker-breach');
                else markerEl.classList.remove('marker-breach');
            }
            markers[bike.id].bike = bike;
        } else {
            addMarker(bike);
            if (isBreach) {
                var markerEl = markers[bike.id].marker.getElement();
                if (markerEl) markerEl.classList.add('marker-breach');
            }
        }
    }

    map.on('load', function () {
        addGeofence();
        bicycles.forEach(addMarker);

        // Map control buttons
        var centerBtn = document.getElementById('centerMapBtn');
        if (centerBtn) {
            centerBtn.addEventListener('click', function () {
                map.flyTo({
                    center: [geofence.centerLng, geofence.centerLat],
                    zoom: 15,
                    pitch: 55,
                    bearing: -15,
                    duration: 1000
                });
            });
        }

        var refreshBtn = document.getElementById('refreshMapBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function () {
                var url = {!! json_encode(route('admin.monitoring.live')) !!};
                fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        (data.bicycles || []).forEach(function (bike) {
                            updateMarker({
                                id: bike.id,
                                name: bike.name,
                                lat: bike.current_lat !== undefined ? parseFloat(bike.current_lat) : bike.currentLat,
                                lng: bike.current_lng !== undefined ? parseFloat(bike.current_lng) : bike.currentLng,
                                status: bike.status,
                                battery: bike.battery_level !== undefined ? bike.battery_level : bike.batteryLevel,
                                locked: bike.lock_status === 'locked' || bike.lockStatus === 'locked',
                                heartbeat: bike.last_heartbeat || bike.lastHeartbeat,
                                zone: bike.zone_level || (bike.zone ? bike.zone.level : null) || 'unknown',
                                distance: bike.zone_distance || (bike.zone ? bike.zone.distance : null) || null
                            });
                        });
                    });
            });
        }

        var fsBtn = document.getElementById('fullscreenMapBtn');
        if (fsBtn) {
            fsBtn.addEventListener('click', function () {
                var mapContainer = document.getElementById('monitoringMap');
                if (mapContainer.requestFullscreen) {
                    mapContainer.requestFullscreen();
                } else if (mapContainer.webkitRequestFullscreen) {
                    mapContainer.webkitRequestFullscreen();
                } else if (mapContainer.msRequestFullscreen) {
                    mapContainer.msRequestFullscreen();
                }
            });
        }

        // WebSocket live updates via Laravel Echo + Reverb (fallback to polling)
        if (window.Pedalya && window.Pedalya.broadcastEnabled && window.Echo) {
            window.Echo.private('geofence-alerts').listen('GeofenceAlert', function (e) {
                if (e && e.bicycle) updateMarker(e.bicycle);
            });

            bicycles.forEach(function (bike) {
                window.Echo.private('gps.' + bike.id).listen('GpsUpdate', function (e) {
                    if (e && e.bicycle) updateMarker(e.bicycle);
                });
            });
        } else {
            startPolling();
        }
    });

    var pollingTimer = null;
    function startPolling() {
        if (pollingTimer) return;
        pollingTimer = setInterval(function () {
            var url = {!! json_encode(route('admin.monitoring.live')) !!};
            fetch(url, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(function (r) { return r.json(); })
              .then(function (data) {
                  (data.bicycles || []).forEach(function (bike) {
                      updateMarker({
                          id: bike.id,
                          name: bike.name,
                          lat: bike.current_lat !== undefined ? parseFloat(bike.current_lat) : bike.currentLat,
                          lng: bike.current_lng !== undefined ? parseFloat(bike.current_lng) : bike.currentLng,
                          status: bike.status,
                          battery: bike.battery_level !== undefined ? bike.battery_level : bike.batteryLevel,
                          locked: bike.lock_status === 'locked' || bike.lockStatus === 'locked',
                          heartbeat: bike.last_heartbeat || bike.lastHeartbeat,
                          zone: bike.zone_level || (bike.zone ? bike.zone.level : null) || 'unknown',
                          distance: bike.zone_distance || (bike.zone ? bike.zone.distance : null) || null
                      });
                  });
              }).catch(function () {});
        }, 15000);
    }
})();
</script>
@endif
<script>
function toggleAutoRefresh() {
    var btn = document.getElementById('autoRefreshBtn');
    var label = document.getElementById('refreshLabel');
    var active = window._autoRefreshInterval;

    if (active) {
        clearInterval(active);
        window._autoRefreshInterval = null;
        label.textContent = 'Auto-Refresh: Off';
        btn.classList.remove('btn-admin--primary');
        btn.classList.add('btn-admin--secondary');
    } else {
        window._autoRefreshInterval = setInterval(function () {
            window.location.reload();
        }, 30000);
        label.textContent = 'Auto-Refresh: 30s';
        btn.classList.remove('btn-admin--secondary');
        btn.classList.add('btn-admin--primary');
    }
}
</script>
@endsection
