<?php
    $fy = $plan->fy_label;
    $fyParts = explode('-', $fy);
    $startYear = substr($fyParts[0] ?? '2026', -2);
    $endYear = substr($fyParts[1] ?? '2027', -2);
    $initialMonthTotals = array_values($hqTotals['by_month'] ?? array_fill(0, 12, 0));
    $quarterLabels = [
        'q1' => '1st Quarter (Jul–Sep)',
        'q2' => '2nd Quarter (Oct–Dec)',
        'q3' => '3rd Quarter (Jan–Mar)',
        'q4' => '4th Quarter (Apr–Jun)',
    ];
?>

<div
    x-data="{
        showAdd: false,
        monthTotals: <?php echo \Illuminate\Support\Js::from($initialMonthTotals)->toHtml() ?>,
        get quarterTotals() {
            return [
                this.monthTotals[0] + this.monthTotals[1] + this.monthTotals[2],
                this.monthTotals[3] + this.monthTotals[4] + this.monthTotals[5],
                this.monthTotals[6] + this.monthTotals[7] + this.monthTotals[8],
                this.monthTotals[9] + this.monthTotals[10] + this.monthTotals[11],
            ];
        },
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
    <div class="border-b border-slate-200 bg-slate-50/80 px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-[13px] font-semibold text-navy-900">Headquarters (HQ) Work Plan</p>
                <p class="text-[11px] text-slate-500">
                    Monitoring &amp; Audit · July <?php echo e($fyParts[0] ?? ''); ?> to June <?php echo e($fyParts[1] ?? ''); ?>

                    · matches Excel HQ sheet (departments × months + quarter totals)
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="<?php echo e(route('annual-audit.export', ['mode' => 'hq', 'fy' => $plan->fy_label])); ?>"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[12px] font-medium text-emerald-800 hover:bg-emerald-100"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Excel
                </a>
                <button
                    type="button"
                    @click="showAdd = !showAdd"
                    class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800"
                >
                    <span class="text-[13px] leading-none">+</span>
                    Add Department
                </button>
            </div>
        </div>

        <div x-show="showAdd" x-cloak class="mt-3 rounded-xl border border-slate-200 bg-white p-4">
            <form method="POST" action="<?php echo e(route('annual-audit.hq.store')); ?>" class="flex flex-wrap items-end gap-2">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                <div class="min-w-[260px] flex-1">
                    <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-400">Department name</label>
                    <input type="text" name="name" required placeholder="e.g. HR and Admin Department" class="block w-full rounded-lg border-slate-200 text-[12px]" value="<?php echo e(old('name')); ?>">
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('name'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('name')),'class' => 'mt-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                </div>
                <button type="submit" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white">Save</button>
                <button type="button" @click="showAdd = false" class="rounded-lg px-3 py-1.5 text-[12px] text-slate-500 hover:bg-slate-50">Cancel</button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left">
            <thead>
                <tr class="bg-emerald-50/80 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                    <th rowspan="2" class="border border-slate-200 px-2 py-2 text-center w-10">#</th>
                    <th rowspan="2" class="border border-slate-200 px-3 py-2 min-w-[240px]">Department / Section</th>
                    <th colspan="3" class="border border-slate-200 px-2 py-1.5 text-center">1st Quarter</th>
                    <th colspan="3" class="border border-slate-200 px-2 py-1.5 text-center">2nd Quarter</th>
                    <th colspan="3" class="border border-slate-200 px-2 py-1.5 text-center">3rd Quarter</th>
                    <th colspan="3" class="border border-slate-200 px-2 py-1.5 text-center">4th Quarter</th>
                    <th rowspan="2" class="border border-slate-200 px-2 py-2 text-center w-12">Total</th>
                    <th rowspan="2" class="border border-slate-200 px-2 py-2 w-16"></th>
                </tr>
                <tr class="bg-emerald-50/60 text-[9px] font-semibold tracking-wide text-slate-600">
                    <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
                            $monthName = match ($month['month']) {
                                7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                                default => $month['label'],
                            };
                        ?>
                        <th class="border border-slate-200 px-1 py-1.5 text-center min-w-[48px]"><?php echo e($monthName); ?>'<?php echo e($shortYear); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr
                        class="text-[12px]"
                        x-data="{ total: <?php echo e((int) $row['total']); ?> }"
                        @audit-tick="total += $event.detail.delta"
                    >
                        <td class="border border-slate-200 px-2 py-1.5 text-center text-slate-500"><?php echo e($row['sl']); ?></td>
                        <td class="border border-slate-200 px-3 py-1.5 font-medium text-navy-900"><?php echo e($row['name']); ?></td>
                        <?php $__currentLoopData = $row['months']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthIndex => $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="border border-slate-200 px-0 py-0 text-center <?php echo e($active ? 'bg-emerald-100' : 'bg-white'); ?>">
                                <?php if (isset($component)) { $__componentOriginalf804d80bf7f70abc19b8214e9b3a6670 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf804d80bf7f70abc19b8214e9b3a6670 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-month-mark','data' => ['active' => (bool) $active,'manual' => (bool) ($row['manual'][$monthIndex] ?? false),'editable' => $canEditSchedule,'category' => $row['category'],'schedulableType' => $row['schedulable_type'],'schedulableId' => $row['id'],'monthIndex' => $monthIndex,'tab' => 'hq','fy' => $plan->fy_label]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-month-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) $active),'manual' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) ($row['manual'][$monthIndex] ?? false)),'editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($canEditSchedule),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['category']),'schedulable-type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['schedulable_type']),'schedulable-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['id']),'month-index' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($monthIndex),'tab' => 'hq','fy' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($plan->fy_label)]); ?>
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
                        <td class="border border-slate-200 px-2 py-1.5 text-center font-semibold text-navy-900" x-text="total"><?php echo e($row['total']); ?></td>
                        <td class="border border-slate-200 px-2 py-1.5 text-center">
                            <form method="POST" action="<?php echo e(route('annual-audit.hq.destroy', $row['id'])); ?>" onsubmit="return confirm('Delete department <?php echo e($row['name']); ?>?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                                <button type="submit" class="text-[10px] font-medium text-rose-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="16" class="border border-slate-200 px-4 py-10 text-center text-[12px] text-slate-400">
                            No HQ departments yet. Click <span class="font-medium text-navy-800">Add Department</span>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if($rows->isNotEmpty()): ?>
                <tfoot>
                    <tr class="bg-slate-50 text-[12px] font-semibold">
                        <td colspan="2" class="border border-slate-200 px-3 py-2 text-navy-900">Monthly total</td>
                        <template x-for="(count, index) in monthTotals" :key="index">
                            <td class="border border-slate-200 px-2 py-2 text-center text-navy-900" x-text="count"></td>
                        </template>
                        <td class="border border-slate-200 px-2 py-2 text-center text-navy-900" x-text="grandTotal"></td>
                        <td class="border border-slate-200"></td>
                    </tr>
                    <tr class="bg-emerald-50/50 text-[11px]">
                        <td colspan="2" class="border border-slate-200 px-3 py-2 font-semibold text-navy-900">Quarter total</td>
                        <td colspan="3" class="border border-slate-200 px-2 py-2 text-center font-semibold text-navy-900">
                            <span class="text-[10px] font-normal text-slate-500">Q1</span>
                            <span class="ml-1" x-text="quarterTotals[0]"></span>
                        </td>
                        <td colspan="3" class="border border-slate-200 px-2 py-2 text-center font-semibold text-navy-900">
                            <span class="text-[10px] font-normal text-slate-500">Q2</span>
                            <span class="ml-1" x-text="quarterTotals[1]"></span>
                        </td>
                        <td colspan="3" class="border border-slate-200 px-2 py-2 text-center font-semibold text-navy-900">
                            <span class="text-[10px] font-normal text-slate-500">Q3</span>
                            <span class="ml-1" x-text="quarterTotals[2]"></span>
                        </td>
                        <td colspan="3" class="border border-slate-200 px-2 py-2 text-center font-semibold text-navy-900">
                            <span class="text-[10px] font-normal text-slate-500">Q4</span>
                            <span class="ml-1" x-text="quarterTotals[3]"></span>
                        </td>
                        <td class="border border-slate-200 px-2 py-2 text-center font-semibold text-navy-900" x-text="grandTotal"></td>
                        <td class="border border-slate-200"></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <p class="border-t border-slate-100 px-4 py-2 text-[11px] text-slate-500">
        Green = planned HQ visit. Click to add/remove. Footer totals update instantly (monthly + quarterly), like Excel.
    </p>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/annual-audit/partials/hq-work-plan.blade.php ENDPATH**/ ?>