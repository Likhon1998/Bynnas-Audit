@php
    /** @var int $previewPage */
    $previewPage = $previewPage ?? 2;
@endphp

<div class="mb-2 flex flex-wrap items-center gap-2">
    <p class="mr-auto text-[11px] font-semibold uppercase tracking-wide text-slate-500">
        সূচিপত্র @if($previewPage === 2) (শুরু) @else (বাকি অংশ) @endif
    </p>
    <button type="button" wire:click="addTocSection(-1, {{ $previewPage }})" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Section</button>
    <button type="button" wire:click="addTocRow(-1, {{ $previewPage }})" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Row</button>
</div>

<div class="overflow-x-auto">
    <table class="w-full min-w-[900px] border-collapse text-[11px]">
        <thead>
            <tr class="bg-slate-200">
                <th class="w-[70px] border border-slate-800 px-1 py-1.5 font-semibold">ক্রমিক নং</th>
                <th class="border border-slate-800 px-1 py-1.5 font-semibold">নিরীক্ষায় প্রাপ্ত ঘটনা সমূহ</th>
                <th class="w-[90px] border border-slate-800 px-1 py-1.5 font-semibold">টাকা</th>
                <th class="w-[130px] border border-slate-800 px-1 py-1.5 font-semibold">রেটিং</th>
                <th class="w-[100px] border border-slate-800 px-1 py-1.5 font-semibold">বর্তমান অবস্থা</th>
                <th class="w-[70px] border border-slate-800 px-1 py-1.5 font-semibold">পৃষ্ঠা</th>
                <th class="w-[70px] border border-slate-800 px-1 py-1.5 font-semibold">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tocRows as $idx => $row)
                @continue((int) ($row['preview_page'] ?? 2) !== $previewPage)
                @php
                    $isSection = ($row['type'] ?? 'item') === 'section';
                    $ratingStyle = \App\Livewire\MakeAuditReport::findingRatingStyle($row['rating'] ?? '');
                @endphp
                <tr class="{{ $isSection ? 'bg-slate-100' : '' }}">
                    <td class="border border-slate-800 px-1 py-1">
                        <input type="text" wire:model.live="tocRows.{{ $idx }}.serial" class="finding-serial-input h-7 w-full border-0 bg-sky-50 px-1 text-center text-[11px] font-semibold focus:ring-1 focus:ring-sky-400">
                    </td>
                    <td class="border border-slate-800 px-1 py-1">
                        <input type="text" wire:model.live="tocRows.{{ $idx }}.finding" class="h-7 w-full border-0 bg-sky-50 px-1 text-[11px] {{ $isSection ? 'font-bold' : '' }} focus:ring-1 focus:ring-sky-400" placeholder="{{ $isSection ? 'Section title' : 'Finding' }}">
                    </td>
                    <td class="border border-slate-800 px-1 py-1">
                        @if ($isSection)
                            <span class="block text-center text-slate-400">—</span>
                        @else
                            <input type="text" wire:model.live="tocRows.{{ $idx }}.amount" class="h-7 w-full border-0 bg-sky-50 px-1 text-right text-[11px] focus:ring-1 focus:ring-sky-400">
                        @endif
                    </td>
                    <td class="border border-slate-800 px-0 py-0">
                        @if ($isSection)
                            <span class="block px-1 text-center text-slate-400">—</span>
                        @else
                            <select
                                wire:model.live="tocRows.{{ $idx }}.rating"
                                class="h-8 w-full border-0 px-1 text-[11px] font-semibold focus:ring-1 focus:ring-sky-400"
                                style="background: {{ $ratingStyle['bg'] }}; color: {{ $ratingStyle['color'] }};"
                            >
                                @foreach ($findingRatings as $opt)
                                    <option value="{{ $opt }}">{{ $opt !== '' ? $opt : '—' }}</option>
                                @endforeach
                            </select>
                        @endif
                    </td>
                    <td class="border border-slate-800 px-1 py-1">
                        @if ($isSection)
                            <span class="block text-center text-slate-400">—</span>
                        @else
                            <input type="text" wire:model.live="tocRows.{{ $idx }}.status" class="h-7 w-full border-0 bg-sky-50 px-1 text-[11px] focus:ring-1 focus:ring-sky-400">
                        @endif
                    </td>
                    <td class="border border-slate-800 px-1 py-1">
                        @if ($isSection)
                            <span class="block text-center text-slate-400">—</span>
                        @else
                            <input type="text" wire:model.live="tocRows.{{ $idx }}.page_no" class="h-7 w-full border-0 bg-sky-50 px-1 text-center text-[11px] focus:ring-1 focus:ring-sky-400">
                        @endif
                    </td>
                    <td class="border border-slate-800 px-1 py-1 text-center">
                        <button type="button" wire:click="removeTocRow({{ $idx }})" class="text-[11px] text-rose-600 hover:underline">×</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
