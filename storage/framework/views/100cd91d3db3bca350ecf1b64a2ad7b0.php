<?php
    $bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $toBn = function (int $n) use ($bnDigits) {
        return implode('', array_map(fn ($d) => $bnDigits[(int) $d], str_split((string) $n)));
    };
?>

<div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
    <div class="mb-2 flex items-center justify-between gap-2">
        <p class="text-[12px] font-semibold text-slate-800">২. এক নজরে শাখার তথ্য</p>
        <span class="text-[11px] text-slate-500">Row/Column যোগ-বাদ করতে পারবেন · Preview এও দেখাবে</span>
    </div>

    <div class="mx-auto max-w-[960px] rounded-sm bg-white p-6 shadow-lg">
        <div class="mb-3 flex flex-wrap items-center gap-2 text-[13px]">
            <span class="font-bold">এক নজরে</span>
            <input type="text" wire:model.live="shakha_display_name" class="inline-input min-w-[180px] flex-1" placeholder="শাখার নাম">
            <span class="font-bold">শাখার তথ্য (</span>
            <input type="text" wire:model.live="glance_as_of" class="inline-input min-w-[140px]" placeholder="৩১ জুন ২০২৬">
            <span class="font-bold">):</span>
        </div>

        <p class="mb-3 flex flex-wrap items-center gap-2 text-[13px]">
            <span class="font-semibold">শাখা গঠনের তারিখ:</span>
            <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'branch_opening_date','format' => 'iso','class' => 'inline-input']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'branch_opening_date','format' => 'iso','class' => 'inline-input']); ?>
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
            <span>ইং</span>
        </p>

        <div class="mb-2 flex items-center justify-between">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Glance table</p>
            <button type="button" wire:click="addGlanceRow" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Row</button>
        </div>

        <table class="mb-3 w-full border-collapse text-[12px]">
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $glanceRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="w-[24%] border border-slate-800 px-1 py-1">
                            <input type="text" wire:model.live="glanceRows.<?php echo e($idx); ?>.left_label" class="h-7 w-full border-0 bg-sky-50 px-1 text-[12px] focus:ring-1 focus:ring-sky-400" placeholder="Label">
                        </td>
                        <td class="w-[20%] border border-slate-800 px-1 py-1">
                            <input type="text" wire:model.live="glanceRows.<?php echo e($idx); ?>.left_value" class="h-7 w-full border-0 bg-sky-50 px-1 text-[12px] focus:ring-1 focus:ring-sky-400" placeholder="Value">
                        </td>
                        <td class="w-[24%] border border-slate-800 px-1 py-1">
                            <input type="text" wire:model.live="glanceRows.<?php echo e($idx); ?>.right_label" class="h-7 w-full border-0 bg-sky-50 px-1 text-[12px] focus:ring-1 focus:ring-sky-400" placeholder="Label">
                        </td>
                        <td class="w-[20%] border border-slate-800 px-1 py-1">
                            <input type="text" wire:model.live="glanceRows.<?php echo e($idx); ?>.right_value" class="h-7 w-full border-0 bg-sky-50 px-1 text-[12px] focus:ring-1 focus:ring-sky-400" placeholder="Value">
                        </td>
                        <td class="w-[12%] border border-slate-800 px-1 py-1 text-center">
                            <button type="button" wire:click="removeGlanceRow(<?php echo e($idx); ?>)" class="text-[11px] text-rose-600 hover:underline" <?php if(count($glanceRows) <= 1): echo 'disabled'; endif; ?>>Remove</button>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>

        <p class="mb-2 flex flex-wrap items-center gap-2 text-[13px]">
            <span class="font-semibold">শাখার কর্মীর তথ্য :</span>
            <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'staff_info_as_of','format' => 'iso','class' => 'inline-input']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'staff_info_as_of','format' => 'iso','class' => 'inline-input']); ?>
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
            <span>ইং</span>
        </p>

        <div class="mb-2 flex flex-wrap items-center gap-2">
            <p class="mr-auto text-[11px] font-semibold uppercase tracking-wide text-slate-500">Staff table</p>
            <button type="button" wire:click="addStaffRow" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Row</button>
            <button type="button" wire:click="addStaffColumn" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Column</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] border-collapse text-[11px]">
                <thead>
                    <tr class="bg-slate-200">
                        <th class="border border-slate-800 px-1 py-1.5 font-semibold">ক্রমিক নং</th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $staffColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cIdx => $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <th class="border border-slate-800 px-1 py-1.5">
                                <div class="flex items-center gap-1">
                                    <input type="text" wire:model.live="staffColumns.<?php echo e($cIdx); ?>" class="h-7 min-w-[90px] flex-1 border-0 bg-transparent px-1 text-center text-[11px] font-semibold focus:bg-white focus:ring-1 focus:ring-sky-400">
                                    <button type="button" wire:click="removeStaffColumn(<?php echo e($cIdx); ?>)" class="shrink-0 text-[10px] text-rose-600 hover:underline" title="Remove column" <?php if(count($staffColumns) <= 1): echo 'disabled'; endif; ?>>×</button>
                                </div>
                            </th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <th class="border border-slate-800 px-1 py-1.5 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $staffRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td class="border border-slate-800 px-1 py-1 text-center"><?php echo e($toBn($idx + 1)); ?></td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $staffColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cIdx => $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td class="border border-slate-800 px-1 py-1">
                                    <input type="text" wire:model.live="staffRows.<?php echo e($idx); ?>.cells.<?php echo e($cIdx); ?>" class="h-7 w-full border-0 bg-sky-50 px-1 focus:ring-1 focus:ring-sky-400">
                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <td class="border border-slate-800 px-1 py-1 text-center">
                                <button type="button" wire:click="removeStaffRow(<?php echo e($idx); ?>)" class="text-[11px] text-rose-600 hover:underline" <?php if(count($staffRows) <= 1): echo 'disabled'; endif; ?>>Remove</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-8 border-t border-dashed border-slate-200 pt-5">
            <h3 class="mb-3 text-center text-[14px] font-bold underline decoration-1 underline-offset-4">সূচিপত্র</h3>
            <p class="mb-3 text-center text-[11px] text-slate-500">PDF-এ পুরো সূচিপত্র এক নজরের পরে একসাথে বসবে</p>
            <?php echo $__env->make('livewire.partials.audit-toc-table-form', ['previewPage' => 2], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
            <p class="text-[11px] text-slate-500">পৃষ্ঠা ২</p>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button type="button" wire:click="savePage2" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ ও পরবর্তী →</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-page2-form.blade.php ENDPATH**/ ?>