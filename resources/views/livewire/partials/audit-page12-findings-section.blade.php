@php
    use App\Livewire\MakeAuditReport;
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $tableClass = $compact ? 'a4-table a4-table-compact text-[8.5px]' : 'w-full border-collapse text-[10px]';
    $cellPad = $compact ? '' : 'border border-slate-800 px-1 py-0.5';
    $dash = $dash ?? '………………';
    $findings = $page12Findings ?? [];
@endphp

@foreach ($findings as $fIndex => $finding)
    @php
        $anchor = MakeAuditReport::findingAnchorId($finding['serial'] ?? '');
        $detailType = (string) ($finding['detail_type'] ?? 'stock');
    @endphp
    @if ($anchor !== '')
        <a id="{{ $anchor }}" name="{{ $anchor }}"></a>
    @endif

    <table class="{{ $compact ? 'a4-table a4-table-compact text-[9px]' : 'w-full border-collapse text-[10.5px]' }} mb-[2mm]">
        <tbody>
            <tr>
                <td class="{{ $cellPad }} w-[9%] text-center font-bold align-top finding-serial-cell">
                    @include('livewire.partials.audit-finding-serial-cell', [
                        'editable' => $editable,
                        'wireModel' => $editable ? 'page12Findings.'.$fIndex.'.serial' : null,
                        'value' => $finding['serial'] ?? '',
                    ])
                </td>
                <td class="{{ $cellPad }} w-[11%] text-center font-bold align-top">
                    @if ($editable)
                        <input type="text" wire:model.live="page12Findings.{{ $fIndex }}.title" class="w-full border-0 bg-sky-50/40 text-center font-bold">
                    @else
                        {{ $finding['title'] ?? 'শিরোনাম' }}
                    @endif
                </td>
                <td class="{{ $cellPad }} align-top">
                    @if ($editable)
                        @include('livewire.partials.audit-indicator-combobox', [
                            'index' => $fIndex,
                            'value' => $finding['body'] ?? '',
                            'indicators' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
                            'collection' => 'page12Findings',
                            'wireKey' => 'p12-ind-'.$fIndex.'-'.md5((string) ($finding['body'] ?? '')),
                        ])
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px]">
                            <span class="font-semibold">টাকার পরিমাণ:</span>
                            <input type="text" wire:model.live="page12Findings.{{ $fIndex }}.amount" class="inline-input min-w-[100px]">
                        </div>
                    @else
                        <p class="m-0 whitespace-pre-wrap text-justify leading-[1.45]">{{ $finding['body'] ?? '' }}</p>
                        @if (($finding['amount'] ?? '') !== '')
                            <p class="mt-[1mm] m-0"><span class="font-semibold">টাকার পরিমাণ:</span> {{ $finding['amount'] }}</p>
                        @endif
                    @endif
                </td>
                <td class="{{ $cellPad }} w-[17%] p-0 align-top">
                    @include('livewire.partials.audit-rating-box', [
                        'rating' => $finding['rating'] ?? '',
                        'editable' => $editable,
                        'wireModel' => $editable ? 'page12Findings.'.$fIndex.'.rating' : null,
                        'findingRatings' => $findingRatings ?? [],
                    ])
                </td>
            </tr>
        </tbody>
    </table>

    <div class="mb-[2mm]">
        <p class="mb-[1mm] font-bold">প্রচলিত নিয়ম (Criteria):</p>
        @if ($editable)
            <textarea wire:model.live="page12Findings.{{ $fIndex }}.criteria" rows="4" class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        @else
            <p class="m-0 text-justify">{{ $finding['criteria'] ?? '' }}</p>
        @endif
    </div>

    <div class="mb-[2mm]">
        <p class="mb-[1mm] font-bold">পর্যবেক্ষণ (Observation) :</p>
        @if ($editable)
            <textarea wire:model.live="page12Findings.{{ $fIndex }}.observation" rows="3" class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        @elseif (($finding['observation'] ?? '') !== '')
            <p class="m-0 text-justify">{{ $finding['observation'] }}</p>
        @else
            <p class="m-0 border-b border-dotted border-black">&nbsp;</p>
        @endif
    </div>

        @if ($editable)
        <x-audit-excel-paste-zone
            :path="'page12Findings.' . $fIndex . '.statsRows'"
            :columns="['total_population', 'sample_size', 'instances_found', 'percentage']"
            hint="Stats: Excel থেকে ৪ কলাম কপি করে পেস্ট করুন"
        />
    @endif

<table class="{{ $compact ? 'a4-table a4-table-compact text-[9px]' : 'w-full border-collapse text-[10.5px]' }} mb-[2mm]">
        @include('livewire.partials.audit-stats-thead', [
            'editable' => $editable,
            'cellPad' => $cellPad,
            'variant' => 'stats',
        ])
        <tbody>
            @foreach (($finding['statsRows'] ?? []) as $rowIndex => $row)
                <tr>
                    @foreach (['total_population', 'sample_size', 'instances_found', 'percentage'] as $field)
                        <td class="{{ $cellPad }} text-center">
                            @if ($editable)
                                @if (((string) $field === 'date' || str_ends_with((string) $field, '_date') || preg_match('/^date[_\d]/', (string) $field)))
                                        <x-audit-date-field wire:model.live="page12Findings.{{ $fIndex }}.statsRows.{{ $rowIndex }}.{{ $field }}" format="dmy" class="w-full border-0 bg-transparent text-center text-[11px]" />
                                    @else
                                        <input type="text" wire:model.live="page12Findings.{{ $fIndex }}.statsRows.{{ $rowIndex }}.{{ $field }}" class="w-full border-0 bg-transparent text-center text-[11px]">
                                    @endif
                            @else
                                {{ $row[$field] ?? '' }}
                            @endif
                        </td>
                    @endforeach
                    @if ($editable)
                        <td class="{{ $cellPad }} text-center">
                            @if (count($finding['statsRows'] ?? []) > 1)
                                <button type="button" wire:click="removePage12StatsRow({{ $fIndex }}, {{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($editable)
        <button type="button" wire:click="addPage12StatsRow({{ $fIndex }})" class="mb-[2mm] text-[11px] font-medium text-[#2b579a]">+ Stats row</button>
    @endif

    @if ($editable)
        <div class="mb-[2mm] flex flex-wrap items-center gap-3">
            <label class="text-[11px] font-semibold text-slate-600">বিস্তারিত ধরন:</label>
            <select wire:model.live="page12Findings.{{ $fIndex }}.detail_type" class="rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[11px]">
                <option value="stock">স্টক তালিকা</option>
                <option value="none">নেই</option>
            </select>
        </div>
    @endif

    @if ($detailType === 'stock')
        <p class="mb-[1mm] font-semibold">{{ $finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:' }}</p>
        @if ($editable)
            <input type="text" wire:model.live="page12Findings.{{ $fIndex }}.detail_intro" class="mb-[2mm] w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[11px]">
        @endif

        <div class="mb-[2mm] overflow-x-auto">
                    @if ($editable)
            <x-audit-excel-paste-zone
                :path="'page12Findings.' . $fIndex . '.stockRows'"
                :columns="['product_name', 'purchase_date_voucher', 'purchase_price', 'register_status']"
                hint="Stock paste"
            />
        @endif

@php $hStock = $tableHeaders['stock'] ?? \App\Support\AuditTableHeaders::defaults()['stock']; @endphp
<table class="{{ $tableClass }} min-w-full">
                <thead>
                    <tr class="bg-slate-100">
                        @foreach ($hStock as $hi => $label)
                            <x-audit-th :editable="$editable" :wire="'tableHeaders.stock.'.$hi" class="{{ $cellPad }} font-semibold">{{ $label }}</x-audit-th>
                        @endforeach
                        @if ($editable)
                            <th class="{{ $cellPad }}"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach (($finding['stockRows'] ?? []) as $rowIndex => $row)
                        <tr>
                            @foreach (['product_name', 'purchase_date_voucher', 'purchase_price', 'register_status'] as $field)
                                <td class="{{ $cellPad }} {{ in_array($field, ['purchase_date_voucher', 'purchase_price'], true) ? 'text-center' : '' }}">
                                    @if ($editable)
                                        @if (((string) $field === 'date' || str_ends_with((string) $field, '_date') || preg_match('/^date[_\d]/', (string) $field)))
                                        <x-audit-date-field wire:model.live="page12Findings.{{ $fIndex }}.stockRows.{{ $rowIndex }}.{{ $field }}" format="dmy" class="w-full border-0 bg-sky-50/50 px-0.5 text-[10px] {{ in_array($field, ['purchase_date_voucher', 'purchase_price'], true) ? 'text-center' : '' }}" />
                                    @else
                                        <input type="text" wire:model.live="page12Findings.{{ $fIndex }}.stockRows.{{ $rowIndex }}.{{ $field }}" class="w-full border-0 bg-sky-50/50 px-0.5 text-[10px] {{ in_array($field, ['purchase_date_voucher', 'purchase_price'], true) ? 'text-center' : '' }}">
                                    @endif
                                    @else
                                        {{ $row[$field] ?? '' }}
                                    @endif
                                </td>
                            @endforeach
                            @if ($editable)
                                <td class="{{ $cellPad }} text-center">
                                    @if (count($finding['stockRows'] ?? []) > 1)
                                        <button type="button" wire:click="removePage12StockRow({{ $fIndex }}, {{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($editable)
            <button type="button" wire:click="addPage12StockRow({{ $fIndex }})" class="mb-[3mm] text-[11px] font-medium text-[#2b579a]">+ Stock row</button>
        @endif
    @elseif (($finding['detail_intro'] ?? '') !== '' && ! $editable)
        <p class="mb-[2mm] font-semibold">{{ $finding['detail_intro'] }}</p>
    @elseif ($editable && $detailType === 'none')
        <input type="text" wire:model.live="page12Findings.{{ $fIndex }}.detail_intro" class="mb-[2mm] w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[11px]" placeholder="নিম্নে বিস্তারিত দেওয়া হলো:">
    @endif

    <div class="mb-[2mm] space-y-[2mm] text-[11px] leading-relaxed">
        <div>
            <p class="font-bold">ঝুঁকি (Risk) :</p>
            @if ($editable)
                <textarea wire:model.live="page12Findings.{{ $fIndex }}.risk" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
            @else
                <p class="m-0 whitespace-pre-wrap text-justify">{{ ($finding['risk'] ?? '') !== '' ? $finding['risk'] : $dash }}</p>
            @endif
        </div>
        <div>
            <p class="font-bold">মূল কারণ (Root Cause):</p>
            @if ($editable)
                <textarea wire:model.live="page12Findings.{{ $fIndex }}.root_cause" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
            @elseif (($finding['root_cause'] ?? '') !== '')
                <p class="m-0 text-justify">{{ $finding['root_cause'] }}</p>
            @else
                <p class="m-0 border-b border-dotted border-black">&nbsp;</p>
            @endif
        </div>
        <div>
            <p class="font-bold">সুপারিশ (Recommendation):</p>
            @if ($editable)
                <textarea wire:model.live="page12Findings.{{ $fIndex }}.recommendation" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
            @elseif (($finding['recommendation'] ?? '') !== '')
                <p class="m-0 text-justify">{{ $finding['recommendation'] }}</p>
            @else
                <p class="m-0 border-b border-dotted border-black">&nbsp;</p>
            @endif
        </div>
    </div>

    <table class="{{ $compact ? 'a4-table a4-table-compact text-[9px]' : 'w-full border-collapse text-[10.5px]' }} {{ $loop->last ? '' : 'mb-[6mm]' }}">
        <tbody>
            <tr>
                <td class="{{ $cellPad }} w-[38%] font-semibold align-top">শাখা ব্যবস্থাপকের জবাব</td>
                <td class="{{ $cellPad }}">
                    @if ($editable)
                        <textarea wire:model.live="page12Findings.{{ $fIndex }}.bm_reply" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                    @else
                        {{ $finding['bm_reply'] ?? '' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="{{ $cellPad }} font-semibold align-top">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
                <td class="{{ $cellPad }}">
                    @if ($editable)
                        <textarea wire:model.live="page12Findings.{{ $fIndex }}.responsible" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                    @else
                        {{ $finding['responsible'] ?? '' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="{{ $cellPad }} font-semibold align-top">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল <span class="underline decoration-yellow-400 decoration-2">(তারিখ)</span></td>
                <td class="{{ $cellPad }}">
                    @if ($editable)
                        <x-audit-date-field wire:model.live="page12Findings.{{ $fIndex }}.resolution_date" format="dmy" class="w-full border-0 bg-sky-50/40 px-1 text-[11px]" />
                    @else
                        {{ $finding['resolution_date'] ?? '' }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
@endforeach
