@php
    use App\Livewire\MakeAuditReport;
    $dash = $dash ?? '………………';
@endphp

<p class="bold" style="margin:0 0 2mm;">বিস্তারিত নিম্নে দেওয়া হল:</p>

<table class="doc-table" style="margin-bottom:3mm;font-size:8.5px;">
    <thead>
        <tr>
            <th rowspan="2">তারিখ/মাসের নাম</th>
            <th rowspan="2">ভাউচার নং</th>
            <th rowspan="2">বিবরণ</th>
            <th rowspan="2">খরচ (টাকা)</th>
            <th colspan="3">ভ্যাট সংক্রান্ত</th>
            <th colspan="3">ট্যাক্স সংক্রান্ত</th>
        </tr>
        <tr>
            <th>প্রযোজ্য</th>
            <th>প্রদানকৃত</th>
            <th>কম/বেশি প্রদান</th>
            <th>প্রযোজ্য</th>
            <th>প্রদানকৃত</th>
            <th>কম/বেশি প্রদান</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($expenseDetailRows as $row)
            <tr>
                <td>{{ $row['date_month'] ?? '' }}</td>
                <td>{{ $row['voucher_no'] ?? '' }}</td>
                <td class="{{ ! empty($row['is_total']) ? 'bold' : '' }}">{{ $row['description'] ?? '' }}</td>
                <td class="center">{{ $row['expense_amount'] ?? '' }}</td>
                <td class="center">{{ $row['vat_applicable'] ?? '' }}</td>
                <td class="center">{{ $row['vat_paid'] ?? '' }}</td>
                <td class="center">{{ $row['vat_diff'] ?? '' }}</td>
                <td class="center">{{ $row['tax_applicable'] ?? '' }}</td>
                <td class="center">{{ $row['tax_paid'] ?? '' }}</td>
                <td class="center">{{ $row['tax_diff'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="bold" style="margin:2mm 0 1mm;">ঝুঁকি/প্রভাব (Risk/Implication):</p>
<p class="justify" style="margin:0 0 2mm;">{{ $expense_detail_risk !== '' ? $expense_detail_risk : $dash }}</p>
<p class="bold" style="margin:0 0 1mm;">মূল কারণ (Root Cause):</p>
@if (($expense_detail_root_cause ?? '') !== '')
    <p class="justify" style="margin:0 0 2mm;">{{ $expense_detail_root_cause }}</p>
@else
    <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
@endif
<p class="bold" style="margin:0 0 1mm;">সুপারিশ (Recommendation):</p>
@if (($expense_detail_recommendation ?? '') !== '')
    <p class="justify" style="margin:0 0 3mm;">{{ $expense_detail_recommendation }}</p>
@else
    <p style="margin:0 0 3mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
@endif

<table class="doc-table" style="margin-bottom:5mm;font-size:10px;">
    <tbody>
        <tr>
            <td style="width:38%;" class="bold">শাখা ব্যবস্থাপকের জবাব</td>
            <td>{{ $expense_detail_bm_reply }}</td>
        </tr>
        <tr>
            <td class="bold">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
            <td>{{ $expense_detail_responsible }}</td>
        </tr>
        <tr>
            <td class="bold">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)</td>
            <td>{{ $expense_detail_resolution_date }}</td>
        </tr>
    </tbody>
</table>

@php $anchor = MakeAuditReport::findingAnchorId($finding13_serial ?? '১.৩'); @endphp
@if ($anchor !== '')
    <a id="{{ $anchor }}" name="{{ $anchor }}"></a>
@endif

@php
    $widths = \App\Support\AuditDocumentLayout::findingColumnWidths();
@endphp
<table class="doc-table finding-table" style="margin-bottom:2mm;">
    <colgroup>
        @foreach ($widths as $w)
            <col style="width:{{ $w }}%;">
        @endforeach
    </colgroup>
    <tbody>
        <tr>
            <td class="bold center">
                @include('audits.partials.bn-num', ['value' => $finding13_serial ?? '', 'variant' => 'serial'])
            </td>
            <td class="bold center">{{ $finding13_title ?? 'শিরোনাম' }}</td>
            <td class="body-cell">
                {{ $finding13_body ?? '' }}
                @if (($finding13_amount ?? '') !== '')
                    <br><span class="bold">টাকার পরিমাণ:</span> {{ $finding13_amount }}
                @endif
            </td>
            <td class="rating-cell" valign="middle">
                @if ($forDoc ?? false)
                    @include('audits.partials.rating-box-doc', ['rating' => $finding13_rating ?? ''])
                @else
                    @include('audits.partials.rating-box-pdf', ['rating' => $finding13_rating ?? ''])
                @endif
            </td>
        </tr>
    </tbody>
</table>

<p class="bold" style="margin:3mm 0 1mm;">প্রচলিত নিয়ম (Criteria):</p>
<p class="justify" style="margin:0;">{{ $finding13_criteria }}</p>

<p class="bold" style="margin:3mm 0 1mm;">পর্যবেক্ষণ (Observation) :</p>
@if (($finding13_observation ?? '') !== '')
    <p class="justify" style="margin:0 0 2mm;">{{ $finding13_observation }}</p>
@else
    <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
@endif

<table class="doc-table obs-table" style="margin-bottom:3mm;">
    <thead>
        <tr>
            <th>Total Population</th>
            <th>Sample Size(Checked)</th>
            <th>Instantans Found</th>
            <th>Persentange(%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($finding13_statsRows as $row)
            <tr>
                <td class="center">{{ $row['total_population'] ?? '' }}</td>
                <td class="center">{{ $row['sample_size'] ?? '' }}</td>
                <td class="center">{{ $row['instances_found'] ?? '' }}</td>
                <td class="center">{{ $row['percentage'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="doc-table" style="margin-bottom:3mm;font-size:9.5px;">
    <thead>
        <tr>
            <th>বিবরণ</th>
            <th>মাসের নাম</th>
            <th>টাকা উত্তোলনের তারিখ</th>
            <th>সরকারী কোষাগারে টাকা জমা প্রদানের তারিখ</th>
            <th>টাকার পরিমাণ</th>
            <th>হস্তমজুদের সময়কাল</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($finding13_depositRows as $row)
            <tr>
                <td>{{ $row['description'] ?? '' }}</td>
                <td>{{ $row['month_name'] ?? '' }}</td>
                <td class="center">{{ $row['withdrawal_date'] ?? '' }}</td>
                <td class="center">{{ $row['deposit_date'] ?? '' }}</td>
                <td class="center">{{ $row['amount'] ?? '' }}</td>
                <td class="center">{{ $row['holding_period'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="bold" style="margin:2mm 0 1mm;">ঝুঁকি/প্রভাব (Risk/Implication):</p>
<p class="justify" style="margin:0 0 2mm;">{{ $finding13_risk !== '' ? $finding13_risk : $dash }}</p>
<p class="bold" style="margin:0 0 1mm;">মূল কারণ (Root Cause):</p>
@if (($finding13_root_cause ?? '') !== '')
    <p class="justify" style="margin:0 0 2mm;">{{ $finding13_root_cause }}</p>
@else
    <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
@endif
<p class="bold" style="margin:0 0 1mm;">সুপারিশ (Recommendation):</p>
@if (($finding13_recommendation ?? '') !== '')
    <p class="justify" style="margin:0 0 3mm;">{{ $finding13_recommendation }}</p>
@else
    <p style="margin:0 0 3mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
@endif

<table class="doc-table" style="font-size:10px;">
    <tbody>
        <tr>
            <td style="width:38%;" class="bold">শাখা ব্যবস্থাপকের জবাব</td>
            <td>{{ $finding13_bm_reply }}</td>
        </tr>
        <tr>
            <td class="bold">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
            <td>{{ $finding13_responsible }}</td>
        </tr>
        <tr>
            <td class="bold">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)</td>
            <td>{{ $finding13_resolution_date }}</td>
        </tr>
    </tbody>
</table>
