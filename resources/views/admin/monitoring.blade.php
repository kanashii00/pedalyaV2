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
    .map-legend .legend-count {
        display: inline-block;
        min-width: 18px;
        text-align: center;
        margin-left: 6px;
        padding: 0 5px;
        border-radius: 9px;
        background: rgba(0,0,0,0.07);
        color: #555;
        font-weight: 700;
        font-size: 0.72rem;
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
            Geofence: <span id="geofenceRadiusText">{{ number_format($geofence['radius'], 0) }}m</span>
            <span id="geofenceAlertBadge">@if($geofence['alertEnabled'])
                <x-admin.badge type="success" label="Alerts ON"/>
            @endif</span>
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
        <div><span class="dot" style="background:#2ecc71;"></span>Inside Zone <span class="legend-count" data-count="safe">0</span></div>
        <div><span class="dot" style="background:#f39c12;"></span>Near Boundary <span class="legend-count" data-count="near">0</span></div>
        <div><span class="dot" style="background:#e74c3c;"></span>Outside Zone <span class="legend-count" data-count="outside">0</span></div>
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
                'warning', 'approaching' => ['bg' => '#f39c12', 'label' => 'Near Boundary'],
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
                        'warning', 'approaching' => ['#f39c12', 'Near Boundary'],
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
<script src="{{ asset('js/geolibre-map.js') }}"></script>
<script>
(function () {
    var geofence = {!! json_encode($geofence) !!};
    var bicycles = {!! $bicycleLocations->toJson() !!};

    window.PedalyaGeoLibre.init({
        container: 'monitoringMap',
        geofence: geofence,
        bicycles: bicycles,
        liveUrl: {!! json_encode(route('admin.monitoring.live')) !!},
        pollMs: 15000,
        zoom: 15,
        pitch: 55,
        bearing: -15,
        readout: { radius: 'geofenceRadiusText', alertBadge: 'geofenceAlertBadge' },
        buttons: { center: 'centerMapBtn', refresh: 'refreshMapBtn', fullscreen: 'fullscreenMapBtn' },
        legendCounts: true,
        bikeCardSelector: '.bike-monitor-card',
        fleetCount: 'fleetCount'
    });
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
