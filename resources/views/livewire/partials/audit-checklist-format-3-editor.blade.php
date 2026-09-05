{{-- Format 3 editor. Expects: $formatModel, $definition, $payload --}}
@php
    $questions = $definition['questions'] ?? [];
    $statsRows = $payload['stats_rows'] ?? [];
    $items = $payload['items'] ?? [];
@endphp

<div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <p class="text-[11px] font-semibold text-slate-500">Format: {{ $formatModel?->format_number }}</p>
        <p class="text-[15px] font-bold text-navy-900">{{ $formatModel?->org_name }}</p>
        <p class="text-[12px] font-semibold text-slate-700">{{ $formatModel?->dept_name }}</p>
        <p class="mt-1 text-[13px] font-bold text-navy-900">“{{ $formatModel?->heading }}”</p>
    </div>

    <div class="grid gap-3 border-b border-slate-200 px-4 py-3 sm:grid-cols-2">
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">শাখার নাম :</label>
            <input type="text" wire:model.live="shakha_name" class="h-9 w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]">
        </div>
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">নিরীক্ষা কাল :</label>
            <input type="text" wire:model.live="audit_period" class="h-9 w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]">
        </div>
    </div>

    <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-3 py-2">
        <p class="text-[12px] font-semibold text-navy-900">সমিতি / মাঠকর্মী তথ্য</p>
        <button type="button" wire:click="addRow" class="rounded border border-sky-200 bg-sky-50 px-2.5 py-1 text-[11px] font-semibold text-[#2b579a] hover:bg-sky-100">+ Row</button>
    </div>

    <div class="overflow-x-auto px-2 py-3">
        <table class="min-w-[980px] w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-center font-semibold text-slate-700">
                    <th class="border border-slate-300 px-1 py-1.5 w-10">ক্রমিক নং</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[110px]">মাঠকর্মকর্তার নাম</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[80px]">সমিতি নং</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[90px]">সমিতি গঠনের তারিখ</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[90px]">সমিতি গ্রহণের তারিখ</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[70px]">সদস্য সংখ্যা</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[70px]">ঋণী সংখ্যা</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[80px]">সঞ্চয়স্থিতি</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[80px]">ঋণস্থিতি</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[70px]">বকেয়া জন</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[80px]">বকেয়া টাকা</th>
                    <th class="border border-slate-300 px-1 py-1.5 w-10"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statsRows as $ri => $row)
                    <tr wire:key="f3-stats-{{ $ri }}">
                        <td class="border border-slate-300 px-1 py-1 text-center tabular-nums">{{ $ri + 1 }}</td>
                        @foreach (['fo_name','society_no','formed_date','accepted_date','member_count','borrower_count','savings_balance','loan_balance','arrear_count','arrear_amount'] as $field)
                            <td class="border border-slate-300 p-0.5">
                                <input type="text" wire:model.live="payload.stats_rows.{{ $ri }}.{{ $field }}" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                            </td>
                        @endforeach
                        <td class="border border-slate-300 p-0.5 text-center">
                            <button type="button" wire:click="removeRow({{ $ri }})" class="text-[11px] text-rose-500 hover:text-rose-700">×</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="overflow-x-auto border-t border-slate-200 px-2 py-3">
        <table class="min-w-[860px] w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-center font-semibold text-slate-700">
                    <th class="border border-slate-300 px-1 py-1.5 w-12">ক্রমিক নং</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-left min-w-[280px]">বিবরণ</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="2">নিয়ম মেনে চলা</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[100px]">ঘটনার সংখ্যা পরীক্ষা</th>
                    <th class="border border-slate-300 px-1 py-1.5 w-20">WP Ref *</th>
                </tr>
                <tr class="bg-slate-50 text-center font-semibold text-slate-600">
                    <th class="border border-slate-300" colspan="2"></th>
                    <th class="border border-slate-300 px-1 py-1 w-14">হ্যাঁ</th>
                    <th class="border border-slate-300 px-1 py-1 w-14">না</th>
                    <th class="border border-slate-300" colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($questions as $qi => $question)
                    <tr wire:key="f3-item-{{ $qi }}">
                        <td class="border border-slate-300 px-1 py-1.5 text-center tabular-nums">{{ $qi + 1 }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-left text-slate-800">{{ $question }}</td>
                        <td class="border border-slate-300 px-1 py-1 text-center">
                            <input
                                type="radio"
                                wire:model.live="payload.items.{{ $qi }}.compliance"
                                value="yes"
                                class="border-slate-300 text-[#2b579a] focus:ring-[#2b579a]"
                            >
                        </td>
                        <td class="border border-slate-300 px-1 py-1 text-center">
                            <input
                                type="radio"
                                wire:model.live="payload.items.{{ $qi }}.compliance"
                                value="no"
                                class="border-slate-300 text-[#2b579a] focus:ring-[#2b579a]"
                            >
                        </td>
                        <td class="border border-slate-300 p-0.5">
                            <input type="text" wire:model.live="payload.items.{{ $qi }}.incident_count" class="h-8 w-full border-0 bg-transparent px-1 text-center text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                        </td>
                        <td class="border border-slate-300 p-0.5">
                            <input type="text" wire:model.live="payload.items.{{ $qi }}.wp_ref" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 px-4 py-3">
        <label class="mb-1 block text-[12px] font-bold text-navy-900">সারসংক্ষেপঃ</label>
        <textarea
            wire:model.live="summary"
            rows="4"
            class="w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]"
            placeholder="সারসংক্ষেপ লিখুন…"
        ></textarea>
    </div>
</div>
