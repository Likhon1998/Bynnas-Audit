@php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
    $bg = $style['bg'] ?? '#FCE4D6';
    $color = $style['color'] ?? '#111111';
@endphp
<table class="rating-box" width="100%" border="1" cellspacing="0" cellpadding="0" align="left"
       style="border-collapse:collapse;margin:0;mso-table-layout-alt:auto;mso-table-overlap:never;">
    <tr>
        <td colspan="2" align="center" valign="middle" bgcolor="#4472C4"
            style="border:1px solid #111111;mso-border-alt:solid #111111 0.5pt;color:#ffffff;font-weight:bold;font-size:8pt;padding:2pt;line-height:1.25;">
            রেটিং (Rating)
        </td>
    </tr>
    <tr>
        <td width="50%" align="center" valign="middle" bgcolor="{{ $bg }}"
            style="border:1px solid #111111;mso-border-alt:solid #111111 0.5pt;background:{{ $bg }};color:{{ $color }};font-weight:bold;font-size:9pt;padding:2pt;">
            {{ $label }}
        </td>
        <td width="50%" align="center" valign="middle" bgcolor="{{ $bg }}"
            style="border:1px solid #111111;mso-border-alt:solid #111111 0.5pt;background:{{ $bg }};color:{{ $color }};font-weight:bold;font-size:9pt;padding:2pt;">
            {{ $code }}
        </td>
    </tr>
</table>
