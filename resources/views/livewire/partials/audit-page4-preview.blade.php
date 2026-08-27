<div class="page">
    @include('audits.partials.financial-audit-pdf', [
        'financial_section_title' => $financial_section_title,
        'financialFindings' => $financialFindings,
        'financial_criteria' => $financial_criteria,
        'vatObservationRows' => $vatObservationRows,
        'taxObservationRows' => $taxObservationRows,
    ])

    <div class="page-num">4</div>
</div>
