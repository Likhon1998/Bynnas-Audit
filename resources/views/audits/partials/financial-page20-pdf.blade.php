<p class="bold center" style="margin:0 0 2mm;font-size:12pt;">{{ $page20_it_title ?? '' }}</p>

<p class="center" style="margin:0 0 1.5mm;font-size:10.5pt;line-height:1.45;">
    {{ $page20_it_org_line1 ?? '' }}<br>
    {{ $page20_it_org_line2 ?? '' }}<br>
    {{ $page20_it_org_line3 ?? '' }}
</p>

<p class="center" style="margin:0 0 2mm;font-size:10pt;">
    <span class="bold">কর্মসূচীর নাম:</span> {{ $page20_it_program ?? '' }}
    &nbsp;&nbsp;
    <span class="bold">শাখার নাম:</span> {{ $page20_it_branch ?? '' }}
</p>

<p class="bold center" style="margin:0 0 3mm;font-size:10.5pt;">{{ $page20_it_instruction ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন' }}</p>

<table class="doc-table it-checklist-table" style="margin-bottom:3mm;font-size:9.5pt;width:100%;border-collapse:collapse;table-layout:fixed;">
    <colgroup>
        <col style="width:6%;">
        <col style="width:34%;">
        <col style="width:5.5%;">
        <col style="width:5.5%;">
        <col style="width:5.5%;">
        <col style="width:13%;">
        <col style="width:14%;">
        <col style="width:16.5%;">
    </colgroup>
    <thead>
        <tr>
            <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">ক্রমিক</th>
            <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">বিবরণ</th>
            <th colspan="3" style="padding:3px 4px;vertical-align:middle;">Compliance</th>
            <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">Action Owner (কার দায়িত্ব)</th>
            <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">Management Comments (ব্যবস্থাপনার মন্তব্য)</th>
            <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">Recommendation (সুপারিশ)</th>
        </tr>
        <tr>
            <th style="padding:3px 2px;">Yes</th>
            <th style="padding:3px 2px;">No</th>
            <th style="padding:3px 2px;">N/A</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($page20ItChecklistRows ?? [] as $row)
            @php $compliance = (string) ($row['compliance'] ?? ''); @endphp
            <tr>
                <td class="center" style="padding:3px 2px;vertical-align:middle;font-weight:700;">{{ $row['sl_no'] ?? '' }}</td>
                <td style="padding:3px 4px;vertical-align:top;text-align:left;">{{ $row['description'] ?? '' }}</td>
                <td class="center it-tick-cell" style="padding:3px 2px;vertical-align:middle;">{!! $compliance === 'yes' ? '<span class="it-tick" style="font-family:dejavusans;font-size:12pt;font-weight:bold;">✓</span>' : '&nbsp;' !!}</td>
                <td class="center it-tick-cell" style="padding:3px 2px;vertical-align:middle;">{!! $compliance === 'no' ? '<span class="it-tick" style="font-family:dejavusans;font-size:12pt;font-weight:bold;">✓</span>' : '&nbsp;' !!}</td>
                <td class="center it-tick-cell" style="padding:3px 2px;vertical-align:middle;">{!! $compliance === 'na' ? '<span class="it-tick" style="font-family:dejavusans;font-size:12pt;font-weight:bold;">✓</span>' : '&nbsp;' !!}</td>
                <td style="padding:3px 4px;vertical-align:top;">{{ $row['action_owner'] ?? '' }}</td>
                <td style="padding:3px 4px;vertical-align:top;">{{ $row['management_comments'] ?? '' }}</td>
                <td style="padding:3px 4px;vertical-align:top;">{{ $row['recommendation'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
