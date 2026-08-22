

<?php $__env->startSection('title', 'ID Scan Review'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>ID Scan Review</h1>
    <p>Scan record #<?php echo e($scan->id); ?> • <?php echo e($scan->created_at->format('M d, Y g:i A')); ?></p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('admin.id-scans.index')); ?>" class="btn-admin btn-admin--secondary">
    <i class="bi bi-arrow-left me-1"></i>Back to Records
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('warning')): ?>
    <div class="alert alert-pedalya alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e(session('warning')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    
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
             <?php $__env->slot('title', null, []); ?> <i class="bi bi-image me-2"></i>Captured Document <?php $__env->endSlot(); ?>
            <div class="row g-2">
                <?php if($scan->frontImagePath): ?>
                    <div class="col-12">
                        <small class="text-muted text-uppercase fw-semibold">Front</small>
                        <img src="<?php echo e(route('admin.id-scans.image', [$scan->id, 'front'])); ?>" alt="Front of ID"
                             class="img-fluid rounded border" style="max-height:260px;width:100%;object-fit:contain;background:#111;">
                    </div>
                <?php endif; ?>
                <?php if($scan->backImagePath): ?>
                    <div class="col-12">
                        <small class="text-muted text-uppercase fw-semibold">Back</small>
                        <img src="<?php echo e(route('admin.id-scans.image', [$scan->id, 'back'])); ?>" alt="Back of ID"
                             class="img-fluid rounded border" style="max-height:260px;width:100%;object-fit:contain;background:#111;">
                    </div>
                <?php else: ?>
                    <div class="col-12 text-muted">
                        <i class="bi bi-info-circle me-1"></i>No back image captured.
                    </div>
                <?php endif; ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
             <?php $__env->slot('title', null, []); ?> <i class="bi bi-sliders me-2"></i>Quality &amp; Confidence <?php $__env->endSlot(); ?>
            <div class="d-flex justify-content-between mb-2">
                <small class="text-muted">OCR Confidence</small>
                <strong><?php echo e($scan->ocrConfidence !== null ? number_format($scan->ocrConfidence, 1) . '%' : '—'); ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <small class="text-muted">Overall Quality</small>
                <strong><?php echo e($scan->qualityScore !== null ? number_format($scan->qualityScore, 1) . '%' : '—'); ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <small class="text-muted">Blur</small>
                <strong><?php echo e($scan->blurScore !== null ? number_format($scan->blurScore, 1) . '%' : '—'); ?></strong>
            </div>
            <div class="d-flex justify-content-between">
                <small class="text-muted">Glare</small>
                <strong><?php echo e($scan->glareScore !== null ? number_format($scan->glareScore, 1) . '%' : '—'); ?></strong>
            </div>

            <hr>

            <small class="text-muted">Raw OCR Text</small>
            <pre style="font-size:0.75rem;background:var(--surface-2);border:1px solid var(--border-subtle);padding:10px;border-radius:var(--radius-sm);max-height:160px;overflow:auto;white-space:pre-wrap;" class="mb-0"><?php echo e($scan->rawOcrText ?? 'No OCR text captured.'); ?></pre>
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

    
    <div class="col-lg-7">
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
             <?php $__env->slot('title', null, []); ?> <i class="bi bi-person-vcard me-2"></i>Extracted Information <?php $__env->endSlot(); ?>
             <?php $__env->slot('tools', null, []); ?> 
                <?php switch($scan->status):
                    case ('approved'): ?><?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'success','label' => 'Approved']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','label' => 'Approved']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?><?php break; ?>
                    <?php case ('rejected'): ?><?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'danger','label' => 'Rejected']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','label' => 'Rejected']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?><?php break; ?>
                    <?php case ('review'): ?><?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => 'warning','label' => 'Needs Review']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','label' => 'Needs Review']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $attributes = $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33)): ?>
<?php $component = $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33; ?>
<?php unset($__componentOriginal92e51077c3bdcbfa01c516c134fd0f33); ?>
<?php endif; ?><?php break; ?>
                    <?php default: ?><?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
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
<?php endif; ?><?php break; ?>
                <?php endswitch; ?>
             <?php $__env->endSlot(); ?>
            <form method="POST" action="<?php echo e(route('admin.id-scans.review', $scan->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Document Type</label>
                        <select name="documentType" class="form-select" disabled>
                            <?php $__currentLoopData = [
                                'national_id' => 'National ID (PhilSys)',
                                'drivers_license' => "Driver's License",
                                'passport' => 'Passport',
                                'umid' => 'UMID',
                                'philhealth_id' => 'PhilHealth ID',
                                'student_id' => 'Student ID',
                                'voters_id' => "Voter's ID",
                                'other' => 'Other Government ID',
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php echo e($scan->documentType === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Current Status</label>
                        <input type="text" class="form-control" value="<?php echo e(ucfirst($scan->status)); ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="fullName" class="form-control" value="<?php echo e($scan->fullName); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ID Number</label>
                        <input type="text" name="idNumber" class="form-control" value="<?php echo e($scan->idNumber); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="text" name="dateOfBirth" class="form-control" value="<?php echo e($scan->dateOfBirth); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiration Date</label>
                        <input type="text" name="expirationDate" class="form-control" value="<?php echo e($scan->expirationDate); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Link to Renter</label>
                        <select name="userId" class="form-select">
                            <option value="">— Not linked —</option>
                            <?php $__currentLoopData = $scan->user ? collect([$scan->user]) : collect(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linked): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($linked->id); ?>" selected><?php echo e($linked->name); ?> (<?php echo e($linked->email); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="form-text">Optionally link this ID to a renter account.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="<?php echo e($scan->address); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Review Notes</label>
                        <textarea name="reviewNotes" rows="2" class="form-control" placeholder="Notes for the record (optional)"><?php echo e($scan->reviewNotes); ?></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rejectToggle">
                            <label class="form-check-label" for="rejectToggle">Reject this scan — show rejection reason</label>
                        </div>
                    </div>
                    <div class="col-12" id="rejectionBox" style="display:none;">
                        <label class="form-label">Rejection Reason</label>
                        <select name="rejectionReason" class="form-select">
                            <option value="">— Select a reason —</option>
                            <option value="illegible">Document is illegible or blurry</option>
                            <option value="expired">Document is expired</option>
                            <option value="forged">Document appears to be forged or tampered</option>
                            <option value="mismatch">Details do not match the renter</option>
                            <option value="duplicate">Duplicate ID already on file</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <input type="hidden" name="editFields" value="1">
                        <input type="hidden" name="status" id="reviewStatusInput">
                        <div class="d-flex gap-2">
                            <button type="submit" name="status" value="approved" class="btn-admin btn-admin--primary"
                                    data-confirm="Approve this ID scan?" data-confirm-title="Approve ID scan" data-confirm-ok="Approve" data-confirm-danger="false">
                                <i class="bi bi-check-lg me-1"></i>Approve &amp; Verify
                            </button>
                            <button type="submit" name="status" value="rejected" class="btn-admin btn-admin--secondary text-danger"
                                    data-confirm="Reject this ID scan?" data-confirm-title="Reject ID scan" data-confirm-ok="Reject">
                                <i class="bi bi-x-lg me-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
             <?php $__env->slot('title', null, []); ?> <i class="bi bi-clipboard-check me-2"></i>Duplicate &amp; Return Check <?php $__env->endSlot(); ?>
            <?php
                $duplicate = $scan->idNumber
                    ? app(\App\Services\IdScanService::class)->findDuplicate($scan->idNumber, ['pending', 'review', 'approved'])
                    : null;
            ?>
            <?php if($duplicate && $duplicate->id !== $scan->id): ?>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    An ID with the same number was previously scanned
                    <a href="<?php echo e(route('admin.id-scans.show', $duplicate->id)); ?>">#<?php echo e($duplicate->id); ?></a>
                    (<?php echo e($duplicate->status); ?>). Verify this is not a duplicate registration.
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">
                    <i class="bi bi-check-circle text-success me-1"></i>
                    No conflicting scan found for this ID number.
                    <?php if($scan->userId): ?>
                        Returning renter: <strong><?php echo e($scan->user->name); ?></strong> can be recognized automatically on future scans.
                    <?php endif; ?>
                </p>
            <?php endif; ?>
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
    (function() {
        const rejectToggle = document.getElementById('rejectToggle');
        const rejectionBox = document.getElementById('rejectionBox');

        if (rejectToggle) {
            rejectToggle.addEventListener('change', function() {
                rejectionBox.style.display = this.checked ? 'block' : 'none';
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('button[data-confirm][name="status"]');
            if (!btn) return;
            const statusInput = document.getElementById('reviewStatusInput');
            if (statusInput) statusInput.value = btn.value;
        });
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\idscans\show.blade.php ENDPATH**/ ?>