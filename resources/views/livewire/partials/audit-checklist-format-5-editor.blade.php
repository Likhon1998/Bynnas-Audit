{{-- Format 5 editor. Expects: $formatModel, $definition, $payload --}}
@php
    $questions = $definition['questions'] ?? [];
    $checkCount = (int) ($definition['check_count'] ?? 17);
    $rows = $payload['rows'] ?? [];
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

    <div class="flex items-center justify-end gap-2 border-b border-slate-100 px-3 py-2">
        <button type="button" wire:click="addRow" class="rounded border border-sky-200 bg-sky-50 px-2.5 py-1 text-[11px] font-semibold text-[#2b579a] hover:bg-sky-100">+ Row</button>
    </div>

    <div class="overflow-x-auto px-2 py-3">
        <table class="min-w-[1200px] w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-center font-semibold text-slate-700">
                    <th class="border border-slate-300 px-1 py-1.5 w-10">ক্রমিক নং</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[120px]">সমিতির নাম ও কোড</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[120px]">সদস্যের নাম ও আইডি</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[90px]">ফেরতের তারিখ</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[80px]">ভাউচার নং</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[70px]">টাকা</th>
                    @for ($i = 1; $i <= $checkCount; $i++)
                        <th class="border border-slate-300 px-0.5 py-1.5 w-7">{{ $i }}</th>
                    @endfor
                    <th class="border border-slate-300 px-1 py-1.5 w-14">WP Ref</th>
                    <th class="border border-slate-300 px-1 py-1.5 w-10"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $ri => $row)
                    <tr wire:key="f5-row-{{ $ri }}">
                        <td class="border border-slate-300 px-1 py-1 text-center tabular-nums">{{ $ri + 1 }}</td>
                        <td class="border border-slate-300 p-0.5">
                            <input type="text" wire:model.live="payload.rows.{{ $ri }}.society_name" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                        </td>
                        <td class="border border-slate-300 p-0.5">
                            <input type="text" wire:model.live="payload.rows.{{ $ri }}.member_name" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                        </td>
                        <td class="border border-slate-300 p-0.5">
                            <input type="text" wire:model.live="payload.rows.{{ $ri }}.refund_date" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                        </td>
                        <td class="border border-slate-300 p-0.5">
                            <input type="text" wire:model.live="payload.rows.{{ $ri }}.voucher_no" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                        </td>
                        <td class="border border-slate-300 p-0.5">
                            <input type="text" wire:model.live="payload.rows.{{ $ri }}.amount" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                        </td>
                        @for ($c = 0; $c < $checkCount; $c++)
                            <td class="border border-slate-300 p-0.5 text-center">
                                <select wire:model.live="payload.rows.{{ $ri }}.checks.{{ $c }}" class="h-8 w-full border-0 bg-transparent p-0 text-center text-[10px] focus:ring-1 focus:ring-[#2b579a]">
                                    <option value=""></option>
                                    <option value="✓">✓</option>
                                    <option value="✗">✗</option>
                                    <option value="N/A">N/A</option>
                                </select>
                            </td>
                        @endfor
                        <td class="border border-slate-300 p-0.5">
                            <input type="text" wire:model.live="payload.rows.{{ $ri }}.wp_ref" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                        </td>
                        <td class="border border-slate-300 p-0.5 text-center">
                            <button type="button" wire:click="removeRow({{ $ri }})" class="text-[11px] text-rose-500 hover:text-rose-700">×</button>
                        </td>
                    </tr>
                @endforeach
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
        <label class="mb-1 block text-[12px] font-bold text-navy-900">সারসংক্ষেপ:</label>
        <textarea
            wire:model.live="summary"
            rows="4"
            class="w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]"
            placeholder="সারসংক্ষেপ লিখুন…"
        ></textarea>
    </div>
</div>
