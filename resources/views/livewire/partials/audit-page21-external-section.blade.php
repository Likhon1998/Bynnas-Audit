@php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $cellPad = $compact ? '' : 'border border-slate-800 px-1 py-0.5';
    $headerBg = 'background-color:#fce5cd;';
    $headerBgAlt = 'background-color:#f5d5b8;';
    $rowFields = ['area_of_observation', 'compliance_area', 'year_of_reporting', 'external_observation', 'compliance', 'internal_index_no'];
    $headers = $tableHeaders['external'] ?? \App\Support\AuditTableHeaders::defaults()['external'];
@endphp

<div class="mb-8">
    @if ($editable)
        <input type="text" wire:model.live="page21_section_title" class="finding-serial-input mb-3 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold">
    @else
        <p class="mb-3 text-[12px] font-bold finding-heading">{!! \App\Support\BanglaNumerals::highlight($page21_section_title ?? '', 'serial') !!}</p>
    @endif

    <div class="mb-3 flex flex-wrap gap-4 text-[11px]">
        <div class="flex items-center gap-2">
            <span class="font-semibold">Year of reporting</span>
            @if ($editable)
                <input type="text" wire:model.live="page21_year_of_reporting" class="min-w-[120px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1">
            @else
                <span>{{ $page21_year_of_reporting ?? '' }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <span class="font-semibold">Name of Branch</span>
            @if ($editable)
                <input type="text" wire:model.live="page21_branch_name" class="min-w-[160px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1">
            @else
                <span>{{ $page21_branch_name ?? '' }}</span>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        @if ($editable)
            <x-audit-excel-paste-zone
                path="page21ExternalAuditRows"
                :columns="['area_of_observation', 'compliance_area', 'year_of_reporting', 'external_observation', 'compliance', 'internal_index_no']"
                hint="External audit: Excel থেকে ৬ কলাম একই ক্রমে পেস্ট করুন"
            />
        @endif
        <table class="{{ $compact ? 'a4-table a4-table-compact text-[7.5px]' : 'w-full border-collapse text-[9px]' }} min-w-full">
            <thead>
                <tr>
                    @foreach ($headers as $index => $header)
                        <x-audit-th
                            :editable="$editable"
                            :wire="'tableHeaders.external.'.$index"
                            class="{{ $cellPad }} font-semibold text-center"
                            style="{{ $index % 2 === 0 ? $headerBg : $headerBgAlt }}"
                        >{{ $header }}</x-audit-th>
                    @endforeach
                    @if ($editable)
                        <th class="{{ $cellPad }}" style="{{ $headerBg }}"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach (($page21ExternalAuditRows ?? []) as $rowIndex => $row)
                    <tr>
                        @foreach ($rowFields as $field)
                            <td class="{{ $cellPad }} align-top">
                                @if ($editable)
                                    <textarea wire:model.live="page21ExternalAuditRows.{{ $rowIndex }}.{{ $field }}" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                                @else
                                    <span class="whitespace-pre-wrap">{{ $row[$field] ?? '' }}</span>
                                @endif
                            </td>
                        @endforeach
                        @if ($editable)
                            <td class="{{ $cellPad }} text-center align-top">
                                @if (count($page21ExternalAuditRows ?? []) > 1)
                                    <button type="button" wire:click="removePage21ExternalAuditRow({{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($editable)
        <button type="button" wire:click="addPage21ExternalAuditRow" class="mt-2 text-[11px] font-medium text-[#2b579a]">+ External audit row</button>
    @endif

    <div class="mt-8 text-[11px]">
        @if ($editable)
            <input type="text" wire:model.live="page21_sign_label" class="mb-2 w-full max-w-md rounded border border-slate-200 bg-sky-50/40 px-2 py-1 font-semibold">
            <div class="mt-6 space-y-2 max-w-md">
                <div class="flex items-center gap-2">
                    <span class="font-semibold shrink-0">নাম :</span>
                    <input type="text" wire:model.live="page21_sign_name" class="w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1">
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-semibold shrink-0">পদবী :</span>
                    <input type="text" wire:model.live="page21_sign_designation" class="w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1">
                </div>
            </div>
        @else
            <p class="mb-2 font-semibold">{{ $page21_sign_label ?? 'নিরীক্ষা কর্মকর্তার স্বাক্ষরঃ' }}</p>
            <div class="mt-6 space-y-1">
                <p class="mb-0"><span class="font-semibold">নাম :</span> {{ $page21_sign_name ?? '' }}</p>
                <p class="mb-0"><span class="font-semibold">পদবী :</span> {{ $page21_sign_designation ?? '' }}</p>
            </div>
        @endif
    </div>
</div>
