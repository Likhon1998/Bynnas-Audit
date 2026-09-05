<div class="px-4 py-5 lg:px-6" style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;">
    <?php $latestFile = $files->first(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewMode === 'home'): ?>
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2">
                    <a
                        href="<?php echo e(route('audits.index')); ?>"
                        class="inline-flex h-7 items-center gap-1 rounded-md border border-slate-200 bg-white px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                    >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        Back to reports
                    </a>
                </div>
                <h1 class="text-[16px] font-semibold text-navy-900">Check List</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    <?php echo e($report->shakha_display_name ?: ($report->shakha?->name ?? 'Branch')); ?>

                    · <?php echo e($report->periodLabel()); ?>

                    · Choose headings · fill · save · edit
                </p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($files->isEmpty()): ?>
                <label
                    class="inline-flex h-8 cursor-pointer items-center rounded-md bg-[#2b579a] px-3 text-[12px] font-semibold text-white hover:bg-[#204072]"
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="upload"
                >
                    <span wire:loading.remove wire:target="upload">Check List File</span>
                    <span wire:loading wire:target="upload">Uploading…</span>
                    <input type="file" wire:model="upload" accept=".pdf,.doc,.docx" class="sr-only">
                </label>
            <?php else: ?>
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button
                        type="button"
                        @click="open = !open"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md bg-[#2b579a] px-3 text-[12px] font-semibold text-white hover:bg-[#204072]"
                    >
                        <span wire:loading.remove wire:target="upload">Check List File</span>
                        <span wire:loading wire:target="upload">Uploading…</span>
                        <svg class="h-3.5 w-3.5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div
                        x-show="open"
                        x-cloak
                        @click.outside="open = false"
                        class="absolute right-0 z-20 mt-1 w-40 overflow-hidden rounded-md border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        <button type="button" wire:click="downloadFile(<?php echo e($latestFile->id); ?>)" @click="open = false" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-medium text-slate-700 hover:bg-slate-50">Download</button>
                        <label class="flex w-full cursor-pointer items-center px-3 py-2 text-left text-[12px] font-medium text-slate-700 hover:bg-slate-50" @click="open = false">
                            Add new
                            <input type="file" wire:model="upload" accept=".pdf,.doc,.docx" class="sr-only">
                        </label>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['upload'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[12px] text-rose-700"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3 py-2.5">
                <div>
                    <p class="text-[13px] font-semibold text-navy-900">Headings for this report</p>
                    <p class="text-[10px] text-slate-500">এই শাখার রিপোর্টে যে চেকলিস্টগুলোতে কাজ করবেন — বেছে নিন (এক বা একাধিক)</p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $choosingHeadings): ?>
                    <button
                        type="button"
                        wire:click="openHeadingPicker"
                        class="inline-flex h-8 items-center rounded-md bg-[#2b579a] px-3 text-[12px] font-semibold text-white hover:bg-[#204072]"
                    ><?php echo e($selectedFormats->isEmpty() ? 'Choose headings' : 'Change headings'); ?></button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($choosingHeadings): ?>
                <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            type="search"
                            wire:model.live.debounce.250ms="search"
                            placeholder="Search heading…"
                            class="h-8 min-w-[200px] flex-1 rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]"
                        >
                        <button type="button" wire:click="saveHeadingSelection" class="inline-flex h-8 items-center rounded-md bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700">Save selection</button>
                        <button type="button" wire:click="closeHeadingPicker" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                    </div>
                    <p class="mt-1.5 text-[10px] text-slate-500">Tick the headings you work on for this shakha report</p>
                </div>
                <div class="max-h-72 divide-y divide-slate-100 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pickerFormats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $checked = in_array((int) $format->id, array_map('intval', $pickedFormatIds), true); ?>
                        <label class="flex cursor-pointer items-start gap-2.5 px-3 py-2.5 hover:bg-slate-50">
                            <input
                                type="checkbox"
                                class="mt-0.5 rounded border-slate-300 text-[#2b579a] focus:ring-[#2b579a]"
                                <?php if($checked): echo 'checked'; endif; ?>
                                wire:click="togglePickFormat(<?php echo e($format->id); ?>)"
                            >
                            <span class="min-w-0 flex-1">
                                <span class="mr-1.5 inline-flex h-5 items-center rounded bg-slate-100 px-1.5 text-[9px] font-bold text-slate-600">F<?php echo e($format->format_number); ?></span>
                                <span class="text-[12px] font-semibold text-navy-900"><?php echo e($format->heading); ?></span>
                            </span>
                        </label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="px-4 py-8 text-center text-[12px] text-slate-400">No heading matched</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif($selectedFormats->isEmpty()): ?>
                <div class="px-4 py-10 text-center">
                    <p class="text-[13px] font-medium text-slate-600">No headings selected yet</p>
                    <p class="mt-1 text-[12px] text-slate-400">Click <span class="font-semibold">Choose headings</span> — pick one or many</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $selectedFormats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $format): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $sub = $submissionsByFormat->get($format->id);
                            $status = $sub?->status;
                        ?>
                        <div class="flex flex-wrap items-center gap-2 px-3 py-2.5">
                            <span class="inline-flex h-7 items-center rounded-md bg-slate-100 px-2 text-[10px] font-bold tabular-nums text-slate-600">
                                Format <?php echo e($format->format_number); ?>

                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-semibold text-navy-900"><?php echo e($format->heading); ?></p>
                                <p class="text-[10px] text-slate-500">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'evidence'): ?>
                                        <span class="font-semibold text-emerald-700">Evidence saved</span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub?->saved_at): ?>
                                            · <?php echo e($sub->saved_at->timezone('Asia/Dhaka')->format('d M Y, h:i A')); ?>

                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($status === 'draft'): ?>
                                        <span class="font-semibold text-amber-700">Draft</span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub?->saved_at): ?>
                                            · <?php echo e($sub->saved_at->timezone('Asia/Dhaka')->format('d M Y, h:i A')); ?>

                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        Not filled yet
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </p>
                            </div>
                            <button
                                type="button"
                                wire:click="workOnFormat(<?php echo e($format->id); ?>)"
                                class="inline-flex h-8 items-center rounded-md <?php echo e($sub ? 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' : 'bg-[#2b579a] text-white hover:bg-[#204072]'); ?> px-3 text-[12px] font-semibold"
                            ><?php echo e($sub ? 'Edit' : 'Fill'); ?></button>
                            <button
                                type="button"
                                wire:click="removeHeading(<?php echo e($format->id); ?>)"
                                wire:confirm="Remove this heading from the report? (Saved evidence stays until you delete it separately.)"
                                class="inline-flex h-8 items-center rounded-md border border-rose-200 px-2 text-[11px] font-medium text-rose-600 hover:bg-rose-50"
                            >Remove</button>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <button type="button" wire:click="backHome" class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </button>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold text-slate-500">Format <?php echo e($formatModel?->format_number); ?> · Fill / Edit</p>
                <p class="truncate text-[13px] font-semibold text-navy-900"><?php echo e($formatModel?->heading); ?></p>
            </div>
            <button type="button" wire:click="saveDraft" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Save draft</button>
            <button type="button" wire:click="saveEvidence" class="inline-flex h-8 items-center rounded-md bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700">Save as evidence</button>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php echo $__env->make('livewire.partials.audit-checklist-format-editor', [
            'formatModel' => $formatModel,
            'definition' => $definition,
            'payload' => $payload,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/audit-report-checklist.blade.php ENDPATH**/ ?>