

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
    <div class="col-md-4">
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
    <div class="col-md-4">
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
    <div class="col-md-4">
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Breach Locations','flush' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Breach Locations','flush' => true]); ?>
    <div id="theftMap"></div>
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
        <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
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
<?php endif; ?>
     <?php $__env->endSlot(); ?>
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

                            </div>
                        </td>
                        <td data-label="Location">
                            <?php if($lat && $lng): ?>
                                <small><?php echo e(number_format($lat, 5)); ?>, <?php echo e(number_format($lng, 5)); ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Distance from Boundary">
                            <?php if($alert->breachDistance !== null): ?>
                                <span class="fw-semibold text-danger">
                                    <?php echo e(number_format($alert->breachDistance, 2)); ?>m outside
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
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
                        <td colspan="6" class="text-center">
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
<?php
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
?>
<script>
(function () {
    var points = <?php echo $breachPoints->toJson(); ?>;
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\theft.blade.php ENDPATH**/ ?>