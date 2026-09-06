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

    <div class="px-3 py-3 lg:px-5">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Findings Summary</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">
                    <?php echo e($periodLabel); ?> · Only indicators used in audit reports for this month
                </p>
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
                <a
                    href="<?php echo e($exportUrl); ?>"
                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-md bg-emerald-700 px-3.5 text-[12px] font-semibold text-white shadow-sm hover:bg-emerald-800"
                    title="Download Excel workbook"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    Excel
                </a>
                <a
                    href="<?php echo e($exportPptUrl); ?>"
                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-md bg-[#c43e1c] px-3.5 text-[12px] font-semibold text-white shadow-sm hover:bg-[#a83316]"
                    title="Download DSK presentation (PowerPoint)"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4zM8 6v12M4 10h16"/></svg>
                    Download PPT
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

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-3 py-2">
                <p class="text-[13px] font-semibold text-slate-800"><?php echo e($periodLabel); ?> — report findings</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-[12px] text-slate-800">
                    <thead>
                        <tr class="bg-slate-100 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                            <th class="border border-slate-200 px-3 py-2 whitespace-nowrap">Heading</th>
                            <th class="border border-slate-200 px-3 py-2 whitespace-nowrap">Sub-heading</th>
                            <th class="border border-slate-200 px-3 py-2 whitespace-nowrap">Code</th>
                            <th class="border border-slate-200 px-3 py-2 min-w-[240px]">Indicator</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Amount</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Sample size</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Irregularities</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Irregularity %</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Total branch</th>
                            <th class="border border-slate-200 px-3 py-2 min-w-[200px]">Branch name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $flatRows = collect($underheadingGroups)->flatMap(function ($group) {
                                return collect($group['rows'])->map(fn ($row) => array_merge($row, [
                                    'category' => $group['category'],
                                    'sub_category' => $group['sub_category'],
                                ]));
                            });
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $flatRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="<?php echo e($index % 2 === 0 ? 'bg-white' : 'bg-slate-50/80'); ?>">
                                <td class="border border-slate-200 px-3 py-2 align-top text-slate-700"><?php echo e($row['category']); ?></td>
                                <td class="border border-slate-200 px-3 py-2 align-top text-slate-700"><?php echo e($row['sub_category']); ?></td>
                                <td class="border border-slate-200 px-3 py-2 align-top font-mono text-[11px] text-slate-600 whitespace-nowrap"><?php echo e($row['code']); ?></td>
                                <td class="border border-slate-200 px-3 py-2 align-top">
                                    <a href="<?php echo e($row['url']); ?>" class="font-medium text-slate-800 hover:underline"><?php echo e($row['title']); ?></a>
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums whitespace-nowrap"><?php echo e($row['amount_fmt']); ?></td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums"><?php echo e(number_format($row['samples'])); ?></td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums font-semibold"><?php echo e(number_format($row['irregularities'])); ?></td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums whitespace-nowrap"><?php echo e($row['percentage_fmt']); ?></td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums"><?php echo e(number_format($row['branch_count'])); ?></td>
                                <td class="border border-slate-200 px-3 py-2 align-top text-[11px] leading-snug text-slate-700">
                                    <?php echo e($row['branches'] !== '' ? $row['branches'] : '—'); ?>

                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="10" class="border border-slate-200 px-3 py-10 text-center text-[12px] text-slate-400">
                                    No report findings for <?php echo e($periodLabel); ?>. Sync or complete an audit report first.
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