{{-- Read-only Format 3 full template for storehouse --}}
@php
    $questions = $definition['questions'] ?? [];
    $rowCount = (int) ($definition['default_stats_rows'] ?? 5);
@endphp

<div class="pointer-events-none select-none overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm opacity-95">
    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <p class="text-[11px] font-semibold text-slate-500">Format: {{ $formatModel?->format_number }}</p>
        <p class="text-[15px] font-bold text-navy-900">{{ $formatModel?->org_name }}</p>
        <p class="text-[12px] font-semibold text-slate-700">{{ $formatModel?->dept_name }}</p>
        <p class="mt-1 text-[13px] font-bold text-navy-900">“{{ $formatModel?->heading }}”</p>
    </div>

    <div class="grid gap-3 border-b border-slate-200 px-4 py-3 sm:grid-cols-2">
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">শাখার নাম :</label>
            <div class="flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] text-slate-400">…………………………</div>
        </div>
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">নিরীক্ষা কাল :</label>
            <div class="flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] text-slate-400">…………………………</div>
        </div>
    </div>

    <div class="overflow-x-auto px-2 py-3">
        <table class="min-w-[980px] w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-center font-semibold text-slate-700">
                    <th class="border border-slate-300 px-1 py-1.5">ক্রমিক নং</th>
                    <th class="border border-slate-300 px-1 py-1.5">মাঠকর্মকর্তার নাম</th>
                    <th class="border border-slate-300 px-1 py-1.5">সমিতি নং</th>
                    <th class="border border-slate-300 px-1 py-1.5">সমিতি গঠনের তারিখ</th>
                    <th class="border border-slate-300 px-1 py-1.5">সমিতি গ্রহণের তারিখ</th>
                    <th class="border border-slate-300 px-1 py-1.5">সদস্য সংখ্যা</th>
                    <th class="border border-slate-300 px-1 py-1.5">ঋণী সংখ্যা</th>
                    <th class="border border-slate-300 px-1 py-1.5">সঞ্চয়স্থিতি</th>
                    <th class="border border-slate-300 px-1 py-1.5">ঋণস্থিতি</th>
                    <th class="border border-slate-300 px-1 py-1.5">বকেয়া জন</th>
                    <th class="border border-slate-300 px-1 py-1.5">বকেয়া টাকা</th>
                </tr>
            </thead>
            <tbody>
                @for ($ri = 0; $ri < $rowCount; $ri++)
                    <tr>
                        <td class="border border-slate-300 px-1 py-2 text-center">{{ $ri + 1 }}</td>
                        @for ($c = 0; $c < 10; $c++)
                            <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="overflow-x-auto border-t border-slate-200 px-2 py-3">
        <table class="min-w-[860px] w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-center font-semibold text-slate-700">
                    <th class="border border-slate-300 px-1 py-1.5 w-12">ক্রমিক নং</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-left">বিবরণ</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="2">নিয়ম মেনে চলা</th>
                    <th class="border border-slate-300 px-1 py-1.5">ঘটনার সংখ্যা পরীক্ষা</th>
                    <th class="border border-slate-300 px-1 py-1.5">WP Ref *</th>
                </tr>
                <tr class="bg-slate-50 text-center font-semibold text-slate-600">
                    <th class="border border-slate-300" colspan="2"></th>
                    <th class="border border-slate-300 px-1 py-1">হ্যাঁ</th>
                    <th class="border border-slate-300 px-1 py-1">না</th>
                    <th class="border border-slate-300" colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($questions as $qi => $question)
                    <tr>
                        <td class="border border-slate-300 px-1 py-1.5 text-center">{{ $qi + 1 }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-left text-slate-800">{{ $question }}</td>
                        <td class="border border-slate-300 px-1 py-1.5 text-center text-slate-300">□</td>
                        <td class="border border-slate-300 px-1 py-1.5 text-center text-slate-300">□</td>
                        <td class="border border-slate-300 px-1 py-1.5">&nbsp;</td>
                        <td class="border border-slate-300 px-1 py-1.5">&nbsp;</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 px-4 py-3">
        <p class="mb-1 text-[12px] font-bold text-navy-900">সারসংক্ষেপঃ</p>
        <div class="min-h-[96px] rounded-md border border-slate-200 bg-white px-3 py-2 text-[12px] text-slate-300">…………………………………………</div>
    </div>
</div>
