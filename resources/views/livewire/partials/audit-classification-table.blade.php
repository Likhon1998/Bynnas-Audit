@php
    $rows = \App\Support\AuditReportClassification::ratingRows();
    $summaryRows = \App\Support\AuditReportClassification::performanceSummaryRows();
    $compact = $compact ?? true;
    $tableClass = $compact ? 'a4-table a4-table-compact text-[8px]' : 'a4-table text-[10px]';
@endphp

<h3 class="mb-[2mm] text-center text-[12px] font-bold underline decoration-1 underline-offset-4">প্রতিবেদনের শ্রেণীবিন্যাস</h3>

<table class="{{ $tableClass }} leading-[1.35]">
    <thead>
        <tr>
            <th class="w-[16%] bg-[#BDD7EE]">পর্যবেক্ষণসমূহের গুরুত্বের মাত্রা</th>
            <th class="w-[7%] bg-[#BDD7EE]">কোড</th>
            <th class="bg-[#BDD7EE]">রেটিং নির্বাচনের বিষদ, পয়েন্ট ও কারণ</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                @if ($row['level'] !== null)
                    <td rowspan="{{ $row['level_rowspan'] }}" class="align-middle text-center font-bold">{{ $row['level'] }}</td>
                @endif
                <td class="text-center font-bold" style="background: {{ $row['code_bg'] }}; color: {{ $row['code_color'] }};">{{ $row['code'] }}</td>
                <td class="align-top text-justify">
                    @if ($row['bulleted'])
                        <ul class="m-0 list-disc pl-[4mm]">
                            @foreach ($row['items'] as $item)
                                <li class="mb-[0.8mm]">{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="m-0">{{ $row['items'][0] ?? '' }}</p>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="{{ $tableClass }} mt-[2mm] leading-[1.35]">
    <thead>
        <tr>
            <th class="bg-[#2E5090] text-white">নিরীক্ষাকার্যে ফলাফল মূল্যায়ন</th>
            <th class="w-[30%] bg-[#2E5090] text-white">সমষ্টিগত কর্মসম্পাদনের হার</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($summaryRows as $summary)
            <tr>
                <td>{{ $summary['label'] }}</td>
                <td class="text-center font-semibold">{{ $summary['range'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
