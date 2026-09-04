@php
    use App\Livewire\MakeAuditReport;
    use App\Support\AuditDocumentLayout as Doc;
    $dash = $dash ?? '………………';
    $widths = Doc::findingColumnWidths();
@endphp

@foreach ($page18Findings ?? [] as $finding)
    @php
        $anchor = MakeAuditReport::findingAnchorId($finding['serial'] ?? '');
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
    @if (($finding['criteria'] ?? '') !== '')
        <p class="justify" style="margin:0;">{{ $finding['criteria'] }}</p>
    @else
        <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    @endif

    <p class="bold" style="margin:3mm 0 1mm;">পর্যবেক্ষণ (Observation) :</p>
    @if (($finding['observation'] ?? '') !== '')
        <p class="justify" style="margin:0 0 2mm;">{{ $finding['observation'] }}</p>
    @else
        <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    @endif

    <table class="doc-table obs-table" style="margin-bottom:2mm;">
        <thead>
            <tr>
                <th>Total Population/Sample size</th>
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

    @if (($finding['detail_type'] ?? 'none') === 'dropout_savings_refund')
        <p class="bold" style="margin:2mm 0 1mm;">{{ $finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হল:' }}</p>
        <table class="doc-table" style="margin-bottom:3mm;font-size:7px;">
            <thead>
                <tr>
                    <th>তারিখ</th>
                    <th>সমিতি/সদস্য নং</th>
                    <th>সদস্যের নাম</th>
                    <th>সঞ্চয় ফেরতের পরিমাণ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($finding['dropoutRefundRows'] ?? [] as $row)
                    <tr>
                        <td class="center">{{ $row['date'] ?? '' }}</td>
                        <td class="center">{{ $row['samity_member_no'] ?? '' }}</td>
                        <td>{{ $row['member_name'] ?? '' }}</td>
                        <td class="center">{{ $row['refund_amount'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif (($finding['detail_type'] ?? 'none') === 'savings_adjust_compare')
        <p class="bold" style="margin:2mm 0 1mm;">{{ $finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হল:' }}</p>
        <table class="doc-table" style="margin-bottom:3mm;font-size:7px;">
            <thead>
                <tr>
                    <th>মাসের নাম</th>
                    <th>ম্যানুয়ালী সঞ্চয় সমন্বয়</th>
                    <th>সফটওয়্যার অনুযায়ী সঞ্চয় সমন্বয়</th>
                    <th>পার্থক্য</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($finding['savingsAdjustCompareRows'] ?? [] as $row)
                    <tr>
                        <td class="center">{{ $row['month_name'] ?? '' }}</td>
                        <td class="center">{{ $row['manual_adjust'] ?? '' }}</td>
                        <td class="center">{{ $row['software_adjust'] ?? '' }}</td>
                        <td class="center">{{ $row['difference'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif (($finding['detail_intro'] ?? '') !== '')
        <p class="bold" style="margin:2mm 0 3mm;">{{ $finding['detail_intro'] }}</p>
    @endif

    <p class="bold" style="margin:2mm 0 1mm;">ঝুঁকি (Risk):</p>
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
