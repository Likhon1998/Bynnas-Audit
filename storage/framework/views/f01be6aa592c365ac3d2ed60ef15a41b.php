
<?php
    use App\Support\CustomTableSchema;
    $editable = $editable ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $table = CustomTableSchema::normalize(is_array($block ?? []) ? $block : []);
    $leafCount = CustomTableSchema::leafCount($table['columns']);
    $editorOpen = $editable && isset($customTableEditorIndex) && (int) $customTableEditorIndex === $blockIndex;
?>

<div class="mt-[3mm]" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'custom-table-'.e($blockIndex).''; ?>wire:key="custom-table-<?php echo e($blockIndex); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-2 flex flex-wrap items-center gap-2">
            <input
                type="text"
                wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.title"
                class="min-w-[220px] flex-1 rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[12px] font-bold"
                placeholder="টেবিল শিরোনাম"
            >
            <button
                type="button"
                wire:click="openCustomTableEditor(<?php echo e($blockIndex); ?>)"
                class="rounded bg-violet-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-violet-700"
            >Customize Table</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'up')" class="text-[11px] text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock(<?php echo e($blockIndex); ?>, 'down')" class="text-[11px] text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock(<?php echo e($blockIndex); ?>)" class="text-[11px] text-rose-600 hover:underline">মুছুন</button>
        </div>
    <?php else: ?>
        <p class="mb-[1mm] font-bold"><?php echo e($table['title']); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="overflow-x-auto">
        <?php echo $__env->make('livewire.partials.audit-custom-table-render', [
            'block' => $table,
            'blockIndex' => $blockIndex,
            'editable' => $editable,
            'selectable' => false,
            'compact' => $compact ?? false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mt-1 flex flex-wrap gap-2 text-right">
            <button type="button" wire:click="addCustomTableRow(<?php echo e($blockIndex); ?>)" class="text-[10px] text-violet-700 hover:underline">+ সারি</button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $table['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rIndex === count($table['rows']) - 1): ?>
                    <button type="button" wire:click="toggleCustomTableTotalRow(<?php echo e($blockIndex); ?>, <?php echo e($rIndex); ?>)" class="text-[10px] text-slate-500 hover:underline">
                        <?php echo e(($row['is_total'] ?? false) ? 'মোট সারি বন্ধ' : 'মোট সারি'); ?>

                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($table['rows']) > 1): ?>
                        <button type="button" wire:click="removeCustomTableRow(<?php echo e($blockIndex); ?>, <?php echo e($rIndex); ?>)" class="text-[10px] text-rose-600 hover:underline">শেষ সারি মুছুন</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editorOpen): ?>
        <?php echo $__env->make('livewire.partials.audit-custom-table-editor-modal', [
            'blockIndex' => $blockIndex,
            'table' => $table,
            'customTableSizeCols' => $customTableSizeCols ?? count($table['columns']),
            'customTableSizeRows' => $customTableSizeRows ?? count($table['rows']),
            'customTableSelR' => $customTableSelR ?? null,
            'customTableSelC' => $customTableSelC ?? null,
            'customTableMergeRows' => $customTableMergeRows ?? 2,
            'customTableMergeCols' => $customTableMergeCols ?? 1,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-custom-table-block.blade.php ENDPATH**/ ?>