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

<table class="header-table">
    <tr>
        <td style="width:68%;" valign="top">
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
        <td style="width:32%;" class="rating-wrap">
            <table class="cover-rating">
                <tr>
                    <td class="cr-label">Branch Internal<br>Control Rating</td>
                </tr>
                <tr>
                    <td class="cr-value" style="background: {{ $ratingColor }};">{{ $control_rating ?: '—' }}</td>
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
