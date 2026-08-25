

<?php $__env->startSection('title', 'Audit Log'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Audit Log</h1>
    <p>Track system activities and changes</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
<button type="button" class="btn-admin btn-admin--secondary" onclick="clearFilters()">
    <i class="bi bi-x-lg me-1"></i>Clear Filters
</button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <form method="GET" action="<?php echo e(route('admin.audit-log.index')); ?>" id="auditFilterForm" class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="d-block text-muted mb-1" style="font-size:11px;font-weight:600;">Action Type</label>
                <select class="form-select form-select-sm" name="action" style="min-width:160px;">
                    <option value="">All Actions</option>
                    <option value="created" <?php echo e(request('action') == 'created' ? 'selected' : ''); ?>>Created</option>
                    <option value="updated" <?php echo e(request('action') == 'updated' ? 'selected' : ''); ?>>Updated</option>
                    <option value="deleted" <?php echo e(request('action') == 'deleted' ? 'selected' : ''); ?>>Deleted</option>
                    <option value="login" <?php echo e(request('action') == 'login' ? 'selected' : ''); ?>>Login</option>
                    <option value="logout" <?php echo e(request('action') == 'logout' ? 'selected' : ''); ?>>Logout</option>
                    <option value="rental_started" <?php echo e(request('action') == 'rental_started' ? 'selected' : ''); ?>>Rental Started</option>
                    <option value="rental_ended" <?php echo e(request('action') == 'rental_ended' ? 'selected' : ''); ?>>Rental Ended</option>
                    <option value="payment" <?php echo e(request('action') == 'payment' ? 'selected' : ''); ?>>Payment</option>
                    <option value="accident" <?php echo e(request('action') == 'accident' ? 'selected' : ''); ?>>Accident</option>
                    <option value="maintenance" <?php echo e(request('action') == 'maintenance' ? 'selected' : ''); ?>>Maintenance</option>
                    <option value="settings_changed" <?php echo e(request('action') == 'settings_changed' ? 'selected' : ''); ?>>Settings Changed</option>
                </select>
            </div>
            <div>
                <label class="d-block text-muted mb-1" style="font-size:11px;font-weight:600;">User</label>
                <select class="form-select form-select-sm" name="userId" style="min-width:160px;">
                    <option value="">All Users</option>
                    <?php $__currentLoopData = $users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>" <?php echo e(request('userId') == $user->id ? 'selected' : ''); ?>>
                            <?php echo e($user->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="d-block text-muted mb-1" style="font-size:11px;font-weight:600;">Date From</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="<?php echo e(request('date_from')); ?>">
            </div>
            <div>
                <label class="d-block text-muted mb-1" style="font-size:11px;font-weight:600;">Date To</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="<?php echo e(request('date_to')); ?>">
            </div>
            <button type="submit" class="btn-admin btn-admin--primary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="sortable">Timestamp <span class="sort-ind"></span></th>
                    <th class="sortable">Action <span class="sort-ind"></span></th>
                    <th class="sortable">User <span class="sort-ind"></span></th>
                    <th>Details <span class="sort-ind"></span></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $actionBadgeType = match ($log->action) {
                            'created' => 'success',
                            'updated' => 'info',
                            'deleted' => 'danger',
                            'login' => 'info',
                            'logout' => 'success',
                            'rental_started' => 'success',
                            'rental_ended' => 'success',
                            'payment' => 'success',
                            'accident' => 'danger',
                            'maintenance' => 'neutral',
                            'settings_changed' => 'danger',
                            default => 'neutral',
                        };
                        $actionLabel = ucfirst(str_replace('_', ' ', $log->action));
                    ?>
                    <tr>
                        <td data-label="Timestamp" class="text-nowrap"><?php echo e(($log->timestamp ?? $log->created_at)->format('M d, Y H:i:s')); ?></td>
                        <td data-label="Action"><?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => $actionBadgeType,'label' => $actionLabel]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actionBadgeType),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actionLabel)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?></td>
                        <td data-label="User"><?php echo e($log->user->name ?? 'System'); ?></td>
                        <td data-label="Details">
                            <?php if($log->details): ?>
                                <button class="btn-admin btn-admin--soft btn-admin--sm" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#details-<?php echo e($log->id); ?>"
                                    aria-expanded="false">
                                    <i class="bi bi-code-slash"></i> View
                                </button>
                                <div class="collapse mt-1" id="details-<?php echo e($log->id); ?>">
                                    <pre class="bg-dark text-light p-2 rounded mb-0"
                                        style="max-height: 300px; overflow-y: auto; font-size: 0.75rem;"><?php echo e(json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4">
                            <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-journal-text','title' => 'No audit log entries found','message' => 'System activities will appear here as they happen.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-journal-text','title' => 'No audit log entries found','message' => 'System activities will appear here as they happen.']); ?>
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

    <?php if(method_exists($logs, 'links')): ?>
        <div class="admin-table-foot">
            <span>Showing <?php echo e($logs->total()); ?> records</span>
            <?php echo e($logs->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function clearFilters() {
    window.location.href = '<?php echo e(route("admin.audit-log.index")); ?>';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\audit.blade.php ENDPATH**/ ?>