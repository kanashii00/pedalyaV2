<?php $__env->startSection('title', 'Bicycle Status — Pedalya Admin'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="admin-pagehead">
        <div class="admin-pagehead__title">
            <h1>Bicycle Status</h1>
            <p>Live monitoring of each bicycle's current condition and operational state</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Total Bicycles','value' => $summary['total'],'icon' => 'bi-bicycle','color' => 'var(--brand)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Bicycles','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['total']),'icon' => 'bi-bicycle','color' => 'var(--brand)']); ?>
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
    <div class="col-6 col-lg-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Available','value' => $summary['available'],'icon' => 'bi-check-circle','color' => 'var(--success)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Available','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['available']),'icon' => 'bi-check-circle','color' => 'var(--success)']); ?>
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
    <div class="col-6 col-lg-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Rented','value' => $summary['rented'],'icon' => 'bi-person-badge','color' => 'var(--info)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Rented','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['rented']),'icon' => 'bi-person-badge','color' => 'var(--info)']); ?>
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
    <div class="col-6 col-lg-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Maintenance','value' => $summary['maintenance'],'icon' => 'bi-tools','color' => 'var(--warning)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Maintenance','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['maintenance']),'icon' => 'bi-tools','color' => 'var(--warning)']); ?>
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
    <div class="col-6 col-lg-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Locked','value' => $summary['locked'],'icon' => 'bi-lock-fill','color' => 'var(--danger)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Locked','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['locked']),'icon' => 'bi-lock-fill','color' => 'var(--danger)']); ?>
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
    <div class="col-6 col-lg-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Unlocked','value' => $summary['unlocked'],'icon' => 'bi-unlock','color' => 'var(--success)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Unlocked','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['unlocked']),'icon' => 'bi-unlock','color' => 'var(--success)']); ?>
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
    <div class="col-6 col-lg-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Online','value' => $summary['online'],'icon' => 'bi-wifi','color' => 'var(--success)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Online','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['online']),'icon' => 'bi-wifi','color' => 'var(--success)']); ?>
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
    <div class="col-6 col-lg-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Offline','value' => $summary['offline'],'icon' => 'bi-wifi-off','color' => 'var(--text-3)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Offline','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['offline']),'icon' => 'bi-wifi-off','color' => 'var(--text-3)']); ?>
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


<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <div class="grow">
            <i class="bi bi-search"></i>
            <input type="text" data-table-search placeholder="Search by name or serial...">
        </div>
        <form method="GET" action="<?php echo e(route('admin.bicycles.status')); ?>" class="d-flex gap-2 align-items-center flex-wrap">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Availability</option>
                <option value="available" <?php echo e(request('status') === 'available' ? 'selected' : ''); ?>>Available</option>
                <option value="rented" <?php echo e(request('status') === 'rented' ? 'selected' : ''); ?>>Rented</option>
                <option value="maintenance" <?php echo e(request('status') === 'maintenance' ? 'selected' : ''); ?>>Maintenance</option>
            </select>
            <select name="lock" class="form-select form-select-sm">
                <option value="">All Locks</option>
                <option value="locked" <?php echo e(request('lock') === 'locked' ? 'selected' : ''); ?>>Locked</option>
                <option value="unlocked" <?php echo e(request('lock') === 'unlocked' ? 'selected' : ''); ?>>Unlocked</option>
            </select>
            <select name="connectivity" class="form-select form-select-sm">
                <option value="">All Connectivity</option>
                <option value="online" <?php echo e(request('connectivity') === 'online' ? 'selected' : ''); ?>>Online</option>
                <option value="offline" <?php echo e(request('connectivity') === 'offline' ? 'selected' : ''); ?>>Offline</option>
            </select>
            <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <?php if(request()->has('status') || request()->has('lock') || request()->has('connectivity')): ?>
                <a href="<?php echo e(route('admin.bicycles.status')); ?>" class="btn-admin btn-admin--ghost btn-admin--sm">
                    <i class="bi bi-x-lg"></i>Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">Name <span class="sort-ind"></span></th>
                <th class="sortable">Serial <span class="sort-ind"></span></th>
                <th class="sortable">Availability <span class="sort-ind"></span></th>
                <th class="sortable">Battery <span class="sort-ind"></span></th>
                <th class="sortable">Smart Lock <span class="sort-ind"></span></th>
                <th class="sortable">Condition <span class="sort-ind"></span></th>
                <th class="sortable">Connectivity <span class="sort-ind"></span></th>
                <th>Current Rider</th>
                <th class="sortable">Last Updated <span class="sort-ind"></span></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $bicycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bike): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="cell-title" data-label="Name">
                        <div class="d-flex align-items-center gap-2">
                            <span class="bike-status-dot <?php echo e($bike->connectivity === 'online' ? 'online' : 'offline'); ?>"></span>
                            <span><?php echo e($bike->name); ?></span>
                        </div>
                    </td>
                    <td data-label="Serial"><code><?php echo e($bike->serialNumber); ?></code></td>
                    <td data-label="Availability">
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
                    <td data-label="Smart Lock">
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
                    </td>
                    <td data-label="Condition"><?php echo e(ucfirst($bike->condition ?? 'good')); ?></td>
                    <td data-label="Connectivity">
                        <?php if($bike->connectivity === 'online'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Online']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Online']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'neutral','label' => 'Offline']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'neutral','label' => 'Offline']); ?>
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
                    <td data-label="Current Rider">
                        <?php if($bike->status === 'rented' && $bike->currentRiderUser): ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-badge text-info"></i>
                                <div>
                                    <div class="fw-semibold"><?php echo e($bike->currentRiderUser->name); ?></div>
                                    <small class="text-muted"><?php echo e($bike->currentRiderUser->email); ?></small>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Last Updated">
                        <small class="text-muted"><?php echo e($bike->updated_at?->diffForHumans() ?? 'Never'); ?></small>
                        <div>
                            <small class="text-muted"><?php echo e($bike->lastHeartbeat?->diffForHumans() ?? 'No heartbeat'); ?></small>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9">
                        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-bicycle','title' => 'No bicycles match the current filters']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-bicycle','title' => 'No bicycles match the current filters']); ?>
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
        <span>Showing <?php echo e(count($bicycles)); ?> bicycle(s)</span>
        <small class="text-muted">Statuses sync automatically with Rental Management, Maintenance, GPS Monitoring and Smart Lock Control.</small>
    </div>
</div>

<style>
    .bike-status-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .bike-status-dot.online { background: var(--success); }
    .bike-status-dot.offline { background: var(--text-3); }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\bicycles-status.blade.php ENDPATH**/ ?>