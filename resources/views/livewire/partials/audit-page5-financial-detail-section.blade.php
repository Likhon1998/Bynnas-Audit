@php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $tableClass = $compact ? 'a4-table a4-table-compact text-[8.5px]' : 'w-full border-collapse text-[10px]';
    $cellPad = $compact ? '' : 'border border-slate-800 px-1 py-1';
    $dash = $dash ?? '………………';
@endphp

<p class="mb-[2mm] font-semibold">বিস্তারিত নিম্নে দেওয়া হল:</p>

@if ($editable)
    <x-audit-excel-paste-zone
        path="expenseDetailRows"
        :columns="['date_month', 'voucher_no', 'description', 'expense_amount', 'vat_applicable', 'vat_paid', 'vat_diff', 'tax_applicable', 'tax_paid', 'tax_diff']"
        hint="Expense/VAT/Tax detail: Excel থেকে একই কলাম ক্রমে পেস্ট করুন"
    />
@endif

<div class="{{ $editable ? 'overflow-x-auto' : '' }}">
    @php
        $hExpenseR1 = $tableHeaders['expense_r1'] ?? \App\Support\AuditTableHeaders::defaults()['expense_r1'];
        $hExpenseR2 = $tableHeaders['expense_r2'] ?? \App\Support\AuditTableHeaders::defaults()['expense_r2'];
    @endphp
    <table class="{{ $tableClass }} mb-[3mm]">
        <thead>
            <tr class="bg-slate-100">
                <x-audit-th :editable="$editable" wire="tableHeaders.expense_r1.0" class="{{ $cellPad }} font-semibold" rowspan="2">{{ $hExpenseR1[0] }}</x-audit-th>
                <x-audit-th :editable="$editable" wire="tableHeaders.expense_r1.1" class="{{ $cellPad }} font-semibold" rowspan="2">{{ $hExpenseR1[1] }}</x-audit-th>
                <x-audit-th :editable="$editable" wire="tableHeaders.expense_r1.2" class="{{ $cellPad }} font-semibold" rowspan="2">{{ $hExpenseR1[2] }}</x-audit-th>
                <x-audit-th :editable="$editable" wire="tableHeaders.expense_r1.3" class="{{ $cellPad }} font-semibold" rowspan="2">{{ $hExpenseR1[3] }}</x-audit-th>
                <x-audit-th :editable="$editable" wire="tableHeaders.expense_r1.4" class="{{ $cellPad }} font-semibold text-center" colspan="3">{{ $hExpenseR1[4] }}</x-audit-th>
                <x-audit-th :editable="$editable" wire="tableHeaders.expense_r1.5" class="{{ $cellPad }} font-semibold text-center" colspan="3">{{ $hExpenseR1[5] }}</x-audit-th>
                @if ($editable)
                    <th class="{{ $cellPad }}" rowspan="2"></th>
                @endif
            </tr>
            <tr class="bg-slate-50">
                @foreach ($hExpenseR2 as $hi => $label)
                    <x-audit-th :editable="$editable" :wire="'tableHeaders.expense_r2.'.$hi" class="{{ $cellPad }} font-semibold">{{ $label }}</x-audit-th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($expenseDetailRows as $rowIndex => $row)
                @php $isTotal = ! empty($row['is_total']); @endphp
                <tr class="{{ $isTotal ? 'bg-slate-50 font-semibold' : '' }}">
                    @foreach (['date_month', 'voucher_no', 'description', 'expense_amount', 'vat_applicable', 'vat_paid', 'vat_diff', 'tax_applicable', 'tax_paid', 'tax_diff'] as $field)
                        <td class="{{ $cellPad }} {{ in_array($field, ['expense_amount', 'vat_applicable', 'vat_paid', 'vat_diff', 'tax_applicable', 'tax_paid', 'tax_diff'], true) ? 'text-center' : '' }}">
                            @if ($editable && ! ($isTotal && $field === 'description'))
                                <input
                                    type="text"
                                    wire:model.live="expenseDetailRows.{{ $rowIndex }}.{{ $field }}"
                                    class="w-full min-w-[56px] border-0 bg-sky-50/50 px-0.5 text-[10px] {{ in_array($field, ['expense_amount', 'vat_applicable', 'vat_paid', 'vat_diff', 'tax_applicable', 'tax_paid', 'tax_diff'], true) ? 'text-center' : '' }}"
                                    @if ($isTotal && $field === 'description') readonly @endif
                                >
                            @else
                                {{ $row[$field] ?? '' }}
                            @endif
                        </td>
                    @endforeach
                    @if ($editable)
                        <td class="{{ $cellPad }} text-center">
                            @if (! $isTotal)
                                <button type="button" wire:click="removeExpenseDetailRow({{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if ($editable)
    <button type="button" wire:click="addExpenseDetailRow" class="mb-[3mm] text-[11px] font-medium text-[#2b579a]">+ খরচের সারি</button>
@endif

<div class="mb-[2mm] space-y-[2mm] text-[11px] leading-relaxed">
    <div>
        <p class="font-bold">ঝুঁকি/প্রভাব (Risk/Implication):</p>
        @if ($editable)
            <textarea wire:model.live="expense_detail_risk" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        @else
            <p class="m-0 text-justify">{{ $expense_detail_risk !== '' ? $expense_detail_risk : $dash }}</p>
        @endif
    </div>
    <div>
        <p class="font-bold">মূল কারণ (Root Cause):</p>
        @if ($editable)
            <textarea wire:model.live="expense_detail_root_cause" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        @else
            <p class="m-0 border-b border-dotted border-black text-justify">{{ $expense_detail_root_cause !== '' ? $expense_detail_root_cause : ' ' }}</p>
        @endif
    </div>
    <div>
        <p class="font-bold">সুপারিশ (Recommendation):</p>
        @if ($editable)
            <textarea wire:model.live="expense_detail_recommendation" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        @else
            <p class="m-0 border-b border-dotted border-black text-justify">{{ $expense_detail_recommendation !== '' ? $expense_detail_recommendation : ' ' }}</p>
        @endif
    </div>
</div>

<table class="{{ $tableClass }} mb-[5mm]">
    <tbody>
        <tr>
            <td class="{{ $cellPad }} w-[38%] font-semibold align-top">শাখা ব্যবস্থাপকের জবাব</td>
            <td class="{{ $cellPad }}">
                @if ($editable)
                    <textarea wire:model.live="expense_detail_bm_reply" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                @else
                    {{ $expense_detail_bm_reply }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="{{ $cellPad }} font-semibold align-top">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
            <td class="{{ $cellPad }}">
                @if ($editable)
                    <textarea wire:model.live="expense_detail_responsible" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                @else
                    {{ $expense_detail_responsible }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="{{ $cellPad }} font-semibold align-top">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল <span class="underline decoration-yellow-400 decoration-2">(তারিখ)</span></td>
            <td class="{{ $cellPad }}">
                @if ($editable)
                    <x-audit-date-field wire:model.live="expense_detail_resolution_date" format="dmy" class="w-full border-0 bg-sky-50/40 px-1 text-[11px]" />
                @else
                    {{ $expense_detail_resolution_date }}
                @endif
            </td>
        </tr>
    </tbody>
</table>

{{-- Finding ১.৩ --}}
@php $anchor13 = \App\Livewire\MakeAuditReport::findingAnchorId($finding13_serial ?? '১.৩'); @endphp
@if ($anchor13 !== '')
    <a id="{{ $anchor13 }}" name="{{ $anchor13 }}"></a>
@endif

<table class="{{ $tableClass }} mb-[2mm]">
    <tbody>
        <tr>
            <td class="{{ $cellPad }} w-[9%] text-center font-bold align-top finding-serial-cell">
                @include('livewire.partials.audit-finding-serial-cell', [
                    'editable' => $editable,
                    'wireModel' => $editable ? 'finding13_serial' : null,
                    'value' => $finding13_serial ?? '',
                ])
            </td>
            <td class="{{ $cellPad }} w-[11%] text-center font-bold align-top">
                @if ($editable)
                    <input type="text" wire:model.live="finding13_title" class="w-full border-0 bg-sky-50/40 text-center font-bold">
                @else
                    {{ $finding13_title }}
                @endif
            </td>
            <td class="{{ $cellPad }} align-top">
                @if ($editable)
                    @include('livewire.partials.audit-indicator-combobox', [
                        'index' => 0,
                        'value' => $finding13_body ?? '',
                        'indicators' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
                        'collection' => 'finding13',
                        'wireKey' => 'p5-ind-13-'.md5((string) ($finding13_body ?? '')),
                    ])
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px]">
                        <span class="font-semibold">টাকার পরিমাণ:</span>
                        <input type="text" wire:model.live="finding13_amount" class="inline-input min-w-[100px]">
                    </div>
                @else
                    <p class="m-0 whitespace-pre-wrap text-justify leading-[1.45]">{{ $finding13_body }}</p>
                    @if (($finding13_amount ?? '') !== '')
                        <p class="mt-[1mm] m-0"><span class="font-semibold">টাকার পরিমাণ:</span> {{ $finding13_amount }}</p>
                    @endif
                @endif
            </td>
            <td class="{{ $cellPad }} w-[17%] p-0 align-top">
                @include('livewire.partials.audit-rating-box', [
                    'rating' => $finding13_rating ?? '',
                    'editable' => $editable,
                    'wireModel' => $editable ? 'finding13_rating' : null,
                    'findingRatings' => $findingRatings ?? [],
                ])
            </td>
        </tr>
    </tbody>
</table>

<div class="mb-[2mm]">
    <p class="mb-[1mm] font-bold">প্রচলিত নিয়ম (Criteria):</p>
    @if ($editable)
        <textarea wire:model.live="finding13_criteria" rows="2" class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
    @else
        <p class="m-0 text-justify">{{ $finding13_criteria }}</p>
    @endif
</div>

<div class="mb-[2mm]">
    <p class="mb-[1mm] font-bold">পর্যবেক্ষণ (Observation) :</p>
    @if ($editable)
        <textarea wire:model.live="finding13_observation" rows="2" class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
    @else
        <p class="m-0 border-b border-dotted border-black">{{ $finding13_observation !== '' ? $finding13_observation : ' ' }}</p>
    @endif
</div>

@if ($editable)
    <x-audit-excel-paste-zone
        path="finding13_statsRows"
        :columns="['total_population', 'sample_size', 'instances_found', 'percentage']"
        hint="Stats: Excel থেকে ৪ কলাম কপি করে পেস্ট করুন"
    />
@endif
<table class="{{ $tableClass }} mb-[3mm]">
    @include('livewire.partials.audit-stats-thead', [
            'editable' => $editable,
            'cellPad' => $cellPad,
            'variant' => 'stats',
        ])
    <tbody>
        @foreach ($finding13_statsRows as $rowIndex => $row)
            <tr>
                @foreach (['total_population', 'sample_size', 'instances_found', 'percentage'] as $field)
                    <td class="{{ $cellPad }} text-center">
                        @if ($editable)
                            @if (((string) $field === 'date' || str_ends_with((string) $field, '_date') || preg_match('/^date[_\d]/', (string) $field)))
                                        <x-audit-date-field wire:model.live="finding13_statsRows.{{ $rowIndex }}.{{ $field }}" format="dmy" class="w-full border-0 bg-transparent text-center text-[11px]" />
                                    @else
                                        <input type="text" wire:model.live="finding13_statsRows.{{ $rowIndex }}.{{ $field }}" class="w-full border-0 bg-transparent text-center text-[11px]">
                                    @endif
                        @else
                            {{ $row[$field] ?? '' }}
                        @endif
                    </td>
                @endforeach
                @if ($editable)
                    <td class="{{ $cellPad }} text-center">
                        @if (count($finding13_statsRows) > 1)
                            <button type="button" wire:click="removeFinding13StatsRow({{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
@if ($editable)
    <button type="button" wire:click="addFinding13StatsRow" class="mb-[3mm] text-[11px] font-medium text-[#2b579a]">+ Stats row</button>
@endif

@if ($editable)
    <x-audit-excel-paste-zone
        path="finding13_depositRows"
        :columns="['description', 'month_name', 'withdrawal_date', 'deposit_date', 'amount', 'holding_period']"
        hint="Deposit detail: Excel থেকে ৬ কলাম একই ক্রমে পেস্ট করুন"
    />
@endif
@php $hDeposit = $tableHeaders['deposit'] ?? \App\Support\AuditTableHeaders::defaults()['deposit']; @endphp
<table class="{{ $tableClass }} mb-[3mm]">
    <thead>
        <tr class="bg-slate-100">
            @foreach ($hDeposit as $hi => $label)
                <x-audit-th :editable="$editable" :wire="'tableHeaders.deposit.'.$hi" class="{{ $cellPad }} font-semibold">{{ $label }}</x-audit-th>
            @endforeach
            @if ($editable)
                <th class="{{ $cellPad }}"></th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($finding13_depositRows as $rowIndex => $row)
            <tr>
                @foreach (['description', 'month_name', 'withdrawal_date', 'deposit_date', 'amount', 'holding_period'] as $field)
                    <td class="{{ $cellPad }} {{ in_array($field, ['amount', 'holding_period'], true) ? 'text-center' : '' }}">
                        @if ($editable)
                            @if (((string) $field === 'date' || str_ends_with((string) $field, '_date') || preg_match('/^date[_\d]/', (string) $field)))
                                        <x-audit-date-field wire:model.live="finding13_depositRows.{{ $rowIndex }}.{{ $field }}" format="dmy" class="w-full border-0 bg-sky-50/50 px-0.5 text-[10px]" />
                                    @else
                                        <input type="text" wire:model.live="finding13_depositRows.{{ $rowIndex }}.{{ $field }}" class="w-full border-0 bg-sky-50/50 px-0.5 text-[10px]">
                                    @endif
                        @else
                            {{ $row[$field] ?? '' }}
                        @endif
                    </td>
                @endforeach
                @if ($editable)
                    <td class="{{ $cellPad }} text-center">
                        @if (count($finding13_depositRows) > 1)
                            <button type="button" wire:click="removeFinding13DepositRow({{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
@if ($editable)
    <button type="button" wire:click="addFinding13DepositRow" class="mb-[3mm] text-[11px] font-medium text-[#2b579a]">+ Deposit row</button>
@endif

<div class="mb-[2mm] space-y-[2mm] text-[11px] leading-relaxed">
    <div>
        <p class="font-bold">ঝুঁকি/প্রভাব (Risk/Implication):</p>
        @if ($editable)
            <textarea wire:model.live="finding13_risk" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        @else
            <p class="m-0 text-justify">{{ $finding13_risk !== '' ? $finding13_risk : $dash }}</p>
        @endif
    </div>
    <div>
        <p class="font-bold">মূল কারণ (Root Cause):</p>
        @if ($editable)
            <textarea wire:model.live="finding13_root_cause" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        @else
            <p class="m-0 border-b border-dotted border-black">{{ $finding13_root_cause !== '' ? $finding13_root_cause : ' ' }}</p>
        @endif
    </div>
    <div>
        <p class="font-bold">সুপারিশ (Recommendation):</p>
        @if ($editable)
            <textarea wire:model.live="finding13_recommendation" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        @else
            <p class="m-0 border-b border-dotted border-black">{{ $finding13_recommendation !== '' ? $finding13_recommendation : ' ' }}</p>
        @endif
    </div>
</div>

<table class="{{ $tableClass }}">
    <tbody>
        <tr>
            <td class="{{ $cellPad }} w-[38%] font-semibold align-top">শাখা ব্যবস্থাপকের জবাব</td>
            <td class="{{ $cellPad }}">
                @if ($editable)
                    <textarea wire:model.live="finding13_bm_reply" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                @else
                    {{ $finding13_bm_reply }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="{{ $cellPad }} font-semibold align-top">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
            <td class="{{ $cellPad }}">
                @if ($editable)
                    <textarea wire:model.live="finding13_responsible" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                @else
                    {{ $finding13_responsible }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="{{ $cellPad }} font-semibold align-top">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল <span class="underline decoration-yellow-400 decoration-2">(তারিখ)</span></td>
            <td class="{{ $cellPad }}">
                @if ($editable)
                    <x-audit-date-field wire:model.live="finding13_resolution_date" format="dmy" class="w-full border-0 bg-sky-50/40 px-1 text-[11px]" />
                @else
                    {{ $finding13_resolution_date }}
                @endif
            </td>
        </tr>
    </tbody>
</table>
