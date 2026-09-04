<?php
    /**
     * Finding serial cell — same rendering as page-4 ১.১ / ১.২ headlines.
     *
     * @var bool $editable
     * @var string|null $wireModel  e.g. page6Findings.0.serial
     * @var string $value
     */
    $editable = $editable ?? false;
    $wireModel = $wireModel ?? null;
    $value = $value ?? '';
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable && filled($wireModel)): ?>
    <input
        type="text"
        wire:model.live="<?php echo e($wireModel); ?>"
        class="finding-serial-input w-full border-0 bg-sky-50/40 text-center font-bold"
    >
<?php else: ?>
    <?php echo $__env->make('audits.partials.bn-num', ['value' => $value, 'variant' => 'serial'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-finding-serial-cell.blade.php ENDPATH**/ ?>