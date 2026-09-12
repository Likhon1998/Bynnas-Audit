<div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
    <div class="mb-2 flex items-center justify-between gap-2">
        <p class="text-[12px] font-semibold text-slate-800">৪. রিপোর্ট বিষয়বস্তু</p>
        <span class="text-[11px] text-slate-500">পৃষ্ঠা ৪ · + → কমপ্লায়েন্স / আইটি চেকলিস্ট / টেবিল</span>
    </div>

    @if (! empty($checklistUrl))
        <div class="mx-auto mb-3 max-w-[960px] rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-[12px] text-slate-700">
            <p>
                <span class="font-semibold">Checklist optional:</span>
                Use checklist only if you want — unusual marks can seed findings into this report.
                @if (($checklistRequired ?? 0) > 0)
                    Progress {{ $checklistDone ?? 0 }}/{{ $checklistRequired }}.
                @endif
            </p>
            <a href="{{ $checklistUrl }}" class="mt-1.5 inline-flex h-7 items-center rounded-md border border-teal-200 bg-white px-2.5 text-[11px] font-semibold text-teal-800 hover:bg-teal-50">Open checklist</a>
        </div>
    @endif
    <div class="mx-auto max-w-[960px] rounded-sm bg-white p-6 shadow-lg">
        @include('livewire.partials.audit-financial-audit-section', [
            'editable' => true,
            'compact' => false,
            'financial_section_title' => $financial_section_title,
            'financialFindings' => $financialFindings,
            'reportSections' => $reportSections ?? [],
            'reportBlocks' => $reportBlocks ?? [],
            'financial_criteria' => $financial_criteria,
            'vatObservationRows' => $vatObservationRows,
            'taxObservationRows' => $taxObservationRows,
            'findingRatings' => $findingRatings,
            'financialIndicatorOptions' => $financialIndicatorOptions ?? [],
            'indicatorOptions' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
            'shakhaStaffOptions' => $shakhaStaffOptions ?? [],
            'customTableEditorIndex' => $customTableEditorIndex ?? null,
            'customTableSizeCols' => $customTableSizeCols ?? 4,
            'customTableSizeRows' => $customTableSizeRows ?? 5,
            'customTableSelR' => $customTableSelR ?? null,
            'customTableSelC' => $customTableSelC ?? null,
            'customTableMergeRows' => $customTableMergeRows ?? 2,
            'customTableMergeCols' => $customTableMergeCols ?? 1,
        ])

        <div class="mt-6 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
            <p class="text-[11px] text-slate-500">পৃষ্ঠা ৪ · + মেনু থেকে কমপ্লায়েন্স টেবিল যোগ করুন</p>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button type="button" wire:click="savePage4" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ</button>
                <button type="button" wire:click="completeReport" class="h-8 rounded-lg bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700">সম্পন্ন করুন</button>
            </div>
        </div>
    </div>
</div>
