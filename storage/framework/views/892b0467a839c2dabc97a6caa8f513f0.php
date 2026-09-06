<?php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
    $bg = $style['bg'] ?? '#FCE4D6';
    $color = $style['color'] ?? '#111111';
    $labelSize = mb_strlen($label) > 10 ? '6.5pt' : '7.5pt';
?>
<table class="rating-box">
    <tr>
        <td colspan="2" class="rb-head">রেটিং (Rating)</td>
    </tr>
    <tr>
        <td class="rb-cell" style="background: <?php echo e($bg); ?>; color: <?php echo e($color); ?>; font-size: <?php echo e($labelSize); ?>; width: 58%;"><?php echo e($label); ?></td>
        <td class="rb-cell" style="background: <?php echo e($bg); ?>; color: <?php echo e($color); ?>; width: 42%;"><?php echo e($code); ?></td>
    </tr>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/rating-box-pdf.blade.php ENDPATH**/ ?>