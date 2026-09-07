<?php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $cellPad = $compact ? '' : 'border border-slate-800 px-1.5 py-1';
    $coreFields = ['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no'];
    $hCompliance = array_values($tableHeaders['compliance'] ?? \App\Support\AuditTableHeaders::defaults()['compliance']);
    $extraCount = max(0, count($hCompliance) - count($coreFields));
?>

<div class="mb-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <input type="text" wire:model.live="page19_compliance_title" class="finding-serial-input mb-3 w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1.5 text-[13px] font-bold">
    <?php else: ?>
        <p class="mb-3 text-[12px] font-bold finding-heading"><?php echo \App\Support\BanglaNumerals::highlight($page19_compliance_title ?? '', 'serial'); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mb-3 flex flex-wrap gap-4 text-[11px]">
        <div class="flex items-center gap-2">
            <span class="font-semibold">নিরীক্ষাকাল:</span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <input type="text" wire:model.live="page19_compliance_period" class="min-w-[180px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1" placeholder="যেমন: December '22 to March '23">
            <?php else: ?>
                <span><?php echo e($page19_compliance_period ?? ''); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="flex items-center gap-2">
            <span class="font-semibold">ফলোআপের তারিখ:</span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'page19_compliance_followup_date','format' => 'dmy','class' => 'min-w-[120px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'page19_compliance_followup_date','format' => 'dmy','class' => 'min-w-[120px] rounded border border-slate-200 bg-sky-50/40 px-2 py-1']); ?>
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
            <?php else: ?>
                <span><?php echo e($page19_compliance_followup_date ?? ''); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <?php if (isset($component)) { $__componentOriginal3931ccc341723360a2655698c41db1b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3931ccc341723360a2655698c41db1b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-excel-paste-zone','data' => ['path' => 'page19ComplianceRows','columns' => array_merge($coreFields, collect(range(0, max(0, $extraCount - 1)))->map(fn ($i) => 'extra.'.$i)->all()),'hint' => 'Compliance: Excel থেকে কলামগুলো একই ক্রমে পেস্ট করুন']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-excel-paste-zone'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => 'page19ComplianceRows','columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(array_merge($coreFields, collect(range(0, max(0, $extraCount - 1)))->map(fn ($i) => 'extra.'.$i)->all())),'hint' => 'Compliance: Excel থেকে কলামগুলো একই ক্রমে পেস্ট করুন']); ?>
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
            <button type="button" wire:click="fillComplianceNumbersFromReport" class="rounded border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                রিপোর্টের নম্বর বসান
            </button>
            <button type="button" wire:click="addPage19ComplianceColumn" class="rounded border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-[#2b579a] hover:bg-sky-50">
                + কলাম
            </button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="overflow-x-auto">
        <table class="<?php echo e($compact ? 'a4-table a4-table-compact text-[7.5px]' : 'w-full border-collapse text-[10px]'); ?> min-w-full">
            <thead>
                <tr class="bg-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hCompliance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hi => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="<?php echo e($cellPad); ?> font-semibold text-center align-middle">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <div class="flex items-start gap-1">
                                    <input type="text" wire:model.live="tableHeaders.compliance.<?php echo e($hi); ?>" class="w-full border-0 bg-transparent text-center text-[10px] font-semibold">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hi >= 6): ?>
                                        <button type="button" wire:click="removePage19ComplianceColumn(<?php echo e($hi); ?>)" class="shrink-0 text-[10px] text-rose-600" title="কলাম মুছুন">×</button>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($page19ComplianceRows ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $coreFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="<?php echo e($cellPad); ?> align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <textarea wire:model.live="page19ComplianceRows.<?php echo e($rowIndex); ?>.<?php echo e($field); ?>" rows="3" class="w-full border-0 bg-sky-50/50 p-1 text-[10px] leading-snug"></textarea>
                                <?php else: ?>
                                    <span class="whitespace-pre-wrap"><?php echo e($row[$field] ?? ''); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($ei = 0; $ei < $extraCount; $ei++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="<?php echo e($cellPad); ?> align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <textarea wire:model.live="page19ComplianceRows.<?php echo e($rowIndex); ?>.extra.<?php echo e($ei); ?>" rows="3" class="w-full border-0 bg-sky-50/50 p-1 text-[10px] leading-snug"></textarea>
                                <?php else: ?>
                                    <span class="whitespace-pre-wrap"><?php echo e($row['extra'][$ei] ?? ''); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($page19ComplianceRows ?? []) > 1): ?>
                                    <button type="button" wire:click="removePage19ComplianceRow(<?php echo e($rowIndex); ?>)" class="text-[11px] font-semibold text-rose-600">×</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <button type="button" wire:click="addPage19ComplianceRow" class="mt-2 text-[11px] font-semibold text-[#2b579a] hover:underline">+ সারি যোগ করুন</button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-page19-compliance-section.blade.php ENDPATH**/ ?>