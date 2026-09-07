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
        $deltaBadge = function (array $delta): array {
            if (($delta['direction'] ?? 'flat') === 'up') {
                return ['class' => 'text-rose-600', 'text' => '▲ '.(($delta['diff'] ?? 0) > 0 ? '+' : '').number_format((float) ($delta['diff'] ?? 0))];
            }
            if (($delta['direction'] ?? 'flat') === 'down') {
                return ['class' => 'text-emerald-600', 'text' => '▼ '.number_format((float) ($delta['diff'] ?? 0))];
            }

            return ['class' => 'text-slate-400', 'text' => '● flat vs '.$brief['prev_period_label']];
        };

        $maxCategoryIrregs = max(1, (int) $categoryCards->max('irregularities'));
        $maxCategoryAmount = max(1.0, (float) $categoryCards->max('amount'));
        $dIrreg = $deltaBadge($brief['deltas']['irregularities'] ?? []);
        $dAmount = $deltaBadge($brief['deltas']['amount'] ?? []);
        $dBranches = $deltaBadge($brief['deltas']['branches'] ?? []);
        $dIndicators = $deltaBadge($brief['deltas']['indicators_hit'] ?? []);
    ?>

    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Findings Summary</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Authority brief · <?php echo e($periodLabel); ?></p>
                <?php echo $__env->make('audit-findings.partials.view-tabs', ['activeTab' => 'summary', 'month' => $month, 'year' => $year], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div class="ml-auto flex flex-wrap items-center justify-end gap-1.5">
                <form method="GET" action="<?php echo e(route('audit-findings.summary')); ?>" class="flex flex-wrap items-center gap-1.5">
                    <select name="month" class="h-8 rounded-md border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($m = 1; $m <= 12; $m++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($m); ?>" <?php if($m === $month): echo 'selected'; endif; ?>><?php echo e(date('F', mktime(0, 0, 0, $m, 1))); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <select name="year" class="h-8 rounded-md border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $yearOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($y); ?>" <?php if($y === $year): echo 'selected'; endif; ?>><?php echo e($y); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </form>
                <a href="<?php echo e($exportUrl); ?>" class="inline-flex h-9 items-center gap-1.5 rounded-md bg-emerald-700 px-3 text-[12px] font-semibold text-white hover:bg-emerald-800">Excel</a>
                <a href="<?php echo e($exportPptUrl); ?>" class="inline-flex h-9 items-center gap-1.5 rounded-md bg-[#c43e1c] px-3 text-[12px] font-semibold text-white hover:bg-[#a83316]">Download PPT</a>
            </div>
        </div>

        
        <div class="mb-3 grid grid-cols-4 gap-1 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm sm:grid-cols-6 lg:grid-cols-12">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $monthStrip; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a
                    href="<?php echo e($chip['url']); ?>"
                    class="rounded-md px-1 py-1.5 text-center text-[11px] font-semibold transition
                        <?php echo e($chip['active'] ? 'bg-navy-900 text-white' : ($chip['has_data'] ? 'bg-sky-50 text-sky-900 hover:bg-sky-100' : 'text-slate-400 hover:bg-slate-50')); ?>"
                ><?php echo e($chip['label']); ?></a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        
        <section class="relative mb-3 overflow-hidden rounded-2xl border border-slate-800 bg-[#0B1F36] text-white shadow-lg">
            <div class="pointer-events-none absolute inset-0 opacity-40" style="background:
                radial-gradient(ellipse 70% 80% at 100% 0%, rgba(14,165,164,.35), transparent 55%),
                radial-gradient(ellipse 50% 60% at 0% 100%, rgba(43,87,154,.4), transparent 50%);"></div>
            <div class="relative grid gap-4 p-4 lg:grid-cols-[1.4fr_1fr] lg:p-5">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-teal-300/90">DSK Internal Audit · Monthly brief</p>
                    <h2 class="mt-1.5 text-[22px] font-semibold leading-tight tracking-tight sm:text-[26px]"><?php echo e($periodLabel); ?></h2>
                    <p class="mt-2 max-w-2xl text-[13px] leading-relaxed text-slate-300"><?php echo e($brief['headline']); ?></p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-semibold text-teal-100 ring-1 ring-white/10">
                            Coverage <?php echo e(number_format((float) ($brief['coverage_pct'] ?? 0), 1)); ?>% of active shakhas
                        </span>
                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-semibold text-slate-200 ring-1 ring-white/10">
                            vs <?php echo e($brief['prev_period_label']); ?>

                        </span>
                        <a href="<?php echo e($matrixUrl); ?>" class="rounded-full bg-teal-500/20 px-2.5 py-1 text-[10px] font-semibold text-teal-100 ring-1 ring-teal-400/30 hover:bg-teal-500/30">Open full matrix →</a>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Irregularities</p>
                        <p class="mt-1 text-[22px] font-semibold tabular-nums tracking-tight"><?php echo e(number_format((int) ($brief['total_irregularities'] ?? 0))); ?></p>
                        <p class="mt-0.5 text-[10px] font-medium <?php echo e($dIrreg['class']); ?>"><?php echo e($dIrreg['text']); ?></p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Amount (BDT)</p>
                        <p class="mt-1 text-[18px] font-semibold tabular-nums tracking-tight"><?php echo e($brief['total_amount_fmt'] ?? '0.00'); ?></p>
                        <p class="mt-0.5 text-[10px] font-medium <?php echo e($dAmount['class']); ?>"><?php echo e($dAmount['text']); ?></p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Branches hit</p>
                        <p class="mt-1 text-[22px] font-semibold tabular-nums tracking-tight"><?php echo e(number_format((int) ($brief['branches_with_findings'] ?? 0))); ?></p>
                        <p class="mt-0.5 text-[10px] font-medium <?php echo e($dBranches['class']); ?>"><?php echo e($dBranches['text']); ?></p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3 ring-1 ring-white/10 backdrop-blur-sm">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Indicators hit</p>
                        <p class="mt-1 text-[22px] font-semibold tabular-nums tracking-tight"><?php echo e(number_format((int) ($brief['indicators_hit'] ?? 0))); ?></p>
                        <p class="mt-0.5 text-[10px] font-medium <?php echo e($dIndicators['class']); ?>"><?php echo e($dIndicators['text']); ?></p>
                    </div>
                </div>
            </div>
        </section>

        
        <div class="mb-3 grid gap-2 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white px-3.5 py-3 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Samples checked</p>
                <p class="mt-1 text-[18px] font-semibold tabular-nums text-navy-900"><?php echo e(number_format((int) ($brief['total_samples'] ?? 0))); ?></p>
                <p class="mt-0.5 text-[11px] text-slate-500">Defect rate <?php echo e(number_format((float) ($brief['defect_rate'] ?? 0), 1)); ?>%</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3.5 py-3 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Major-risk indicators</p>
                <p class="mt-1 text-[18px] font-semibold tabular-nums text-rose-700"><?php echo e(number_format((int) ($brief['major_risk_hits'] ?? 0))); ?></p>
                <p class="mt-0.5 text-[11px] text-slate-500">Flagged in this period</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3.5 py-3 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Category groups</p>
                <p class="mt-1 text-[18px] font-semibold tabular-nums text-navy-900"><?php echo e($categoryCards->count()); ?></p>
                <p class="mt-0.5 text-[11px] text-slate-500"><?php echo e($flatRows->count()); ?> indicator rows in detail</p>
            </div>
        </div>

        <div class="mb-3 grid gap-3 xl:grid-cols-2">
            
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-3.5 py-2.5">
                    <div>
                        <p class="text-[12px] font-semibold text-navy-900">Priority issues</p>
                        <p class="text-[10px] text-slate-500">Ranked by irregularities</p>
                    </div>
                    <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">Top <?php echo e(min(8, count($brief['issue_rows'] ?? []))); ?></span>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = array_slice($brief['issue_rows'] ?? [], 0, 8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $pct = min(100, ((int) $issue['irregularities'] / max(1, (int) $brief['max_issue_irregularities'])) * 100); ?>
                        <a href="<?php echo e($issue['url']); ?>" class="block px-3.5 py-2.5 transition hover:bg-slate-50/80">
                            <div class="flex items-start gap-2.5">
                                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#0B1F36] text-[10px] font-bold text-white"><?php echo e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[12px] font-semibold text-slate-800"><?php echo e($issue['title']); ?></p>
                                    <p class="mt-0.5 text-[10px] text-slate-400"><?php echo e($issue['code']); ?> · <?php echo e($issue['category']); ?></p>
                                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-gradient-to-r from-rose-500 to-amber-400" style="width: <?php echo e($pct); ?>%"></div>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-[13px] font-semibold tabular-nums text-rose-700"><?php echo e(number_format((int) $issue['irregularities'])); ?></p>
                                    <p class="text-[10px] tabular-nums text-slate-400">৳ <?php echo e($issue['amount_fmt']); ?></p>
                                </div>
                            </div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="px-3.5 py-10 text-center text-[12px] text-slate-400">No priority issues this month.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-3.5 py-2.5">
                    <div>
                        <p class="text-[12px] font-semibold text-navy-900">Branches under observation</p>
                        <p class="text-[10px] text-slate-500">Heat by irregularity volume</p>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = array_slice($brief['branch_rows'] ?? [], 0, 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $pct = min(100, ((int) $branch['irregularities'] / max(1, (int) $brief['max_branch_irregularities'])) * 100); ?>
                        <div class="px-3.5 py-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-[12px] font-semibold text-slate-800"><?php echo e($branch['name']); ?></p>
                                    <p class="text-[10px] text-slate-400"><?php echo e($branch['code'] ?: '—'); ?> · <?php echo e(number_format((int) $branch['cells'])); ?> cells · defect <?php echo e(number_format((float) $branch['defect_rate'], 1)); ?>%</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-[12px] font-semibold tabular-nums text-navy-900"><?php echo e(number_format((int) $branch['irregularities'])); ?></p>
                                    <p class="text-[10px] tabular-nums text-slate-400">৳ <?php echo e($branch['amount_fmt']); ?></p>
                                </div>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#0B1F36] to-teal-500" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="px-3.5 py-10 text-center text-[12px] text-slate-400">No branch findings this month.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </div>

        
        <section class="mb-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-[#0B1F36] px-3.5 py-3 text-white">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-300/90">DSK Internal Audit</p>
                        <h3 class="mt-0.5 text-[15px] font-semibold tracking-tight">Category coverage</h3>
                    </div>
                    <p class="text-[11px] text-slate-300">Underheading breakdown · <?php echo e($periodLabel); ?></p>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categoryCards->isEmpty()): ?>
                <p class="px-3.5 py-12 text-center text-[12px] text-slate-400">No category coverage yet for <?php echo e($periodLabel); ?>.</p>
            <?php else: ?>
                <div class="grid gap-3 p-3 sm:grid-cols-2 xl:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categoryCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $barIrreg = min(100, ($card['irregularities'] / $maxCategoryIrregs) * 100);
                            $barAmount = min(100, ($card['amount'] / $maxCategoryAmount) * 100);
                        ?>
                        <article class="rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50/80 to-white p-3.5 shadow-sm">
                            <p class="text-[12px] font-semibold leading-snug text-navy-900"><?php echo e($card['category']); ?></p>
                            <p class="mt-0.5 text-[10px] text-slate-400"><?php echo e($card['sub_category'] !== '—' ? $card['sub_category'] : 'No sub-heading'); ?></p>

                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <div>
                                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">Indicators</p>
                                    <p class="text-[15px] font-semibold tabular-nums text-slate-800"><?php echo e($card['indicator_count']); ?></p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">Irregular</p>
                                    <p class="text-[15px] font-semibold tabular-nums text-rose-700"><?php echo e(number_format($card['irregularities'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">Rate</p>
                                    <p class="text-[15px] font-semibold tabular-nums text-amber-700"><?php echo e(number_format($card['rate'], 1)); ?>%</p>
                                </div>
                            </div>

                            <div class="mt-3 space-y-1.5">
                                <div>
                                    <div class="mb-0.5 flex justify-between text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                                        <span>Irregularity load</span><span><?php echo e(number_format($card['irregularities'])); ?></span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-rose-500" style="width: <?php echo e($barIrreg); ?>%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-0.5 flex justify-between text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                                        <span>Amount</span><span>৳ <?php echo e($card['amount_fmt']); ?></span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-teal-500" style="width: <?php echo e($barAmount); ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <p class="mt-2.5 text-[10px] text-slate-500">
                                Sample <?php echo e(number_format($card['samples'])); ?> · branch mentions <?php echo e(number_format($card['branch_mentions'])); ?>

                            </p>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-2.5">
                <div>
                    <p class="text-[12px] font-semibold text-navy-900">Indicator ledger</p>
                    <p class="text-[10px] text-slate-500">Every matrix heading used in <?php echo e($periodLabel); ?> reports</p>
                </div>
                <span class="text-[10px] font-semibold text-slate-400"><?php echo e($flatRows->count()); ?> rows</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#0B1F36] text-[10px] font-semibold uppercase tracking-wide text-slate-200">
                            <th class="px-3 py-2.5">#</th>
                            <th class="px-3 py-2.5">Heading</th>
                            <th class="px-3 py-2.5">Code</th>
                            <th class="px-3 py-2.5 min-w-[220px]">Indicator</th>
                            <th class="px-3 py-2.5 text-right">Amount</th>
                            <th class="px-3 py-2.5 text-right">Sample</th>
                            <th class="px-3 py-2.5 text-right">Irreg.</th>
                            <th class="px-3 py-2.5 text-right">%</th>
                            <th class="px-3 py-2.5 text-right">Branches</th>
                            <th class="px-3 py-2.5 min-w-[180px]">Branch names</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $flatRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $rate = (float) ($row['percentage'] ?? 0);
                                $rateClass = $rate >= 20 ? 'bg-rose-50 text-rose-700' : ($rate >= 10 ? 'bg-amber-50 text-amber-700' : 'bg-slate-50 text-slate-600');
                            ?>
                            <tr class="<?php echo e($index % 2 === 0 ? 'bg-white' : 'bg-slate-50/60'); ?> hover:bg-sky-50/40">
                                <td class="px-3 py-2.5 tabular-nums text-slate-400"><?php echo e($index + 1); ?></td>
                                <td class="px-3 py-2.5 align-top">
                                    <p class="font-medium text-slate-800"><?php echo e($row['category']); ?></p>
                                    <p class="text-[10px] text-slate-400"><?php echo e($row['sub_category']); ?></p>
                                </td>
                                <td class="px-3 py-2.5 align-top font-mono text-[11px] text-slate-500 whitespace-nowrap"><?php echo e($row['code']); ?></td>
                                <td class="px-3 py-2.5 align-top">
                                    <a href="<?php echo e($row['url']); ?>" class="font-semibold text-[#2b579a] hover:underline"><?php echo e($row['title']); ?></a>
                                </td>
                                <td class="px-3 py-2.5 text-right align-top tabular-nums whitespace-nowrap"><?php echo e($row['amount_fmt']); ?></td>
                                <td class="px-3 py-2.5 text-right align-top tabular-nums"><?php echo e(number_format($row['samples'])); ?></td>
                                <td class="px-3 py-2.5 text-right align-top text-[13px] font-semibold tabular-nums text-rose-700"><?php echo e(number_format($row['irregularities'])); ?></td>
                                <td class="px-3 py-2.5 text-right align-top">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold <?php echo e($rateClass); ?>"><?php echo e($row['percentage_fmt']); ?></span>
                                </td>
                                <td class="px-3 py-2.5 text-right align-top tabular-nums font-semibold"><?php echo e(number_format($row['branch_count'])); ?></td>
                                <td class="px-3 py-2.5 align-top text-[11px] leading-snug text-slate-600"><?php echo e($row['branches'] !== '' ? $row['branches'] : '—'); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="10" class="px-3 py-12 text-center text-[12px] text-slate-400">
                                    No report findings for <?php echo e($periodLabel); ?>. Complete an audit report first.
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audit-findings\summary.blade.php ENDPATH**/ ?>