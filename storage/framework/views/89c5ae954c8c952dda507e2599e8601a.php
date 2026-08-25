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
    <?php
        $areaOptions = $areas->map(fn ($area) => [
            'id' => (string) $area->id,
            'name' => $area->name,
            'division' => $area->division,
            'shakhas_count' => $area->shakhas_count,
        ])->values();
    ?>

    <div
        class="px-4 py-4 lg:px-6"
        x-data="{
            name: <?php echo \Illuminate\Support\Js::from((string) old('name', ''))->toHtml() ?>,
            code: <?php echo \Illuminate\Support\Js::from((string) old('code', ''))->toHtml() ?>,
            status: <?php echo \Illuminate\Support\Js::from((string) old('status', 'active'))->toHtml() ?>,
            areaId: <?php echo \Illuminate\Support\Js::from(old('area_id') !== null ? (string) old('area_id') : '')->toHtml() ?>,
            areas: <?php echo \Illuminate\Support\Js::from($areaOptions)->toHtml() ?>,
            get selectedArea() {
                return this.areas.find(area => area.id === String(this.areaId)) || null;
            }
        }"
    >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                <a href="<?php echo e(route('shakhas.index')); ?>" class="hover:text-brand-600">All Shakha</a>
                <span>/</span>
                <span class="text-slate-600">Add Shakha</span>
            </div>
            <div class="flex items-center gap-1.5">
                <a href="<?php echo e(route('shakhas.index')); ?>" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">
                    Back to list
                </a>
                <a href="<?php echo e(route('areas.create')); ?>" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">
                    Add Area
                </a>
            </div>
        </div>

        <?php if($errors->any()): ?>
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('shakhas.store')); ?>" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
            <?php echo csrf_field(); ?>

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Shakha information</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Complete the fields below to create a branch record.</p>
                </div>

                <div class="space-y-5 px-5 py-5">
                    <?php if($areas->isEmpty()): ?>
                        <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-[12px] text-amber-800">
                            <p class="font-medium">No areas available</p>
                            <p class="mt-0.5 text-amber-700">Create an area first, then return here to add a shakha.</p>
                            <a href="<?php echo e(route('areas.create')); ?>" class="mt-2 inline-flex text-[12px] font-semibold text-amber-900 underline">Go to Add Area</a>
                        </div>
                    <?php endif; ?>

                    <section>
                        <div class="mb-3 flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-brand-50 text-[10px] font-semibold text-brand-600">1</span>
                            <div>
                                <p class="text-[12px] font-semibold text-navy-900">Basic details</p>
                                <p class="text-[11px] text-slate-400">Name and optional branch code</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="name" class="mb-1.5 block text-[11px] font-medium text-slate-600">
                                    Shakha name <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="<?php echo e(old('name')); ?>"
                                    x-model="name"
                                    required
                                    <?php if($areas->isEmpty()): echo 'disabled'; endif; ?>
                                    placeholder="e.g. Mirpur Shakha"
                                    class="block w-full rounded-lg border-slate-200 text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-slate-50"
                                >
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

                            <div>
                                <label for="code" class="mb-1.5 block text-[11px] font-medium text-slate-600">
                                    Branch code
                                </label>
                                <input
                                    id="code"
                                    name="code"
                                    type="text"
                                    value="<?php echo e(old('code')); ?>"
                                    x-model="code"
                                    <?php if($areas->isEmpty()): echo 'disabled'; endif; ?>
                                    placeholder="e.g. DHA-001"
                                    class="block w-full rounded-lg border-slate-200 text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-slate-50"
                                >
                                <p class="mt-1 text-[10px] text-slate-400">Optional unique identifier for reports.</p>
                                <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('code'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('code')),'class' => 'mt-1']); ?>
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
                                <label for="status" class="mb-1.5 block text-[11px] font-medium text-slate-600">
                                    Status <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    id="status"
                                    name="status"
                                    x-model="status"
                                    required
                                    <?php if($areas->isEmpty()): echo 'disabled'; endif; ?>
                                    class="block w-full rounded-lg border-slate-200 text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-slate-50"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('status'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('status')),'class' => 'mt-1']); ?>
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
                        </div>
                    </section>

                    <section class="border-t border-slate-100 pt-5">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-50 text-[10px] font-semibold text-emerald-600">2</span>
                            <div>
                                <p class="text-[12px] font-semibold text-navy-900">Location</p>
                                <p class="text-[11px] text-slate-400">Assign this shakha to an area</p>
                            </div>
                        </div>

                        <div>
                            <label for="area_id" class="mb-1.5 block text-[11px] font-medium text-slate-600">
                                Area <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="area_id"
                                name="area_id"
                                x-model="areaId"
                                required
                                <?php if($areas->isEmpty()): echo 'disabled'; endif; ?>
                                class="block w-full rounded-lg border-slate-200 text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-slate-50"
                            >
                                <option value="">Select area</option>
                                <?php $__currentLoopData = $areasByDivision; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division => $divisionAreas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <optgroup label="<?php echo e($division); ?>">
                                        <?php $__currentLoopData = $divisionAreas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($area->id); ?>" <?php if((string) old('area_id') === (string) $area->id): echo 'selected'; endif; ?>>
                                                <?php echo e($area->name); ?> (<?php echo e($area->shakhas_count); ?> shakha)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </optgroup>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('area_id'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('area_id')),'class' => 'mt-1']); ?>
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
                    </section>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3.5">
                    <p class="text-[11px] text-slate-400">Fields marked with <span class="text-rose-500">*</span> are required.</p>
                    <div class="flex items-center gap-1.5">
                        <a href="<?php echo e(route('shakhas.index')); ?>" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-white">Cancel</a>
                        <button
                            type="submit"
                            <?php if($areas->isEmpty()): echo 'disabled'; endif; ?>
                            class="inline-flex items-center gap-1.5 rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Shakha
                        </button>
                    </div>
                </div>
            </div>

            <aside class="flex flex-col gap-3">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-card">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Live preview</p>
                    <div class="mt-3 rounded-xl border border-slate-100 bg-slate-50 p-3.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-semibold text-navy-900" x-text="name.trim() !== '' ? name : 'Shakha name'"></p>
                                <p class="mt-0.5 truncate text-[11px] text-slate-500">
                                    <span x-text="selectedArea ? selectedArea.name : 'Area not selected'"></span>
                                    <template x-if="selectedArea">
                                        <span> · <span x-text="selectedArea.division"></span></span>
                                    </template>
                                </p>
                            </div>
                            <span
                                class="inline-flex shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium"
                                :class="status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600'"
                                x-text="status === 'active' ? 'Active' : 'Inactive'"
                            ></span>
                        </div>
                        <div class="mt-3 flex items-center gap-2 border-t border-slate-200/70 pt-3 text-[11px] text-slate-500">
                            <span class="rounded-md bg-white px-2 py-1 font-medium text-slate-600" x-text="code.trim() !== '' ? code : 'No code'"></span>
                            <template x-if="selectedArea">
                                <span x-text="selectedArea.shakhas_count + ' existing in area'"></span>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-slate-100 bg-white p-3.5 shadow-card">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Areas</p>
                        <p class="mt-1 text-[18px] font-semibold tracking-tight text-navy-900"><?php echo e($areaCount); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-3.5 shadow-card">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Shakhas</p>
                        <p class="mt-1 text-[18px] font-semibold tracking-tight text-navy-900"><?php echo e($shakhaCount); ?></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-card">
                    <p class="text-[12px] font-semibold text-navy-900">Guidelines</p>
                    <ul class="mt-2 space-y-2 text-[11px] leading-relaxed text-slate-500">
                        <li class="flex gap-2">
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                            Use a clear branch name that matches local usage.
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                            Select the correct area so division reporting stays accurate.
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                            Keep inactive only for closed or suspended branches.
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-4">
                    <p class="text-[12px] font-medium text-navy-900">Need a new area?</p>
                    <p class="mt-1 text-[11px] text-slate-500">If the target area is missing, create it first then continue here.</p>
                    <a href="<?php echo e(route('areas.create')); ?>" class="mt-2.5 inline-flex text-[12px] font-semibold text-brand-600 hover:text-brand-700">
                        Create area →
                    </a>
                </div>
            </aside>
        </form>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/shakhas/create.blade.php ENDPATH**/ ?>