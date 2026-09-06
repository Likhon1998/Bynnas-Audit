<?php
    $stats = $stats ?? [];
    $todayVisits = $todayVisits ?? [];
    $firstName = explode(' ', trim(auth()->user()->name))[0] ?? auth()->user()->name;
    $visitsUrl = route('monthly-visits.index', ['fy' => $fy->label ?? null, 'month' => $monthIndex ?? 0]);
    $reportsUrl = route('audits.index');
    $delayedOrOverdue = max((int) ($stats['delayed'] ?? 0), (int) ($stats['overdue'] ?? 0));
    $cards = [
        [
            'label' => 'Visits today',
            'value' => number_format($stats['visits_today'] ?? 0),
            'meta' => ($todayLabel ?? 'Today').' · on your calendar',
            'href' => $visitsUrl,
            'tone' => 'magenta',
        ],
        [
            'label' => 'Active today',
            'value' => number_format($stats['today_active'] ?? 0),
            'meta' => number_format($stats['today_completed'] ?? 0).' done today',
            'href' => $visitsUrl,
            'tone' => 'fuchsia',
        ],
        [
            'label' => 'Monthly visits',
            'value' => number_format($stats['visits_month'] ?? 0),
            'meta' => ($monthLabel ?? 'This month').' scheduled',
            'href' => $visitsUrl,
            'tone' => 'violet',
        ],
        [
            'label' => 'Shakhas this month',
            'value' => number_format($stats['shakhas_month'] ?? 0),
            'meta' => 'Branches you cover',
            'href' => $visitsUrl,
            'tone' => 'indigo',
        ],
        [
            'label' => 'Planned',
            'value' => number_format($stats['planned'] ?? 0),
            'meta' => 'Not started yet',
            'href' => $visitsUrl,
            'tone' => 'blue',
        ],
        [
            'label' => 'In progress',
            'value' => number_format($stats['in_progress'] ?? 0),
            'meta' => 'Active field work',
            'href' => $visitsUrl,
            'tone' => 'cyan',
        ],
        [
            'label' => 'Completed',
            'value' => number_format($stats['completed'] ?? 0),
            'meta' => ($stats['month_completion_pct'] ?? 0).'% of month',
            'href' => $visitsUrl,
            'tone' => 'sky',
        ],
        [
            'label' => 'Delayed / overdue',
            'value' => number_format($delayedOrOverdue),
            'meta' => 'Needs your follow-up',
            'href' => $visitsUrl,
            'tone' => 'rose',
        ],
        [
            'label' => 'Critical risk',
            'value' => number_format($stats['risk_critical'] ?? (($stats['risk_significant'] ?? 0) + ($stats['risk_high'] ?? 0))),
            'meta' => 'Significant + High in access',
            'href' => $reportsUrl,
            'tone' => 'magenta',
        ],
        [
            'label' => 'Not assessed',
            'value' => number_format($stats['risk_not_assessed'] ?? 0),
            'meta' => 'Need risk score',
            'href' => $reportsUrl,
            'tone' => 'sky',
        ],
        [
            'label' => 'Branch access',
            'value' => number_format($stats['total_access'] ?? 0),
            'meta' => 'Shakhas you can work on',
            'href' => $reportsUrl,
            'tone' => 'indigo',
        ],
        [
            'label' => 'Draft reports',
            'value' => number_format($stats['drafts'] ?? 0),
            'meta' => ($slotsLeft ?? 0).' slots free',
            'href' => $reportsUrl,
            'tone' => 'violet',
        ],
    ];
?>

<div class="px-4 py-5 lg:px-6">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <div class="mb-1 flex items-center gap-2">
                <span class="h-2 w-8 rounded-full bg-gradient-to-r from-[#ff2d9b] via-[#7c3aed] to-[#2563eb]"></span>
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">My work</p>
            </div>
            <h1 class="text-[18px] font-semibold tracking-tight text-navy-900">Hello, <?php echo e($firstName); ?></h1>
            <p class="mt-0.5 text-[12px] text-slate-500">
                <?php echo e(auth()->user()->roleLabel()); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->employee?->position): ?>
                    · <?php echo e(auth()->user()->employee->position->title); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                · FY <?php echo e($fy->label ?? ''); ?>

            </p>
        </div>
        <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-1.5">
            <input type="hidden" name="fy" value="<?php echo e($fy->label ?? ''); ?>">
            <select name="month" class="h-8 rounded-lg border-slate-200 bg-white py-0 text-[12px]" onchange="this.form.submit()">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $monthOptions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($opt['index']); ?>" <?php if((int) $opt['index'] === (int) $monthIndex): echo 'selected'; endif; ?>>
                        <?php echo e($opt['label']); ?> <?php echo e($opt['year']); ?>

                    </option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </form>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! (auth()->user()->employee_id)): ?>
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-[12px] text-amber-900">
            Your login is not linked to an organogram employee — visit allocations cannot appear until Super Admin links you.
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.dashboard-metric-cards', ['cards' => $cards], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="mt-4 overflow-hidden rounded-xl border border-[#e9d5ff]/60 bg-white/95 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-2.5">
            <div>
                <p class="text-[13px] font-semibold text-navy-900">Today’s visits</p>
                <p class="text-[10px] text-slate-500"><?php echo e($todayLabel ?? now('Asia/Dhaka')->format('d M Y')); ?> · <?php echo e(count($todayVisits)); ?> window<?php echo e(count($todayVisits) === 1 ? '' : 's'); ?></p>
            </div>
            <a href="<?php echo e($visitsUrl); ?>" class="text-[11px] font-semibold text-[#7c3aed] hover:underline">Open visits</a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $todayVisits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5">
                    <div class="min-w-0">
                        <p class="truncate text-[12px] font-semibold text-navy-900"><?php echo e($row['label']); ?></p>
                        <p class="truncate text-[10px] text-slate-500"><?php echo e($row['purpose']); ?> · <?php echo e($row['dates']); ?></p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold capitalize <?php echo e($row['tone']['bg']); ?> <?php echo e($row['tone']['text']); ?>"><?php echo e($row['status_label']); ?></span>
                        <a href="<?php echo e($row['execution_url']); ?>" class="inline-flex h-7 items-center rounded-lg bg-gradient-to-r from-[#c026d3] via-[#7c3aed] to-[#2563eb] px-2.5 text-[11px] font-semibold text-white">Open</a>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <p class="px-3.5 py-8 text-center text-[12px] text-slate-400">No visits scheduled for today.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/dashboard-officer.blade.php ENDPATH**/ ?>