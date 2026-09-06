@php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
    $bg = $style['bg'] ?? '#FCE4D6';
    $color = $style['color'] ?? '#111111';
    $labelSize = mb_strlen($label) > 10 ? '6.5pt' : '7.5pt';
@endphp
<table class="rating-box" width="100%" border="1" cellspacing="0" cellpadding="0" align="left"
       style="width:100%;max-width:100%;border-collapse:collapse;table-layout:fixed;margin:0;mso-table-layout-alt:fixed;mso-table-overlap:never;">
    <tr>
        <td colspan="2" align="center" valign="middle" bgcolor="#4472C4"
            style="border:1px solid #111111;mso-border-alt:solid #111111 0.5pt;color:#ffffff;font-weight:bold;font-size:7pt;padding:1.5pt 1pt;line-height:1.15;">
            রেটিং (Rating)
        </td>
    </tr>
    <tr>
        <td width="58%" align="center" valign="middle" bgcolor="{{ $bg }}"
            style="border:1px solid #111111;mso-border-alt:solid #111111 0.5pt;background:{{ $bg }};color:{{ $color }};font-weight:bold;font-size:{{ $labelSize }};padding:1.5pt 1pt;line-height:1.15;word-wrap:break-word;overflow-wrap:anywhere;">
            {{ $label }}
        </td>
        <td width="42%" align="center" valign="middle" bgcolor="{{ $bg }}"
            style="border:1px solid #111111;mso-border-alt:solid #111111 0.5pt;background:{{ $bg }};color:{{ $color }};font-weight:bold;font-size:7.5pt;padding:1.5pt 1pt;line-height:1.15;">
            {{ $code }}
        </td>
    </tr>
</table>
