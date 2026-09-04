

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
    .shape-picker {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .shape-btn {
        flex: 1 1 31%;
        min-width: 120px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 10px;
        border-radius: 10px;
        background: var(--surface, #fff);
        border: 1.5px solid var(--border, rgba(0,0,0,0.1));
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-2, #444);
        transition: all .15s;
        text-align: center;
    }
    .shape-btn:hover { border-color: var(--primary, #2563eb); color: var(--primary); }
    .shape-btn.active {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(37,99,235,0.35);
    }
    .shape-btn .shape-mini {
        width: 18px;
        height: 18px;
        display: inline-block;
        border: 2px solid currentColor;
        flex-shrink: 0;
    }
    .shape-btn.active .shape-mini { color: #fff; }
    .shape-mini.circle { border-radius: 50%; }
    .shape-mini.oval-h { border-radius: 50%; width: 20px; height: 14px; }
    .shape-mini.oval-v { border-radius: 50%; width: 14px; height: 20px; }
    .shape-mini.rectangle { border-radius: 2px; }
    .shape-mini.polygon {
        width: 16px; height: 18px;
        clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
        background: currentColor;
        border: none;
    }
    .control-group {
        padding: 14px;
        border: 1px solid var(--border, rgba(0,0,0,0.1));
        border-radius: 12px;
        margin-bottom: 16px;
        background: var(--surface-soft, rgba(0,0,0,0.02));
    }
    .control-group-title {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--muted, #888);
        font-weight: 700;
        margin-bottom: 10px;
    }
    .polygon-hint {
        font-size: 0.78rem;
        color: var(--muted, #888);
        margin-top: 8px;
    }
    .vertex-count {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--primary, #2563eb);
    }
    .draw-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }
    .draw-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 9px;
        border: 1.5px solid var(--border, rgba(0,0,0,0.1));
        background: var(--surface, #fff);
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-2, #444);
        cursor: pointer;
        transition: all .15s;
    }
    .draw-btn:hover:not(:disabled) { border-color: var(--primary, #2563eb); color: var(--primary); }
    .draw-btn:disabled { opacity: .45; cursor: not-allowed; }
    .draw-btn.primary {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        border-color: transparent;
    }
    .draw-btn.primary:hover:not(:disabled) { color: #fff; box-shadow: 0 4px 14px rgba(37,99,235,0.35); }
    .draw-btn.danger { color: #e74c3c; }
    .draw-btn.danger:hover:not(:disabled) { border-color: #e74c3c; color: #e74c3c; }
    .draw-btn.warning { color: #f39c12; }
    .draw-btn.warning:hover:not(:disabled) { border-color: #f39c12; color: #f39c12; }
    .draw-status {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 0.78rem;
        color: var(--muted, #888);
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 8px;
        background: var(--surface, rgba(0,0,0,0.03));
        border: 1px dashed var(--border, rgba(0,0,0,0.1));
    }
    .draw-status .pulse {
        width: 9px; height: 9px; border-radius: 50%;
        background: #27ae60;
        animation: pulse-dot 1.4s infinite;
        flex-shrink: 0;
    }
    .draw-status.counting .pulse { background: #7c3aed; }
    .draw-status.closed .pulse { background: #27ae60; animation: none; }
    @keyframes pulse-dot {
        0% { box-shadow: 0 0 0 0 rgba(39,174,96,.5); }
        70% { box-shadow: 0 0 0 6px rgba(39,174,96,0); }
        100% { box-shadow: 0 0 0 0 rgba(39,174,96,0); }
    }
    .vertex-chip {
        color: #7c3aed;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .vertex-marker {
        width: 24px; height: 24px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        background: #7c3aed;
        border: 2px solid #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.35);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: grab;
        transition: transform .12s ease, background .12s ease;
    }
    .vertex-marker.selected {
        background: #e74c3c;
        transform: rotate(-45deg) scale(1.25);
        cursor: pointer;
    }
    .vertex-marker span {
        transform: rotate(45deg);
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
    <p>Configure the riding boundary — choose a shape, then click the map or drag the marker to reposition</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
    <span id="saveStatus" class="badge-admin badge-admin--success me-2" style="display:none;"></span>
    <button class="btn-admin btn-admin--secondary me-2" id="resetShapeBtn" title="Reset the current shape to a default sized shape">
        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Shape
    </button>
    <button class="btn-admin btn-admin--secondary me-2 d-none" id="undoPointBtn" title="Remove the last drawn polygon point">
        <i class="bi bi-arrow-return-left me-1"></i>Undo Point
    </button>
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
                    <div><span class="line" style="background:#d5f5e3; border:1px solid #27ae60;"></span>Safe Zone (inside)</div>
                    <div><span class="line" style="background:#ef5350;"></span>Warning Band (near boundary)</div>
                    <div><span class="line" style="background:#2c3e50;"></span>Center (drag or click map to move)</div>
                    <div id="polygonHint" style="display:none; color:#7c3aed;"><i class="bi bi-cursor-fill me-1"></i>Polygon mode: click map to add points</div>
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
                <div class="control-group">
                    <div class="control-group-title"><i class="bi bi-shapes me-1"></i>Geofence Shape</div>
                    <div class="shape-picker">
                        <button type="button" class="shape-btn active" data-shape="circle">
                            <span class="shape-mini circle"></span> Circle
                        </button>
                        <button type="button" class="shape-btn" data-shape="oval_h">
                            <span class="shape-mini oval-h"></span> Oval (H)
                        </button>
                        <button type="button" class="shape-btn" data-shape="oval_v">
                            <span class="shape-mini oval-v"></span> Oval (V)
                        </button>
                        <button type="button" class="shape-btn" data-shape="rectangle">
                            <span class="shape-mini rectangle"></span> Rectangle
                        </button>
                        <button type="button" class="shape-btn" data-shape="polygon">
                            <span class="shape-mini polygon"></span> Polygon
                        </button>
                    </div>
                </div>

                
                <div class="control-group" id="circleControls">
                    <div class="control-group-title">Radius</div>
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

                
                <div class="control-group d-none" id="dimensionControls">
                    <div class="control-group-title" id="dimensionTitle">Dimensions</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Width (m)</label>
                            <input type="number" class="form-control form-control-sm" id="widthInput"
                                   min="10" max="5000" value="<?php echo e(number_format($config['width'] ?? $config['radius'], 0)); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Height (m)</label>
                            <input type="number" class="form-control form-control-sm" id="heightInput"
                                   min="10" max="5000" value="<?php echo e(number_format($config['height'] ?? $config['radius'], 0)); ?>">
                        </div>
                        <div class="col-12 d-none" id="rotationWrap">
                            <label class="form-label">Rotation / Angle (°)</label>
                            <input type="range" class="radius-slider" id="rotationSlider" min="0" max="360" step="1"
                                   value="<?php echo e(number_format($config['rotation'] ?? 0, 0)); ?>">
                            <div class="d-flex justify-content-between">
                                <span id="rotationValue" class="text-muted" style="font-size:0.78rem;">0°</span>
                                <span class="text-muted" style="font-size:0.78rem;">Clockwise from north</span>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="control-group d-none" id="polygonControls">
                    <div class="control-group-title"><i class="bi bi-pencil-fill me-1"></i>Draw Boundary</div>
                    <p class="polygon-hint mb-1">
                        Click on the map to add points and trace your custom riding perimeter.
                        Finish the boundary to enclose the area, then drag vertices or double-click an
                        edge to fine-tune.
                    </p>

                    <div class="draw-status counting" id="drawStatus">
                        <span class="pulse"></span>
                        <span id="drawStatusText">Click the map to add boundary points…</span>
                    </div>

                    <div class="draw-toolbar">
                        <button type="button" class="draw-btn primary" id="finishBoundaryBtn" disabled>
                            <i class="bi bi-patch-check me-1"></i>Finish / Close Boundary
                        </button>
                        <button type="button" class="draw-btn" id="undoPointBtn2" disabled>
                            <i class="bi bi-arrow-return-left me-1"></i>Undo Point
                        </button>
                        <button type="button" class="draw-btn warning" id="removePointBtn" disabled title="Remove the currently selected vertex">
                            <i class="bi bi-x-lg me-1"></i>Remove Selected Point
                        </button>
                        <button type="button" class="draw-btn danger" id="clearBoundaryBtn" disabled>
                            <i class="bi bi-trash me-1"></i>Clear Boundary
                        </button>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <span class="vertex-count"><span id="vertexCount">0</span> boundary point(s)</span>
                        <span class="vertex-chip"><i class="bi bi-dot me-1"></i>drag a point to move it</span>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-group-title">Warning & Transport Alerts</div>
                    <div class="mb-3">
                        <label class="form-label d-flex justify-content-between">
                            <span>Warning Threshold (inside boundary)</span>
                            <strong id="thresholdValue"><?php echo e(number_format($warningThreshold, 0)); ?> m</strong>
                        </label>
                        <input type="range" class="radius-slider" id="thresholdSlider" min="10" max="500" step="5"
                               value="<?php echo e($warningThreshold); ?>">
                        <div class="form-text">Bicycles within this distance of the boundary are flagged as approaching.</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="alertToggle"
                               <?php echo e($config['alertEnabled'] ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="alertToggle">Geofence alerts enabled (breach notifications & theft detection)</label>
                    </div>
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
                               value="<?php echo e(number_format($config['centerLat'], 6)); ?>" readonly>
                    </div>
                    <div class="input-group input-group-sm mt-2">
                        <span class="input-group-text">Lng</span>
                        <input type="text" class="form-control" id="centerLngInput"
                               value="<?php echo e(number_format($config['centerLng'], 6)); ?>" readonly>
                    </div>
                    <div class="form-text">Updated automatically when you click the map or drag the marker.</div>
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

    var M_PER_DEG_LAT = 111320;

    // ---- State ----
    var center = [config.centerLng, config.centerLat];
    var shapeType = config.shapeType || 'circle';
    var radius = config.radius || 500;
    var width = config.width || radius || 800;
    var height = config.height || radius || 500;
    var rotation = config.rotation || 0;
    var points = (config.points && config.points.length) ? config.points.slice() : null; // [{lat,lng}]
    var polygonClosed = (shapeType === 'polygon' && points && points.length >= 3);
    var selectedVertex = null; // index of currently selected polygon vertex
    var warningThreshold = initialThreshold;

    // ---- Draw Boundary toolbar refs ----
    var drawStatus = document.getElementById('drawStatus');
    var drawStatusText = document.getElementById('drawStatusText');
    var finishBoundaryBtn = document.getElementById('finishBoundaryBtn');
    var clearBoundaryBtn = document.getElementById('clearBoundaryBtn');
    var removePointBtn = document.getElementById('removePointBtn');
    var undoPointBtn2 = document.getElementById('undoPointBtn2');

    // ---- Local metre helpers (mirror the server) ----
    function centerLatRad() { return center[1] * Math.PI / 180; }
    function metersToLatLng(x, y) {
        var latRad = centerLatRad();
        return {
            lng: center[0] + (x / (M_PER_DEG_LAT * Math.cos(latRad))),
            lat: center[1] + (y / M_PER_DEG_LAT)
        };
    }

    // ---- Shape -> boundary vertices (list of [lng, lat]) ----
    function boundaryVertices() {
        switch (shapeType) {
            case 'rectangle': {
                var a = width / 2, b = height / 2;
                var th = rotation * Math.PI / 180, cos = Math.cos(th), sin = Math.sin(th);
                var corners = [[a, b], [-a, b], [-a, -b], [a, -b]];
                return corners.map(function (c) {
                    var x = c[0] * cos - c[1] * sin;
                    var y = c[0] * sin + c[1] * cos;
                    var p = metersToLatLng(x, y);
                    return [p.lng, p.lat];
                });
            }
            case 'oval_h':
            case 'oval_v': {
                var a2 = Math.max(1, width / 2), b2 = Math.max(1, height / 2);
                return sampleEllipse(a2, b2);
            }
            case 'polygon':
                if (points && points.length) {
                    return points.map(function (p) { return [p.lng, p.lat]; });
                }
                return sampleCircle();
            case 'circle':
            default:
                return sampleCircle();
        }
    }

    function sampleCircle() {
        var coords = [];
        var steps = 120;
        for (var i = 0; i < steps; i++) {
            var rad = (i / steps) * 2 * Math.PI;
            coords.push(vert(Math.cos(rad) * radius, Math.sin(rad) * radius));
        }
        return coords;
    }
    function sampleEllipse(a, b) {
        var coords = [];
        var steps = 120;
        for (var i = 0; i < steps; i++) {
            var rad = (i / steps) * 2 * Math.PI;
            coords.push(vert(Math.cos(rad) * a, Math.sin(rad) * b));
        }
        return coords;
    }
    function vert(x, y) {
        var p = metersToLatLng(x, y);
        return [p.lng, p.lat];
    }

    function polygonFeature(verts) {
        var coords = verts.slice();
        if (coords.length) coords.push(coords[0]);
        return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [coords] } };
    }

    function polyLineFeature(verts) {
        return { type: 'Feature', geometry: { type: 'LineString', coordinates: verts.slice() } };
    }

    // ---- Map init ----
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

    var centerMarker = new maplibregl.Marker({ color: '#2c3e50', draggable: true })
        .setLngLat(center)
        .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML('<strong>Geofence Center</strong><br><small>Drag to reposition</small>'))
        .addTo(map);

    var vertexMarkers = [];

    // Warning-zone: a transparent band running along the inside of the boundary.
    // It is rendered as a donut (outer boundary with the inner "safe" ring cut out),
    // so only the region between the boundary line and the shrunk safe area shows
    // the red/orange warning fill while the centre stays translucent green.
    function safeRingVertices() {
        if (warningThreshold <= 0) return [];
        return shrinkRing(boundaryVertices(), Math.min(warningThreshold, Math.max(width, height, radius) * 0.5));
    }
    function shrinkRing(verts, threshold) {
        var dlng = threshold / (M_PER_DEG_LAT * Math.cos(centerLatRad()));
        var dlat = threshold / M_PER_DEG_LAT;
        return verts.map(function (v) {
            var dx = v[0] - center[0];
            var dy = v[1] - center[1];
            var pd = normalize(dx, dy);
            return [v[0] - pd.x * dlng, v[1] - pd.y * dlat];
        });
    }
    // Outer boundary (closed) with the inner safe ring reversed so it becomes a hole.
    function warningBandFeature() {
        var outer = boundaryVertices().slice();
        if (outer.length) outer.push(outer[0]);
        if (warningThreshold <= 0) {
            return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [outer] } };
        }
        var inner = safeRingVertices().slice().reverse();
        if (inner.length) inner.push(inner[0]);
        return {
            type: 'Feature',
            geometry: { type: 'Polygon', coordinates: [outer, inner] }
        };
    }
    function normalize(x, y) {
        var m = Math.sqrt(x * x + y * y) || 1;
        return { x: x / m, y: y / m };
    }

    function renderShape() {
        // A shape is "rendered filled" when it is inherently closed (circle / oval / rectangle)
        // or when a polygon has been finished (closed) with 3+ points.
        var canRenderFill = shapeType !== 'polygon' || polygonClosed;
        var data = polygonFeature(boundaryVertices());

        if (!map.getSource('geofence')) {
            map.addSource('geofence', { type: 'geojson', data: data });
        } else {
            map.getSource('geofence').setData(data);
        }
        // Order matters for correct z-stacking:
        //   geofence-fill (green) -> warning-band (red/orange) -> geofence-outline (on top).
        if (!map.getLayer('geofence-fill')) {
            map.addLayer({ id: 'geofence-fill', type: 'fill', source: 'geofence', paint: { 'fill-color': '#27ae60', 'fill-opacity': 0.18 } });
        }

        // Red/orange warning band inside the boundary (donut around the green safe core).
        var bandData = warningBandFeature();
        if (!map.getSource('warning-band')) {
            map.addSource('warning-band', { type: 'geojson', data: bandData });
        } else {
            map.getSource('warning-band').setData(bandData);
        }
        if (!map.getLayer('warning-band-fill')) {
            map.addLayer({ id: 'warning-band-fill', type: 'fill', source: 'warning-band', paint: { 'fill-color': '#ef5350', 'fill-opacity': 0.5 } });
        }
        // Dashed inner edge of the danger zone (where the safe area begins).
        var ringVerts = safeRingVertices();
        if (ringVerts.length) ringVerts.push(ringVerts[0]);
        var ringFeature = { type: 'Feature', geometry: { type: 'LineString', coordinates: ringVerts } };
        if (!map.getSource('safe-ring')) {
            map.addSource('safe-ring', { type: 'geojson', data: ringFeature });
        } else {
            map.getSource('safe-ring').setData(ringFeature);
        }
        if (!map.getLayer('safe-ring-line')) {
            map.addLayer({ id: 'safe-ring-line', type: 'line', source: 'safe-ring', paint: { 'line-color': '#e67e22', 'line-width': 2, 'line-opacity': 0.9, 'line-dasharray': [3, 2] } });
        }
        if (map.getLayer('safe-ring-line')) {
            map.setLayoutProperty('safe-ring-line', 'visibility', canRenderFill && warningThreshold > 0 ? 'visible' : 'none');
        }

        if (!map.getLayer('geofence-outline')) {
            map.addLayer({ id: 'geofence-outline', type: 'line', source: 'geofence', paint: { 'line-color': '#1e8449', 'line-width': 3, 'line-dasharray': [0, 2, 2, 2] } });
        }

        // For an open (still drawing) polygon we show a bold guide line instead of a solid fill.
        if (map.getLayer('geofence-outline')) {
            map.setPaintProperty('geofence-outline', 'line-color', canRenderFill ? '#1e8449' : '#7c3aed');
            map.setPaintProperty('geofence-outline', 'line-width', canRenderFill ? 3 : 4);
            map.setPaintProperty('geofence-outline', 'line-dasharray', canRenderFill ? [0, 2, 2, 2] : [2, 1]);
        }
        if (map.getLayer('geofence-fill')) {
            map.setLayoutProperty('geofence-fill', 'visibility', canRenderFill ? 'visible' : 'none');
        }

        // The band is only meaningful once the boundary is enclosed; hide it while drawing.
        if (map.getLayer('warning-band-fill')) {
            map.setLayoutProperty('warning-band-fill', 'visibility', canRenderFill && warningThreshold > 0 ? 'visible' : 'none');
        }

        // Open drawing guide: line through the points + closing dashed segment to completion.
        renderDrawGuide(canRenderFill);

        renderVertices();
        updateDrawUI();
    }

    function renderDrawGuide(closed) {
        if (shapeType !== 'polygon' || !points || points.length < 2) {
            if (map.getLayer('draw-guide')) map.setLayoutProperty('draw-guide', 'visibility', 'none');
            return;
        }
        var coords = points.map(function (p) { return [p.lng, p.lat]; });
        if (!closed) coords.push(coords[0]); // show the closing dashed edge while drawing
        if (!map.getSource('draw-guide')) {
            map.addSource('draw-guide', { type: 'geojson', data: polyLineFeature(coords) });
        } else {
            map.getSource('draw-guide').setData(polyLineFeature(coords));
        }
        if (!map.getLayer('draw-guide')) {
            map.addLayer({
                id: 'draw-guide',
                type: 'line',
                source: 'draw-guide',
                paint: {
                    'line-color': '#7c3aed',
                    'line-width': 2,
                    'line-dasharray': [2, 1],
                    'line-opacity': 0.7
                }
            });
        }
        map.setLayoutProperty('draw-guide', 'visibility', (closed || points.length < 3) ? 'none' : 'visible');
    }

    function vertexElement(idx, isSel) {
        var el = document.createElement('div');
        el.className = 'vertex-marker' + (isSel ? ' selected' : '');
        el.innerHTML = '<span>' + (idx + 1) + '</span>';
        el.title = 'Vertex ' + (idx + 1) + ' — click to select';
        el.addEventListener('click', function (e) { e.preventDefault(); });
        return el;
    }

    function renderVertices() {
        vertexMarkers.forEach(function (m) { m.remove(); });
        vertexMarkers = [];
        updateRemoveBtn();
        if (shapeType !== 'polygon' || !points) return;
        points.forEach(function (p, idx) {
            var isSel = (idx === selectedVertex);
            var m = new maplibregl.Marker({ element: vertexElement(idx, isSel), draggable: true })
                .setLngLat([p.lng, p.lat])
                .setPopup(new maplibregl.Popup({ offset: 20 }).setHTML('<strong>Vertex ' + (idx + 1) + '</strong><br><small>Drag to move · click to select</small>'))
                .addTo(map);
            m.getElement().addEventListener('click', function (e) {
                e.stopPropagation();
                selectedVertex = (selectedVertex === idx) ? null : idx;
                renderVertices();
            });
            m.on('dragend', function () {
                var pos = m.getLngLat();
                points[idx] = { lat: pos.lat, lng: pos.lng };
                renderShape();
            });
            vertexMarkers.push(m);
        });
    }

    function updateRemoveBtn() {
        if (removePointBtn) removePointBtn.disabled = !(shapeType === 'polygon' && points && points.length > 0 && selectedVertex !== null);
    }

    function updateDrawUI() {
        if (shapeType !== 'polygon') return;
        var count = points ? points.length : 0;
        document.getElementById('vertexCount').textContent = count;

        finishBoundaryBtn.disabled = count < 3 || polygonClosed;
        undoPointBtn2.disabled = count === 0 || polygonClosed;
        clearBoundaryBtn.disabled = count === 0;
        updateRemoveBtn();

        drawStatus.classList.remove('counting', 'closed');
        if (count === 0) {
            drawStatus.classList.add('counting');
            drawStatusText.textContent = 'Click the map to add boundary points…';
        } else if (!polygonClosed) {
            drawStatus.classList.add('counting');
            drawStatusText.textContent = 'Placed ' + count + ' point(s). Click "Finish / Close Boundary" (or add ' + Math.max(0, 3 - count) + ' more) to enclose the area.';
        } else {
            drawStatus.classList.add('closed');
            drawStatusText.textContent = 'Boundary closed with ' + count + ' points. Drag points or double-click an edge to edit.';
        }
    }

    // ---- Shape UI wiring ----
    function toggleMapInteractions() {
        if (typeof map !== 'undefined' && map.doubleClickZoom) {
            if (shapeType === 'polygon') map.doubleClickZoom.disable();
            else map.doubleClickZoom.enable();
        }
    }
    function showShapePanels() {
        document.getElementById('circleControls').classList.toggle('d-none', shapeType !== 'circle');
        document.getElementById('dimensionControls').classList.toggle('d-none', !(shapeType === 'oval_h' || shapeType === 'oval_v' || shapeType === 'rectangle'));
        document.getElementById('polygonControls').classList.toggle('d-none', shapeType !== 'polygon');
        document.getElementById('rotationWrap').classList.toggle('d-none', shapeType !== 'rectangle');
        document.getElementById('polygonHint').style.display = shapeType === 'polygon' ? 'block' : 'none';
        document.getElementById('undoPointBtn').classList.toggle('d-none', shapeType !== 'polygon');

        var title = 'Dimensions';
        if (shapeType === 'oval_h') title = 'Horizontal Oval (Width × Height)';
        else if (shapeType === 'oval_v') title = 'Vertical Oval (Width × Height)';
        else if (shapeType === 'rectangle') title = 'Rectangle (Width × Height × Rotation)';
        document.getElementById('dimensionTitle').textContent = title;

        document.querySelectorAll('.shape-btn').forEach(function (b) {
            b.classList.toggle('active', b.getAttribute('data-shape') === shapeType);
        });

        toggleMapInteractions();
        if (shapeType === 'polygon') {
            updateDrawUI();
        }
    }

    document.querySelectorAll('.shape-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var newShape = btn.getAttribute('data-shape');
            if (newShape === shapeType) return;
            shapeType = newShape;
            if (shapeType === 'polygon') {
                // Start a fresh manual draw if we don't already have a saved boundary.
                if (!points) {
                    points = [];
                    polygonClosed = false;
                    selectedVertex = null;
                } else {
                    polygonClosed = points.length >= 3;
                }
            } else {
                points = null;
                polygonClosed = false;
                selectedVertex = null;
            }
            showShapePanels();
            renderShape();
            updateReadouts();
        });
    });

    function selectShapeButtons() {
        showShapePanels();
    }

    // ---- Controls ----
    var radiusSlider = document.getElementById('radiusSlider');
    var thresholdSlider = document.getElementById('thresholdSlider');
    var widthInput = document.getElementById('widthInput');
    var heightInput = document.getElementById('heightInput');
    var rotationSlider = document.getElementById('rotationSlider');
    var rotationValue = document.getElementById('rotationValue');
    var radiusValue = document.getElementById('radiusValue');

    radiusSlider.addEventListener('input', function () {
        radius = parseInt(this.value, 10);
        radiusValue.textContent = numberFormat(radius) + ' m';
        renderShape();
    });

    widthInput.addEventListener('input', function () {
        width = Math.max(10, parseInt(this.value, 10) || 10);
        renderShape();
    });
    heightInput.addEventListener('input', function () {
        height = Math.max(10, parseInt(this.value, 10) || 10);
        renderShape();
    });
    rotationSlider.addEventListener('input', function () {
        rotation = parseInt(this.value, 10);
        rotationValue.textContent = rotation + '°';
        renderShape();
    });

    thresholdSlider.addEventListener('input', function () {
        warningThreshold = parseInt(this.value, 10);
        thresholdValue.textContent = numberFormat(warningThreshold) + ' m';
        renderShape();
    });

    function numberFormat(n) {
        return n.toLocaleString('en-US');
    }

    // ---- Center movement ----
    centerMarker.on('dragend', function () {
        var pos = centerMarker.getLngLat();
        center = [pos.lng, pos.lat];
        renderShape();
        updateReadouts();
    });

    // Drawing / editing interactions on the map.
    map.on('click', function (e) {
        if (shapeType === 'polygon') {
            // While the boundary is open (drawing), each click adds a point.
            // Once closed, clicks are used to select/move points instead.
            if (!polygonClosed) {
                if (!points) points = [];
                points.push({ lat: e.lngLat.lat, lng: e.lngLat.lng });
                if (points.length >= 3) selectedVertex = null;
                renderShape();
            }
        } else {
            center = [e.lngLat.lng, e.lngLat.lat];
            centerMarker.setLngLat([e.lngLat.lng, e.lngLat.lat]);
            renderShape();
            updateReadouts();
        }
    });

    // Double-click a closed boundary edge to insert a new point there.
    map.on('dblclick', function (e) {
        if (shapeType === 'polygon' && polygonClosed && points && points.length >= 3) {
            var idx = nearestEdgeIndex([e.lngLat.lng, e.lngLat.lat]);
            if (idx >= 0) {
                points.splice(idx, 0, { lat: e.lngLat.lat, lng: e.lngLat.lng });
                polygonClosed = points.length >= 3;
                renderShape();
            }
        }
    });

    // Find the boundary edge index whose midpoint is nearest a click, for point insertion.
    function nearestEdgeIndex(mapLngLat) {
        var best = -1, bestDist = Infinity;
        for (var i = 0; i < points.length; i++) {
            var a = points[i];
            var b = points[(i + 1) % points.length];
            var midLat = (a.lat + b.lat) / 2;
            var midLng = (a.lng + b.lng) / 2;
            var dy = (midLat - mapLngLat[1]) * M_PER_DEG_LAT;
            var dx = (midLng - mapLngLat[0]) * (M_PER_DEG_LAT * Math.cos(centerLatRad()));
            var d = Math.sqrt(dx * dx + dy * dy);
            if (d < bestDist && d < Math.max(width, height, radius, 200)) {
                bestDist = d;
                best = i + 1;
            }
        }
        return best;
    }

    function removeSelectedPoint() {
        if (shapeType === 'polygon' && points && selectedVertex !== null && points.length > 0) {
            points.splice(selectedVertex, 1);
            if (points.length < 3) polygonClosed = false;
            selectedVertex = null;
            renderShape();
        }
    }
    function undoPoint() {
        if (shapeType === 'polygon' && points && points.length > 0 && !polygonClosed) {
            points.pop();
            selectedVertex = null;
            renderShape();
        }
    }
    function clearBoundary() {
        if (shapeType === 'polygon') {
            points = [];
            polygonClosed = false;
            selectedVertex = null;
            renderShape();
        }
    }

    // Draw Boundary toolbar actions.
    if (finishBoundaryBtn) finishBoundaryBtn.addEventListener('click', function () {
        if (points && points.length >= 3) {
            polygonClosed = true;
            selectedVertex = null;
            renderShape();
        }
    });
    if (undoPointBtn2) undoPointBtn2.addEventListener('click', undoPoint);
    if (clearBoundaryBtn) clearBoundaryBtn.addEventListener('click', clearBoundary);
    if (removePointBtn) removePointBtn.addEventListener('click', removeSelectedPoint);

    // Header actions.
    document.getElementById('undoPointBtn').addEventListener('click', undoPoint);

    document.getElementById('resetShapeBtn').addEventListener('click', function () {
        var base = shapeType;
        if (base === 'polygon') {
            clearBoundary();
        } else if (base === 'circle') {
            radius = 800;
            radiusSlider.value = 800;
            radiusValue.textContent = '800 m';
            renderShape();
        } else if (base === 'rectangle') {
            width = 1600; height = 900; rotation = 0;
            widthInput.value = 1600; heightInput.value = 900;
            rotationSlider.value = 0; rotationValue.textContent = '0°';
            renderShape();
        } else { // ovals
            width = 1600; height = 900;
            widthInput.value = 1600; heightInput.value = 900;
            renderShape();
        }
    });

    function updateReadouts() {
        document.getElementById('centerLatInput').value = center[1].toFixed(6);
        document.getElementById('centerLngInput').value = center[0].toFixed(6);
        var shapeLabel = document.getElementById('centerReadout');
        var label = 'Center: ' + center[1].toFixed(6) + ', ' + center[0].toFixed(6);
        if (shapeType === 'circle') label += ' • Radius: ' + numberFormat(radius) + ' m';
        else if (shapeType === 'rectangle') label += ' • ' + numberFormat(width) + '×' + numberFormat(height) + ' m • ' + rotation + '°';
        else if (shapeType === 'oval_h' || shapeType === 'oval_v') label += ' • ' + numberFormat(width) + '×' + numberFormat(height) + ' m (ov)';
        else if (shapeType === 'polygon' && points) label += ' • Polygon: ' + points.length + ' pts';
        shapeLabel.textContent = label;
    }

    // ---- Search ----
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
            center = [lng, lat];
            centerMarker.setLngLat([lng, lat]);
            renderShape();
            updateReadouts();
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
        if (e.key === 'Enter') { e.preventDefault(); performSearch(); }
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

    // ---- Save ----
    var saveBtn = document.getElementById('saveGeofenceBtn');
    var saveStatus = document.getElementById('saveStatus');

    saveBtn.addEventListener('click', function () {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        var payload = {
            centerLat: center[1],
            centerLng: center[0],
            shapeType: shapeType,
            warningThreshold: warningThreshold,
            alertEnabled: document.getElementById('alertToggle').checked
        };

        if (shapeType === 'circle') {
            payload.radius = radius;
        } else if (shapeType === 'rectangle') {
            payload.radius = null;
            payload.width = width;
            payload.height = height;
            payload.rotation = rotation;
        } else if (shapeType === 'oval_h' || shapeType === 'oval_v') {
            payload.radius = null;
            payload.width = width;
            payload.height = height;
        } else if (shapeType === 'polygon') {
            // A customer-drawn boundary must be finished (closed) with at least 3 points to save.
            if (!points || points.length < 3 || !polygonClosed) {
                showSaveError('Draw and finish your boundary (3+ points) before saving.');
                return;
            }
            payload.radius = null;
            payload.points = points;
        }

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
            showSaveError(e.message);
        })
        .finally(function () {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>Save Geofence';
        });
    });

    function showSaveError(msg) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>Save Geofence';
        saveStatus.className = 'badge-admin badge-admin--danger';
        saveStatus.innerHTML = '<i class="bi bi-x-circle me-1"></i>' + msg;
        saveStatus.style.display = 'inline-flex';
    }

    map.on('load', function () {
        selectShapeButtons();
        renderShape();
        updateReadouts();
    });
})();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\geofence.blade.php ENDPATH**/ ?>