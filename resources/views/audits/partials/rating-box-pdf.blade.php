@php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
@endphp
<table class="rating-box">
    <tr>
        <td colspan="2" class="rb-head">রেটিং (Rating)</td>
    </tr>
    <tr>
        <td class="rb-cell">{{ $label }}</td>
        <td class="rb-cell">{{ $code }}</td>
    </tr>
</table>
