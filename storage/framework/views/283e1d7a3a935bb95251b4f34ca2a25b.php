<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => null, 'sub' => null, 'flush' => false, 'bodyClass' => '']));

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

foreach (array_filter((['title' => null, 'sub' => null, 'flush' => false, 'bodyClass' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="admin-card">
    <?php if($title || isset($tools)): ?>
        <div class="admin-card__head">
            <div>
                <?php if($title): ?><div class="admin-card__title"><?php echo e($title); ?></div><?php endif; ?>
                <?php if($sub): ?><div class="admin-card__sub"><?php echo e($sub); ?></div><?php endif; ?>
            </div>
            <?php if(isset($tools)): ?>
                <div class="admin-card__tools"><?php echo e($tools); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="admin-card__body <?php echo e($flush ? 'admin-card__body--flush' : ''); ?> <?php echo e($bodyClass); ?>">
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH C:\flutter\Projects\4th_year\pedalya\resources\views/components/admin/card.blade.php ENDPATH**/ ?>