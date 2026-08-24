<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['href', 'active' => false]));

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

foreach (array_filter((['href', 'active' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = $active
        ? 'bg-brand-50 text-brand-600 font-semibold'
        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800';
?>

<a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => 'relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition '.$classes])); ?>>
    <?php if($active): ?>
        <span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-brand-500"></span>
    <?php endif; ?>
    <?php echo e($slot); ?>

</a>
<?php /**PATH C:\xampp\htdocs\bynnas_Audit\resources\views\components\sidebar-link.blade.php ENDPATH**/ ?>