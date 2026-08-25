

<?php $__env->startSection('title', 'Bicycle Details'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Bicycle Details</h1>
    <p>Serial: <?php echo e($bicycle->serialNumber); ?></p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
    <a href="<?php echo e(route('admin.bicycles.index')); ?>" class="btn-admin btn-admin--secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Bicycles
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Overview -->
    <div class="col-lg-5">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <div class="text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                    style="width:90px;height:90px;background:linear-gradient(135deg,var(--brand),var(--brand-strong));color:#fff;font-size:2.4rem;">
                    <i class="bi bi-bicycle"></i>
                </div>
                <h5 class="mb-1"><?php echo e($bicycle->name); ?></h5>
                <p class="text-muted mb-3"><?php echo e($bicycle->model ?? 'Standard Model'); ?></p>
                <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                    <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => match($bicycle->status) { 'available' => 'success', 'rented' => 'info', 'maintenance' => 'warning', default => 'neutral' },'label' => ucfirst($bicycle->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($bicycle->status) { 'available' => 'success', 'rented' => 'info', 'maintenance' => 'warning', default => 'neutral' }),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($bicycle->status))]); ?>
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
                    <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => $bicycle->lockStatus === 'locked' ? 'danger' : 'success','label' => $bicycle->lockStatus === 'locked' ? 'Locked' : 'Unlocked']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bicycle->lockStatus === 'locked' ? 'danger' : 'success'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bicycle->lockStatus === 'locked' ? 'Locked' : 'Unlocked')]); ?>
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
                    <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => $bicycle->batteryLevel <= 20 ? 'danger' : 'success','label' => $bicycle->batteryLevel . '%']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bicycle->batteryLevel <= 20 ? 'danger' : 'success'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bicycle->batteryLevel . '%')]); ?>
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
                </div>
                <div class="d-flex justify-content-center gap-4">
                    <div class="text-center"><strong class="d-block"><?php echo e($bicycle->totalRentals); ?></strong><small class="text-muted">Rentals</small></div>
                    <div class="text-center"><strong class="d-block"><?php echo e($bicycle->totalDistance); ?> km</strong><small class="text-muted">Distance</small></div>
                    <div class="text-center"><strong class="d-block">₱<?php echo e(number_format($bicycle->hourlyRate, 2)); ?></strong><small class="text-muted">/hr</small></div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Remote Lock Control']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Remote Lock Control']); ?>
            <p class="text-muted" style="font-size:0.85rem;">Send a command to the ESP32 smart lock via the device's pending command queue.</p>
            <div class="d-flex gap-2">
                <form action="<?php echo e(route('admin.bicycles.lock', $bicycle->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="lock">
                    <button type="submit" class="btn-admin btn-admin--danger" <?php echo e($bicycle->lockStatus === 'locked' ? 'disabled' : ''); ?>>
                        <i class="bi bi-lock-fill me-1"></i>Lock
                    </button>
                </form>
                <form action="<?php echo e(route('admin.bicycles.lock', $bicycle->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="unlock">
                    <button type="submit" class="btn-admin btn-admin--secondary" <?php echo e($bicycle->lockStatus === 'unlocked' ? 'disabled' : ''); ?>>
                        <i class="bi bi-unlock-fill me-1"></i>Unlock
                    </button>
                </form>
            </div>
            <small class="text-muted d-block mt-2">
                Last action: <?php echo e($bicycle->lastLockAction ? $bicycle->lastLockAction->diffForHumans() : 'Never'); ?>

                <?php if($bicycle->lockActionBy): ?> by #<?php echo e($bicycle->lockActionBy); ?> <?php endif; ?>
            </small>
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

    <!-- Telemetry -->
    <div class="col-lg-7">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Device Telemetry']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Device Telemetry']); ?>
            <?php $t = $bicycle->latestTelemetry; ?>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded bg-light">
                        <small class="text-muted d-block">Battery</small>
                        <strong class="fs-5"><?php echo e($t?->battery['level'] ?? $bicycle->batteryLevel ?? '—'); ?>%</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded bg-light">
                        <small class="text-muted d-block">Lock</small>
                        <strong class="fs-5"><?php echo e(ucfirst($t?->lockStatus ?? $bicycle->lockStatus)); ?></strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded bg-light">
                        <small class="text-muted d-block">Uptime</small>
                        <strong class="fs-5"><?php echo e($t?->uptime ? number_format($t->uptime / 3600, 1) . 'h' : '—'); ?></strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded bg-light">
                        <small class="text-muted d-block">Firmware</small>
                        <strong class="fs-6"><?php echo e($t?->deviceVersion ?? '—'); ?></strong>
                    </div>
                </div>
            </div>
            <hr>
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><th class="text-muted" style="width:40%">Last Heartbeat</th><td><?php echo e($bicycle->lastHeartbeat ? $bicycle->lastHeartbeat->diffForHumans() : '—'); ?></td></tr>
                    <tr><th class="text-muted">Last GPS Update</th><td><?php echo e($bicycle->lastGpsUpdate ? $bicycle->lastGpsUpdate->diffForHumans() : '—'); ?></td></tr>
                    <tr><th class="text-muted">Current Location</th>
                        <td>
                            <?php if($bicycle->currentLat && $bicycle->currentLng): ?>
                                <?php echo e($bicycle->currentLat); ?>, <?php echo e($bicycle->currentLng); ?>

                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><th class="text-muted">Current Rider</th><td><?php echo e($bicycle->currentRiderUser->name ?? ($bicycle->currentRider ?? '—')); ?></td></tr>
                    <tr><th class="text-muted">Condition</th><td><?php echo e(ucfirst($bicycle->condition)); ?></td></tr>
                </tbody>
            </table>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Live Position','flush' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Live Position','flush' => true]); ?>
            <div id="bicycleDetailMap" style="height:340px;width:100%;"></div>
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
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('bicycleDetailMap');
        if (!el || typeof maplibregl === 'undefined') return;

        const center = {
            lat: parseFloat(<?php echo e($bicycle->currentLat ?? '7.0990'); ?>),
            lng: parseFloat(<?php echo e($bicycle->currentLng ?? '125.6470'); ?>)
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
                '<strong><?php echo e($bicycle->name); ?></strong><br>#<?php echo e($bicycle->serialNumber); ?><br><?php echo e(ucfirst($bicycle->status)); ?>'
            ))
            .addTo(map);
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\bicycles\show.blade.php ENDPATH**/ ?>