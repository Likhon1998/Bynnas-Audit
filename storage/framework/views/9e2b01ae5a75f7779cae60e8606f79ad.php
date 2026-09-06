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

<p class="sheet-label">প্রতিবেদন মূল অংশ — এক নজরে → সূচিপত্র → স্বাক্ষর → শ্রেণীবিন্যাস → আর্থিক → বিস্তারিত/১.৩ (একসাথে)</p>
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
                'reportSections' => $reportSections ?? [],
                'reportBlocks' => $reportBlocks ?? [],
                'financial_criteria' => $financial_criteria,
                'vatObservationRows' => $vatObservationRows,
                'taxObservationRows' => $taxObservationRows,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_detail'])): ?>
        <div class="section-follow financial-detail-follow">
            <?php echo $__env->make('audits.partials.financial-detail-pdf', [
                'expenseDetailRows' => $expenseDetailRows ?? [],
                'expense_detail_risk' => $expense_detail_risk ?? '',
                'expense_detail_root_cause' => $expense_detail_root_cause ?? '',
                'expense_detail_recommendation' => $expense_detail_recommendation ?? '',
                'expense_detail_bm_reply' => $expense_detail_bm_reply ?? '',
                'expense_detail_responsible' => $expense_detail_responsible ?? '',
                'expense_detail_resolution_date' => $expense_detail_resolution_date ?? '',
                'finding13_serial' => $finding13_serial ?? '১.৩',
                'finding13_title' => $finding13_title ?? 'শিরোনাম',
                'finding13_body' => $finding13_body ?? '',
                'finding13_amount' => $finding13_amount ?? '',
                'finding13_rating' => $finding13_rating ?? '',
                'finding13_criteria' => $finding13_criteria ?? '',
                'finding13_observation' => $finding13_observation ?? '',
                'finding13_statsRows' => $finding13_statsRows ?? [],
                'finding13_depositRows' => $finding13_depositRows ?? [],
                'finding13_risk' => $finding13_risk ?? '',
                'finding13_root_cause' => $finding13_root_cause ?? '',
                'finding13_recommendation' => $finding13_recommendation ?? '',
                'finding13_bm_reply' => $finding13_bm_reply ?? '',
                'finding13_responsible' => $finding13_responsible ?? '',
                'finding13_resolution_date' => $finding13_resolution_date ?? '',
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page6'])): ?>
        <div class="section-follow financial-page6-follow">
            <?php echo $__env->make('audits.partials.financial-page6-pdf', [
                'page6Findings' => $page6Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page7'])): ?>
        <div class="section-follow financial-page7-follow">
            <?php echo $__env->make('audits.partials.financial-page7-pdf', [
                'page7Findings' => $page7Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page8'])): ?>
        <div class="section-follow financial-page8-follow">
            <?php echo $__env->make('audits.partials.financial-page8-pdf', [
                'page8Findings' => $page8Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page9'])): ?>
        <div class="section-follow financial-page9-follow">
            <?php echo $__env->make('audits.partials.financial-page9-pdf', [
                'page9Findings' => $page9Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page10'])): ?>
        <div class="section-follow financial-page10-follow">
            <?php echo $__env->make('audits.partials.financial-page10-pdf', [
                'page10_section_title' => $page10_section_title ?? '',
                'page10Findings' => $page10Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page11'])): ?>
        <div class="section-follow financial-page11-follow">
            <?php echo $__env->make('audits.partials.financial-page11-pdf', [
                'page11Findings' => $page11Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page12'])): ?>
        <div class="section-follow financial-page12-follow">
            <?php echo $__env->make('audits.partials.financial-page12-pdf', [
                'page12_section_title' => $page12_section_title ?? '',
                'page12Findings' => $page12Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page13'])): ?>
        <div class="section-follow financial-page13-follow">
            <?php echo $__env->make('audits.partials.financial-page13-pdf', [
                'page13_section_title' => $page13_section_title ?? '',
                'page13Findings' => $page13Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page14'])): ?>
        <div class="section-follow financial-page14-follow">
            <?php echo $__env->make('audits.partials.financial-page14-pdf', [
                'page14Findings' => $page14Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page15'])): ?>
        <div class="section-follow financial-page15-follow">
            <?php echo $__env->make('audits.partials.financial-page15-pdf', [
                'page15Findings' => $page15Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page16'])): ?>
        <div class="section-follow financial-page16-follow">
            <?php echo $__env->make('audits.partials.financial-page16-pdf', [
                'page16Findings' => $page16Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page17'])): ?>
        <div class="section-follow financial-page17-follow">
            <?php echo $__env->make('audits.partials.financial-page17-pdf', [
                'page17Findings' => $page17Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page18'])): ?>
        <div class="section-follow financial-page18-follow">
            <?php echo $__env->make('audits.partials.financial-page18-pdf', [
                'page18Findings' => $page18Findings ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page19'])): ?>
        <div class="section-follow financial-page19-follow">
            <?php echo $__env->make('audits.partials.financial-page19-pdf', [
                'page19_compliance_title' => $page19_compliance_title ?? '',
                'page19_compliance_period' => $page19_compliance_period ?? '',
                'page19_compliance_followup_date' => $page19_compliance_followup_date ?? '',
                'page19ComplianceRows' => $page19ComplianceRows ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page20'])): ?>
        <div class="section-follow financial-page20-follow">
            <?php echo $__env->make('audits.partials.financial-page20-pdf', [
                'page20_it_title' => $page20_it_title ?? '',
                'page20_it_org_line1' => $page20_it_org_line1 ?? '',
                'page20_it_org_line2' => $page20_it_org_line2 ?? '',
                'page20_it_org_line3' => $page20_it_org_line3 ?? '',
                'page20_it_program' => $page20_it_program ?? '',
                'page20_it_branch' => $page20_it_branch ?? '',
                'page20_it_instruction' => $page20_it_instruction ?? '',
                'page20ItChecklistRows' => $page20ItChecklistRows ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($byType['financial_page21'])): ?>
        <div class="section-follow financial-page21-follow">
            <?php echo $__env->make('audits.partials.financial-page21-pdf', [
                'page21_section_title' => $page21_section_title ?? '',
                'page21_year_of_reporting' => $page21_year_of_reporting ?? '',
                'page21_branch_name' => $page21_branch_name ?? '',
                'page21ExternalAuditRows' => $page21ExternalAuditRows ?? [],
                'page21_sign_label' => $page21_sign_label ?? '',
                'page21_sign_name' => $page21_sign_name ?? '',
                'page21_sign_designation' => $page21_sign_designation ?? '',
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-document-preview-pages.blade.php ENDPATH**/ ?>