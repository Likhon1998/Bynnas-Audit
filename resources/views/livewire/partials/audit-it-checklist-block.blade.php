{{-- IT (Software) checklist — readable type, clear ticks, formal table --}}
@php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $cellPad = $compact ? 'border border-slate-800 px-1 py-1' : 'border border-slate-800 px-1.5 py-1.5';
    $tableClass = $compact
        ? 'a4-table a4-table-compact text-[9.5px]'
        : 'w-full border-collapse text-[11px] leading-snug';
    $hItR1 = array_values((array) ($block['headers_r1'] ?? \App\Support\AuditTableHeaders::defaults()['it_r1']));
    $hItR2 = array_values((array) ($block['headers_r2'] ?? \App\Support\AuditTableHeaders::defaults()['it_r2']));
    $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));
    $rows = array_values((array) ($block['rows'] ?? []));
    $bodySize = $compact ? '9.5px' : '11px';
    $titleSize = $compact ? '12px' : '14px';
    $metaSize = $compact ? '10px' : '12px';
@endphp

<div class="mt-[3mm] mb-[4mm]" wire:key="it-checklist-{{ $blockIndex }}" id="{{ \App\Livewire\MakeAuditReport::sectionAnchorId((string) ($block['serial'] ?? 'it')) }}">
    @if ($editable)
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <p class="text-[12px] font-semibold text-blue-900">আইটি (সফটওয়্যার) চেকলিস্ট</p>
            <button type="button" wire:click="addItChecklistBlockRow({{ $blockIndex }})" class="rounded bg-blue-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-blue-800">+ সারি</button>
            <button type="button" wire:click="addItChecklistBlockColumn({{ $blockIndex }})" class="rounded bg-blue-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-blue-800">+ কলাম</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'up')" class="ml-auto text-[12px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'down')" class="text-[12px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock({{ $blockIndex }})" class="text-[12px] text-rose-600 hover:underline">মুছুন</button>
        </div>

        <div class="mb-3 space-y-1.5 rounded border border-slate-300 bg-slate-50/80 p-3 text-center">
            <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.title" class="finding-serial-input w-full rounded border border-slate-200 bg-white px-2 py-1.5 text-center text-[14px] font-bold">
            <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.org_line1" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px]">
            <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.org_line2" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px]">
            <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.org_line3" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px]">
            <div class="flex flex-wrap items-center justify-center gap-3 pt-1 text-[12px]">
                <span class="font-semibold">কর্মসূচীর নাম :</span>
                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.program" class="min-w-[110px] rounded border border-slate-200 bg-white px-2 py-1 text-center">
                <span class="font-semibold">শাখার নাম :</span>
                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.branch" class="min-w-[150px] rounded border border-slate-200 bg-white px-2 py-1 text-center">
            </div>
            <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.instruction" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px] font-semibold">
        </div>
    @else
        <div style="text-align:center;margin:0 0 3.5mm;">
            <p class="bold finding-heading" style="margin:0 0 1.5mm;font-size:{{ $titleSize }};font-weight:700;line-height:1.35;">{!! \App\Support\BanglaNumerals::highlight($block['title'] ?? '', 'serial') !!}</p>
            @foreach (['org_line1', 'org_line2', 'org_line3'] as $orgKey)
                @if (trim((string) ($block[$orgKey] ?? '')) !== '')
                    <p style="margin:0;font-size:{{ $metaSize }};line-height:1.45;">{{ $block[$orgKey] }}</p>
                @endif
            @endforeach
            <p style="margin:2mm 0 1mm;font-size:{{ $metaSize }};line-height:1.45;">
                <span class="font-semibold">কর্মসূচীর নাম :</span> {{ $block['program'] ?? '' }}
                &nbsp;&nbsp;&nbsp;
                <span class="font-semibold">শাখার নাম :</span> {{ $block['branch'] ?? '' }}
            </p>
            <p class="bold" style="margin:0 0 2.5mm;font-size:{{ $metaSize }};font-weight:700;">{{ $block['instruction'] ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন' }}</p>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="{{ $tableClass }} it-checklist-table min-w-full" style="table-layout:fixed;width:100%;">
            <colgroup>
                <col style="width:6%;">
                <col style="width:34%;">
                <col style="width:5%;">
                <col style="width:5%;">
                <col style="width:5%;">
                <col style="width:12%;">
                <col style="width:14%;">
                <col style="width:{{ $extraHeaders === [] ? '19%' : '12%' }};">
                @foreach ($extraHeaders as $unused)
                    <col style="width:7%;">
                @endforeach
                @if ($editable)
                    <col style="width:4%;">
                @endif
            </colgroup>
            <thead>
                <tr class="bg-slate-200">
                    <th class="{{ $cellPad }} font-bold text-center align-middle" rowspan="2">{{ $hItR1[0] ?? 'ক্র. নং' }}</th>
                    <th class="{{ $cellPad }} font-bold text-center align-middle" rowspan="2">{{ $hItR1[1] ?? 'বিবরণ' }}</th>
                    <th class="{{ $cellPad }} font-bold text-center align-middle" colspan="3">{{ $hItR1[2] ?? 'Compliance' }}</th>
                    <th class="{{ $cellPad }} font-bold text-center align-middle" rowspan="2">{{ $hItR1[3] ?? 'Action Owner' }}</th>
                    <th class="{{ $cellPad }} font-bold text-center align-middle" rowspan="2">{{ $hItR1[4] ?? 'Management Comments' }}</th>
                    <th class="{{ $cellPad }} font-bold text-center align-middle" rowspan="2">{{ $hItR1[5] ?? 'Recommendation' }}</th>
                    @foreach ($extraHeaders as $ei => $extraLabel)
                        <th class="{{ $cellPad }} font-bold text-center align-middle" rowspan="2">
                            @if ($editable)
                                <div class="flex items-start gap-1">
                                    <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.extra_headers.{{ $ei }}" class="w-full border-0 bg-transparent text-center text-[11px] font-bold">
                                    <button type="button" wire:click="removeItChecklistBlockColumn({{ $blockIndex }}, {{ $ei }})" class="text-rose-600">×</button>
                                </div>
                            @else
                                {{ $extraLabel }}
                            @endif
                        </th>
                    @endforeach
                    @if ($editable)
                        <th class="{{ $cellPad }}" rowspan="2"></th>
                    @endif
                </tr>
                <tr class="bg-slate-100">
                    @foreach ($hItR2 as $label)
                        <th class="{{ $cellPad }} font-bold text-center align-middle">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $rowIndex => $row)
                    @php $compliance = (string) ($row['compliance'] ?? ''); @endphp
                    <tr x-data="{ compliance: @js($compliance) }">
                        <td class="{{ $cellPad }} text-center align-middle font-semibold" style="font-size:{{ $bodySize }};">{{ $row['sl_no'] ?? '' }}</td>
                        <td class="{{ $cellPad }} align-top" style="font-size:{{ $bodySize }};">
                            @if ($editable && trim((string) ($row['description'] ?? '')) === '')
                                <textarea wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.description" rows="2" class="w-full border-0 bg-sky-50/50 p-1 text-[11px]" placeholder="নতুন বিবরণ…"></textarea>
                            @else
                                <span class="whitespace-pre-wrap">{{ $row['description'] ?? '' }}</span>
                            @endif
                        </td>
                        @if ($editable)
                            @foreach (['yes' => 'Yes', 'no' => 'No', 'na' => 'N/A'] as $val => $lab)
                                <td class="{{ $cellPad }} text-center align-middle">
                                    <button
                                        type="button"
                                        @click="
                                            compliance = compliance === '{{ $val }}' ? '' : '{{ $val }}';
                                            $wire.set('reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.compliance', compliance, false);
                                        "
                                        :class="compliance === '{{ $val }}'
                                            ? 'border-emerald-700 bg-emerald-100 text-emerald-900'
                                            : 'border-slate-300 bg-white text-slate-300 hover:bg-slate-50'"
                                        class="mx-auto flex h-7 w-7 items-center justify-center rounded border text-[14px] font-bold"
                                        title="{{ $lab }}"
                                        x-text="compliance === '{{ $val }}' ? '✓' : ''"
                                    ></button>
                                </td>
                            @endforeach
                        @else
                            <td class="{{ $cellPad }} text-center align-middle"><span class="it-tick">{!! $compliance === 'yes' ? '✓' : '&nbsp;' !!}</span></td>
                            <td class="{{ $cellPad }} text-center align-middle"><span class="it-tick">{!! $compliance === 'no' ? '✓' : '&nbsp;' !!}</span></td>
                            <td class="{{ $cellPad }} text-center align-middle"><span class="it-tick">{!! $compliance === 'na' ? '✓' : '&nbsp;' !!}</span></td>
                        @endif
                        <td class="{{ $cellPad }} align-top" style="font-size:{{ $bodySize }};">
                            @if ($editable)
                                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.action_owner" class="w-full border-0 bg-sky-50/40 px-1 text-[11px]">
                            @else
                                {{ $row['action_owner'] ?? '' }}
                            @endif
                        </td>
                        <td class="{{ $cellPad }} align-top" style="font-size:{{ $bodySize }};">
                            @if ($editable)
                                <textarea wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.management_comments" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                            @else
                                <span class="whitespace-pre-wrap">{{ $row['management_comments'] ?? '' }}</span>
                            @endif
                        </td>
                        <td class="{{ $cellPad }} align-top" style="font-size:{{ $bodySize }};">
                            @if ($editable)
                                <textarea wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.recommendation" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                            @else
                                <span class="whitespace-pre-wrap">{{ $row['recommendation'] ?? '' }}</span>
                            @endif
                        </td>
                        @foreach ($extraHeaders as $ei => $unused)
                            <td class="{{ $cellPad }} align-top" style="font-size:{{ $bodySize }};">
                                @if ($editable)
                                    <textarea wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.extra.{{ $ei }}" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                                @else
                                    <span class="whitespace-pre-wrap">{{ $row['extra'][$ei] ?? '' }}</span>
                                @endif
                            </td>
                        @endforeach
                        @if ($editable)
                            <td class="{{ $cellPad }} text-center align-top">
                                @if (count($rows) > 1)
                                    <button type="button" wire:click="removeItChecklistBlockRow({{ $blockIndex }}, {{ $rowIndex }})" class="text-[12px] text-rose-600">×</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
