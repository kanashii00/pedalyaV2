<?php $__env->startSection('title', 'Blacklisted Customers'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="admin-pagehead">
        <div class="admin-pagehead__title">
            <h1>Blacklisted Customers</h1>
            <p>Suspended, disabled, or blacklisted customers</p>
        </div>
        <div class="admin-pagehead__actions">
            <a href="<?php echo e(route('admin.riders.index')); ?>" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Customer List
            </a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-table-wrap">
    <div class="admin-table-toolbar">
        <form method="GET" action="<?php echo e(route('admin.riders.blacklisted')); ?>" class="d-flex gap-2 align-items-center">
            <div class="grow">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Search by name, email, phone, or student ID..." value="<?php echo e(request('search')); ?>">
            </div>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Disabled</option>
                <option value="suspended" <?php echo e(request('status') === 'suspended' ? 'selected' : ''); ?>>Suspended</option>
                <option value="blacklisted" <?php echo e(request('status') === 'blacklisted' ? 'selected' : ''); ?>>Blacklisted</option>
            </select>
            <button type="submit" class="btn-admin btn-admin--secondary btn-admin--sm">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th class="sortable">ID <span class="sort-ind"></span></th>
                <th class="sortable">Full Name <span class="sort-ind"></span></th>
                <th class="sortable">Student ID <span class="sort-ind"></span></th>
                <th class="sortable">Contact <span class="sort-ind"></span></th>
                <th class="sortable">Email <span class="sort-ind"></span></th>
                <th class="sortable">Reason <span class="sort-ind"></span></th>
                <th class="sortable">Status <span class="sort-ind"></span></th>
                <th class="sortable">Date Added <span class="sort-ind"></span></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td data-label="ID"><code><?php echo e($rider->id); ?></code></td>
                    <td data-label="Full Name">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-2" style="width:32px;height:32px;font-size:12px;">
                                <?php echo e(strtoupper(substr($rider->name, 0, 1))); ?>

                            </div>
                            <div>
                                <div class="fw-semibold" style="color:var(--text-1)"><?php echo e($rider->name); ?></div>
                                <?php if($rider->address): ?>
                                    <small class="text-muted"><?php echo e(Str::limit($rider->address, 30)); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td data-label="Student ID"><small><?php echo e($rider->studentId ?? '—'); ?></small></td>
                    <td data-label="Contact"><?php echo e($rider->phoneNumber ?? '—'); ?></td>
                    <td data-label="Email"><small><?php echo e($rider->email); ?></small></td>
                    <td data-label="Reason">
                        <?php if($rider->blacklistReason): ?>
                            <small class="text-muted" title="<?php echo e($rider->blacklistReason); ?>"><?php echo e(Str::limit($rider->blacklistReason, 40)); ?></small>
                        <?php else: ?>
                            <small class="text-muted">—</small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Status">
                        <?php if($rider->status === 'inactive'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'neutral','label' => 'Disabled']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'neutral','label' => 'Disabled']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'warning','label' => 'Suspended']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','label' => 'Suspended']); ?>
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
                        <?php elseif($rider->status === 'blacklisted'): ?>
                            <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'danger','label' => 'Blacklisted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','label' => 'Blacklisted']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'neutral','label' => ucfirst($rider->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'neutral','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($rider->status))]); ?>
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
                    <td data-label="Date Added"><small class="text-muted"><?php echo e($rider->created_at->format('M d, Y')); ?></small></td>
                    <td data-label="Actions">
                        <div class="actions-row">
                            <button type="button" class="btn-admin btn-admin--secondary btn-admin--sm"
                                    title="View Customer"
                                    onclick="openBlacklistedView(this)"
                                    data-id="<?php echo e($rider->id); ?>"
                                    data-name="<?php echo e($rider->name); ?>"
                                    data-student-id="<?php echo e($rider->studentId ?? '—'); ?>"
                                    data-email="<?php echo e($rider->email); ?>"
                                    data-phone="<?php echo e($rider->phoneNumber ?? '—'); ?>"
                                    data-address="<?php echo e($rider->address ?? '—'); ?>"
                                    data-joined="<?php echo e($rider->created_at->format('M d, Y')); ?>"
                                    data-status="<?php echo e($rider->status); ?>"
                                    data-blacklist-reason="<?php echo e($rider->blacklistReason ?? ''); ?>"
                                    data-id-url="<?php echo e(($rider->idVerification['id_url'] ?? '')); ?>">
                                <i class="bi bi-eye"></i>
                            </button>

                            <button type="button" class="btn-admin btn-admin--warning btn-admin--sm"
                                    title="Edit / Manage"
                                    onclick="openBlacklistManage(this)"
                                    data-id="<?php echo e($rider->id); ?>"
                                    data-name="<?php echo e($rider->name); ?>"
                                    data-student-id="<?php echo e($rider->studentId ?? ''); ?>"
                                    data-email="<?php echo e($rider->email); ?>"
                                    data-phone="<?php echo e($rider->phoneNumber ?? ''); ?>"
                                    data-address="<?php echo e($rider->address ?? ''); ?>"
                                    data-status="<?php echo e($rider->status); ?>"
                                    data-blacklist-reason="<?php echo e($rider->blacklistReason ?? ''); ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <form action="<?php echo e(route('admin.riders.status', $rider->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn-admin btn-admin--success btn-admin--sm" title="Restore to Active" data-confirm="Restore this customer to Active? They will appear in Verified Customers if verified.">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9">
                        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-x-octagon','title' => 'No blacklisted customers']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-x-octagon','title' => 'No blacklisted customers']); ?>
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
        <span>Showing <?php echo e(method_exists($riders, 'total') ? $riders->total() : $riders->count()); ?> blacklisted customers</span>
        <?php if(method_exists($riders, 'links')): ?>
            <?php echo e($riders->withQueryString()->links()); ?>

        <?php endif; ?>
    </div>
</div>


<div class="admin-modal" id="blacklistedViewModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <div class="d-flex align-items-center gap-2">
                <span class="vr-header-icon"><i class="bi bi-x-octagon"></i></span>
                <div>
                    <h3 class="mb-0">Customer Details</h3>
                    <small class="text-muted">Blacklisted customer profile</small>
                </div>
            </div>
            <button type="button" class="btn-admin btn-admin--ghost btn-admin--sm" data-modal-close>
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="admin-modal__body">
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="vr-card vr-card--details">
                        <div class="vr-card__head">
                            <span class="vr-card__icon"><i class="bi bi-person-circle"></i></span>
                            <span class="vr-card__title">Profile</span>
                        </div>
                        <div class="vr-card__body">
                            <div class="vr-field">
                                <span class="vr-field__label">Customer ID</span>
                                <span class="vr-field__value" id="bvId">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Full Name</span>
                                <span class="vr-field__value" id="bvName">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Student ID</span>
                                <span class="vr-field__value" id="bvStudentId">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Email Address</span>
                                <span class="vr-field__value" id="bvEmail">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Phone Number</span>
                                <span class="vr-field__value" id="bvPhone">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Address</span>
                                <span class="vr-field__value" id="bvAddress">—</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="vr-card vr-card--id">
                        <div class="vr-card__head">
                            <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-shield-x"></i></span>
                            <span class="vr-card__title">Account Information</span>
                        </div>
                        <div class="vr-card__body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Date Joined</span>
                                        <span class="vr-field__value" id="bvJoined">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Account Status</span>
                                        <span class="vr-field__value" id="bvStatus">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Blacklist Reason</span>
                                        <span class="vr-field__value" id="bvReason">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vr-card vr-card--id mt-3">
                        <div class="vr-card__head">
                            <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-card-image"></i></span>
                            <span class="vr-card__title">Submitted ID</span>
                            <button type="button" class="btn-admin btn-admin--ghost btn-admin--xs ms-auto d-none" id="bvZoomBtn" title="View full size">
                                <i class="bi bi-arrows-fullscreen me-1"></i>Full Size
                            </button>
                        </div>
                        <div class="vr-card__body vr-card__body--id">
                            <div class="vr-id-preview">
                                <img id="bvIdImg" src="" alt="Submitted ID">
                                <p id="bvIdEmpty" class="vr-id-empty d-none">
                                    <i class="bi bi-file-earmark-image"></i>
                                    <span>No ID image submitted</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admin-modal__foot">
            <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Close</button>
        </div>
    </div>
</div>


<div class="admin-modal" id="blacklistManageModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <div class="d-flex align-items-center gap-2">
                <span class="vr-header-icon"><i class="bi bi-pencil-square"></i></span>
                <div>
                    <h3 class="mb-0">Edit / Manage Customer</h3>
                    <small class="text-muted">Update blacklist status, reason, and profile details</small>
                </div>
            </div>
            <button type="button" class="btn-admin btn-admin--ghost btn-admin--sm" data-modal-close>
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="blacklistManageForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="admin-modal__body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="vr-card vr-card--details">
                            <div class="vr-card__head">
                                <span class="vr-card__icon"><i class="bi bi-person-circle"></i></span>
                                <span class="vr-card__title">Profile</span>
                            </div>
                            <div class="vr-card__body">
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Full Name</label>
                                    <input type="text" name="name" class="form-control form-control-sm" id="bmName" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Student ID</label>
                                    <input type="text" name="studentId" class="form-control form-control-sm" id="bmStudentId">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Email</label>
                                    <input type="email" name="email" class="form-control form-control-sm" id="bmEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Phone Number</label>
                                    <input type="text" name="phoneNumber" class="form-control form-control-sm" id="bmPhone">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Address</label>
                                    <input type="text" name="address" class="form-control form-control-sm" id="bmAddress">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="vr-card vr-card--id">
                            <div class="vr-card__head">
                                <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-shield-x"></i></span>
                                <span class="vr-card__title">Blacklist Details</span>
                            </div>
                            <div class="vr-card__body">
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Status</label>
                                    <select name="status" class="form-select form-select-sm" id="bmStatus">
                                        <option value="inactive">Disabled</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="blacklisted">Blacklisted</option>
                                        <option value="active">Restore to Active</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label form-label-sm">Blacklist Reason</label>
                                    <textarea name="blacklistReason" class="form-control form-control-sm" id="bmReason" rows="4" placeholder="Enter reason for blacklisting..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin-modal__foot">
                <button type="button" class="btn-admin btn-admin--secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-admin btn-admin--success btn-admin--sm">
                    <i class="bi bi-check-lg me-1"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<div class="vr-lightbox" id="bvLightbox">
    <div class="vr-lightbox__backdrop" onclick="closeBvLightbox()"></div>
    <div class="vr-lightbox__content">
        <button type="button" class="vr-lightbox__close" onclick="closeBvLightbox()"><i class="bi bi-x-lg"></i></button>
        <img id="bvLightboxImg" src="" alt="ID Full Size">
    </div>
</div>

<script>
function openBlacklistedView(btn) {
    const d = btn.dataset;
    document.getElementById('bvId').textContent = '#' + (d.id || '—');
    document.getElementById('bvName').textContent = d.name || '—';
    document.getElementById('bvStudentId').textContent = d.studentId || '—';
    document.getElementById('bvEmail').textContent = d.email || '—';
    document.getElementById('bvPhone').textContent = d.phone || '—';
    document.getElementById('bvAddress').textContent = d.address || '—';
    document.getElementById('bvJoined').textContent = d.joined || '—';

    const statusEl = document.getElementById('bvStatus');
    const s = d.status || 'inactive';
    const badgeMap = { active: 'vr-badge--success', inactive: 'vr-badge--neutral', suspended: 'vr-badge--warning', blacklisted: 'vr-badge--danger' };
    const labelMap = { active: 'Active', inactive: 'Disabled', suspended: 'Suspended', blacklisted: 'Blacklisted' };
    statusEl.innerHTML = '<span class="vr-badge ' + (badgeMap[s] || 'vr-badge--neutral') + '">' + (labelMap[s] || s) + '</span>';

    document.getElementById('bvReason').textContent = d.blacklistReason || 'No reason provided';

    const img = document.getElementById('bvIdImg');
    const empty = document.getElementById('bvIdEmpty');
    const zoomBtn = document.getElementById('bvZoomBtn');
    if (d.idUrl) {
        img.src = d.idUrl;
        img.classList.remove('d-none');
        empty.classList.add('d-none');
        zoomBtn.classList.remove('d-none');
    } else {
        img.classList.add('d-none');
        empty.classList.remove('d-none');
        zoomBtn.classList.add('d-none');
    }

    PedalyaModal.open('blacklistedViewModal');
}

function openBlacklistManage(btn) {
    const d = btn.dataset;
    document.getElementById('bmName').value = d.name || '';
    document.getElementById('bmStudentId').value = d.studentId || '';
    document.getElementById('bmEmail').value = d.email || '';
    document.getElementById('bmPhone').value = d.phone || '';
    document.getElementById('bmAddress').value = d.address || '';
    document.getElementById('bmStatus').value = d.status || 'inactive';
    document.getElementById('bmReason').value = d.blacklistReason || '';

    document.getElementById('blacklistManageForm').action =
        '<?php echo e(url("admin/blacklisted-customers")); ?>/' + d.id;

    PedalyaModal.open('blacklistManageModal');
}

document.getElementById('bvZoomBtn')?.addEventListener('click', function() {
    const img = document.getElementById('bvIdImg');
    if (img && img.src && !img.classList.contains('d-none')) {
        document.getElementById('bvLightboxImg').src = img.src;
        document.getElementById('bvLightbox').classList.add('open');
    }
});
function closeBvLightbox() {
    document.getElementById('bvLightbox').classList.remove('open');
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeBvLightbox(); });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\blacklisted-customers.blade.php ENDPATH**/ ?>