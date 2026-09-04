{{-- Shared custom table HTML (merges + column widths). Always wire:model when editable so save/reload keeps inputs editable. --}}
@php
    use App\Support\CustomTableSchema;
    $editable = (bool) ($editable ?? false);
    $selectable = (bool) ($selectable ?? false);
    $alpineSelect = (bool) ($alpineSelect ?? false);
    $blockIndex = (int) ($blockIndex ?? 0);
    $table = CustomTableSchema::normalize(is_array($block ?? []) ? $block : []);
    $columns = $table['columns'];
    $rows = $table['rows'];
    $leaves = CustomTableSchema::leafColumns($columns);
    $leafCount = count($leaves);
    $widths = CustomTableSchema::leafWidths($columns);
    $headerMatrix = CustomTableSchema::headerMatrix($columns);
    $paint = CustomTableSchema::bodyPaintPlan($table);
    $tableClass = ($compact ?? false) ? 'a4-table a4-table-compact text-[9px]' : 'a4-table text-[10.5px]';
@endphp

<table class="{{ $tableClass }} mb-[2mm] w-full border-collapse" style="table-layout: fixed;" wire:key="ct-table-{{ $blockIndex }}-{{ $leafCount }}-{{ count($rows) }}">
    @if ($leafCount > 0)
        <colgroup>
            @foreach ($widths as $w)
                <col style="width: {{ $w }}%;">
            @endforeach
        </colgroup>
    @endif
    <thead>
        @foreach ($headerMatrix as $hRow)
            <tr>
                @foreach ($hRow as $hCell)
                    <th
                        class="border border-slate-700 bg-slate-200 px-1 py-1 text-center font-bold align-middle"
                        colspan="{{ $hCell['colspan'] }}"
                        rowspan="{{ $hCell['rowspan'] }}"
                    >{{ $hCell['text'] }}</th>
                @endforeach
            </tr>
        @endforeach
    </thead>
    <tbody>
        @foreach ($rows as $rIndex => $row)
            @php $isTotal = (bool) ($row['is_total'] ?? false); @endphp
            <tr class="{{ $isTotal ? 'font-bold' : '' }}">
                @for ($c = 0; $c < $leafCount; $c++)
                    @php
                        $cell = $paint[$rIndex][$c] ?? null;
                        if (! $cell || ($cell['skip'] ?? false)) {
                            continue;
                        }
                        $rs = max(1, (int) ($cell['rowspan'] ?? 1));
                        $cs = max(1, (int) ($cell['colspan'] ?? 1));
                        $alignClass = ($c <= 2 && $rs > 1) ? 'align-middle' : 'align-top';
                        $textAlign = ($c === 2 && $cs === 1 && $rs === 1) ? 'text-left' : 'text-center';
                    @endphp
                    <td
                        class="border border-slate-700 px-1 py-0.5 {{ $alignClass }} {{ $textAlign }} {{ $selectable || $alpineSelect ? 'cursor-pointer' : '' }}"
                        rowspan="{{ $rs }}"
                        colspan="{{ $cs }}"
                        @if ($alpineSelect)
                            data-merge-rs="{{ $rs }}"
                            data-merge-cs="{{ $cs }}"
                            @click="selectCell({{ $rIndex }}, {{ $c }}, $event.currentTarget)"
                            :class="selR === {{ $rIndex }} && selC === {{ $c }} ? 'ring-2 ring-inset ring-violet-500 bg-violet-50' : ''"
                        @elseif ($selectable)
                            wire:click="selectCustomTableCell({{ $rIndex }}, {{ $c }})"
                        @endif
                    >
                        @if ($editable)
                            <input
                                type="text"
                                wire:key="ct-cell-{{ $blockIndex }}-{{ $rIndex }}-{{ $c }}"
                                wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rIndex }}.cells.{{ $c }}"
                                class="w-full border-0 bg-transparent {{ $textAlign }} text-[11px] {{ $isTotal ? 'font-bold' : '' }}"
                                @if ($alpineSelect || $selectable) @click.stop @endif
                            >
                        @else
                            {{ $cell['text'] ?? '' }}
                        @endif
                    </td>
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>
