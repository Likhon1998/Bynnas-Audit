<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>Monthly Schedule — {{ $monthLabel }} · FY {{ $plan->fy_label }}</title>
    @if (!empty($forPdf))
    <style>
        body { font-family: hindsiliguri, sans-serif; color: #0f172a; font-size: 10px; }
        .org, .title, .meta { text-align: center; }
        .org-name { font-size: 16px; font-weight: bold; }
        .title { font-size: 13px; font-weight: bold; margin: 6px 0 2px; }
        .meta { font-size: 11px; margin-bottom: 8px; }
        table.schedule { width: 100%; border-collapse: collapse; }
        th { background: #e2e8f0; border: 1px solid #334155; padding: 4px; font-size: 9px; text-align: center; }
        td { border: 1px solid #334155; padding: 4px; font-size: 10px; vertical-align: top; }
        .c { text-align: center; }
        .muted { color: #64748b; }
        .footer-note { margin-top: 8px; font-size: 9px; color: #64748b; }
    </style>
    @else
    <style>
        @unless (!empty($forPdf))
        @page { size: A4 landscape; margin: 12mm; }
        @endunless
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: hindsiliguri, 'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.35;
            background: #fff;
        }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .toolbar a, .toolbar button {
            display: inline-flex;
            align-items: center;
            height: 32px;
            padding: 0 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .toolbar .primary { background: #059669; border-color: #059669; color: #fff; }
        .sheet { padding: 18px 20px 28px; }
        .org {
            text-align: center;
            margin-bottom: 6px;
        }
        .org-name {
            font-size: 16px;
            font-weight: 700;
        }
        .org-sub {
            font-size: 12px;
            color: #334155;
            margin-top: 2px;
        }
        .title {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            margin: 10px 0 4px;
        }
        .meta {
            text-align: center;
            font-size: 12px;
            margin-bottom: 14px;
            color: #1e293b;
        }
        table.schedule {
            width: 100%;
            border-collapse: collapse;
            overflow: wrap;
        }
        table.schedule th,
        table.schedule td {
            border: 1px solid #334155;
            padding: 4px 5px;
            vertical-align: middle;
            word-wrap: break-word;
            white-space: normal;
        }
        table.schedule th {
            background: #e2e8f0;
            font-size: 9px;
            font-weight: 700;
            text-align: center;
        }
        table.schedule td {
            font-size: 11px;
        }
        .c { text-align: center; }
        .visitors { font-weight: 600; }
        .remarks {
            text-align: center;
            font-weight: 700;
            writing-mode: horizontal-tb;
            background: #f8fafc;
        }
        .muted { color: #64748b; font-style: italic; }
        .footer-note {
            margin-top: 10px;
            font-size: 10px;
            color: #64748b;
        }
        @media print {
            .toolbar { display: none !important; }
            .sheet { padding: 0; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    @endif
</head>
<body>
    @unless (!empty($forDoc) || !empty($forPdf))
        <div class="toolbar">
            <div>
                <strong>Monthly plan printout</strong>
                <span style="color:#64748b;margin-left:8px;">FY {{ $plan->fy_label }} · {{ $monthLabel }} · {{ count($rows) }} rows</span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('monthly-visits.index', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}">← Back</a>
                <button type="button" onclick="window.print()" class="primary">Print / Save PDF</button>
                <a href="{{ route('monthly-visits.schedule.pdf', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="primary">Download PDF</a>
                <a href="{{ route('monthly-visits.schedule.doc', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}">Download DOC</a>
                <a href="{{ route('monthly-visits.schedule.excel', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}">Download Excel</a>
            </div>
        </div>
    @endunless

    <div class="sheet">
        <div class="org">
            <div class="org-name">DSK — Dushtha Shasthya Kendra</div>
            <div class="org-sub">দুস্থ স্বাস্থ্য কেন্দ্র</div>
        </div>
        <div class="title">পরিবীক্ষণ ও নিরীক্ষা বিষয়ক মাসিক সিডিউল</div>
        <div class="title" style="font-size:12px;font-weight:600;margin-top:0;">Monthly Schedule for Monitoring and Audit</div>
        <div class="meta">
            অর্থ বৎসর / FY: <strong>{{ $plan->fy_label }}</strong>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            মাসের নাম / Month: <strong>{{ $monthLabelBn }} ({{ $monthLabel }})</strong>
        </div>

        <table class="schedule">
            <thead>
                <tr>
                    <th style="width:4%">ক্রম<br>SL</th>
                    <th style="width:24%">শাখার নাম<br>Branch</th>
                    <th style="width:16%">পরিদর্শনকারী<br>Visitor</th>
                    <th style="width:14%">শেষ নিরীক্ষা<br>Last audit</th>
                    <th style="width:18%">পরিদর্শনের তারিখ<br>Visit dates</th>
                    <th style="width:8%">দিন<br>Days</th>
                    <th style="width:16%">মন্তব্য<br>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @php $groupMap = collect($groups)->keyBy('start'); @endphp
                @forelse ($rows as $i => $row)
                    <tr>
                        <td class="c">{{ $row['sl'] }}</td>
                        <td>
                            {{ $row['entity'] }}
                            @if ($row['is_special'])
                                <span class="muted">(Special)</span>
                            @endif
                        </td>
                        <td class="visitors {{ $row['visitors'] === '—' ? 'muted' : '' }}">{!! nl2br(e($row['visitors'])) !!}</td>
                        <td class="c">{{ $row['last_audit_upto_bn'] }}</td>
                        <td class="c {{ $row['visit_dates'] === 'Not allocated' ? 'muted' : '' }}">{{ $row['visit_dates'] }}</td>
                        <td class="c">{{ $row['days'] }}</td>
                        @if (!empty($forPdf))
                            <td class="c">{{ $row['purpose_bn'] }}<br>{{ $row['purpose'] }}</td>
                        @elseif ($groupMap->has($i))
                            @php $g = $groupMap[$i]; @endphp
                            <td class="remarks" rowspan="{{ $g['count'] }}">
                                {{ $g['purpose_bn'] }}
                                <div style="font-size:9px;font-weight:600;margin-top:2px;color:#475569;">{{ $g['purpose'] }}</div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="c muted" style="padding:24px;">No planned offices for this month.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer-note">
            Includes all yearly-plan offices for the month — allocated and not yet allocated.
            Generated {{ bd_datetime(bd_now()) }}.
        </div>
    </div>
</body>
</html>
