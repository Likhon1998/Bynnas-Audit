<div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
    <div class="mb-2 flex items-center justify-between gap-2">
        <p class="text-[12px] font-semibold text-slate-800">৮. Finding ১.৮ (Cost of Fund)</p>
        <span class="text-[11px] text-slate-500">পৃষ্ঠা ৮ · প্রধান কার্যালয় তহবিলের লাভ হিসাব</span>
    </div>

    <div class="mx-auto max-w-[1180px] rounded-sm bg-white p-6 shadow-lg">
        <?php echo $__env->make('livewire.partials.audit-page8-findings-section', [
            'editable' => true,
            'compact' => false,
            'page8Findings' => $page8Findings,
            'findingRatings' => $findingRatings,
            'indicatorOptions' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
            'financialIndicatorOptions' => $financialIndicatorOptions ?? [],
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mt-6 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
            <p class="text-[11px] text-slate-500">পৃষ্ঠা ৮</p>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="$set('activeTab', 'page7')" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">← পৃষ্ঠা ৭</button>
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button type="button" wire:click="savePage8" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ ও পরবর্তী →</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-page8-form.blade.php ENDPATH**/ ?>