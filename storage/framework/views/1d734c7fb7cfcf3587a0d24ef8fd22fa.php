<div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
    <div class="mb-2 flex items-center justify-between gap-2">
        <p class="text-[12px] font-semibold text-slate-800">৫. বিস্তারিত খরচ টেবিল + ১.৩ হস্তমজুদ</p>
        <span class="text-[11px] text-slate-500">পৃষ্ঠা ৫ · ১.১/১.২ এর ধারাবাহিকতা + Finding ১.৩</span>
    </div>

    <div class="mx-auto max-w-[1100px] rounded-sm bg-white p-6 shadow-lg">
        <?php echo $__env->make('livewire.partials.audit-page5-financial-detail-section', [
            'editable' => true,
            'compact' => false,
            'expenseDetailRows' => $expenseDetailRows,
            'expense_detail_risk' => $expense_detail_risk,
            'expense_detail_root_cause' => $expense_detail_root_cause,
            'expense_detail_recommendation' => $expense_detail_recommendation,
            'expense_detail_bm_reply' => $expense_detail_bm_reply,
            'expense_detail_responsible' => $expense_detail_responsible,
            'expense_detail_resolution_date' => $expense_detail_resolution_date,
            'finding13_serial' => $finding13_serial,
            'finding13_title' => $finding13_title,
            'finding13_body' => $finding13_body,
            'finding13_amount' => $finding13_amount,
            'finding13_rating' => $finding13_rating,
            'finding13_criteria' => $finding13_criteria,
            'finding13_observation' => $finding13_observation,
            'finding13_statsRows' => $finding13_statsRows,
            'finding13_depositRows' => $finding13_depositRows,
            'finding13_risk' => $finding13_risk,
            'finding13_root_cause' => $finding13_root_cause,
            'finding13_recommendation' => $finding13_recommendation,
            'finding13_bm_reply' => $finding13_bm_reply,
            'finding13_responsible' => $finding13_responsible,
            'finding13_resolution_date' => $finding13_resolution_date,
            'findingRatings' => $findingRatings,
            'indicatorOptions' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
            'financialIndicatorOptions' => $financialIndicatorOptions ?? [],
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mt-6 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
            <p class="text-[11px] text-slate-500">পৃষ্ঠা ৫</p>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="$set('activeTab', 'page4')" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">← পৃষ্ঠা ৪</button>
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button type="button" wire:click="savePage5" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ ও পরবর্তী →</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-page5-form.blade.php ENDPATH**/ ?>