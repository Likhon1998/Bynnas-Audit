
<?php
    $layout = $definition['layout'] ?? 'society_lifecycle';
    $code = $definition['code'] ?? $formatModel?->code;
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'savings_refund' || $code === 'format-5'): ?>
    <?php echo $__env->make('livewire.partials.audit-checklist-format-5-editor', compact('formatModel', 'definition', 'payload'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($layout === 'savings_loan_collection' || $code === 'format-4'): ?>
    <?php echo $__env->make('livewire.partials.audit-checklist-format-4-editor', compact('formatModel', 'definition', 'payload'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($layout === 'society_management' || $code === 'format-3'): ?>
    <?php echo $__env->make('livewire.partials.audit-checklist-format-3-editor', compact('formatModel', 'definition', 'payload'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($layout === 'member_admission' || $code === 'format-2'): ?>
    <?php echo $__env->make('livewire.partials.audit-checklist-format-2-editor', compact('formatModel', 'definition', 'payload'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <?php echo $__env->make('livewire.partials.audit-checklist-format-1-editor', compact('formatModel', 'definition', 'payload'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-checklist-format-editor.blade.php ENDPATH**/ ?>