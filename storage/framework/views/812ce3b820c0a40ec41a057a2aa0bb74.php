<?php
    $dash = $dash ?? '………………';
    $forDoc = $forDoc ?? false;
    $byType = [];
    foreach ($documentSheets ?? [] as $sheet) {
        $byType[$sheet['type']] = $sheet;
    }
    $overviewRows = $byType['overview']['rows'] ?? $tocRows ?? [];
?>


<div class="doc-cover">
    <?php echo $__env->make('audits.partials.cover-page', [
        'forDoc' => $forDoc,
        'logoDoc' => $logoDoc ?? null,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<div class="doc-flow">
    <?php echo $__env->make('audits.partials.page2-content', [
        'shakha_display_name' => $shakha_display_name,
        'glance_as_of' => $glance_as_of,
        'branch_opening_date' => $branch_opening_date,
        'staff_info_as_of' => $staff_info_as_of,
        'glanceRows' => $glanceRows,
        'staffColumns' => $staffColumns,
        'staffRows' => $staffRows,
        'tocRows' => $overviewRows,
        'dash' => $dash,
        'forDoc' => $forDoc,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($forDoc): ?>
        <p style="margin:0;line-height:0;font-size:0;">&nbsp;</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="section-follow signatures-follow">
        <?php echo $__env->make('audits.partials.signatures-classification', [
            'sign_auditor_name' => $sign_auditor_name,
            'sign_auditor_designation' => $sign_auditor_designation,
            'sign_auditor_date' => $sign_auditor_date,
            'sign_bm_name' => $sign_bm_name,
            'sign_bm_date' => $sign_bm_date,
            'sign_abm_name' => $sign_abm_name,
            'sign_abm_date' => $sign_abm_date,
            'dash' => $dash,
            'forDoc' => $forDoc,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial'])): ?>
        <div class="section-follow financial-follow">
            <?php echo $__env->make('audits.partials.financial-audit-pdf', [
                'financial_section_title' => $financial_section_title,
                'financialFindings' => $financialFindings,
                'financial_criteria' => $financial_criteria,
                'vatObservationRows' => $vatObservationRows,
                'taxObservationRows' => $taxObservationRows,
                'forDoc' => $forDoc,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/report-body.blade.php ENDPATH**/ ?>