

<?php $__env->startSection('title', 'Theft Alerts'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    #theftMap {
        width: 100%;
        height: 380px;
        border-radius: 14px;
        overflow: hidden;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Theft Alerts</h1>
    <p>Monitor boundary breaches and potential theft incidents</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-4" data-theft-kpi="openBreaches">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Open Breaches','value' => ''.e($openBreachCount).'','icon' => 'bi-shield-exclamation','color' => 'var(--danger)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Open Breaches','value' => ''.e($openBreachCount).'','icon' => 'bi-shield-exclamation','color' => 'var(--danger)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e)): ?>
<?php $attributes = $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e; ?>
<?php unset($__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e)): ?>
<?php $component = $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e; ?>
<?php unset($__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e); ?>
<?php endif; ?>
    </div>
    <div class="col-md-4" data-theft-kpi="unacknowledged">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Unacknowledged Alerts','value' => ''.e($alerts->where('acknowledged', false)->count()).'','icon' => 'bi-bell','color' => 'var(--warning)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Unacknowledged Alerts','value' => ''.e($alerts->where('acknowledged', false)->count()).'','icon' => 'bi-bell','color' => 'var(--warning)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e)): ?>
<?php $attributes = $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e; ?>
<?php unset($__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e)): ?>
<?php $component = $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e; ?>
<?php unset($__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e); ?>
<?php endif; ?>
    </div>
    <div class="col-md-4" data-theft-kpi="atRisk">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'At-Risk Bicycles','value' => ''.e($bicycles->count()).'','icon' => 'bi-bicycle','color' => 'var(--brand)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'At-Risk Bicycles','value' => ''.e($bicycles->count()).'','icon' => 'bi-bicycle','color' => 'var(--brand)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e)): ?>
<?php $attributes = $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e; ?>
<?php unset($__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e)): ?>
<?php $component = $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e; ?>
<?php unset($__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e); ?>
<?php endif; ?>
    </div>
</div>


<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Live GeoLibre 3D Map','flush' => true,'bodyClass' => 'p-0 position-relative']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Live GeoLibre 3D Map','flush' => true,'bodyClass' => 'p-0 position-relative']); ?>
     <?php $__env->slot('tools', null, []); ?> 
        <small class="text-muted me-3"><?php echo e(count($mapBicycles ?? [])); ?> bicycle(s)</small>
        <small class="text-muted">
            Geofence: <span id="theftGeofenceRadiusText"><?php echo e(number_format($geofence['radius'] ?? 0, 0)); ?>m</span>
            <span id="theftGeofenceAlertBadge"><?php if(($geofence['alertEnabled'] ?? false)): ?>
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
        <button class="btn-admin btn-admin--ghost btn-admin--sm" id="theftCenterMapBtn" title="Center on geofence">
            <i class="bi bi-crosshair"></i>
        </button>
        <button class="btn-admin btn-admin--ghost btn-admin--sm" id="theftRefreshMapBtn" title="Refresh positions">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
     <?php $__env->endSlot(); ?>
    <div id="theftMap"></div>
    <div class="map-legend">
        <div><span class="dot" style="background:#2ecc71;"></span>Inside Zone <span class="legend-count" data-count="safe">0</span></div>
        <div><span class="dot" style="background:#f39c12;"></span>Near Boundary <span class="legend-count" data-count="near">0</span></div>
        <div><span class="dot" style="background:#e74c3c;"></span>Outside Zone <span class="legend-count" data-count="outside">0</span></div>
        <div><span class="dot" style="background:#27ae60;"></span>Geofence Boundary</div>
        <div><span class="dot" style="background:#8e44ad;"></span>&#9888; Has theft alert</div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Theft Alert Log','flush' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Theft Alert Log','flush' => true]); ?>
     <?php $__env->slot('tools', null, []); ?> 
        <span id="theftUnreadBadge"><?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'danger','label' => $alerts->where('acknowledged', false)->count() . ' unread']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($alerts->where('acknowledged', false)->count() . ' unread')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?></span>
     <?php $__env->endSlot(); ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $loc = is_array($alert->gpsLocation) ? $alert->gpsLocation : [];
                        $lat = $loc['lat'] ?? null;
                        $lng = $loc['lng'] ?? null;
                    ?>
                    <tr>
                        <td data-label="Bicycle" class="cell-title">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-bicycle me-2 text-muted"></i>
                                <?php echo e($alert->bicycle->name ?? 'Unknown'); ?>

                                <small class="text-muted ms-1">#<?php echo e($alert->bicycleId); ?></small>
                            </div>
                        </td>
                        <td data-label="Rider">
                            <?php $riderName = $alert->bicycle?->currentRiderUser?->name ?? null; ?>
                            <?php if($riderName): ?>
                                <small><?php echo e($riderName); ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Location">
                            <?php if($lat && $lng): ?>
                                <small><?php echo e(number_format($lat, 5)); ?>, <?php echo e(number_format($lng, 5)); ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Distance from Boundary">
                            <?php $dist = $alert->breachDistance ?? $alert->distanceFromBoundary; ?>
                            <?php if($dist !== null): ?>
                                <span class="fw-semibold text-danger">
                                    <?php echo e(number_format($dist, 2)); ?>m outside
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Status">
                            <?php if($alert->status === 'open'): ?>
                                <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'danger','label' => 'Open']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','label' => 'Open']); ?>
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
                            <?php elseif($alert->status === 'returned'): ?>
                                <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Returned']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Returned']); ?>
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
                            <?php elseif($alert->status === 'resolved'): ?>
                                <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Resolved']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Resolved']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'secondary','label' => ucfirst($alert->status ?? 'Unknown')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'secondary','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($alert->status ?? 'Unknown'))]); ?>
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
                        </td>
                        <td data-label="Timestamp">
                            <small><?php echo e($alert->created_at->format('M d, Y h:i A')); ?></small><br>
                            <small class="text-muted"><?php echo e($alert->created_at->diffForHumans()); ?></small>
                        </td>
                        <td data-label="Acknowledged">
                            <?php if($alert->acknowledged): ?>
                                <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Acknowledged']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Acknowledged']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'warning','label' => 'Pending']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','label' => 'Pending']); ?>
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
                        </td>
                        <td data-label="Actions">
                            <?php if(!$alert->acknowledged): ?>
                                <form action="<?php echo e(route('admin.theft-alerts.acknowledge', $alert->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn-admin btn-admin--soft btn-admin--sm" title="Acknowledge" data-confirm="Mark this theft alert as acknowledged?">
                                        <i class="bi bi-check-lg me-1"></i>Acknowledge
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted"><small>Handled</small></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-shield-check','title' => 'No theft alerts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-shield-check','title' => 'No theft alerts']); ?>
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
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if(method_exists($alerts, 'links')): ?>
        <div class="admin-table-foot">
            <?php echo e($alerts->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/geolibre-map.js')); ?>"></script>
<script>
(function () {
    var geofence = <?php echo json_encode($geofence); ?>;
    var bicycles = <?php echo $mapBicycles->map(fn($b) => [
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
    ])->values()->toJson(); ?>;
    var alertBicycles = <?php echo json_encode($openTheftAlerts); ?>;
    var liveUrl = <?php echo json_encode(route('admin.theft-alerts.live')); ?>;
    var ackUrl = <?php echo json_encode(route('admin.theft-alerts.acknowledge', '__ID__')); ?>;
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\theft.blade.php ENDPATH**/ ?>