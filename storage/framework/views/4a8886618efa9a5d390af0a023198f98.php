<?php
    use App\Livewire\MakeAuditReport;
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $tableClass = $compact ? 'a4-table a4-table-compact text-[9.5px]' : 'a4-table text-[10.5px]';
    $obsTableClass = $compact ? 'a4-table a4-table-compact text-[9px]' : 'a4-table text-[10px]';

    $blocks = $reportBlocks ?? [];
    if ($blocks === []) {
        $sections = $reportSections ?? [];
        if ($sections === [] && ! empty($financialFindings)) {
            $sections = [[
                'serial' => '১.০',
                'title' => $financial_section_title ?? '১.০ আর্থিক নিরীক্ষা',
                'findings' => $financialFindings,
            ]];
        }
        foreach ($sections as $section) {
            $blocks[] = [
                'type' => 'section',
                'serial' => $section['serial'] ?? '১.০',
                'title' => $section['title'] ?? '',
            ];
            foreach (($section['findings'] ?? []) as $finding) {
                $blocks[] = array_merge(['type' => 'finding'], is_array($finding) ? $finding : []);
            }
        }
        $blocks[] = ['type' => 'criteria'];
        $blocks[] = ['type' => 'observation'];
        $blocks[] = ['type' => 'stats'];
        $blocks[] = ['type' => 'stats'];
    }
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bIndex => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php $type = $block['type'] ?? ''; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <?php echo $__env->make('livewire.partials.audit-block-insert-menu', ['insertIndex' => $bIndex], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'section'): ?>
        <?php $sectionAnchor = MakeAuditReport::sectionAnchorId($block['serial'] ?? ($block['title'] ?? '')); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sectionAnchor !== ''): ?>
            <a id="<?php echo e($sectionAnchor); ?>" name="<?php echo e($sectionAnchor); ?>"></a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <div class="mb-[3mm] flex flex-wrap items-center gap-2 <?php echo e($bIndex > 0 ? 'mt-[2mm]' : ''); ?>" <?php if($sectionAnchor !== ''): ?> data-outline-id="<?php echo e($sectionAnchor); ?>" <?php endif; ?>>
                <input
                    type="text"
                    wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.serial"
                    class="finding-serial-input h-8 w-[72px] rounded border border-slate-200 bg-sky-50/40 px-1 text-center text-[12px] font-bold"
                    title="বিভাগ ক্রমিক"
                >
                <input
                    type="text"
                    wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.title"
                    class="finding-serial-input min-w-[220px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                    placeholder="বিভাগের শিরোনাম"
                >
                <div class="ml-auto flex flex-wrap items-center gap-1">
                    <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'up')" class="h-7 rounded border border-slate-200 px-2 text-[11px] text-slate-600 hover:bg-slate-50" title="উপরে">↑</button>
                    <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'down')" class="h-7 rounded border border-slate-200 px-2 text-[11px] text-slate-600 hover:bg-slate-50" title="নিচে">↓</button>
                    <button type="button" wire:click="removeBlock(<?php echo e($bIndex); ?>)" class="h-7 rounded border border-rose-200 px-2 text-[11px] text-rose-600 hover:bg-rose-50">
                        বিভাগ মুছুন
                    </button>
                </div>
            </div>
        <?php else: ?>
            <p class="mb-[2mm] <?php echo e($bIndex > 0 ? 'mt-[4mm]' : ''); ?> font-bold finding-heading"><?php echo \App\Support\BanglaNumerals::highlight(($block['title'] ?? $block['serial'] ?? ''), 'serial'); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php elseif($type === 'finding'): ?>
        <?php $anchor = MakeAuditReport::findingAnchorId($block['serial'] ?? ''); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anchor !== ''): ?>
            <a id="<?php echo e($anchor); ?>" name="<?php echo e($anchor); ?>"></a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <table class="<?php echo e($tableClass); ?> mb-[2mm]">
            <tbody>
                <tr>
                    <td style="width:9%;" class="align-top text-center font-bold finding-serial-cell">
                        <?php echo $__env->make('livewire.partials.audit-finding-serial-cell', [
                            'editable' => $editable,
                            'wireModel' => $editable ? 'reportBlocks.'.$bIndex.'.serial' : null,
                            'value' => $block['serial'] ?? '',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                    <td style="width:11%;" class="align-top text-center font-bold">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <input type="text" wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.title" class="w-full border-0 bg-sky-50/40 text-center font-bold" placeholder="শিরোনাম">
                        <?php else: ?>
                            <?php echo e($block['title'] ?? 'শিরোনাম'); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="align-top">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <?php echo $__env->make('livewire.partials.audit-indicator-combobox', [
                                'index' => $bIndex,
                                'value' => $block['body'] ?? '',
                                'indicators' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
                                'collection' => 'reportBlocks',
                                'wireKey' => 'blk-ind-'.$bIndex.'-'.md5((string) ($block['body'] ?? '')),
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px]">
                                <span class="font-semibold">টাকার পরিমাণ:</span>
                                <input type="text" wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.amount" class="inline-input min-w-[100px]">
                            </div>
                        <?php else: ?>
                            <p class="m-0 whitespace-pre-wrap text-justify leading-[1.45]"><?php echo e($block['body'] ?? ''); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($block['amount'] ?? '') !== ''): ?>
                                <p class="mt-[1mm] m-0"><span class="font-semibold">টাকার পরিমাণ:</span> <?php echo e($block['amount']); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td style="width:17%;" class="align-top p-0">
                        <?php echo $__env->make('livewire.partials.audit-rating-box', [
                            'rating' => $block['rating'] ?? '',
                            'editable' => $editable,
                            'wireModel' => $editable ? 'reportBlocks.'.$bIndex.'.rating' : null,
                            'findingRatings' => $findingRatings ?? [],
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <div class="mb-[3mm] flex flex-wrap items-center justify-end gap-2">
                <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'up')" class="text-[11px] text-slate-600 hover:underline">↑ উপরে</button>
                <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'down')" class="text-[11px] text-slate-600 hover:underline">↓ নিচে</button>
                <button
                    type="button"
                    wire:click="removeBlock(<?php echo e($bIndex); ?>)"
                    class="text-[11px] text-rose-600 hover:underline"
                >শিরোনাম মুছুন</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php elseif($type === 'criteria'): ?>
        <div class="mt-[3mm]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <div class="mb-1 flex flex-wrap items-center gap-2">
                    <input
                        type="text"
                        wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.label"
                        class="min-w-[200px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                        placeholder="প্রচলিত নিয়ম (Criteria):"
                    >
                    <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'up')" class="text-[11px] text-slate-600 hover:underline">↑</button>
                    <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
                    <button type="button" wire:click="removeBlock(<?php echo e($bIndex); ?>)" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
                </div>
                <textarea
                    wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.body"
                    rows="4"
                    class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px] leading-relaxed"
                    placeholder="প্রচলিত নিয়ম লিখুন…"
                ></textarea>
            <?php else: ?>
                <p class="mb-[1mm] font-bold"><?php echo e($block['label'] ?? 'প্রচলিত নিয়ম (Criteria):'); ?></p>
                <p class="m-0 text-justify leading-[1.45]"><?php echo e($block['body'] ?? $financial_criteria); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    <?php elseif($type === 'observation'): ?>
        <div class="mt-[3mm]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <div class="mb-1 flex flex-wrap items-center gap-2">
                    <input
                        type="text"
                        wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.label"
                        class="min-w-[200px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                        placeholder="পর্যবেক্ষণ (Observation) :"
                    >
                    <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'up')" class="text-[11px] text-slate-600 hover:underline">↑</button>
                    <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
                    <button type="button" wire:click="removeBlock(<?php echo e($bIndex); ?>)" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
                </div>
                <textarea
                    wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.body"
                    rows="3"
                    class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px] leading-relaxed"
                    placeholder="পর্যবেক্ষণ লিখুন…"
                ></textarea>
            <?php else: ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($block['label'] ?? '') !== ''): ?>
                    <p class="mb-[1mm] font-bold"><?php echo e($block['label']); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($block['body'] ?? '') !== ''): ?>
                    <p class="m-0 whitespace-pre-wrap text-justify leading-[1.45]"><?php echo e($block['body']); ?></p>
                <?php else: ?>
                    <p class="m-0 border-b border-dotted border-black">&nbsp;</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    <?php elseif(in_array($type, ['stats', 'vat', 'tax'], true)): ?>
        <?php
            $obsHeading = (string) ($block['heading'] ?? 'Report Rating Box:');
            if ($obsHeading === 'ভ্যাট সংক্রান্ত:' || $obsHeading === 'ট্যাক্স সংক্রান্ত:' || $obsHeading === 'সারণী:' || $obsHeading === 'নতুন সারণী:') {
                $obsHeading = 'Report Rating Box:';
            }
            $obsRows = array_values((array) ($block['rows'] ?? (
                $type === 'tax' ? ($taxObservationRows ?? []) : ($vatObservationRows ?? [])
            )));
            if ($obsRows === []) {
                $obsRows = [['total_population' => '', 'sample_size' => '', 'instances_found' => '', 'percentage' => '']];
            }
            $linkedSerial = trim((string) ($block['linked_finding_serial'] ?? ''));
            $linkedTitle = trim((string) ($block['linked_finding_title'] ?? ''));
            $linkedCode = trim((string) ($block['linked_indicator_code'] ?? ''));
            $linkedIndicatorId = (int) ($block['linked_indicator_id'] ?? 0);
            $hasMatrixLink = $linkedIndicatorId > 0;
        ?>
        <div class="mt-[3mm]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <div class="mb-1 flex flex-wrap items-center gap-2">
                    <input
                        type="text"
                        wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.heading"
                        class="min-w-[200px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                        placeholder="Report Rating Box:"
                    >
                    <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'up')" class="text-[11px] text-slate-600 hover:underline">↑</button>
                    <button type="button" wire:click="moveBlock(<?php echo e($bIndex); ?>, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
                    <button type="button" wire:click="removeBlock(<?php echo e($bIndex); ?>)" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
                </div>

                <div class="mb-2 rounded-lg border <?php echo e($hasMatrixLink ? 'border-emerald-200 bg-emerald-50/70' : 'border-amber-200 bg-amber-50/80'); ?> px-2.5 py-2">
                    <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[10px] font-bold uppercase tracking-wide <?php echo e($hasMatrixLink ? 'text-emerald-800' : 'text-amber-800'); ?>">
                            <?php echo e($hasMatrixLink ? '✓ Matrix indicator confirmed' : '⚠ Matrix indicator missing'); ?>

                        </p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasMatrixLink && $linkedCode !== ''): ?>
                            <span class="rounded bg-white/80 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-emerald-800"><?php echo e($linkedCode); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasMatrixLink): ?>
                        <p class="mb-1.5 text-[11px] font-semibold leading-snug text-emerald-950">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($linkedSerial !== ''): ?>
                                <span class="text-emerald-700"><?php echo e($linkedSerial); ?></span> ·
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php echo e($linkedTitle !== '' ? $linkedTitle : 'Selected indicator'); ?>

                        </p>
                        <p class="mb-1.5 text-[10px] text-emerald-800/80">এই বক্সের Sample / Instances / Amount এই indicator-এর Findings Matrix সারিতে যাবে (শাখা × মাস)।</p>
                    <?php else: ?>
                        <p class="mb-1.5 text-[10px] text-amber-900">নিচ থেকে indicator বেছে নিন — না হলে Matrix-এ ডেটা যাবে না।</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <label class="mb-0.5 block text-[10px] font-semibold text-slate-600">এই Rating Box কোন indicator-এর?</label>
                    <?php echo $__env->make('livewire.partials.audit-indicator-combobox', [
                        'index' => $bIndex,
                        'value' => $hasMatrixLink ? $linkedTitle : '',
                        'indicators' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
                        'collection' => 'statsBlocks',
                        'wireKey' => 'stats-ind-'.$bIndex.'-'.(int) $linkedIndicatorId,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php elseif($obsHeading !== ''): ?>
                <p class="mb-[1mm] font-bold"><?php echo e($obsHeading); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasMatrixLink): ?>
                    <p class="mb-[1mm] text-[10px] text-slate-600">
                        Indicator:
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($linkedSerial !== ''): ?> <?php echo e($linkedSerial); ?> · <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php echo e($linkedTitle); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($linkedCode !== ''): ?> (<?php echo e($linkedCode); ?>) <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <table class="<?php echo e($obsTableClass); ?> mb-[2mm]">
                <?php echo $__env->make('livewire.partials.audit-stats-thead', [
                    'editable' => $editable,
                    'cellPad' => '',
                    'variant' => 'stats',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $obsRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['total_population', 'sample_size', 'instances_found', 'percentage']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                        <input type="text" wire:model.live="reportBlocks.<?php echo e($bIndex); ?>.rows.<?php echo e($rowIndex); ?>.<?php echo e($field); ?>" class="w-full border-0 bg-transparent text-center text-[11px]">
                                    <?php else: ?>
                                        <?php echo e($row[$field] ?? ''); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($obsRows) > 1): ?>
                                        <button type="button" wire:click="removeObservationBlockRow(<?php echo e($bIndex); ?>, <?php echo e($rowIndex); ?>)" class="text-[10px] text-rose-600">×</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <button type="button" wire:click="addObservationBlockRow(<?php echo e($bIndex); ?>)" class="mb-[2mm] text-[11px] font-medium text-[#2b579a]">+ সারি যোগ</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    <?php elseif($type === 'custom_table'): ?>
        <?php echo $__env->make('livewire.partials.audit-custom-table-block', [
            'editable' => $editable,
            'compact' => $compact,
            'blockIndex' => $bIndex,
            'block' => $block,
            'customTableEditorIndex' => $customTableEditorIndex ?? null,
            'customTableSizeCols' => $customTableSizeCols ?? null,
            'customTableSizeRows' => $customTableSizeRows ?? null,
            'customTableSelR' => $customTableSelR ?? null,
            'customTableSelC' => $customTableSelC ?? null,
            'customTableMergeRows' => $customTableMergeRows ?? 2,
            'customTableMergeCols' => $customTableMergeCols ?? 1,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php elseif($type === 'jobab_table'): ?>
        <?php echo $__env->make('livewire.partials.audit-jobab-table-block', [
            'editable' => $editable,
            'compact' => $compact,
            'blockIndex' => $bIndex,
            'block' => $block,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
    <?php echo $__env->make('livewire.partials.audit-block-insert-menu', ['insertIndex' => count($blocks), 'end' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-financial-audit-section.blade.php ENDPATH**/ ?>