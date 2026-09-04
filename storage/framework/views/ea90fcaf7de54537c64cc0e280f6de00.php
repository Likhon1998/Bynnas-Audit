<?php
    $dash = $dash ?? '………………';
?>

<p class="bold" style="margin:0 0 2mm;font-size:10px;"><?php echo e($page19_compliance_title ?? ''); ?></p>

<p style="margin:0 0 2mm;font-size:8px;">
    <span class="bold">নিরীক্ষাকাল:</span> <?php echo e($page19_compliance_period ?? ''); ?>

    &nbsp;&nbsp;
    <span class="bold">ফলোআপের তারিখ:</span> <?php echo e($page19_compliance_followup_date ?? ''); ?>

</p>

<table class="doc-table" style="margin-bottom:5mm;font-size:7px;">
    <thead>
        <tr>
            <th>বিগত প্রতিবেদনের অনুচ্ছেদ নং</th>
            <th>নিরীক্ষা ও পরিবীক্ষণে প্রাপ্ত ঘটনা সমূহ</th>
            <th>প্রথম উদঘাটনের সময়কাল</th>
            <th>ব্যবস্থাপনার জবাব</th>
            <th>বর্তমান অবস্থা</th>
            <th>বর্তমান প্রতিবেদনের অনুচ্ছেদ নং</th>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $page19ComplianceRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td class="center"><?php echo e($row['prev_para_no'] ?? ''); ?></td>
                <td><?php echo e($row['findings'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['first_discovery_period'] ?? ''); ?></td>
                <td><?php echo e($row['management_reply'] ?? ''); ?></td>
                <td><?php echo e($row['current_status'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['current_para_no'] ?? ''); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </tbody>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\financial-page19-pdf.blade.php ENDPATH**/ ?>