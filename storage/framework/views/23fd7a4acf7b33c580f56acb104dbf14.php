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

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($mode ?? 'ops') === 'officer'): ?>
    <?php echo $__env->make('dashboard-officer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <?php
        $visitsUrl = route('monthly-visits.index', ['fy' => $pulse['fy_label'], 'month' => $pulse['month_index']]);
        $shakhasUrl = route('shakhas.index');
        $risk = $pulse['shakha_risk'] ?? [];
        $sights = $pulse['sights'] ?? [];

        // Row 1 — total shakha + risk breakdown
        $row1 = [
            [
                'label' => 'Total shakha',
                'value' => number_format($risk['active'] ?? 0),
                'meta' => 'Active branches',
                'href' => $shakhasUrl,
                'tone' => 'violet',
            ],
            [
                'label' => 'Significant',
                'value' => number_format($risk['significant'] ?? 0),
                'meta' => 'Highest risk',
                'href' => $shakhasUrl,
                'tone' => 'magenta',
            ],
            [
                'label' => 'High',
                'value' => number_format($risk['high'] ?? 0),
                'meta' => 'High risk',
                'href' => $shakhasUrl,
                'tone' => 'fuchsia',
            ],
            [
                'label' => 'Medium',
                'value' => number_format($risk['medium'] ?? 0),
                'meta' => 'Medium risk',
                'href' => $shakhasUrl,
                'tone' => 'indigo',
            ],
            [
                'label' => 'Low',
                'value' => number_format($risk['low'] ?? 0),
                'meta' => 'Low risk',
                'href' => $shakhasUrl,
                'tone' => 'cyan',
            ],
        ];

        // Row 2 — annual / monthly plan shakhas, target %, KPI
        $row2 = [
            [
                'label' => 'Annual plan shakhas',
                'value' => number_format($sights['annual_plan_shakhas'] ?? 0),
                'meta' => 'FY '.$pulse['fy_label'].' · '.($sights['plan_status'] ?? $pulse['plan_status']),
                'href' => route('annual-audit.index'),
                'tone' => 'indigo',
            ],
            [
                'label' => 'Monthly plan shakhas',
                'value' => number_format($sights['monthly_plan_shakhas'] ?? 0),
                'meta' => $pulse['month_label'],
                'href' => $visitsUrl,
                'tone' => 'blue',
            ],
            [
                'label' => 'Annual target achieved',
                'value' => number_format($sights['annual_target_pct'] ?? 0, 1).'%',
                'meta' => number_format($sights['annual_completed_ytd'] ?? 0).' / '.number_format($sights['annual_planned_ytd'] ?? 0).' YTD',
                'href' => route('annual-audit.index'),
                'tone' => 'sky',
            ],
            [
                'label' => 'KPI entered',
                'value' => number_format($sights['kpi_pct'] ?? 0, 1).'%',
                'meta' => number_format($sights['kpi_entered'] ?? 0).' of '.number_format($sights['kpi_total'] ?? 0).' · '.number_format($sights['kpi_missing'] ?? 0).' missing',
                'href' => route('kpis.index'),
                'tone' => 'violet',
            ],
        ];
    ?>

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="mb-1 flex items-center gap-2">
                    <span class="h-2 w-8 rounded-full bg-gradient-to-r from-[#ff2d9b] via-[#7c3aed] to-[#2563eb]"></span>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Bynnas Audit</p>
                </div>
                <h1 class="text-[18px] font-semibold tracking-tight text-navy-900">Dashboard</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">
                    <?php echo e($pulse['period_label']); ?>

                    · plan <span class="font-medium text-slate-700"><?php echo e($pulse['plan_status']); ?></span>
                </p>
            </div>
            <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="flex flex-wrap items-center gap-1.5">
                <select name="fy" class="h-8 rounded-lg border-slate-200 bg-white py-0 text-[12px]" onchange="this.form.submit()">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pulse['fy_options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($fy); ?>" <?php if($fy === $pulse['fy_label']): echo 'selected'; endif; ?>><?php echo e($fy); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <select name="month" class="h-8 rounded-lg border-slate-200 bg-white py-0 text-[12px]" onchange="this.form.submit()">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pulse['month_options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($m['index']); ?>" <?php if($m['index'] === $pulse['month_index']): echo 'selected'; endif; ?>>
                            <?php echo e($m['label']); ?> <?php echo e($m['year']); ?>

                        </option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </form>
        </div>

        <div class="space-y-3">
            <div>
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Shakha risk</p>
                <?php echo $__env->make('partials.dashboard-metric-cards', ['cards' => $row1, 'columns' => 5], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div>
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Plan · target · KPI</p>
                <?php echo $__env->make('partials.dashboard-metric-cards', ['cards' => $row2, 'columns' => 4], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/dashboard.blade.php ENDPATH**/ ?>