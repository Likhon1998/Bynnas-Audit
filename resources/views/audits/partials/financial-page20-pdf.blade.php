<p class="bold center" style="margin:0 0 2mm;font-size:10px;">{{ $page20_it_title ?? '' }}</p>

<p class="center" style="margin:0 0 1mm;font-size:9px;line-height:1.4;">
    {{ $page20_it_org_line1 ?? '' }}<br>
    {{ $page20_it_org_line2 ?? '' }}<br>
    {{ $page20_it_org_line3 ?? '' }}
</p>

<p class="center" style="margin:0 0 2mm;font-size:8px;">
    <span class="bold">কর্মসূচীর নাম:</span> {{ $page20_it_program ?? '' }}
    &nbsp;&nbsp;
    <span class="bold">শাখার নাম:</span> {{ $page20_it_branch ?? '' }}
</p>

<p class="bold center" style="margin:0 0 2mm;font-size:8px;">{{ $page20_it_instruction ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন' }}</p>

<table class="doc-table" style="margin-bottom:3mm;font-size:7px;">
    <thead>
        <tr>
            <th rowspan="2">ক্রমিক</th>
            <th rowspan="2">বিবরণ</th>
            <th colspan="3">Compliance</th>
            <th rowspan="2">Action Owner (কার দায়িত্ব)</th>
            <th rowspan="2">Management Comments (ব্যবস্থাপনার মন্তব্য)</th>
            <th rowspan="2">Recommendation (সুপারিশ)</th>
        </tr>
        <tr>
            <th>Yes</th>
            <th>No</th>
            <th>N/A</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($page20ItChecklistRows ?? [] as $row)
            @php $compliance = (string) ($row['compliance'] ?? ''); @endphp
            <tr>
                <td class="center">{{ $row['sl_no'] ?? '' }}</td>
                <td>{{ $row['description'] ?? '' }}</td>
                <td class="center">{{ $compliance === 'yes' ? '✓' : '' }}</td>
                <td class="center">{{ $compliance === 'no' ? '✓' : '' }}</td>
                <td class="center">{{ $compliance === 'na' ? '✓' : '' }}</td>
                <td>{{ $row['action_owner'] ?? '' }}</td>
                <td>{{ $row['management_comments'] ?? '' }}</td>
                <td>{{ $row['recommendation'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
