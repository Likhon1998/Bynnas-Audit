@php
    $rows = \App\Support\AuditReportClassification::ratingRows();
    $summaryRows = \App\Support\AuditReportClassification::performanceSummaryRows();
    $compact = $compact ?? true;
    $editable = $editable ?? false;
    $tableClass = $compact ? 'a4-table a4-table-compact text-[8px]' : 'a4-table text-[10px]';
    $hImportance = $tableHeaders['classification_importance'] ?? \App\Support\AuditTableHeaders::defaults()['classification_importance'];
    $hEval = $tableHeaders['classification_eval'] ?? \App\Support\AuditTableHeaders::defaults()['classification_eval'];
@endphp

<h3 class="mb-[2mm] text-center text-[12px] font-bold underline decoration-1 underline-offset-4">প্রতিবেদনের শ্রেণীবিন্যাস</h3>

<table class="{{ $tableClass }} leading-[1.35]">
    <thead>
        <tr>
            <x-audit-th :editable="$editable" wire="tableHeaders.classification_importance.0" class="w-[16%] bg-[#BDD7EE]">{{ $hImportance[0] }}</x-audit-th>
            <x-audit-th :editable="$editable" wire="tableHeaders.classification_importance.1" class="w-[7%] bg-[#BDD7EE]">{{ $hImportance[1] }}</x-audit-th>
            <x-audit-th :editable="$editable" wire="tableHeaders.classification_importance.2" class="bg-[#BDD7EE]">{{ $hImportance[2] }}</x-audit-th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                @if ($row['level'] !== null)
                    <td rowspan="{{ $row['level_rowspan'] }}" class="align-middle text-center font-bold">{{ $row['level'] }}</td>
                @endif
                <td class="text-center font-bold" style="background: {{ $row['code_bg'] }}; color: {{ $row['code_color'] }};">{{ $row['code'] }}</td>
                <td class="align-top text-justify">
                    @if ($row['bulleted'])
                        <ul class="m-0 list-disc pl-[4mm]">
                            @foreach ($row['items'] as $item)
                                <li class="mb-[0.8mm]">{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="m-0">{{ $row['items'][0] ?? '' }}</p>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="{{ $tableClass }} mt-[2mm] leading-[1.35]">
    <thead>
        <tr>
            <x-audit-th :editable="$editable" wire="tableHeaders.classification_eval.0" class="bg-[#2E5090] text-white">{{ $hEval[0] }}</x-audit-th>
            <x-audit-th :editable="$editable" wire="tableHeaders.classification_eval.1" class="w-[30%] bg-[#2E5090] text-white">{{ $hEval[1] }}</x-audit-th>
        </tr>
    </thead>
    <tbody>
        @foreach ($summaryRows as $summary)
            <tr>
                <td>{{ $summary['label'] }}</td>
                <td class="text-center font-semibold">{{ $summary['range'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
