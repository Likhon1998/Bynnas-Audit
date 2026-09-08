@php
    $fmt = function (?string $date) {
        if (! $date) {
            return '……………………';
        }
        $date = trim($date);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'j/n/Y'] as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, $date)->format('d/m/Y');
            } catch (\Throwable) {
                // try next
            }
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    };
@endphp

<table class="header-table" width="100%" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;table-layout:fixed;mso-table-layout-alt:fixed;">
    <tr>
        <td class="logo-cell" width="68%" valign="top" style="width:68%;padding-right:8pt;vertical-align:top;">
            @if (($forDoc ?? false) && ! empty($logoDoc))
                @include('audits.partials.logo-word', ['logoDoc' => $logoDoc])
            @elseif (! empty($logoDataUri))
                <img src="{{ $logoDataUri }}" class="logo-large" alt="Logo">
            @else
                <p class="org-name">DSK</p>
                <p class="org-bn">দুঃস্থ স্বাস্থ্য কেন্দ্র</p>
                <p class="org-en">Dushtha Shasthya Kendra</p>
            @endif
        </td>
        <td class="rating-wrap" width="32%" valign="top" align="right" style="width:32%;max-width:160pt;vertical-align:top;text-align:right;overflow:hidden;">
            <table class="cover-rating" width="156" cellspacing="0" cellpadding="0" align="right"
                   style="width:156pt;max-width:156pt;border-collapse:collapse;table-layout:fixed;margin-left:auto;mso-table-layout-alt:fixed;">
                <tr>
                    <td align="left" bgcolor="#E7E6E6"
                        style="width:48%;background:#E7E6E6;border:0.75pt solid #222222;color:#111111;font-size:7pt;font-weight:bold;line-height:1.2;padding:2.5pt 3pt;text-align:left;">
                        Audit Score
                    </td>
                    <td align="center" bgcolor="#C6EFCE"
                        style="width:52%;background:#C6EFCE;border:0.75pt solid #222222;color:#111111;font-size:7.5pt;font-weight:bold;line-height:1.2;padding:2.5pt 2pt;text-align:center;">
                        {{ $audit_score_display ?? '—' }}
                    </td>
                </tr>
                <tr>
                    <td align="left" bgcolor="#E7E6E6"
                        style="background:#E7E6E6;border:0.75pt solid #222222;color:#111111;font-size:7pt;font-weight:bold;line-height:1.2;padding:2.5pt 3pt;text-align:left;">
                        Performance Grade
                    </td>
                    <td align="center" bgcolor="#F4B183"
                        style="background:#F4B183;border:0.75pt solid #222222;color:#111111;font-size:6.5pt;font-weight:bold;line-height:1.2;padding:2.5pt 2pt;text-align:center;word-wrap:break-word;overflow-wrap:anywhere;">
                        {{ $performance_grade ?? '—' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

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

<div class="mt-6 copy-block">
    <p class="bold">অনুলিপি:</p>
    <ol class="copy">
        @foreach (($copy_recipients ?? \App\Support\AuditCopyRecipients::defaults()) as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ol>
</div>
