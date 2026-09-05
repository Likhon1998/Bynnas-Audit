{{-- Read-only Format 1 full template for storehouse --}}
@php
    $sections = $definition['sections'] ?? [];
    $blankPayload = \App\Support\AuditChecklistCatalog::blankPayload($definition);
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
                    <th class="border border-slate-300 px-1 py-1.5 w-10">ক্রঃ নং</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[140px]">সমিতির নাম ও আইডি</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[100px]">সমিতি শুরুর তারিখ</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[110px]">মাঠকর্মীর নাম</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="5">সমিতি গঠন</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="3">সমিতি বন্ধ /একত্রিকরণ</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="4">সমিতি স্থানান্তর (পার্শ্ববর্তী শাখায়)</th>
                    <th class="border border-slate-300 px-1 py-1.5 w-16">WP Ref.</th>
                </tr>
                <tr class="bg-slate-50 text-center font-semibold text-slate-600">
                    <th class="border border-slate-300" colspan="4"></th>
                    @for ($i = 1; $i <= 5; $i++)
                        <th class="border border-slate-300 px-0.5 py-1 w-8">{{ $i }}</th>
                    @endfor
                    @for ($i = 1; $i <= 3; $i++)
                        <th class="border border-slate-300 px-0.5 py-1 w-8">{{ $i }}</th>
                    @endfor
                    @for ($i = 1; $i <= 4; $i++)
                        <th class="border border-slate-300 px-0.5 py-1 w-8">{{ $i }}</th>
                    @endfor
                    <th class="border border-slate-300"></th>
                </tr>
            </thead>
            <tbody>
                @foreach (['formation' => 'সমিতি গঠন', 'closure' => 'সমিতি বন্ধ /একত্রিকরণ', 'transfer' => 'সমিতি স্থানান্তর (পার্শ্ববর্তী শাখায়)'] as $sectionKey => $sectionLabel)
                    @php $rows = $blankPayload['sections'][$sectionKey] ?? [[], []]; @endphp
                    <tr class="bg-sky-50/70">
                        <td colspan="17" class="border border-slate-300 px-2 py-1.5 text-[12px] font-bold text-navy-900">{{ $sectionLabel }}</td>
                    </tr>
                    @foreach ($rows as $ri => $row)
                        <tr>
                            <td class="border border-slate-300 px-1 py-2 text-center tabular-nums">{{ $ri + 1 }}</td>
                            <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                            <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                            <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                            @for ($c = 0; $c < 5; $c++)
                                <td class="border border-slate-300 px-1 py-2 text-center text-slate-300">{{ $sectionKey === 'formation' ? '□' : '' }}</td>
                            @endfor
                            @for ($c = 0; $c < 3; $c++)
                                <td class="border border-slate-300 px-1 py-2 text-center text-slate-300">{{ $sectionKey === 'closure' ? '□' : '' }}</td>
                            @endfor
                            @for ($c = 0; $c < 4; $c++)
                                <td class="border border-slate-300 px-1 py-2 text-center text-slate-300">{{ $sectionKey === 'transfer' ? '□' : '' }}</td>
                            @endfor
                            <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="grid gap-3 border-t border-slate-200 px-4 py-3 lg:grid-cols-3">
        @foreach ($sections as $section)
            <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                <p class="mb-2 text-[12px] font-bold text-navy-900">{{ $section['label'] }}</p>
                <ol class="list-decimal space-y-1.5 pl-4 text-[11px] leading-snug text-slate-700">
                    @foreach (($section['questions'] ?? []) as $q)
                        <li>{{ $q }}</li>
                    @endforeach
                </ol>
            </div>
        @endforeach
    </div>

    <div class="border-t border-slate-200 px-4 py-3">
        <p class="mb-1 text-[12px] font-bold text-navy-900">সারসংক্ষেপ:</p>
        <div class="min-h-[96px] rounded-md border border-slate-200 bg-white px-3 py-2 text-[12px] text-slate-300">…………………………………………</div>
    </div>
</div>
