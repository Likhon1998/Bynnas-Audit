<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php
        $fmt = function (array $col) {
            $v = $col['value'] ?? null;
            if ($v === null || $v === '') {
                return '—';
            }
            return match ($col['format'] ?? 'text') {
                'int' => number_format((float) $v, 0),
                'money' => number_format((float) $v, 2),
                'pct' => number_format((float) $v, 2).'%',
                'ratio' => number_format((float) $v, 4),
                default => (string) $v,
            };
        };
        $flat = $flat_columns;
        $stickyKey = 'shakha_name';
    ?>

    <div class="px-4 py-5 lg:px-6 print:px-0">
        
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3 print:hidden">
            <div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="<?php echo e(route('shakhas.index')); ?>" class="hover:text-brand-600">All Shakha</a>
                    <span>/</span>
                    <a href="<?php echo e(route('shakhas.kpis.create', ['shakha' => $shakha, 'month' => $month, 'year' => $year])); ?>" class="hover:text-brand-600">KPI Input</a>
                    <span>/</span>
                    <span class="text-slate-600">Report</span>
                </div>
                <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">Monthly KPI Report</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">Professional branch performance document · calculated metrics are live (not stored)</p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="<?php echo e(route('shakhas.kpis.create', ['shakha' => $shakha, 'month' => $month, 'year' => $year])); ?>" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Edit raw data</a>
                <button type="button" onclick="window.print()" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Print / PDF</button>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800 print:hidden"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card print:rounded-none print:border-slate-300 print:shadow-none">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-sky-50 px-6 py-5 print:bg-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-navy-900 text-[11px] font-bold tracking-wide text-white print:border print:border-slate-300 print:bg-white print:text-navy-900">
                            BA
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Bynnas Audit</p>
                            <h2 class="mt-0.5 text-[18px] font-semibold tracking-tight text-navy-900">Shakha Monthly KPI Statement</h2>
                            <p class="mt-1 text-[12px] text-slate-500">Confidential management report · FY <?php echo e($fy_totals['fy_label'] ?? '—'); ?></p>
                        </div>
                    </div>
                    <div class="text-right text-[12px]">
                        <p class="font-semibold text-navy-900"><?php echo e($period_label); ?></p>
                        <p class="mt-0.5 text-slate-500">Generated <?php echo e(now()->format('d M Y, h:i A')); ?></p>
                        <p class="mt-0.5 text-slate-400">Document ref: KPI-<?php echo e($shakha->id); ?>-<?php echo e($year); ?><?php echo e(str_pad((string) $month, 2, '0', STR_PAD_LEFT)); ?></p>
                    </div>
                </div>
            </div>

            <div class="grid gap-0 border-b border-slate-100 sm:grid-cols-4">
                <div class="border-b border-slate-100 px-5 py-3 sm:border-b-0 sm:border-r">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Shakha</p>
                    <p class="mt-0.5 text-[13px] font-semibold text-navy-900"><?php echo e($shakha->name); ?></p>
                </div>
                <div class="border-b border-slate-100 px-5 py-3 sm:border-b-0 sm:border-r">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Area</p>
                    <p class="mt-0.5 text-[13px] font-medium text-slate-700"><?php echo e($shakha->area?->name ?? '—'); ?></p>
                </div>
                <div class="border-b border-slate-100 px-5 py-3 sm:border-b-0 sm:border-r">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Division</p>
                    <p class="mt-0.5 text-[13px] font-medium text-slate-700"><?php echo e($shakha->area?->division ?? '—'); ?></p>
                </div>
                <div class="px-5 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Branch code</p>
                    <p class="mt-0.5 text-[13px] font-medium text-slate-700"><?php echo e($shakha->code ?: '—'); ?></p>
                </div>
            </div>

            
            <div class="grid gap-px bg-slate-100 sm:grid-cols-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                    ['OTR', $metrics['otr'] ?? null, 'pct'],
                    ['PAR', $metrics['par'] ?? null, 'pct'],
                    ['Dropout %', $metrics['dropout_pct'] ?? null, 'pct'],
                    ['Savings : Loan', $metrics['savings_loan_ratio'] ?? null, 'ratio'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $type]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="bg-white px-5 py-3.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400"><?php echo e($label); ?></p>
                        <p class="mt-1 text-[20px] font-semibold tracking-tight text-navy-900">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($value === null): ?>
                                —
                            <?php elseif($type === 'pct'): ?>
                                <?php echo e(number_format($value * 100, 2)); ?>%
                            <?php else: ?>
                                <?php echo e(number_format($value, 4)); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        
        <div class="mb-4 space-y-3 print:space-y-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($section['title'] === 'Identity'): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php continue; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card print:shadow-none">
                    <div class="border-b border-slate-100 bg-slate-50/90 px-4 py-2.5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500"><?php echo e($section['title']); ?></p>
                    </div>
                    <div class="grid gap-0 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $section['columns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="border-b border-r border-slate-50 px-4 py-2.5">
                                <p class="text-[10px] font-medium text-slate-400"><?php echo e($col['label']); ?></p>
                                <p class="mt-0.5 whitespace-nowrap text-[13px] font-semibold tabular-nums text-navy-900"><?php echo e($fmt($col)); ?></p>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card print:shadow-none">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
                <div>
                    <p class="text-[13px] font-semibold text-navy-900">Master KPI matrix</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Scroll horizontally · first column stays locked · <?php echo e(count($flat)); ?> indicators</p>
                </div>
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500 print:hidden"><?php echo e(count($flat)); ?> columns</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-max border-collapse text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $flat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <th
                                    class="whitespace-nowrap px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500
                                        <?php echo e(($col['key'] ?? '') === $stickyKey ? 'sticky left-0 z-20 bg-slate-50 shadow-[2px_0_4px_-2px_rgba(15,23,42,0.12)]' : ''); ?>"
                                >
                                    <?php echo e($col['label']); ?>

                                </th>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-slate-100 hover:bg-sky-50/40">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $flat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td
                                    class="whitespace-nowrap px-3 py-2.5 tabular-nums text-slate-700
                                        <?php echo e(($col['key'] ?? '') === $stickyKey ? 'sticky left-0 z-10 bg-white font-semibold text-navy-900 shadow-[2px_0_4px_-2px_rgba(15,23,42,0.12)]' : ''); ?>"
                                >
                                    <?php echo e($fmt($col)); ?>

                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="mt-4 rounded-xl border border-slate-200 bg-white px-5 py-4 print:break-inside-avoid">
            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Calculation notes</p>
            <div class="mt-3 grid gap-2 text-[11px] text-slate-600 sm:grid-cols-2">
                <p><span class="font-semibold text-navy-900">OTR</span> = Current recovery ÷ Recoverable</p>
                <p><span class="font-semibold text-navy-900">PAR</span> = Due loanee loan OS ÷ Loan outstanding</p>
                <p><span class="font-semibold text-navy-900">Due Recovery %</span> = (Recoverable − Current recovery) ÷ Total OD taka</p>
                <p><span class="font-semibold text-navy-900">Member : Loanee</span> = Total borrowers ÷ Total members</p>
                <p><span class="font-semibold text-navy-900">Savings : Loan OS</span> = Savings balance ÷ Loan outstanding</p>
                <p><span class="font-semibold text-navy-900">Dropout %</span> = Members dropout ÷ Members admitted</p>
                <p><span class="font-semibold text-navy-900">Samities : Member</span> = Total members ÷ Total samities</p>
                <p><span class="font-semibold text-navy-900">FO : Member</span> = Total members ÷ Field officer count</p>
                <p class="sm:col-span-2 text-slate-500">FY cumulatives sum monthly activity from 1 July of the fiscal year through the selected month. Division by zero yields “—”.</p>
            </div>
            <div class="mt-4 flex flex-wrap justify-between gap-4 border-t border-slate-100 pt-4 text-[11px] text-slate-400">
                <p>Prepared for internal audit &amp; branch performance review.</p>
                <p>Bynnas Audit · <?php echo e($shakha->name); ?> · <?php echo e($period_label); ?></p>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($periods->isNotEmpty()): ?>
            <div class="mt-4 print:hidden">
                <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Other periods</p>
                <div class="flex flex-wrap gap-1.5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a
                            href="<?php echo e(route('shakhas.kpis.show', [$shakha, $period->report_month, $period->report_year])); ?>"
                            class="rounded-full border px-2.5 py-1 text-[11px] font-medium
                                <?php echo e($period->report_month === $month && $period->report_year === $year
                                    ? 'border-navy-900 bg-navy-900 text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'); ?>"
                        ><?php echo e($period->periodLabel()); ?></a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <style>
        @media print {
            .sidebar-shell, aside, nav, header, .print\\:hidden { display: none !important; }
            body { background: white !important; }
            main, .min-h-screen { margin: 0 !important; padding: 0 !important; }
        }
    </style>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\shakhas\kpis\report.blade.php ENDPATH**/ ?>