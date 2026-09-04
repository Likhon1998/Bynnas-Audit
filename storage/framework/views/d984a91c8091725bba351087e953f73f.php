<?php
    use App\Support\AuditDocumentLayout as Doc;
    use App\Livewire\MakeAuditReport;
    $widths = Doc::findingColumnWidths();

    $blocks = $reportBlocks ?? [];
    if ($blocks === []) {
        $sections = $reportSections ?? [];
        if ($sections === []) {
            $sections = [[
                'serial' => '১.০',
                'title' => $financial_section_title ?? '১.০ আর্থিক নিরীক্ষা (Financial Audit) :',
                'findings' => $financialFindings ?? [],
            ]];
        }
        foreach ($sections as $section) {
            $blocks[] = [
                'type' => 'section',
                'serial' => $section['serial'] ?? '১.০',
                'title' => $section['title'] ?? '',
            ];
            foreach (($section['findings'] ?? []) as $finding) {
                $blocks[] = array_merge(['type' => 'finding'], is_array($finding) ? $finding : []);
            }
        }
        $blocks[] = [
            'type' => 'criteria',
            'label' => 'প্রচলিত নিয়ম (Criteria):',
            'body' => $financial_criteria ?? '',
        ];
        $blocks[] = [
            'type' => 'observation',
            'label' => 'পর্যবেক্ষণ (Observation) :',
            'body' => '',
        ];
        $blocks[] = [
            'type' => 'stats',
            'heading' => 'Report Rating Box:',
            'rows' => $vatObservationRows ?? [],
        ];
        $blocks[] = [
            'type' => 'stats',
            'heading' => 'Report Rating Box:',
            'rows' => $taxObservationRows ?? [],
        ];
    }
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bIndex => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php $type = $block['type'] ?? ''; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'section'): ?>
        <p class="section-heading bold finding-heading" style="<?php echo e($bIndex > 0 ? 'margin-top:4mm;' : ''); ?>"><?php echo \App\Support\BanglaNumerals::highlight($block['title'] ?? ($block['serial'] ?? ''), 'serial'); ?></p>

    <?php elseif($type === 'finding'): ?>
        <?php $anchor = MakeAuditReport::findingAnchorId($block['serial'] ?? ''); ?>
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
                        <?php echo $__env->make('audits.partials.bn-num', ['value' => $block['serial'] ?? '', 'variant' => 'serial'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                    <td class="bold center"><?php echo e($block['title'] ?? 'শিরোনাম'); ?></td>
                    <td class="body-cell">
                        <?php echo e($block['body'] ?? ''); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($block['amount'] ?? '') !== ''): ?>
                            <br><span class="bold">টাকার পরিমাণ:</span> <?php echo e($block['amount']); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="rating-cell" valign="middle">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($forDoc ?? false): ?>
                            <?php echo $__env->make('audits.partials.rating-box-doc', ['rating' => $block['rating'] ?? ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php else: ?>
                            <?php echo $__env->make('audits.partials.rating-box-pdf', ['rating' => $block['rating'] ?? ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

    <?php elseif($type === 'criteria'): ?>
        <p class="bold" style="margin:3mm 0 1mm;"><?php echo e($block['label'] ?? 'প্রচলিত নিয়ম (Criteria):'); ?></p>
        <p class="justify" style="margin:0;"><?php echo e($block['body'] ?? $financial_criteria ?? ''); ?></p>

    <?php elseif($type === 'observation'): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($block['label'] ?? '') !== ''): ?>
            <p class="bold" style="margin:3mm 0 1mm;"><?php echo e($block['label']); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($block['body'] ?? '') !== ''): ?>
            <p class="justify" style="margin:0 0 2mm;"><?php echo e($block['body']); ?></p>
        <?php else: ?>
            <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1;">&nbsp;</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php elseif(in_array($type, ['stats', 'vat', 'tax'], true)): ?>
        <?php
            $obsHeading = (string) ($block['heading'] ?? 'Report Rating Box:');
            if (in_array($obsHeading, ['ভ্যাট সংক্রান্ত:', 'ট্যাক্স সংক্রান্ত:', 'সারণী:', 'নতুন সারণী:'], true)) {
                $obsHeading = 'Report Rating Box:';
            }
            $obsRows = array_values((array) ($block['rows'] ?? (
                $type === 'tax' ? ($taxObservationRows ?? []) : ($vatObservationRows ?? [])
            )));
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($obsHeading !== ''): ?>
            <p class="bold obs-label"><?php echo e($obsHeading); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <table class="doc-table obs-table" style="margin-bottom:3mm;">
            <colgroup>
                <col style="width:25%;">
                <col style="width:25%;">
                <col style="width:25%;">
                <col style="width:25%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Total Population</th>
                    <th>Sample Size(Checked)</th>
                    <th>Instantans Found</th>
                    <th>Persentange(%)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $obsRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="center"><?php echo e($row['total_population'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['sample_size'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['instances_found'] ?? ''); ?></td>
                        <td class="center"><?php echo e($row['percentage'] ?? ''); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>

    <?php elseif($type === 'custom_table'): ?>
        <?php echo $__env->make('audits.partials.custom-table-pdf', ['block' => $block], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php elseif($type === 'jobab_table'): ?>
        <?php
            $jobabRows = array_values((array) ($block['rows'] ?? []));
        ?>
        <table class="doc-table" style="margin:3mm 0;width:100%;border-collapse:collapse;">
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $jobabRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $cells = array_values((array) ($row['cells'] ?? [])); ?>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cells; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci => $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td
                                class="<?php echo e($ci === 0 ? 'bold' : ''); ?>"
                                style="border:1px solid #333;padding:3px 4px;vertical-align:top;<?php echo e($ci === 0 && count($cells) === 2 ? 'width:38%;' : ''); ?>"
                            ><?php echo e($cell); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/financial-audit-pdf.blade.php ENDPATH**/ ?>