@php
    use App\Livewire\MakeAuditReport;
    use App\Support\AuditDocumentLayout as Doc;
    $dash = $dash ?? '………………';
    $widths = Doc::findingColumnWidths();
@endphp

@foreach ($page11Findings ?? [] as $finding)
    @php
        $anchor = MakeAuditReport::findingAnchorId($finding['serial'] ?? '');
        $detailType = (string) ($finding['detail_type'] ?? 'none');
    @endphp
    @if ($anchor !== '')
        <a id="{{ $anchor }}" name="{{ $anchor }}"></a>
    @endif

    <table class="doc-table finding-table" style="margin-bottom:2mm;">
        <colgroup>
            @foreach ($widths as $w)
                <col style="width:{{ $w }}%;">
            @endforeach
        </colgroup>
        <tbody>
            <tr>
                <td class="bold center">
                    @include('audits.partials.bn-num', ['value' => $finding['serial'] ?? '', 'variant' => 'serial'])
                </td>
                <td class="bold center">{{ $finding['title'] ?? 'শিরোনাম' }}</td>
                <td class="body-cell">
                    {{ $finding['body'] ?? '' }}
                    @if (($finding['amount'] ?? '') !== '')
                        <br><span class="bold">টাকার পরিমাণ:</span> {{ $finding['amount'] }}
                    @endif
                </td>
                <td class="rating-cell" valign="middle">
                    @if ($forDoc ?? false)
                        @include('audits.partials.rating-box-doc', ['rating' => $finding['rating'] ?? ''])
                    @else
                        @include('audits.partials.rating-box-pdf', ['rating' => $finding['rating'] ?? ''])
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <p class="bold" style="margin:3mm 0 1mm;">প্রচলিত নিয়ম (Criteria):</p>
    <p class="justify" style="margin:0;">{{ $finding['criteria'] ?? '' }}</p>

    <p class="bold" style="margin:3mm 0 1mm;">পর্যবেক্ষণ (Observation) :</p>
    @if (($finding['observation'] ?? '') !== '')
        <p class="justify" style="margin:0 0 2mm;">{{ $finding['observation'] }}</p>
    @else
        <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    @endif

    <table class="doc-table obs-table" style="margin-bottom:2mm;">
        <thead>
            <tr>
                <th>Total Population</th>
                <th>Sample Size(Checked)</th>
                <th>Instantans Found</th>
                <th>Persentange(%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($finding['statsRows'] ?? [] as $row)
                <tr>
                    <td class="center">{{ $row['total_population'] ?? '' }}</td>
                    <td class="center">{{ $row['sample_size'] ?? '' }}</td>
                    <td class="center">{{ $row['instances_found'] ?? '' }}</td>
                    <td class="center">{{ $row['percentage'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($detailType === 'dep_compare')
        <p class="bold" style="margin:2mm 0 1mm;">{{ $finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:' }}</p>
        <table class="doc-table" style="margin-bottom:3mm;font-size:7.5px;">
            <thead>
                <tr>
                    <th rowspan="2">সম্পদের গ্রুপ</th>
                    <th colspan="3">সম্পদের প্রারম্ভিক মূল্য (গ্রুপভিত্তিক মোট)</th>
                    <th colspan="3">অবচয়ের প্রারম্ভিক স্থিতি (গ্রুপভিত্তিক মোট)</th>
                </tr>
                <tr>
                    <th>মাসিক প্রতিবেদন</th>
                    <th>সম্পদ রেজিস্টার</th>
                    <th>পার্থক্য</th>
                    <th>মাসিক প্রতিবেদন</th>
                    <th>সম্পদ রেজিস্টার</th>
                    <th>পার্থক্য</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($finding['depRows'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['asset_group'] ?? '' }}</td>
                        <td class="center">{{ $row['value_report'] ?? '' }}</td>
                        <td class="center">{{ $row['value_register'] ?? '' }}</td>
                        <td class="center">{{ $row['value_diff'] ?? '' }}</td>
                        <td class="center">{{ $row['dep_report'] ?? '' }}</td>
                        <td class="center">{{ $row['dep_register'] ?? '' }}</td>
                        <td class="center">{{ $row['dep_diff'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif ($detailType === 'quote')
        <p class="bold" style="margin:2mm 0 1mm;">{{ $finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হলো:' }}</p>
        <table class="doc-table" style="margin-bottom:3mm;font-size:8px;">
            <thead>
                <tr>
                    <th>পণ্যের নাম</th>
                    <th>পণ্যের গ্রুপ</th>
                    <th>ক্রয়ের তারিখ</th>
                    <th>ভাউচার নং</th>
                    <th>টাকার পরিমাণ (ভ্যাট ও ট্যাক্সসহ)</th>
                    <th>কোটেশনের অবস্থা</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($finding['quoteRows'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['product_name'] ?? '' }}</td>
                        <td>{{ $row['product_group'] ?? '' }}</td>
                        <td class="center">{{ $row['purchase_date'] ?? '' }}</td>
                        <td class="center">{{ $row['voucher_no'] ?? '' }}</td>
                        <td class="center">{{ $row['amount'] ?? '' }}</td>
                        <td>{{ $row['quote_status'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif (($finding['detail_intro'] ?? '') !== '')
        <p class="bold" style="margin:2mm 0 2mm;">{{ $finding['detail_intro'] }}</p>
    @endif

    <p class="bold" style="margin:2mm 0 1mm;">ঝুঁকি/প্রভাব (Risk/Implication):</p>
    <p class="justify" style="margin:0 0 2mm;white-space:pre-wrap;">{{ ($finding['risk'] ?? '') !== '' ? $finding['risk'] : $dash }}</p>

    <p class="bold" style="margin:0 0 1mm;">মূল কারণ (Root Cause):</p>
    @if (($finding['root_cause'] ?? '') !== '')
        <p class="justify" style="margin:0 0 2mm;">{{ $finding['root_cause'] }}</p>
    @else
        <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    @endif

    <p class="bold" style="margin:0 0 1mm;">সুপারিশ (Recommendation):</p>
    @if (($finding['recommendation'] ?? '') !== '')
        <p class="justify" style="margin:0 0 3mm;">{{ $finding['recommendation'] }}</p>
    @else
        <p style="margin:0 0 3mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    @endif

    <table class="doc-table" style="margin-bottom:5mm;font-size:10px;">
        <tbody>
            <tr>
                <td style="width:38%;" class="bold">শাখা ব্যবস্থাপকের জবাব</td>
                <td>{{ $finding['bm_reply'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="bold">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
                <td>{{ $finding['responsible'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="bold">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)</td>
                <td>{{ $finding['resolution_date'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>
@endforeach
