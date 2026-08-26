<?php
    /** @var \Illuminate\Support\Collection|array $rows */
    $rows = $rows ?? [];
    $compact = $compact ?? false;
?>

<div class="<?php echo e(($showTitle ?? true) ? 'mt-[5mm]' : 'mt-0'); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTitle ?? true): ?>
        <h3 class="mb-[2mm] text-center text-[13px] font-bold underline decoration-1 underline-offset-4">সূচিপত্র</h3>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <table class="a4-table <?php echo e($compact ? 'a4-table-compact' : ''); ?> text-[10px] leading-[1.3]">
        <thead>
            <tr>
                <th class="w-[12mm]">ক্রমিক নং</th>
                <th>নিরীক্ষায় প্রাপ্ত ঘটনা সমূহ</th>
                <th class="w-[16mm]">টাকা</th>
                <th class="w-[24mm]">রেটিং</th>
                <th class="w-[18mm]">বর্তমান অবস্থা</th>
                <th class="w-[14mm]">পৃষ্ঠা নাম্বার</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $isSection = ($row['type'] ?? 'item') === 'section';
                    $rating = $row['rating'] ?? '';
                    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating);
                ?>
                <tr class="<?php echo e($isSection ? 'bg-[#efefef]' : ''); ?>">
                    <td class="text-center font-semibold"><?php echo e($row['serial'] !== '' ? $row['serial'] : '—'); ?></td>
                    <td class="<?php echo e($isSection ? 'font-bold' : ''); ?>"><?php echo e($row['finding'] !== '' ? $row['finding'] : '—'); ?></td>
                    <td class="text-right"><?php echo e($isSection ? '' : ($row['amount'] !== '' ? $row['amount'] : '')); ?></td>
                    <td
                        class="text-center font-semibold"
                        style="<?php echo e($isSection || $rating === '' ? '' : 'background: '.$style['bg'].'; color: '.$style['color'].';'); ?>"
                    ><?php echo e($isSection ? '' : $rating); ?></td>
                    <td class="text-center"><?php echo e($isSection ? '' : ($row['status'] ?? '')); ?></td>
                    <td class="text-center"><?php echo e($isSection ? '' : ($row['page_no'] ?? '')); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="6" class="px-2 py-3 text-center text-slate-500">কোনো সূচিপত্র এন্ট্রি নেই</td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-toc-table-preview.blade.php ENDPATH**/ ?>