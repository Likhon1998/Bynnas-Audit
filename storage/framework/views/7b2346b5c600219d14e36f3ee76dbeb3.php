<?php
    /** @var \App\Models\ShakhaEmployee|null $employee */
    $size = $size ?? 'md';
    $sizes = [
        'sm' => 'h-8 w-8 text-[10px]',
        'md' => 'h-10 w-10 text-[11px]',
        'lg' => 'h-16 w-16 text-[14px]',
    ];
    $class = $sizes[$size] ?? $sizes['md'];
    $url = $employee?->photoUrl();
    $initial = mb_strtoupper(mb_substr((string) ($employee?->name ?: '?'), 0, 1));
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($url): ?>
    <img
        src="<?php echo e($url); ?>"
        alt="<?php echo e($employee?->name); ?>"
        class="<?php echo e($class); ?> shrink-0 rounded-full object-cover ring-1 ring-slate-200 <?php echo e($classExtra ?? ''); ?>"
    >
<?php else: ?>
    <span class="<?php echo e($class); ?> inline-flex shrink-0 items-center justify-center rounded-full bg-slate-100 font-semibold text-slate-500 ring-1 ring-slate-200 <?php echo e($classExtra ?? ''); ?>">
        <?php echo e($initial); ?>

    </span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\shakha-employees\partials\photo.blade.php ENDPATH**/ ?>