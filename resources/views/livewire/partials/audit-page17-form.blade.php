<div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
    <div class="mb-2 flex items-center justify-between gap-2">
        <p class="text-[12px] font-semibold text-slate-800">১৭. Finding ৪.৯ (সঞ্চয় হতে কিস্তি সমন্বয়)</p>
        <span class="text-[11px] text-slate-500">পৃষ্ঠা ১৭ · ১ম ঋণ বাধ্যতামূলক সঞ্চয় আংশিক সমন্বয়</span>
    </div>

    <div class="mx-auto max-w-[1180px] rounded-sm bg-white p-6 shadow-lg">
        @include('livewire.partials.audit-page17-findings-section', [
            'editable' => true,
            'compact' => false,
            'page17Findings' => $page17Findings,
            'findingRatings' => $findingRatings,
            'indicatorOptions' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
            'financialIndicatorOptions' => $financialIndicatorOptions ?? [],
        ])

        <div class="mt-6 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
            <p class="text-[11px] text-slate-500">পৃষ্ঠা ১৭</p>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="$set('activeTab', 'page16')" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">← পৃষ্ঠা ১৬</button>
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button type="button" wire:click="savePage17" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ ও পরবর্তী →</button>
            </div>
        </div>
    </div>
</div>
