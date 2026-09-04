

<?php
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
?>

<?php $__env->startSection('title', ($sectionTitles[$section] ?? 'Monitoring') . ' — Pedalya Admin'); ?>

<?php $__env->startSection('styles'); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-header'); ?>
    <h1><?php echo e($sectionTitles[$section]); ?></h1>
    <p><?php echo e($sectionSubs[$section]); ?></p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
<button class="btn-admin btn-admin--secondary btn-admin--sm" id="autoRefreshBtn" onclick="toggleAutoRefresh()">
    <i class="bi bi-arrow-clockwise me-1"></i><span id="refreshLabel">Auto-Refresh: Off</span>
</button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="section-tabs">
    <?php $__currentLoopData = $sectionTitles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e($key === 'map' ? route('admin.monitoring.index') : route('admin.monitoring.index') . '?section=' . $key); ?>"
           class="<?php echo e($section === $key ? 'active' : ''); ?>">
            <i class="bi <?php echo e($sectionIcons[$key]); ?>"></i> <?php echo e($title); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<?php if(in_array($section, ['map', 'gps'])): ?>
<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => ''.e($section === 'gps' ? 'Live GPS Tracking Map' : 'Bicycle Locations').'','bodyClass' => 'p-0 position-relative mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($section === 'gps' ? 'Live GPS Tracking Map' : 'Bicycle Locations').'','bodyClass' => 'p-0 position-relative mb-4']); ?>
     <?php $__env->slot('tools', null, []); ?> 
        <small class="text-muted me-3" id="fleetCount"><?php echo e(count($bicycles ?? [])); ?> bicycle(s)</small>
        <small class="text-muted">
            Geofence: <span id="geofenceRadiusText"><?php echo e(number_format($geofence['radius'], 0)); ?>m</span>
            <span id="geofenceAlertBadge"><?php if($geofence['alertEnabled']): ?>
                <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Alerts ON']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Alerts ON']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
            <?php endif; ?></span>
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
     <?php $__env->endSlot(); ?>
    <div id="monitoringMap"></div>
    <div class="map-legend">
        <div><span class="dot" style="background:#2ecc71;"></span>Inside Zone <span class="legend-count" data-count="safe">0</span></div>
        <div><span class="dot" style="background:#f39c12;"></span>Near Boundary <span class="legend-count" data-count="near">0</span></div>
        <div><span class="dot" style="background:#e74c3c;"></span>Outside Zone <span class="legend-count" data-count="outside">0</span></div>
        <div><span class="dot" style="background:#27ae60;"></span>Geofence Boundary</div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php endif; ?>

<?php if($section === 'map'): ?>

<div class="row align-items-center mb-3">
    <div class="col">
        <h5 class="mb-0">Fleet Status</h5>
    </div>
    <div class="col-auto">
        <small class="text-muted">Last updated: <?php echo e(now()->format('h:i:s A')); ?></small>
    </div>
</div>

<div class="row g-3">
    <?php $__empty_1 = true; $__currentLoopData = $bicycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bike): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $zoneLevel = $bike->zone['level'] ?? 'unknown';
            $zonePill = match ($zoneLevel) {
                'breach' => ['bg' => '#e74c3c', 'label' => 'Outside Zone'],
                'warning', 'approaching' => ['bg' => '#f39c12', 'label' => 'Near Boundary'],
                'safe' => ['bg' => '#2ecc71', 'label' => 'Inside Zone'],
                default => ['bg' => '#95a5a6', 'label' => 'No GPS'],
            };
        ?>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="bike-monitor-card" data-bike-id="<?php echo e($bike->id); ?>">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <span class="bike-status-dot <?php echo e(($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 5 ? 'online' : (($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 30 ? 'stale' : 'offline')); ?>"></span>
                        <span class="fw-semibold ms-2"><?php echo e($bike->name); ?></span>
                    </div>
                    <span class="zone-pill" style="background: <?php echo e($zonePill['bg']); ?>22; color: <?php echo e($zonePill['bg']); ?>;">
                        <span class="dot" style="background: <?php echo e($zonePill['bg']); ?>;"></span><?php echo e($zonePill['label']); ?>

                    </span>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <small class="text-muted d-block">Battery</small>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:5px;">
                                <div class="progress-bar bg-<?php echo e($bike->batteryLevel <= 20 ? 'danger' : ($bike->batteryLevel <= 50 ? 'warning' : 'success')); ?>"
                                     style="width:<?php echo e($bike->batteryLevel); ?>%"></div>
                            </div>
                            <small class="fw-semibold"><?php echo e($bike->batteryLevel); ?>%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Lock</small>
                        <?php if($bike->lockStatus === 'locked'): ?>
                            <span class="text-danger fw-semibold"><i class="bi bi-lock-fill me-1"></i>Locked</span>
                        <?php else: ?>
                            <span class="text-success fw-semibold"><i class="bi bi-unlock me-1"></i>Unlocked</span>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-2" style="border-color:#f0f0f0;">

                <div class="row g-1">
                    <div class="col-6">
                        <small class="text-muted d-block">Last Heartbeat</small>
                        <small class="fw-semibold"><?php echo e($bike->lastHeartbeat?->diffForHumans() ?? 'Never'); ?></small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">GPS</small>
                        <small class="fw-semibold">
                            <?php if($bike->currentLat && $bike->currentLng): ?>
                                <?php echo e(number_format($bike->currentLat, 4)); ?>, <?php echo e(number_format($bike->currentLng, 4)); ?>

                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-bicycle','title' => 'No bicycles to monitor']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-bicycle','title' => 'No bicycles to monitor']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if($section === 'gps'): ?>

<div class="row align-items-center mb-3">
    <div class="col"><h5 class="mb-0">Live Position Feed</h5></div>
    <div class="col-auto">
        <small class="text-muted">Center: <?php echo e(number_format($geofence['centerLat'], 5)); ?>, <?php echo e(number_format($geofence['centerLng'], 5)); ?> · Radius <?php echo e(number_format($geofence['radius'], 0)); ?>m</small>
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
            <?php $__empty_1 = true; $__currentLoopData = $bicycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bike): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $zoneLevel = $bike->zone['level'] ?? 'unknown';
                    $zoneStyle = match ($zoneLevel) {
                        'breach' => ['#e74c3c', 'Outside Zone'],
                        'warning', 'approaching' => ['#f39c12', 'Near Boundary'],
                        'safe' => ['#2ecc71', 'Inside Zone'],
                        default => ['#95a5a6', 'No GPS'],
                    };
                    $speed = $bike->latestGpsLog?->speed;
                ?>
                <tr>
                    <td data-label="Bicycle">
                        <div class="d-flex align-items-center gap-2">
                            <span class="bike-status-dot <?php echo e(($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 5 ? 'online' : 'offline'); ?>"></span>
                            <span class="fw-semibold"><?php echo e($bike->name); ?></span>
                        </div>
                    </td>
                    <td data-label="Zone">
                        <span class="zone-pill" style="background: <?php echo e($zoneStyle[0]); ?>22; color: <?php echo e($zoneStyle[0]); ?>;">
                            <span class="dot" style="background: <?php echo e($zoneStyle[0]); ?>;"></span><?php echo e($zoneStyle[1]); ?>

                        </span>
                    </td>
                    <td data-label="Latitude"><?php echo e($bike->currentLat ? number_format((float) $bike->currentLat, 6) : '—'); ?></td>
                    <td data-label="Longitude"><?php echo e($bike->currentLng ? number_format((float) $bike->currentLng, 6) : '—'); ?></td>
                    <td data-label="Speed"><?php echo e($speed !== null ? number_format((float) $speed, 1) . ' km/h' : '—'); ?></td>
                    <td data-label="Distance from center"><?php echo e($bike->zone['distance'] !== null ? number_format($bike->zone['distance'], 0) . ' m' : '—'); ?></td>
                    <td data-label="Last GPS"><?php echo e($bike->lastGpsUpdate?->diffForHumans() ?? 'Never'); ?></td>
                    <td data-label="Last heartbeat"><?php echo e($bike->lastHeartbeat?->diffForHumans() ?? 'Never'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8"><?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-bicycle','title' => 'No bicycles to monitor']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-bicycle','title' => 'No bicycles to monitor']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if($section === 'locks'): ?>

<div class="row align-items-center mb-3">
    <div class="col"><h5 class="mb-0">Smart Lock Control</h5></div>
    <div class="col-auto">
        <small class="text-muted">Send lock / unlock commands to the ESP32 via WebSocket</small>
    </div>
</div>
<div class="row g-3">
    <?php $__empty_1 = true; $__currentLoopData = $bicycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bike): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="bike-monitor-card" data-bike-id="<?php echo e($bike->id); ?>">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <span class="bike-status-dot <?php echo e(($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 5 ? 'online' : 'offline'); ?>"></span>
                        <span class="fw-semibold ms-2"><?php echo e($bike->name); ?></span>
                    </div>
                    <?php if($bike->lockStatus === 'locked'): ?>
                        <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'danger','label' => 'Locked']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','label' => 'Locked']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Unlocked']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Unlocked']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center gap-2 mb-2" style="font-size:13px;">
                    <i class="bi <?php echo e($bike->lockStatus === 'locked' ? 'bi-lock-fill text-danger' : 'bi-unlock text-success'); ?>"></i>
                    <span class="text-muted">Last action:</span>
                    <span class="fw-semibold"><?php echo e($bike->lastLockAction?->diffForHumans() ?? 'Never'); ?></span>
                </div>

                <div class="row g-1 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Battery</small>
                        <small class="fw-semibold"><?php echo e($bike->batteryLevel); ?>%</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">GPS</small>
                        <small class="fw-semibold"><?php echo e($bike->currentLat && $bike->currentLng ? 'Valid' : 'N/A'); ?></small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <form method="POST" action="<?php echo e(route('admin.bicycles.lock', $bike->id)); ?>" class="flex-grow-1">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="lock">
                        <button type="submit" class="btn-admin btn-admin--danger btn-admin--block <?php echo e($bike->lockStatus === 'locked' ? 'disabled' : ''); ?>" <?php echo e($bike->lockStatus === 'locked' ? 'disabled' : ''); ?>>
                            <i class="bi bi-lock-fill"></i> Lock
                        </button>
                    </form>
                    <form method="POST" action="<?php echo e(route('admin.bicycles.lock', $bike->id)); ?>" class="flex-grow-1">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="unlock">
                        <button type="submit" class="btn-admin btn-admin--soft btn-admin--block <?php echo e($bike->lockStatus !== 'locked' ? 'disabled' : ''); ?>" <?php echo e($bike->lockStatus !== 'locked' ? 'disabled' : ''); ?>>
                            <i class="bi bi-unlock"></i> Unlock
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-lock','title' => 'No bicycles to control']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-lock','title' => 'No bicycles to control']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if($section === 'devices'): ?>

<div class="row align-items-center mb-3">
    <div class="col"><h5 class="mb-0">IoT Device Monitoring</h5></div>
    <div class="col-auto">
        <small class="text-muted">Firmware, telemetry and device health</small>
    </div>
</div>
<div class="row g-3">
    <?php $__empty_1 = true; $__currentLoopData = $bicycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bike): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $tel = $bike->latestTelemetry;
            $battery = is_array($tel?->battery) ? $tel->battery : null;
            $online = ($bike->lastHeartbeat?->diffInMinutes(now()) ?? 999) <= 5;
        ?>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="bike-monitor-card" data-bike-id="<?php echo e($bike->id); ?>">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <span class="bike-status-dot <?php echo e($online ? 'online' : 'offline'); ?>"></span>
                        <span class="fw-semibold ms-2"><?php echo e($bike->name); ?></span>
                    </div>
                    <span class="text-muted small"><i class="bi bi-cpu"></i> <?php echo e($tel?->deviceVersion ?? '—'); ?></span>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <small class="text-muted d-block">Battery</small>
                        <small class="fw-semibold"><?php echo e($battery['pct'] ?? $bike->batteryLevel); ?>%<?php echo e(isset($battery['charging']) && $battery['charging'] ? ' ⚡' : ''); ?></small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Uptime</small>
                        <small class="fw-semibold"><?php echo e($tel?->uptime ? gmdate('H:i:s', $tel->uptime) : '—'); ?></small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Lock</small>
                        <?php if($bike->lockStatus === 'locked'): ?>
                            <small class="fw-semibold text-danger"><i class="bi bi-lock-fill me-1"></i>Locked</small>
                        <?php else: ?>
                            <small class="fw-semibold text-success"><i class="bi bi-unlock me-1"></i>Unlocked</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">RFID</small>
                        <small class="fw-semibold"><?php echo e($tel?->rfid ? \Illuminate\Support\Str::limit($tel->rfid, 10) : '—'); ?></small>
                    </div>
                </div>

                <hr class="my-2" style="border-color:#f0f0f0;">

                <div class="row g-1">
                    <div class="col-6">
                        <small class="text-muted d-block">Last Heartbeat</small>
                        <small class="fw-semibold"><?php echo e($bike->lastHeartbeat?->diffForHumans() ?? 'Never'); ?></small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Last Telemetry</small>
                        <small class="fw-semibold"><?php echo e($tel?->eventTimestamp?->diffForHumans() ?? 'Never'); ?></small>
                    </div>
                </div>

                <?php if($tel?->command): ?>
                    <div class="mt-2 p-2 rounded" style="background:#f6f7fb; font-size:11.5px;">
                        <strong>Last command:</strong> <?php echo e($tel->command); ?>

                        <span class="text-muted"> → <?php echo e($tel->result ?? ($tel->status ?? 'sent')); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-cpu','title' => 'No devices to monitor']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-cpu','title' => 'No devices to monitor']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<?php if(in_array($section, ['map', 'gps'])): ?>
<?php
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
?>
<script src="<?php echo e(asset('js/geolibre-map.js')); ?>"></script>
<script>
(function () {
    var geofence = <?php echo json_encode($geofence); ?>;
    var bicycles = <?php echo $bicycleLocations->toJson(); ?>;

    window.PedalyaGeoLibre.init({
        container: 'monitoringMap',
        geofence: geofence,
        bicycles: bicycles,
        liveUrl: <?php echo json_encode(route('admin.monitoring.live')); ?>,
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
<?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\monitoring.blade.php ENDPATH**/ ?>