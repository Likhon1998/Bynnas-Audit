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

    <div class="px-4 py-5 lg:px-6 print:px-0">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-2 print:hidden">
            <div>
                <a href="<?php echo e(route('monthly-visits.index', ['fy' => $plan->fy_label, 'month' => $monthIndex])); ?>" class="text-[11px] font-medium text-brand-600 hover:underline">← Monthly visits</a>
                <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'performance'): ?>
                        Monthly Performance Report
                    <?php elseif($type === 'workload'): ?>
                        Staff Workload
                    <?php elseif($type === 'projects'): ?>
                        Project Audit &amp; Monitoring Visit Plan
                    <?php else: ?>
                        Field Visit &amp; Inspection Monthly Schedule
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </h1>
                <p class="mt-0.5 text-[11px] text-slate-500">FY <?php echo e($plan->fy_label); ?> · <?php echo e($monthLabel); ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'schedule'])); ?>" class="rounded-lg border px-2 py-1 text-[11px] <?php echo e($type === 'schedule' ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200'); ?>">Schedule</a>
                <a href="<?php echo e(route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'projects'])); ?>" class="rounded-lg border px-2 py-1 text-[11px] <?php echo e($type === 'projects' ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200'); ?>">Projects</a>
                <a href="<?php echo e(route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'performance'])); ?>" class="rounded-lg border px-2 py-1 text-[11px] <?php echo e($type === 'performance' ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200'); ?>">Performance</a>
                <a href="<?php echo e(route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'workload'])); ?>" class="rounded-lg border px-2 py-1 text-[11px] <?php echo e($type === 'workload' ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200'); ?>">Workload</a>
                <button type="button" onclick="window.print()" class="rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-800">Print / PDF</button>
            </div>
        </div>

        <div class="hidden print:mb-4 print:block">
            <p class="text-[16px] font-semibold text-navy-900">Field Visit &amp; Inspection — <?php echo e($monthLabel); ?></p>
            <p class="text-[12px] text-slate-600">Financial Year <?php echo e($plan->fy_label); ?></p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($type, ['schedule', 'projects'], true)): ?>
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card print:shadow-none print:border-slate-300">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2">SL</th>
                                <th class="px-3 py-2">Visitor</th>
                                <th class="px-3 py-2">Last Audit Upto</th>
                                <th class="px-3 py-2"><?php echo e($type === 'projects' ? 'Project / Location' : 'Shakha / Project / Entity'); ?></th>
                                <th class="px-3 py-2">Visit Date</th>
                                <th class="px-3 py-2">Days</th>
                                <th class="px-3 py-2">Purpose</th>
                                <th class="px-3 py-2">Remarks</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $assigned; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $a = $item->assignment; ?>
                                <tr class="text-[12px]">
                                    <td class="px-3 py-2 text-slate-500"><?php echo e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></td>
                                    <td class="px-3 py-2 font-medium text-navy-900 whitespace-pre-line"><?php echo e($a?->visitorNames("\n")); ?></td>
                                    <td class="px-3 py-2 text-slate-600"><?php echo e($a?->last_audit_upto?->format('F-Y') ?? '—'); ?></td>
                                    <td class="px-3 py-2 text-slate-700">
                                        <?php echo e($item->entity_label); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->isSpecial()): ?>
                                            <span class="text-[10px] text-amber-700">(Additional / Special)</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-slate-600"><?php echo e($a?->visitDateRangeLabel()); ?></td>
                                    <td class="px-3 py-2 text-slate-600"><?php echo e($a?->duration_days ? str_pad((string) $a->duration_days, 2, '0', STR_PAD_LEFT).' days' : '—'); ?></td>
                                    <td class="px-3 py-2 text-slate-600"><?php echo e($a?->purpose); ?></td>
                                    <td class="px-3 py-2 text-slate-500"><?php echo e($a?->remarks ?: '—'); ?></td>
                                    <td class="px-3 py-2 capitalize text-slate-600"><?php echo e(str_replace('_', ' ', $a?->execution?->status ?? 'planned')); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="9" class="px-4 py-8 text-center text-[12px] text-slate-400">No assigned visits for this report.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif($type === 'performance'): ?>
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">Category</th>
                            <th class="px-3 py-2 text-right">Planned</th>
                            <th class="px-3 py-2 text-right">Assigned</th>
                            <th class="px-3 py-2 text-right">Completed</th>
                            <th class="px-3 py-2 text-right">Unassigned</th>
                            <th class="px-3 py-2 text-right">Cancelled</th>
                            <th class="px-3 py-2 text-right">Overdue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $performance['byCategory']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="text-[12px]">
                                <td class="px-3 py-2 font-medium capitalize text-navy-900"><?php echo e(str_replace('_', ' ', $category)); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($row['planned']); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($row['assigned']); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($row['completed']); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($row['pending']); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($row['cancelled']); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($row['overdue']); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr class="bg-slate-50 text-[12px] font-semibold">
                            <td class="px-3 py-2">TOTAL</td>
                            <td class="px-3 py-2 text-right"><?php echo e($performance['totals']['planned']); ?></td>
                            <td class="px-3 py-2 text-right"><?php echo e($performance['totals']['assigned']); ?></td>
                            <td class="px-3 py-2 text-right"><?php echo e($performance['totals']['completed']); ?></td>
                            <td class="px-3 py-2 text-right"><?php echo e($performance['totals']['pending']); ?></td>
                            <td class="px-3 py-2 text-right"><?php echo e($performance['totals']['cancelled']); ?></td>
                            <td class="px-3 py-2 text-right"><?php echo e($performance['totals']['overdue']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">Person</th>
                            <th class="px-3 py-2">Designation</th>
                            <th class="px-3 py-2 text-right">Activities</th>
                            <th class="px-3 py-2 text-right">Total Days</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workload; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="text-[12px]">
                                <td class="px-3 py-2 font-medium text-navy-900"><?php echo e($row['employee']?->name); ?></td>
                                <td class="px-3 py-2 text-slate-600"><?php echo e($row['employee']?->position?->title ?? '—'); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($row['activities']); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($row['total_days']); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="4" class="px-4 py-8 text-center text-[12px] text-slate-400">No assignments yet.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\monthly-visits\report.blade.php ENDPATH**/ ?>