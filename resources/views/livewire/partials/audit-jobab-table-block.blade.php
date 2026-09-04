{{-- Jobab (reply) table: label | value rows, add row/column --}}
@php
    $editable = $editable ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $rows = array_values((array) ($block['rows'] ?? []));
    if ($rows === []) {
        $rows = [
            ['cells' => ['শাখা ব্যবস্থাপকের জবাব', '']],
            ['cells' => ['সমস্যা সমাধানের ক্ষেত্রে দায়িত্বপ্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ', '']],
            ['cells' => ['সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)', '']],
        ];
    }
    $colCount = max(2, count($rows[0]['cells'] ?? []));
    foreach ($rows as $ri => $row) {
        $cells = array_values((array) ($row['cells'] ?? []));
        while (count($cells) < $colCount) {
            $cells[] = '';
        }
        $rows[$ri]['cells'] = array_slice($cells, 0, $colCount);
    }
    $tableClass = ($compact ?? false) ? 'a4-table a4-table-compact text-[9.5px]' : 'a4-table text-[10.5px]';
    $labelWidth = $colCount === 2 ? '38%' : null;
@endphp

<div class="mt-[3mm]" wire:key="jobab-{{ $blockIndex }}">
    @if ($editable)
        <div class="mb-1 flex flex-wrap items-center gap-2">
            <p class="text-[11px] font-semibold text-slate-700">জবাব টেবিল</p>
            <button type="button" wire:click="addJobabRow({{ $blockIndex }})" class="rounded bg-sky-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-sky-800">+ সারি</button>
            <button type="button" wire:click="addJobabColumn({{ $blockIndex }})" class="rounded bg-sky-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-sky-800">+ কলাম</button>
            @if ($colCount > 1)
                <button type="button" wire:click="removeJobabColumn({{ $blockIndex }})" class="rounded border border-slate-300 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-700 hover:bg-slate-50">কলাম −</button>
            @endif
            @if (count($rows) > 1)
                <button type="button" wire:click="removeJobabRow({{ $blockIndex }}, {{ count($rows) - 1 }})" class="rounded border border-rose-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-rose-700 hover:bg-rose-50">শেষ সারি −</button>
            @endif
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'up')" class="ml-auto text-[11px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock({{ $blockIndex }})" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="{{ $tableClass }} mb-[2mm] w-full border-collapse">
            <tbody>
                @foreach ($rows as $rIndex => $row)
                    <tr>
                        @foreach ($row['cells'] as $cIndex => $cell)
                            <td
                                class="border border-slate-700 px-1.5 py-1 align-top {{ $cIndex === 0 ? 'font-semibold bg-slate-50' : '' }}"
                                @if ($cIndex === 0 && $labelWidth) style="width: {{ $labelWidth }};" @endif
                            >
                                @if ($editable)
                                    <textarea
                                        wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rIndex }}.cells.{{ $cIndex }}"
                                        rows="{{ $cIndex === 0 ? 2 : 3 }}"
                                        class="w-full resize-y border-0 bg-transparent text-[11px] leading-snug {{ $cIndex === 0 ? 'font-semibold' : '' }}"
                                    ></textarea>
                                @else
                                    <span class="whitespace-pre-wrap">{{ $cell }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
