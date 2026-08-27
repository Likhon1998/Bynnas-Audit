@php
    use App\Livewire\MakeAuditReport;
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $tableClass = $compact ? 'a4-table a4-table-compact text-[9.5px]' : 'a4-table text-[10.5px]';
    $obsTableClass = $compact ? 'a4-table a4-table-compact text-[9px]' : 'a4-table text-[10px]';
@endphp

<p class="mb-[2mm] font-bold">{{ $financial_section_title }}</p>

@foreach ($financialFindings as $index => $finding)
    @php $anchor = MakeAuditReport::findingAnchorId($finding['serial'] ?? ''); @endphp
    @if ($anchor !== '')
        <a id="{{ $anchor }}" name="{{ $anchor }}"></a>
    @endif
    <table class="{{ $tableClass }} mb-[2mm]">
        <tbody>
            <tr>
                <td style="width:9%;" class="align-top text-center font-bold">{{ $finding['serial'] ?? '' }}</td>
                <td style="width:11%;" class="align-top text-center font-bold">{{ $finding['title'] ?? 'শিরোনাম' }}</td>
                <td class="align-top">
                    @if ($editable)
                        @include('livewire.partials.audit-indicator-combobox', [
                            'index' => $index,
                            'value' => $finding['body'] ?? '',
                            'indicators' => $financialIndicatorOptions ?? [],
                            'wireKey' => 'fin-ind-'.$index.'-'.md5((string) ($finding['body'] ?? '')),
                        ])
                    @else
                        <p class="m-0 whitespace-pre-wrap text-justify leading-[1.45]">{{ $finding['body'] ?? '' }}</p>
                    @endif
                </td>
                <td style="width:17%;" class="align-top p-0">
                    @include('livewire.partials.audit-rating-box', [
                        'rating' => $finding['rating'] ?? '',
                        'editable' => $editable,
                        'wireModel' => $editable ? 'financialFindings.'.$index.'.rating' : null,
                        'findingRatings' => $findingRatings ?? [],
                    ])
                </td>
            </tr>
        </tbody>
    </table>
@endforeach

<div class="mt-[3mm]">
    <p class="mb-[1mm] font-bold">প্রচলিত নিয়ম (Criteria):</p>
    @if ($editable)
        <textarea
            wire:model.live="financial_criteria"
            rows="3"
            class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px] leading-relaxed"
        ></textarea>
    @else
        <p class="m-0 text-justify leading-[1.45]">{{ $financial_criteria }}</p>
    @endif
</div>

<p class="mb-[1mm] mt-[3mm] font-bold">পর্যবেক্ষণ (Observation) :</p>
<p class="mb-[2mm] border-b border-dotted border-black">&nbsp;</p>

<p class="mb-[1mm] font-bold">ভ্যাট সংক্রান্ত:</p>
<table class="{{ $obsTableClass }} mb-[3mm]">
    <thead>
        <tr>
            <th class="bg-[#5b2a86] text-white">Total Population</th>
            <th class="bg-[#5b2a86] text-white">Sample Size(Checked)</th>
            <th class="bg-[#5b2a86] text-white">Instantans Found</th>
            <th class="bg-[#5b2a86] text-white">Persentange(%)</th>
            @if ($editable)
                <th class="w-[8%] bg-[#5b2a86] text-white"></th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($vatObservationRows as $rowIndex => $row)
            <tr>
                @foreach (['total_population', 'sample_size', 'instances_found', 'percentage'] as $field)
                    <td class="text-center">
                        @if ($editable)
                            <input type="text" wire:model.live="vatObservationRows.{{ $rowIndex }}.{{ $field }}" class="w-full border-0 bg-transparent text-center text-[11px]">
                        @else
                            {{ $row[$field] ?? '' }}
                        @endif
                    </td>
                @endforeach
                @if ($editable)
                    <td class="text-center">
                        @if (count($vatObservationRows) > 1)
                            <button type="button" wire:click="removeVatObservationRow({{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
@if ($editable)
    <button type="button" wire:click="addVatObservationRow" class="mb-[3mm] text-[11px] font-medium text-[#2b579a]">+ VAT row</button>
@endif

<p class="mb-[1mm] font-bold">ট্যাক্স সংক্রান্ত:</p>
<table class="{{ $obsTableClass }}">
    <thead>
        <tr>
            <th class="bg-[#5b2a86] text-white">Total Population</th>
            <th class="bg-[#5b2a86] text-white">Sample Size(Checked)</th>
            <th class="bg-[#5b2a86] text-white">Instantans Found</th>
            <th class="bg-[#5b2a86] text-white">Persentange(%)</th>
            @if ($editable)
                <th class="w-[8%] bg-[#5b2a86] text-white"></th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($taxObservationRows as $rowIndex => $row)
            <tr>
                @foreach (['total_population', 'sample_size', 'instances_found', 'percentage'] as $field)
                    <td class="text-center">
                        @if ($editable)
                            <input type="text" wire:model.live="taxObservationRows.{{ $rowIndex }}.{{ $field }}" class="w-full border-0 bg-transparent text-center text-[11px]">
                        @else
                            {{ $row[$field] ?? '' }}
                        @endif
                    </td>
                @endforeach
                @if ($editable)
                    <td class="text-center">
                        @if (count($taxObservationRows) > 1)
                            <button type="button" wire:click="removeTaxObservationRow({{ $rowIndex }})" class="text-[10px] text-rose-600">×</button>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
@if ($editable)
    <button type="button" wire:click="addTaxObservationRow" class="mt-[2mm] text-[11px] font-medium text-[#2b579a]">+ Tax row</button>
@endif
