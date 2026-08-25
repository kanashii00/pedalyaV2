

<?php $__env->startSection('title', 'Payment Details — <?php echo e($payment->paymentReference); ?>'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .detail-row { display: flex; margin-bottom: 8px; }
    .detail-label { min-width: 180px; font-weight: 600; color: var(--text-2); font-size: 13px; }
    .detail-value { flex: 1; font-family: 'Inter', monospace; font-variant-numeric: tabular-nums; }
    .payment-method-badge { font-size: 0.75rem; padding: 4px 10px; border-radius: 999px; font-weight: 600; }
    .status-badge { font-size: 0.75rem; padding: 4px 10px; border-radius: 999px; font-weight: 600; }
    .json-view { max-height: 300px; overflow: auto; background: var(--surface-3); border-radius: 8px; padding: 12px; font-size: 12px; font-family: monospace; white-space: pre-wrap; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Payment Details</h1>
    <p><?php echo e($payment->paymentReference); ?> · <?php echo e($payment->getStatusLabel()); ?></p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
    <div class="d-flex gap-2">
        <?php if($payment->status === 'paid'): ?>
            <a href="<?php echo e(route('admin.payments.receipt', $payment)); ?>" class="btn-admin btn-admin--primary" target="_blank">
                <i class="bi bi-receipt me-1"></i>View Receipt
            </a>
            <button class="btn-admin btn-admin--secondary" onclick="downloadReceipt()">
                <i class="bi bi-download me-1"></i>Download PDF
            </button>
        <?php endif; ?>
        <?php if($payment->status === 'pending' || $payment->status === 'processing'): ?>
            <a href="<?php echo e(route('admin.payments.verify', $payment)); ?>" class="btn-admin btn-admin--secondary">
                <i class="bi bi-arrow-clockwise me-1"></i>Verify Status
            </a>
        <?php endif; ?>
        <?php if($payment->status === 'paid' && !$payment->refund): ?>
            <a href="<?php echo e(route('admin.refunds.create', $payment)); ?>" class="btn-admin btn-admin--warning">
                <i class="bi bi-arrow-return-left me-1"></i>Refund
            </a>
        <?php endif; ?>
        <a href="<?php echo e(route('admin.payments.index')); ?>" class="btn-admin btn-admin--ghost">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-4">
    
    <div class="col-12 col-xl-4">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Payment Overview']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payment Overview']); ?>
            <div class="detail-row">
                <span class="detail-label">Reference</span>
                <span class="detail-value fw-semibold"><?php echo e($payment->paymentReference); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">PayMongo ID</span>
                <span class="detail-value text-truncate" style="max-width: 200px;"><?php echo e($payment->paymongoPaymentId ?? '—'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <span class="status-badge" style="background: var(--<?php echo e($payment->getStatusColor()); ?>-soft); color: var(--<?php echo e($payment->getStatusColor()); ?>);">
                        <?php echo e($payment->getStatusLabel()); ?>

                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value">
                    <span class="payment-method-badge" style="background: <?php echo e(match($payment->paymentMethod) {
                        'gcash' => '#007AFF22', 'maya' => '#00B8E622', 'grabpay' => '#00AA1322',
                        'card' => '#6366F122', 'online_banking' => '#0EA5E922', default => 'var(--surface-3)'); ?>; color: <?php echo e(match($payment->paymentMethod) {
                        'gcash' => '#007AFF', 'maya' => '#00B8E6', 'grabpay' => '#00AA13',
                        'card' => '#6366F1', 'online_banking' => '#0EA5E9', default => 'var(--text-3)'); ?>; }}">
                        <?php echo e($payment->getPaymentMethodLabel()); ?>

                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Base Amount</span>
                <span class="detail-value text-success fw-semibold">₱<?php echo e(number_format($payment->amount, 2)); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Convenience Fee</span>
                <span class="detail-value text-warning">₱<?php echo e(number_format($payment->convenienceFee, 2)); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Amount</span>
                <span class="detail-value fw-bold" style="font-size: 1.1rem;">₱<?php echo e(number_format($payment->totalAmount, 2)); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Currency</span>
                <span class="detail-value"><?php echo e($payment->currency); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value"><?php echo e($payment->created_at->format('M j, Y g:i A')); ?></span>
            </div>
            <?php if($payment->paidAt): ?>
                <div class="detail-row">
                    <span class="detail-label">Paid At</span>
                    <span class="detail-value text-success"><?php echo e($payment->paidAt->format('M j, Y g:i A')); ?></span>
                </div>
            <?php endif; ?>
            <?php if($payment->expiredAt): ?>
                <div class="detail-row">
                    <span class="detail-label">Expired At</span>
                    <span class="detail-value text-danger"><?php echo e($payment->expiredAt->format('M j, Y g:i A')); ?></span>
                </div>
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

        <?php if($payment->billingInfo): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Billing Information','class' => 'mt-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Billing Information','class' => 'mt-4']); ?>
                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value"><?php echo e($payment->billingInfo['name'] ?? '—'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value"><?php echo e($payment->billingInfo['email'] ?? '—'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value"><?php echo e($payment->billingInfo['phone'] ?? '—'); ?></span>
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
        <?php endif; ?>
    </div>

    <div class="col-12 col-xl-8">
        
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Customer']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Customer']); ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="user-avatar-lg bg-success"><?php echo e(strtoupper($payment->user->name[0])); ?></div>
                        <div>
                            <h5 class="mb-1"><?php echo e($payment->user->name); ?></h5>
                            <p class="text-muted mb-0"><?php echo e($payment->user->email); ?></p>
                        </div>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Verified</span>
                        <span class="detail-value">
                            <?php if($payment->user->verified): ?>
                                <span class="badge-admin badge-admin--success">Verified</span>
                            <?php else: ?>
                                <span class="badge-admin badge-admin--warning">Pending</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value"><?php echo e($payment->user->phone ?? '—'); ?></span>
                    </div>
                    <a href="<?php echo e(route('admin.riders.show', $payment->user)); ?>" class="btn-admin btn-admin--ghost btn-admin--sm mt-2">
                        <i class="bi bi-person me-1"></i>View Profile
                    </a>
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

            <div class="col-12 col-md-6">
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Bicycle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Bicycle']); ?>
                    <?php if($payment->bicycle): ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="kpi__icon bg-primary"><i class="bi bi-bicycle"></i></div>
                            <div>
                                <h5 class="mb-1"><?php echo e($payment->bicycle->name); ?></h5>
                                <p class="text-muted mb-0"><?php echo e($payment->bicycle->serialNumber); ?></p>
                            </div>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span class="badge-admin badge-admin--<?php echo e(match($payment->bicycle->status) {
                                    'available' => 'success', 'rented' => 'primary',
                                    'maintenance' => 'warning', 'locked' => 'danger',
                                    default => 'secondary'); ?> }}"><?php echo e(ucfirst($payment->bicycle->status)); ?></span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Battery</span>
                            <span class="detail-value"><?php echo e($payment->bicycle->batteryLevel); ?>%</span>
                        </div>
                        <a href="<?php echo e(route('admin.bicycles.show', $payment->bicycle)); ?>" class="btn-admin btn-admin--ghost btn-admin--sm mt-2">
                            <i class="bi bi-bicycle me-1"></i>View Bicycle
                        </a>
                    <?php else: ?>
                        <p class="text-muted">No bicycle assigned</p>
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

        
        <?php if($payment->rental): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Rental Information','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Rental Information','class' => 'mb-4']); ?>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="detail-row">
                            <span class="detail-label">Rental ID</span>
                            <span class="detail-value"><a href="<?php echo e(route('admin.rentals.show', $payment->rental)); ?>"><?php echo e($payment->rental->id); ?></a></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span class="badge-admin badge-admin--<?php echo e(match($payment->rental->status) {
                                    'active' => 'success', 'pending' => 'warning',
                                    'completed' => 'info', 'cancelled' => 'danger', default => 'secondary'); ?> }}"><?php echo e(ucfirst($payment->rental->status)); ?></span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Started</span>
                            <span class="detail-value"><?php echo e($payment->rental->startedAt?->format('M j, Y g:i A') ?? '—'); ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-row">
                            <span class="detail-label">Ends</span>
                            <span class="detail-value"><?php echo e($payment->rental->endsAt?->format('M j, Y g:i A') ?? '—'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Fee</span>
                            <span class="detail-value fw-semibold text-success">₱<?php echo e(number_format($payment->rental->totalFee, 2)); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Duration</span>
                            <span class="detail-value"><?php echo e($payment->rental->startedAt && $payment->rental->endsAt ? $payment->rental->startedAt->diffInHours($payment->rental->endsAt) : '—'); ?> hours</span>
                        </div>
                    </div>
                </div>
                <a href="<?php echo e(route('admin.rentals.show', $payment->rental)); ?>" class="btn-admin btn-admin--secondary btn-admin--sm">
                    <i class="bi bi-key me-1"></i>View Rental Details
                </a>
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
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
                <p class="text-muted mb-0">No rental created yet. Payment must be completed first.</p>
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
        <?php endif; ?>

        
        <?php if($payment->paymentDetails): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'PayMongo Response','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'PayMongo Response','class' => 'mb-4']); ?>
                <div class="json-view"><?php echo e(json_encode($payment->paymentDetails, JSON_PRETTY_PRINT)); ?></div>
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
        <?php endif; ?>

        
        <?php if($payment->refund): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Refund Information']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Refund Information']); ?>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="detail-row">
                            <span class="detail-label">Refund Reference</span>
                            <span class="detail-value"><?php echo e($payment->refund->refundReference); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Amount</span>
                            <span class="detail-value text-warning">₱<?php echo e(number_format($payment->refund->amount, 2)); ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span class="badge-admin badge-admin--<?php echo e($payment->refund->getStatusColor()); ?>">
                                    <?php echo e($payment->refund->getStatusLabel()); ?>

                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reason</span>
                            <span class="detail-value"><?php echo e($payment->refund->getReasonLabel()); ?></span>
                        </div>
                    </div>
                </div>
                <a href="<?php echo e(route('admin.refunds.show', $payment->refund)); ?>" class="btn-admin btn-admin--ghost btn-admin--sm mt-2">
                    <i class="bi bi-arrow-return-left me-1"></i>View Refund Details
                </a>
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
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    function downloadReceipt() {
        window.open('<?php echo e(route('admin.payments.receipt', $payment)); ?>?download=1', '_blank');
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\payments\show.blade.php ENDPATH**/ ?>