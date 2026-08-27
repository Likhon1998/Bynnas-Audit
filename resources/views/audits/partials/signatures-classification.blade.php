@php
    $dash = $dash ?? '………………';
    $fmt = $fmt ?? function (?string $date) {
        if (! $date) {
            return '……………………';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    };
@endphp

@include('audits.partials.signatures-block')

<div class="classification-section">
    @include('audits.partials.classification-table-pdf')
</div>
