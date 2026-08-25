

<?php $__env->startSection('title', 'Geofence Management'); ?>

<?php $__env->startSection('styles'); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Geofence Management</h1>
    <p>Configure the circular riding boundary around the Azuela Cove bicycle area</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
    <span id="saveStatus" class="badge-admin badge-admin--success me-2" style="display:none;"></span>
    <button class="btn-admin btn-admin--primary" id="saveGeofenceBtn">
        <i class="bi bi-save me-1"></i>Save Geofence
    </button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?>
    <div class="alert alert-pedalya alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card inside">
            <div class="value"><?php echo e($stats['inside']); ?></div>
            <div class="label">Inside Boundary</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card near">
            <div class="value"><?php echo e($stats['near']); ?></div>
            <div class="label">Near Boundary</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card outside">
            <div class="value"><?php echo e($stats['outside']); ?></div>
            <div class="label">Outside Boundary</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card no-gps">
            <div class="value"><?php echo e($stats['noGps']); ?></div>
            <div class="label">No GPS Signal</div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-7">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Riding Boundary','flush' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Riding Boundary','flush' => true]); ?>
             <?php $__env->slot('tools', null, []); ?> <small class="text-muted" id="centerReadout"></small> <?php $__env->endSlot(); ?>
            <div class="position-relative">
                <div id="geofenceMap"></div>
                <div class="geofence-legend">
                    <div><span class="line" style="background:#27ae60;"></span>Geofence Boundary</div>
                    <div><span class="line" style="background:#f39c12;"></span>Warning Zone</div>
                    <div><span class="line" style="background:#e74c3c;"></span>Breach Zone</div>
                    <div><span class="line" style="background:#2c3e50;"></span>Center (drag to move)</div>
                </div>
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

        
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Live Bicycle Zone Status','class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Live Bicycle Zone Status','class' => 'mt-4']); ?>
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
                        <?php $__currentLoopData = $bicycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bike): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <strong><?php echo e($bike->name ?? 'Bicycle #' . $bike->id); ?></strong>
                                <?php if($bike->status): ?>
                                <span class="badge-admin badge-admin--<?php echo e($bike->status === 'rented' ? 'primary' : ($bike->status === 'maintenance' ? 'warning' : 'secondary')); ?> ms-1" style="font-size:0.65rem;"><?php echo e(ucfirst($bike->status)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($bike->currentRiderUser): ?>
                                    <?php echo e($bike->currentRiderUser->name); ?>

                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(isset($bike->zone)): ?>
                                    <span class="zone-badge <?php echo e($bike->zone['level'] ?? 'no-gps'); ?>">
                                        <?php echo e(ucfirst($bike->zone['level'] ?? 'No GPS')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="zone-badge no-gps">No GPS</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(isset($bike->zone['distance']) && $bike->zone['distance'] !== null): ?>
                                    <?php echo e(number_format($bike->zone['distance'], 1)); ?> m
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($bike->currentLat && $bike->currentLng): ?>
                                    <span class="badge-admin badge-admin--success">Active</span>
                                <?php else: ?>
                                    <span class="badge-admin badge-admin--secondary">No Signal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
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
    </div>

    
    <div class="col-lg-5">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Boundary Controls']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Boundary Controls']); ?>
            <div class="admin-form">
                <div class="mb-4">
                    <label class="form-label d-flex justify-content-between">
                        <span>Radius</span>
                        <strong id="radiusValue"><?php echo e(number_format($config['radius'], 0)); ?> m</strong>
                    </label>
                    <input type="range" class="radius-slider" id="radiusSlider" min="50" max="3000" step="10"
                           value="<?php echo e($config['radius']); ?>">
                    <div class="d-flex justify-content-between text-muted mt-1" style="font-size:0.75rem;">
                        <span>50 m</span><span>1500 m</span><span>3000 m</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label d-flex justify-content-between">
                        <span>Warning Threshold (inside boundary)</span>
                        <strong id="thresholdValue"><?php echo e(number_format($warningThreshold, 0)); ?> m</strong>
                    </label>
                    <input type="range" class="radius-slider" id="thresholdSlider" min="10" max="500" step="5"
                           value="<?php echo e($warningThreshold); ?>">
                    <div class="form-text">Bicycles within this distance of the boundary are flagged as approaching.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Center Coordinates</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Lat</span>
                        <input type="text" class="form-control" id="centerLatInput"
                               value="<?php echo e(number_format($config['centerLat'], 6)); ?>" readonly>
                    </div>
                    <div class="input-group input-group-sm mt-2">
                        <span class="input-group-text">Lng</span>
                        <input type="text" class="form-control" id="centerLngInput"
                               value="<?php echo e(number_format($config['centerLng'], 6)); ?>" readonly>
                    </div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="alertToggle"
                           <?php echo e($config['alertEnabled'] ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="alertToggle">Geofence alerts enabled (breach notifications & theft detection)</label>
                </div>
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

        
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Theft & Breach Incidents','class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Theft & Breach Incidents','class' => 'mt-4']); ?>
            <div style="max-height:280px;overflow-y:auto;">
                <?php $__empty_1 = true; $__currentLoopData = $theftIncidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="incident-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong style="font-size:0.85rem;">
                                <?php echo e($incident->bicycle->name ?? 'Bicycle #' . $incident->bicycleId); ?>

                            </strong>
                            <span class="badge-admin badge-admin--<?php echo e(in_array($incident->type, ['theft']) ? 'danger' : 'warning'); ?>" style="font-size:0.65rem;">
                                <?php echo e(ucfirst(str_replace('_', ' ', $incident->type))); ?>

                            </span>
                        </div>
                        <small class="text-muted d-block mt-1">
                            <?php echo e($incident->distanceFromBoundary ? number_format($incident->distanceFromBoundary, 1) . 'm from boundary' : ($incident->breachDistance ? number_format($incident->breachDistance, 1) . 'm outside' : '')); ?>

                            <?php if($incident->location && isset($incident->location['lat'])): ?>
                                — <?php echo e(number_format($incident->location['lat'], 5)); ?>, <?php echo e(number_format($incident->location['lng'] ?? $incident->location['lon'] ?? 0, 5)); ?>

                            <?php endif; ?>
                        </small>
                        <small class="text-muted d-block"><?php echo e($incident->created_at->format('M d, Y g:i A')); ?></small>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted text-center mb-0 py-3">
                        <i class="bi bi-shield-check" style="font-size:28px;"></i><br>
                        No theft or breach incidents recorded.
                    </p>
                <?php endif; ?>
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

        
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Smart Lock Activation History','class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Smart Lock Activation History','class' => 'mt-4']); ?>
            <div style="max-height:280px;overflow-y:auto;">
                <?php $__empty_1 = true; $__currentLoopData = $lockHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cmd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="lock-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong style="font-size:0.85rem;"><?php echo e($cmd->bicycle->name ?? 'Bicycle #' . $cmd->bicycleId); ?></strong>
                                <span class="command <?php echo e($cmd->command); ?> ms-2"><?php echo e($cmd->command); ?></span>
                            </div>
                            <span class="text-muted" style="font-size:0.7rem;"><?php echo e($cmd->created_at->format('M d, g:i A')); ?></span>
                        </div>
                        <div class="meta mt-1">
                            <?php if($cmd->issuer): ?>
                                Issued by: <?php echo e($cmd->issuer->name); ?>

                            <?php else: ?>
                                System / Auto
                            <?php endif; ?>
                            <?php if($cmd->status): ?>
                                • Status: <?php echo e(ucfirst($cmd->status)); ?>

                            <?php endif; ?>
                            <?php if($cmd->executedAt): ?>
                                • Executed: <?php echo e($cmd->executedAt->format('M d, g:i A')); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted text-center mb-0 py-3">
                        <i class="bi bi-lock" style="font-size:28px;"></i><br>
                        No lock/unlock commands recorded.
                    </p>
                <?php endif; ?>
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
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
(function () {
    var config = <?php echo json_encode($config); ?>;
    var initialThreshold = <?php echo json_encode((float) $warningThreshold); ?>;

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

    // Convert center + radius (meters) to a GeoJSON circle polygon
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

        // Warning ring (just inside the boundary)
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

    centerMarker.on('dragend', function () {
        center = centerMarker.getLngLat().toArray();
        renderCircle();
        updateReadouts();
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

        var url = <?php echo json_encode(route('admin.geofence.update')); ?>;
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
            saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>Save Geofence';
        });
    });

    map.on('load', function () {
        renderCircle();
        updateReadouts();
    });
})();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\geofence.blade.php ENDPATH**/ ?>