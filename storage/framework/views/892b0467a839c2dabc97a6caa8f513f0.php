<?php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
?>
<table class="rating-box">
    <tr>
        <td colspan="2" class="rb-head">রেটিং (Rating)</td>
    </tr>
    <tr>
        <td class="rb-cell"><?php echo e($label); ?></td>
        <td class="rb-cell"><?php echo e($code); ?></td>
    </tr>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/rating-box-pdf.blade.php ENDPATH**/ ?>