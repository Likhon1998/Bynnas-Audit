{{-- Nested custom table editor / display --}}
@php
    use App\Support\CustomTableSchema;
    $editable = $editable ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $table = CustomTableSchema::normalize(is_array($block ?? []) ? $block : []);
    $leafCount = CustomTableSchema::leafCount($table['columns']);
    $editorOpen = $editable && isset($customTableEditorIndex) && (int) $customTableEditorIndex === $blockIndex;
@endphp

<div class="mt-[3mm]" wire:key="custom-table-{{ $blockIndex }}">
    @if ($editable)
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <input
                type="text"
                wire:model.blur="reportBlocks.{{ $blockIndex }}.title"
                class="min-w-[220px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                placeholder="টেবিল শিরোনাম"
            >
            <button
                type="button"
                wire:click="openCustomTableEditor({{ $blockIndex }})"
                class="rounded bg-violet-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-violet-700"
            >Customize Table</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'up')" class="text-[11px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock({{ $blockIndex }})" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
        </div>
    @else
        <p class="mb-[1mm] font-bold">{{ $table['title'] }}</p>
    @endif

    <div class="overflow-x-auto">
        @include('livewire.partials.audit-custom-table-render', [
            'block' => $table,
            'blockIndex' => $blockIndex,
            'editable' => $editable,
            'selectable' => false,
            'compact' => $compact ?? false,
        ])
    </div>

    @if ($editable)
        <div class="mt-1 flex flex-wrap gap-2 text-right">
            <button type="button" wire:click="addCustomTableRow({{ $blockIndex }})" class="text-[10px] text-violet-700 hover:underline">+ সারি</button>
            @foreach ($table['rows'] as $rIndex => $row)
                @if ($rIndex === count($table['rows']) - 1)
                    <button type="button" wire:click="toggleCustomTableTotalRow({{ $blockIndex }}, {{ $rIndex }})" class="text-[10px] text-slate-500 hover:underline">
                        {{ ($row['is_total'] ?? false) ? 'মোট সারি বন্ধ' : 'মোট সারি' }}
                    </button>
                    @if (count($table['rows']) > 1)
                        <button type="button" wire:click="removeCustomTableRow({{ $blockIndex }}, {{ $rIndex }})" class="text-[10px] text-rose-600 hover:underline">শেষ সারি মুছুন</button>
                    @endif
                @endif
            @endforeach
        </div>
    @endif

    @if ($editorOpen)
        @include('livewire.partials.audit-custom-table-editor-modal', [
            'blockIndex' => $blockIndex,
            'table' => $table,
            'customTableSizeCols' => $customTableSizeCols ?? count($table['columns']),
            'customTableSizeRows' => $customTableSizeRows ?? count($table['rows']),
            'customTableSelR' => $customTableSelR ?? null,
            'customTableSelC' => $customTableSelC ?? null,
            'customTableMergeRows' => $customTableMergeRows ?? 2,
            'customTableMergeCols' => $customTableMergeCols ?? 1,
        ])
    @endif
</div>
