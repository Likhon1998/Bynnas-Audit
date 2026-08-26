<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</title>
    <style>
        @font-face {
            font-family: 'NotoSansBengali';
            font-style: normal;
            font-weight: 400;
            src: url('{{ $fontRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'NotoSansBengali';
            font-style: normal;
            font-weight: 700;
            src: url('{{ $fontBold }}') format('truetype');
        }
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'NotoSansBengali', DejaVu Sans, sans-serif;
            font-size: 11.5px;
            line-height: 1.5;
            color: #111;
        }
        .page {
            width: 210mm;
            height: 297mm;
            padding: 12mm 14mm 10mm;
            page-break-after: always;
            position: relative;
        }
        .page:last-child { page-break-after: auto; }
        .page-num {
            position: absolute;
            right: 14mm;
            bottom: 8mm;
            font-size: 11px;
        }
        .row { width: 100%; }
        .left { float: left; }
        .right { float: right; }
        .clear { clear: both; }
        h2 {
            text-align: center;
            font-size: 14.5px;
            font-weight: 700;
            text-decoration: underline;
            margin: 5mm 0 4mm;
        }
        h3 {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            text-decoration: underline;
            margin: 5mm 0 2mm;
        }
        p { margin: 0 0 1.5mm; }
        .mt-2 { margin-top: 2mm; }
        .mt-4 { margin-top: 4mm; }
        .mt-5 { margin-top: 5mm; }
        .mt-6 { margin-top: 6mm; }
        .mt-8 { margin-top: 8mm; }
        .bold { font-weight: 700; }
        .justify { text-align: justify; }
        .dotted {
            border-bottom: 1px dotted #111;
            padding: 0 2px 1px;
            font-weight: 700;
        }
        .underline-field {
            border-bottom: 1px dotted #111;
            padding: 0 4px;
            font-weight: 700;
        }
        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2.5mm;
            font-size: 10.5px;
        }
        table.grid th,
        table.grid td {
            border: 1px solid #111;
            padding: 1.4mm 1.6mm;
            vertical-align: middle;
        }
        table.grid th {
            background: #d9d9d9;
            font-weight: 700;
            text-align: center;
        }
        table.grid .section { background: #efefef; font-weight: 700; }
        table.compact { font-size: 10px; }
        table.compact th,
        table.compact td { padding: 1.1mm 1.3mm; }
        .center { text-align: center; }
        .right-align { text-align: right; }
        .logo {
            width: 14mm;
            height: 14mm;
            object-fit: contain;
        }
        .org-name { font-size: 18px; font-weight: 700; margin: 0; line-height: 1.1; }
        .org-bn { font-size: 11.5px; font-weight: 700; margin: 0; }
        .org-en { font-size: 8.5px; font-weight: 700; text-transform: uppercase; margin: 0; letter-spacing: 0.03em; }
        .rating-wrap { width: 38mm; text-align: center; }
        .rating-label {
            background: #1d4ed8;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            padding: 1.5mm 2mm;
            line-height: 1.25;
        }
        .rating-value {
            margin-top: 1mm;
            border: 2px solid #f97316;
            color: #fff;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2mm;
        }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 8mm; }
        .sign-table td {
            width: 33.33%;
            border: 1px solid #111;
            vertical-align: top;
            padding: 2.5mm;
            font-size: 10.5px;
            height: 28mm;
        }
        ol.copy { margin: 1mm 0 0 5mm; padding: 0; }
        ol.copy li { margin: 0.6mm 0; }
    </style>
</head>
<body>
@php
    $dash = '………………';
    $bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $toBn = function (int $n) use ($bnDigits) {
        return implode('', array_map(fn ($d) => $bnDigits[(int) $d], str_split((string) $n)));
    };
    $fmt = function (?string $date) {
        if (! $date) {
            return '……………………';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    };
@endphp

{{-- Page 1: Cover --}}
<div class="page">
    <div class="row">
        <div class="left" style="width: 70%;">
            @if (! empty($logoDataUri))
                <img src="{{ $logoDataUri }}" class="logo" alt="Logo" style="float:left; margin-right: 3mm;">
            @endif
            <div style="{{ ! empty($logoDataUri) ? 'margin-left: 17mm;' : '' }}">
                <p class="org-name">DSK</p>
                <p class="org-bn">দুঃস্থ স্বাস্থ্য কেন্দ্র</p>
                <p class="org-en">Dushtha Shasthya Kendra</p>
            </div>
            <div class="clear"></div>
        </div>
        <div class="right rating-wrap">
            <div class="rating-label">Branch Internal<br>Control Rating</div>
            <div class="rating-value" style="background: {{ $ratingColor }};">{{ $control_rating ?: '—' }}</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="mt-4">
        <p><span class="bold">সূত্র নাম্বার:</span> <span class="dotted">{{ $memo_no ?: '………………………………' }}</span></p>
        <p><span class="bold">তারিখ:</span> <span class="dotted">{{ $fmt($report_date) }}</span></p>
    </div>

    <div class="mt-5">
        <p>বরাবর,</p>
        <p>যুগ্ম পরিচালক (নিরীক্ষা)</p>
        <p>দুঃস্থ স্বাস্থ্য কেন্দ্র (ডিএসকে)</p>
        <p>প্রধান কার্যালয়, ঢাকা।</p>
    </div>

    <h2>অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</h2>

    <div>
        <p><span class="bold">শাখার নাম ও নাম্বার:</span> <span class="dotted">{{ $shakha_display_name ?: '………………………………' }}</span></p>
        <p><span class="bold">অঞ্চলের নাম:</span> <span class="dotted">{{ $area_display_name ?: '………………………………' }}</span></p>
        <p><span class="bold">নিরীক্ষাকাল:</span> <span class="dotted">{{ $audit_period_label ?: '………………………………' }}</span></p>
    </div>

    <div class="mt-4">
        <p class="bold">প্রিয় মহোদয়,</p>
        <p class="mt-2 justify">
            গত
            <span class="underline-field">{{ $fmt($audit_start_date) }}</span>
            হতে
            <span class="underline-field">{{ $fmt($audit_end_date) }}</span>
            পর্যন্ত মোট
            <span class="underline-field">{{ $working_days !== null && $working_days !== '' ? $working_days : '……' }}</span>
            কর্ম দিবস
            <span class="underline-field">{{ $shakha_display_name ?: '………………' }}</span>
            শাখা হতে
            <span class="underline-field">{{ $period_scope ?: '………………' }}</span>
            সময়ের উপর অভ্যন্তরীণ নিরীক্ষা সম্পন্ন করা হয়। শাখার খসড়া প্রতিবেদন
            <span class="underline-field">{{ $fmt($draft_sent_date) }}</span>
            ইং তারিখে প্রেরণ করা হয় এবং
            <span class="underline-field">{{ $fmt($comments_received_date) }}</span>
            তারিখে মতামত পাওয়া যায়। এতদসংক্রান্ত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন আপনার সদয় অবগতির জন্য পেশ করা হলো।
        </p>
    </div>

    <div class="mt-6">
        <p>আপনার বিশ্বস্ত,</p>
        <p class="mt-5"><span class="bold">নাম:</span> <span class="dotted">{{ $auditor_name ?: '……………………' }}</span></p>
        <p><span class="bold">পদবী:</span> <span class="dotted">{{ $auditor_designation ?: '……………………' }}</span></p>
    </div>

    <div class="mt-6">
        <p class="bold">অনুলিপি:</p>
        <ol class="copy">
            <li>নির্বাহী পরিচালক</li>
            <li>উপ-নির্বাহী পরিচালক</li>
            <li>পরিচালক ঋণ</li>
            <li>উপ-প্রধান ঋণ</li>
            <li>যুগ্ম পরিচালক প্রশাসন ও মানব সম্পদ</li>
            <li>ফোকাল পার্সন</li>
            <li>অঞ্চলিক ব্যবস্থাপক</li>
            <li>শাখা ব্যবস্থাপক</li>
            <li>অফিস কপি</li>
        </ol>
    </div>

    <div class="page-num">1</div>
</div>

{{-- Page 2 --}}
<div class="page">
    @if (! empty($logoDataUri))
        <div style="height: 14mm; margin-bottom: 4mm;">
            <img src="{{ $logoDataUri }}" class="logo" alt="Logo">
        </div>
    @else
        <div style="height: 4mm;"></div>
    @endif

    <p class="bold" style="font-size: 12.5px;">
        এক নজরে {{ $shakha_display_name ?: '………………' }} শাখার তথ্য ({{ $glance_as_of ?: '………………' }}):
    </p>
    <p class="mt-2">
        শাখা গঠনের তারিখ:
        <span class="dotted">{{ $fmt($branch_opening_date) }}</span>
        ইং
    </p>

    <table class="grid compact">
        <tbody>
            @foreach ($glanceRows as $row)
                <tr>
                    <td style="width:28%;">{{ $row['left_label'] !== '' ? $row['left_label'] : '—' }}</td>
                    <td style="width:22%;" class="center bold">{{ $row['left_value'] !== '' ? $row['left_value'] : $dash }}</td>
                    <td style="width:28%;">{{ $row['right_label'] !== '' ? $row['right_label'] : '—' }}</td>
                    <td style="width:22%;" class="center bold">{{ $row['right_value'] !== '' ? $row['right_value'] : $dash }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="mt-5 bold">
        শাখার কর্মীর তথ্য :
        <span class="dotted" style="font-weight:400;">{{ $fmt($staff_info_as_of) }}</span>
        ইং
    </p>

    <table class="grid compact">
        <thead>
            <tr>
                <th>ক্রমিক নং</th>
                @foreach ($staffColumns as $col)
                    <th>{{ $col !== '' ? $col : '—' }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($staffRows as $idx => $row)
                <tr>
                    <td class="center">{{ $toBn($idx + 1) }}</td>
                    @foreach ($staffColumns as $cIdx => $col)
                        <td class="center">{{ $row['cells'][$cIdx] ?? '' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>সূচিপত্র</h3>
    <table class="grid compact">
        <thead>
            <tr>
                <th style="width:12mm;">ক্রমিক নং</th>
                <th>নিরীক্ষায় প্রাপ্ত ঘটনা সমূহ</th>
                <th style="width:16mm;">টাকা</th>
                <th style="width:24mm;">রেটিং</th>
                <th style="width:18mm;">বর্তমান অবস্থা</th>
                <th style="width:14mm;">পৃষ্ঠা নাম্বার</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tocPage2Rows as $row)
                @php
                    $isSection = ($row['type'] ?? 'item') === 'section';
                    $rating = $row['rating'] ?? '';
                    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating);
                @endphp
                <tr>
                    <td class="center bold {{ $isSection ? 'section' : '' }}">{{ $row['serial'] !== '' ? $row['serial'] : '—' }}</td>
                    <td class="{{ $isSection ? 'section' : '' }}">{{ $row['finding'] !== '' ? $row['finding'] : '—' }}</td>
                    <td class="right-align">{{ $isSection ? '' : ($row['amount'] !== '' ? $row['amount'] : '') }}</td>
                    <td class="center bold" style="{{ $isSection || $rating === '' ? '' : 'background: '.$style['bg'].'; color: '.$style['color'].';' }}">{{ $isSection ? '' : $rating }}</td>
                    <td class="center">{{ $isSection ? '' : ($row['status'] ?? '') }}</td>
                    <td class="center">{{ $isSection ? '' : ($row['page_no'] ?? '') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">কোনো সূচিপত্র এন্ট্রি নেই</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-num">2</div>
</div>

{{-- Page 3 --}}
<div class="page">
    <h3 style="margin-top: 0;">সূচিপত্র</h3>
    <table class="grid compact">
        <thead>
            <tr>
                <th style="width:12mm;">ক্রমিক নং</th>
                <th>নিরীক্ষায় প্রাপ্ত ঘটনা সমূহ</th>
                <th style="width:16mm;">টাকা</th>
                <th style="width:24mm;">রেটিং</th>
                <th style="width:18mm;">বর্তমান অবস্থা</th>
                <th style="width:14mm;">পৃষ্ঠা নাম্বার</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tocPage3Rows as $row)
                @php
                    $isSection = ($row['type'] ?? 'item') === 'section';
                    $rating = $row['rating'] ?? '';
                    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating);
                @endphp
                <tr>
                    <td class="center bold {{ $isSection ? 'section' : '' }}">{{ $row['serial'] !== '' ? $row['serial'] : '—' }}</td>
                    <td class="{{ $isSection ? 'section' : '' }}">{{ $row['finding'] !== '' ? $row['finding'] : '—' }}</td>
                    <td class="right-align">{{ $isSection ? '' : ($row['amount'] !== '' ? $row['amount'] : '') }}</td>
                    <td class="center bold" style="{{ $isSection || $rating === '' ? '' : 'background: '.$style['bg'].'; color: '.$style['color'].';' }}">{{ $isSection ? '' : $rating }}</td>
                    <td class="center">{{ $isSection ? '' : ($row['status'] ?? '') }}</td>
                    <td class="center">{{ $isSection ? '' : ($row['page_no'] ?? '') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">কোনো সূচিপত্র এন্ট্রি নেই</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                <p>নিরীক্ষা কর্মকর্তার নাম: <span class="bold">{{ $sign_auditor_name !== '' ? $sign_auditor_name : $dash }}</span></p>
                <p class="mt-2">পদবী: <span class="bold">{{ $sign_auditor_designation !== '' ? $sign_auditor_designation : $dash }}</span></p>
                <p class="mt-2">তারিখ: <span class="bold">{{ $fmt($sign_auditor_date) }}</span></p>
            </td>
            <td>
                <p>শাখা ব্যবস্থাপকের নাম: <span class="bold">{{ $sign_bm_name !== '' ? $sign_bm_name : $dash }}</span></p>
                <p class="mt-8">তারিখ: <span class="bold">{{ $fmt($sign_bm_date) }}</span></p>
            </td>
            <td>
                <p>সহকারী শাখা ব্যবস্থাপকের নাম: <span class="bold">{{ $sign_abm_name !== '' ? $sign_abm_name : $dash }}</span></p>
                <p class="mt-8">তারিখ: <span class="bold">{{ $fmt($sign_abm_date) }}</span></p>
            </td>
        </tr>
    </table>

    <div class="page-num">3</div>
</div>
</body>
</html>
