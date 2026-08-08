

<?php $__env->startSection('title', 'Rider Management'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Riders</h1>
    <p>Manage registered riders and verifications</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <form method="GET" action="<?php echo e(route('admin.riders.index')); ?>" class="d-flex gap-2 align-items-center">
            <div class="grow">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Name, email, or phone..." value="<?php echo e(request('search')); ?>">
            </div>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
                <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                <option value="suspended" <?php echo e(request('status') === 'suspended' ? 'selected' : ''); ?>>Suspended</option>
            </select>
            <select name="verified" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="1" <?php echo e(request('verified') === '1' ? 'selected' : ''); ?>>Verified</option>
                <option value="0" <?php echo e(request('verified') === '0' ? 'selected' : ''); ?>>Unverified</option>
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
                <th class="sortable">Email <span class="sort-ind"></span></th>
                <th class="sortable">Phone <span class="sort-ind"></span></th>
                <th class="sortable">Status <span class="sort-ind"></span></th>
                <th class="sortable">Verified <span class="sort-ind"></span></th>
                <th class="sortable">Total Rentals <span class="sort-ind"></span></th>
                <th class="sortable">Total Spent <span class="sort-ind"></span></th>
                <th class="sortable">Joined <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="cell-title" data-label="Name">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-2" style="width:32px;height:32px;font-size:12px;">
                                <?php echo e(strtoupper(substr($rider->name, 0, 1))); ?>

                            </div>
                            <?php echo e($rider->name); ?>

                        </div>
                    </td>
                    <td data-label="Email"><small><?php echo e($rider->email); ?></small></td>
                    <td data-label="Phone"><?php echo e($rider->phoneNumber ?? '—'); ?></td>
                    <td data-label="Status">
                        <?php if($rider->status === 'active'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Active']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Active']); ?>
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
                        <?php elseif($rider->status === 'inactive'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'neutral','label' => 'Inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'neutral','label' => 'Inactive']); ?>
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
                        <?php elseif($rider->status === 'suspended'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'danger','label' => 'Suspended']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','label' => 'Suspended']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'neutral','label' => ucfirst($rider->status ?? 'active')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'neutral','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($rider->status ?? 'active'))]); ?>
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
                    <td data-label="Verified">
                        <?php if($rider->verified ?? false): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Verified']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Verified']); ?>
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
                    <td data-label="Total Rentals"><?php echo e($rider->totalRentals ?? 0); ?></td>
                    <td data-label="Total Spent">₱<?php echo e(number_format($rider->totalSpent ?? 0, 2)); ?></td>
                    <td data-label="Joined"><small class="text-muted"><?php echo e($rider->created_at->format('M d, Y')); ?></small></td>
                    <td data-label="Actions">
                        <div class="d-flex gap-1">
                            <?php if(!($rider->verified ?? false) && ($rider->idUploaded ?? false)): ?>
                                <form action="<?php echo e(route('admin.riders.verify', $rider->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="approved" value="1">
                                    <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm" title="Verify">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if(($rider->status ?? 'active') === 'active'): ?>
                                <form action="<?php echo e(route('admin.riders.status', $rider->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="status" value="inactive">
                                    <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm" title="Disable" data-confirm="Disable this rider?">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="<?php echo e(route('admin.riders.status', $rider->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm" title="Enable">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if(($rider->status ?? 'active') !== 'suspended'): ?>
                                <form action="<?php echo e(route('admin.riders.status', $rider->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="status" value="suspended">
                                    <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm" title="Suspend" data-confirm="Suspend this rider?">
                                        <i class="bi bi-slash-circle"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9">
                        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-people','title' => 'No riders found']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-people','title' => 'No riders found']); ?>
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
        <span>Showing <?php echo e(method_exists($riders, 'total') ? $riders->total() : $riders->count()); ?> records</span>
        <?php if(method_exists($riders, 'links')): ?>
            <?php echo e($riders->withQueryString()->links()); ?>

        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\flutter\Projects\4th_year\pedalya\resources\views/admin/riders.blade.php ENDPATH**/ ?>