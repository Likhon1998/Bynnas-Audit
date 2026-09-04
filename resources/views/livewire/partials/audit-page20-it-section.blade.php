@php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $cellPad = $compact ? '' : 'border border-slate-800 px-1 py-0.5';
@endphp

<div class="mb-4">
    @if ($editable)
        <input type="text" wire:model.live="page20_it_title" class="finding-serial-input mb-3 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold">
    @else
        <p class="mb-3 text-center text-[12px] font-bold finding-heading">{!! \App\Support\BanglaNumerals::highlight($page20_it_title ?? '', 'serial') !!}</p>
    @endif

    <div class="mb-3 text-center text-[11px] leading-relaxed">
        @if ($editable)
            <input type="text" wire:model.live="page20_it_org_line1" class="mb-1 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-center">
            <input type="text" wire:model.live="page20_it_org_line2" class="mb-1 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-center">
            <input type="text" wire:model.live="page20_it_org_line3" class="mb-1 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-center">
        @else
            <p class="m-0">{{ $page20_it_org_line1 ?? '' }}</p>
            <p class="m-0">{{ $page20_it_org_line2 ?? '' }}</p>
            <p class="m-0">{{ $page20_it_org_line3 ?? '' }}</p>
        @endif
    </div>

    <div class="mb-3 flex flex-wrap justify-center gap-4 text-[11px]">
        <div class="flex items-center gap-2">
            <span class="font-semibold">কর্মসূচীর নাম:</span>
            @if ($editable)
                <input type="text" wire:model.live="page20_it_program" class="min-w-[120px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1">
            @else
                <span>{{ $page20_it_program ?? '' }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <span class="font-semibold">শাখার নাম:</span>
            @if ($editable)
                <input type="text" wire:model.live="page20_it_branch" class="min-w-[160px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1">
            @else
                <span>{{ $page20_it_branch ?? '' }}</span>
            @endif
        </div>
    </div>

    @if ($editable)
        <input type="text" wire:model.live="page20_it_instruction" class="mb-3 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-center text-[11px]" placeholder="প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন">
    @else
        <p class="mb-2 text-center text-[11px] font-semibold">{{ $page20_it_instruction ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন' }}</p>
    @endif

    <div class="overflow-x-auto">
        @if ($editable)
            <x-audit-excel-paste-zone
                path="page20ItChecklistRows"
                :columns="['sl_no', 'description', 'compliance', 'action_owner', 'management_comments', 'recommendation']"
                hint="IT checklist: Excel থেকে কলাম ক্রমে পেস্ট (Compliance = Yes/No/N/A)"
            />
        @endif
        <table class="{{ $compact ? 'a4-table a4-table-compact text-[7.5px]' : 'w-full border-collapse text-[9px]' }} min-w-full">
            <thead>
                <tr class="bg-slate-100">
                    <th class="{{ $cellPad }} font-semibold text-center">ক্রমিক</th>
                    <th class="{{ $cellPad }} font-semibold text-center">বিবরণ</th>
                    <th class="{{ $cellPad }} font-semibold text-center" colspan="3">Compliance</th>
                    <th class="{{ $cellPad }} font-semibold text-center">Action Owner (কার দায়িত্ব)</th>
                    <th class="{{ $cellPad }} font-semibold text-center">Management Comments (ব্যবস্থাপনার মন্তব্য)</th>
                    <th class="{{ $cellPad }} font-semibold text-center">Recommendation (সুপারিশ)</th>
                    @if ($editable)
                        <th class="{{ $cellPad }}"></th>
                    @endif
                </tr>
                <tr class="bg-slate-100">
                    <th class="{{ $cellPad }}" colspan="2"></th>
                    <th class="{{ $cellPad }} font-semibold text-center">Yes</th>
                    <th class="{{ $cellPad }} font-semibold text-center">No</th>
                    <th class="{{ $cellPad }} font-semibold text-center">N/A</th>
                    <th class="{{ $cellPad }}" colspan="3"></th>
                    @if ($editable)
                        <th class="{{ $cellPad }}"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach (($page20ItChecklistRows ?? []) as $rowIndex => $row)
                    @php $compliance = (string) ($row['compliance'] ?? ''); @endphp
                    <tr>
                        <td class="{{ $cellPad }} text-center align-top">
                            @if ($editable)
                                <input type="text" wire:model.live="page20ItChecklistRows.{{ $rowIndex }}.sl_no" class="w-full border-0 bg-sky-50/50 px-0.5 text-center text-[8px]">
                            @else
                                {{ $row['sl_no'] ?? '' }}
                            @endif
                        </td>
                        <td class="{{ $cellPad }} align-top">
                            @if ($editable)
                                <textarea wire:model.live="page20ItChecklistRows.{{ $rowIndex }}.description" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                            @else
                                <span class="whitespace-pre-wrap">{{ $row['description'] ?? '' }}</span>
                            @endif
                        </td>
                        @if ($editable)
                            <td class="{{ $cellPad }} text-center align-top" colspan="3">
                                <select wire:model.live="page20ItChecklistRows.{{ $rowIndex }}.compliance" class="w-full rounded border border-slate-200 bg-sky-50/40 px-1 py-0.5 text-[8px]">
                                    <option value="">—</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                    <option value="na">N/A</option>
                                </select>
                            </td>
                        @else
                            <td class="{{ $cellPad }} text-center align-top">{{ $compliance === 'yes' ? '✓' : '' }}</td>
                            <td class="{{ $cellPad }} text-center align-top">{{ $compliance === 'no' ? '✓' : '' }}</td>
                            <td class="{{ $cellPad }} text-center align-top">{{ $compliance === 'na' ? '✓' : '' }}</td>
                        @endif
                        <td class="{{ $cellPad }} align-top">
                            @if ($editable)
                                <input type="text" wire:model.live="page20ItChecklistRows.{{ $rowIndex }}.action_owner" class="w-full border-0 bg-sky-50/50 px-0.5 text-[8px]">
                            @else
                                {{ $row['action_owner'] ?? '' }}
                            @endif
                        </td>
                        <td class="{{ $cellPad }} align-top">
                            @if ($editable)
                                <textarea wire:model.live="page20ItChecklistRows.{{ $rowIndex }}.management_comments" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                            @else
                                <span class="whitespace-pre-wrap">{{ $row['management_comments'] ?? '' }}</span>
                            @endif
                        </td>
                        <td class="{{ $cellPad }} align-top">
                            @if ($editable)
                                <textarea wire:model.live="page20ItChecklistRows.{{ $rowIndex }}.recommendation" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                            @else
                                <span class="whitespace-pre-wrap">{{ $row['recommendation'] ?? '' }}</span>
                            @endif
                        </td>
                        @if ($editable)
                            <td class="{{ $cellPad }} text-center align-top">
                                @if (count($page20ItChecklistRows ?? []) > 1)
                                    <button type="button" wire:click="removePage20ItChecklistRow({{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($editable)
        <button type="button" wire:click="addPage20ItChecklistRow" class="mt-2 text-[11px] font-medium text-[#2b579a]">+ IT checklist row</button>
    @endif
</div>
