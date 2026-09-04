<?php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $cellPad = $compact ? '' : 'border border-slate-800 px-1 py-0.5';
?>

<div class="mb-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <input type="text" wire:model.live="page20_it_title" class="finding-serial-input mb-3 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold">
    <?php else: ?>
        <p class="mb-3 text-center text-[12px] font-bold finding-heading"><?php echo \App\Support\BanglaNumerals::highlight($page20_it_title ?? '', 'serial'); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mb-3 text-center text-[11px] leading-relaxed">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <input type="text" wire:model.live="page20_it_org_line1" class="mb-1 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-center">
            <input type="text" wire:model.live="page20_it_org_line2" class="mb-1 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-center">
            <input type="text" wire:model.live="page20_it_org_line3" class="mb-1 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-center">
        <?php else: ?>
            <p class="m-0"><?php echo e($page20_it_org_line1 ?? ''); ?></p>
            <p class="m-0"><?php echo e($page20_it_org_line2 ?? ''); ?></p>
            <p class="m-0"><?php echo e($page20_it_org_line3 ?? ''); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mb-3 flex flex-wrap justify-center gap-4 text-[11px]">
        <div class="flex items-center gap-2">
            <span class="font-semibold">কর্মসূচীর নাম:</span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <input type="text" wire:model.live="page20_it_program" class="min-w-[120px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1">
            <?php else: ?>
                <span><?php echo e($page20_it_program ?? ''); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="flex items-center gap-2">
            <span class="font-semibold">শাখার নাম:</span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <input type="text" wire:model.live="page20_it_branch" class="min-w-[160px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1">
            <?php else: ?>
                <span><?php echo e($page20_it_branch ?? ''); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <input type="text" wire:model.live="page20_it_instruction" class="mb-3 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-center text-[11px]" placeholder="প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন">
    <?php else: ?>
        <p class="mb-2 text-center text-[11px] font-semibold"><?php echo e($page20_it_instruction ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন'); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="overflow-x-auto">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <?php if (isset($component)) { $__componentOriginal3931ccc341723360a2655698c41db1b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3931ccc341723360a2655698c41db1b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-excel-paste-zone','data' => ['path' => 'page20ItChecklistRows','columns' => ['sl_no', 'description', 'compliance', 'action_owner', 'management_comments', 'recommendation'],'hint' => 'IT checklist: Excel থেকে কলাম ক্রমে পেস্ট (Compliance = Yes/No/N/A)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-excel-paste-zone'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => 'page20ItChecklistRows','columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['sl_no', 'description', 'compliance', 'action_owner', 'management_comments', 'recommendation']),'hint' => 'IT checklist: Excel থেকে কলাম ক্রমে পেস্ট (Compliance = Yes/No/N/A)']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3931ccc341723360a2655698c41db1b9)): ?>
<?php $attributes = $__attributesOriginal3931ccc341723360a2655698c41db1b9; ?>
<?php unset($__attributesOriginal3931ccc341723360a2655698c41db1b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3931ccc341723360a2655698c41db1b9)): ?>
<?php $component = $__componentOriginal3931ccc341723360a2655698c41db1b9; ?>
<?php unset($__componentOriginal3931ccc341723360a2655698c41db1b9); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <table class="<?php echo e($compact ? 'a4-table a4-table-compact text-[7.5px]' : 'w-full border-collapse text-[9px]'); ?> min-w-full">
            <thead>
                <tr class="bg-slate-100">
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center">ক্রমিক</th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center">বিবরণ</th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center" colspan="3">Compliance</th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center">Action Owner (কার দায়িত্ব)</th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center">Management Comments (ব্যবস্থাপনার মন্তব্য)</th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center">Recommendation (সুপারিশ)</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <th class="<?php echo e($cellPad); ?>"></th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
                <tr class="bg-slate-100">
                    <th class="<?php echo e($cellPad); ?>" colspan="2"></th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center">Yes</th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center">No</th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center">N/A</th>
                    <th class="<?php echo e($cellPad); ?>" colspan="3"></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <th class="<?php echo e($cellPad); ?>"></th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($page20ItChecklistRows ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $compliance = (string) ($row['compliance'] ?? ''); ?>
                    <tr>
                        <td class="<?php echo e($cellPad); ?> text-center align-top">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.live="page20ItChecklistRows.<?php echo e($rowIndex); ?>.sl_no" class="w-full border-0 bg-sky-50/50 px-0.5 text-center text-[8px]">
                            <?php else: ?>
                                <?php echo e($row['sl_no'] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="<?php echo e($cellPad); ?> align-top">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <textarea wire:model.live="page20ItChecklistRows.<?php echo e($rowIndex); ?>.description" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                            <?php else: ?>
                                <span class="whitespace-pre-wrap"><?php echo e($row['description'] ?? ''); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-top" colspan="3">
                                <select wire:model.live="page20ItChecklistRows.<?php echo e($rowIndex); ?>.compliance" class="w-full rounded border border-slate-200 bg-sky-50/40 px-1 py-0.5 text-[8px]">
                                    <option value="">—</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                    <option value="na">N/A</option>
                                </select>
                            </td>
                        <?php else: ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-top"><?php echo e($compliance === 'yes' ? '✓' : ''); ?></td>
                            <td class="<?php echo e($cellPad); ?> text-center align-top"><?php echo e($compliance === 'no' ? '✓' : ''); ?></td>
                            <td class="<?php echo e($cellPad); ?> text-center align-top"><?php echo e($compliance === 'na' ? '✓' : ''); ?></td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <td class="<?php echo e($cellPad); ?> align-top">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.live="page20ItChecklistRows.<?php echo e($rowIndex); ?>.action_owner" class="w-full border-0 bg-sky-50/50 px-0.5 text-[8px]">
                            <?php else: ?>
                                <?php echo e($row['action_owner'] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="<?php echo e($cellPad); ?> align-top">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <textarea wire:model.live="page20ItChecklistRows.<?php echo e($rowIndex); ?>.management_comments" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                            <?php else: ?>
                                <span class="whitespace-pre-wrap"><?php echo e($row['management_comments'] ?? ''); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="<?php echo e($cellPad); ?> align-top">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <textarea wire:model.live="page20ItChecklistRows.<?php echo e($rowIndex); ?>.recommendation" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                            <?php else: ?>
                                <span class="whitespace-pre-wrap"><?php echo e($row['recommendation'] ?? ''); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($page20ItChecklistRows ?? []) > 1): ?>
                                    <button type="button" wire:click="removePage20ItChecklistRow(<?php echo e($rowIndex); ?>)" class="text-[10px] text-rose-600">×</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <button type="button" wire:click="addPage20ItChecklistRow" class="mt-2 text-[11px] font-medium text-[#2b579a]">+ IT checklist row</button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-page20-it-section.blade.php ENDPATH**/ ?>