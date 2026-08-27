

<?php $__env->startSection('title', 'Dashboard Overview — Pedalya Admin'); ?>

<?php $__env->startSection('actions'); ?>
    <a href="<?php echo e(route('admin.bicycles.index')); ?>?action=add" class="btn-admin btn-admin--secondary btn-admin--sm">
        <i class="bi bi-plus-circle"></i> Add Bicycle
    </a>
    <button class="btn-admin btn-admin--primary btn-admin--sm" onclick="window.PedalyaModal.open('quickRentalModal')">
        <i class="bi bi-key"></i> Quick Rental
    </button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h3 style="font-size: 17px; margin: 0;">Good <?php echo e(now()->format('A') === 'AM' ? 'morning' : 'afternoon'); ?>, <?php echo e(auth()->user()->name); ?> 👋</h3>
        <p style="color: var(--text-3); font-size: 13px; margin: 3px 0 0;">
            Here's what's happening with Pedalya at Azuela Cove today.
        </p>
    </div>
    <div class="admin-clock d-flex align-items-center gap-2" style="color: var(--text-3); font-size: 13px;">
        <i class="bi bi-calendar3"></i><?php echo e(now()->format('l, F j, Y')); ?>

    </div>
</div>


<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Active Rentals','value' => ''.e($stats['rentals']['active'] ?? 0).'','icon' => 'bi-play-circle','color' => 'var(--accent)','foot' => 'in progress now','link' => ''.e(route('admin.rentals.index')).'?filter=active']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Active Rentals','value' => ''.e($stats['rentals']['active'] ?? 0).'','icon' => 'bi-play-circle','color' => 'var(--accent)','foot' => 'in progress now','link' => ''.e(route('admin.rentals.index')).'?filter=active']); ?>
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
    <div class="col-6 col-md-4 col-xl-2">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Available Bicycles','value' => ''.e($stats['bicycles']['available'] ?? 0).'','icon' => 'bi-bicycle','color' => 'var(--success)','foot' => 'of '.e($stats['bicycles']['total'] ?? 0).' total','link' => ''.e(route('admin.bicycles.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Available Bicycles','value' => ''.e($stats['bicycles']['available'] ?? 0).'','icon' => 'bi-bicycle','color' => 'var(--success)','foot' => 'of '.e($stats['bicycles']['total'] ?? 0).' total','link' => ''.e(route('admin.bicycles.index')).'']); ?>
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
    <div class="col-6 col-md-4 col-xl-2">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Live GPS Devices','value' => ''.e($stats['devices']['gpsOnline'] ?? 0).'','icon' => 'bi-geo-alt','color' => 'var(--info)','foot' => 'active GPS fix','link' => ''.e(route('admin.monitoring.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Live GPS Devices','value' => ''.e($stats['devices']['gpsOnline'] ?? 0).'','icon' => 'bi-geo-alt','color' => 'var(--info)','foot' => 'active GPS fix','link' => ''.e(route('admin.monitoring.index')).'']); ?>
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
    <div class="col-6 col-md-4 col-xl-2">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Active Alerts','value' => ''.e($stats['alerts']['activeAlerts'] ?? 0).'','icon' => 'bi-shield-exclamation','color' => 'var(--danger)','foot' => 'Theft + Accident combined','link' => ''.e(route('admin.theft-alerts.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Active Alerts','value' => ''.e($stats['alerts']['activeAlerts'] ?? 0).'','icon' => 'bi-shield-exclamation','color' => 'var(--danger)','foot' => 'Theft + Accident combined','link' => ''.e(route('admin.theft-alerts.index')).'']); ?>
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
    <div class="col-6 col-md-4 col-xl-2">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Today\'s Revenue','value' => '₱'.e(number_format($stats['revenue']['today'] ?? 0, 0)).'','icon' => 'bi-cash-stack','color' => 'var(--success)','foot' => 'completed today','link' => ''.e(route('admin.reports.index')).'?tab=revenue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Today\'s Revenue','value' => '₱'.e(number_format($stats['revenue']['today'] ?? 0, 0)).'','icon' => 'bi-cash-stack','color' => 'var(--success)','foot' => 'completed today','link' => ''.e(route('admin.reports.index')).'?tab=revenue']); ?>
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
    <div class="col-6 col-md-4 col-xl-2">
        <?php if (isset($component)) { $__componentOriginal38ba340f24ecf72ab18e0a0bbbda933e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38ba340f24ecf72ab18e0a0bbbda933e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi','data' => ['title' => 'Verified Customers','value' => ''.e($stats['users']['verified'] ?? 0).'','icon' => 'bi-patch-check','color' => 'var(--success)','foot' => 'ID verified','link' => ''.e(route('admin.riders.index')).'?filter=verified']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Verified Customers','value' => ''.e($stats['users']['verified'] ?? 0).'','icon' => 'bi-patch-check','color' => 'var(--success)','foot' => 'ID verified','link' => ''.e(route('admin.riders.index')).'?filter=verified']); ?>
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


<div id="analytics" class="d-flex align-items-center justify-content-between mb-3 mt-2">
    <h4 style="font-size: 15px; font-weight: 700; margin: 0;"><i class="bi bi-graph-up-arrow me-2" style="color: var(--brand);"></i>Analytics</h4>
    <button class="btn-admin btn-admin--ghost btn-admin--sm" onclick="refreshCharts()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-8">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Revenue & Rental Trends','sub' => 'Last 12 months']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Revenue & Rental Trends','sub' => 'Last 12 months']); ?>
            <div class="chart-box chart-box--lg"><canvas id="trendsChart"></canvas></div>
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
    <div class="col-12 col-xl-4">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Weekly Rentals','sub' => 'Last 7 days']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Weekly Rentals','sub' => 'Last 7 days']); ?>
            <div class="chart-box"><canvas id="weeklyChart"></canvas></div>
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

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Peak Rental Hours','sub' => 'Last 7 days']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Peak Rental Hours','sub' => 'Last 7 days']); ?>
            <div class="chart-box"><canvas id="peakChart"></canvas></div>
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
    <div class="col-12 col-lg-3">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Fleet Status','sub' => 'Live distribution']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Fleet Status','sub' => 'Live distribution']); ?>
            <div class="chart-box chart-box--sm"><canvas id="fleetChart"></canvas></div>
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
    <div class="col-12 col-lg-3">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Battery Health','sub' => 'Across fleet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Battery Health','sub' => 'Across fleet']); ?>
            <div class="chart-box chart-box--sm"><canvas id="batteryChart"></canvas></div>
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

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-7">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Incident Trends','sub' => 'Theft vs accidents, last 12 months']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Incident Trends','sub' => 'Theft vs accidents, last 12 months']); ?>
            <div class="chart-box"><canvas id="incidentChart"></canvas></div>
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
    <div class="col-12 col-xl-5">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Device Health','sub' => 'Connectivity overview']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Device Health','sub' => 'Connectivity overview']); ?>
            <div class="d-grid gap-3">
                <?php
                    $totalDevices = max($stats['devices']['total'] ?? 0, 1);
                    $gpsPct = round((($stats['devices']['gpsOnline'] ?? 0) / $totalDevices) * 100);
                    $iotPct = round((($stats['devices']['iotOnline'] ?? 0) / $totalDevices) * 100);
                    $batteryOk = max($stats['battery']['good'] ?? 0, 0) + max($stats['battery']['full'] ?? 0, 0);
                    $batteryPct = round(($batteryOk / $totalDevices) * 100);
                ?>
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size: 12.5px;">
                        <span style="color: var(--text-2); font-weight: 600;"><i class="bi bi-geo-alt me-1" style="color: var(--info);"></i>GPS Connectivity</span>
                        <span style="color: var(--text-1); font-weight: 700;"><?php echo e($gpsPct); ?>%</span>
                    </div>
                    <div class="admin-progress"><div class="admin-progress__bar" data-progress="<?php echo e($gpsPct); ?>" style="width: <?php echo e($gpsPct); ?>%; background: var(--info);"></div></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size: 12.5px;">
                        <span style="color: var(--text-2); font-weight: 600;"><i class="bi bi-cpu me-1" style="color: var(--purple);"></i>IoT Heartbeat</span>
                        <span style="color: var(--text-1); font-weight: 700;"><?php echo e($iotPct); ?>%</span>
                    </div>
                    <div class="admin-progress"><div class="admin-progress__bar" data-progress="<?php echo e($iotPct); ?>" style="width: <?php echo e($iotPct); ?>%; background: var(--purple);"></div></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size: 12.5px;">
                        <span style="color: var(--text-2); font-weight: 600;"><i class="bi bi-battery-full me-1" style="color: var(--success);"></i>Healthy Battery</span>
                        <span style="color: var(--text-1); font-weight: 700;"><?php echo e($batteryPct); ?>%</span>
                    </div>
                    <div class="admin-progress"><div class="admin-progress__bar" data-progress="<?php echo e($batteryPct); ?>" style="width: <?php echo e($batteryPct); ?>%; background: var(--success);"></div></div>
                </div>
                <div class="mt-2 pt-3 border-top" style="border-color: var(--border-subtle) !important;">
                    <div class="d-flex justify-content-between mb-1" style="font-size: 12.5px;">
                        <span style="color: var(--text-2); font-weight: 600;">Battery breakdown</span>
                        <span style="color: var(--text-3);">good / fair / low</span>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <span class="badge-admin badge-admin--success badge-admin--plain"><?php echo e($stats['battery']['full'] ?? 0); ?> full</span>
                        <span class="badge-admin badge-admin--brand badge-admin--plain"><?php echo e($stats['battery']['good'] ?? 0); ?> good</span>
                        <span class="badge-admin badge-admin--warning badge-admin--plain"><?php echo e($stats['battery']['mid'] ?? 0); ?> fair</span>
                        <span class="badge-admin badge-admin--danger badge-admin--plain"><?php echo e($stats['battery']['low'] ?? 0); ?> low</span>
                    </div>
                </div>
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


<div class="row g-3">
    <div class="col-12 col-lg-6">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Recent Rentals','sub' => 'Latest activity','flush' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Rentals','sub' => 'Latest activity','flush' => true]); ?>
            <?php $__empty_1 = true; $__currentLoopData = $recentRentals ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom" style="border-color: var(--border-subtle) !important;">
                    <div class="kpi__icon" style="width: 36px; height: 36px; font-size: 15px; background: var(--brand-soft); color: var(--brand-strong);">
                        <i class="bi bi-bicycle"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-1);" class="text-truncate"><?php echo e($r->riderName ?? 'Rider'); ?> → <?php echo e($r->bicycleName ?? $r->bicycleId); ?></div>
                        <div style="font-size: 12px; color: var(--text-3);"><?php echo e($r->created_at->format('M j, g:i A')); ?> · <?php echo e($r->status); ?></div>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => match($r->status) { 'active' => 'success', 'pending' => 'warning', 'completed' => 'info', 'cancelled' => 'neutral', default => 'neutral' },'label' => $r->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($r->status) { 'active' => 'success', 'pending' => 'warning', 'completed' => 'info', 'cancelled' => 'neutral', default => 'neutral' }),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->status)]); ?>
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
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-key','title' => 'No rentals yet','message' => 'Rental activity will appear here as customers start riding.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-key','title' => 'No rentals yet','message' => 'Rental activity will appear here as customers start riding.']); ?>
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

    <div class="col-12 col-lg-6">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Incident Feed','sub' => 'Theft & accident alerts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Incident Feed','sub' => 'Theft & accident alerts']); ?>
            <?php $__empty_1 = true; $__currentLoopData = $recentIncidents ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="admin-timeline__item mb-3" style="padding-bottom: 14px;">
                    <span class="admin-timeline__dot <?php echo e($inc->type === 'theft' ? 'admin-timeline__dot--danger' : 'admin-timeline__dot--warning'); ?>"></span>
                    <div class="admin-timeline__time"><?php echo e($inc->created_at->diffForHumans()); ?></div>
                    <div class="admin-timeline__title text-capitalize"><?php echo e($inc->type); ?> detected · <?php echo e($inc->bicycle->name ?? 'Bike #' . $inc->bicycleId); ?></div>
                    <div style="font-size: 12px; color: var(--text-3);">Severity: <?php echo e($inc->severity); ?> · <?php echo e($inc->acknowledged ? 'Acknowledged' : 'Unacknowledged'); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-shield-check','title' => 'No incidents','message' => 'All clear. No theft or accident alerts right now.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-shield-check','title' => 'No incidents','message' => 'All clear. No theft or accident alerts right now.']); ?>
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

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Low Battery Alerts','sub' => 'Units at 20% or below','flush' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Low Battery Alerts','sub' => 'Units at 20% or below','flush' => true]); ?>
            <?php $__empty_1 = true; $__currentLoopData = $lowBatteryBicycles ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bike): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom" style="border-color: var(--border-subtle) !important;">
                    <div class="kpi__icon" style="width: 36px; height: 36px; font-size: 15px; background: var(--warning-soft); color: var(--warning);">
                        <i class="bi bi-battery-quarter"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-1);"><?php echo e($bike->name); ?></div>
                        <div style="font-size: 12px; color: var(--text-3);"><?php echo e($bike->serialNumber); ?></div>
                    </div>
                    <span class="badge-admin badge-admin--<?php echo e($bike->batteryLevel <= 15 ? 'danger' : 'warning'); ?> badge-admin--plain"><?php echo e($bike->batteryLevel); ?>%</span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-check-circle','title' => 'All batteries healthy','message' => 'No bicycles below 20% battery.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-check-circle','title' => 'All batteries healthy','message' => 'No bicycles below 20% battery.']); ?>
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

    <div class="col-12 col-lg-6">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => 'Maintenance Schedule','sub' => 'Upcoming and in-progress work','flush' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Maintenance Schedule','sub' => 'Upcoming and in-progress work','flush' => true]); ?>
            <?php $__empty_1 = true; $__currentLoopData = $upcomingMaintenance ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom" style="border-color: var(--border-subtle) !important;">
                    <div class="kpi__icon" style="width: 36px; height: 36px; font-size: 15px; background: var(--danger-soft); color: var(--danger);">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-1);"><?php echo e($m->bicycleName ?? 'Bike #' . $m->bicycleId); ?></div>
                        <div style="font-size: 12px; color: var(--text-3);"><?php echo e($m->type); ?> · <?php echo e($m->scheduledDate?->format('M j')); ?></div>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal92e51077c3bdcbfa01c516c134fd0f33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal92e51077c3bdcbfa01c516c134fd0f33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.badge','data' => ['type' => match($m->status) { 'scheduled' => 'warning', 'in_progress' => 'info', 'completed' => 'success', default => 'neutral' },'label' => str_replace('_', ' ', $m->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match($m->status) { 'scheduled' => 'warning', 'in_progress' => 'info', 'completed' => 'success', default => 'neutral' }),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(str_replace('_', ' ', $m->status))]); ?>
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
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-tools','title' => 'No maintenance scheduled','message' => 'All bicycles are cleared for service.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-tools','title' => 'No maintenance scheduled','message' => 'All bicycles are cleared for service.']); ?>
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


<div class="admin-modal" id="quickRentalModal">
    <div class="admin-modal__backdrop" data-modal-close></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__head">
            <h3>Quick Rental</h3>
            <button class="admin-icon-btn" data-modal-close><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="admin-modal__body">
            <p style="color: var(--text-2); font-size: 13px;">Start a rental from the available fleet. Manage details from the Rental Management module.</p>
            <a href="<?php echo e(route('admin.rentals.index')); ?>?filter=active" class="btn-admin btn-admin--primary btn-admin--block">
                <i class="bi bi-key"></i> Go to Rental Management
            </a>
            <a href="<?php echo e(route('admin.monitoring.index')); ?>" class="btn-admin btn-admin--secondary btn-admin--block mt-2">
                <i class="bi bi-map"></i> View live map
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || getComputedStyle(document.body).getPropertyValue(name).trim();
    const color = (name, fallback) => css(name) || fallback;
    const brand = color('--brand', '#2E7D32');
    const text3 = color('--text-3', '#94A3B8');
    const grid = color('--border-subtle', '#E7ECF1');

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = color('--text-3', '#94A3B8');
    Chart.defaults.borderColor = grid;

    const charts = [];

    /* Revenue + rentals combo */
    const trendsCtx = document.getElementById('trendsChart');
    if (trendsCtx) {
        charts.push(new Chart(trendsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($stats['monthlyRentalsLabels'] ?? []); ?>,
                datasets: [
                    {
                        type: 'line',
                        label: 'Revenue (₱)',
                        data: <?php echo json_encode($stats['monthlyRevenueData'] ?? []); ?>,
                        borderColor: brand,
                        backgroundColor: 'rgba(46,125,50,0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y',
                        pointBackgroundColor: brand,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        type: 'bar',
                        label: 'Rentals',
                        data: <?php echo json_encode($stats['monthlyRentalsData'] ?? []); ?>,
                        backgroundColor: 'rgba(14,165,233,0.55)',
                        borderRadius: 5,
                        borderSkipped: false,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } },
                scales: {
                    y: { position: 'left', grid: { color: grid }, ticks: { callback: v => '₱' + (v >= 1000 ? (v / 1000) + 'k' : v) } },
                    y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } },
                },
            },
        }));
    }

    /* Weekly rentals */
    const weeklyCtx = document.getElementById('weeklyChart');
    if (weeklyCtx) {
        charts.push(new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($stats['weeklyLabels'] ?? []); ?>,
                datasets: [{
                    label: 'Rentals',
                    data: <?php echo json_encode($stats['weeklyData'] ?? []); ?>,
                    backgroundColor: 'rgba(46,125,50,0.75)',
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: grid }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } },
            },
        }));
    }

    /* Peak hours */
    const peakCtx = document.getElementById('peakChart');
    if (peakCtx) {
        const peakData = <?php echo json_encode($stats['peakData'] ?? []); ?>;
        charts.push(new Chart(peakCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($stats['peakLabels'] ?? []); ?>,
                datasets: [{
                    label: 'Rentals',
                    data: peakData,
                    backgroundColor: peakData.map((_, i) => i >= 17 && i <= 20 ? 'rgba(217,119,6,0.85)' : 'rgba(46,125,50,0.5)'),
                    borderRadius: 4,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: grid }, ticks: { stepSize: 1 } }, x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } } },
            },
        }));
    }

    /* Fleet status doughnut */
    const fleetCtx = document.getElementById('fleetChart');
    if (fleetCtx) {
        charts.push(new Chart(fleetCtx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Rented', 'Maintenance', 'Removed'],
                datasets: [{
                    data: [
                        <?php echo e($stats['bicycles']['available'] ?? 0); ?>,
                        <?php echo e($stats['bicycles']['rented'] ?? 0); ?>,
                        <?php echo e($stats['bicycles']['maintenance'] ?? 0); ?>,
                        <?php echo e($stats['bicycles']['total'] - $stats['bicycles']['available'] - $stats['bicycles']['rented'] - $stats['bicycles']['maintenance'] ?? 0); ?>,
                    ],
                    backgroundColor: [color('--success', '#16A34A'), color('--accent', '#2563EB'), color('--warning', '#D97706'), color('--text-3', '#94A3B8')],
                    borderWidth: 2,
                    borderColor: color('--surface', '#FFFFFF'),
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, font: { size: 11 } } } },
            },
        }));
    }

    /* Battery doughnut */
    const batteryCtx = document.getElementById('batteryChart');
    if (batteryCtx) {
        charts.push(new Chart(batteryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Low (≤20%)', 'Fair (21-50%)', 'Good (51-80%)', 'Full (81%+)'],
                datasets: [{
                    data: [
                        <?php echo e($stats['battery']['low'] ?? 0); ?>,
                        <?php echo e($stats['battery']['mid'] ?? 0); ?>,
                        <?php echo e($stats['battery']['good'] ?? 0); ?>,
                        <?php echo e($stats['battery']['full'] ?? 0); ?>,
                    ],
                    backgroundColor: [color('--danger', '#DC2626'), color('--warning', '#D97706'), color('--brand', '#2E7D32'), color('--success', '#16A34A')],
                    borderWidth: 2,
                    borderColor: color('--surface', '#FFFFFF'),
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, font: { size: 11 } } } },
            },
        }));
    }

    /* Incident trends */
    const incidentCtx = document.getElementById('incidentChart');
    if (incidentCtx) {
        charts.push(new Chart(incidentCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($stats['monthlyRentalsLabels'] ?? []); ?>,
                datasets: [
                    { label: 'Theft', data: <?php echo json_encode($stats['theftTrendData'] ?? []); ?>, borderColor: color('--danger', '#DC2626'), backgroundColor: 'rgba(220,38,38,0.08)', fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 3 },
                    { label: 'Accidents', data: <?php echo json_encode($stats['accidentTrendData'] ?? []); ?>, borderColor: color('--warning', '#D97706'), backgroundColor: 'rgba(217,119,6,0.08)', fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 3 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } },
                scales: { y: { beginAtZero: true, grid: { color: grid }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } },
            },
        }));
    }

    window.PedalyaCharts = charts;
});

window.refreshCharts = function () {
    if (window.PedalyaCharts) {
        window.PedalyaCharts.forEach(c => c.update());
        window.PedalyaToast.success('Charts refreshed', 'Analytics');
    }
};
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>