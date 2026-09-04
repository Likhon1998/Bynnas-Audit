@php
    $dash = $dash ?? '………………';
@endphp

<p class="bold" style="margin:0 0 2mm;font-size:10px;">{{ $page19_compliance_title ?? '' }}</p>

<p style="margin:0 0 2mm;font-size:8px;">
    <span class="bold">নিরীক্ষাকাল:</span> {{ $page19_compliance_period ?? '' }}
    &nbsp;&nbsp;
    <span class="bold">ফলোআপের তারিখ:</span> {{ $page19_compliance_followup_date ?? '' }}
</p>

<table class="doc-table" style="margin-bottom:5mm;font-size:7px;">
    <thead>
        <tr>
            <th>বিগত প্রতিবেদনের অনুচ্ছেদ নং</th>
            <th>নিরীক্ষা ও পরিবীক্ষণে প্রাপ্ত ঘটনা সমূহ</th>
            <th>প্রথম উদঘাটনের সময়কাল</th>
            <th>ব্যবস্থাপনার জবাব</th>
            <th>বর্তমান অবস্থা</th>
            <th>বর্তমান প্রতিবেদনের অনুচ্ছেদ নং</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($page19ComplianceRows ?? [] as $row)
            <tr>
                <td class="center">{{ $row['prev_para_no'] ?? '' }}</td>
                <td>{{ $row['findings'] ?? '' }}</td>
                <td class="center">{{ $row['first_discovery_period'] ?? '' }}</td>
                <td>{{ $row['management_reply'] ?? '' }}</td>
                <td>{{ $row['current_status'] ?? '' }}</td>
                <td class="center">{{ $row['current_para_no'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
