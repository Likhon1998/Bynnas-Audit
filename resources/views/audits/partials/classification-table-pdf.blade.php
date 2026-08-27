@php
    $rows = \App\Support\AuditReportClassification::ratingRows();
    $summaryRows = \App\Support\AuditReportClassification::performanceSummaryRows();
@endphp

<h3>প্রতিবেদনের শ্রেণীবিন্যাস</h3>

<table class="doc-table classification-table">
    <colgroup>
        <col style="width:16%;">
        <col style="width:7%;">
        <col style="width:77%;">
    </colgroup>
    <thead>
        <tr>
            <th style="background:#BDD7EE;">পর্যবেক্ষণসমূহের গুরুত্বের মাত্রা</th>
            <th style="background:#BDD7EE;">কোড</th>
            <th style="background:#BDD7EE;">রেটিং নির্বাচনের বিষদ, পয়েন্ট ও কারণ</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                @if ($row['level'] !== null)
                    <td rowspan="{{ $row['level_rowspan'] }}" class="center bold" style="vertical-align:middle;background:#fff;">{{ $row['level'] }}</td>
                @endif
                <td class="center bold" style="background: {{ $row['code_bg'] }}; color: {{ $row['code_color'] }};">{{ $row['code'] }}</td>
                <td style="vertical-align:top;text-align:justify;line-height:1.35;">
                    @if ($row['bulleted'])
                        @foreach ($row['items'] as $item)
                            <p style="margin:0 0 0.8mm 3mm;text-indent:-2mm;">• {{ $item }}</p>
                        @endforeach
                    @else
                        <p style="margin:0;">{{ $row['items'][0] ?? '' }}</p>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="doc-table classification-table classification-summary">
    <colgroup>
        <col style="width:70%;">
        <col style="width:30%;">
    </colgroup>
    <tbody>
        <tr>
            <th style="background:#2E5090;color:#fff;width:70%;">নিরীক্ষাকার্যে ফলাফল মূল্যায়ন</th>
            <th style="background:#2E5090;color:#fff;width:30%;">সমষ্টিগত কর্মসম্পাদনের হার</th>
        </tr>
        @foreach ($summaryRows as $summary)
            <tr>
                <td>{{ $summary['label'] }}</td>
                <td class="center bold" style="width:30%;">{{ $summary['range'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
