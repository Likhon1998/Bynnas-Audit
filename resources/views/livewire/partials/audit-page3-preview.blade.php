@php
    $dash = '………………';
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

<div class="page">
    @include('audits.partials.toc-table', [
        'rows' => $tocPage3Rows,
        'showTitle' => true,
        'titleMarginTop' => '0',
    ])

    <table class="sign-table compact" style="margin-top:3mm;">
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

    <div style="margin-top:3mm;">
        @include('audits.partials.classification-table-pdf')
    </div>

    <div class="page-num">3</div>
</div>
