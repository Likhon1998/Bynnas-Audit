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
        $tabs = [
            'policies' => [
                'label' => $plan->generated_at ? 'Policies' : '1. Policies',
                'idle' => 'bg-rose-50 text-rose-700 hover:bg-rose-100',
                'active' => 'bg-rose-600 text-white shadow-md ring-2 ring-rose-600/30',
            ],
            'total' => ['label' => 'Total', 'idle' => 'bg-slate-100 text-slate-600 hover:bg-slate-200', 'active' => 'bg-navy-900 text-white shadow-md ring-2 ring-navy-900/20'],
            'shakha' => ['label' => 'Shakha Audit', 'idle' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100', 'active' => 'bg-emerald-600 text-white shadow-md ring-2 ring-emerald-600/30'],
            'area' => ['label' => 'Area Office', 'idle' => 'bg-amber-50 text-amber-800 hover:bg-amber-100', 'active' => 'bg-amber-500 text-white shadow-md ring-2 ring-amber-500/30'],
            'pksf' => ['label' => 'PKSF & Maternity', 'idle' => 'bg-orange-50 text-orange-700 hover:bg-orange-100', 'active' => 'bg-orange-500 text-white shadow-md ring-2 ring-orange-500/30'],
            'hq' => ['label' => 'HQ', 'idle' => 'bg-sky-50 text-sky-700 hover:bg-sky-100', 'active' => 'bg-sky-600 text-white shadow-md ring-2 ring-sky-600/30'],
            'project_audit' => ['label' => 'Project Audit', 'idle' => 'bg-teal-50 text-teal-700 hover:bg-teal-100', 'active' => 'bg-teal-600 text-white shadow-md ring-2 ring-teal-600/30'],
            'project_monitoring' => ['label' => 'Project Monitoring', 'idle' => 'bg-cyan-50 text-cyan-700 hover:bg-cyan-100', 'active' => 'bg-cyan-600 text-white shadow-md ring-2 ring-cyan-600/30'],
        ];
        $canEditSchedule = $canEditSchedule ?? true;
    ?>

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-3 flex flex-nowrap items-center gap-2 overflow-x-auto pb-0.5">
            <h1 class="shrink-0 text-[14px] font-semibold tracking-tight text-navy-900">Annual Audit &amp; Monitoring</h1>
            <span class="hidden h-4 w-px shrink-0 bg-slate-200 sm:block"></span>
            <label class="inline-flex shrink-0 items-center gap-1 text-[11px] text-slate-400">
                FY
                <select
                    class="h-7 rounded-md border-slate-200 py-0 text-[12px] font-medium text-navy-900"
                    onchange="window.location = this.value"
                >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $availablePlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $availablePlan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option
                            value="<?php echo e(route('annual-audit.index', array_filter(['fy' => $availablePlan->fy_label, 'tab' => $tab]))); ?>"
                            <?php if($availablePlan->fy_label === $plan->fy_label): echo 'selected'; endif; ?>
                        >
                            <?php echo e($availablePlan->fy_label); ?> (<?php echo e($availablePlan->status); ?>)
                        </option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </label>
            <span class="hidden shrink-0 text-[11px] capitalize text-slate-400 sm:inline"><?php echo e($plan->status); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->generated_at): ?>
                <span class="hidden shrink-0 text-[11px] text-slate-400 lg:inline">· <?php echo e($plan->generated_at->format('d M Y H:i')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="ml-auto flex shrink-0 flex-nowrap items-center gap-1.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canDeletePlan ?? false): ?>
                    <form
                        method="POST"
                        action="<?php echo e(route('annual-audit.years.destroy')); ?>"
                        class="inline"
                        onsubmit="return confirm('Delete the entire FY <?php echo e($plan->fy_label); ?> report?\n\nThis permanently removes all schedules and policies for that year. This cannot be undone.')"
                    >
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md border border-rose-200 bg-rose-50 px-2 text-[11px] font-medium text-rose-700 hover:bg-rose-100">
                            Delete FY
                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($nextPlanExists)): ?>
                    <form method="POST" action="<?php echo e(route('annual-audit.years.store')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 text-[11px] font-medium text-emerald-800 hover:bg-emerald-100">
                            Create <?php echo e($nextFyLabel); ?>

                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a
                    href="<?php echo e(route('annual-audit.export', ['mode' => 'all', 'fy' => $plan->fy_label])); ?>"
                    class="inline-flex h-7 items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 text-[11px] font-medium text-emerald-800 hover:bg-emerald-100"
                    title="Download Total through Project Monitoring in one Excel file"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Full Report
                </a>
                <form method="POST" action="<?php echo e(route('annual-audit.generate')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                    <button
                        type="submit"
                        class="inline-flex h-7 items-center rounded-md bg-navy-900 px-2.5 text-[11px] font-medium text-white hover:bg-navy-800"
                        title="Uses frequencies from Policies to build the yearly schedule"
                    >
                        <?php echo e($plan->generated_at ? 'Regenerate' : '2. Generate Plan'); ?>

                    </button>
                </form>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->generated_at): ?>
                    <form method="POST" action="<?php echo e(route('annual-audit.sync-missing')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                        <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                        <button
                            type="submit"
                            class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                            title="Add only new shakha / area / project rows without changing existing schedules"
                        >
                            Sync new items
                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->status !== 'published'): ?>
                    <form method="POST" action="<?php echo e(route('annual-audit.publish')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50">
                            Publish
                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($plan->generated_at)): ?>
            <div class="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 rounded-lg border border-rose-100 bg-rose-50/70 px-3 py-2 text-[12px] text-rose-900">
                <span class="font-semibold">Setup this FY:</span>
                <a href="<?php echo e(route('annual-audit.index', ['fy' => $plan->fy_label, 'tab' => 'policies'])); ?>" class="font-medium underline decoration-rose-300 underline-offset-2 hover:text-rose-700">1. Set Policies</a>
                <span class="text-rose-300">→</span>
                <span>2. Generate Plan</span>
                <span class="text-rose-300">→</span>
                <span class="text-rose-700/80">3. Review report tabs</span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                ['label' => 'Planned', 'value' => $kpis['planned']],
                ['label' => 'Completed', 'value' => $kpis['completed']],
                ['label' => 'Pending', 'value' => $kpis['pending']],
                ['label' => 'Shakha', 'value' => $kpis['shakha']],
                ['label' => 'Area', 'value' => $kpis['area']],
                ['label' => 'Projects', 'value' => $kpis['project_audit'] + $kpis['project_monitoring']],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="rounded-xl border border-slate-100 bg-white px-3 py-2.5 shadow-card">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400"><?php echo e($kpi['label']); ?></p>
                    <p class="mt-1 text-[18px] font-semibold tracking-tight text-navy-900"><?php echo e(number_format($kpi['value'])); ?></p>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <div class="mb-3 flex flex-wrap gap-1.5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tabMeta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a
                    href="<?php echo e(route('annual-audit.index', array_filter(['fy' => $plan->fy_label, 'tab' => $key, 'division' => $filters['division'], 'area_id' => $filters['area_id']]))); ?>"
                    class="whitespace-nowrap rounded-lg px-3 py-1.5 text-[12px] font-semibold transition <?php echo e($tab === $key ? $tabMeta['active'] : $tabMeta['idle']); ?>"
                >
                    <?php echo e($tabMeta['label']); ?>

                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'pksf'): ?>
            <p class="mb-3 text-[11px] text-slate-500">
                Click any month cell to schedule or remove. Nothing is fixed — admin controls each month.
            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'hq'): ?>
                <?php echo $__env->make('annual-audit.partials.hq-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'rows' => $rows,
                    'hqTotals' => $hqTotals,
                    'canEditSchedule' => $canEditSchedule,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($tab === 'shakha'): ?>
                <?php echo $__env->make('annual-audit.partials.shakha-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'shakhaGroups' => $shakhaGroups,
                    'shakhaTotals' => $shakhaTotals,
                    'divisions' => $divisions,
                    'areas' => $areas,
                    'canEditSchedule' => $canEditSchedule,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($tab === 'pksf'): ?>
                <?php echo $__env->make('annual-audit.partials.pksf-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'rows' => $rows,
                    'pksfTotals' => $pksfTotals,
                    'canEditSchedule' => $canEditSchedule,
                    'highlightProjectId' => $highlightProjectId ?? null,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($tab === 'area'): ?>
                <?php echo $__env->make('annual-audit.partials.area-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'rows' => $rows,
                    'areaTotals' => $areaTotals,
                    'divisions' => $divisions,
                    'canEditSchedule' => $canEditSchedule,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif(in_array($tab, ['project_audit', 'project_monitoring'], true)): ?>
                <?php echo $__env->make('annual-audit.partials.project-work-plan', [
                    'mode' => $tab === 'project_audit' ? 'audit' : 'monitoring',
                    'plan' => $plan,
                    'months' => $months,
                    'projectGroups' => $projectGroups,
                    'divisions' => $divisions,
                    'canEditSchedule' => $canEditSchedule,
                    'highlightProjectId' => $highlightProjectId ?? null,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($tab === 'total'): ?>
                <?php echo $__env->make('annual-audit.partials.total-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'categoryTotals' => $categoryTotals,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($tab === 'policies'): ?>
                <form method="POST" action="<?php echo e(route('annual-audit.policies')); ?>" class="p-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                    <p class="mb-3 text-[12px] text-slate-600">
                        <span class="font-semibold text-navy-900">Step 1 — set times per year.</span>
                        That is the only policy setting. Months are placed evenly across the FY when you generate;
                        change any cell later on the report tabs.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="border-b border-slate-100 bg-slate-50/80">
                                <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    <th class="px-3 py-2.5">Category</th>
                                    <th class="px-3 py-2.5">Times / Year</th>
                                    <th class="px-3 py-2.5">When generating</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $policies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php
                                        $hints = [
                                            'shakha_audit' => 'Months are rotated across branches so visits are spread out.',
                                            'area_office' => 'Same months for every area (evenly spaced).',
                                            'pksf_maternity' => 'Same months for each PKSF / Maternity location.',
                                            'hq_concern' => 'Same months for each HQ department.',
                                            'project_audit' => 'Same months for each project-audit location.',
                                            'project_monitoring' => 'Same months for each monitoring location.',
                                        ];
                                    ?>
                                    <tr class="text-[12px]">
                                        <td class="px-3 py-2.5 font-medium capitalize text-navy-900"><?php echo e(str_replace('_', ' ', $policy->category)); ?></td>
                                        <td class="px-3 py-2.5">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($policy->category === 'shakha_audit'): ?>
                                                <select name="policies[<?php echo e($policy->id); ?>][frequency_per_year]" class="w-24 rounded-lg border-slate-200 text-[12px]">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [2, 3, 4, 6, 12]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $freq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                        <option value="<?php echo e($freq); ?>" <?php if((int) $policy->frequency_per_year === $freq): echo 'selected'; endif; ?>><?php echo e($freq); ?></option>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="number" min="1" max="12" name="policies[<?php echo e($policy->id); ?>][frequency_per_year]" value="<?php echo e($policy->frequency_per_year); ?>" class="w-24 rounded-lg border-slate-200 text-[12px]">
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-500"><?php echo e($hints[$policy->category] ?? 'Evenly spaced months.'); ?></td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                            Save policies
                        </button>
                        <button type="submit" name="regenerate" value="1" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                            Save &amp; regenerate plan
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-slate-100 bg-slate-50/80">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-3 py-2.5">Project</th>
                                <th class="px-3 py-2.5">Division</th>
                                <th class="px-3 py-2.5">Location</th>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <th class="px-1 py-2.5 text-center"><?php echo e($month['label']); ?></th>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <th class="px-3 py-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr
                                    class="text-[12px]"
                                    @audit-tick="
                                        const cell = $el.querySelector('[data-row-total]');
                                        if (cell) cell.textContent = Number(cell.textContent || 0) + Number($event.detail.delta || 0);
                                    "
                                >
                                    <td class="px-3 py-1.5 font-medium text-navy-900"><?php echo e($row['project']); ?></td>
                                    <td class="px-3 py-1.5 text-slate-600"><?php echo e($row['division'] ?: '—'); ?></td>
                                    <td class="px-3 py-1.5 text-slate-600"><?php echo e($row['location']); ?></td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $row['months']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthIndex => $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <td class="px-1 py-1 text-center">
                                            <?php if (isset($component)) { $__componentOriginalf804d80bf7f70abc19b8214e9b3a6670 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf804d80bf7f70abc19b8214e9b3a6670 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-month-mark','data' => ['active' => (bool) $active,'manual' => (bool) ($row['manual'][$monthIndex] ?? false),'editable' => $canEditSchedule,'category' => $row['category'],'schedulableType' => $row['schedulable_type'],'schedulableId' => $row['id'],'monthIndex' => $monthIndex,'tab' => $tab,'fy' => $plan->fy_label]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-month-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) $active),'manual' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) ($row['manual'][$monthIndex] ?? false)),'editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($canEditSchedule),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['category']),'schedulable-type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['schedulable_type']),'schedulable-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['id']),'month-index' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($monthIndex),'tab' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tab),'fy' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($plan->fy_label)]); ?>
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
                                    <td class="px-3 py-1.5 text-right font-semibold text-navy-900" data-row-total><?php echo e($row['total']); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="16" class="px-4 py-10 text-center text-[12px] text-slate-400">
                                        No schedule rows yet. Set frequency in <span class="font-medium text-navy-800">Policies</span>, then click <span class="font-medium text-navy-800">Generate Annual Plan</span>, or click months directly.
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/annual-audit/index.blade.php ENDPATH**/ ?>