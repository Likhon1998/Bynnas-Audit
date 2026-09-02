<?php
    $fmt = function (?string $date) {
        if (! $date) {
            return '……………………';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    };
    $dash = $dash ?? '………………';
?>

<p class="bold page2-title">
    এক নজরে <?php echo e($shakha_display_name ?: '………………'); ?> শাখার তথ্য (<?php echo e($glance_as_of ?: '………………'); ?>):
</p>
<p class="mt-2">
    শাখা গঠনের তারিখ:
    <span class="dotted"><?php echo e($fmt($branch_opening_date)); ?></span>
    ইং
</p>

<?php echo $__env->make('audits.partials.glance-table', ['glanceRows' => $glanceRows, 'dash' => $dash], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page2-section">
    <p class="bold">
        শাখার কর্মীর তথ্য :
        <span class="dotted" style="font-weight:400;"><?php echo e($fmt($staff_info_as_of)); ?></span>
        ইং
    </p>

    <?php echo $__env->make('audits.partials.staff-table', [
        'staffColumns' => $staffColumns,
        'staffRows' => $staffRows,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<div class="page2-section">
    <?php echo $__env->make('audits.partials.toc-table', [
        'rows' => $tocRows ?? $tocPage2Rows ?? [],
        'showTitle' => true,
        'forDoc' => $forDoc ?? false,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/page2-content.blade.php ENDPATH**/ ?>