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

    <?php elseif($type === 'compliance_table'): ?>
        <?php
            $coreFields = ['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no'];
            $headers = array_values((array) ($block['headers'] ?? \App\Support\AuditTableHeaders::defaults()['compliance']));
            if (count($headers) < 6) {
                $headers = array_values(\App\Support\AuditTableHeaders::defaults()['compliance']);
            }
            $extraCount = max(0, count($headers) - count($coreFields));
            $complianceRows = array_values((array) ($block['rows'] ?? []));
        ?>
        <?php echo $__env->make('audits.partials.compliance-heading', ['block' => $block, 'forDoc' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <table class="doc-table" style="margin-bottom:5mm;font-size:7px;">
            <thead>
                <tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th><?php echo e($header); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $complianceRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $coreFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="<?php echo e(in_array($field, ['prev_para_no', 'first_discovery_period', 'current_para_no'], true) ? 'center' : ''); ?>"><?php echo e($row[$field] ?? ''); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($ei = 0; $ei < $extraCount; $ei++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td><?php echo e($row['extra'][$ei] ?? ''); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>

    <?php elseif($type === 'it_checklist'): ?>
        <?php
            $hItR1 = array_values((array) ($block['headers_r1'] ?? \App\Support\AuditTableHeaders::defaults()['it_r1']));
            $hItR2 = array_values((array) ($block['headers_r2'] ?? \App\Support\AuditTableHeaders::defaults()['it_r2']));
            $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));
            $itRows = array_values((array) ($block['rows'] ?? []));
        ?>
        <div style="text-align:center;margin:3mm 0;">
            <p class="bold" style="margin:0 0 1mm;font-size:10px;"><?php echo \App\Support\BanglaNumerals::highlight($block['title'] ?? '', 'serial'); ?></p>
            <p style="margin:0;font-size:9px;line-height:1.4;"><?php echo e($block['org_line1'] ?? ''); ?></p>
            <p style="margin:0;font-size:9px;line-height:1.4;"><?php echo e($block['org_line2'] ?? ''); ?></p>
            <p style="margin:0 0 1.5mm;font-size:9px;line-height:1.4;"><?php echo e($block['org_line3'] ?? ''); ?></p>
            <p style="margin:0 0 1mm;font-size:8px;">
                <span class="bold">কর্মসূচীর নাম :</span> <?php echo e($block['program'] ?? ''); ?>

                &nbsp;&nbsp;
                <span class="bold">শাখার নাম :</span> <?php echo e($block['branch'] ?? ''); ?>

            </p>
            <p class="bold" style="margin:0 0 2mm;font-size:8px;"><?php echo e($block['instruction'] ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন'); ?></p>
        </div>
        <table class="doc-table" style="margin-bottom:5mm;font-size:7px;">
            <thead>
                <tr>
                    <th rowspan="2"><?php echo e($hItR1[0] ?? 'ক্রমিক'); ?></th>
                    <th rowspan="2"><?php echo e($hItR1[1] ?? 'বিবরণ'); ?></th>
                    <th colspan="3"><?php echo e($hItR1[2] ?? 'Compliance'); ?></th>
                    <th rowspan="2"><?php echo e($hItR1[3] ?? 'Action Owner'); ?></th>
                    <th rowspan="2"><?php echo e($hItR1[4] ?? 'Management Comments'); ?></th>
                    <th rowspan="2"><?php echo e($hItR1[5] ?? 'Recommendation'); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th rowspan="2"><?php echo e($eh); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tr>
                <tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hItR2; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th><?php echo e($label); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $itRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $extraHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ei => $unused): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td><?php echo e($row['extra'][$ei] ?? ''); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>

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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\financial-audit-pdf.blade.php ENDPATH**/ ?>