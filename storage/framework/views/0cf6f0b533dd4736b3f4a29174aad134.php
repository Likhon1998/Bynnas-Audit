
<?php
    use App\Support\CustomTableSchema;
    $editable = (bool) ($editable ?? false);
    $selectable = (bool) ($selectable ?? false);
    $alpineSelect = (bool) ($alpineSelect ?? false);
    $blockIndex = (int) ($blockIndex ?? 0);
    $table = CustomTableSchema::normalize(is_array($block ?? []) ? $block : []);
    $columns = $table['columns'];
    $rows = $table['rows'];
    $leaves = CustomTableSchema::leafColumns($columns);
    $leafCount = count($leaves);
    $widths = CustomTableSchema::leafWidths($columns);
    $headerMatrix = CustomTableSchema::headerMatrix($columns);
    $paint = CustomTableSchema::bodyPaintPlan($table);
    $tableClass = ($compact ?? false) ? 'a4-table a4-table-compact text-[9px]' : 'a4-table text-[10.5px]';
?>

<table class="<?php echo e($tableClass); ?> mb-[2mm] w-full border-collapse" style="table-layout: fixed;" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'ct-table-'.e($blockIndex).'-'.e($leafCount).'-'.e(count($rows)).''; ?>wire:key="ct-table-<?php echo e($blockIndex); ?>-<?php echo e($leafCount); ?>-<?php echo e(count($rows)); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leafCount > 0): ?>
        <colgroup>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $widths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <col style="width: <?php echo e($w); ?>%;">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </colgroup>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <thead>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $headerMatrix; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hRow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hCell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <th
                        class="border border-slate-700 bg-slate-200 px-1 py-1 text-center font-bold align-middle"
                        colspan="<?php echo e($hCell['colspan']); ?>"
                        rowspan="<?php echo e($hCell['rowspan']); ?>"
                    ><?php echo e($hCell['text']); ?></th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php $isTotal = (bool) ($row['is_total'] ?? false); ?>
            <tr class="<?php echo e($isTotal ? 'font-bold' : ''); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($c = 0; $c < $leafCount; $c++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $cell = $paint[$rIndex][$c] ?? null;
                        if (! $cell || ($cell['skip'] ?? false)) {
                            continue;
                        }
                        $rs = max(1, (int) ($cell['rowspan'] ?? 1));
                        $cs = max(1, (int) ($cell['colspan'] ?? 1));
                        $alignClass = ($c <= 2 && $rs > 1) ? 'align-middle' : 'align-top';
                        $textAlign = ($c === 2 && $cs === 1 && $rs === 1) ? 'text-left' : 'text-center';
                    ?>
                    <td
                        class="border border-slate-700 px-1 py-0.5 <?php echo e($alignClass); ?> <?php echo e($textAlign); ?> <?php echo e($selectable || $alpineSelect ? 'cursor-pointer' : ''); ?>"
                        rowspan="<?php echo e($rs); ?>"
                        colspan="<?php echo e($cs); ?>"
                        <?php if($alpineSelect): ?>
                            data-merge-rs="<?php echo e($rs); ?>"
                            data-merge-cs="<?php echo e($cs); ?>"
                            @click="selectCell(<?php echo e($rIndex); ?>, <?php echo e($c); ?>, $event.currentTarget)"
                            :class="selR === <?php echo e($rIndex); ?> && selC === <?php echo e($c); ?> ? 'ring-2 ring-inset ring-violet-500 bg-violet-50' : ''"
                        <?php elseif($selectable): ?>
                            wire:click="selectCustomTableCell(<?php echo e($rIndex); ?>, <?php echo e($c); ?>)"
                        <?php endif; ?>
                    >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <input
                                type="text"
                                <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'ct-cell-'.e($blockIndex).'-'.e($rIndex).'-'.e($c).''; ?>wire:key="ct-cell-<?php echo e($blockIndex); ?>-<?php echo e($rIndex); ?>-<?php echo e($c); ?>"
                                wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.rows.<?php echo e($rIndex); ?>.cells.<?php echo e($c); ?>"
                                class="w-full border-0 bg-transparent <?php echo e($textAlign); ?> text-[11px] <?php echo e($isTotal ? 'font-bold' : ''); ?>"
                                <?php if($alpineSelect || $selectable): ?> @click.stop <?php endif; ?>
                            >
                        <?php else: ?>
                            <?php echo e($cell['text'] ?? ''); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </tbody>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-custom-table-render.blade.php ENDPATH**/ ?>