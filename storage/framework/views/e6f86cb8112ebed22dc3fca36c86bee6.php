<div class="page">
    <?php echo $__env->make('audits.partials.financial-audit-pdf', [
        'financial_section_title' => $financial_section_title,
        'financialFindings' => $financialFindings,
        'financial_criteria' => $financial_criteria,
        'vatObservationRows' => $vatObservationRows,
        'taxObservationRows' => $taxObservationRows,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="page-num">4</div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-page4-preview.blade.php ENDPATH**/ ?>