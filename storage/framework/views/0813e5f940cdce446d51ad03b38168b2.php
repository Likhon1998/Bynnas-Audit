<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'title', 'accent' => '#4C6FFF']));

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

foreach (array_filter((['name', 'title', 'accent' => '#4C6FFF']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
?>

<div <?php echo e($attributes->merge(['class' => 'flex min-w-[168px] max-w-[200px] items-center gap-2 rounded-lg border border-slate-100 bg-white px-2.5 py-1.5 text-left shadow-sm'])); ?>>
    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-medium text-white" style="background-color: <?php echo e($accent); ?>">
        <?php echo e($initials); ?>

    </div>
    <div class="min-w-0">
        <p class="truncate text-[12px] font-medium leading-tight text-slate-800"><?php echo e($name); ?></p>
        <p class="truncate text-[10px] leading-tight text-slate-400"><?php echo e($title); ?></p>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\components\org-node.blade.php ENDPATH**/ ?>