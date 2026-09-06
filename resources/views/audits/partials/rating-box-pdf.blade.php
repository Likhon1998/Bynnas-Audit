@php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
    $bg = $style['bg'] ?? '#FCE4D6';
    $color = $style['color'] ?? '#111111';
    $labelSize = mb_strlen($label) > 10 ? '6.5pt' : '7.5pt';
@endphp
<table class="rating-box">
    <tr>
        <td colspan="2" class="rb-head">রেটিং (Rating)</td>
    </tr>
    <tr>
        <td class="rb-cell" style="background: {{ $bg }}; color: {{ $color }}; font-size: {{ $labelSize }}; width: 58%;">{{ $label }}</td>
        <td class="rb-cell" style="background: {{ $bg }}; color: {{ $color }}; width: 42%;">{{ $code }}</td>
    </tr>
</table>
