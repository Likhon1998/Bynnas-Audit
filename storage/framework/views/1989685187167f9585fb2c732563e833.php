
<?php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $cellPad = $compact ? '' : 'border border-slate-800 px-1 py-0.5';
    $tableClass = $compact ? 'a4-table a4-table-compact text-[7.5px]' : 'w-full border-collapse text-[9px]';
    $hItR1 = array_values((array) ($block['headers_r1'] ?? \App\Support\AuditTableHeaders::defaults()['it_r1']));
    $hItR2 = array_values((array) ($block['headers_r2'] ?? \App\Support\AuditTableHeaders::defaults()['it_r2']));
    $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));
    $rows = array_values((array) ($block['rows'] ?? []));
?>

<div class="mt-[3mm] mb-[4mm]" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'it-checklist-'.e($blockIndex).''; ?>wire:key="it-checklist-<?php echo e($blockIndex); ?>" id="<?php echo e(\App\Livewire\MakeAuditReport::sectionAnchorId((string) ($block['serial'] ?? 'it'))); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <p class="text-[11px] font-semibold text-blue-900">আইটি (সফটওয়্যার) চেকলিস্ট</p>
            <button type="button" wire:click="addItChecklistBlockRow(<?php echo e($blockIndex); ?>)" class="rounded bg-blue-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-blue-800">+ সারি</button>
            <button type="button" wire:click="addItChecklistBlockColumn(<?php echo e($blockIndex); ?>)" class="rounded bg-blue-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-blue-800">+ কলাম</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'up')" class="ml-auto text-[11px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock(<?php echo e($blockIndex); ?>)" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
        </div>

        <div class="mb-3 space-y-1.5 rounded border border-blue-100 bg-blue-50/30 p-3 text-center">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.title" class="finding-serial-input w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px] font-bold">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.org_line1" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[11px]">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.org_line2" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[11px]">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.org_line3" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[11px]">
            <div class="flex flex-wrap items-center justify-center gap-3 pt-1 text-[11px]">
                <span class="font-semibold">কর্মসূচীর নাম :</span>
                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.program" class="min-w-[100px] rounded border border-slate-200 bg-white px-2 py-1 text-center">
                <span class="font-semibold">শাখার নাম :</span>
                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.branch" class="min-w-[140px] rounded border border-slate-200 bg-white px-2 py-1 text-center">
            </div>
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.instruction" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[11px] font-semibold">
            <p class="text-[10px] text-slate-500">বিবরণ টেমপ্লেট — একই সব রিপোর্টে; Yes/No/N/A, মন্তব্য ও সুপারিশ পরিবর্তনযোগ্য</p>
        </div>
    <?php else: ?>
        <div style="text-align:center;margin:0 0 3mm;">
            <p class="bold finding-heading" style="margin:0 0 1mm;font-size:<?php echo e($compact ? '10px' : '12px'); ?>;font-weight:700;"><?php echo \App\Support\BanglaNumerals::highlight($block['title'] ?? '', 'serial'); ?></p>
            <p style="margin:0;font-size:<?php echo e($compact ? '8px' : '11px'); ?>;"><?php echo e($block['org_line1'] ?? ''); ?></p>
            <p style="margin:0;font-size:<?php echo e($compact ? '8px' : '11px'); ?>;"><?php echo e($block['org_line2'] ?? ''); ?></p>
            <p style="margin:0 0 1.5mm;font-size:<?php echo e($compact ? '8px' : '11px'); ?>;"><?php echo e($block['org_line3'] ?? ''); ?></p>
            <p style="margin:0 0 1mm;font-size:<?php echo e($compact ? '8px' : '11px'); ?>;">
                <span class="font-semibold">কর্মসূচীর নাম :</span> <?php echo e($block['program'] ?? ''); ?>

                &nbsp;&nbsp;
                <span class="font-semibold">শাখার নাম :</span> <?php echo e($block['branch'] ?? ''); ?>

            </p>
            <p class="bold" style="margin:0 0 2mm;font-size:<?php echo e($compact ? '8px' : '11px'); ?>;font-weight:700;"><?php echo e($block['instruction'] ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন'); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="overflow-x-auto">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <?php if (isset($component)) { $__componentOriginal3931ccc341723360a2655698c41db1b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3931ccc341723360a2655698c41db1b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-excel-paste-zone','data' => ['path' => 'reportBlocks.'.e($blockIndex).'.rows','columns' => array_merge(
                    ['sl_no', 'description', 'compliance', 'action_owner', 'management_comments', 'recommendation'],
                    collect(range(0, max(0, count($extraHeaders) - 1)))->map(fn ($i) => 'extra.'.$i)->all()
                ),'hint' => 'IT checklist: Compliance = Yes/No/N/A']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-excel-paste-zone'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => 'reportBlocks.'.e($blockIndex).'.rows','columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(array_merge(
                    ['sl_no', 'description', 'compliance', 'action_owner', 'management_comments', 'recommendation'],
                    collect(range(0, max(0, count($extraHeaders) - 1)))->map(fn ($i) => 'extra.'.$i)->all()
                )),'hint' => 'IT checklist: Compliance = Yes/No/N/A']); ?>
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

        <table class="<?php echo e($tableClass); ?> min-w-full">
            <thead>
                <tr class="bg-slate-100">
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center" rowspan="2"><?php echo e($hItR1[0] ?? 'ক্র. নং'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center" rowspan="2"><?php echo e($hItR1[1] ?? 'বিবরণ'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center" colspan="3"><?php echo e($hItR1[2] ?? 'Compliance'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center" rowspan="2"><?php echo e($hItR1[3] ?? 'Action Owner'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center" rowspan="2"><?php echo e($hItR1[4] ?? 'Management Comments'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-semibold text-center" rowspan="2"><?php echo e($hItR1[5] ?? 'Recommendation'); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ei => $extraLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="<?php echo e($cellPad); ?> font-semibold text-center" rowspan="2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <div class="flex items-start gap-1">
                                    <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.extra_headers.<?php echo e($ei); ?>" class="w-full border-0 bg-transparent text-center text-[9px] font-semibold">
                                    <button type="button" wire:click="removeItChecklistBlockColumn(<?php echo e($blockIndex); ?>, <?php echo e($ei); ?>)" class="text-rose-600">×</button>
                                </div>
                            <?php else: ?>
                                <?php echo e($extraLabel); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <th class="<?php echo e($cellPad); ?>" rowspan="2"></th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
                <tr class="bg-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hItR2; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="<?php echo e($cellPad); ?> font-semibold text-center"><?php echo e($label); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $compliance = (string) ($row['compliance'] ?? ''); ?>
                    <tr>
                        <td class="<?php echo e($cellPad); ?> text-center align-top"><?php echo e($row['sl_no'] ?? ''); ?></td>
                        <td class="<?php echo e($cellPad); ?> align-top">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable && trim((string) ($row['description'] ?? '')) === ''): ?>
                                <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.description" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]" placeholder="নতুন বিবরণ…"></textarea>
                            <?php else: ?>
                                <span class="whitespace-pre-wrap"><?php echo e($row['description'] ?? ''); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['yes' => 'Yes', 'no' => 'No', 'na' => 'N/A']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td class="<?php echo e($cellPad); ?> text-center align-middle">
                                    <button
                                        type="button"
                                        wire:click="setItChecklistCompliance(<?php echo e($blockIndex); ?>, <?php echo e($rowIndex); ?>, '<?php echo e($val); ?>')"
                                        class="mx-auto flex h-6 w-6 items-center justify-center rounded border text-[12px] <?php echo e($compliance === $val ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-white text-slate-400 hover:bg-slate-50'); ?>"
                                        title="<?php echo e($lab); ?>"
                                    ><?php echo e($compliance === $val ? '✓' : ''); ?></button>
                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php else: ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-top"><?php echo e($compliance === 'yes' ? '✓' : ''); ?></td>
                            <td class="<?php echo e($cellPad); ?> text-center align-top"><?php echo e($compliance === 'no' ? '✓' : ''); ?></td>
                            <td class="<?php echo e($cellPad); ?> text-center align-top"><?php echo e($compliance === 'na' ? '✓' : ''); ?></td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <td class="<?php echo e($cellPad); ?> align-top">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.action_owner" class="w-full border-0 bg-sky-50/50 px-0.5 text-[8px]">
                            <?php else: ?>
                                <?php echo e($row['action_owner'] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="<?php echo e($cellPad); ?> align-top">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.management_comments" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                            <?php else: ?>
                                <span class="whitespace-pre-wrap"><?php echo e($row['management_comments'] ?? ''); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="<?php echo e($cellPad); ?> align-top">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.recommendation" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                            <?php else: ?>
                                <span class="whitespace-pre-wrap"><?php echo e($row['recommendation'] ?? ''); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ei => $unused): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="<?php echo e($cellPad); ?> align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.extra.<?php echo e($ei); ?>" rows="2" class="w-full border-0 bg-sky-50/50 p-0.5 text-[8px]"></textarea>
                                <?php else: ?>
                                    <span class="whitespace-pre-wrap"><?php echo e($row['extra'][$ei] ?? ''); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($rows) > 1): ?>
                                    <button type="button" wire:click="removeItChecklistBlockRow(<?php echo e($blockIndex); ?>, <?php echo e($rowIndex); ?>)" class="text-[10px] text-rose-600">×</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-it-checklist-block.blade.php ENDPATH**/ ?>