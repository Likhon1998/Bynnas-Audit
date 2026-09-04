<?php
    $rows = \App\Support\AuditReportClassification::ratingRows();
    $summaryRows = \App\Support\AuditReportClassification::performanceSummaryRows();
    $hImportance = \App\Support\AuditTableHeaders::get($tableHeaders ?? [], 'classification_importance');
    $hEval = \App\Support\AuditTableHeaders::get($tableHeaders ?? [], 'classification_eval');
?>

<h3>প্রতিবেদনের শ্রেণীবিন্যাস</h3>

<table class="doc-table classification-table">
    <colgroup>
        <col style="width:16%;">
        <col style="width:7%;">
        <col style="width:77%;">
    </colgroup>
    <thead>
        <tr>
            <th style="background:#BDD7EE;"><?php echo e($hImportance[0]); ?></th>
            <th style="background:#BDD7EE;"><?php echo e($hImportance[1]); ?></th>
            <th style="background:#BDD7EE;"><?php echo e($hImportance[2]); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['level'] !== null): ?>
                    <td rowspan="<?php echo e($row['level_rowspan']); ?>" class="center bold" style="vertical-align:middle;background:#fff;"><?php echo e($row['level']); ?></td>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <td class="center bold" style="background: <?php echo e($row['code_bg']); ?>; color: <?php echo e($row['code_color']); ?>;"><?php echo e($row['code']); ?></td>
                <td style="vertical-align:top;text-align:justify;line-height:1.35;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['bulleted']): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $row['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <p style="margin:0 0 0.8mm 3mm;text-indent:-2mm;">• <?php echo e($item); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php else: ?>
                        <p style="margin:0;"><?php echo e($row['items'][0] ?? ''); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </tbody>
</table>

<table class="doc-table classification-table classification-summary">
    <colgroup>
        <col style="width:70%;">
        <col style="width:30%;">
    </colgroup>
    <tbody>
        <tr>
            <th style="background:#2E5090;color:#fff;width:70%;"><?php echo e($hEval[0]); ?></th>
            <th style="background:#2E5090;color:#fff;width:30%;"><?php echo e($hEval[1]); ?></th>
        </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $summaryRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td><?php echo e($summary['label']); ?></td>
                <td class="center bold"><?php echo \App\Support\BanglaNumerals::highlight($summary['range'], 'stat'); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </tbody>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/classification-table-pdf.blade.php ENDPATH**/ ?>