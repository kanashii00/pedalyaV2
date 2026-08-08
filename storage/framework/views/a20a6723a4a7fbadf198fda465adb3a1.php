<?php $__env->startSection('title', 'Accident Reports'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Accident Reports</h1>
    <p>View and manage accident reports</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
    <button class="btn-admin btn-admin--secondary" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
    </button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <div class="grow"><i class="bi bi-search"></i><input type="text" data-table-search placeholder="Search accident reports..."></div>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">Bicycle <span class="sort-ind"></span></th>
                <th class="sortable">Type <span class="sort-ind"></span></th>
                <th class="sortable">Severity <span class="sort-ind"></span></th>
                <th class="sortable">Description <span class="sort-ind"></span></th>
                <th class="sortable">Location <span class="sort-ind"></span></th>
                <th class="sortable">Timestamp <span class="sort-ind"></span></th>
                <th class="sortable">Acknowledged <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $accident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td data-label="Bicycle" class="cell-title">
                    <a href="<?php echo e(route('admin.bicycles.show', $accident->bicycleId)); ?>" style="text-decoration:none;">
                        <?php echo e($accident->bicycle->name ?? $accident->bicycleId); ?>

                    </a>
                </td>
                <td data-label="Type"><?php echo e(ucfirst($accident->type)); ?></td>
                <td data-label="Severity">
                    <?php switch($accident->severity):
                        case ('minor'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'info','label' => 'Minor']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'info','label' => 'Minor']); ?>
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
                            <?php break; ?>
                        <?php case ('moderate'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'warning','label' => 'Moderate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','label' => 'Moderate']); ?>
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
                            <?php break; ?>
                        <?php case ('major'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'danger','label' => 'Major']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','label' => 'Major']); ?>
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
                            <?php break; ?>
                        <?php case ('critical'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'danger','label' => 'Critical']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','label' => 'Critical']); ?>
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
                            <?php break; ?>
                        <?php default: ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['label' => ucfirst($accident->severity)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($accident->severity))]); ?>
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
                    <?php endswitch; ?>
                </td>
                <td data-label="Description" class="text-truncate" style="max-width: 200px;" title="<?php echo e($accident->description); ?>">
                    <?php echo e($accident->description); ?>

                </td>
                <td data-label="Location" class="text-truncate" style="max-width: 150px;" title="<?php echo e($accident->gpsLocation ? $accident->gpsLocation['lat'] . ', ' . $accident->gpsLocation['lng'] : ''); ?>">
                    <?php if($accident->gpsLocation): ?>
                        <?php echo e($accident->gpsLocation['lat']); ?>, <?php echo e($accident->gpsLocation['lng']); ?>

                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td data-label="Timestamp"><small><?php echo e($accident->created_at->format('M d, Y H:i')); ?></small></td>
                <td data-label="Acknowledged">
                    <?php if($accident->acknowledged): ?>
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
                    <div class="d-flex gap-1">
                        <button class="btn-admin btn-admin--secondary btn-admin--sm" title="View Details"
                            data-id="<?php echo e($accident->id); ?>"
                            data-bicycle="<?php echo e($accident->bicycle->name ?? $accident->bicycleId); ?>"
                            data-type="<?php echo e($accident->type); ?>"
                            data-severity="<?php echo e($accident->severity); ?>"
                            data-location="<?php echo e($accident->gpsLocation ? $accident->gpsLocation['lat'] . ', ' . $accident->gpsLocation['lng'] : ''); ?>"
                            data-acknowledged="<?php echo e($accident->acknowledged ? '1' : '0'); ?>"
                            data-description="<?php echo e($accident->description); ?>"
                            data-timestamp="<?php echo e($accident->created_at); ?>"
                            onclick="fillAccident(this); PedalyaModal.open('accidentDetailModal')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <?php if (! ($accident->acknowledged)): ?>
                        <form action="<?php echo e(route('admin.accidents.acknowledge', $accident->id)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn-admin btn-admin--soft btn-admin--sm" title="Acknowledge"
                                data-confirm="Acknowledge this accident report?">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="8" class="text-center">
                    <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-activity','title' => 'No accident reports found']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-activity','title' => 'No accident reports found']); ?>
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

<!-- Detail Modal -->
<div class="admin-modal" id="accidentDetailModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <h3><i class="bi bi-activity me-2"></i>Accident Report Details</h3>
            <button type="button" class="admin-icon-btn" data-modal-close><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="admin-modal__body">
            <div class="admin-form">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Bicycle</label>
                            <p id="modalBicycle" class="mb-0">—</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <p id="modalType" class="mb-0">—</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Severity</label>
                            <p id="modalSeverity" class="mb-0">—</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Timestamp</label>
                            <p id="modalTimestamp" class="mb-0">—</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <p id="modalLocation" class="mb-0">—</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Acknowledged</label>
                            <p id="modalAcknowledged" class="mb-0">—</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <p id="modalDescription" class="mb-0">—</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="admin-modal__foot">
            <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Close</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function formatSeverityBadge(severity) {
    const map = {
        'minor': '<span class="badge-admin badge-admin--info">Minor</span>',
        'moderate': '<span class="badge-admin badge-admin--warning">Moderate</span>',
        'major': '<span class="badge-admin badge-admin--danger">Major</span>',
        'critical': '<span class="badge-admin badge-admin--danger">Critical</span>'
    };
    return map[severity] || '<span class="badge-admin badge-admin--neutral">' + severity + '</span>';
}

function fillAccident(btn) {
    document.getElementById('modalBicycle').textContent = btn.dataset.bicycle || '—';
    document.getElementById('modalType').textContent = btn.dataset.type ? btn.dataset.type.charAt(0).toUpperCase() + btn.dataset.type.slice(1) : '—';
    document.getElementById('modalSeverity').innerHTML = formatSeverityBadge(btn.dataset.severity);
    document.getElementById('modalTimestamp').textContent = btn.dataset.timestamp || '—';
    document.getElementById('modalLocation').textContent = btn.dataset.location || '—';
    document.getElementById('modalAcknowledged').innerHTML = btn.dataset.acknowledged === '1'
        ? '<span class="badge-admin badge-admin--success">Yes</span>'
        : '<span class="badge-admin badge-admin--warning">No</span>';
    document.getElementById('modalDescription').textContent = btn.dataset.description || '—';
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\kyra&friends\laravel\resources\views/admin/accidents.blade.php ENDPATH**/ ?>