<div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
    <div class="mb-2 flex items-center justify-between gap-2">
        <p class="text-[12px] font-semibold text-slate-800">৪. রিপোর্ট বিষয়বস্তু</p>
        <span class="text-[11px] text-slate-500">পৃষ্ঠা ৪ · + → শিরোনাম / বিভাগ / নিয়ম / পর্যবেক্ষণ / Risk / Jobab / Table</span>
    </div>

    <div class="mx-auto max-w-[960px] rounded-sm bg-white p-6 shadow-lg">
        <?php echo $__env->make('livewire.partials.audit-financial-audit-section', [
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
            'customTableEditorIndex' => $customTableEditorIndex ?? null,
            'customTableSizeCols' => $customTableSizeCols ?? 4,
            'customTableSizeRows' => $customTableSizeRows ?? 5,
            'customTableSelR' => $customTableSelR ?? null,
            'customTableSelC' => $customTableSelC ?? null,
            'customTableMergeRows' => $customTableMergeRows ?? 2,
            'customTableMergeCols' => $customTableMergeCols ?? 1,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mt-6 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
            <p class="text-[11px] text-slate-500">পৃষ্ঠা ৪ · শেষ পৃষ্ঠা</p>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button type="button" wire:click="savePage4" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ</button>
                <button type="button" wire:click="completeReport" class="h-8 rounded-lg bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700">সম্পন্ন করুন</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-page4-form.blade.php ENDPATH**/ ?>