{{-- Read-only Format 5 full template for storehouse --}}
@php
    $questions = $definition['questions'] ?? [];
    $checkCount = (int) ($definition['check_count'] ?? 17);
    $rowCount = (int) ($definition['default_rows'] ?? 2);
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
        <table class="min-w-[1200px] w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-center font-semibold text-slate-700">
                    <th class="border border-slate-300 px-1 py-1.5">ক্রমিক নং</th>
                    <th class="border border-slate-300 px-1 py-1.5">সমিতির নাম ও কোড</th>
                    <th class="border border-slate-300 px-1 py-1.5">সদস্যের নাম ও আইডি</th>
                    <th class="border border-slate-300 px-1 py-1.5">ফেরতের তারিখ</th>
                    <th class="border border-slate-300 px-1 py-1.5">ভাউচার নং</th>
                    <th class="border border-slate-300 px-1 py-1.5">টাকা</th>
                    @for ($i = 1; $i <= $checkCount; $i++)
                        <th class="border border-slate-300 px-0.5 py-1.5 w-7">{{ $i }}</th>
                    @endfor
                    <th class="border border-slate-300 px-1 py-1.5">WP Ref</th>
                </tr>
            </thead>
            <tbody>
                @for ($ri = 0; $ri < $rowCount; $ri++)
                    <tr>
                        <td class="border border-slate-300 px-1 py-2 text-center">{{ $ri + 1 }}</td>
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        @for ($c = 0; $c < $checkCount; $c++)
                            <td class="border border-slate-300 px-1 py-2 text-center text-slate-300">□</td>
                        @endfor
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 px-4 py-3">
        <p class="mb-2 text-[12px] font-bold text-navy-900">চেকলিস্ট পয়েন্ট (১–{{ $checkCount }})</p>
        <ol class="columns-1 gap-x-6 space-y-1 text-[11px] leading-snug text-slate-700 md:columns-2">
            @foreach ($questions as $qi => $q)
                <li class="break-inside-avoid pl-1"><span class="font-semibold text-slate-800">{{ $qi + 1 }}.</span> {{ $q }}</li>
            @endforeach
        </ol>
    </div>

    <div class="border-t border-slate-200 px-4 py-3">
        <p class="mb-1 text-[12px] font-bold text-navy-900">সারসংক্ষেপ:</p>
        <div class="min-h-[96px] rounded-md border border-slate-200 bg-white px-3 py-2 text-[12px] text-slate-300">…………………………………………</div>
    </div>
</div>
