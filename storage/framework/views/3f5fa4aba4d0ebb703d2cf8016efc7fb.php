<?php
    use App\Livewire\MakeAuditReport;
    use App\Support\AuditDocumentLayout as Doc;
    $dash = $dash ?? '………………';
    $widths = Doc::findingColumnWidths();
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($page13_section_title ?? '') !== ''): ?>
    <p class="bold center" style="margin:4mm 0 3mm;font-size:12px;text-decoration:underline;"><?php echo e($page13_section_title); ?></p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $page13Findings ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $finding): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php
        $anchor = MakeAuditReport::findingAnchorId($finding['serial'] ?? '');
        $detailType = (string) ($finding['detail_type'] ?? 'none');
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anchor !== ''): ?>
        <a id="<?php echo e($anchor); ?>" name="<?php echo e($anchor); ?>"></a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <table class="doc-table finding-table" style="margin-bottom:2mm;">
        <colgroup>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $widths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <col style="width:<?php echo e($w); ?>%;">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </colgroup>
        <tbody>
            <tr>
                <td class="bold center">
                    <?php echo $__env->make('audits.partials.bn-num', ['value' => $finding['serial'] ?? '', 'variant' => 'serial'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </td>
                <td class="bold center"><?php echo e($finding['title'] ?? 'শিরোনাম'); ?></td>
                <td class="body-cell">
                    <?php echo e($finding['body'] ?? ''); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding['amount'] ?? '') !== ''): ?>
                        <br><span class="bold">টাকার পরিমাণ:</span> <?php echo e($finding['amount']); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="rating-cell" valign="middle">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($forDoc ?? false): ?>
                        <?php echo $__env->make('audits.partials.rating-box-doc', ['rating' => $finding['rating'] ?? ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php else: ?>
                        <?php echo $__env->make('audits.partials.rating-box-pdf', ['rating' => $finding['rating'] ?? ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="bold" style="margin:3mm 0 1mm;">প্রচলিত নিয়ম (Criteria):</p>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding['criteria'] ?? '') !== ''): ?>
        <p class="justify" style="margin:0;"><?php echo e($finding['criteria']); ?></p>
    <?php else: ?>
        <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <p class="bold" style="margin:3mm 0 1mm;">পর্যবেক্ষণ (Observation) :</p>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding['observation'] ?? '') !== ''): ?>
        <p class="justify" style="margin:0 0 2mm;"><?php echo e($finding['observation']); ?></p>
    <?php else: ?>
        <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <table class="doc-table obs-table" style="margin-bottom:2mm;">
        <thead>
            <tr>
                <th>Total Population</th>
                <th>Sample Size(Checked)</th>
                <th>Instantans Found</th>
                <th>Persentange(%)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $finding['statsRows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td class="center"><?php echo e($row['total_population'] ?? ''); ?></td>
                    <td class="center"><?php echo e($row['sample_size'] ?? ''); ?></td>
                    <td class="center"><?php echo e($row['instances_found'] ?? ''); ?></td>
                    <td class="center"><?php echo e($row['percentage'] ?? ''); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailType === 'samity_collection'): ?>
        <p class="bold" style="margin:2mm 0 1mm;"><?php echo e($finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হলো:'); ?></p>
        <table class="doc-table" style="margin-bottom:3mm;font-size:6.5px;">
            <thead>
                <tr>
                    <th rowspan="2">সমিতি নং</th>
                    <th rowspan="2">সদস্যের নাম/আইডি</th>
                    <th rowspan="2">তারিখ</th>
                    <th colspan="5">পাসবই অনুযায়ী আদায়ের তথ্য</th>
                    <th colspan="2">জমার তথ্য</th>
                    <th rowspan="2">পার্থক্য</th>
                    <th rowspan="2">কর্মীর নাম ও আইডি</th>
                </tr>
                <tr>
                    <th>বা: স:</th>
                    <th>স্বেচ্ছা</th>
                    <th>মেয়াদী</th>
                    <th>কিস্তি</th>
                    <th>মোট আদায়</th>
                    <th>তারিখ</th>
                    <th>টাকা</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $finding['samityRows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="center"><?php echo e($row['samity_no'] ?? ''); ?></td>
                        <td><?php echo e($row['member_name_id'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['date'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['savings'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['voluntary'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['term'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['installment'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['total_collection'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['deposit_date'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['deposit_amount'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['difference'] ?? ''); ?></td>
                        <td><?php echo e($row['staff_name_id'] ?? ''); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    <?php elseif(($finding['detail_intro'] ?? '') !== ''): ?>
        <p class="bold" style="margin:2mm 0 2mm;"><?php echo e($finding['detail_intro']); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <p class="bold" style="margin:2mm 0 1mm;">ঝুঁকি:-</p>
    <p class="justify" style="margin:0 0 2mm;white-space:pre-wrap;"><?php echo e(($finding['risk'] ?? '') !== '' ? $finding['risk'] : $dash); ?></p>

    <p class="bold" style="margin:0 0 1mm;">মূল কারণ (Root Cause):</p>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding['root_cause'] ?? '') !== ''): ?>
        <p class="justify" style="margin:0 0 2mm;"><?php echo e($finding['root_cause']); ?></p>
    <?php else: ?>
        <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <p class="bold" style="margin:0 0 1mm;">সুপারিশ (Recommendation) :</p>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding['recommendation'] ?? '') !== ''): ?>
        <p class="justify" style="margin:0 0 3mm;"><?php echo e($finding['recommendation']); ?></p>
    <?php else: ?>
        <p style="margin:0 0 3mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <table class="doc-table" style="margin-bottom:5mm;font-size:10px;">
        <tbody>
            <tr>
                <td style="width:38%;" class="bold">শাখা ব্যবস্থাপকের জবাব</td>
                <td><?php echo e($finding['bm_reply'] ?? ''); ?></td>
            </tr>
            <tr>
                <td class="bold">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
                <td><?php echo e($finding['responsible'] ?? ''); ?></td>
            </tr>
            <tr>
                <td class="bold">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)</td>
                <td><?php echo e($finding['resolution_date'] ?? ''); ?></td>
            </tr>
        </tbody>
    </table>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\financial-page13-pdf.blade.php ENDPATH**/ ?>