@extends('layouts.admin')

@section('title', 'Geofence Management')

@section('styles')
<style>
    #geofenceMap {
        width: 100%;
        height: 560px;
        border-radius: 14px;
        overflow: hidden;
    }
    .radius-slider {
        width: 100%;
        accent-color: var(--primary, #2563eb);
    }
    .breach-item {
        border-left: 3px solid var(--gray-300);
        padding: 10px 12px;
        border-radius: 8px;
        background: var(--gray-100);
        margin-bottom: 10px;
    }
    .breach-item.open {
        border-left-color: var(--danger);
    }
    .breach-item.resolved {
        border-left-color: var(--success);
    }
    .geofence-legend {
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
    .geofence-legend div {
        display: flex;
        align-items: center;
        margin-bottom: 4px;
    }
    .geofence-legend div:last-child { margin-bottom: 0; }
    .geofence-legend .line {
        height: 12px;
        width: 24px;
        display: inline-block;
        vertical-align: middle;
        margin-right: 8px;
        border-radius: 3px;
        border: 2px solid rgba(255,255,255,0.8);
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .zone-badge {
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
    }
    .zone-badge.safe { background:#d4edda; color:#155724; }
    .zone-badge.approaching { background:#fff3cd; color:#856404; }
    .zone-badge.warning { background:#ffe0b2; color:#e65100; }
    .zone-badge.breach { background:#f8d7da; color:#721c24; }
    .zone-badge.no-gps { background:#e2e3e5; color:#383d41; }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        transition: box-shadow .15s;
    }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .stat-card .value { font-size: 2rem; font-weight: 700; line-height: 1.2; }
    .stat-card .label { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
    .stat-card.inside .value { color: #27ae60; }
    .stat-card.near .value { color: #f39c12; }
    .stat-card.outside .value { color: #e74c3c; }
    .stat-card.no-gps .value { color: var(--muted); }
    .distance-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
    .distance-table th, .distance-table td { padding: 8px 10px; text-align: left; border-bottom: 1px solid var(--border); }
    .distance-table th { font-weight: 600; color: var(--muted); font-size: 0.7rem; text-transform: uppercase; }
    .distance-table tr:hover td { background: var(--gray-50); }
    .incident-item, .lock-item {
        padding: 10px 12px;
        border-bottom: 1px solid var(--border);
        font-size: 0.8rem;
    }
    .incident-item:last-child, .lock-item:last-child { border-bottom: none; }
    .incident-item .meta, .lock-item .meta { color: var(--muted); font-size: 0.7rem; }
    .lock-item .command { font-weight: 600; text-transform: uppercase; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; }
    .lock-item .lock { background:#f8d7da; color:#721c24; }
    .lock-item .unlock { background:#d4edda; color:#155724; }
</style>
@endsection

@section('page-header')
    <h1>Geofence Management</h1>
    <p>Configure the circular riding boundary — click the map or drag the marker to reposition</p>
@endsection

@section('actions')
    <span id="saveStatus" class="badge-admin badge-admin--success me-2" style="display:none;"></span>
    <button class="btn-admin btn-admin--primary" id="saveGeofenceBtn">
        <i class="bi bi-save me-1"></i>Save Geofence Location
    </button>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-pedalya alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Live Zone Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card inside">
            <div class="value">{{ $stats['inside'] }}</div>
            <div class="label">Inside Boundary</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card near">
            <div class="value">{{ $stats['near'] }}</div>
            <div class="label">Near Boundary</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card outside">
            <div class="value">{{ $stats['outside'] }}</div>
            <div class="label">Outside Boundary</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card no-gps">
            <div class="value">{{ $stats['noGps'] }}</div>
            <div class="label">No GPS Signal</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Map --}}
    <div class="col-lg-7">
        <x-admin.card title="Riding Boundary" :flush="true">
            <x-slot name="tools"><small class="text-muted" id="centerReadout"></small></x-slot>
            <div class="position-relative">
                <div id="geofenceMap"></div>
                <div class="geofence-legend">
                    <div><span class="line" style="background:#27ae60;"></span>Geofence Boundary</div>
                    <div><span class="line" style="background:#f39c12;"></span>Warning Zone</div>
                    <div><span class="line" style="background:#e74c3c;"></span>Breach Zone</div>
                    <div><span class="line" style="background:#2c3e50;"></span>Center (drag or click map to move)</div>
                </div>
            </div>
        </x-admin.card>

        {{-- Live Bicycle Distances --}}
        <x-admin.card title="Live Bicycle Zone Status" class="mt-4">
            <div class="table-responsive">
                <table class="distance-table">
                    <thead>
                        <tr>
                            <th>Bicycle</th>
                            <th>Rider</th>
                            <th>Zone</th>
                            <th>Distance from Center</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bicycles as $bike)
                        <tr>
                            <td>
                                <strong>{{ $bike->name ?? 'Bicycle #' . $bike->id }}</strong>
                                @if($bike->status)
                                <span class="badge-admin badge-admin--{{ $bike->status === 'rented' ? 'primary' : ($bike->status === 'maintenance' ? 'warning' : 'secondary') }} ms-1" style="font-size:0.65rem;">{{ ucfirst($bike->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($bike->currentRiderUser)
                                    {{ $bike->currentRiderUser->name }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($bike->zone))
                                    <span class="zone-badge {{ $bike->zone['level'] ?? 'no-gps' }}">
                                        {{ ucfirst($bike->zone['level'] ?? 'No GPS') }}
                                    </span>
                                @else
                                    <span class="zone-badge no-gps">No GPS</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($bike->zone['distance']) && $bike->zone['distance'] !== null)
                                    {{ number_format($bike->zone['distance'], 1) }} m
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($bike->currentLat && $bike->currentLng)
                                    <span class="badge-admin badge-admin--success">Active</span>
                                @else
                                    <span class="badge-admin badge-admin--secondary">No Signal</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </div>

    {{-- Controls --}}
    <div class="col-lg-5">
        <x-admin.card title="Boundary Controls">
            <div class="admin-form">
                <div class="mb-4">
                    <label class="form-label d-flex justify-content-between">
                        <span>Radius</span>
                        <strong id="radiusValue">{{ number_format($config['radius'], 0) }} m</strong>
                    </label>
                    <input type="range" class="radius-slider" id="radiusSlider" min="50" max="3000" step="10"
                           value="{{ $config['radius'] }}">
                    <div class="d-flex justify-content-between text-muted mt-1" style="font-size:0.75rem;">
                        <span>50 m</span><span>1500 m</span><span>3000 m</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label d-flex justify-content-between">
                        <span>Warning Threshold (inside boundary)</span>
                        <strong id="thresholdValue">{{ number_format($warningThreshold, 0) }} m</strong>
                    </label>
                    <input type="range" class="radius-slider" id="thresholdSlider" min="10" max="500" step="5"
                           value="{{ $warningThreshold }}">
                    <div class="form-text">Bicycles within this distance of the boundary are flagged as approaching.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="searchLocationInput">
                        <i class="bi bi-search me-1"></i>Search Location
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="searchLocationInput"
                               placeholder="Search address or place name...">
                        <button class="btn btn-outline-secondary" type="button" id="searchLocationBtn" title="Search">
                            <i class="bi bi-search"></i>
                        </button>
                        <button class="btn btn-outline-secondary d-none" type="button" id="clearSearchBtn" title="Clear search">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="form-text" id="searchStatus"></div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Center Coordinates</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Lat</span>
                        <input type="text" class="form-control" id="centerLatInput"
                               value="{{ number_format($config['centerLat'], 6) }}" readonly>
                    </div>
                    <div class="input-group input-group-sm mt-2">
                        <span class="input-group-text">Lng</span>
                        <input type="text" class="form-control" id="centerLngInput"
                               value="{{ number_format($config['centerLng'], 6) }}" readonly>
                    </div>
                    <div class="form-text">Updated automatically when you click the map or drag the marker.</div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="alertToggle"
                           {{ $config['alertEnabled'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="alertToggle">Geofence alerts enabled (breach notifications & theft detection)</label>
                </div>
            </div>
        </x-admin.card>

        {{-- Theft / Breach Incidents --}}
        <x-admin.card title="Theft & Breach Incidents" class="mt-4">
            <div style="max-height:280px;overflow-y:auto;">
                @forelse($theftIncidents as $incident)
                    <div class="incident-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong style="font-size:0.85rem;">
                                {{ $incident->bicycle->name ?? 'Bicycle #' . $incident->bicycleId }}
                            </strong>
                            <span class="badge-admin badge-admin--{{ in_array($incident->type, ['theft']) ? 'danger' : 'warning' }}" style="font-size:0.65rem;">
                                {{ ucfirst(str_replace('_', ' ', $incident->type)) }}
                            </span>
                        </div>
                        <small class="text-muted d-block mt-1">
                            {{ $incident->distanceFromBoundary ? number_format($incident->distanceFromBoundary, 1) . 'm from boundary' : ($incident->breachDistance ? number_format($incident->breachDistance, 1) . 'm outside' : '') }}
                            @if($incident->location && isset($incident->location['lat']))
                                — {{ number_format($incident->location['lat'], 5) }}, {{ number_format($incident->location['lng'] ?? $incident->location['lon'] ?? 0, 5) }}
                            @endif
                        </small>
                        <small class="text-muted d-block">{{ $incident->created_at->format('M d, Y g:i A') }}</small>
                    </div>
                @empty
                    <p class="text-muted text-center mb-0 py-3">
                        <i class="bi bi-shield-check" style="font-size:28px;"></i><br>
                        No theft or breach incidents recorded.
                    </p>
                @endforelse
            </div>
        </x-admin.card>

        {{-- Smart Lock Activation History --}}
        <x-admin.card title="Smart Lock Activation History" class="mt-4">
            <div style="max-height:280px;overflow-y:auto;">
                @forelse($lockHistory as $cmd)
                    <div class="lock-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong style="font-size:0.85rem;">{{ $cmd->bicycle->name ?? 'Bicycle #' . $cmd->bicycleId }}</strong>
                                <span class="command {{ $cmd->command }} ms-2">{{ $cmd->command }}</span>
                            </div>
                            <span class="text-muted" style="font-size:0.7rem;">{{ $cmd->created_at->format('M d, g:i A') }}</span>
                        </div>
                        <div class="meta mt-1">
                            @if($cmd->issuer)
                                Issued by: {{ $cmd->issuer->name }}
                            @else
                                System / Auto
                            @endif
                            @if($cmd->status)
                                • Status: {{ ucfirst($cmd->status) }}
                            @endif
                            @if($cmd->executedAt)
                                • Executed: {{ $cmd->executedAt->format('M d, g:i A') }}
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center mb-0 py-3">
                        <i class="bi bi-lock" style="font-size:28px;"></i><br>
                        No lock/unlock commands recorded.
                    </p>
                @endforelse
            </div>
        </x-admin.card>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var config = {!! json_encode($config) !!};
    var initialThreshold = {!! json_encode((float) $warningThreshold) !!};

    var el = document.getElementById('geofenceMap');
    if (!el || typeof maplibregl === 'undefined') return;

    var center = [config.centerLng, config.centerLat];
    var radius = config.radius;
    var warningThreshold = initialThreshold;

    var map = new maplibregl.Map({
        container: el,
        style: 'https://tiles.openfreemap.org/styles/liberty',
        center: center,
        zoom: 15,
        pitch: 58,
        bearing: -12,
        attributionControl: true
    });

    map.addControl(new maplibregl.NavigationControl(), 'top-right');
    map.addControl(new maplibregl.FullscreenControl(), 'top-right');

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

    var centerMarker = new maplibregl.Marker({ color: '#2c3e50', draggable: true })
        .setLngLat(center)
        .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML('<strong>Geofence Center</strong><br><small>Drag to reposition</small>'))
        .addTo(map);

    function renderCircle() {
        if (!map.getSource('geofence')) {
            map.addSource('geofence', { type: 'geojson', data: circlePolygon(center[0], center[1], radius) });
        } else {
            map.getSource('geofence').setData(circlePolygon(center[0], center[1], radius));
        }

        if (!map.getLayer('geofence-fill')) {
            map.addLayer({
                id: 'geofence-fill',
                type: 'fill',
                source: 'geofence',
                paint: { 'fill-color': '#27ae60', 'fill-opacity': 0.18 }
            });
            map.addLayer({
                id: 'geofence-outline',
                type: 'line',
                source: 'geofence',
                paint: {
                    'line-color': '#1e8449',
                    'line-width': 3,
                    'line-dasharray': [0, 2, 2, 2]
                }
            });
        }

        if (!map.getSource('warning-zone')) {
            map.addSource('warning-zone', { type: 'geojson', data: circlePolygon(center[0], center[1], Math.max(25, radius - warningThreshold)) });
        } else {
            map.getSource('warning-zone').setData(circlePolygon(center[0], center[1], Math.max(25, radius - warningThreshold)));
        }
        if (!map.getLayer('warning-zone-fill')) {
            map.addLayer({
                id: 'warning-zone-fill',
                type: 'fill',
                source: 'warning-zone',
                paint: { 'fill-color': '#f39c12', 'fill-opacity': 0.06 }
            });
        }
    }

    function moveCenter(lng, lat) {
        center = [lng, lat];
        centerMarker.setLngLat([lng, lat]);
        renderCircle();
        updateReadouts();
    }

    centerMarker.on('dragend', function () {
        var pos = centerMarker.getLngLat();
        moveCenter(pos.lng, pos.lat);
    });

    map.on('click', function (e) {
        moveCenter(e.lngLat.lng, e.lngLat.lat);
    });

    var radiusSlider = document.getElementById('radiusSlider');
    var thresholdSlider = document.getElementById('thresholdSlider');
    var radiusValue = document.getElementById('radiusValue');
    var thresholdValue = document.getElementById('thresholdValue');

    radiusSlider.addEventListener('input', function () {
        radius = parseInt(this.value, 10);
        radiusValue.textContent = numberFormat(radius) + ' m';
        renderCircle();
    });

    thresholdSlider.addEventListener('input', function () {
        warningThreshold = parseInt(this.value, 10);
        thresholdValue.textContent = numberFormat(warningThreshold) + ' m';
        renderCircle();
    });

    function numberFormat(n) {
        return n.toLocaleString('en-US');
    }

    function updateReadouts() {
        document.getElementById('centerLatInput').value = center[1].toFixed(6);
        document.getElementById('centerLngInput').value = center[0].toFixed(6);
        document.getElementById('centerReadout').textContent =
            'Center: ' + center[1].toFixed(6) + ', ' + center[0].toFixed(6) + ' • Radius: ' + numberFormat(radius) + ' m';
    }

    var searchInput = document.getElementById('searchLocationInput');
    var searchBtn = document.getElementById('searchLocationBtn');
    var clearSearchBtn = document.getElementById('clearSearchBtn');
    var searchStatus = document.getElementById('searchStatus');
    var searchTimeout = null;

    function performSearch() {
        var query = searchInput.value.trim();
        if (!query) return;

        searchBtn.disabled = true;
        searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        searchStatus.textContent = 'Searching...';
        searchStatus.className = 'form-text text-muted';

        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (results) {
            if (!results || results.length === 0) {
                searchStatus.textContent = 'No results found.';
                searchStatus.className = 'form-text text-danger';
                return;
            }
            var place = results[0];
            var lng = parseFloat(place.lon);
            var lat = parseFloat(place.lat);

            map.flyTo({ center: [lng, lat], zoom: 15, essential: true });
            moveCenter(lng, lat);

            searchStatus.textContent = 'Found: ' + place.display_name;
            searchStatus.className = 'form-text text-success';
            clearSearchBtn.classList.remove('d-none');
        })
        .catch(function () {
            searchStatus.textContent = 'Search failed. Try again.';
            searchStatus.className = 'form-text text-danger';
        })
        .finally(function () {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="bi bi-search"></i>';
        });
    }

    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        if (!searchInput.value.trim()) {
            clearSearchBtn.classList.add('d-none');
            searchStatus.textContent = '';
        }
    });

    clearSearchBtn.addEventListener('click', function () {
        searchInput.value = '';
        searchStatus.textContent = '';
        clearSearchBtn.classList.add('d-none');
    });

    var saveBtn = document.getElementById('saveGeofenceBtn');
    var saveStatus = document.getElementById('saveStatus');

    saveBtn.addEventListener('click', function () {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        var payload = {
            centerLat: center[1],
            centerLng: center[0],
            radius: radius,
            warningThreshold: warningThreshold,
            alertEnabled: document.getElementById('alertToggle').checked
        };

        var url = {!! json_encode(route('admin.geofence.update')) !!};
        fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json().then(function (d) { if (!r.ok) throw new Error(d.message || 'Save failed'); return d; }); })
        .then(function () {
            saveStatus.className = 'badge-admin badge-admin--success';
            saveStatus.innerHTML = '<i class="bi bi-check-circle me-1"></i>Saved';
            saveStatus.style.display = 'inline-flex';
            setTimeout(function () { saveStatus.style.display = 'none'; }, 4000);
        })
        .catch(function (e) {
            saveStatus.className = 'badge-admin badge-admin--danger';
            saveStatus.innerHTML = '<i class="bi bi-x-circle me-1"></i>' + e.message;
            saveStatus.style.display = 'inline-flex';
        })
        .finally(function () {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>Save Geofence Location';
        });
    });

    map.on('load', function () {
        renderCircle();
        updateReadouts();
    });
})();
</script>
@endsection