<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['icon' => 'bi-inbox', 'title' => 'Nothing here yet', 'message' => '']));

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

foreach (array_filter((['icon' => 'bi-inbox', 'title' => 'Nothing here yet', 'message' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="admin-empty">
    <i class="bi <?php echo e($icon); ?>"></i>
    <h4><?php echo e($title); ?></h4>
    <?php if($message): ?><p><?php echo e($message); ?></p><?php endif; ?>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\Users\Administrator\pedalya\resources\views/components/admin/empty-state.blade.php ENDPATH**/ ?>