@php
    use App\Livewire\MakeAuditReport;
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $tableClass = $compact ? 'a4-table a4-table-compact text-[9.5px]' : 'a4-table text-[10.5px]';
    $obsTableClass = $compact ? 'a4-table a4-table-compact text-[9px]' : 'a4-table text-[10px]';

    $blocks = $reportBlocks ?? [];
    if ($blocks === []) {
        $sections = $reportSections ?? [];
        if ($sections === [] && ! empty($financialFindings)) {
            $sections = [[
                'serial' => '১.০',
                'title' => $financial_section_title ?? '১.০ আর্থিক নিরীক্ষা',
                'findings' => $financialFindings,
            ]];
        }
        foreach ($sections as $section) {
            $blocks[] = [
                'type' => 'section',
                'serial' => $section['serial'] ?? '১.০',
                'title' => $section['title'] ?? '',
            ];
            foreach (($section['findings'] ?? []) as $finding) {
                $blocks[] = array_merge(['type' => 'finding'], is_array($finding) ? $finding : []);
            }
        }
        $blocks[] = ['type' => 'criteria'];
        $blocks[] = ['type' => 'observation'];
        $blocks[] = ['type' => 'stats'];
        $blocks[] = ['type' => 'stats'];
    }
@endphp

@foreach ($blocks as $bIndex => $block)
    @php $type = $block['type'] ?? ''; @endphp

    @if ($editable)
        @include('livewire.partials.audit-block-insert-menu', ['insertIndex' => $bIndex])
    @endif

    @if ($type === 'section')
        @php $sectionAnchor = MakeAuditReport::sectionAnchorId($block['serial'] ?? ($block['title'] ?? '')); @endphp
        @if ($sectionAnchor !== '')
            <a id="{{ $sectionAnchor }}" name="{{ $sectionAnchor }}"></a>
        @endif
        @if ($editable)
            <div class="mb-[3mm] flex flex-wrap items-center gap-2 {{ $bIndex > 0 ? 'mt-[2mm]' : '' }}" @if ($sectionAnchor !== '') data-outline-id="{{ $sectionAnchor }}" @endif>
                <input
                    type="text"
                    wire:model.live="reportBlocks.{{ $bIndex }}.serial"
                    class="finding-serial-input h-8 w-[72px] rounded border border-slate-200 bg-sky-50/40 px-1 text-center text-[12px] font-bold"
                    title="বিভাগ ক্রমিক"
                >
                <input
                    type="text"
                    wire:model.live="reportBlocks.{{ $bIndex }}.title"
                    class="finding-serial-input min-w-[220px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                    placeholder="বিভাগের শিরোনাম"
                >
                <div class="ml-auto flex flex-wrap items-center gap-1">
                    <button type="button" wire:click="moveBlock({{ $bIndex }}, 'up')" class="h-7 rounded border border-slate-200 px-2 text-[11px] text-slate-600 hover:bg-slate-50" title="উপরে">↑</button>
                    <button type="button" wire:click="moveBlock({{ $bIndex }}, 'down')" class="h-7 rounded border border-slate-200 px-2 text-[11px] text-slate-600 hover:bg-slate-50" title="নিচে">↓</button>
                    <button type="button" wire:click="removeBlock({{ $bIndex }})" class="h-7 rounded border border-rose-200 px-2 text-[11px] text-rose-600 hover:bg-rose-50">
                        বিভাগ মুছুন
                    </button>
                </div>
            </div>
        @else
            <p class="mb-[2mm] {{ $bIndex > 0 ? 'mt-[4mm]' : '' }} font-bold finding-heading">{!! \App\Support\BanglaNumerals::highlight(($block['title'] ?? $block['serial'] ?? ''), 'serial') !!}</p>
        @endif

    @elseif ($type === 'finding')
        @php $anchor = MakeAuditReport::findingAnchorId($block['serial'] ?? ''); @endphp
        @if ($anchor !== '')
            <a id="{{ $anchor }}" name="{{ $anchor }}"></a>
        @endif
        <table class="{{ $tableClass }} mb-[2mm]">
            <tbody>
                <tr>
                    <td style="width:9%;" class="align-top text-center font-bold finding-serial-cell">
                        @include('livewire.partials.audit-finding-serial-cell', [
                            'editable' => $editable,
                            'wireModel' => $editable ? 'reportBlocks.'.$bIndex.'.serial' : null,
                            'value' => $block['serial'] ?? '',
                        ])
                    </td>
                    <td style="width:11%;" class="align-top text-center font-bold">
                        @if ($editable)
                            <input type="text" wire:model.live="reportBlocks.{{ $bIndex }}.title" class="w-full border-0 bg-sky-50/40 text-center font-bold" placeholder="শিরোনাম">
                        @else
                            {{ $block['title'] ?? 'শিরোনাম' }}
                        @endif
                    </td>
                    <td class="align-top">
                        @if ($editable)
                            @include('livewire.partials.audit-indicator-combobox', [
                                'index' => $bIndex,
                                'value' => $block['body'] ?? '',
                                'indicators' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
                                'collection' => 'reportBlocks',
                                'wireKey' => 'blk-ind-'.$bIndex.'-'.md5((string) ($block['body'] ?? '')),
                            ])
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px]">
                                <span class="font-semibold">টাকার পরিমাণ:</span>
                                <input type="text" wire:model.live="reportBlocks.{{ $bIndex }}.amount" class="inline-input min-w-[100px]">
                            </div>
                        @else
                            <p class="m-0 whitespace-pre-wrap text-justify leading-[1.45]">{{ $block['body'] ?? '' }}</p>
                            @if (($block['amount'] ?? '') !== '')
                                <p class="mt-[1mm] m-0"><span class="font-semibold">টাকার পরিমাণ:</span> {{ $block['amount'] }}</p>
                            @endif
                        @endif
                    </td>
                    <td style="width:17%;" class="align-top p-0">
                        @include('livewire.partials.audit-rating-box', [
                            'rating' => $block['rating'] ?? '',
                            'editable' => $editable,
                            'wireModel' => $editable ? 'reportBlocks.'.$bIndex.'.rating' : null,
                            'findingRatings' => $findingRatings ?? [],
                        ])
                    </td>
                </tr>
            </tbody>
        </table>
        @if ($editable)
            <div class="mb-[3mm] flex flex-wrap items-center justify-end gap-2">
                <button type="button" wire:click="moveBlock({{ $bIndex }}, 'up')" class="text-[11px] text-slate-600 hover:underline">↑ উপরে</button>
                <button type="button" wire:click="moveBlock({{ $bIndex }}, 'down')" class="text-[11px] text-slate-600 hover:underline">↓ নিচে</button>
                <button
                    type="button"
                    wire:click="removeBlock({{ $bIndex }})"
                    class="text-[11px] text-rose-600 hover:underline"
                >শিরোনাম মুছুন</button>
            </div>
        @endif

    @elseif ($type === 'criteria')
        <div class="mt-[3mm]">
            @if ($editable)
                <div class="mb-1 flex flex-wrap items-center gap-2">
                    <input
                        type="text"
                        wire:model.live="reportBlocks.{{ $bIndex }}.label"
                        class="min-w-[200px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                        placeholder="প্রচলিত নিয়ম (Criteria):"
                    >
                    <button type="button" wire:click="moveBlock({{ $bIndex }}, 'up')" class="text-[11px] text-slate-600 hover:underline">↑</button>
                    <button type="button" wire:click="moveBlock({{ $bIndex }}, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
                    <button type="button" wire:click="removeBlock({{ $bIndex }})" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
                </div>
                <textarea
                    wire:model.live="reportBlocks.{{ $bIndex }}.body"
                    rows="4"
                    class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px] leading-relaxed"
                    placeholder="প্রচলিত নিয়ম লিখুন…"
                ></textarea>
            @else
                <p class="mb-[1mm] font-bold">{{ $block['label'] ?? 'প্রচলিত নিয়ম (Criteria):' }}</p>
                <p class="m-0 text-justify leading-[1.45]">{{ $block['body'] ?? $financial_criteria }}</p>
            @endif
        </div>

    @elseif ($type === 'observation')
        <div class="mt-[3mm]">
            @if ($editable)
                <div class="mb-1 flex flex-wrap items-center gap-2">
                    <input
                        type="text"
                        wire:model.live="reportBlocks.{{ $bIndex }}.label"
                        class="min-w-[200px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                        placeholder="পর্যবেক্ষণ (Observation) :"
                    >
                    <button type="button" wire:click="moveBlock({{ $bIndex }}, 'up')" class="text-[11px] text-slate-600 hover:underline">↑</button>
                    <button type="button" wire:click="moveBlock({{ $bIndex }}, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
                    <button type="button" wire:click="removeBlock({{ $bIndex }})" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
                </div>
                <textarea
                    wire:model.live="reportBlocks.{{ $bIndex }}.body"
                    rows="3"
                    class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px] leading-relaxed"
                    placeholder="পর্যবেক্ষণ লিখুন…"
                ></textarea>
            @else
                @if (($block['label'] ?? '') !== '')
                    <p class="mb-[1mm] font-bold">{{ $block['label'] }}</p>
                @endif
                @if (($block['body'] ?? '') !== '')
                    <p class="m-0 whitespace-pre-wrap text-justify leading-[1.45]">{{ $block['body'] }}</p>
                @else
                    <p class="m-0 border-b border-dotted border-black">&nbsp;</p>
                @endif
            @endif
        </div>

    @elseif (in_array($type, ['stats', 'vat', 'tax'], true))
        @php
            $obsHeading = (string) ($block['heading'] ?? ($type === 'tax' ? 'Report Rating Box:' : ($type === 'vat' ? 'Report Rating Box:' : 'Report Rating Box:')));
            if ($obsHeading === 'ভ্যাট সংক্রান্ত:' || $obsHeading === 'ট্যাক্স সংক্রান্ত:' || $obsHeading === 'সারণী:' || $obsHeading === 'নতুন সারণী:') {
                $obsHeading = 'Report Rating Box:';
            }
            $obsRows = array_values((array) ($block['rows'] ?? (
                $type === 'tax' ? ($taxObservationRows ?? []) : ($vatObservationRows ?? [])
            )));
            if ($obsRows === []) {
                $obsRows = [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']];
            }
        @endphp
        <div class="mt-[3mm]">
            @if ($editable)
                <div class="mb-1 flex flex-wrap items-center gap-2">
                    <input
                        type="text"
                        wire:model.live="reportBlocks.{{ $bIndex }}.heading"
                        class="min-w-[200px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                        placeholder="Report Rating Box:"
                    >
                    <button type="button" wire:click="moveBlock({{ $bIndex }}, 'up')" class="text-[11px] text-slate-600 hover:underline">↑</button>
                    <button type="button" wire:click="moveBlock({{ $bIndex }}, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
                    <button type="button" wire:click="removeBlock({{ $bIndex }})" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
                </div>
            @elseif ($obsHeading !== '')
                <p class="mb-[1mm] font-bold">{{ $obsHeading }}</p>
            @endif

            <table class="{{ $obsTableClass }} mb-[2mm]">
                @include('livewire.partials.audit-stats-thead', [
                    'editable' => $editable,
                    'cellPad' => '',
                    'variant' => 'stats',
                ])
                <tbody>
                    @foreach ($obsRows as $rowIndex => $row)
                        <tr>
                            @foreach (['total_population', 'sample_size', 'instances_found', 'percentage'] as $field)
                                <td class="text-center">
                                    @if ($editable)
                                        <input type="text" wire:model.live="reportBlocks.{{ $bIndex }}.rows.{{ $rowIndex }}.{{ $field }}" class="w-full border-0 bg-transparent text-center text-[11px]">
                                    @else
                                        {{ $row[$field] ?? '' }}
                                    @endif
                                </td>
                            @endforeach
                            @if ($editable)
                                <td class="text-center">
                                    @if (count($obsRows) > 1)
                                        <button type="button" wire:click="removeObservationBlockRow({{ $bIndex }}, {{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($editable)
                <button type="button" wire:click="addObservationBlockRow({{ $bIndex }})" class="mb-[2mm] text-[11px] font-medium text-[#2b579a]">+ সারি যোগ</button>
            @endif
        </div>

    @elseif ($type === 'custom_table')
        @include('livewire.partials.audit-custom-table-block', [
            'editable' => $editable,
            'compact' => $compact,
            'blockIndex' => $bIndex,
            'block' => $block,
            'customTableEditorIndex' => $customTableEditorIndex ?? null,
            'customTableSizeCols' => $customTableSizeCols ?? null,
            'customTableSizeRows' => $customTableSizeRows ?? null,
        ])

    @elseif ($type === 'jobab_table')
        @include('livewire.partials.audit-jobab-table-block', [
            'editable' => $editable,
            'compact' => $compact,
            'blockIndex' => $bIndex,
            'block' => $block,
        ])
    @endif
@endforeach

@if ($editable)
    @include('livewire.partials.audit-block-insert-menu', ['insertIndex' => count($blocks), 'end' => true])
@endif
