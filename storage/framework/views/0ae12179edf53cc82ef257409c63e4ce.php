
<?php
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $cellPad = $compact ? 'border border-slate-800 px-1 py-1' : 'border border-slate-800 px-1.5 py-1.5';
    $tableClass = $compact
        ? 'a4-table a4-table-compact text-[9.5px]'
        : 'w-full border-collapse text-[11px] leading-snug';
    $hItR1 = array_values((array) ($block['headers_r1'] ?? \App\Support\AuditTableHeaders::defaults()['it_r1']));
    $hItR2 = array_values((array) ($block['headers_r2'] ?? \App\Support\AuditTableHeaders::defaults()['it_r2']));
    $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));
    $rows = array_values((array) ($block['rows'] ?? []));
    $bodySize = $compact ? '9.5px' : '11px';
    $titleSize = $compact ? '12px' : '14px';
    $metaSize = $compact ? '10px' : '12px';
?>

<div class="mt-[3mm] mb-[4mm]" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'it-checklist-'.e($blockIndex).''; ?>wire:key="it-checklist-<?php echo e($blockIndex); ?>" id="<?php echo e(\App\Livewire\MakeAuditReport::sectionAnchorId((string) ($block['serial'] ?? 'it'))); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <p class="text-[12px] font-semibold text-blue-900">আইটি (সফটওয়্যার) চেকলিস্ট</p>
            <button type="button" wire:click="addItChecklistBlockRow(<?php echo e($blockIndex); ?>)" class="rounded bg-blue-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-blue-800">+ সারি</button>
            <button type="button" wire:click="addItChecklistBlockColumn(<?php echo e($blockIndex); ?>)" class="rounded bg-blue-700 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-blue-800">+ কলাম</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'up')" class="ml-auto text-[12px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'down')" class="text-[12px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock(<?php echo e($blockIndex); ?>)" class="text-[12px] text-rose-600 hover:underline">মুছুন</button>
        </div>

        <div class="mb-3 space-y-1.5 rounded border border-slate-300 bg-slate-50/80 p-3 text-center">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.title" class="finding-serial-input w-full rounded border border-slate-200 bg-white px-2 py-1.5 text-center text-[14px] font-bold">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.org_line1" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px]">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.org_line2" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px]">
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.org_line3" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px]">
            <div class="flex flex-wrap items-center justify-center gap-3 pt-1 text-[12px]">
                <span class="font-semibold">কর্মসূচীর নাম :</span>
                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.program" class="min-w-[110px] rounded border border-slate-200 bg-white px-2 py-1 text-center">
                <span class="font-semibold">শাখার নাম :</span>
                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.branch" class="min-w-[150px] rounded border border-slate-200 bg-white px-2 py-1 text-center">
            </div>
            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.instruction" class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-center text-[12px] font-semibold">
        </div>
    <?php else: ?>
        <div style="text-align:center;margin:0 0 3.5mm;">
            <p class="bold finding-heading" style="margin:0 0 1.5mm;font-size:<?php echo e($titleSize); ?>;font-weight:700;line-height:1.35;"><?php echo \App\Support\BanglaNumerals::highlight($block['title'] ?? '', 'serial'); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['org_line1', 'org_line2', 'org_line3']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orgKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) ($block[$orgKey] ?? '')) !== ''): ?>
                    <p style="margin:0;font-size:<?php echo e($metaSize); ?>;line-height:1.45;"><?php echo e($block[$orgKey]); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <p style="margin:2mm 0 1mm;font-size:<?php echo e($metaSize); ?>;line-height:1.45;">
                <span class="font-semibold">কর্মসূচীর নাম :</span> <?php echo e($block['program'] ?? ''); ?>

                &nbsp;&nbsp;&nbsp;
                <span class="font-semibold">শাখার নাম :</span> <?php echo e($block['branch'] ?? ''); ?>

            </p>
            <p class="bold" style="margin:0 0 2.5mm;font-size:<?php echo e($metaSize); ?>;font-weight:700;"><?php echo e($block['instruction'] ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন'); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="overflow-x-auto">
        <table class="<?php echo e($tableClass); ?> it-checklist-table min-w-full" style="table-layout:fixed;width:100%;">
            <colgroup>
                <col style="width:6%;">
                <col style="width:34%;">
                <col style="width:5%;">
                <col style="width:5%;">
                <col style="width:5%;">
                <col style="width:12%;">
                <col style="width:14%;">
                <col style="width:<?php echo e($extraHeaders === [] ? '19%' : '12%'); ?>;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unused): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <col style="width:7%;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                    <col style="width:4%;">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </colgroup>
            <thead>
                <tr class="bg-slate-200">
                    <th class="<?php echo e($cellPad); ?> font-bold text-center align-middle" rowspan="2"><?php echo e($hItR1[0] ?? 'ক্র. নং'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-bold text-center align-middle" rowspan="2"><?php echo e($hItR1[1] ?? 'বিবরণ'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-bold text-center align-middle" colspan="3"><?php echo e($hItR1[2] ?? 'Compliance'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-bold text-center align-middle" rowspan="2"><?php echo e($hItR1[3] ?? 'Action Owner'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-bold text-center align-middle" rowspan="2"><?php echo e($hItR1[4] ?? 'Management Comments'); ?></th>
                    <th class="<?php echo e($cellPad); ?> font-bold text-center align-middle" rowspan="2"><?php echo e($hItR1[5] ?? 'Recommendation'); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ei => $extraLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="<?php echo e($cellPad); ?> font-bold text-center align-middle" rowspan="2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <div class="flex items-start gap-1">
                                    <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.extra_headers.<?php echo e($ei); ?>" class="w-full border-0 bg-transparent text-center text-[11px] font-bold">
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
                        <th class="<?php echo e($cellPad); ?> font-bold text-center align-middle"><?php echo e($label); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $compliance = (string) ($row['compliance'] ?? ''); ?>
                    <tr>
                        <td class="<?php echo e($cellPad); ?> text-center align-middle font-semibold" style="font-size:<?php echo e($bodySize); ?>;"><?php echo e($row['sl_no'] ?? ''); ?></td>
                        <td class="<?php echo e($cellPad); ?> align-top" style="font-size:<?php echo e($bodySize); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable && trim((string) ($row['description'] ?? '')) === ''): ?>
                                <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.description" rows="2" class="w-full border-0 bg-sky-50/50 p-1 text-[11px]" placeholder="নতুন বিবরণ…"></textarea>
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
                                        class="mx-auto flex h-7 w-7 items-center justify-center rounded border text-[14px] font-bold <?php echo e($compliance === $val ? 'border-emerald-700 bg-emerald-100 text-emerald-900' : 'border-slate-300 bg-white text-slate-300 hover:bg-slate-50'); ?>"
                                        title="<?php echo e($lab); ?>"
                                    ><?php echo e($compliance === $val ? '✓' : ''); ?></button>
                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php else: ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-middle"><span class="it-tick"><?php echo $compliance === 'yes' ? '✓' : '&nbsp;'; ?></span></td>
                            <td class="<?php echo e($cellPad); ?> text-center align-middle"><span class="it-tick"><?php echo $compliance === 'no' ? '✓' : '&nbsp;'; ?></span></td>
                            <td class="<?php echo e($cellPad); ?> text-center align-middle"><span class="it-tick"><?php echo $compliance === 'na' ? '✓' : '&nbsp;'; ?></span></td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <td class="<?php echo e($cellPad); ?> align-top" style="font-size:<?php echo e($bodySize); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.action_owner" class="w-full border-0 bg-sky-50/40 px-1 text-[11px]">
                            <?php else: ?>
                                <?php echo e($row['action_owner'] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="<?php echo e($cellPad); ?> align-top" style="font-size:<?php echo e($bodySize); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.management_comments" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                            <?php else: ?>
                                <span class="whitespace-pre-wrap"><?php echo e($row['management_comments'] ?? ''); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="<?php echo e($cellPad); ?> align-top" style="font-size:<?php echo e($bodySize); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.recommendation" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                            <?php else: ?>
                                <span class="whitespace-pre-wrap"><?php echo e($row['recommendation'] ?? ''); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ei => $unused): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="<?php echo e($cellPad); ?> align-top" style="font-size:<?php echo e($bodySize); ?>;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.extra.<?php echo e($ei); ?>" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                                <?php else: ?>
                                    <span class="whitespace-pre-wrap"><?php echo e($row['extra'][$ei] ?? ''); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <td class="<?php echo e($cellPad); ?> text-center align-top">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($rows) > 1): ?>
                                    <button type="button" wire:click="removeItChecklistBlockRow(<?php echo e($blockIndex); ?>, <?php echo e($rowIndex); ?>)" class="text-[12px] text-rose-600">×</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-it-checklist-block.blade.php ENDPATH**/ ?>