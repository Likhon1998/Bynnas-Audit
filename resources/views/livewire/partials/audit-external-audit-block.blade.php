{{-- Previous External Audit Report compliance — insertable, rows/columns editable --}}
@php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $coreFields = ['area_of_observation', 'year_of_reporting', 'external_observation', 'compliance', 'internal_index_no'];
    $headers = array_values((array) ($block['headers'] ?? \App\Support\AuditTableHeaders::defaults()['external_audit']));
    if (count($headers) < 5) {
        $headers = array_values(\App\Support\AuditTableHeaders::defaults()['external_audit']);
    }
    $extraCount = max(0, count($headers) - count($coreFields));
    $rows = array_values((array) ($block['rows'] ?? []));
    $cellPad = $compact ? 'border border-slate-800 px-1 py-1' : 'border border-slate-800 px-1.5 py-1.5';
    $tableClass = $compact
        ? 'a4-table a4-table-compact text-[9.5px] external-audit-table'
        : 'w-full border-collapse text-[11px] leading-snug external-audit-table';
    $titleSize = $compact ? '12px' : '14px';
    $metaSize = $compact ? '10.5px' : '12px';
@endphp

<div class="mt-[3mm] mb-[4mm]" wire:key="external-audit-{{ $blockIndex }}" id="{{ \App\Livewire\MakeAuditReport::sectionAnchorId((string) ($block['serial'] ?? 'external')) }}">
    @if ($editable)
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <p class="text-[12px] font-semibold text-amber-900">বহিঃ নিরীক্ষা কমপ্লায়েন্স</p>
            <button type="button" wire:click="addExternalAuditBlockRow({{ $blockIndex }})" class="rounded bg-amber-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-amber-800">+ সারি</button>
            <button type="button" wire:click="addExternalAuditBlockColumn({{ $blockIndex }})" class="rounded bg-amber-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-amber-800">+ কলাম</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'up')" class="ml-auto text-[12px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'down')" class="text-[12px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock({{ $blockIndex }})" class="text-[12px] text-rose-600 hover:underline">মুছুন</button>
        </div>

        <div class="mb-3 space-y-1.5 rounded border border-amber-200 bg-amber-50/50 p-3 text-center">
            <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.title" class="finding-serial-input w-full rounded border border-slate-200 bg-white px-2 py-1.5 text-center text-[14px] font-bold underline" placeholder="৭.০ Compliance of Previous External Audit Report">
            <div class="flex flex-wrap items-center justify-center gap-2 pt-1 text-[12px]">
                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.branch_label" class="w-36 rounded border border-slate-200 bg-white px-2 py-1 text-center font-semibold" placeholder="Name of Branch----">
                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.branch" class="min-w-[160px] rounded border border-slate-200 bg-white px-2 py-1 text-center" placeholder="শাখার নাম">
            </div>
        </div>
    @else
        <div style="text-align:center;margin:0 0 3.5mm;">
            <p class="bold finding-heading" style="margin:0 0 2mm;font-size:{{ $titleSize }};font-weight:700;text-decoration:underline;line-height:1.35;">{!! \App\Support\BanglaNumerals::highlight($block['title'] ?? '', 'serial') !!}</p>
            <p style="margin:0 0 2.5mm;font-size:{{ $metaSize }};line-height:1.45;font-weight:600;">
                {{ $block['branch_label'] ?? 'Name of Branch----' }} {{ $block['branch'] ?? '' }}
            </p>
        </div>
    @endif

    @if ($editable)
        <x-audit-excel-paste-zone
            path="reportBlocks.{{ $blockIndex }}.rows"
            :columns="array_merge($coreFields, collect(range(0, max(0, $extraCount - 1)))->map(fn ($i) => 'extra.'.$i)->all())"
            hint="External audit: Excel থেকে কলামগুলো একই ক্রমে পেস্ট করুন"
        />
    @endif

    <div class="overflow-x-auto">
        <table class="{{ $tableClass }} min-w-full" style="table-layout:fixed;width:100%;">
            <colgroup>
                <col style="width:14%;">
                <col style="width:11%;">
                <col style="width:{{ $extraCount === 0 ? '38%' : '32%' }};">
                <col style="width:22%;">
                <col style="width:15%;">
                @for ($ei = 0; $ei < $extraCount; $ei++)
                    <col style="width:8%;">
                @endfor
                @if ($editable)
                    <col style="width:3%;">
                @endif
            </colgroup>
            <thead>
                <tr class="bg-[#f0e4d4]">
                    @foreach ($headers as $hi => $label)
                        <th class="{{ $cellPad }} font-bold text-center align-middle" style="background:#f0e4d4;">
                            @if ($editable)
                                <div class="flex items-start gap-1">
                                    <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.headers.{{ $hi }}" class="w-full border-0 bg-transparent text-center text-[11px] font-bold">
                                    @if ($hi >= 5)
                                        <button type="button" wire:click="removeExternalAuditBlockColumn({{ $blockIndex }}, {{ $hi }})" class="shrink-0 text-rose-600" title="কলাম মুছুন">×</button>
                                    @endif
                                </div>
                            @else
                                {{ $label }}
                            @endif
                        </th>
                    @endforeach
                    @if ($editable)
                        <th class="{{ $cellPad }}"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $rowIndex => $row)
                    <tr>
                        @foreach ($coreFields as $field)
                            <td class="{{ $cellPad }} align-top {{ in_array($field, ['area_of_observation', 'year_of_reporting', 'internal_index_no'], true) ? 'text-center' : 'text-left' }}">
                                @if ($editable)
                                    @if (in_array($field, ['external_observation', 'compliance'], true))
                                        <textarea wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.{{ $field }}" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                                    @else
                                        <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.{{ $field }}" class="w-full border-0 bg-sky-50/40 px-1 text-center text-[11px]">
                                    @endif
                                @else
                                    <span class="whitespace-pre-wrap">{{ $row[$field] ?? '' }}</span>
                                @endif
                            </td>
                        @endforeach
                        @for ($ei = 0; $ei < $extraCount; $ei++)
                            <td class="{{ $cellPad }} align-top">
                                @if ($editable)
                                    <textarea wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.extra.{{ $ei }}" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                                @else
                                    <span class="whitespace-pre-wrap">{{ $row['extra'][$ei] ?? '' }}</span>
                                @endif
                            </td>
                        @endfor
                        @if ($editable)
                            <td class="{{ $cellPad }} text-center align-top">
                                @if (count($rows) > 1)
                                    <button type="button" wire:click="removeExternalAuditBlockRow({{ $blockIndex }}, {{ $rowIndex }})" class="text-[12px] text-rose-600">×</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
