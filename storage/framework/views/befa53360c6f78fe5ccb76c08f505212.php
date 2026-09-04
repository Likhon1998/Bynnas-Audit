<?php
    $mode = $mode ?? 'monitoring'; // audit | monitoring
    $isAudit = $mode === 'audit';
    $tabKey = $isAudit ? 'project_audit' : 'project_monitoring';
    $title = $isAudit ? 'Project Audit Work Plan' : 'Project Monitoring Work Plan';
    $flagLabel = $isAudit ? 'Also include in Project Monitoring' : 'Also include in Project Audit';
    $otherFlag = $isAudit ? 'has_project_monitoring' : 'has_project_audit';
    $category = $isAudit
        ? \App\Models\AuditPolicy::CATEGORY_PROJECT_AUDIT
        : \App\Models\AuditPolicy::CATEGORY_PROJECT_MONITORING;

    $fy = $plan->fy_label;
    $fyParts = explode('-', $fy);
    $startYear = substr($fyParts[0] ?? '2026', -2);
    $endYear = substr($fyParts[1] ?? '2027', -2);
    $highlightProjectId = (int) ($highlightProjectId ?? 0);
?>

<div
    x-data="{ showAddProject: false, openLocationFor: null }"
    x-init="
        $nextTick(() => {
            const el = document.getElementById('highlighted-project');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        })
    "
>
    <div class="border-b border-slate-200 bg-slate-50/80 px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-[13px] font-semibold text-navy-900"><?php echo e($title); ?></p>
                <p class="text-[11px] text-slate-500">
                    July <?php echo e($fyParts[0] ?? ''); ?> to June <?php echo e($fyParts[1] ?? ''); ?>

                    · same projects master as
                    <a href="<?php echo e(route('projects.index')); ?>" class="font-medium text-brand-600 hover:underline">Projects</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAudit): ?>
                        · matches Excel <span class="font-medium text-slate-600">Project Audit</span> sheet
                    <?php else: ?>
                        · matches Excel <span class="font-medium text-slate-600">Project Monitoring</span> sheet
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="<?php echo e(route('annual-audit.export', ['mode' => $isAudit ? 'audit' : 'monitoring', 'fy' => $plan->fy_label])); ?>"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[12px] font-medium text-emerald-800 hover:bg-emerald-100"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Excel
                </a>
                <button
                    type="button"
                    @click="showAddProject = !showAddProject"
                    class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800"
                >
                    <span class="text-[13px] leading-none">+</span>
                    Add Project
                </button>
            </div>
        </div>

        <div x-show="showAddProject" x-cloak class="mt-3 rounded-xl border border-slate-200 bg-white p-4">
            <p class="mb-3 text-[12px] font-medium text-navy-900">New <?php echo e($isAudit ? 'audit' : 'monitoring'); ?> project</p>
            <form method="POST" action="<?php echo e(route('annual-audit.projects.store')); ?>" x-data="{ locations: [{ name: '', division: '' }] }">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                <input type="hidden" name="return_tab" value="<?php echo e($tabKey); ?>">
                <input type="hidden" name="status" value="active">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-400">Name of the Project</label>
                        <input type="text" name="name" required placeholder="e.g. DSK-WASH Water Aid Project" class="block w-full rounded-lg border-slate-200 text-[12px]" value="<?php echo e(old('name')); ?>">
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
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
                    <div>
                        <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-400">Donor</label>
                        <input type="text" name="donor" placeholder="e.g. Water Aid" class="block w-full rounded-lg border-slate-200 text-[12px]" value="<?php echo e(old('donor')); ?>">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-[12px] text-slate-600">
                            <input type="checkbox" name="<?php echo e($otherFlag); ?>" value="1" class="rounded border-slate-300 text-brand-600">
                            <?php echo e($flagLabel); ?>

                        </label>
                    </div>
                </div>

                <div class="mt-3 border-t border-slate-100 pt-3">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-[11px] font-medium text-slate-600">Locations of the Project</p>
                        <button type="button" @click="locations.push({ name: '', division: '' })" class="text-[11px] font-medium text-brand-600 hover:underline">+ Location</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(loc, index) in locations" :key="index">
                            <div class="grid gap-2 sm:grid-cols-12">
                                <select :name="'locations['+index+'][division]'" x-model="loc.division" required class="sm:col-span-4 rounded-lg border-slate-200 text-[12px]">
                                    <option value="">Division</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($division); ?>"><?php echo e($division); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <input type="text" :name="'locations['+index+'][name]'" x-model="loc.name" required placeholder="Location / site e.g. Savar Unit Office" class="sm:col-span-7 rounded-lg border-slate-200 text-[12px]">
                                <button type="button" @click="if (locations.length > 1) locations.splice(index, 1)" class="sm:col-span-1 text-[11px] text-rose-500">×</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-3 flex justify-end gap-2">
                    <button type="button" @click="showAddProject = false" class="rounded-lg px-3 py-1.5 text-[12px] text-slate-500 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Save Project</button>
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-emerald-50/60 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                    <th class="border border-slate-200 px-2 py-2 text-center w-10">#</th>
                    <th class="border border-slate-200 px-3 py-2 min-w-[220px]">Name of the Projects / Donor</th>
                    <th class="border border-slate-200 px-3 py-2 min-w-[180px]">Location of the Projects</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
                            $monthName = match ($month['month']) {
                                7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
                                default => $month['label'],
                            };
                        ?>
                        <th class="border border-slate-200 px-1 py-2 text-center text-[9px] leading-tight min-w-[52px]">
                            <?php echo e($monthName); ?>'<?php echo e($shortYear); ?>

                        </th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <th class="border border-slate-200 px-2 py-2 text-center w-12">Total</th>
                    <th class="border border-slate-200 px-2 py-2 w-28">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $projectGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $isHighlighted = $highlightProjectId > 0 && (int) $group['project_id'] === $highlightProjectId; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($group['rows']->isEmpty()): ?>
                        <tr
                            <?php if($isHighlighted): ?> id="highlighted-project" <?php endif; ?>
                            class="text-[12px] <?php echo e($isHighlighted ? 'bg-amber-100 ring-2 ring-inset ring-amber-400' : ''); ?>"
                        >
                            <td class="border border-slate-200 px-2 py-2 text-center text-slate-500"><?php echo e($group['sl']); ?></td>
                            <td class="border border-slate-200 px-3 py-2 align-top">
                                <p class="font-medium text-navy-900"><?php echo e($group['project']); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($group['donor']): ?>
                                    <p class="mt-0.5 text-[11px] text-slate-500"><?php echo e($group['donor']); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td colspan="<?php echo e(count($months) + 1); ?>" class="border border-slate-200 px-3 py-3 text-slate-400">
                                No locations yet.
                                <button type="button" @click="openLocationFor = openLocationFor === <?php echo e($group['project_id']); ?> ? null : <?php echo e($group['project_id']); ?>" class="ml-1 font-medium text-brand-600 hover:underline">Add location</button>
                            </td>
                            <td class="border border-slate-200 px-2 py-2 text-center">
                                <button type="button" @click="openLocationFor = openLocationFor === <?php echo e($group['project_id']); ?> ? null : <?php echo e($group['project_id']); ?>" class="text-[10px] font-medium text-brand-600 hover:underline">+ Loc</button>
                            </td>
                        </tr>
                        <tr x-show="openLocationFor === <?php echo e($group['project_id']); ?>" x-cloak>
                            <td colspan="<?php echo e(count($months) + 5); ?>" class="border border-slate-200 bg-slate-50 px-3 py-3">
                                <form method="POST" action="<?php echo e(route('annual-audit.projects.locations.store', $group['project_id'])); ?>" class="flex flex-wrap items-end gap-2">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="return_tab" value="<?php echo e($tabKey); ?>">
                                    <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                                    <div>
                                        <label class="mb-1 block text-[10px] font-medium text-slate-400">Division</label>
                                        <select name="division" required class="rounded-lg border-slate-200 text-[12px]">
                                            <option value="">Select division</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($division); ?>"><?php echo e($division); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[10px] font-medium text-slate-400">Location</label>
                                        <input type="text" name="name" required placeholder="e.g. Savar Unit Office" class="rounded-lg border-slate-200 text-[12px]">
                                    </div>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white">Add</button>
                                    <button type="button" @click="openLocationFor = null" class="rounded-lg px-3 py-1.5 text-[12px] text-slate-500 hover:bg-white">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $group['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr
                                <?php if($isHighlighted && $index === 0): ?> id="highlighted-project" <?php endif; ?>
                                class="text-[12px] <?php echo e($isHighlighted ? 'bg-amber-100 ring-2 ring-inset ring-amber-400' : ''); ?>"
                                x-data="{ total: <?php echo e((int) $row['total']); ?> }"
                                @audit-tick="total += $event.detail.delta"
                            >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($index === 0): ?>
                                    <td rowspan="<?php echo e($group['rows']->count()); ?>" class="border border-slate-200 px-2 py-2 text-center align-middle font-medium text-slate-600"><?php echo e($group['sl']); ?></td>
                                    <td rowspan="<?php echo e($group['rows']->count()); ?>" class="border border-slate-200 px-3 py-2 align-top">
                                        <p class="font-medium leading-snug text-navy-900"><?php echo e($group['project']); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($group['donor']): ?>
                                            <p class="mt-1 text-[11px] text-slate-500"><?php echo e($group['donor']); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <td class="border border-slate-200 px-3 py-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-slate-700">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($row['division'])): ?>
                                                <span class="font-medium text-navy-900"><?php echo e($row['division']); ?></span>
                                                <span class="text-slate-400"> · </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php echo e($row['location']); ?>

                                        </span>
                                        <form method="POST" action="<?php echo e(route('annual-audit.projects.locations.destroy', [$group['project_id'], $row['id']])); ?>" onsubmit="return confirm('Remove location <?php echo e($row['location']); ?>?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <input type="hidden" name="return_tab" value="<?php echo e($tabKey); ?>">
                                    <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                                            <button type="submit" class="shrink-0 text-[10px] font-medium text-rose-500 hover:underline" title="Remove location">Remove</button>
                                        </form>
                                    </div>
                                </td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $row['months']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthIndex => $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <td class="border border-slate-200 px-0 py-0 text-center <?php echo e($active ? 'bg-emerald-100' : 'bg-white'); ?>">
                                        <?php if (isset($component)) { $__componentOriginalf804d80bf7f70abc19b8214e9b3a6670 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf804d80bf7f70abc19b8214e9b3a6670 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-month-mark','data' => ['active' => (bool) $active,'manual' => (bool) ($row['manual'][$monthIndex] ?? false),'editable' => $canEditSchedule,'category' => $category,'schedulableType' => $row['schedulable_type'],'schedulableId' => $row['id'],'monthIndex' => $monthIndex,'tab' => $tabKey,'fy' => $plan->fy_label]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-month-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) $active),'manual' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) ($row['manual'][$monthIndex] ?? false)),'editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($canEditSchedule),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category),'schedulable-type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['schedulable_type']),'schedulable-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['id']),'month-index' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($monthIndex),'tab' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tabKey),'fy' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($plan->fy_label)]); ?>
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
                                <td class="border border-slate-200 px-2 py-1.5 text-center font-semibold text-navy-900" x-text="total"><?php echo e($row['total']); ?></td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($index === 0): ?>
                                    <td rowspan="<?php echo e($group['rows']->count()); ?>" class="border border-slate-200 px-2 py-2 align-middle text-center">
                                        <button type="button" @click="openLocationFor = openLocationFor === <?php echo e($group['project_id']); ?> ? null : <?php echo e($group['project_id']); ?>" class="text-[10px] font-medium text-brand-600 hover:underline">+ Loc</button>
                                    </td>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr x-show="openLocationFor === <?php echo e($group['project_id']); ?>" x-cloak>
                            <td colspan="<?php echo e(count($months) + 5); ?>" class="border border-slate-200 bg-slate-50 px-3 py-3">
                                <form method="POST" action="<?php echo e(route('annual-audit.projects.locations.store', $group['project_id'])); ?>" class="flex flex-wrap items-end gap-2">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="return_tab" value="<?php echo e($tabKey); ?>">
                                    <input type="hidden" name="fy" value="<?php echo e($plan->fy_label); ?>">
                                    <div>
                                        <label class="mb-1 block text-[10px] font-medium text-slate-400">Division</label>
                                        <select name="division" required class="rounded-lg border-slate-200 text-[12px]">
                                            <option value="">Select division</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($division); ?>"><?php echo e($division); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[10px] font-medium text-slate-400">Location</label>
                                        <input type="text" name="name" required placeholder="e.g. Savar Unit Office" class="rounded-lg border-slate-200 text-[12px]">
                                    </div>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white">Add location</button>
                                    <button type="button" @click="openLocationFor = null" class="rounded-lg px-3 py-1.5 text-[12px] text-slate-500 hover:bg-white">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="<?php echo e(count($months) + 5); ?>" class="border border-slate-200 px-4 py-10 text-center text-[12px] text-slate-400">
                            No <?php echo e($isAudit ? 'audit' : 'monitoring'); ?> projects yet. Click <span class="font-medium text-navy-800">Add Project</span> to match your Excel work plan.
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <p class="border-t border-slate-100 px-4 py-2 text-[11px] text-slate-500">
        Green cells = planned <?php echo e($isAudit ? 'audit' : 'monitoring'); ?> visit (like Excel). Click to add/remove. Use <span class="font-medium text-slate-700">Remove</span> on a location to drop it individually.
    </p>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\annual-audit\partials\project-work-plan.blade.php ENDPATH**/ ?>