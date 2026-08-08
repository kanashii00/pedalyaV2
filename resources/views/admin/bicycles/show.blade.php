@extends('layouts.admin')

@section('title', 'Bicycle Details')

@section('page-header')
    <h1>Bicycle Details</h1>
    <p>Serial: {{ $bicycle->serialNumber }}</p>
@endsection

@section('actions')
    <a href="{{ route('admin.bicycles.index') }}" class="btn-admin btn-admin--secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Bicycles
    </a>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Overview -->
    <div class="col-lg-5">
        <x-admin.card>
            <div class="text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                    style="width:90px;height:90px;background:linear-gradient(135deg,var(--brand),var(--brand-strong));color:#fff;font-size:2.4rem;">
                    <i class="bi bi-bicycle"></i>
                </div>
                <h5 class="mb-1">{{ $bicycle->name }}</h5>
                <p class="text-muted mb-3">{{ $bicycle->model ?? 'Standard Model' }}</p>
                <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                    <x-admin.badge :type="match($bicycle->status) { 'available' => 'success', 'rented' => 'info', 'maintenance' => 'warning', default => 'neutral' }" :label="ucfirst($bicycle->status)" />
                    <x-admin.badge :type="$bicycle->lockStatus === 'locked' ? 'danger' : 'success'" :label="$bicycle->lockStatus === 'locked' ? 'Locked' : 'Unlocked'" />
                    <x-admin.badge :type="$bicycle->batteryLevel <= 20 ? 'danger' : 'success'" :label="$bicycle->batteryLevel . '%'" />
                </div>
                <div class="d-flex justify-content-center gap-4">
                    <div class="text-center"><strong class="d-block">{{ $bicycle->totalRentals }}</strong><small class="text-muted">Rentals</small></div>
                    <div class="text-center"><strong class="d-block">{{ $bicycle->totalDistance }} km</strong><small class="text-muted">Distance</small></div>
                    <div class="text-center"><strong class="d-block">₱{{ number_format($bicycle->hourlyRate, 2) }}</strong><small class="text-muted">/hr</small></div>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="Remote Lock Control">
            <p class="text-muted" style="font-size:0.85rem;">Send a command to the ESP32 smart lock via the device's pending command queue.</p>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.bicycles.lock', $bicycle->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="lock">
                    <button type="submit" class="btn-admin btn-admin--danger" {{ $bicycle->lockStatus === 'locked' ? 'disabled' : '' }}>
                        <i class="bi bi-lock-fill me-1"></i>Lock
                    </button>
                </form>
                <form action="{{ route('admin.bicycles.lock', $bicycle->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="unlock">
                    <button type="submit" class="btn-admin btn-admin--secondary" {{ $bicycle->lockStatus === 'unlocked' ? 'disabled' : '' }}>
                        <i class="bi bi-unlock-fill me-1"></i>Unlock
                    </button>
                </form>
            </div>
            <small class="text-muted d-block mt-2">
                Last action: {{ $bicycle->lastLockAction ? $bicycle->lastLockAction->diffForHumans() : 'Never' }}
                @if($bicycle->lockActionBy) by #{{ $bicycle->lockActionBy }} @endif
            </small>
        </x-admin.card>
    </div>

    <!-- Telemetry -->
    <div class="col-lg-7">
        <x-admin.card title="Device Telemetry">
            @php $t = $bicycle->latestTelemetry; @endphp
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded bg-light">
                        <small class="text-muted d-block">Battery</small>
                        <strong class="fs-5">{{ $t?->battery['level'] ?? $bicycle->batteryLevel ?? '—' }}%</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded bg-light">
                        <small class="text-muted d-block">Lock</small>
                        <strong class="fs-5">{{ ucfirst($t?->lockStatus ?? $bicycle->lockStatus) }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded bg-light">
                        <small class="text-muted d-block">Uptime</small>
                        <strong class="fs-5">{{ $t?->uptime ? number_format($t->uptime / 3600, 1) . 'h' : '—' }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded bg-light">
                        <small class="text-muted d-block">Firmware</small>
                        <strong class="fs-6">{{ $t?->deviceVersion ?? '—' }}</strong>
                    </div>
                </div>
            </div>
            <hr>
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><th class="text-muted" style="width:40%">Last Heartbeat</th><td>{{ $bicycle->lastHeartbeat ? $bicycle->lastHeartbeat->diffForHumans() : '—' }}</td></tr>
                    <tr><th class="text-muted">Last GPS Update</th><td>{{ $bicycle->lastGpsUpdate ? $bicycle->lastGpsUpdate->diffForHumans() : '—' }}</td></tr>
                    <tr><th class="text-muted">Current Location</th>
                        <td>
                            @if($bicycle->currentLat && $bicycle->currentLng)
                                {{ $bicycle->currentLat }}, {{ $bicycle->currentLng }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th class="text-muted">Current Rider</th><td>{{ $bicycle->currentRiderUser->name ?? ($bicycle->currentRider ?? '—') }}</td></tr>
                    <tr><th class="text-muted">Condition</th><td>{{ ucfirst($bicycle->condition) }}</td></tr>
                </tbody>
            </table>
        </x-admin.card>

        <x-admin.card title="Live Position" :flush="true">
            <div id="bicycleDetailMap" style="height:340px;width:100%;"></div>
        </x-admin.card>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('bicycleDetailMap');
        if (!el || typeof maplibregl === 'undefined') return;

        const center = {
            lat: parseFloat({{ $bicycle->currentLat ?? '7.0990' }}),
            lng: parseFloat({{ $bicycle->currentLng ?? '125.6470' }})
        };

        const map = new maplibregl.Map({
            container: el,
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: [center.lng, center.lat],
            zoom: 16,
            pitch: 55,
            bearing: -20,
            attributionControl: true
        });

        map.addControl(new maplibregl.NavigationControl(), 'top-right');
        map.addControl(new maplibregl.FullscreenControl(), 'top-right');

        new maplibregl.Marker({ color: '#e74c3c' })
            .setLngLat([center.lng, center.lat])
            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(
                '<strong>{{ $bicycle->name }}</strong><br>#{{ $bicycle->serialNumber }}<br>{{ ucfirst($bicycle->status) }}'
            ))
            .addTo(map);
    });
</script>
@endsection