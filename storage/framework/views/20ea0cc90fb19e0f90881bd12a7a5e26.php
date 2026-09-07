
<?php
    use App\Support\AuditScoreSheet;
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $rows = array_values((array) ($block['rows'] ?? []));
    $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));
    $extraCount = count($extraHeaders);
    $adjustments = array_values((array) ($block['adjustments'] ?? AuditScoreSheet::defaultAdjustments()));
    $subsequent = array_values((array) ($block['subsequent'] ?? AuditScoreSheet::defaultSubsequent()));
    $summary = AuditScoreSheet::summarize($rows, $adjustments, $subsequent);
    $auditScoreDisplay = $summary['audit_score_display'] !== '' ? $summary['audit_score_display'] : '—';
    $gradeDisplay = $summary['grade'] !== '' ? $summary['grade'] : '—';
    $initialDisplay = AuditScoreSheet::formatPercent($summary['initial']) ?: '—';
    $finalDisplay = AuditScoreSheet::formatPercent($summary['final']) ?: '—';
    $adjustedDisplay = AuditScoreSheet::formatPercent($summary['adjusted']) ?: '—';
    $fs = $compact ? '8.5px' : '10.5px';
    $pad = $compact ? '1px 3px' : '3px 4px';
    $coreColspan = 9 + $extraCount;
    $fullColspan = $coreColspan + ($editable ? 1 : 0);
?>

<div class="mt-[2mm] mb-[4mm] audit-score-sheet" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'audit-score-'.e($blockIndex).''; ?>wire:key="audit-score-<?php echo e($blockIndex); ?>" id="<?php echo e(\App\Livewire\MakeAuditReport::sectionAnchorId((string) ($block['serial'] ?? 'audit-score'))); ?>" style="font-size:<?php echo e($fs); ?>;line-height:1.35;">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-2 flex flex-wrap items-center gap-2" style="font-size:11px;">
            <p class="font-semibold text-indigo-900">Audit Score Sheet</p>
            <button type="button" wire:click="fillAuditScoreBlockFromReport(<?php echo e($blockIndex); ?>)" class="rounded border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">রিপোর্ট থেকে ভরুন</button>
            <button type="button" wire:click="addAuditScoreBlockRow(<?php echo e($blockIndex); ?>)" class="rounded bg-indigo-700 px-2 py-1 text-[11px] font-semibold text-white">+ সারি</button>
            <button type="button" wire:click="addAuditScoreBlockColumn(<?php echo e($blockIndex); ?>)" class="rounded bg-indigo-700 px-2 py-1 text-[11px] font-semibold text-white">+ কলাম</button>
            <button type="button" wire:click="addAuditScoreAdjustmentRow(<?php echo e($blockIndex); ?>)" class="rounded bg-indigo-700 px-2 py-1 text-[11px] font-semibold text-white">+ Adjustment</button>
            <button type="button" wire:click="recalculateAuditScoreBlock(<?php echo e($blockIndex); ?>)" class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-800">হিসাব করুন</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'up')" class="ml-auto text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'down')" class="text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock(<?php echo e($blockIndex); ?>)" class="text-rose-600 hover:underline">মুছুন</button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <table style="width:100%;border-collapse:collapse;margin-bottom:3mm;table-layout:fixed;">
        <tr>
            <td style="width:58%;vertical-align:top;padding:0 <?php echo e($compact ? '2mm' : '3mm'); ?> 0 0;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                    <div style="margin-bottom:2px;"><span style="font-weight:700;">Branch Name &amp; Code:</span>
                        <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.branch_name_code" class="ml-1 rounded border border-slate-200 px-1" style="width:70%;font-size:<?php echo e($fs); ?>;"></div>
                    <div style="margin-bottom:2px;"><span style="font-weight:700;">Branch Category:</span>
                        <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.branch_category" class="ml-1 rounded border border-slate-200 px-1" style="width:55%;font-size:<?php echo e($fs); ?>;"></div>
                    <div><span style="font-weight:700;">Audit period:</span>
                        <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.audit_period" class="ml-1 rounded border border-slate-200 px-1" style="width:65%;font-size:<?php echo e($fs); ?>;"></div>
                <?php else: ?>
                    <p style="margin:0 0 1.5px;"><span style="font-weight:700;">Branch Name &amp; Code:</span> <?php echo e($block['branch_name_code'] ?? ''); ?></p>
                    <p style="margin:0 0 1.5px;"><span style="font-weight:700;">Branch Category:</span> <?php echo e($block['branch_category'] ?? ''); ?></p>
                    <p style="margin:0;"><span style="font-weight:700;">Audit period:</span> <?php echo e($block['audit_period'] ?? ''); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
            <td style="width:42%;vertical-align:top;padding:0;">
                <table style="width:100%;border-collapse:collapse;font-size:<?php echo e($fs); ?>;">
                    <tr>
                        <td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SUMMARY_LABEL); ?>;padding:<?php echo e($pad); ?>;font-weight:700;width:48%;">Audit Score</td>
                        <td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_CALC_GREEN); ?>;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;"><?php echo e($auditScoreDisplay); ?></td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SUMMARY_LABEL); ?>;padding:<?php echo e($pad); ?>;font-weight:700;">Performance Grade</td>
                        <td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_GRADE); ?>;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;"><?php echo e($gradeDisplay); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="overflow-x-auto">
        <table class="audit-score-table" style="width:100%;border-collapse:collapse;table-layout:fixed;font-size:<?php echo e($fs); ?>;">
            <colgroup>
                <col style="width:<?php echo e($extraCount ? '28%' : '32%'); ?>;">
                <col style="width:8%;">
                <col style="width:7%;">
                <col style="width:6%;">
                <col style="width:8%;">
                <col style="width:7%;">
                <col style="width:8%;">
                <col style="width:8%;">
                <col style="width:7%;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unused): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <col style="width:6%;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?><col style="width:3%;"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;vertical-align:middle;">Observation Title</th>
                    <th rowspan="2" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;vertical-align:middle;">Category / Grade</th>
                    <th colspan="3" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;">Sample Score</th>
                    <th rowspan="2" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;vertical-align:middle;">Instance size (D)</th>
                    <th colspan="2" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;">Achieved Score</th>
                    <th rowspan="2" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;vertical-align:middle;">Audit Score<br>G = (F/C)</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ei => $eh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th rowspan="2" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;vertical-align:middle;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <div class="flex items-start gap-0.5">
                                    <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.extra_headers.<?php echo e($ei); ?>" class="w-full border-0 bg-transparent text-center text-white" style="font-size:<?php echo e($fs); ?>;font-weight:700;">
                                    <button type="button" wire:click="removeAuditScoreBlockColumn(<?php echo e($blockIndex); ?>, <?php echo e($ei); ?>)" class="text-rose-200">×</button>
                                </div>
                            <?php else: ?>
                                <?php echo e($eh); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?><th rowspan="2" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER); ?>;"></th><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
                <tr>
                    <th style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER_SUB); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;">Sample size (A)</th>
                    <th style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER_SUB); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;">Risk weight (B)</th>
                    <th style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER_SUB); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;">Risk weighted score<br>C = (A*B)</th>
                    <th style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER_SUB); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;">Samples not reported<br>E = (A-D)</th>
                    <th style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_HEADER_SUB); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;">Risk weighted score<br>F = (E*B)</th>
                </tr>
                <tr>
                    <th colspan="<?php echo e($fullColspan); ?>" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SECTION); ?>;padding:<?php echo e($pad); ?>;text-align:left;font-weight:700;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.section_label" class="w-full border-0 bg-transparent font-bold" style="font-size:<?php echo e($fs); ?>;">
                        <?php else: ?>
                            <?php echo e($block['section_label'] ?? 'Sample-based observations'); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $computed = AuditScoreSheet::computeRow(is_array($row) ? $row : []);
                        $cat = (string) ($computed['category'] ?? '');
                        $calcBg = AuditScoreSheet::calcCellBg($cat);
                        $gBg = AuditScoreSheet::gCellBg($cat);
                        $inBg = AuditScoreSheet::inputCellBg($cat);
                    ?>
                    <tr>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;vertical-align:top;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <textarea wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.title" rows="2" class="w-full border-0 bg-transparent p-0" style="font-size:<?php echo e($fs); ?>;line-height:1.35;"></textarea>
                            <?php else: ?>
                                <span style="white-space:pre-wrap;"><?php echo e($computed['title'] ?? ''); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;vertical-align:middle;background:<?php echo e($inBg); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <select wire:change="setAuditScoreCategory(<?php echo e($blockIndex); ?>, <?php echo e($rowIndex); ?>, $event.target.value)" class="w-full border-0 bg-transparent text-center" style="font-size:<?php echo e($fs); ?>;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = AuditScoreSheet::categoryOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($opt); ?>" <?php if($cat === $opt): echo 'selected'; endif; ?>><?php echo e($opt); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            <?php else: ?>
                                <?php echo e($cat); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;vertical-align:middle;background:<?php echo e($inBg); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.sample_size" class="w-full border-0 bg-transparent text-center" style="font-size:<?php echo e($fs); ?>;">
                            <?php else: ?>
                                <?php echo e($computed['sample_size'] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;vertical-align:middle;background:<?php echo e($inBg); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.risk_weight" class="w-full border-0 bg-transparent text-center" style="font-size:<?php echo e($fs); ?>;">
                            <?php else: ?>
                                <?php echo e($computed['risk_weight'] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;vertical-align:middle;background:<?php echo e($calcBg); ?>;font-weight:700;"><?php echo e($computed['risk_weighted_c'] ?? ''); ?></td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;vertical-align:middle;background:<?php echo e($inBg); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.instance_size" class="w-full border-0 bg-transparent text-center" style="font-size:<?php echo e($fs); ?>;">
                            <?php else: ?>
                                <?php echo e($computed['instance_size'] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;vertical-align:middle;background:<?php echo e($calcBg); ?>;font-weight:700;"><?php echo e($computed['samples_not_reported_e'] ?? ''); ?></td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;vertical-align:middle;background:<?php echo e($calcBg); ?>;font-weight:700;"><?php echo e($computed['risk_weighted_f'] ?? ''); ?></td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;vertical-align:middle;background:<?php echo e($gBg); ?>;font-weight:700;"><?php echo e($computed['audit_score_g'] ?? ''); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ei => $unused): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;vertical-align:top;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rowIndex); ?>.extra.<?php echo e($ei); ?>" class="w-full border-0 bg-transparent text-center" style="font-size:<?php echo e($fs); ?>;">
                                <?php else: ?>
                                    <?php echo e($row['extra'][$ei] ?? ''); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($rows) > 1): ?>
                                    <button type="button" wire:click="removeAuditScoreBlockRow(<?php echo e($blockIndex); ?>, <?php echo e($rowIndex); ?>)" class="text-rose-600">×</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                <tr>
                    <td colspan="<?php echo e(8 + $extraCount); ?>" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SCORE_ROW); ?>;color:#fff;padding:<?php echo e($pad); ?>;font-weight:700;">Initial Audit Score</td>
                    <td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SCORE_ROW); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;"><?php echo e($initialDisplay); ?></td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?><td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SCORE_ROW); ?>;"></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
                <tr>
                    <td colspan="<?php echo e($fullColspan); ?>" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SECTION); ?>;padding:<?php echo e($pad); ?>;font-weight:700;">Other Considerations:</td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $adjustments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adjIndex => $adj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td colspan="<?php echo e(8 + $extraCount); ?>" style="border:1px solid #222;padding:<?php echo e($pad); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.adjustments.<?php echo e($adjIndex); ?>.label" class="w-full border-0 bg-transparent" style="font-size:<?php echo e($fs); ?>;">
                            <?php else: ?>
                                <?php echo e($adj['label'] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.adjustments.<?php echo e($adjIndex); ?>.value" class="w-full border-0 bg-transparent text-center" style="font-size:<?php echo e($fs); ?>;" placeholder="-5">
                            <?php else: ?>
                                <?php echo e(AuditScoreSheet::formatPercent(AuditScoreSheet::parseNumber($adj['value'] ?? null))); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;">
                                <button type="button" wire:click="removeAuditScoreAdjustmentRow(<?php echo e($blockIndex); ?>, <?php echo e($adjIndex); ?>)" class="text-rose-600">×</button>
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="<?php echo e(8 + $extraCount); ?>" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SCORE_ROW); ?>;color:#fff;padding:<?php echo e($pad); ?>;font-weight:700;">Final Audit Score</td>
                    <td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SCORE_ROW); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;"><?php echo e($finalDisplay); ?></td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?><td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SCORE_ROW); ?>;"></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
                <tr>
                    <td colspan="<?php echo e($fullColspan); ?>" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SECTION); ?>;padding:<?php echo e($pad); ?>;font-weight:700;">Other Relevant Considerations:</td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subsequent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subIndex => $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td colspan="<?php echo e(8 + $extraCount); ?>" style="border:1px solid #222;padding:<?php echo e($pad); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.subsequent.<?php echo e($subIndex); ?>.label" class="w-full border-0 bg-transparent" style="font-size:<?php echo e($fs); ?>;">
                            <?php else: ?>
                                <?php echo e($sub['label'] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="border:1px solid #222;padding:<?php echo e($pad); ?>;text-align:center;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <input type="text" wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.subsequent.<?php echo e($subIndex); ?>.value" class="w-full border-0 bg-transparent text-center" style="font-size:<?php echo e($fs); ?>;" placeholder="0">
                            <?php else: ?>
                                <?php echo e(AuditScoreSheet::formatPercent(AuditScoreSheet::parseNumber($sub['value'] ?? null))); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?><td style="border:1px solid #222;padding:<?php echo e($pad); ?>;"></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="<?php echo e(8 + $extraCount); ?>" style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SCORE_ROW); ?>;color:#fff;padding:<?php echo e($pad); ?>;font-weight:700;">Adjusted Audit Score</td>
                    <td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SCORE_ROW); ?>;color:#fff;padding:<?php echo e($pad); ?>;text-align:center;font-weight:700;"><?php echo e($adjustedDisplay); ?></td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?><td style="border:1px solid #222;background:<?php echo e(AuditScoreSheet::COLOR_SCORE_ROW); ?>;"></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-audit-score-block.blade.php ENDPATH**/ ?>