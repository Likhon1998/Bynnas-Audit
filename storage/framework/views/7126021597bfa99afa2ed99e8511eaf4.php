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
        /** @var array<string, mixed> $brief */
        $deltaBadge = function (array $delta): array {
            if (($delta['direction'] ?? 'flat') === 'up') {
                return ['class' => 'bg-rose-50 text-rose-700', 'text' => '▲ '.($delta['diff'] > 0 ? '+' : '').$delta['diff']];
            }
            if (($delta['direction'] ?? 'flat') === 'down') {
                return ['class' => 'bg-emerald-50 text-emerald-700', 'text' => '▼ '.$delta['diff']];
            }

            return ['class' => 'bg-slate-100 text-slate-500', 'text' => '● 0'];
        };
    ?>

    <div class="px-3 py-3 lg:px-5">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Authority brief</p>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Monthly irregularities summary</h1>
                <p class="mt-0.5 text-[12px] text-slate-500"><?php echo e($brief['headline']); ?></p>
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
                <a href="<?php echo e($matrixUrl); ?>" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">Full matrix</a>
                <a
                    href="<?php echo e($exportUrl); ?>"
                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-md bg-emerald-700 px-3.5 text-[12px] font-semibold text-white shadow-sm hover:bg-emerald-800"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    Download <?php echo e($brief['period_label']); ?>

                </a>
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

        
        <div class="mb-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <?php $dIrreg = $deltaBadge($brief['deltas']['irregularities']); ?>
            <div class="rounded-xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white px-3.5 py-3 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-700">Irregularities</p>
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold <?php echo e($dIrreg['class']); ?>"><?php echo e($dIrreg['text']); ?></span>
                </div>
                <p class="mt-1 text-[28px] font-bold tabular-nums leading-none text-rose-900"><?php echo e(number_format($brief['total_irregularities'])); ?></p>
                <p class="mt-1.5 text-[10px] text-rose-700/80">vs <?php echo e($brief['prev_period_label']); ?></p>
            </div>

            <?php $dAmt = $deltaBadge($brief['deltas']['amount']); ?>
            <div class="rounded-xl border border-slate-200 bg-white px-3.5 py-3 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Amount (৳)</p>
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold <?php echo e($dAmt['class']); ?>"><?php echo e($dAmt['text']); ?></span>
                </div>
                <p class="mt-1 text-[22px] font-bold tabular-nums leading-none text-navy-900"><?php echo e($brief['total_amount_fmt']); ?></p>
                <p class="mt-1.5 text-[10px] text-slate-400">Under observation this month</p>
            </div>

            <?php $dBr = $deltaBadge($brief['deltas']['branches']); ?>
            <div class="rounded-xl border border-slate-200 bg-white px-3.5 py-3 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Branches hit</p>
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold <?php echo e($dBr['class']); ?>"><?php echo e($dBr['text']); ?></span>
                </div>
                <p class="mt-1 text-[22px] font-bold tabular-nums leading-none text-navy-900"><?php echo e($brief['branches_with_findings']); ?></p>
                <p class="mt-1.5 text-[10px] text-slate-400"><?php echo e($brief['coverage_pct']); ?>% of <?php echo e($brief['active_shakhas']); ?> active shakhas</p>
            </div>

            <?php $dHit = $deltaBadge($brief['deltas']['indicators_hit']); ?>
            <div class="rounded-xl border border-slate-200 bg-white px-3.5 py-3 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Issues / risk</p>
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold <?php echo e($dHit['class']); ?>"><?php echo e($dHit['text']); ?></span>
                </div>
                <p class="mt-1 text-[22px] font-bold tabular-nums leading-none text-navy-900"><?php echo e($brief['indicators_hit']); ?></p>
                <p class="mt-1.5 text-[10px] text-slate-400"><?php echo e($brief['major_risk_hits']); ?> major · defect <?php echo e($brief['defect_rate']); ?>%</p>
            </div>
        </div>

        <div class="grid gap-3 lg:grid-cols-5">
            
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-3">
                <div class="flex items-center justify-between border-b border-slate-100 px-3 py-2">
                    <div>
                        <p class="text-[13px] font-semibold text-navy-900">Branches to watch</p>
                        <p class="text-[10px] text-slate-400">Ranked by irregularities · <?php echo e($brief['period_label']); ?></p>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $brief['branch_rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <span class="w-5 text-[11px] font-semibold tabular-nums text-slate-400"><?php echo e($i + 1); ?></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-[12px] font-semibold text-navy-900">
                                        <?php echo e($row['name']); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['code'] !== ''): ?>
                                            <span class="font-mono text-[10px] font-normal text-slate-400"><?php echo e($row['code']); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </p>
                                    <p class="shrink-0 text-[12px] font-bold tabular-nums text-rose-700"><?php echo e($row['irregularities']); ?></p>
                                </div>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-rose-500" style="width: <?php echo e(min(100, ($row['irregularities'] / max(1, $brief['max_branch_irregularities'])) * 100)); ?>%"></div>
                                </div>
                                <p class="mt-1 text-[10px] text-slate-400">৳<?php echo e($row['amount_fmt']); ?> · <?php echo e($row['samples']); ?> samples · defect <?php echo e($row['defect_rate']); ?>%</p>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="px-3 py-8 text-center text-[12px] text-slate-400">No branch findings this month.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                <div class="border-b border-slate-100 px-3 py-2">
                    <p class="text-[13px] font-semibold text-navy-900">By category</p>
                    <p class="text-[10px] text-slate-400">Where issues concentrate</p>
                </div>
                <div class="space-y-2.5 px-3 py-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $brief['categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div>
                            <div class="mb-0.5 flex items-center justify-between gap-2">
                                <p class="truncate text-[11px] font-medium text-slate-700"><?php echo e($cat['name']); ?></p>
                                <p class="shrink-0 text-[11px] font-semibold tabular-nums text-navy-900"><?php echo e($cat['hits']); ?></p>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-[#2b579a]" style="width: <?php echo e(min(100, ($cat['hits'] / max(1, $brief['category_max_hits'])) * 100)); ?>%"></div>
                            </div>
                            <p class="mt-0.5 text-[10px] text-slate-400">৳<?php echo e($cat['amount_fmt']); ?></p>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="py-6 text-center text-[12px] text-slate-400">No category hits.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </div>

        
        <section class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3 py-2">
                <div>
                    <p class="text-[13px] font-semibold text-navy-900">Top issues this month</p>
                    <p class="text-[10px] text-slate-400">What authority needs to act on · click for branch breakdown</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Issue</th>
                            <th class="px-3 py-2">Risk</th>
                            <th class="px-3 py-2 text-right">Irregularities</th>
                            <th class="px-3 py-2 text-right">Branches</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $brief['issue_rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-3 py-2 tabular-nums text-slate-400"><?php echo e($i + 1); ?></td>
                                <td class="px-3 py-2">
                                    <p class="font-semibold text-navy-900"><?php echo e($row['title']); ?></p>
                                    <p class="text-[10px] text-slate-400"><span class="font-mono"><?php echo e($row['code']); ?></span> · <?php echo e($row['category']); ?></p>
                                    <div class="mt-1 h-1 max-w-xs overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-amber-500" style="width: <?php echo e(min(100, ($row['irregularities'] / max(1, $brief['max_issue_irregularities'])) * 100)); ?>%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold
                                        <?php echo e(str_contains(strtolower($row['risk_rating']), 'major') ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600'); ?>">
                                        <?php echo e($row['risk_rating']); ?>

                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right text-[13px] font-bold tabular-nums text-rose-700"><?php echo e($row['irregularities']); ?></td>
                                <td class="px-3 py-2 text-right tabular-nums text-slate-700"><?php echo e($row['objected_branches']); ?></td>
                                <td class="px-3 py-2 text-right tabular-nums text-slate-700"><?php echo e($row['amount_fmt']); ?></td>
                                <td class="px-3 py-2 text-right">
                                    <a href="<?php echo e($row['url']); ?>" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Branches</a>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="7" class="px-3 py-10 text-center text-[12px] text-slate-400">No irregularities this month — clean brief for authority.</td>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audit-findings/summary.blade.php ENDPATH**/ ?>