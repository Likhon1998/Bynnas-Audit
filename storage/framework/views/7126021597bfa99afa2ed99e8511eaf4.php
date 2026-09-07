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

    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Findings Summary</h1>
                <p class="mt-0.5 text-[12px] text-slate-500"><?php echo e($periodLabel); ?></p>
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

        
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($row['sub_category'] ?? '') !== '' && ($row['sub_category'] ?? '') !== '—'): ?>
                                        <p class="text-[10px] text-slate-400"><?php echo e($row['sub_category']); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5 align-top whitespace-nowrap font-mono text-[11px] text-sky-700"><?php echo e($row['code']); ?></td>
                                <td class="px-3 py-2.5 align-top">
                                    <a href="<?php echo e($row['url']); ?>" class="font-semibold text-[#2b579a] hover:underline"><?php echo e($row['title']); ?></a>
                                </td>
                                <td class="px-3 py-2.5 text-right align-top tabular-nums whitespace-nowrap"><?php echo e($row['amount_fmt']); ?></td>
                                <td class="px-3 py-2.5 text-right align-top tabular-nums"><?php echo e(number_format($row['samples'])); ?></td>
                                <td class="px-3 py-2.5 text-right align-top text-[13px] font-semibold tabular-nums text-rose-700"><?php echo e(number_format($row['irregularities'])); ?></td>
                                <td class="px-3 py-2.5 text-right align-top">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold <?php echo e($rateClass); ?>"><?php echo e($row['percentage_fmt']); ?></span>
                                </td>
                                <td class="px-3 py-2.5 text-right align-top font-semibold tabular-nums"><?php echo e(number_format($row['branch_count'])); ?></td>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audit-findings/summary.blade.php ENDPATH**/ ?>