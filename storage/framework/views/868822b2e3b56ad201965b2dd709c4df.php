<?php $__env->startSection('title', 'Verified Customers'); ?>

<?php $__env->startSection('page-header'); ?>
    <div class="admin-pagehead">
        <div class="admin-pagehead__title">
            <h1>Verified Customers</h1>
            <p>Active customers with confirmed identity verification</p>
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
        <form method="GET" action="<?php echo e(route('admin.riders.verified')); ?>" class="d-flex gap-2 align-items-center">
            <div class="grow">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Search by name, email, phone, or student ID..." value="<?php echo e(request('search')); ?>">
            </div>
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
                <th class="sortable">Verified <span class="sort-ind"></span></th>
                <th class="sortable text-center">Rentals <span class="sort-ind"></span></th>
                <th class="sortable text-center">Spent <span class="sort-ind"></span></th>
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
                    <td data-label="Verified">
                        <?php
                            $verifiedAt = null;
                            if (is_array($rider->idVerification)) {
                                $verifiedAt = $rider->idVerification['verified_at'] ?? null;
                            }
                        ?>
                        <?php if($verifiedAt): ?>
                            <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($verifiedAt)->format('M d, Y')); ?></small>
                        <?php else: ?>
                            <small class="text-muted">—</small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" data-label="Rentals"><?php echo e($rider->totalRentals ?? 0); ?></td>
                    <td class="text-center" data-label="Spent">₱<?php echo e(number_format($rider->totalSpent ?? 0, 2)); ?></td>
                    <td data-label="Actions">
                        <div class="actions-row">
                            <button type="button" class="btn-admin btn-admin--secondary btn-admin--sm"
                                    title="View Customer"
                                    onclick="openVerifiedView(this)"
                                    data-id="<?php echo e($rider->id); ?>"
                                    data-name="<?php echo e($rider->name); ?>"
                                    data-student-id="<?php echo e($rider->studentId ?? '—'); ?>"
                                    data-email="<?php echo e($rider->email); ?>"
                                    data-phone="<?php echo e($rider->phoneNumber ?? '—'); ?>"
                                    data-address="<?php echo e($rider->address ?? '—'); ?>"
                                    data-joined="<?php echo e($rider->created_at->format('M d, Y')); ?>"
                                    data-rentals="<?php echo e($rider->totalRentals ?? 0); ?>"
                                    data-spent="<?php echo e(number_format($rider->totalSpent ?? 0, 2)); ?>"
                                    data-status="<?php echo e($rider->status ?? 'active'); ?>"
                                    data-verified-at="<?php echo e($verifiedAt ? \Carbon\Carbon::parse($verifiedAt)->format('M d, Y \a\t g:i A') : '—'); ?>"
                                    data-id-url="<?php echo e(($rider->idVerification['id_url'] ?? '')); ?>">
                                <i class="bi bi-eye"></i>
                            </button>

                            <form action="<?php echo e(route('admin.riders.status', $rider->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="status" value="inactive">
                                <button type="submit" class="btn-admin btn-admin--danger btn-admin--sm" title="Blacklist" data-confirm="Blacklist this customer? They will be moved to Blacklisted Customers.">
                                    <i class="bi bi-slash-circle"></i>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-patch-check','title' => 'No verified customers found']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-patch-check','title' => 'No verified customers found']); ?>
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
        <span>Showing <?php echo e(method_exists($riders, 'total') ? $riders->total() : $riders->count()); ?> verified customers</span>
        <?php if(method_exists($riders, 'links')): ?>
            <?php echo e($riders->withQueryString()->links()); ?>

        <?php endif; ?>
    </div>
</div>


<div class="admin-modal" id="verifiedViewModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog admin-modal__dialog--lg">
        <div class="admin-modal__head">
            <div class="d-flex align-items-center gap-2">
                <span class="vr-header-icon"><i class="bi bi-person-badge"></i></span>
                <div>
                    <h3 class="mb-0">Customer Details</h3>
                    <small class="text-muted">Verified &amp; active customer profile</small>
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
                                <span class="vr-field__value" id="vvId">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Full Name</span>
                                <span class="vr-field__value" id="vvName">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Student ID</span>
                                <span class="vr-field__value" id="vvStudentId">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Email Address</span>
                                <span class="vr-field__value" id="vvEmail">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Phone Number</span>
                                <span class="vr-field__value" id="vvPhone">—</span>
                            </div>
                            <div class="vr-field">
                                <span class="vr-field__label">Address</span>
                                <span class="vr-field__value" id="vvAddress">—</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="vr-card vr-card--id">
                        <div class="vr-card__head">
                            <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-shield-check"></i></span>
                            <span class="vr-card__title">Account Information</span>
                        </div>
                        <div class="vr-card__body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Verification Date</span>
                                        <span class="vr-field__value" id="vvVerifiedAt">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Account Status</span>
                                        <span class="vr-field__value" id="vvStatus">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Date Joined</span>
                                        <span class="vr-field__value" id="vvJoined">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Total Rentals</span>
                                        <span class="vr-field__value" id="vvRentals">—</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="vr-field">
                                        <span class="vr-field__label">Total Spent</span>
                                        <span class="vr-field__value" id="vvSpent">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vr-card vr-card--id mt-3">
                        <div class="vr-card__head">
                            <span class="vr-card__icon vr-card__icon--id"><i class="bi bi-card-image"></i></span>
                            <span class="vr-card__title">Submitted ID</span>
                            <button type="button" class="btn-admin btn-admin--ghost btn-admin--xs ms-auto d-none" id="vvZoomBtn" title="View full size">
                                <i class="bi bi-arrows-fullscreen me-1"></i>Full Size
                            </button>
                        </div>
                        <div class="vr-card__body vr-card__body--id">
                            <div class="vr-id-preview">
                                <img id="vvIdImg" src="" alt="Submitted ID">
                                <p id="vvIdEmpty" class="vr-id-empty d-none">
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

<div class="vr-lightbox" id="vvLightbox">
    <div class="vr-lightbox__backdrop" onclick="closeVvLightbox()"></div>
    <div class="vr-lightbox__content">
        <button type="button" class="vr-lightbox__close" onclick="closeVvLightbox()"><i class="bi bi-x-lg"></i></button>
        <img id="vvLightboxImg" src="" alt="ID Full Size">
    </div>
</div>

<script>
function openVerifiedView(btn) {
    const d = btn.dataset;
    document.getElementById('vvId').textContent = '#' + (d.id || '—');
    document.getElementById('vvName').textContent = d.name || '—';
    document.getElementById('vvStudentId').textContent = d.studentId || '—';
    document.getElementById('vvEmail').textContent = d.email || '—';
    document.getElementById('vvPhone').textContent = d.phone || '—';
    document.getElementById('vvAddress').textContent = d.address || '—';
    document.getElementById('vvJoined').textContent = d.joined || '—';
    document.getElementById('vvVerifiedAt').textContent = d.verifiedAt || '—';
    document.getElementById('vvRentals').textContent = d.rentals || '0';
    document.getElementById('vvSpent').textContent = '₱' + (d.spent || '0.00');

    const statusEl = document.getElementById('vvStatus');
    const s = d.status || 'active';
    const badgeClass = s === 'active' ? 'vr-badge--success' : s === 'suspended' ? 'vr-badge--danger' : 'vr-badge--warning';
    statusEl.innerHTML = '<span class="vr-badge ' + badgeClass + '">' + s.charAt(0).toUpperCase() + s.slice(1) + '</span>';

    const img = document.getElementById('vvIdImg');
    const empty = document.getElementById('vvIdEmpty');
    const zoomBtn = document.getElementById('vvZoomBtn');
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

    PedalyaModal.open('verifiedViewModal');
}

document.getElementById('vvZoomBtn')?.addEventListener('click', function() {
    const img = document.getElementById('vvIdImg');
    if (img && img.src && !img.classList.contains('d-none')) {
        document.getElementById('vvLightboxImg').src = img.src;
        document.getElementById('vvLightbox').classList.add('open');
    }
});
function closeVvLightbox() {
    document.getElementById('vvLightbox').classList.remove('open');
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeVvLightbox(); });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\verified-customers.blade.php ENDPATH**/ ?>