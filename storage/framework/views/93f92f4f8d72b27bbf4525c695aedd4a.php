<?php
    use App\Support\AuditDocumentLayout as Doc;
    use App\Livewire\MakeAuditReport;
    $rows = $rows ?? [];
    $showTitle = $showTitle ?? false;
    $titleMarginTop = $titleMarginTop ?? '5mm';
    $widths = Doc::tocColumnWidths();
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTitle): ?>
    <h3 <?php if($titleMarginTop === '0'): ?> style="margin-top:0;" <?php endif; ?>>সূচিপত্র</h3>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<table class="doc-table compact toc-table">
    <colgroup>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $widths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <col style="width:<?php echo e($w); ?>%;">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </colgroup>
    <thead>
        <tr>
            <th>ক্রমিক নং</th>
            <th class="left-align">নিরীক্ষায় প্রাপ্ত ঘটনা সমূহ</th>
            <th>টাকা</th>
            <th>রেটিং</th>
            <th>বর্তমান অবস্থা</th>
            <th>পৃষ্ঠা নাম্বার</th>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $isSection = ($row['type'] ?? 'item') === 'section';
                $rating = $row['rating'] ?? '';
                $anchor = ! $isSection ? MakeAuditReport::findingAnchorId($row['serial'] ?? '') : '';
                $findingText = ($row['finding'] ?? '') !== '' ? $row['finding'] : '—';
                $pageNo = ($row['page_no'] ?? '') !== '' ? $row['page_no'] : '';
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSection): ?>
                <tr>
                    <td class="center bold section"><?php echo e($row['serial'] !== '' ? $row['serial'] : '—'); ?></td>
                    <td colspan="5" class="section left-align"><?php echo e($row['finding'] !== '' ? $row['finding'] : '—'); ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td class="center bold"><?php echo e($row['serial'] !== '' ? $row['serial'] : '—'); ?></td>
                    <td class="align-top left-align">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anchor !== ''): ?>
                            <a href="#<?php echo e($anchor); ?>" style="color:#111; text-decoration:underline;"><?php echo e($findingText); ?></a>
                        <?php else: ?>
                            <?php echo e($findingText); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="right-align"><?php echo ($row['amount'] ?? '') !== '' ? e($row['amount']) : '&nbsp;'; ?></td>
                    <td class="rating-cell">
                        <?php echo $__env->make('audits.partials.toc-rating-cell-pdf', ['isSection' => false, 'rating' => $rating], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                    <td class="center"><?php echo ($row['status'] ?? '') !== '' ? e($row['status']) : '&nbsp;'; ?></td>
                    <td class="center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anchor !== '' && $pageNo !== ''): ?>
                            <a href="#<?php echo e($anchor); ?>" style="color:#111; text-decoration:underline;"><?php echo e($pageNo); ?></a>
                        <?php else: ?>
                            <?php echo $pageNo !== '' ? e($pageNo) : '&nbsp;'; ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <tr>
                <td colspan="6" class="center">কোনো সূচিপত্র এন্ট্রি নেই</td>
            </tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </tbody>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/toc-table.blade.php ENDPATH**/ ?>