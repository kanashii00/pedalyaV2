

<?php $__env->startSection('title', 'Bicycle Management'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="admin-pagehead">
        <div class="admin-pagehead__title">
            <h1>Bicycles</h1>
            <p>Manage your bicycle fleet</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <div class="grow">
            <i class="bi bi-search"></i>
            <input type="text" data-table-search placeholder="Search...">
        </div>
        <form method="GET" action="<?php echo e(route('admin.bicycles.index')); ?>" class="d-flex gap-2 align-items-center">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="available" <?php echo e(request('status') === 'available' ? 'selected' : ''); ?>>Available</option>
                <option value="rented" <?php echo e(request('status') === 'rented' ? 'selected' : ''); ?>>Rented</option>
                <option value="maintenance" <?php echo e(request('status') === 'maintenance' ? 'selected' : ''); ?>>Maintenance</option>
            </select>
            <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">Name <span class="sort-ind"></span></th>
                <th class="sortable">Serial <span class="sort-ind"></span></th>
                <th class="sortable">Status <span class="sort-ind"></span></th>
                <th class="sortable">Battery <span class="sort-ind"></span></th>
                <th class="sortable">Lock <span class="sort-ind"></span></th>
                <th class="sortable">Condition <span class="sort-ind"></span></th>
                <th class="sortable">Hourly Rate <span class="sort-ind"></span></th>
                <th class="sortable">Last Updated <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $bicycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bike): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $isLocked = $bike->lockStatus === 'locked';
                    $isRented = $bike->status === 'rented';
                    $inMaintenance = $bike->status === 'maintenance';
                ?>
                <tr>
                    <td class="cell-title" data-label="Name"><?php echo e($bike->name); ?></td>
                    <td data-label="Serial"><code><?php echo e($bike->serialNumber); ?></code></td>
                    <td data-label="Status">
                        <?php if($bike->status === 'available'): ?>
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
                        <?php elseif($bike->status === 'rented'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'info','label' => 'Rented']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'info','label' => 'Rented']); ?>
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
                        <?php elseif($bike->status === 'maintenance'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'warning','label' => 'Maintenance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','label' => 'Maintenance']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'neutral','label' => ucfirst($bike->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'neutral','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($bike->status))]); ?>
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
                    <td data-label="Battery">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px; max-width:80px;">
                                <div class="progress-bar bg-<?php echo e($bike->batteryLevel <= 20 ? 'danger' : ($bike->batteryLevel <= 50 ? 'warning' : 'success')); ?>"
                                     style="width:<?php echo e($bike->batteryLevel); ?>%"></div>
                            </div>
                            <small><?php echo e($bike->batteryLevel); ?>%</small>
                        </div>
                    </td>
                    <td data-label="Lock">
                        <?php if($isLocked): ?>
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
                    </td>
                    <td data-label="Condition"><?php echo e(ucfirst($bike->condition ?? 'good')); ?></td>
                    <td data-label="Hourly Rate">₱<?php echo e(number_format($bike->hourlyRate, 2)); ?>/hr</td>
                    <td data-label="Last Updated"><small class="text-muted"><?php echo e($bike->updated_at->diffForHumans()); ?></small></td>
                    <td data-label="Actions">
                        <div class="d-flex gap-1">
                            <?php if($inMaintenance): ?>
                                
                                <button class="btn-admin btn-admin--secondary btn-admin--sm" type="button" disabled
                                        title="Disabled while bicycle is under maintenance"
                                        aria-label="Edit disabled, bicycle under maintenance">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-admin btn-admin--secondary btn-admin--sm" type="button" disabled
                                        title="Disabled while bicycle is under maintenance"
                                        aria-label="Lock control disabled, bicycle under maintenance">
                                    <i class="bi bi-<?php echo e($isLocked ? 'lock' : 'unlock'); ?>-fill"></i>
                                    <span><?php echo e($isLocked ? 'Lock' : 'Unlock'); ?></span>
                                </button>
                                <button class="btn-admin btn-admin--danger btn-admin--sm" type="button" disabled
                                        title="Disabled while bicycle is under maintenance"
                                        aria-label="Delete disabled, bicycle under maintenance">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn-admin btn-admin--secondary btn-admin--sm" type="button"
                                        onclick="PedalyaModal.open('editBicycleModal<?php echo e($bike->id); ?>')" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <?php if($isRented): ?>
                                    
                                    <button class="btn-admin btn-admin--secondary btn-admin--sm" type="button" disabled
                                            title="Bicycle is currently rented - the smart lock is controlled by the rider"
                                            aria-label="Bicycle in use, lock control unavailable">
                                        <i class="bi bi-bicycle"></i>
                                        <span>In Use</span>
                                    </button>
                                <?php elseif(!$isLocked): ?>
                                    <form action="<?php echo e(route('admin.bicycles.lock', $bike->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        
                                        <input type="hidden" name="action" value="lock">
                                        <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm"
                                                title="Unlocked - click to lock"
                                                aria-label="Unlocked bicycle, click to lock">
                                            <i class="bi bi-unlock-fill"></i>
                                            <span>Unlock</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?php echo e(route('admin.bicycles.lock', $bike->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="unlock">
                                        <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm"
                                                title="Locked - click to unlock"
                                                aria-label="Locked bicycle, click to unlock">
                                            <i class="bi bi-lock-fill"></i>
                                            <span>Lock</span>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form action="<?php echo e(route('admin.bicycles.destroy', $bike->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm" title="Delete"
                                            data-confirm="Are you sure you want to delete this bicycle?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

                
                <div class="admin-modal" id="editBicycleModal<?php echo e($bike->id); ?>">
                    <div class="admin-modal__backdrop" data-modal-close></div>
                    <div class="admin-modal__dialog admin-modal__dialog--lg">
                        <form action="<?php echo e(route('admin.bicycles.update', $bike->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="admin-modal__head">
                                <h3>Edit Bicycle - <?php echo e($bike->name); ?></h3>
                                <button type="button" class="admin-icon-btn" data-modal-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <div class="admin-modal__body">
                                <div class="admin-form">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control" value="<?php echo e($bike->name); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Model</label>
                                            <input type="text" name="model" class="form-control" value="<?php echo e($bike->model); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Serial Number</label>
                                            <input type="text" name="serialNumber" class="form-control" value="<?php echo e($bike->serialNumber); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Hourly Rate (₱)</label>
                                            <input type="number" name="hourlyRate" class="form-control" value="<?php echo e($bike->hourlyRate); ?>" step="0.01" min="0" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Latitude</label>
                                            <input type="number" name="currentLat" class="form-control" value="<?php echo e($bike->currentLat); ?>" step="any">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Longitude</label>
                                            <input type="number" name="currentLng" class="form-control" value="<?php echo e($bike->currentLng); ?>" step="any">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Battery Level (%)</label>
                                            <input type="number" name="batteryLevel" class="form-control" value="<?php echo e($bike->batteryLevel); ?>" min="0" max="100">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="available" <?php echo e($bike->status === 'available' ? 'selected' : ''); ?>>Available</option>
                                                <option value="rented" <?php echo e($bike->status === 'rented' ? 'selected' : ''); ?>>Rented</option>
                                                <option value="maintenance" <?php echo e($bike->status === 'maintenance' ? 'selected' : ''); ?>>Maintenance</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3"><?php echo e($bike->description); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="admin-modal__foot">
                                <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Cancel</button>
                                <button type="submit" class="btn-admin btn-admin--primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9">
                        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-bicycle','title' => 'No bicycles found']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-bicycle','title' => 'No bicycles found']); ?>
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

    <div class="admin-table-foot">
        <span>Showing <?php echo e(method_exists($bicycles, 'total') ? $bicycles->total() : $bicycles->count()); ?> records</span>
        <?php if(method_exists($bicycles, 'links')): ?>
            <?php echo e($bicycles->withQueryString()->links()); ?>

        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\bicycles.blade.php ENDPATH**/ ?>