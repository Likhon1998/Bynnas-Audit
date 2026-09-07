<?php
    $lines = \App\Support\AuditComplianceHeading::lines(is_array($block ?? null) ? $block : []);
    $forDoc = (bool) ($forDoc ?? false);
    $titleSize = $forDoc ? '11px' : '12px';
    $metaSize = $forDoc ? '9px' : '10px';
?>

<div class="compliance-heading" style="text-align:center;margin:3mm 0 3mm;">
    <p class="bold finding-heading" style="margin:0 0 1mm;font-size:<?php echo e($titleSize); ?>;font-weight:700;text-align:center;">
        <?php echo \App\Support\BanglaNumerals::highlight($lines['bn'], 'serial'); ?>

    </p>
    <p style="margin:0 0 1.5mm;font-size:<?php echo e($titleSize); ?>;font-weight:700;text-align:center;">
        <?php echo e($lines['en']); ?>

    </p>
    <p style="margin:0 0 1mm;font-size:<?php echo e($metaSize); ?>;text-align:center;">
        <?php echo e($lines['period_line']); ?>

    </p>
    <p style="margin:0 0 2mm;font-size:<?php echo e($metaSize); ?>;text-align:center;">
        <?php echo e($lines['followup_line']); ?>

    </p>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\compliance-heading.blade.php ENDPATH**/ ?>