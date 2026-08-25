<?php
    $fy = $plan->fy_label;
    $fyParts = explode('-', $fy);
    $startYear = substr($fyParts[0] ?? '2026', -2);
    $endYear = substr($fyParts[1] ?? '2027', -2);
    $initialMonthTotals = array_values($pksfTotals['by_month'] ?? array_fill(0, 12, 0));
?>

<div
    x-data="{
        monthTotals: <?php echo \Illuminate\Support\Js::from($initialMonthTotals)->toHtml() ?>,
        get grandTotal() {
            return this.monthTotals.reduce((a, b) => a + b, 0);
        },
        onTick(e) {
            const i = e.detail.month_index;
            if (i === undefined || i === null) return;
            this.monthTotals[i] = Math.max(0, (this.monthTotals[i] || 0) + e.detail.delta);
            this.monthTotals = [...this.monthTotals];
        },
    }"
    @audit-tick.window="onTick($event)"
>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 border-b border-slate-200 bg-slate-50/80 px-3 py-1.5">
        <p class="mr-auto text-[12px] font-semibold text-navy-900">
            PKSF and Maternity Work Plan
            <span class="ml-1.5 font-normal text-slate-400">FY <?php echo e($fy); ?> · <?php echo e($rows->count()); ?> rows</span>
        </p>
        <a
            href="<?php echo e(route('annual-audit.export', ['mode' => 'pksf', 'fy' => $plan->fy_label])); ?>"
            class="inline-flex h-7 items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 text-[11px] font-medium text-emerald-800 hover:bg-emerald-100"
        >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export Excel
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left">
            <thead>
                <tr class="bg-amber-200/90 text-[10px] font-semibold tracking-wide text-slate-800">
                    <th rowspan="2" class="border border-slate-300 px-1.5 py-1 text-center w-8">#</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-1 min-w-[150px] text-center">Project Name</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-1 min-w-[150px] text-center">Project Location</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center">1st Quarter</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center">2nd Quarter</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center">3rd Quarter</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center">4th Quarter</th>
                    <th rowspan="2" class="border border-slate-300 px-1.5 py-1 text-center w-12">Total</th>
                </tr>
                <tr class="bg-slate-100 text-[9px] font-semibold tracking-wide text-slate-600">
                    <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthIndex => $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
                            $monthName = match ($month['month']) {
                                7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                                default => $month['label'],
                            };
                        ?>
                        <th class="border border-slate-300 px-0.5 py-1 text-center min-w-[42px]">
                            <div class="text-[10px] font-bold leading-none text-navy-900" x-text="monthTotals[<?php echo e($monthIndex); ?>] ?? 0"><?php echo e($initialMonthTotals[$monthIndex] ?? 0); ?></div>
                            <div class="mt-0.5 text-[8px] font-semibold uppercase leading-none text-slate-500"><?php echo e($monthName); ?>-<?php echo e($shortYear); ?></div>
                        </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr
                        class="text-[12px] <?php echo e(($row['is_maternity'] ?? false) && ! ($row['is_pksf'] ?? false) && ($row['sl'] ?? 0) === 7 ? 'border-t-2 border-slate-400' : ''); ?>"
                        @audit-tick="
                            const cell = $el.querySelector('[data-row-total]');
                            if (cell) cell.textContent = Number(cell.textContent || 0) + Number($event.detail.delta || 0);
                        "
                    >
                        <td class="border border-slate-300 px-1.5 py-0.5 text-center text-[11px] text-slate-500"><?php echo e($row['sl']); ?></td>
                        <td class="border border-slate-300 px-2 py-0.5 font-medium text-navy-900"><?php echo e($row['project']); ?></td>
                        <td class="border border-slate-300 px-2 py-0.5 text-slate-700"><?php echo e($row['location']); ?></td>
                        <?php $__currentLoopData = $row['months']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthIndex => $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="border border-slate-300 px-0 py-0 text-center <?php echo e($active ? 'bg-emerald-100' : 'bg-white'); ?>">
                                <?php if (isset($component)) { $__componentOriginalf804d80bf7f70abc19b8214e9b3a6670 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf804d80bf7f70abc19b8214e9b3a6670 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-month-mark','data' => ['active' => (bool) $active,'manual' => (bool) ($row['manual'][$monthIndex] ?? false),'editable' => $canEditSchedule,'category' => $row['category'],'schedulableType' => $row['schedulable_type'],'schedulableId' => $row['id'],'monthIndex' => $monthIndex,'tab' => 'pksf','fy' => $plan->fy_label]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-month-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) $active),'manual' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) ($row['manual'][$monthIndex] ?? false)),'editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($canEditSchedule),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['category']),'schedulable-type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['schedulable_type']),'schedulable-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['id']),'month-index' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($monthIndex),'tab' => 'pksf','fy' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($plan->fy_label)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf804d80bf7f70abc19b8214e9b3a6670)): ?>
<?php $attributes = $__attributesOriginalf804d80bf7f70abc19b8214e9b3a6670; ?>
<?php unset($__attributesOriginalf804d80bf7f70abc19b8214e9b3a6670); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf804d80bf7f70abc19b8214e9b3a6670)): ?>
<?php $component = $__componentOriginalf804d80bf7f70abc19b8214e9b3a6670; ?>
<?php unset($__componentOriginalf804d80bf7f70abc19b8214e9b3a6670); ?>
<?php endif; ?>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="border border-slate-300 px-1.5 py-0.5 text-center text-[12px] font-semibold text-navy-900" data-row-total><?php echo e($row['total']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="16" class="border border-slate-300 px-4 py-10 text-center text-[12px] text-slate-400">
                            No PKSF / Maternity schedules yet. Generate the annual plan after seeding projects.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if($rows->isNotEmpty()): ?>
                <tfoot>
                    <tr class="bg-orange-50 text-[11px] font-semibold text-navy-900">
                        <td class="border border-slate-300 px-1.5 py-1"></td>
                        <td colspan="2" class="border border-slate-300 px-2 py-1">Total</td>
                        <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthIndex => $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="border border-slate-300 px-1 py-1 text-center" x-text="monthTotals[<?php echo e($monthIndex); ?>] ?? 0"><?php echo e($initialMonthTotals[$monthIndex] ?? 0); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="border border-slate-300 px-1.5 py-1 text-center" x-text="grandTotal"><?php echo e($pksfTotals['grand'] ?? 0); ?></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/annual-audit/partials/pksf-work-plan.blade.php ENDPATH**/ ?>