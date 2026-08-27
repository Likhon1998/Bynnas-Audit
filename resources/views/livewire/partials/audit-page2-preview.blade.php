<div class="page">
    @include('audits.partials.page2-content', [
        'logoSrc' => $logoUrl ?? null,
        'shakha_display_name' => $shakha_display_name,
        'glance_as_of' => $glance_as_of,
        'branch_opening_date' => $branch_opening_date,
        'staff_info_as_of' => $staff_info_as_of,
        'glanceRows' => $glanceRows,
        'staffColumns' => $staffColumns,
        'staffRows' => $staffRows,
        'tocRows' => $tocRows ?? [],
    ])

    <div class="page-num">2</div>
</div>
