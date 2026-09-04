<?php
    $dash = $dash ?? '………………';
    $fmt = function (?string $date) use ($dash) {
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

<table class="sign-table compact">
    <tr>
        <td>
            <p>নিরীক্ষা কর্মকর্তার নাম: <span class="bold"><?php echo e($sign_auditor_name !== '' ? $sign_auditor_name : $dash); ?></span></p>
            <p class="mt-2">পদবী: <span class="bold"><?php echo e($sign_auditor_designation !== '' ? $sign_auditor_designation : $dash); ?></span></p>
            <p class="mt-2">তারিখ: <span class="bold"><?php echo e($fmt($sign_auditor_date)); ?></span></p>
        </td>
        <td>
            <p>শাখা ব্যবস্থাপকের নাম: <span class="bold"><?php echo e($sign_bm_name !== '' ? $sign_bm_name : $dash); ?></span></p>
            <p class="mt-8">তারিখ: <span class="bold"><?php echo e($fmt($sign_bm_date)); ?></span></p>
        </td>
        <td>
            <p>সহকারী শাখা ব্যবস্থাপকের নাম: <span class="bold"><?php echo e($sign_abm_name !== '' ? $sign_abm_name : $dash); ?></span></p>
            <p class="mt-8">তারিখ: <span class="bold"><?php echo e($fmt($sign_abm_date)); ?></span></p>
        </td>
    </tr>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\signatures-block.blade.php ENDPATH**/ ?>