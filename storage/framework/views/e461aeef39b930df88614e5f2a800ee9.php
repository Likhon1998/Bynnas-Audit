
<?php
    $editable = $editable ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $rows = array_values((array) ($block['rows'] ?? []));
    if ($rows === []) {
        $rows = [
            ['cells' => ['শাখা ব্যবস্থাপকের জবাব', '']],
            ['cells' => ['সমস্যা সমাধানের ক্ষেত্রে দায়িত্বপ্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ', '']],
            ['cells' => ['সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)', '']],
        ];
    }
    $colCount = max(2, count($rows[0]['cells'] ?? []));
    foreach ($rows as $ri => $row) {
        $cells = array_values((array) ($row['cells'] ?? []));
        while (count($cells) < $colCount) {
            $cells[] = '';
        }
        $rows[$ri]['cells'] = array_slice($cells, 0, $colCount);
    }
    $tableClass = ($compact ?? false) ? 'a4-table a4-table-compact text-[9.5px]' : 'a4-table text-[10.5px]';
    $labelWidth = $colCount === 2 ? '38%' : null;
?>

<div class="mt-[3mm]" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'jobab-'.e($blockIndex).''; ?>wire:key="jobab-<?php echo e($blockIndex); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-1 flex flex-wrap items-center gap-2">
            <p class="text-[11px] font-semibold text-slate-700">জবাব টেবিল</p>
            <button type="button" wire:click="addJobabRow(<?php echo e($blockIndex); ?>)" class="rounded bg-sky-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-sky-800">+ সারি</button>
            <button type="button" wire:click="addJobabColumn(<?php echo e($blockIndex); ?>)" class="rounded bg-sky-700 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-sky-800">+ কলাম</button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($colCount > 1): ?>
                <button type="button" wire:click="removeJobabColumn(<?php echo e($blockIndex); ?>)" class="rounded border border-slate-300 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-700 hover:bg-slate-50">কলাম −</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($rows) > 1): ?>
                <button type="button" wire:click="removeJobabRow(<?php echo e($blockIndex); ?>, <?php echo e(count($rows) - 1); ?>)" class="rounded border border-rose-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-rose-700 hover:bg-rose-50">শেষ সারি −</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'up')" class="ml-auto text-[11px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock(<?php echo e($blockIndex); ?>)" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="overflow-x-auto">
        <table class="<?php echo e($tableClass); ?> mb-[2mm] w-full border-collapse">
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $row['cells']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cIndex => $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td
                                class="border border-slate-700 px-1.5 py-1 align-top <?php echo e($cIndex === 0 ? 'font-semibold bg-slate-50' : ''); ?>"
                                <?php if($cIndex === 0 && $labelWidth): ?> style="width: <?php echo e($labelWidth); ?>;" <?php endif; ?>
                            >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                    <textarea
                                        wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rIndex); ?>.cells.<?php echo e($cIndex); ?>"
                                        rows="<?php echo e($cIndex === 0 ? 2 : 3); ?>"
                                        class="w-full resize-y border-0 bg-transparent text-[11px] leading-snug <?php echo e($cIndex === 0 ? 'font-semibold' : ''); ?>"
                                    ></textarea>
                                <?php else: ?>
                                    <span class="whitespace-pre-wrap"><?php echo e($cell); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-jobab-table-block.blade.php ENDPATH**/ ?>