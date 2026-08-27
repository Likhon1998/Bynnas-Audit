<?php
    $byType = [];
    foreach ($documentSheets as $sheet) {
        $byType[$sheet['type']] = $sheet;
    }
    $overviewRows = $byType['overview']['rows'] ?? [];
?>


<p class="sheet-label">Cover</p>
<div class="page">
    <?php echo $__env->make('livewire.partials.audit-cover-preview', [
        'logoUrl' => $logoUrl,
        'ratingColor' => $ratingColor,
        'control_rating' => $control_rating,
        'memo_no' => $memo_no,
        'report_date' => $report_date,
        'shakha_display_name' => $shakha_display_name,
        'area_display_name' => $area_display_name,
        'audit_period_label' => $audit_period_label,
        'audit_start_date' => $audit_start_date,
        'audit_end_date' => $audit_end_date,
        'working_days' => $working_days,
        'period_scope' => $period_scope,
        'draft_sent_date' => $draft_sent_date,
        'comments_received_date' => $comments_received_date,
        'auditor_name' => $auditor_name,
        'auditor_designation' => $auditor_designation,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<p class="sheet-label">প্রতিবেদন মূল অংশ — এক নজরে → সূচিপত্র → স্বাক্ষর → শ্রেণীবিন্যাস → আর্থিক (একসাথে)</p>
<div class="page page-body">
    <?php echo $__env->make('audits.partials.page2-content', [
        'shakha_display_name' => $shakha_display_name,
        'glance_as_of' => $glance_as_of,
        'branch_opening_date' => $branch_opening_date,
        'staff_info_as_of' => $staff_info_as_of,
        'glanceRows' => $glanceRows,
        'staffColumns' => $staffColumns,
        'staffRows' => $staffRows,
        'tocRows' => $overviewRows,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="section-follow signatures-follow">
        <?php echo $__env->make('audits.partials.signatures-classification', [
            'sign_auditor_name' => $sign_auditor_name,
            'sign_auditor_designation' => $sign_auditor_designation,
            'sign_auditor_date' => $sign_auditor_date,
            'sign_bm_name' => $sign_bm_name,
            'sign_bm_date' => $sign_bm_date,
            'sign_abm_name' => $sign_abm_name,
            'sign_abm_date' => $sign_abm_date,
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
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-document-preview-pages.blade.php ENDPATH**/ ?>