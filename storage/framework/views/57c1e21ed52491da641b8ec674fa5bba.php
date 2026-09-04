<?php
    use App\Livewire\MakeAuditReport;
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
                    $anchor = ! $isSection ? MakeAuditReport::findingAnchorId($row['serial'] ?? '') : '';
                    $findingText = ($row['finding'] ?? '') !== '' ? $row['finding'] : '—';
                    $pageNo = $row['page_no'] ?? '';
                ?>
                <tr class="<?php echo e($isSection ? 'bg-[#efefef]' : ''); ?>">
                    <td class="text-center font-semibold">
                        <?php echo $__env->make('audits.partials.bn-num', [
                            'value' => $row['serial'] !== '' ? $row['serial'] : '—',
                            'variant' => $isSection ? 'serial-section' : 'serial',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                    <td class="<?php echo e($isSection ? 'font-bold' : ''); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isSection && $anchor !== ''): ?>
                            <a href="#<?php echo e($anchor); ?>" class="text-navy-900 underline decoration-slate-400 underline-offset-2 hover:text-[#2b579a]"><?php echo e($findingText); ?></a>
                        <?php else: ?>
                            <?php echo e($findingText); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="text-right"><?php echo e($isSection ? '' : ($row['amount'] !== '' ? $row['amount'] : '')); ?></td>
                    <td class="p-0 align-top">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isSection && $rating !== ''): ?>
                            <?php echo $__env->make('livewire.partials.audit-rating-box', ['rating' => $rating, 'editable' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo e($isSection ? '' : ($row['status'] ?? '')); ?></td>
                    <td class="text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isSection && $pageNo !== ''): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anchor !== ''): ?>
                                <a href="#<?php echo e($anchor); ?>" class="bn-page-link">
                                    <?php echo $__env->make('audits.partials.bn-num', ['value' => $pageNo, 'variant' => 'page'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </a>
                            <?php else: ?>
                                <?php echo $__env->make('audits.partials.bn-num', ['value' => $pageNo, 'variant' => 'page'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="6" class="px-2 py-3 text-center text-slate-500">কোনো সূচিপত্র এন্ট্রি নেই</td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-toc-table-preview.blade.php ENDPATH**/ ?>