<div class="px-4 py-5 lg:px-6" style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewMode === 'catalog'): ?>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-[16px] font-semibold text-navy-900">Checklists</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">ফরম্যাট স্টোরহাউস · শিরোনাম দিয়ে খুঁজুন · পূর্ণ টেমপ্লেট দেখুন</p>
            </div>
            <div class="w-full max-w-sm">
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Search by heading</label>
                <input
                    type="search"
                    wire:model.live.debounce.250ms="search"
                    placeholder="যেমন: সদস্য নির্বাচন… চেকলিস্ট"
                    class="h-9 w-full rounded-lg border-slate-200 text-[12px] shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]"
                >
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-3 py-2">
                <p class="text-[13px] font-semibold text-navy-900">Format storehouse</p>
                <p class="text-[10px] text-slate-500"><?php echo e($formats->count()); ?> format(s) · full template view</p>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $formats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex flex-wrap items-center gap-2 px-3 py-2.5">
                        <span class="inline-flex h-7 items-center rounded-md bg-slate-100 px-2 text-[10px] font-bold tabular-nums text-slate-600">
                            Format <?php echo e($format->format_number); ?>

                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13px] font-semibold text-navy-900"><?php echo e($format->heading); ?></p>
                            <p class="text-[10px] text-slate-500"><?php echo e($format->org_name); ?> · <?php echo e($format->dept_name); ?></p>
                        </div>
                        <button
                            type="button"
                            wire:click="openFormat('<?php echo e($format->code); ?>')"
                            class="inline-flex h-8 items-center rounded-md bg-[#2b579a] px-3 text-[12px] font-semibold text-white hover:bg-[#204072]"
                        >View full template</button>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="px-4 py-10 text-center text-[12px] text-slate-400">কোনো ফরম্যাট পাওয়া যায়নি</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <button type="button" wire:click="backToCatalog" class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Storehouse
            </button>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold text-slate-500">Format <?php echo e($formatModel?->format_number); ?> · Full template</p>
                <p class="truncate text-[13px] font-semibold text-navy-900"><?php echo e($formatModel?->heading); ?></p>
            </div>
            <span class="inline-flex h-8 items-center rounded-md bg-slate-100 px-3 text-[11px] font-medium text-slate-600">View only</span>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($definition['layout'] ?? '') === 'savings_refund' || ($definition['code'] ?? '') === 'format-5'): ?>
            <?php echo $__env->make('livewire.partials.audit-checklist-format-5-preview', [
                'formatModel' => $formatModel,
                'definition' => $definition,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif(($definition['layout'] ?? '') === 'savings_loan_collection' || ($definition['code'] ?? '') === 'format-4'): ?>
            <?php echo $__env->make('livewire.partials.audit-checklist-format-4-preview', [
                'formatModel' => $formatModel,
                'definition' => $definition,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif(($definition['layout'] ?? '') === 'society_management' || ($definition['code'] ?? '') === 'format-3'): ?>
            <?php echo $__env->make('livewire.partials.audit-checklist-format-3-preview', [
                'formatModel' => $formatModel,
                'definition' => $definition,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif(($definition['layout'] ?? '') === 'member_admission' || ($definition['code'] ?? '') === 'format-2'): ?>
            <?php echo $__env->make('livewire.partials.audit-checklist-format-2-preview', [
                'formatModel' => $formatModel,
                'definition' => $definition,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('livewire.partials.audit-checklist-format-1-preview', [
                'formatModel' => $formatModel,
                'definition' => $definition,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <p class="mt-3 text-[11px] text-slate-500">
            পূরণ ও evidence সেভ করতে Audit Report → <span class="font-semibold text-slate-700">Check List</span> ব্যবহার করুন।
        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/audit-checklists.blade.php ENDPATH**/ ?>