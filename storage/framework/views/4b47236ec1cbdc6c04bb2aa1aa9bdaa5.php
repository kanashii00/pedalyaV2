<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'value' => '',
    'icon' => 'bi-info-circle',
    'color' => 'var(--brand)',
    'trend' => null,
    'trendLabel' => '',
    'foot' => '',
    'link' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => '',
    'value' => '',
    'icon' => 'bi-info-circle',
    'color' => 'var(--brand)',
    'trend' => null,
    'trendLabel' => '',
    'foot' => '',
    'link' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="kpi <?php echo e($link ? 'kpi--clickable' : ''); ?>" <?php if($link): ?> onclick="window.location='<?php echo e($link); ?>'" role="link" tabindex="0" onkeydown="if(event.key==='Enter')window.location='<?php echo e($link); ?>'" <?php endif; ?>>
    <div class="kpi__top">
        <div class="kpi__icon" style="background: <?php echo e($color); ?>1a; color: <?php echo e($color); ?>;">
            <i class="bi <?php echo e($icon); ?>"></i>
        </div>
        <?php if($trend): ?>
            <span class="kpi__trend <?php echo e($trend); ?>">
                <i class="bi bi-arrow-<?php echo e($trend === 'up' ? 'up-right' : 'down-right'); ?>"></i><?php echo e($trendLabel); ?>

            </span>
        <?php endif; ?>
    </div>
    <div class="kpi__value"><?php echo e($value); ?></div>
    <div class="kpi__label"><?php echo e($title); ?></div>
    <?php if($foot): ?>
        <div class="kpi__foot"><i class="bi bi-clock-history"></i><?php echo e($foot); ?></div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\Administrator\pedalya\resources\views\components\admin\kpi.blade.php ENDPATH**/ ?>