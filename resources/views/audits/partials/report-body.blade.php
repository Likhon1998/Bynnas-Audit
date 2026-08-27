@php
    $dash = $dash ?? '………………';
    $byType = [];
    foreach ($documentSheets ?? [] as $sheet) {
        $byType[$sheet['type']] = $sheet;
    }
    $overviewRows = $byType['overview']['rows'] ?? $tocRows ?? [];
@endphp

{{-- Cover alone; everything after flows continuously so no blank leftover pages. --}}
<div class="doc-cover">
    @include('audits.partials.cover-page')
</div>

<div class="doc-flow">
    @include('audits.partials.page2-content', [
        'shakha_display_name' => $shakha_display_name,
        'glance_as_of' => $glance_as_of,
        'branch_opening_date' => $branch_opening_date,
        'staff_info_as_of' => $staff_info_as_of,
        'glanceRows' => $glanceRows,
        'staffColumns' => $staffColumns,
        'staffRows' => $staffRows,
        'tocRows' => $overviewRows,
        'dash' => $dash,
    ])

    <div class="section-follow signatures-follow">
        @include('audits.partials.signatures-classification', [
            'sign_auditor_name' => $sign_auditor_name,
            'sign_auditor_designation' => $sign_auditor_designation,
            'sign_auditor_date' => $sign_auditor_date,
            'sign_bm_name' => $sign_bm_name,
            'sign_bm_date' => $sign_bm_date,
            'sign_abm_name' => $sign_abm_name,
            'sign_abm_date' => $sign_abm_date,
            'dash' => $dash,
        ])
    </div>

    @if (isset($byType['financial']))
        <div class="section-follow financial-follow">
            @include('audits.partials.financial-audit-pdf', [
                'financial_section_title' => $financial_section_title,
                'financialFindings' => $financialFindings,
                'financial_criteria' => $financial_criteria,
                'vatObservationRows' => $vatObservationRows,
                'taxObservationRows' => $taxObservationRows,
            ])
        </div>
    @endif
</div>
