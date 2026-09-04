<div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
    <div class="mb-2 flex items-center justify-between gap-2">
        <p class="text-[12px] font-semibold text-slate-800">৩. সূচিপত্র (ধারাবাহিকতা) + শ্রেণীবিন্যাস + স্বাক্ষর</p>
        <span class="text-[11px] text-slate-500">সূচিপত্র PDF-এ এক নজরের সাথে একসাথে · এখানে শ্রেণীবিন্যাস ও স্বাক্ষর</span>
    </div>

    <div class="mx-auto max-w-[960px] rounded-sm bg-white p-6 shadow-lg">
        <h3 class="mb-3 text-center text-[14px] font-bold underline decoration-1 underline-offset-4">সূচিপত্র</h3>

        <?php echo $__env->make('livewire.partials.audit-toc-table-form', ['previewPage' => 3], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mt-8 border-t border-dashed border-slate-200 pt-5">
            <p class="mb-3 text-[11px] font-semibold uppercase tracking-wide text-slate-500">প্রতিবেদনের শ্রেণীবিন্যাস (নির্দেশিকা)</p>
            <?php echo $__env->make('livewire.partials.audit-classification-table', ['compact' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <div class="mt-8 border-t border-dashed border-slate-200 pt-5">
            <p class="mb-3 text-[11px] font-semibold uppercase tracking-wide text-slate-500">স্বাক্ষর অংশ (PDF পৃষ্ঠা ৩)</p>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded border border-slate-300 p-3">
                    <p class="mb-2 text-[12px] font-bold">নিরীক্ষা কর্মকর্তা</p>
                    <label class="mb-2 block text-[11px] text-slate-600">নাম
                        <input type="text" wire:model.live="sign_auditor_name" class="mt-1 inline-input w-full">
                    </label>
                    <label class="mb-2 block text-[11px] text-slate-600">পদবী
                        <input type="text" wire:model.live="sign_auditor_designation" class="mt-1 inline-input w-full">
                    </label>
                    <label class="block text-[11px] text-slate-600">তারিখ
                        <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'sign_auditor_date','format' => 'iso','class' => 'mt-1 inline-input w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'sign_auditor_date','format' => 'iso','class' => 'mt-1 inline-input w-full']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $attributes = $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $component = $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
                    </label>
                </div>

                <div class="rounded border border-slate-300 p-3">
                    <p class="mb-2 text-[12px] font-bold">শাখা ব্যবস্থাপক</p>
                    <label class="mb-2 block text-[11px] text-slate-600">নাম
                        <input type="text" wire:model.live="sign_bm_name" class="mt-1 inline-input w-full">
                    </label>
                    <label class="block text-[11px] text-slate-600">তারিখ
                        <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'sign_bm_date','format' => 'iso','class' => 'mt-1 inline-input w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'sign_bm_date','format' => 'iso','class' => 'mt-1 inline-input w-full']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $attributes = $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $component = $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
                    </label>
                </div>

                <div class="rounded border border-slate-300 p-3">
                    <p class="mb-2 text-[12px] font-bold">সহকারী শাখা ব্যবস্থাপক</p>
                    <label class="mb-2 block text-[11px] text-slate-600">নাম
                        <input type="text" wire:model.live="sign_abm_name" class="mt-1 inline-input w-full">
                    </label>
                    <label class="block text-[11px] text-slate-600">তারিখ
                        <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'sign_abm_date','format' => 'iso','class' => 'mt-1 inline-input w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'sign_abm_date','format' => 'iso','class' => 'mt-1 inline-input w-full']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $attributes = $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $component = $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
            <p class="text-[11px] text-slate-500">পৃষ্ঠা ৩</p>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button type="button" wire:click="savePage3" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ ও পরবর্তী →</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-page3-form.blade.php ENDPATH**/ ?>