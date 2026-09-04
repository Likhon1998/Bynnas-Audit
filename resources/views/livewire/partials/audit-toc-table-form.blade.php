@php
    /** @var int $previewPage */
    $previewPage = $previewPage ?? 2;
    $hToc = $tableHeaders['toc'] ?? \App\Support\AuditTableHeaders::defaults()['toc'];
    $tocWidths = ['w-[70px]', '', 'w-[90px]', 'w-[130px]', 'w-[100px]', 'w-[70px]'];
@endphp

<div class="mb-2 flex flex-wrap items-center gap-2">
    <p class="mr-auto text-[11px] font-semibold uppercase tracking-wide text-slate-500">
        সূচিপত্র @if($previewPage === 2) (শুরু) @else (বাকি অংশ) @endif
        <span class="ml-2 font-normal normal-case text-slate-400">· শুধু রিপোর্টের শিরোনাম (পৃষ্ঠা ৪ থেকে)</span>
    </p>
</div>

<div class="overflow-x-auto">
    <table class="w-full min-w-[900px] border-collapse text-[11px]">
        <thead>
            <tr class="bg-slate-200">
                @foreach ($hToc as $hi => $label)
                    <x-audit-th
                        :editable="true"
                        :wire="'tableHeaders.toc.'.$hi"
                        class="{{ $tocWidths[$hi] ?? '' }} border border-slate-800 px-1 py-1.5 font-semibold"
                    >{{ $label }}</x-audit-th>
                @endforeach
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
