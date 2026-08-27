@php
    $bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $toBn = function (int $n) use ($bnDigits) {
        return implode('', array_map(fn ($d) => $bnDigits[(int) $d], str_split((string) $n)));
    };
@endphp

<div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
    <div class="mb-2 flex items-center justify-between gap-2">
        <p class="text-[12px] font-semibold text-slate-800">২. এক নজরে শাখার তথ্য</p>
        <span class="text-[11px] text-slate-500">Row/Column যোগ-বাদ করতে পারবেন · Preview এও দেখাবে</span>
    </div>

    <div class="mx-auto max-w-[960px] rounded-sm bg-white p-6 shadow-lg">
        <div class="mb-3 flex flex-wrap items-center gap-2 text-[13px]">
            <span class="font-bold">এক নজরে</span>
            <input type="text" wire:model.live="shakha_display_name" class="inline-input min-w-[180px] flex-1" placeholder="শাখার নাম">
            <span class="font-bold">শাখার তথ্য (</span>
            <input type="text" wire:model.live="glance_as_of" class="inline-input min-w-[140px]" placeholder="৩১ জুন ২০২৬">
            <span class="font-bold">):</span>
        </div>

        <p class="mb-3 flex flex-wrap items-center gap-2 text-[13px]">
            <span class="font-semibold">শাখা গঠনের তারিখ:</span>
            <input type="date" wire:model.live="branch_opening_date" class="inline-input">
            <span>ইং</span>
        </p>

        <div class="mb-2 flex items-center justify-between">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Glance table</p>
            <button type="button" wire:click="addGlanceRow" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Row</button>
        </div>

        <table class="mb-3 w-full border-collapse text-[12px]">
            <tbody>
                @foreach ($glanceRows as $idx => $row)
                    <tr>
                        <td class="w-[24%] border border-slate-800 px-1 py-1">
                            <input type="text" wire:model.live="glanceRows.{{ $idx }}.left_label" class="h-7 w-full border-0 bg-sky-50 px-1 text-[12px] focus:ring-1 focus:ring-sky-400" placeholder="Label">
                        </td>
                        <td class="w-[20%] border border-slate-800 px-1 py-1">
                            <input type="text" wire:model.live="glanceRows.{{ $idx }}.left_value" class="h-7 w-full border-0 bg-sky-50 px-1 text-[12px] focus:ring-1 focus:ring-sky-400" placeholder="Value">
                        </td>
                        <td class="w-[24%] border border-slate-800 px-1 py-1">
                            <input type="text" wire:model.live="glanceRows.{{ $idx }}.right_label" class="h-7 w-full border-0 bg-sky-50 px-1 text-[12px] focus:ring-1 focus:ring-sky-400" placeholder="Label">
                        </td>
                        <td class="w-[20%] border border-slate-800 px-1 py-1">
                            <input type="text" wire:model.live="glanceRows.{{ $idx }}.right_value" class="h-7 w-full border-0 bg-sky-50 px-1 text-[12px] focus:ring-1 focus:ring-sky-400" placeholder="Value">
                        </td>
                        <td class="w-[12%] border border-slate-800 px-1 py-1 text-center">
                            <button type="button" wire:click="removeGlanceRow({{ $idx }})" class="text-[11px] text-rose-600 hover:underline" @disabled(count($glanceRows) <= 1)>Remove</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="mb-2 flex flex-wrap items-center gap-2 text-[13px]">
            <span class="font-semibold">শাখার কর্মীর তথ্য :</span>
            <input type="date" wire:model.live="staff_info_as_of" class="inline-input">
            <span>ইং</span>
        </p>

        <div class="mb-2 flex flex-wrap items-center gap-2">
            <p class="mr-auto text-[11px] font-semibold uppercase tracking-wide text-slate-500">Staff table</p>
            <button type="button" wire:click="addStaffRow" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Row</button>
            <button type="button" wire:click="addStaffColumn" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Column</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] border-collapse text-[11px]">
                <thead>
                    <tr class="bg-slate-200">
                        <th class="border border-slate-800 px-1 py-1.5 font-semibold">ক্রমিক নং</th>
                        @foreach ($staffColumns as $cIdx => $col)
                            <th class="border border-slate-800 px-1 py-1.5">
                                <div class="flex items-center gap-1">
                                    <input type="text" wire:model.live="staffColumns.{{ $cIdx }}" class="h-7 min-w-[90px] flex-1 border-0 bg-transparent px-1 text-center text-[11px] font-semibold focus:bg-white focus:ring-1 focus:ring-sky-400">
                                    <button type="button" wire:click="removeStaffColumn({{ $cIdx }})" class="shrink-0 text-[10px] text-rose-600 hover:underline" title="Remove column" @disabled(count($staffColumns) <= 1)>×</button>
                                </div>
                            </th>
                        @endforeach
                        <th class="border border-slate-800 px-1 py-1.5 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffRows as $idx => $row)
                        <tr>
                            <td class="border border-slate-800 px-1 py-1 text-center">{{ $toBn($idx + 1) }}</td>
                            @foreach ($staffColumns as $cIdx => $col)
                                <td class="border border-slate-800 px-1 py-1">
                                    <input type="text" wire:model.live="staffRows.{{ $idx }}.cells.{{ $cIdx }}" class="h-7 w-full border-0 bg-sky-50 px-1 focus:ring-1 focus:ring-sky-400">
                                </td>
                            @endforeach
                            <td class="border border-slate-800 px-1 py-1 text-center">
                                <button type="button" wire:click="removeStaffRow({{ $idx }})" class="text-[11px] text-rose-600 hover:underline" @disabled(count($staffRows) <= 1)>Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 border-t border-dashed border-slate-200 pt-5">
            <h3 class="mb-3 text-center text-[14px] font-bold underline decoration-1 underline-offset-4">সূচিপত্র</h3>
            <p class="mb-3 text-center text-[11px] text-slate-500">PDF-এ পুরো সূচিপত্র এক নজরের পরে একসাথে বসবে</p>
            @include('livewire.partials.audit-toc-table-form', ['previewPage' => 2])
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
            <p class="text-[11px] text-slate-500">পৃষ্ঠা ২</p>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button type="button" wire:click="savePage2" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ ও পরবর্তী →</button>
            </div>
        </div>
    </div>
</div>
