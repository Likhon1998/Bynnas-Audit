{{-- Compliance template table: title + period + editable headers/rows/columns --}}
@php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $coreFields = ['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no'];
    $headers = array_values((array) ($block['headers'] ?? \App\Support\AuditTableHeaders::defaults()['compliance']));
    if (count($headers) < 6) {
        $headers = array_values(\App\Support\AuditTableHeaders::defaults()['compliance']);
    }
    $extraCount = max(0, count($headers) - count($coreFields));
    $rows = array_values((array) ($block['rows'] ?? []));
    $cellPad = $compact ? '' : 'border border-slate-800 px-1.5 py-1';
    $tableClass = $compact ? 'a4-table a4-table-compact text-[7.5px]' : 'w-full border-collapse text-[10px]';
@endphp

<div class="mt-[3mm] mb-[4mm]" wire:key="compliance-{{ $blockIndex }}" id="{{ \App\Livewire\MakeAuditReport::sectionAnchorId((string) ($block['serial'] ?? 'compliance')) }}">
    @if ($editable)
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <p class="text-[11px] font-semibold text-emerald-800">কমপ্লায়েন্স টেবিল</p>
            <button type="button" wire:click="fillComplianceBlockFromReport({{ $blockIndex }})" class="rounded border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-700 hover:bg-slate-50">রিপোর্টের নম্বর বসান</button>
            <button type="button" wire:click="addComplianceBlockColumn({{ $blockIndex }})" class="rounded bg-emerald-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-emerald-800">+ কলাম</button>
            <button type="button" wire:click="addComplianceBlockRow({{ $blockIndex }})" class="rounded bg-emerald-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-emerald-800">+ সারি</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'up')" class="ml-auto text-[11px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock({{ $blockIndex }})" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
        </div>
    @endif

    @if ($editable)
        <div class="mb-3 space-y-2 rounded border border-emerald-100 bg-emerald-50/30 p-3">
            <p class="text-center text-[10px] font-semibold uppercase tracking-wide text-emerald-800">শিরোনাম (প্রিভিউ/PDF/Doc-এর মতো কেন্দ্রীয়)</p>
            <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.title" class="finding-serial-input w-full rounded border border-slate-200 bg-white px-2 py-1.5 text-center text-[13px] font-bold" placeholder="৫.০ বিগত অভ্যন্তরীণ নিরীক্ষা…">
            <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.title_en" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px] font-semibold" placeholder="(Compliance of Previous Internal Audit Report Reply)">
            <div class="flex flex-wrap items-center justify-center gap-2 text-[11px]">
                <span class="font-semibold">নিরীক্ষাকাল ঃ</span>
                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.period" class="min-w-[200px] rounded border border-slate-200 bg-white px-2 py-1 text-center" placeholder="-ডিসেম্বর’২৫ থেকে মার্চ’২৬">
            </div>
            <div class="flex flex-wrap items-center justify-center gap-2 text-[11px]">
                <span class="font-semibold">ফলোআপের তারিখ ঃ</span>
                <x-audit-date-field wire:model.blur="reportBlocks.{{ $blockIndex }}.followup_date" format="dmy" class="min-w-[120px] rounded border border-slate-200 bg-white px-2 py-1 text-center" />
            </div>
        </div>
    @else
        @include('audits.partials.compliance-heading', ['block' => $block, 'forDoc' => $compact])
    @endif

    @if ($editable)
        <x-audit-excel-paste-zone
            path="reportBlocks.{{ $blockIndex }}.rows"
            :columns="array_merge($coreFields, collect(range(0, max(0, $extraCount - 1)))->map(fn ($i) => 'extra.'.$i)->all())"
            hint="Compliance: Excel থেকে কলামগুলো একই ক্রমে পেস্ট করুন"
        />
    @endif

    <div class="overflow-x-auto">
        <table class="{{ $tableClass }} min-w-full">
            <thead>
                <tr class="bg-slate-100">
                    @foreach ($headers as $hi => $label)
                        <th class="{{ $cellPad }} font-semibold text-center align-middle">
                            @if ($editable)
                                <div class="flex items-start gap-1">
                                    <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.headers.{{ $hi }}" class="w-full border-0 bg-transparent text-center text-[10px] font-semibold">
                                    @if ($hi >= 6)
                                        <button type="button" wire:click="removeComplianceBlockColumn({{ $blockIndex }}, {{ $hi }})" class="shrink-0 text-[10px] text-rose-600" title="কলাম মুছুন">×</button>
                                    @endif
                                </div>
                            @else
                                {{ $label }}
                            @endif
                        </th>
                    @endforeach
                    @if ($editable)
                        <th class="{{ $cellPad }} w-8"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $rowIndex => $row)
                    <tr>
                        @foreach ($coreFields as $field)
                            <td class="{{ $cellPad }} align-top">
                                @if ($editable)
                                    <textarea wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.{{ $field }}" rows="3" class="w-full border-0 bg-sky-50/50 p-1 text-[10px] leading-snug"></textarea>
                                @else
                                    <span class="whitespace-pre-wrap">{{ $row[$field] ?? '' }}</span>
                                @endif
                            </td>
                        @endforeach
                        @for ($ei = 0; $ei < $extraCount; $ei++)
                            <td class="{{ $cellPad }} align-top">
                                @if ($editable)
                                    <textarea wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.extra.{{ $ei }}" rows="3" class="w-full border-0 bg-sky-50/50 p-1 text-[10px] leading-snug"></textarea>
                                @else
                                    <span class="whitespace-pre-wrap">{{ $row['extra'][$ei] ?? '' }}</span>
                                @endif
                            </td>
                        @endfor
                        @if ($editable)
                            <td class="{{ $cellPad }} text-center align-top">
                                @if (count($rows) > 1)
                                    <button type="button" wire:click="removeComplianceBlockRow({{ $blockIndex }}, {{ $rowIndex }})" class="text-[11px] font-semibold text-rose-600">×</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
