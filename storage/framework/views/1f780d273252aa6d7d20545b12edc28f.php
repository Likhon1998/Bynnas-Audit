
<?php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $coreFields = ['area_of_observation', 'year_of_reporting', 'external_observation', 'compliance', 'internal_index_no'];
    $headers = array_values((array) ($block['headers'] ?? \App\Support\AuditTableHeaders::defaults()['external_audit']));
    if (count($headers) < 5) {
        $headers = array_values(\App\Support\AuditTableHeaders::defaults()['external_audit']);
    }
    $extraCount = max(0, count($headers) - count($coreFields));
    $rows = array_values((array) ($block['rows'] ?? []));
    $cellPad = $compact ? 'border border-slate-800 px-1 py-1' : 'border border-slate-800 px-1.5 py-1.5';
    $tableClass = $compact
        ? 'a4-table a4-table-compact text-[9.5px] external-audit-table'
        : 'w-full border-collapse text-[11px] leading-snug external-audit-table';
    $titleSize = $compact ? '12px' : '14px';
    $metaSize = $compact ? '10.5px' : '12px';
?>

<div class="mt-[3mm] mb-[4mm]" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'external-audit-'.e($blockIndex).''; ?>wire:key="external-audit-<?php echo e($blockIndex); ?>" id="<?php echo e(\App\Livewire\MakeAuditReport::sectionAnchorId((string) ($block['serial'] ?? 'external'))); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <p class="text-[12px] font-semibold text-amber-900">বহিঃ নিরীক্ষা কমপ্লায়েন্স</p>
            <button type="button" wire:click="addExternalAuditBlockRow(<?php echo e($blockIndex); ?>)" class="rounded bg-amber-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-amber-800">+ সারি</button>
            <button type="button" wire:click="addExternalAuditBlockColumn(<?php echo e($blockIndex); ?>)" class="rounded bg-amber-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-amber-800">+ কলাম</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'up')" class="ml-auto text-[12px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'down')" class="text-[12px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock(<?php echo e($blockIndex); ?>)" class="text-[12px] text-rose-600 hover:underline">মুছুন</button>
        </div>

        <div class="mb-3 space-y-1.5 rounded border border-amber-200 bg-amber-50/50 p-3 text-center">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.title" class="finding-serial-input w-full rounded border border-slate-200 bg-white px-2 py-1.5 text-center text-[14px] font-bold underline" placeholder="৭.০ Compliance of Previous External Audit Report">
            <div class="flex flex-wrap items-center justify-center gap-2 pt-1 text-[12px]">
                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.branch_label" class="w-36 rounded border border-slate-200 bg-white px-2 py-1 text-center font-semibold" placeholder="Name of Branch----">
                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.branch" class="min-w-[160px] rounded border border-slate-200 bg-white px-2 py-1 text-center" placeholder="শাখার নাম">
            </div>
        </div>
    <?php else: ?>
        <div style="text-align:center;margin:0 0 3.5mm;">
            <p class="bold finding-heading" style="margin:0 0 2mm;font-size:<?php echo e($titleSize); ?>;font-weight:700;text-decoration:underline;line-height:1.35;"><?php echo \App\Support\BanglaNumerals::highlight($block['title'] ?? '', 'serial'); ?></p>
            <p style="margin:0 0 2.5mm;font-size:<?php echo e($metaSize); ?>;line-height:1.45;font-weight:600;">
                <?php echo e($block['branch_label'] ?? 'Name of Branch----'); ?> <?php echo e($block['branch'] ?? ''); ?>

            </p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <?php if (isset($component)) { $__componentOriginal3931ccc341723360a2655698c41db1b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3931ccc341723360a2655698c41db1b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-excel-paste-zone','data' => ['path' => 'reportBlocks.'.e($blockIndex).'.rows','columns' => array_merge($coreFields, collect(range(0, max(0, $extraCount - 1)))->map(fn ($i) => 'extra.'.$i)->all()),'hint' => 'External audit: Excel থেকে কলামগুলো একই ক্রমে পেস্ট করুন']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-excel-paste-zone'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => 'reportBlocks.'.e($blockIndex).'.rows','columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(array_merge($coreFields, collect(range(0, max(0, $extraCount - 1)))->map(fn ($i) => 'extra.'.$i)->all())),'hint' => 'External audit: Excel থেকে কলামগুলো একই ক্রমে পেস্ট করুন']); ?>
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
        <table class="<?php echo e($tableClass); ?> min-w-full" style="table-layout:fixed;width:100%;">
            <colgroup>
                <col style="width:14%;">
                <col style="width:11%;">
                <col style="width:<?php echo e($extraCount === 0 ? '38%' : '32%'); ?>;">
                <col style="width:22%;">
                <col style="width:15%;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($ei = 0; $ei < $extraCount; $ei++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <col style="width:8%;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                    <col style="width:3%;">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </colgroup>
            <thead>
                <tr class="bg-[#f0e4d4]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hi => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="<?php echo e($cellPad); ?> font-bold text-center align-middle" style="background:#f0e4d4;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <div class="flex items-start gap-1">
                                    <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.headers.<?php echo e($hi); ?>" class="w-full border-0 bg-transparent text-center text-[11px] font-bold">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hi >= 5): ?>
                                        <button type="button" wire:click="removeExternalAuditBlockColumn(<?php echo e($blockIndex); ?>, <?php echo e($hi); ?>)" class="shrink-0 text-rose-600" title="কলাম মুছুন">×</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php else: ?>
                                <?php echo e($label); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <th class="<?php echo e($cellPad); ?>"></th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $coreFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="<?php echo e($cellPad); ?> align-top <?php echo e(in_array($field, ['area_of_observation', 'year_of_reporting', 'internal_index_no'], true) ? 'text-center' : 'text-left'); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($field, ['external_observation', 'compliance'], true)): ?>
                                        <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.<?php echo e($field); ?>" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                                    <?php else: ?>
                                        <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.<?php echo e($field); ?>" class="w-full border-0 bg-sky-50/40 px-1 text-center text-[11px]">
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <span class="whitespace-pre-wrap"><?php echo e($row[$field] ?? ''); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($ei = 0; $ei < $extraCount; $ei++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="<?php echo e($cellPad); ?> align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.extra.<?php echo e($ei); ?>" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                                <?php else: ?>
                                    <span class="whitespace-pre-wrap"><?php echo e($row['extra'][$ei] ?? ''); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($rows) > 1): ?>
                                    <button type="button" wire:click="removeExternalAuditBlockRow(<?php echo e($blockIndex); ?>, <?php echo e($rowIndex); ?>)" class="text-[12px] text-rose-600">×</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-external-audit-block.blade.php ENDPATH**/ ?>