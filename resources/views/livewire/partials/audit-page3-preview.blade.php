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

<div class="a4-sheet">
    <div class="a4-inner official-preview text-black">
        <div class="a4-body">
            @include('livewire.partials.audit-toc-table-preview', [
                'rows' => $tocPage3Rows,
                'showTitle' => true,
                'compact' => true,
            ])

            <div class="mt-[8mm] grid grid-cols-3 gap-[3mm] text-[10.5px] leading-[1.45]">
                <div class="min-h-[28mm] border border-black p-[2.5mm]">
                    <p>নিরীক্ষা কর্মকর্তার নাম: <span class="font-semibold">{{ $sign_auditor_name !== '' ? $sign_auditor_name : $dash }}</span></p>
                    <p class="mt-[2.5mm]">পদবী: <span class="font-semibold">{{ $sign_auditor_designation !== '' ? $sign_auditor_designation : $dash }}</span></p>
                    <p class="mt-[2.5mm]">তারিখ: <span class="font-semibold">{{ $fmt($sign_auditor_date) }}</span></p>
                </div>
                <div class="min-h-[28mm] border border-black p-[2.5mm]">
                    <p>শাখা ব্যবস্থাপকের নাম: <span class="font-semibold">{{ $sign_bm_name !== '' ? $sign_bm_name : $dash }}</span></p>
                    <p class="mt-[8mm]">তারিখ: <span class="font-semibold">{{ $fmt($sign_bm_date) }}</span></p>
                </div>
                <div class="min-h-[28mm] border border-black p-[2.5mm]">
                    <p>সহকারী শাখা ব্যবস্থাপকের নাম: <span class="font-semibold">{{ $sign_abm_name !== '' ? $sign_abm_name : $dash }}</span></p>
                    <p class="mt-[8mm]">তারিখ: <span class="font-semibold">{{ $fmt($sign_abm_date) }}</span></p>
                </div>
            </div>
        </div>

        <p class="a4-page-num">3</p>
    </div>
</div>
