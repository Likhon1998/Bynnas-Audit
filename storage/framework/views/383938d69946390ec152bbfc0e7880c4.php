<?php
    use App\Livewire\MakeAuditReport;
    use App\Support\AuditDocumentLayout as Doc;
    $dash = $dash ?? '………………';
    $widths = Doc::findingColumnWidths();
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $page7Findings ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $finding): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php
        $anchor = MakeAuditReport::findingAnchorId($finding['serial'] ?? '');
        $detailType = (string) ($finding['detail_type'] ?? 'none');
        $budgetYear = (string) ($finding['budget_year'] ?? '২০২২-২০২৩');
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
    <p class="justify" style="margin:0;"><?php echo e($finding['criteria'] ?? ''); ?></p>

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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailType === 'budget'): ?>
        <p class="bold" style="margin:2mm 0 1mm;"><?php echo e($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:'); ?></p>
        <table class="doc-table" style="margin-bottom:3mm;font-size:9.5px;">
            <thead>
                <tr>
                    <th rowspan="2">বাজেটের খাত</th>
                    <th colspan="2">বাজেট <?php echo e($budgetYear); ?></th>
                    <th rowspan="2">প্রকৃত খরচ জুন পর্যন্ত</th>
                    <th rowspan="2">পার্থক্য</th>
                </tr>
                <tr>
                    <th>বাৎসরিক</th>
                    <th>জুন পর্যন্ত</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $finding['budgetRows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><?php echo e($row['budget_head'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['budget_annual'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['budget_upto_june'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['actual_expense'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['difference'] ?? ''); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailType === 'bonus'): ?>
        <p class="bold" style="margin:2mm 0 1mm;"><?php echo e($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:'); ?></p>
        <table class="doc-table" style="margin-bottom:3mm;font-size:9.5px;">
            <thead>
                <tr>
                    <th>চাকরিতে যোগদানের তারিখ</th>
                    <th>বোনাস প্রদানের তারিখ ও ভাউচার নং</th>
                    <th>বোনাস প্রদানের দিন পর্যন্ত চাকরির বয়স</th>
                    <th>বোনাসের পরিমাণ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $finding['bonusRows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="center"><?php echo e($row['joining_date'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['bonus_date_voucher'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['service_age'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['bonus_amount'] ?? ''); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <p class="bold" style="margin:2mm 0 1mm;">ঝুঁকি/প্রভাব (Risk/Implication):</p>
    <p class="justify" style="margin:0 0 2mm;white-space:pre-wrap;"><?php echo e(($finding['risk'] ?? '') !== '' ? $finding['risk'] : $dash); ?></p>

    <p class="bold" style="margin:0 0 1mm;">মূল কারণ (Root Cause):</p>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding['root_cause'] ?? '') !== ''): ?>
        <p class="justify" style="margin:0 0 2mm;"><?php echo e($finding['root_cause']); ?></p>
    <?php else: ?>
        <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1.4;">&nbsp;</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <p class="bold" style="margin:0 0 1mm;">সুপারিশ (Recommendation):</p>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\financial-page7-pdf.blade.php ENDPATH**/ ?>