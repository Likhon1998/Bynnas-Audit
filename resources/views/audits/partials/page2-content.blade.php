@php
    $fmt = function (?string $date) {
        if (! $date) {
            return '……………………';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    };
    $dash = $dash ?? '………………';
@endphp

<p class="bold page2-title">
    এক নজরে {{ $shakha_display_name ?: '………………' }} শাখার তথ্য ({{ $glance_as_of ?: '………………' }}):
</p>
<p class="mt-2">
    শাখা গঠনের তারিখ:
    <span class="dotted">{{ $fmt($branch_opening_date) }}</span>
    ইং
</p>

@include('audits.partials.glance-table', ['glanceRows' => $glanceRows, 'dash' => $dash])

<div class="page2-section">
    <p class="bold">
        শাখার কর্মীর তথ্য :
        <span class="dotted" style="font-weight:400;">{{ $fmt($staff_info_as_of) }}</span>
        ইং
    </p>

    @include('audits.partials.staff-table', [
        'staffColumns' => $staffColumns,
        'staffRows' => $staffRows,
    ])
</div>

<div class="page2-section">
    @include('audits.partials.toc-table', [
        'rows' => $tocRows ?? $tocPage2Rows ?? [],
        'showTitle' => true,
    ])
</div>
