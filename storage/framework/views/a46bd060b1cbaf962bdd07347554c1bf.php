

<?php $__env->startSection('title', 'System Settings'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>System Settings</h1>
    <p>Configure system parameters</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form action="<?php echo e(route('admin.settings.update')); ?>" method="POST" id="settingsForm" class="admin-form">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <!-- Company Section -->
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
         <?php $__env->slot('title', null, []); ?> <i class="bi bi-building me-2"></i>Company <?php $__env->endSlot(); ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control" name="companyName"
                    value="<?php echo e(old('companyName', $settings->companyName)); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Support Email</label>
                <input type="email" class="form-control" name="companyEmail"
                    value="<?php echo e(old('companyEmail', $settings->companyEmail)); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact Phone</label>
                <input type="text" class="form-control" name="companyPhone"
                    value="<?php echo e(old('companyPhone', $settings->companyPhone)); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" class="form-control" name="companyAddress"
                    value="<?php echo e(old('companyAddress', $settings->companyAddress)); ?>">
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

    <!-- Pricing Section -->
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
         <?php $__env->slot('title', null, []); ?> <i class="bi bi-cash-stack me-2"></i>Pricing <?php $__env->endSlot(); ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Rental Rate Per Hour (₱)</label>
                <input type="number" class="form-control" name="rentalRatePerHour"
                    step="0.01" min="0" value="<?php echo e(old('rentalRatePerHour', $settings->rentalRatePerHour)); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Max Rental Duration (hours)</label>
                <input type="number" class="form-control" name="rentalMaxDurationHours"
                    min="1" value="<?php echo e(old('rentalMaxDurationHours', $settings->rentalMaxDurationHours)); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Deposit Amount (₱)</label>
                <input type="number" class="form-control" name="depositAmount"
                    step="0.01" min="0" value="<?php echo e(old('depositAmount', $settings->depositAmount)); ?>">
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

    <!-- Geofence Section -->
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
         <?php $__env->slot('title', null, []); ?> <i class="bi bi-geo-alt me-2"></i>Geofence — Riding Zone (Circular Geofence) <?php $__env->endSlot(); ?>
        <p class="text-muted mb-3" style="font-size:0.85rem;">
            This circular boundary defines the maximum distance bicycles are allowed to travel. Bicycles outside the boundary trigger a potential theft alert.
        </p>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Enabled</label>
                <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="geofenceEnabled"
                        name="geofenceEnabled" value="1"
                        <?php echo e(old('geofenceEnabled', $settings->geofenceEnabled ? '1' : '0') ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="geofenceEnabled" style="font-size: 0.85rem;">Enable Geofence</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Center Latitude</label>
                <input type="number" class="form-control" name="geofenceCenterLat"
                    step="0.000001" min="-90" max="90" value="<?php echo e(old('geofenceCenterLat', $settings->geofenceCenterLat)); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Center Longitude</label>
                <input type="number" class="form-control" name="geofenceCenterLng"
                    step="0.000001" min="-180" max="180" value="<?php echo e(old('geofenceCenterLng', $settings->geofenceCenterLng)); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Radius (meters)</label>
                <input type="number" class="form-control" name="geofenceRadius"
                    min="10" max="50000" value="<?php echo e(old('geofenceRadius', $settings->geofenceRadius)); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Warning Threshold (m)</label>
                <input type="number" class="form-control" name="geofenceWarningThreshold"
                    min="1" value="<?php echo e(old('geofenceWarningThreshold', $settings->geofenceWarningThreshold)); ?>">
                <small class="text-muted">Distance from boundary where warning triggers.</small>
            </div>
        </div>
        <div class="mt-3 p-3 rounded" style="background: var(--surface-2); border: 1px solid var(--border-subtle);">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Current center: <strong><?php echo e($geofence['centerLat']); ?>, <?php echo e($geofence['centerLng']); ?></strong> &mdash;
                Radius: <strong><?php echo e($geofence['radius']); ?>m</strong>
            </small>
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

    <!-- Operations Section -->
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
         <?php $__env->slot('title', null, []); ?> <i class="bi bi-gear me-2"></i>Operations <?php $__env->endSlot(); ?>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Device Timeout (minutes)</label>
                <input type="number" class="form-control" name="deviceTimeoutMinutes"
                    min="1" value="<?php echo e(old('deviceTimeoutMinutes', $settings->deviceTimeoutMinutes)); ?>">
                <small class="text-muted">Mark device inactive after no heartbeat.</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Low Battery Threshold (%)</label>
                <input type="number" class="form-control" name="lowBatteryThreshold"
                    min="0" max="100" value="<?php echo e(old('lowBatteryThreshold', $settings->lowBatteryThreshold)); ?>">
                <small class="text-muted">Bicycles below this level are flagged.</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Overdue Buzzer (minutes before expiry)</label>
                <input type="number" class="form-control" name="overdueBuzzerMinutes"
                    min="0" value="<?php echo e(old('overdueBuzzerMinutes', $settings->overdueBuzzerMinutes)); ?>">
                <small class="text-muted">Activate LCD/buzzer warning before rental expires.</small>
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

    <!-- Save Button -->
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
        <div class="d-flex justify-content-end gap-2">
            <button type="reset" class="btn-admin btn-admin--secondary">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </button>
            <button type="submit" class="btn-admin btn-admin--primary">
                <i class="bi bi-check-lg me-1"></i>Save Settings
            </button>
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
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to save these settings? Changes take effect immediately.')) {
            e.preventDefault();
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\settings.blade.php ENDPATH**/ ?>