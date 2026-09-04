<?php
    $isSection = ($isSection ?? false);
    $rating = $rating ?? '';
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isSection && $rating !== ''): ?>
    <?php echo $__env->make('audits.partials.rating-box-pdf', ['rating' => $rating], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\toc-rating-cell-pdf.blade.php ENDPATH**/ ?>