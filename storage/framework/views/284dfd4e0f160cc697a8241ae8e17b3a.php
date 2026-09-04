<?php
    use App\Livewire\MakeAuditReport;
    $dash = $dash ?? '………………';
?>

<p class="bold" style="margin:0 0 2mm;">বিস্তারিত নিম্নে দেওয়া হল:</p>

<table class="doc-table" style="margin-bottom:3mm;font-size:8.5px;">
    <thead>
        <tr>
            <th rowspan="2">তারিখ/মাসের নাম</th>
            <th rowspan="2">ভাউচার নং</th>
            <th rowspan="2">বিবরণ</th>
            <th rowspan="2">খরচ (টাকা)</th>
            <th colspan="3">ভ্যাট সংক্রান্ত</th>
            <th colspan="3">ট্যাক্স সংক্রান্ত</th>
        </tr>
        <tr>
            <th>প্রযোজ্য</th>
            <th>প্রদানকৃত</th>
            <th>কম/বেশি প্রদান</th>
            <th>প্রযোজ্য</th>
            <th>প্রদানকৃত</th>
            <th>কম/বেশি প্রদান</th>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $expenseDetailRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td><?php echo e($row['date_month'] ?? ''); ?></td>
                <td><?php echo e($row['voucher_no'] ?? ''); ?></td>
                <td class="<?php echo e(! empty($row['is_total']) ? 'bold' : ''); ?>"><?php echo e($row['description'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['expense_amount'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['vat_applicable'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['vat_paid'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['vat_diff'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['tax_applicable'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['tax_paid'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['tax_diff'] ?? ''); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </tbody>
</table>

<p class="bold" style="margin:2mm 0 1mm;">ঝুঁকি/প্রভাব (Risk/Implication):</p>
<p class="justify" style="margin:0 0 2mm;"><?php echo e($expense_detail_risk !== '' ? $expense_detail_risk : $dash); ?></p>
<p class="bold" style="margin:0 0 1mm;">মূল কারণ (Root Cause):</p>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($expense_detail_root_cause ?? '') !== ''): ?>
    <p class="justify" style="margin:0 0 2mm;"><?php echo e($expense_detail_root_cause); ?></p>
<?php else: ?>
    <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<p class="bold" style="margin:0 0 1mm;">সুপারিশ (Recommendation):</p>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($expense_detail_recommendation ?? '') !== ''): ?>
    <p class="justify" style="margin:0 0 3mm;"><?php echo e($expense_detail_recommendation); ?></p>
<?php else: ?>
    <p style="margin:0 0 3mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<table class="doc-table" style="margin-bottom:5mm;font-size:10px;">
    <tbody>
        <tr>
            <td style="width:38%;" class="bold">শাখা ব্যবস্থাপকের জবাব</td>
            <td><?php echo e($expense_detail_bm_reply); ?></td>
        </tr>
        <tr>
            <td class="bold">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
            <td><?php echo e($expense_detail_responsible); ?></td>
        </tr>
        <tr>
            <td class="bold">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)</td>
            <td><?php echo e($expense_detail_resolution_date); ?></td>
        </tr>
    </tbody>
</table>

<?php $anchor = MakeAuditReport::findingAnchorId($finding13_serial ?? '১.৩'); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anchor !== ''): ?>
    <a id="<?php echo e($anchor); ?>" name="<?php echo e($anchor); ?>"></a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php
    $widths = \App\Support\AuditDocumentLayout::findingColumnWidths();
?>
<table class="doc-table finding-table" style="margin-bottom:2mm;">
    <colgroup>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $widths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <col style="width:<?php echo e($w); ?>%;">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </colgroup>
    <tbody>
        <tr>
            <td class="bold center">
                <?php echo $__env->make('audits.partials.bn-num', ['value' => $finding13_serial ?? '', 'variant' => 'serial'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </td>
            <td class="bold center"><?php echo e($finding13_title ?? 'শিরোনাম'); ?></td>
            <td class="body-cell">
                <?php echo e($finding13_body ?? ''); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding13_amount ?? '') !== ''): ?>
                    <br><span class="bold">টাকার পরিমাণ:</span> <?php echo e($finding13_amount); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
            <td class="rating-cell" valign="middle">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($forDoc ?? false): ?>
                    <?php echo $__env->make('audits.partials.rating-box-doc', ['rating' => $finding13_rating ?? ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php else: ?>
                    <?php echo $__env->make('audits.partials.rating-box-pdf', ['rating' => $finding13_rating ?? ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>

<p class="bold" style="margin:3mm 0 1mm;">প্রচলিত নিয়ম (Criteria):</p>
<p class="justify" style="margin:0;"><?php echo e($finding13_criteria); ?></p>

<p class="bold" style="margin:3mm 0 1mm;">পর্যবেক্ষণ (Observation) :</p>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding13_observation ?? '') !== ''): ?>
    <p class="justify" style="margin:0 0 2mm;"><?php echo e($finding13_observation); ?></p>
<?php else: ?>
    <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<table class="doc-table obs-table" style="margin-bottom:3mm;">
    <thead>
        <tr>
            <th>Total Population</th>
            <th>Sample Size(Checked)</th>
            <th>Instantans Found</th>
            <th>Persentange(%)</th>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $finding13_statsRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td class="center"><?php echo e($row['total_population'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['sample_size'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['instances_found'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['percentage'] ?? ''); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </tbody>
</table>

<table class="doc-table" style="margin-bottom:3mm;font-size:9.5px;">
    <thead>
        <tr>
            <th>বিবরণ</th>
            <th>মাসের নাম</th>
            <th>টাকা উত্তোলনের তারিখ</th>
            <th>সরকারী কোষাগারে টাকা জমা প্রদানের তারিখ</th>
            <th>টাকার পরিমাণ</th>
            <th>হস্তমজুদের সময়কাল</th>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $finding13_depositRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td><?php echo e($row['description'] ?? ''); ?></td>
                <td><?php echo e($row['month_name'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['withdrawal_date'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['deposit_date'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['amount'] ?? ''); ?></td>
                <td class="center"><?php echo e($row['holding_period'] ?? ''); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </tbody>
</table>

<p class="bold" style="margin:2mm 0 1mm;">ঝুঁকি/প্রভাব (Risk/Implication):</p>
<p class="justify" style="margin:0 0 2mm;"><?php echo e($finding13_risk !== '' ? $finding13_risk : $dash); ?></p>
<p class="bold" style="margin:0 0 1mm;">মূল কারণ (Root Cause):</p>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding13_root_cause ?? '') !== ''): ?>
    <p class="justify" style="margin:0 0 2mm;"><?php echo e($finding13_root_cause); ?></p>
<?php else: ?>
    <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<p class="bold" style="margin:0 0 1mm;">সুপারিশ (Recommendation):</p>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding13_recommendation ?? '') !== ''): ?>
    <p class="justify" style="margin:0 0 3mm;"><?php echo e($finding13_recommendation); ?></p>
<?php else: ?>
    <p style="margin:0 0 3mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<table class="doc-table" style="font-size:10px;">
    <tbody>
        <tr>
            <td style="width:38%;" class="bold">শাখা ব্যবস্থাপকের জবাব</td>
            <td><?php echo e($finding13_bm_reply); ?></td>
        </tr>
        <tr>
            <td class="bold">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
            <td><?php echo e($finding13_responsible); ?></td>
        </tr>
        <tr>
            <td class="bold">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)</td>
            <td><?php echo e($finding13_resolution_date); ?></td>
        </tr>
    </tbody>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\financial-detail-pdf.blade.php ENDPATH**/ ?>