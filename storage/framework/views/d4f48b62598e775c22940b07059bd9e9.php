

<?php $__env->startSection('title', 'Payment Management'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .payment-status-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 999px;
        font-weight: 600;
    }
    .payment-method-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    .amount-cell { font-family: 'Inter', monospace; font-variant-numeric: tabular-nums; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Payment Management</h1>
    <p>Monitor and manage all PayMongo payment transactions</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('admin.payments.create')); ?>" class="btn-admin btn-admin--primary">
            <i class="bi bi-plus-circle me-1"></i>New Payment
        </a>
        <a href="<?php echo e(route('admin.payments.index')); ?>?status=paid" class="btn-admin btn-admin--secondary btn-admin--sm">
            <i class="bi bi-filter me-1"></i>Paid Only
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Total Payments','value' => ''.e($stats['total']).'','icon' => 'bi-credit-card','color' => 'var(--brand)','foot' => 'all time']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Payments','value' => ''.e($stats['total']).'','icon' => 'bi-credit-card','color' => 'var(--brand)','foot' => 'all time']); ?>
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
    <div class="col-6 col-md-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Successful','value' => ''.e($stats['paid']).'','icon' => 'bi-check-circle','color' => 'var(--success)','foot' => 'completed']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Successful','value' => ''.e($stats['paid']).'','icon' => 'bi-check-circle','color' => 'var(--success)','foot' => 'completed']); ?>
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
    <div class="col-6 col-md-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Pending','value' => ''.e($stats['pending']).'','icon' => 'bi-clock','color' => 'var(--warning)','foot' => 'awaiting payment']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pending','value' => ''.e($stats['pending']).'','icon' => 'bi-clock','color' => 'var(--warning)','foot' => 'awaiting payment']); ?>
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
    <div class="col-6 col-md-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Failed','value' => ''.e($stats['failed']).'','icon' => 'bi-x-circle','color' => 'var(--danger)','foot' => 'failed/expired']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Failed','value' => ''.e($stats['failed']).'','icon' => 'bi-x-circle','color' => 'var(--danger)','foot' => 'failed/expired']); ?>
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

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Total Revenue','value' => '₱'.e(number_format($stats['totalRevenue'], 0)).'','icon' => 'bi-cash-stack','color' => 'var(--success)','foot' => 'lifetime']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Revenue','value' => '₱'.e(number_format($stats['totalRevenue'], 0)).'','icon' => 'bi-cash-stack','color' => 'var(--success)','foot' => 'lifetime']); ?>
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
    <div class="col-6 col-md-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Today\'s Revenue','value' => '₱'.e(number_format($stats['todayRevenue'], 0)).'','icon' => 'bi-calendar-check','color' => 'var(--accent)','foot' => 'today only']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Today\'s Revenue','value' => '₱'.e(number_format($stats['todayRevenue'], 0)).'','icon' => 'bi-calendar-check','color' => 'var(--accent)','foot' => 'today only']); ?>
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
    <div class="col-6 col-md-3">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Success Rate','value' => ''.e($stats['total'] > 0 ? round(($stats['paid'] / $stats['total']) * 100, 1) : 0).'%','icon' => 'bi-graph-up','color' => 'var(--info)','foot' => 'paid / total']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Success Rate','value' => ''.e($stats['total'] > 0 ? round(($stats['paid'] / $stats['total']) * 100, 1) : 0).'%','icon' => 'bi-graph-up','color' => 'var(--info)','foot' => 'paid / total']); ?>
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


<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Payments','flush' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payments','flush' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
     <?php $__env->slot('tools', null, []); ?> 
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end" style="max-width: 100%;">
            <div class="flex-grow-1" style="min-width: 200px;">
                <label class="form-label form-label-sm">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Reference, ID, customer..." value="<?php echo e(request('search')); ?>">
                </div>
            </div>
            <div style="min-width: 150px;">
                <label class="form-label form-label-sm">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <?php $__currentLoopData = ['pending', 'processing', 'paid', 'failed', 'expired', 'cancelled', 'refunded']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e(request('status') === $s ? 'selected' : ''); ?>><?php echo e(ucfirst($s)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div style="min-width: 150px;">
                <label class="form-label form-label-sm">Method</label>
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">All Methods</option>
                    <?php $__currentLoopData = ['gcash', 'maya', 'grabpay', 'card', 'online_banking']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m); ?>" <?php echo e(request('payment_method') === $m ? 'selected' : ''); ?>><?php echo e(ucfirst(str_replace('_', ' ', $m))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div style="min-width: 160px;">
                <label class="form-label form-label-sm">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo e(request('date_from')); ?>">
            </div>
            <div style="min-width: 160px;">
                <label class="form-label form-label-sm">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo e(request('date_to')); ?>">
            </div>
            <button type="submit" class="btn-admin btn-admin--primary btn-admin--sm">Filter</button>
            <a href="<?php echo e(route('admin.payments.index')); ?>" class="btn-admin btn-admin--secondary btn-admin--sm">Clear</a>
        </form>
     <?php $__env->endSlot(); ?>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Bicycle</th>
                    <th>Method</th>
                    <th class="amount-cell">Amount</th>
                    <th>Status</th>
                    <th>Rental</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="cell-title">
                            <strong><?php echo e($payment->paymentReference); ?></strong>
                            <?php if($payment->paymongoPaymentId): ?>
                                <div class="cell-sub"><?php echo e(Str::limit($payment->paymongoPaymentId, 20)); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-sm"><?php echo e(strtoupper($payment->user->name[0])); ?></div>
                                <div>
                                    <div class="fw-semibold text-truncate" style="max-width: 180px;"><?php echo e($payment->user->name); ?></div>
                                    <div class="cell-sub"><?php echo e($payment->user->email); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if($payment->bicycle): ?>
                                <div class="fw-semibold"><?php echo e($payment->bicycle->name); ?></div>
                                <div class="cell-sub"><?php echo e($payment->bicycle->serialNumber); ?></div>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                                $pmBg = match($payment->paymentMethod) {
                                    'gcash' => '#007AFF22', 'maya' => '#00B8E622', 'grabpay' => '#00AA1322',
                                    'card' => '#6366F122', 'online_banking' => '#0EA5E922', default => 'var(--surface-3)'
                                };
                                $pmColor = match($payment->paymentMethod) {
                                    'gcash' => '#007AFF', 'maya' => '#00B8E6', 'grabpay' => '#00AA13',
                                    'card' => '#6366F1', 'online_banking' => '#0EA5E9', default => 'var(--text-3)'
                                };
                                $pmIcon = match($payment->paymentMethod) {
                                    'gcash' => 'bi-phone', 'maya' => 'bi-phone', 'grabpay' => 'bi-bag',
                                    'card' => 'bi-credit-card', 'online_banking' => 'bi-bank',
                                    default => 'bi-currency-exchange'
                                };
                            ?>
                            <span class="payment-method-icon" style="background: <?php echo e($pmBg); ?>; color: <?php echo e($pmColor); ?>;">
                                <i class="bi <?php echo e($pmIcon); ?>"></i>
                            </span>
                            <span class="ms-2 fw-medium"><?php echo e($payment->getPaymentMethodLabel()); ?></span>
                        </td>
                        <td class="amount-cell fw-semibold">₱<?php echo e(number_format($payment->totalAmount, 2)); ?>

                            <?php if($payment->convenienceFee > 0): ?>
                                <div class="cell-sub">Fee: ₱<?php echo e(number_format($payment->convenienceFee, 2)); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="payment-status-badge" style="background: var(--<?php echo e($payment->getStatusColor()); ?>-soft); color: var(--<?php echo e($payment->getStatusColor()); ?>);">
                                <?php echo e($payment->getStatusLabel()); ?>

                            </span>
                        </td>
                        <td>
                            <?php if($payment->rental): ?>
                                <a href="<?php echo e(route('admin.rentals.show', $payment->rental)); ?>" class="text-decoration-none">
                                    <i class="bi bi-key me-1"></i><?php echo e($payment->rental->id); ?>

                                    <span class="badge-admin badge-admin--success badge-admin--plain ms-1"><?php echo e(ucfirst($payment->rental->status)); ?></span>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="cell-sub"><?php echo e($payment->created_at->format('M j, Y g:i A')); ?>

                            <?php if($payment->paidAt): ?>
                                <div class="text-success">Paid: <?php echo e($payment->paidAt->format('g:i A')); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?php echo e(route('admin.payments.show', $payment)); ?>" class="btn-admin btn-admin--ghost btn-admin--sm" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if($payment->status === 'paid'): ?>
                                    <a href="<?php echo e(route('admin.payments.receipt', $payment)); ?>" class="btn-admin btn-admin--ghost btn-admin--sm" title="View Receipt" target="_blank">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if($payment->status === 'pending' || $payment->status === 'processing'): ?>
                                    <a href="<?php echo e(route('admin.payments.verify', $payment)); ?>" class="btn-admin btn-admin--ghost btn-admin--sm" title="Verify with PayMongo">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9"><?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-credit-card','title' => 'No payments found','message' => 'No payment transactions match your filters.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-credit-card','title' => 'No payments found','message' => 'No payment transactions match your filters.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-table-foot">
        <div><?php echo e($payments->total()); ?> payments</div>
        <?php echo e($payments->links()); ?>

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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\payments\index.blade.php ENDPATH**/ ?>