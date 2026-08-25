<?php $__env->startSection('title', 'Add Bicycle'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="admin-pagehead">
        <div class="admin-pagehead__title">
            <h1>Add Bicycle</h1>
            <p>Register a new Beach Cruiser to the fleet</p>
        </div>
        <div class="admin-pagehead__actions">
            <a href="<?php echo e(route('admin.bicycles.index')); ?>" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Inventory
            </a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('admin.bicycles.store')); ?>" class="admin-form">
    <?php echo csrf_field(); ?>

    <div class="row g-4">

        
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="admin-card__head">
                    <i class="bi bi-shield-lock me-2" style="color:var(--info)"></i>
                    <span class="admin-card__title">System-Generated</span>
                    <span class="badge-admin badge-admin--brand ms-auto" style="font-size:10px">Auto</span>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Bicycle Type</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <i class="bi bi-bicycle me-1" style="color:var(--brand)"></i>Beach Cruiser
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Serial Number</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <code class="vr-mono"><?php echo e($nextSerial); ?></code>
                            </div>
                            <small class="text-muted">Auto-generated, unique. Cannot be changed.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">QR Code</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <code class="vr-mono"><?php echo e($nextQr); ?></code>
                            </div>
                            <small class="text-muted">Auto-generated, unique. Cannot be changed.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="admin-card__head">
                    <i class="bi bi-gear me-2" style="color:var(--text-3)"></i>
                    <span class="admin-card__title">System Defaults</span>
                    <span class="badge-admin badge-admin--neutral ms-auto" style="font-size:10px">Defaults</span>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Initial Status</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Available']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Available']); ?>
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
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Condition</label>
                            <div class="form-control-plaintext vr-readonly-field">Good</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Battery Level</label>
                            <div class="form-control-plaintext vr-readonly-field">
                                <span class="vr-battery-pill">100%</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Smart Lock</label>
                            <div class="form-control-plaintext vr-readonly-field">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card__head">
                    <i class="bi bi-pencil-square me-2" style="color:var(--brand)"></i>
                    <span class="admin-card__title">Bicycle Details</span>
                    <span class="badge-admin badge-admin--warning ms-auto" style="font-size:10px">Required</span>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bicycle Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('name')); ?>" placeholder="e.g. Alpha, Bravo, Charlie..." required autofocus>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model / Variant</label>
                            <input type="text" name="model" class="form-control <?php $__errorArgs = ['model'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('model', 'Beach Cruiser')); ?>" placeholder="Beach Cruiser">
                            <?php $__errorArgs = ['model'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted">Defaults to Beach Cruiser if left unchanged.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hourly Rate (&#8369;) <span class="text-danger">*</span></label>
                            <input type="number" name="hourlyRate" class="form-control <?php $__errorArgs = ['hourlyRate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('hourlyRate')); ?>" step="0.01" min="0" placeholder="0.00" required>
                            <?php $__errorArgs = ['hourlyRate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('description')); ?>" placeholder="Optional notes...">
                            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card__head">
                    <i class="bi bi-geo-alt me-2" style="color:var(--warning)"></i>
                    <span class="admin-card__title">GPS / Initial Location</span>
                    <small class="text-muted ms-auto">Optional</small>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Latitude</label>
                            <input type="number" name="currentLat" class="form-control <?php $__errorArgs = ['currentLat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('currentLat')); ?>" step="any" placeholder="e.g. 10.3157">
                            <?php $__errorArgs = ['currentLat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Longitude</label>
                            <input type="number" name="currentLng" class="form-control <?php $__errorArgs = ['currentLng'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('currentLng')); ?>" step="any" placeholder="e.g. 123.8854">
                            <?php $__errorArgs = ['currentLng'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <small class="text-muted">Set the bicycle's starting GPS coordinates. Location updates automatically via device telemetry.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Serial Number <strong><?php echo e($nextSerial); ?></strong> and QR Code <strong><?php echo e($nextQr); ?></strong> will be assigned on save.
        </small>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.bicycles.index')); ?>" class="btn-admin btn-admin--secondary">
                <i class="bi bi-x-lg me-1"></i>Cancel
            </a>
            <button type="submit" class="btn-admin btn-admin--primary">
                <i class="bi bi-plus-lg me-1"></i>Add Bicycle
            </button>
        </div>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\bicycles\create.blade.php ENDPATH**/ ?>