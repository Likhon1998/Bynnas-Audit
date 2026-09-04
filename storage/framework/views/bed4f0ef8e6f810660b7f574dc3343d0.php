<?php
    $dash = $dash ?? '………………';
    $fmt = $fmt ?? function (?string $date) {
        if (! $date) {
            return '……………………';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    };
?>

<?php echo $__env->make('audits.partials.signatures-block', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="classification-section">
    <?php echo $__env->make('audits.partials.classification-table-pdf', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\signatures-classification.blade.php ENDPATH**/ ?>