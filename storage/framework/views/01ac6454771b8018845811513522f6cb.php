<?php
    $depth = (int) ($depth ?? 0);
    $column = is_array($column ?? null) ? $column : [];
    $colId = (string) ($column['id'] ?? '');
    $children = array_values((array) ($column['children'] ?? []));
    $path = array_values((array) ($path ?? []));
    $pad = $depth * 14;
    $showWidth = (bool) ($showWidth ?? false);
    $isLeaf = $children === [];

    $segments = ['columns'];
    foreach ($path as $i => $idx) {
        if ($i === 0) {
            $segments[] = (string) $idx;
        } else {
            $segments[] = 'children';
            $segments[] = (string) $idx;
        }
    }
    $labelWire = 'reportBlocks.'.$blockIndex.'.'.implode('.', $segments).'.label';
?>

<div class="rounded border border-slate-100 bg-slate-50/80 px-2 py-1" style="margin-left: <?php echo e($pad); ?>px;">
    <div class="flex flex-wrap items-center gap-1">
        <input
            type="text"
            wire:model.blur="<?php echo e($labelWire); ?>"
            class="min-w-[120px] flex-1 rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[11px] font-semibold"
            placeholder="কলাম নাম"
        >

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showWidth && $isLeaf): ?>
            <label class="flex items-center gap-0.5 text-[10px] text-slate-500" title="কলামের প্রস্থ %">
                <span>প্রস্থ</span>
                <input
                    type="number"
                    min="4"
                    max="80"
                    step="1"
                    value="<?php echo e(isset($column['width']) ? (float) $column['width'] : ''); ?>"
                    placeholder="auto"
                    class="w-14 rounded border border-slate-200 bg-white px-1 py-0.5 text-[11px]"
                    wire:change="setCustomTableLeafWidth(<?php echo e((int) $blockIndex); ?>, '<?php echo e($colId); ?>', $event.target.value)"
                >
                <span>%</span>
            </label>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <button
            type="button"
            wire:click="addCustomTableColumn(<?php echo e((int) $blockIndex); ?>, '<?php echo e($colId); ?>')"
            class="rounded border border-violet-300 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-violet-700 hover:bg-violet-50"
            title="এই কলামের নিচে সাব-কলাম"
        >+ সাব</button>

        <button
            type="button"
            wire:click="removeCustomTableColumn(<?php echo e((int) $blockIndex); ?>, '<?php echo e($colId); ?>')"
            class="rounded border border-rose-200 px-1.5 py-0.5 text-[10px] text-rose-600 hover:bg-rose-50"
        >×</button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childIndex => $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php echo $__env->make('livewire.partials.audit-custom-table-column-node', [
            'blockIndex' => $blockIndex,
            'column' => $child,
            'depth' => $depth + 1,
            'path' => array_merge($path, [$childIndex]),
            'showWidth' => $showWidth,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-custom-table-column-node.blade.php ENDPATH**/ ?>