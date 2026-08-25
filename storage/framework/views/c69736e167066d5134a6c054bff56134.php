

<?php $__env->startSection('title', 'Payment Successful'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Payment Successful</h1>
    <p>Your payment has been processed successfully</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-center">
    <div class="col-12 col-md-8 col-lg-6">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'text-center py-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-center py-5']); ?>
            <div class="kpi__icon mx-auto mb-4" style="width: 80px; height: 80px; font-size: 2.5rem; background: var(--success-soft); color: var(--success);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="mb-2">Payment Confirmed!</h2>
            <p class="text-muted mb-4">Your bicycle rental payment has been successfully processed.</p>

            <div class="row g-3 mb-4 text-start">
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Payment Reference</div>
                        <div class="fw-semibold"><?php echo e($payment->paymentReference); ?></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Amount Paid</div>
                        <div class="fw-bold text-success">₱<?php echo e(number_format($payment->totalAmount, 2)); ?></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Payment Method</div>
                        <div class="fw-semibold"><?php echo e($payment->getPaymentMethodLabel()); ?></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background: var(--surface-3);">
                        <div class="text-muted small">Bicycle</div>
                        <div class="fw-semibold"><?php echo e($payment->bicycle->name ?? '—'); ?></div>
                    </div>
                </div>
            </div>

            <?php if($payment->rental): ?>
                <div class="alert alert-success mb-4">
                    <i class="bi bi-key me-2"></i>
                    <strong>Rental Activated!</strong>
                    <div class="mt-2">
                        <a href="<?php echo e(route('admin.rentals.show', $payment->rental)); ?>" class="btn-admin btn-admin--success btn-admin--sm">
                            <i class="bi bi-key me-1"></i>View Rental
                        </a>
                        <a href="<?php echo e(route('admin.payments.receipt', $payment)); ?>" class="btn-admin btn-admin--secondary btn-admin--sm ms-2" target="_blank">
                            <i class="bi bi-receipt me-1"></i>View Receipt
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2 justify-content-center">
                <a href="<?php echo e(route('admin.payments.index')); ?>" class="btn-admin btn-admin--primary">
                    <i class="bi bi-credit-card me-1"></i>All Payments
                </a>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn-admin btn-admin--secondary">
                    <i class="bi bi-house-door me-1"></i>Dashboard
                </a>
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
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\payments\success.blade.php ENDPATH**/ ?>