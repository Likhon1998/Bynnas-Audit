@php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $cellPad = $compact ? '' : 'border border-slate-800 px-1.5 py-1';
    $coreFields = ['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no'];
    $hCompliance = array_values($tableHeaders['compliance'] ?? \App\Support\AuditTableHeaders::defaults()['compliance']);
    $extraCount = max(0, count($hCompliance) - count($coreFields));
@endphp

<div class="mb-4">
    @if ($editable)
        <input type="text" wire:model.live="page19_compliance_title" class="finding-serial-input mb-3 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1.5 text-[13px] font-bold">
    @else
        <p class="mb-3 text-[12px] font-bold finding-heading">{!! \App\Support\BanglaNumerals::highlight($page19_compliance_title ?? '', 'serial') !!}</p>
    @endif

    <div class="mb-3 flex flex-wrap gap-4 text-[11px]">
        <div class="flex items-center gap-2">
            <span class="font-semibold">নিরীক্ষাকাল:</span>
            @if ($editable)
                <input type="text" wire:model.live="page19_compliance_period" class="min-w-[180px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1" placeholder="যেমন: December '22 to March '23">
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

    @if ($editable)
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <x-audit-excel-paste-zone
                path="page19ComplianceRows"
                :columns="array_merge($coreFields, collect(range(0, max(0, $extraCount - 1)))->map(fn ($i) => 'extra.'.$i)->all())"
                hint="Compliance: Excel থেকে কলামগুলো একই ক্রমে পেস্ট করুন"
            />
            <button type="button" wire:click="fillComplianceNumbersFromReport" class="rounded border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                রিপোর্টের নম্বর বসান
            </button>
            <button type="button" wire:click="addPage19ComplianceColumn" class="rounded border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-[#2b579a] hover:bg-sky-50">
                + কলাম
            </button>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="{{ $compact ? 'a4-table a4-table-compact text-[7.5px]' : 'w-full border-collapse text-[10px]' }} min-w-full">
            <thead>
                <tr class="bg-slate-100">
                    @foreach ($hCompliance as $hi => $label)
                        <th class="{{ $cellPad }} font-semibold text-center align-middle">
                            @if ($editable)
                                <div class="flex items-start gap-1">
                                    <input type="text" wire:model.live="tableHeaders.compliance.{{ $hi }}" class="w-full border-0 bg-transparent text-center text-[10px] font-semibold">
                                    @if ($hi >= 6)
                                        <button type="button" wire:click="removePage19ComplianceColumn({{ $hi }})" class="shrink-0 text-[10px] text-rose-600" title="কলাম মুছুন">×</button>
                                    @endif
                                </div>
                            @else
                                {{ $label }}
                            @endif
                        </th>
                    @endforeach
                    @if ($editable)
                        <th class="{{ $cellPad }} w-8"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach (($page19ComplianceRows ?? []) as $rowIndex => $row)
                    <tr>
                        @foreach ($coreFields as $field)
                            <td class="{{ $cellPad }} align-top">
                                @if ($editable)
                                    <textarea wire:model.live="page19ComplianceRows.{{ $rowIndex }}.{{ $field }}" rows="3" class="w-full border-0 bg-sky-50/50 p-1 text-[10px] leading-snug"></textarea>
                                @else
                                    <span class="whitespace-pre-wrap">{{ $row[$field] ?? '' }}</span>
                                @endif
                            </td>
                        @endforeach
                        @for ($ei = 0; $ei < $extraCount; $ei++)
                            <td class="{{ $cellPad }} align-top">
                                @if ($editable)
                                    <textarea wire:model.live="page19ComplianceRows.{{ $rowIndex }}.extra.{{ $ei }}" rows="3" class="w-full border-0 bg-sky-50/50 p-1 text-[10px] leading-snug"></textarea>
                                @else
                                    <span class="whitespace-pre-wrap">{{ $row['extra'][$ei] ?? '' }}</span>
                                @endif
                            </td>
                        @endfor
                        @if ($editable)
                            <td class="{{ $cellPad }} text-center align-top">
                                @if (count($page19ComplianceRows ?? []) > 1)
                                    <button type="button" wire:click="removePage19ComplianceRow({{ $rowIndex }})" class="text-[11px] font-semibold text-rose-600">×</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($editable)
        <button type="button" wire:click="addPage19ComplianceRow" class="mt-2 text-[11px] font-semibold text-[#2b579a] hover:underline">+ সারি যোগ করুন</button>
    @endif
</div>
