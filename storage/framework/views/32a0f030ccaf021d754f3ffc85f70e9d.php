<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>Monthly Schedule — <?php echo e($monthLabel); ?> · FY <?php echo e($plan->fy_label); ?></title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.35;
            background: #fff;
        }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .toolbar a, .toolbar button {
            display: inline-flex;
            align-items: center;
            height: 32px;
            padding: 0 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .toolbar .primary { background: #059669; border-color: #059669; color: #fff; }
        .sheet { padding: 18px 20px 28px; }
        .org {
            text-align: center;
            margin-bottom: 6px;
        }
        .org-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .org-sub {
            font-size: 12px;
            color: #334155;
            margin-top: 2px;
        }
        .title {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            margin: 10px 0 4px;
        }
        .meta {
            text-align: center;
            font-size: 12px;
            margin-bottom: 14px;
            color: #1e293b;
        }
        table.schedule {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.schedule th,
        table.schedule td {
            border: 1px solid #334155;
            padding: 5px 6px;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.schedule th {
            background: #e2e8f0;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
        }
        table.schedule td {
            font-size: 11px;
        }
        .c { text-align: center; }
        .visitors { white-space: pre-line; font-weight: 600; }
        .remarks {
            text-align: center;
            font-weight: 700;
            writing-mode: horizontal-tb;
            background: #f8fafc;
        }
        .muted { color: #64748b; font-style: italic; }
        .footer-note {
            margin-top: 10px;
            font-size: 10px;
            color: #64748b;
        }
        @media print {
            .toolbar { display: none !important; }
            .sheet { padding: 0; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! (!empty($forDoc) || !empty($forPdf))): ?>
        <div class="toolbar">
            <div>
                <strong>Monthly plan printout</strong>
                <span style="color:#64748b;margin-left:8px;">FY <?php echo e($plan->fy_label); ?> · <?php echo e($monthLabel); ?> · <?php echo e(count($rows)); ?> rows</span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?php echo e(route('monthly-visits.index', ['fy' => $plan->fy_label, 'month' => $monthIndex])); ?>">← Back</a>
                <button type="button" onclick="window.print()" class="primary">Print / Save PDF</button>
                <a href="<?php echo e(route('monthly-visits.schedule.pdf', ['fy' => $plan->fy_label, 'month' => $monthIndex])); ?>" class="primary">Download PDF</a>
                <a href="<?php echo e(route('monthly-visits.schedule.doc', ['fy' => $plan->fy_label, 'month' => $monthIndex])); ?>">Download DOC</a>
                <a href="<?php echo e(route('monthly-visits.schedule.excel', ['fy' => $plan->fy_label, 'month' => $monthIndex])); ?>">Download Excel</a>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="sheet">
        <div class="org">
            <div class="org-name">DSK — Dushtha Shasthya Kendra</div>
            <div class="org-sub">দুস্থ স্বাস্থ্য কেন্দ্র</div>
        </div>
        <div class="title">পরিবীক্ষণ ও নিরীক্ষা বিষয়ক মাসিক সিডিউল</div>
        <div class="title" style="font-size:12px;font-weight:600;margin-top:0;">Monthly Schedule for Monitoring and Audit</div>
        <div class="meta">
            অর্থ বৎসর / FY: <strong><?php echo e($plan->fy_label); ?></strong>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            মাসের নাম / Month: <strong><?php echo e($monthLabelBn); ?> (<?php echo e($monthLabel); ?>)</strong>
        </div>

        <table class="schedule">
            <thead>
                <tr>
                    <th style="width:4%">ক্র.নং<br>SL</th>
                    <th style="width:16%">পরিদর্শনকারীর নাম<br>Visitor Name</th>
                    <th style="width:14%">যে মাস পর্যন্ত নিরীক্ষা ও পরিবীক্ষণ করা হয়েছে<br>Last Audit Upto</th>
                    <th style="width:22%">শাখার নাম<br>Branch / Entity</th>
                    <th style="width:18%">পরিদর্শনের তারিখ ও মাস<br>Visit Date &amp; Month</th>
                    <th style="width:8%">দিন<br>Days</th>
                    <th style="width:12%">মন্তব্য<br>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php $groupMap = collect($groups)->keyBy('start'); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="c"><?php echo e($row['sl']); ?></td>
                        <td class="visitors <?php echo e($row['visitors'] === '—' ? 'muted' : ''); ?>"><?php echo e($row['visitors']); ?></td>
                        <td class="c"><?php echo e($row['last_audit_upto_bn']); ?></td>
                        <td>
                            <?php echo e($row['entity']); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['is_special']): ?>
                                <span class="muted">(Special)</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="c <?php echo e($row['visit_dates'] === 'Not allocated' ? 'muted' : ''); ?>"><?php echo e($row['visit_dates']); ?></td>
                        <td class="c"><?php echo e($row['days']); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($groupMap->has($i)): ?>
                            <?php $g = $groupMap[$i]; ?>
                            <td class="remarks" rowspan="<?php echo e($g['count']); ?>">
                                <?php echo e($g['purpose_bn']); ?>

                                <div style="font-size:9px;font-weight:600;margin-top:2px;color:#475569;"><?php echo e($g['purpose']); ?></div>
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="7" class="c muted" style="padding:24px;">No planned offices for this month.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <div class="footer-note">
            Includes all yearly-plan offices for the month — allocated and not yet allocated.
            Generated <?php echo e(now()->format('d M Y H:i')); ?>.
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\monthly-visits\print-schedule.blade.php ENDPATH**/ ?>