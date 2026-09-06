<?php
    $fy = $plan->fy_label;
    $fyParts = explode('-', $fy);
    $startYear = substr($fyParts[0] ?? '2026', -2);
    $endYear = substr($fyParts[1] ?? '2027', -2);
    $initialMonthTotals = array_values($shakhaTotals['by_month'] ?? array_fill(0, 12, 0));
    $flatRows = $shakhaGroups->flatMap(fn ($g) => $g['rows']);
?>

<div
    x-data="{
        division: '',
        areaId: '',
        shakhaCode: '',
        monthTotals: <?php echo \Illuminate\Support\Js::from($initialMonthTotals)->toHtml() ?>,
        areas: <?php echo \Illuminate\Support\Js::from($areas->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'division' => $a->division])->values())->toHtml() ?>,
        rowMeta: <?php echo \Illuminate\Support\Js::from($flatRows->map(fn ($r) => ['division' => $r['division'] ?? '', 'area_id' => $r['area_id'] ?? null, 'code' => $r['code'] ?? ''])->values())->toHtml() ?>,
        get filteredAreas() {
            return this.division
                ? this.areas.filter((a) => a.division === this.division)
                : this.areas;
        },
        matches(rowDivision, rowAreaId = null, rowCode = '') {
            if (this.division && rowDivision !== this.division) return false;
            if (this.areaId && String(rowAreaId) !== String(this.areaId)) return false;
            if (this.shakhaCode && !(rowCode || '').toLowerCase().includes(this.shakhaCode.toLowerCase().trim())) return false;
            return true;
        },
        groupVisible(groupDivision, groupAreaId, codes) {
            if (this.division && groupDivision !== this.division) return false;
            if (this.areaId && String(groupAreaId) !== String(this.areaId)) return false;
            if (this.shakhaCode) {
                const q = this.shakhaCode.toLowerCase().trim();
                return codes.some((c) => (c || '').toLowerCase().includes(q));
            }
            return true;
        },
        get visibleCount() {
            return this.rowMeta.filter((r) => this.matches(r.division, r.area_id, r.code)).length;
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
            Shakha Work Plan
            <span class="ml-1.5 font-normal text-slate-400">FY <?php echo e($fy); ?></span>
        </p>
        <select x-model="division" @change="areaId = ''" class="h-7 rounded-md border-slate-200 py-0 text-[11px]" title="Division">
            <option value="">All divisions</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $divisionOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($divisionOption); ?>"><?php echo e($divisionOption); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
        <select x-model="areaId" class="h-7 max-w-[160px] rounded-md border-slate-200 py-0 text-[11px]" title="Area">
            <option value="">All areas</option>
            <template x-for="area in filteredAreas" :key="area.id">
                <option :value="String(area.id)" x-text="area.name"></option>
            </template>
        </select>
        <input
            type="search"
            x-model="shakhaCode"
            placeholder="Code…"
            title="Shakha code"
            class="h-7 w-24 rounded-md border-slate-200 py-0 text-[11px]"
        >
        <span class="text-[11px] text-slate-400" x-show="visibleCount > 0"><span x-text="visibleCount" class="font-medium text-slate-600"></span> branches</span>
        <span x-show="visibleCount === 0 && rowMeta.length > 0" class="text-[11px] text-amber-600">No match</span>
        <a
            href="<?php echo e(route('annual-audit.export', ['mode' => 'shakha', 'fy' => $plan->fy_label])); ?>"
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
                <tr class="bg-emerald-50/80 text-[10px] font-semibold tracking-wide text-slate-600">
                    <th class="border border-slate-200 px-1.5 py-1 text-center w-8">#</th>
                    <th class="border border-slate-200 px-1.5 py-1 text-center min-w-[56px]">Code</th>
                    <th class="border border-slate-200 px-2 py-1 min-w-[120px] text-center">Area</th>
                    <th class="border border-slate-200 px-2 py-1 min-w-[150px] text-center">Branch</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthIndex => $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
                            $monthName = match ($month['month']) {
                                7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                                default => $month['label'],
                            };
                        ?>
                        <th class="border border-slate-200 px-0.5 py-1 text-center min-w-[42px]">
                            <div class="text-[10px] font-bold leading-none text-navy-900" x-text="monthTotals[<?php echo e($monthIndex); ?>] ?? 0"><?php echo e($initialMonthTotals[$monthIndex] ?? 0); ?></div>
                            <div class="mt-0.5 text-[8px] font-semibold uppercase leading-none text-slate-500"><?php echo e($monthName); ?>'<?php echo e($shortYear); ?></div>
                        </th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <th class="border border-slate-200 px-1.5 py-1 text-center w-12">Total</th>
                </tr>
            </thead>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $shakhaGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $rowCount = $group['rows']->count();
                    $codes = $group['rows']->pluck('code')->values()->all();
                ?>
                <tbody
                    class="border-t-2 border-slate-300"
                    x-show="groupVisible(<?php echo \Illuminate\Support\Js::from($group['division'] ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($group['area_id'] ?? null)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($codes)->toHtml() ?>)"
                >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $group['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr
                            class="text-[12px]"
                            x-show="matches(<?php echo \Illuminate\Support\Js::from($row['division'] ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($row['area_id'] ?? null)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($row['code'] ?? '')->toHtml() ?>)"
                            @audit-tick="
                                const cell = $el.querySelector('[data-row-total]');
                                if (cell) cell.textContent = Number(cell.textContent || 0) + Number($event.detail.delta || 0);
                            "
                        >
                            <td class="border border-slate-200 px-1.5 py-0.5 text-center text-[11px] text-slate-500"><?php echo e($row['sl']); ?></td>
                            <td class="border border-slate-200 px-1.5 py-0.5 text-center font-mono text-[11px] text-slate-700"><?php echo e($row['code'] ?: '—'); ?></td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($index === 0): ?>
                                <td
                                    rowspan="<?php echo e($rowCount); ?>"
                                    class="border border-slate-200 bg-slate-50/50 px-2 py-1 text-center align-middle text-[11px] font-semibold text-navy-900"
                                >
                                    <?php echo e($group['area'] ?: '—'); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($group['division']): ?>
                                        <span class="mt-0.5 block text-[9px] font-normal text-slate-400"><?php echo e($group['division']); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <td class="border border-slate-200 px-2 py-0.5 text-[12px] font-medium text-navy-900"><?php echo e($row['name']); ?></td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $row['months']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthIndex => $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td class="border border-slate-200 px-0 py-0 text-center <?php echo e($active ? 'bg-emerald-100' : 'bg-white'); ?>">
                                    <?php if (isset($component)) { $__componentOriginalf804d80bf7f70abc19b8214e9b3a6670 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf804d80bf7f70abc19b8214e9b3a6670 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-month-mark','data' => ['active' => (bool) $active,'manual' => (bool) ($row['manual'][$monthIndex] ?? false),'editable' => $canEditSchedule,'category' => $row['category'],'schedulableType' => $row['schedulable_type'],'schedulableId' => $row['id'],'monthIndex' => $monthIndex,'tab' => 'shakha','fy' => $plan->fy_label]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-month-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) $active),'manual' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) ($row['manual'][$monthIndex] ?? false)),'editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($canEditSchedule),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['category']),'schedulable-type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['schedulable_type']),'schedulable-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['id']),'month-index' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($monthIndex),'tab' => 'shakha','fy' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($plan->fy_label)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <td class="border border-slate-200 px-1.5 py-0.5 text-center text-[12px] font-semibold text-navy-900" data-row-total><?php echo e($row['total']); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tbody>
                    <tr>
                        <td colspan="17" class="border border-slate-200 px-4 py-10 text-center text-[12px] text-slate-400">
                            No schedule rows yet. Set frequency in <span class="font-medium text-navy-800">Policies</span>, then click <span class="font-medium text-navy-800">Generate Annual Plan</span>.
                        </td>
                    </tr>
                </tbody>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/annual-audit/partials/shakha-work-plan.blade.php ENDPATH**/ ?>