@php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $cellPad = $compact ? '' : 'border border-slate-800 px-1 py-0.5';
@endphp

<div class="mb-8">
    @if ($editable)
        <input type="text" wire:model.live="page19_compliance_title" class="finding-serial-input mb-3 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold">
    @else
        <p class="mb-3 text-[12px] font-bold finding-heading">{!! \App\Support\BanglaNumerals::highlight($page19_compliance_title ?? '', 'serial') !!}</p>
    @endif

    <div class="mb-3 flex flex-wrap gap-4 text-[11px]">
        <div class="flex items-center gap-2">
            <span class="font-semibold">নিরীক্ষাকাল:</span>
            @if ($editable)
                <input type="text" wire:model.live="page19_compliance_period" class="min-w-[160px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1">
            @else
                <span>{{ $page19_compliance_period ?? '' }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <span class="font-semibold">ফলোআপের তারিখ:</span>
            @if ($editable)
                <x-audit-date-field wire:model.live="page19_compliance_followup_date" format="dmy" class="min-w-[120px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1" />
            @else
                <span>{{ $page19_compliance_followup_date ?? '' }}</span>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        @if ($editable)
            <x-audit-excel-paste-zone
                path="page19ComplianceRows"
                :columns="['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no']"
                hint="Compliance: Excel থেকে ৬ কলাম একই ক্রমে পেস্ট করুন"
            />
        @endif
        <table class="{{ $compact ? 'a4-table a4-table-compact text-[7.5px]' : 'w-full border-collapse text-[9px]' }} min-w-full">
            @php $hCompliance = $tableHeaders['compliance'] ?? \App\Support\AuditTableHeaders::defaults()['compliance']; @endphp
            <thead>
                <tr class="bg-slate-100">
                    @foreach ($hCompliance as $hi => $label)
                        <x-audit-th :editable="$editable" :wire="'tableHeaders.compliance.'.$hi" class="{{ $cellPad }} font-semibold text-center">{{ $label }}</x-audit-th>
                    @endforeach
                    @if ($editable)
                        <th class="{{ $cellPad }}"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach (($page19ComplianceRows ?? []) as $rowIndex => $row)
                    <tr>
                        @foreach (['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no'] as $field)
                            <td class="{{ $cellPad }} align-top">
                                @if ($editable)
                                    <textarea wire:model.live="page19ComplianceRows.{{ $rowIndex }}.{{ $field }}" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                                @else
                                    <span class="whitespace-pre-wrap">{{ $row[$field] ?? '' }}</span>
                                @endif
                            </td>
                        @endforeach
                        @if ($editable)
                            <td class="{{ $cellPad }} text-center align-top">
                                @if (count($page19ComplianceRows ?? []) > 1)
                                    <button type="button" wire:click="removePage19ComplianceRow({{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($editable)
        <button type="button" wire:click="addPage19ComplianceRow" class="mt-2 text-[11px] font-medium text-[#2b579a]">+ Compliance row</button>
    @endif
</div>
