@extends('layouts.admin')

@section('title', 'Dashboard Overview — Pedalya Admin')

@section('actions')
    <a href="{{ route('admin.id-scans.create') }}" class="btn-admin btn-admin--secondary btn-admin--sm">
        <i class="bi bi-person-badge"></i> Scan ID
    </a>
    <a href="{{ route('admin.bicycles.index') }}?action=add" class="btn-admin btn-admin--secondary btn-admin--sm">
        <i class="bi bi-plus-circle"></i> Add Bicycle
    </a>
    <button class="btn-admin btn-admin--primary btn-admin--sm" onclick="window.PedalyaModal.open('quickRentalModal')">
        <i class="bi bi-key"></i> Quick Rental
    </button>
@endsection

@section('content')
{{-- Greeting --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h3 style="font-size: 17px; margin: 0;">Good {{ now()->format('A') === 'AM' ? 'morning' : 'afternoon' }}, {{ auth()->user()->name }} 👋</h3>
        <p style="color: var(--text-3); font-size: 13px; margin: 3px 0 0;">
            Here's what's happening with Pedalya at Azuela Cove today.
        </p>
    </div>
    <div class="admin-clock d-flex align-items-center gap-2" style="color: var(--text-3); font-size: 13px;">
        <i class="bi bi-calendar3"></i>{{ now()->format('l, F j, Y') }}
    </div>
</div>

{{-- ===================== KPI CARDS ===================== --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <x-admin.kpi title="Active Rentals" value="{{ $stats['rentals']['active'] ?? 0 }}"
                     icon="bi-play-circle" color="var(--accent)" foot="in progress now"
                     link="{{ route('admin.rentals.index') }}?filter=active" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-admin.kpi title="Available Bicycles" value="{{ $stats['bicycles']['available'] ?? 0 }}"
                     icon="bi-bicycle" color="var(--success)" foot="of {{ $stats['bicycles']['total'] ?? 0 }} total"
                     link="{{ route('admin.bicycles.index') }}" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-admin.kpi title="Live GPS Devices" value="{{ $stats['devices']['gpsOnline'] ?? 0 }}"
                     icon="bi-geo-alt" color="var(--info)" foot="active GPS fix"
                     link="{{ route('admin.monitoring.index') }}" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-admin.kpi title="Active Alerts" value="{{ $stats['alerts']['activeAlerts'] ?? 0 }}"
                     icon="bi-shield-exclamation" color="var(--danger)" foot="Theft + Accident combined"
                     link="{{ route('admin.theft-alerts.index') }}" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-admin.kpi title="Today's Revenue" value="₱{{ number_format($stats['revenue']['today'] ?? 0, 0) }}"
                     icon="bi-cash-stack" color="var(--success)" foot="completed today"
                     link="{{ route('admin.reports.index') }}?tab=revenue" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-admin.kpi title="Verified Customers" value="{{ $stats['users']['verified'] ?? 0 }}"
                     icon="bi-patch-check" color="var(--success)" foot="ID verified"
                     link="{{ route('admin.riders.index') }}?filter=verified" />
    </div>
</div>

{{-- ===================== ANALYTICS ===================== --}}
<div id="analytics" class="d-flex align-items-center justify-content-between mb-3 mt-2">
    <h4 style="font-size: 15px; font-weight: 700; margin: 0;"><i class="bi bi-graph-up-arrow me-2" style="color: var(--brand);"></i>Analytics</h4>
    <button class="btn-admin btn-admin--ghost btn-admin--sm" onclick="refreshCharts()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-8">
        <x-admin.card title="Revenue & Rental Trends" sub="Last 12 months">
            <div class="chart-box chart-box--lg"><canvas id="trendsChart"></canvas></div>
        </x-admin.card>
    </div>
    <div class="col-12 col-xl-4">
        <x-admin.card title="Weekly Rentals" sub="Last 7 days">
            <div class="chart-box"><canvas id="weeklyChart"></canvas></div>
        </x-admin.card>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <x-admin.card title="Peak Rental Hours" sub="Last 7 days">
            <div class="chart-box"><canvas id="peakChart"></canvas></div>
        </x-admin.card>
    </div>
    <div class="col-12 col-lg-3">
        <x-admin.card title="Fleet Status" sub="Live distribution">
            <div class="chart-box chart-box--sm"><canvas id="fleetChart"></canvas></div>
        </x-admin.card>
    </div>
    <div class="col-12 col-lg-3">
        <x-admin.card title="Battery Health" sub="Across fleet">
            <div class="chart-box chart-box--sm"><canvas id="batteryChart"></canvas></div>
        </x-admin.card>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-7">
        <x-admin.card title="Incident Trends" sub="Theft vs accidents, last 12 months">
            <div class="chart-box"><canvas id="incidentChart"></canvas></div>
        </x-admin.card>
    </div>
    <div class="col-12 col-xl-5">
        <x-admin.card title="Device Health" sub="Connectivity overview">
            <div class="d-grid gap-3">
                @php
                    $totalDevices = max($stats['devices']['total'] ?? 0, 1);
                    $gpsPct = round((($stats['devices']['gpsOnline'] ?? 0) / $totalDevices) * 100);
                    $iotPct = round((($stats['devices']['iotOnline'] ?? 0) / $totalDevices) * 100);
                    $batteryOk = max($stats['battery']['good'] ?? 0, 0) + max($stats['battery']['full'] ?? 0, 0);
                    $batteryPct = round(($batteryOk / $totalDevices) * 100);
                @endphp
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size: 12.5px;">
                        <span style="color: var(--text-2); font-weight: 600;"><i class="bi bi-geo-alt me-1" style="color: var(--info);"></i>GPS Connectivity</span>
                        <span style="color: var(--text-1); font-weight: 700;">{{ $gpsPct }}%</span>
                    </div>
                    <div class="admin-progress"><div class="admin-progress__bar" data-progress="{{ $gpsPct }}" style="width: {{ $gpsPct }}%; background: var(--info);"></div></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size: 12.5px;">
                        <span style="color: var(--text-2); font-weight: 600;"><i class="bi bi-cpu me-1" style="color: var(--purple);"></i>IoT Heartbeat</span>
                        <span style="color: var(--text-1); font-weight: 700;">{{ $iotPct }}%</span>
                    </div>
                    <div class="admin-progress"><div class="admin-progress__bar" data-progress="{{ $iotPct }}" style="width: {{ $iotPct }}%; background: var(--purple);"></div></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size: 12.5px;">
                        <span style="color: var(--text-2); font-weight: 600;"><i class="bi bi-battery-full me-1" style="color: var(--success);"></i>Healthy Battery</span>
                        <span style="color: var(--text-1); font-weight: 700;">{{ $batteryPct }}%</span>
                    </div>
                    <div class="admin-progress"><div class="admin-progress__bar" data-progress="{{ $batteryPct }}" style="width: {{ $batteryPct }}%; background: var(--success);"></div></div>
                </div>
                <div class="mt-2 pt-3 border-top" style="border-color: var(--border-subtle) !important;">
                    <div class="d-flex justify-content-between mb-1" style="font-size: 12.5px;">
                        <span style="color: var(--text-2); font-weight: 600;">Battery breakdown</span>
                        <span style="color: var(--text-3);">good / fair / low</span>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <span class="badge-admin badge-admin--success badge-admin--plain">{{ $stats['battery']['full'] ?? 0 }} full</span>
                        <span class="badge-admin badge-admin--brand badge-admin--plain">{{ $stats['battery']['good'] ?? 0 }} good</span>
                        <span class="badge-admin badge-admin--warning badge-admin--plain">{{ $stats['battery']['mid'] ?? 0 }} fair</span>
                        <span class="badge-admin badge-admin--danger badge-admin--plain">{{ $stats['battery']['low'] ?? 0 }} low</span>
                    </div>
                </div>
            </div>
        </x-admin.card>
    </div>
</div>

{{-- ===================== ACTIVITY FEED ===================== --}}
<div class="row g-3">
    <div class="col-12 col-lg-6">
        <x-admin.card title="Recent Rentals" sub="Latest activity" :flush="true">
            @forelse($recentRentals ?? [] as $r)
                <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom" style="border-color: var(--border-subtle) !important;">
                    <div class="kpi__icon" style="width: 36px; height: 36px; font-size: 15px; background: var(--brand-soft); color: var(--brand-strong);">
                        <i class="bi bi-bicycle"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-1);" class="text-truncate">{{ $r->riderName ?? 'Rider' }} → {{ $r->bicycleName ?? $r->bicycleId }}</div>
                        <div style="font-size: 12px; color: var(--text-3);">{{ $r->created_at->format('M j, g:i A') }} · {{ $r->status }}</div>
                    </div>
                    <x-admin.badge :type="match($r->status) { 'active' => 'success', 'pending' => 'warning', 'completed' => 'info', 'cancelled' => 'neutral', default => 'neutral' }" :label="$r->status" />
                </div>
            @empty
                <x-admin.empty-state icon="bi-key" title="No rentals yet" message="Rental activity will appear here as customers start riding." />
            @endforelse
        </x-admin.card>
    </div>

    <div class="col-12 col-lg-6">
        <x-admin.card title="Incident Feed" sub="Theft & accident alerts">
            @forelse($recentIncidents ?? [] as $inc)
                <div class="admin-timeline__item mb-3" style="padding-bottom: 14px;">
                    <span class="admin-timeline__dot {{ $inc->type === 'theft' ? 'admin-timeline__dot--danger' : 'admin-timeline__dot--warning' }}"></span>
                    <div class="admin-timeline__time">{{ $inc->created_at->diffForHumans() }}</div>
                    <div class="admin-timeline__title text-capitalize">{{ $inc->type }} detected · {{ $inc->bicycle->name ?? 'Bike #' . $inc->bicycleId }}</div>
                    <div style="font-size: 12px; color: var(--text-3);">Severity: {{ $inc->severity }} · {{ $inc->acknowledged ? 'Acknowledged' : 'Unacknowledged' }}</div>
                </div>
            @empty
                <x-admin.empty-state icon="bi-shield-check" title="No incidents" message="All clear. No theft or accident alerts right now." />
            @endforelse
        </x-admin.card>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <x-admin.card title="Low Battery Alerts" sub="Units at 20% or below" :flush="true">
            @forelse($lowBatteryBicycles ?? [] as $bike)
                <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom" style="border-color: var(--border-subtle) !important;">
                    <div class="kpi__icon" style="width: 36px; height: 36px; font-size: 15px; background: var(--warning-soft); color: var(--warning);">
                        <i class="bi bi-battery-quarter"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-1);">{{ $bike->name }}</div>
                        <div style="font-size: 12px; color: var(--text-3);">{{ $bike->serialNumber }}</div>
                    </div>
                    <span class="badge-admin badge-admin--{{ $bike->batteryLevel <= 15 ? 'danger' : 'warning' }} badge-admin--plain">{{ $bike->batteryLevel }}%</span>
                </div>
            @empty
                <x-admin.empty-state icon="bi-check-circle" title="All batteries healthy" message="No bicycles below 20% battery." />
            @endforelse
        </x-admin.card>
    </div>

    <div class="col-12 col-lg-6">
        <x-admin.card title="Maintenance Schedule" sub="Upcoming and in-progress work" :flush="true">
            @forelse($upcomingMaintenance ?? [] as $m)
                <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom" style="border-color: var(--border-subtle) !important;">
                    <div class="kpi__icon" style="width: 36px; height: 36px; font-size: 15px; background: var(--danger-soft); color: var(--danger);">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-1);">{{ $m->bicycleName ?? 'Bike #' . $m->bicycleId }}</div>
                        <div style="font-size: 12px; color: var(--text-3);">{{ $m->type }} · {{ $m->scheduledDate?->format('M j') }}</div>
                    </div>
                    <x-admin.badge :type="match($m->status) { 'scheduled' => 'warning', 'in_progress' => 'info', 'completed' => 'success', default => 'neutral' }" :label="str_replace('_', ' ', $m->status)" />
                </div>
            @empty
                <x-admin.empty-state icon="bi-tools" title="No maintenance scheduled" message="All bicycles are cleared for service." />
            @endforelse
        </x-admin.card>
    </div>
</div>

{{-- Quick Rental modal --}}
<div class="admin-modal" id="quickRentalModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__head">
            <h3>Quick Rental</h3>
            <button class="admin-icon-btn" data-modal-close><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="admin-modal__body">
            <p style="color: var(--text-2); font-size: 13px;">Start a rental from the available fleet. Manage details from the Rental Management module.</p>
            <a href="{{ route('admin.rentals.index') }}?filter=active" class="btn-admin btn-admin--primary btn-admin--block">
                <i class="bi bi-key"></i> Go to Rental Management
            </a>
            <a href="{{ route('admin.monitoring.index') }}" class="btn-admin btn-admin--secondary btn-admin--block mt-2">
                <i class="bi bi-map"></i> View live map
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || getComputedStyle(document.body).getPropertyValue(name).trim();
    const color = (name, fallback) => css(name) || fallback;
    const brand = color('--brand', '#2E7D32');
    const text3 = color('--text-3', '#94A3B8');
    const grid = color('--border-subtle', '#E7ECF1');

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = color('--text-3', '#94A3B8');
    Chart.defaults.borderColor = grid;

    const charts = [];

    /* Revenue + rentals combo */
    const trendsCtx = document.getElementById('trendsChart');
    if (trendsCtx) {
        charts.push(new Chart(trendsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($stats['monthlyRentalsLabels'] ?? []) !!},
                datasets: [
                    {
                        type: 'line',
                        label: 'Revenue (₱)',
                        data: {!! json_encode($stats['monthlyRevenueData'] ?? []) !!},
                        borderColor: brand,
                        backgroundColor: 'rgba(46,125,50,0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y',
                        pointBackgroundColor: brand,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        type: 'bar',
                        label: 'Rentals',
                        data: {!! json_encode($stats['monthlyRentalsData'] ?? []) !!},
                        backgroundColor: 'rgba(14,165,233,0.55)',
                        borderRadius: 5,
                        borderSkipped: false,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } },
                scales: {
                    y: { position: 'left', grid: { color: grid }, ticks: { callback: v => '₱' + (v >= 1000 ? (v / 1000) + 'k' : v) } },
                    y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } },
                },
            },
        }));
    }

    /* Weekly rentals */
    const weeklyCtx = document.getElementById('weeklyChart');
    if (weeklyCtx) {
        charts.push(new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($stats['weeklyLabels'] ?? []) !!},
                datasets: [{
                    label: 'Rentals',
                    data: {!! json_encode($stats['weeklyData'] ?? []) !!},
                    backgroundColor: 'rgba(46,125,50,0.75)',
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: grid }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } },
            },
        }));
    }

    /* Peak hours */
    const peakCtx = document.getElementById('peakChart');
    if (peakCtx) {
        const peakData = {!! json_encode($stats['peakData'] ?? []) !!};
        charts.push(new Chart(peakCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($stats['peakLabels'] ?? []) !!},
                datasets: [{
                    label: 'Rentals',
                    data: peakData,
                    backgroundColor: peakData.map((_, i) => i >= 17 && i <= 20 ? 'rgba(217,119,6,0.85)' : 'rgba(46,125,50,0.5)'),
                    borderRadius: 4,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: grid }, ticks: { stepSize: 1 } }, x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } } },
            },
        }));
    }

    /* Fleet status doughnut */
    const fleetCtx = document.getElementById('fleetChart');
    if (fleetCtx) {
        charts.push(new Chart(fleetCtx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Rented', 'Maintenance', 'Locked'],
                datasets: [{
                    data: [
                        {{ $stats['bicycles']['available'] ?? 0 }},
                        {{ $stats['bicycles']['rented'] ?? 0 }},
                        {{ $stats['bicycles']['maintenance'] ?? 0 }},
                        {{ $stats['bicycles']['total'] - $stats['bicycles']['available'] - $stats['bicycles']['rented'] - $stats['bicycles']['maintenance'] ?? 0 }},
                    ],
                    backgroundColor: [color('--success', '#16A34A'), color('--accent', '#2563EB'), color('--warning', '#D97706'), color('--text-3', '#94A3B8')],
                    borderWidth: 2,
                    borderColor: color('--surface', '#FFFFFF'),
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, font: { size: 11 } } } },
            },
        }));
    }

    /* Battery doughnut */
    const batteryCtx = document.getElementById('batteryChart');
    if (batteryCtx) {
        charts.push(new Chart(batteryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Low (≤20%)', 'Fair (21-50%)', 'Good (51-80%)', 'Full (81%+)'],
                datasets: [{
                    data: [
                        {{ $stats['battery']['low'] ?? 0 }},
                        {{ $stats['battery']['mid'] ?? 0 }},
                        {{ $stats['battery']['good'] ?? 0 }},
                        {{ $stats['battery']['full'] ?? 0 }},
                    ],
                    backgroundColor: [color('--danger', '#DC2626'), color('--warning', '#D97706'), color('--brand', '#2E7D32'), color('--success', '#16A34A')],
                    borderWidth: 2,
                    borderColor: color('--surface', '#FFFFFF'),
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, font: { size: 11 } } } },
            },
        }));
    }

    /* Incident trends */
    const incidentCtx = document.getElementById('incidentChart');
    if (incidentCtx) {
        charts.push(new Chart(incidentCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($stats['monthlyRentalsLabels'] ?? []) !!},
                datasets: [
                    { label: 'Theft', data: {!! json_encode($stats['theftTrendData'] ?? []) !!}, borderColor: color('--danger', '#DC2626'), backgroundColor: 'rgba(220,38,38,0.08)', fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 3 },
                    { label: 'Accidents', data: {!! json_encode($stats['accidentTrendData'] ?? []) !!}, borderColor: color('--warning', '#D97706'), backgroundColor: 'rgba(217,119,6,0.08)', fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 3 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } },
                scales: { y: { beginAtZero: true, grid: { color: grid }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } },
            },
        }));
    }

    window.PedalyaCharts = charts;
});

window.refreshCharts = function () {
    if (window.PedalyaCharts) {
        window.PedalyaCharts.forEach(c => c.update());
        window.PedalyaToast.success('Charts refreshed', 'Analytics');
    }
};
</script>
@endsection
