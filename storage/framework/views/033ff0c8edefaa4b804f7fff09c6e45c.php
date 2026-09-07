
<?php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $coreFields = ['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no'];
    $headers = array_values((array) ($block['headers'] ?? \App\Support\AuditTableHeaders::defaults()['compliance']));
    if (count($headers) < 6) {
        $headers = array_values(\App\Support\AuditTableHeaders::defaults()['compliance']);
    }
    $extraCount = max(0, count($headers) - count($coreFields));
    $rows = array_values((array) ($block['rows'] ?? []));
    $cellPad = $compact ? '' : 'border border-slate-800 px-1.5 py-1';
    $tableClass = $compact ? 'a4-table a4-table-compact text-[7.5px]' : 'w-full border-collapse text-[10px]';
?>

<div class="mt-[3mm] mb-[4mm]" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'compliance-'.e($blockIndex).''; ?>wire:key="compliance-<?php echo e($blockIndex); ?>" id="<?php echo e(\App\Livewire\MakeAuditReport::sectionAnchorId((string) ($block['serial'] ?? 'compliance'))); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <p class="text-[11px] font-semibold text-emerald-800">কমপ্লায়েন্স টেবিল</p>
            <button type="button" wire:click="fillComplianceBlockFromReport(<?php echo e($blockIndex); ?>)" class="rounded border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-700 hover:bg-slate-50">রিপোর্টের নম্বর বসান</button>
            <button type="button" wire:click="addComplianceBlockColumn(<?php echo e($blockIndex); ?>)" class="rounded bg-emerald-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-emerald-800">+ কলাম</button>
            <button type="button" wire:click="addComplianceBlockRow(<?php echo e($blockIndex); ?>)" class="rounded bg-emerald-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-emerald-800">+ সারি</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'up')" class="ml-auto text-[11px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock(<?php echo e($blockIndex); ?>)" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-3 space-y-2 rounded border border-emerald-100 bg-emerald-50/30 p-3">
            <p class="text-center text-[10px] font-semibold uppercase tracking-wide text-emerald-800">শিরোনাম (প্রিভিউ/PDF/Doc-এর মতো কেন্দ্রীয়)</p>
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.title" class="finding-serial-input w-full rounded border border-slate-200 bg-white px-2 py-1.5 text-center text-[13px] font-bold" placeholder="৫.০ বিগত অভ্যন্তরীণ নিরীক্ষা…">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.title_en" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px] font-semibold" placeholder="(Compliance of Previous Internal Audit Report Reply)">
            <div class="flex flex-wrap items-center justify-center gap-2 text-[11px]">
                <span class="font-semibold">নিরীক্ষাকাল ঃ</span>
                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.period" class="min-w-[200px] rounded border border-slate-200 bg-white px-2 py-1 text-center" placeholder="-ডিসেম্বর’২৫ থেকে মার্চ’২৬">
            </div>
            <div class="flex flex-wrap items-center justify-center gap-2 text-[11px]">
                <span class="font-semibold">ফলোআপের তারিখ ঃ</span>
                <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.blur' => 'reportBlocks.'.e($blockIndex).'.followup_date','format' => 'dmy','class' => 'min-w-[120px] rounded border border-slate-200 bg-white px-2 py-1 text-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.blur' => 'reportBlocks.'.e($blockIndex).'.followup_date','format' => 'dmy','class' => 'min-w-[120px] rounded border border-slate-200 bg-white px-2 py-1 text-center']); ?>
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
            </div>
        </div>
    <?php else: ?>
        <?php echo $__env->make('audits.partials.compliance-heading', ['block' => $block, 'forDoc' => $compact], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <?php if (isset($component)) { $__componentOriginal3931ccc341723360a2655698c41db1b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3931ccc341723360a2655698c41db1b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-excel-paste-zone','data' => ['path' => 'reportBlocks.'.e($blockIndex).'.rows','columns' => array_merge($coreFields, collect(range(0, max(0, $extraCount - 1)))->map(fn ($i) => 'extra.'.$i)->all()),'hint' => 'Compliance: Excel থেকে কলামগুলো একই ক্রমে পেস্ট করুন']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-excel-paste-zone'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => 'reportBlocks.'.e($blockIndex).'.rows','columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(array_merge($coreFields, collect(range(0, max(0, $extraCount - 1)))->map(fn ($i) => 'extra.'.$i)->all())),'hint' => 'Compliance: Excel থেকে কলামগুলো একই ক্রমে পেস্ট করুন']); ?>
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

    <div class="overflow-x-auto">
        <table class="<?php echo e($tableClass); ?> min-w-full">
            <thead>
                <tr class="bg-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hi => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="<?php echo e($cellPad); ?> font-semibold text-center align-middle">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <div class="flex items-start gap-1">
                                    <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.headers.<?php echo e($hi); ?>" class="w-full border-0 bg-transparent text-center text-[10px] font-semibold">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hi >= 6): ?>
                                        <button type="button" wire:click="removeComplianceBlockColumn(<?php echo e($blockIndex); ?>, <?php echo e($hi); ?>)" class="shrink-0 text-[10px] text-rose-600" title="কলাম মুছুন">×</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php else: ?>
                                <?php echo e($label); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <th class="<?php echo e($cellPad); ?> w-8"></th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $coreFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="<?php echo e($cellPad); ?> align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.<?php echo e($field); ?>" rows="3" class="w-full border-0 bg-sky-50/50 p-1 text-[10px] leading-snug"></textarea>
                                <?php else: ?>
                                    <span class="whitespace-pre-wrap"><?php echo e($row[$field] ?? ''); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($ei = 0; $ei < $extraCount; $ei++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="<?php echo e($cellPad); ?> align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.extra.<?php echo e($ei); ?>" rows="3" class="w-full border-0 bg-sky-50/50 p-1 text-[10px] leading-snug"></textarea>
                                <?php else: ?>
                                    <span class="whitespace-pre-wrap"><?php echo e($row['extra'][$ei] ?? ''); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($rows) > 1): ?>
                                    <button type="button" wire:click="removeComplianceBlockRow(<?php echo e($blockIndex); ?>, <?php echo e($rowIndex); ?>)" class="text-[11px] font-semibold text-rose-600">×</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-compliance-table-block.blade.php ENDPATH**/ ?>