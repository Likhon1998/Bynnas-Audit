@php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
    $bg = $style['bg'] ?? '#FCE4D6';
    $color = $style['color'] ?? '#111111';
@endphp
<table class="rating-box">
    <tr>
        <td colspan="2" class="rb-head">রেটিং (Rating)</td>
    </tr>
    <tr>
        <td class="rb-cell" style="background: {{ $bg }}; color: {{ $color }};">{{ $label }}</td>
        <td class="rb-cell" style="background: {{ $bg }}; color: {{ $color }};">{{ $code }}</td>
    </tr>
</table>
