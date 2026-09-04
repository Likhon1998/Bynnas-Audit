@php
    $depth = (int) ($depth ?? 0);
    $column = is_array($column ?? null) ? $column : [];
    $colId = (string) ($column['id'] ?? '');
    $children = array_values((array) ($column['children'] ?? []));
    $path = array_values((array) ($path ?? []));
    $pad = $depth * 14;
    $showWidth = (bool) ($showWidth ?? false);
    $isLeaf = $children === [];

    $segments = ['columns'];
    foreach ($path as $i => $idx) {
        if ($i === 0) {
            $segments[] = (string) $idx;
        } else {
            $segments[] = 'children';
            $segments[] = (string) $idx;
        }
    }
    $labelWire = 'reportBlocks.'.$blockIndex.'.'.implode('.', $segments).'.label';
@endphp

<div class="rounded border border-slate-100 bg-slate-50/80 px-2 py-1" style="margin-left: {{ $pad }}px;">
    <div class="flex flex-wrap items-center gap-1">
        <input
            type="text"
            wire:model.blur="{{ $labelWire }}"
            class="min-w-[120px] flex-1 rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[11px] font-semibold"
            placeholder="কলাম নাম"
        >

        @if ($showWidth && $isLeaf)
            <label class="flex items-center gap-0.5 text-[10px] text-slate-500" title="কলামের প্রস্থ %">
                <span>প্রস্থ</span>
                <input
                    type="number"
                    min="4"
                    max="80"
                    step="1"
                    value="{{ isset($column['width']) ? (float) $column['width'] : '' }}"
                    placeholder="auto"
                    class="w-14 rounded border border-slate-200 bg-white px-1 py-0.5 text-[11px]"
                    wire:change="setCustomTableLeafWidth({{ (int) $blockIndex }}, '{{ $colId }}', $event.target.value)"
                >
                <span>%</span>
            </label>
        @endif

        <button
            type="button"
            wire:click="addCustomTableColumn({{ (int) $blockIndex }}, '{{ $colId }}')"
            class="rounded border border-violet-300 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-violet-700 hover:bg-violet-50"
            title="এই কলামের নিচে সাব-কলাম"
        >+ সাব</button>

        <button
            type="button"
            wire:click="removeCustomTableColumn({{ (int) $blockIndex }}, '{{ $colId }}')"
            class="rounded border border-rose-200 px-1.5 py-0.5 text-[10px] text-rose-600 hover:bg-rose-50"
        >×</button>
    </div>

    @foreach ($children as $childIndex => $child)
        @include('livewire.partials.audit-custom-table-column-node', [
            'blockIndex' => $blockIndex,
            'column' => $child,
            'depth' => $depth + 1,
            'path' => array_merge($path, [$childIndex]),
            'showWidth' => $showWidth,
        ])
    @endforeach
</div>
