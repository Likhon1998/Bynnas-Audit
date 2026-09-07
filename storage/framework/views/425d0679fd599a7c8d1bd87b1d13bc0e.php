<p class="bold center" style="margin:0 0 2mm;font-size:10px;"><?php echo e($page20_it_title ?? ''); ?></p>

<p class="center" style="margin:0 0 1mm;font-size:9px;line-height:1.4;">
    <?php echo e($page20_it_org_line1 ?? ''); ?><br>
    <?php echo e($page20_it_org_line2 ?? ''); ?><br>
    <?php echo e($page20_it_org_line3 ?? ''); ?>

</p>

<p class="center" style="margin:0 0 2mm;font-size:8px;">
    <span class="bold">কর্মসূচীর নাম:</span> <?php echo e($page20_it_program ?? ''); ?>

    &nbsp;&nbsp;
    <span class="bold">শাখার নাম:</span> <?php echo e($page20_it_branch ?? ''); ?>

</p>

<p class="bold center" style="margin:0 0 2mm;font-size:8px;"><?php echo e($page20_it_instruction ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন'); ?></p>

<table class="doc-table" style="margin-bottom:3mm;font-size:7px;">
    <thead>
        <tr>
            <th rowspan="2">ক্রমিক</th>
            <th rowspan="2">বিবরণ</th>
            <th colspan="3">Compliance</th>
            <th rowspan="2">Action Owner (কার দায়িত্ব)</th>
            <th rowspan="2">Management Comments (ব্যবস্থাপনার মন্তব্য)</th>
            <th rowspan="2">Recommendation (সুপারিশ)</th>
        </tr>
        <tr>
            <th>Yes</th>
            <th>No</th>
            <th>N/A</th>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $page20ItChecklistRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php $compliance = (string) ($row['compliance'] ?? ''); ?>
            <tr>
                <td class="center"><?php echo e($row['sl_no'] ?? ''); ?></td>
                <td><?php echo e($row['description'] ?? ''); ?></td>
                <td class="center"><?php echo e($compliance === 'yes' ? '✓' : ''); ?></td>
                <td class="center"><?php echo e($compliance === 'no' ? '✓' : ''); ?></td>
                <td class="center"><?php echo e($compliance === 'na' ? '✓' : ''); ?></td>
                <td><?php echo e($row['action_owner'] ?? ''); ?></td>
                <td><?php echo e($row['management_comments'] ?? ''); ?></td>
                <td><?php echo e($row['recommendation'] ?? ''); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </tbody>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\financial-page20-pdf.blade.php ENDPATH**/ ?>