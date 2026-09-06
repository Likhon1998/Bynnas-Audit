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
        $selectedShakha = $filters['shakha_id']
            ? $shakhas->firstWhere('id', (int) $filters['shakha_id'])
            : null;

        $areaOptions = $areas->map(fn ($area) => [
            'id' => (string) $area->id,
            'name' => $area->name,
            'shakhas' => $area->shakhas->map(fn ($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
            ])->values(),
        ])->values();
    ?>

    <div
        class="px-4 py-5 lg:px-6"
        x-data="{
            addOpen: false,
            areaId: <?php echo \Illuminate\Support\Js::from((string) ($filters['area_id'] ?? ''))->toHtml() ?>,
            shakhaId: <?php echo \Illuminate\Support\Js::from((string) ($filters['shakha_id'] ?? ''))->toHtml() ?>,
            areas: <?php echo \Illuminate\Support\Js::from($areaOptions)->toHtml() ?>,
            get shakhasForArea() {
                const area = this.areas.find((a) => a.id === String(this.areaId));
                return area ? area.shakhas : [];
            },
            onAreaChange() {
                if (!this.shakhasForArea.some((s) => s.id === String(this.shakhaId))) {
                    this.shakhaId = '';
                }
            },
            manageUrl() {
                if (!this.shakhaId) return '';
                return `<?php echo e(url('/shakhas')); ?>/${this.shakhaId}/employees`;
            },
            goAdd() {
                if (!this.shakhaId) return;
                window.location.href = this.manageUrl();
            }
        }"
    >
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">Shakha Employees</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Add and manage branch staff under Area → Shakha
                </p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedShakha): ?>
                    <a
                        href="<?php echo e(route('shakha-employees.manage', $selectedShakha)); ?>"
                        class="inline-flex h-9 items-center gap-1 rounded-lg bg-navy-900 px-3.5 text-[12px] font-semibold text-white hover:bg-navy-800"
                    >
                        <span class="text-[14px] leading-none">+</span>
                        Add employee to <?php echo e($selectedShakha->name); ?>

                    </a>
                <?php else: ?>
                    <button
                        type="button"
                        @click="addOpen = true"
                        class="inline-flex h-9 items-center gap-1 rounded-lg bg-navy-900 px-3.5 text-[12px] font-semibold text-white hover:bg-navy-800"
                    >
                        <span class="text-[14px] leading-none">+</span>
                        Add employee
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div
            x-show="addOpen"
            x-cloak
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4"
            @keydown.escape.window="addOpen = false"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-4 shadow-xl" @click.outside="addOpen = false">
                <div class="mb-3">
                    <p class="text-[14px] font-semibold text-navy-900">Add employee</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Choose the area and shakha first, then enter employee details.</p>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Area</label>
                        <select x-model="areaId" @change="onAreaChange()" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                            <option value="">Select area…</option>
                            <template x-for="area in areas" :key="area.id">
                                <option :value="area.id" x-text="area.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Shakha</label>
                        <select x-model="shakhaId" class="h-9 w-full rounded-lg border-slate-200 text-[12px]" :disabled="!areaId">
                            <option value="">Select shakha…</option>
                            <template x-for="shakha in shakhasForArea" :key="shakha.id">
                                <option :value="shakha.id" x-text="shakha.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-2">
                    <button type="button" @click="addOpen = false" class="h-8 rounded-lg px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button
                        type="button"
                        @click="goAdd()"
                        :disabled="!shakhaId"
                        class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800 disabled:cursor-not-allowed disabled:opacity-40"
                    >Continue</button>
                </div>
            </div>
        </div>

        <form method="GET" action="<?php echo e(route('shakha-employees.index')); ?>" class="mb-4 rounded-xl border border-slate-100 bg-white p-3 shadow-card">
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-2">
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Area</label>
                    <select name="area_id" class="h-8 w-full rounded-lg border-slate-200 text-[12px]" onchange="this.form.shakha_id.value=''; this.form.submit()">
                        <option value="">All areas</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($area->id); ?>" <?php if((int) $filters['area_id'] === $area->id): echo 'selected'; endif; ?>><?php echo e($area->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Shakha</label>
                    <select name="shakha_id" class="h-8 w-full rounded-lg border-slate-200 text-[12px]" onchange="this.form.submit()">
                        <option value="">All shakhas</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $shakhas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shakha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($shakha->id); ?>" <?php if((int) $filters['shakha_id'] === $shakha->id): echo 'selected'; endif; ?>><?php echo e($shakha->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Status</label>
                    <select name="status" class="h-8 w-full rounded-lg border-slate-200 text-[12px]" onchange="this.form.submit()">
                        <option value="all" <?php if($filters['status'] === 'all'): echo 'selected'; endif; ?>>All</option>
                        <option value="active" <?php if($filters['status'] === 'active'): echo 'selected'; endif; ?>>Active</option>
                        <option value="inactive" <?php if($filters['status'] === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                    </select>
                </div>
                <div class="lg:col-span-5">
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Search roster</label>
                    <div class="flex gap-2">
                        <input
                            type="search"
                            name="q"
                            value="<?php echo e($filters['q']); ?>"
                            placeholder="Employee ID, name, designation…"
                            class="h-8 w-full rounded-lg border-slate-200 text-[12px]"
                        >
                        <button type="submit" class="inline-flex h-8 shrink-0 items-center rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Search</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="mb-4 grid gap-3 lg:grid-cols-[280px_minmax(0,1fr)]">
            <div
                class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card"
                x-data="{
                    branchQ: '',
                    branches: <?php echo \Illuminate\Support\Js::from($byShakha->map(fn ($b) => [
                        'id' => $b->id,
                        'name' => $b->name,
                        'area' => (string) ($b->area?->name ?: ''),
                        'count' => (int) $b->employees_count,
                        'url' => route('shakha-employees.manage', $b),
                        'selected' => (int) $filters['shakha_id'] === $b->id,
                    ])->values())->toHtml() ?>,
                    get filteredBranches() {
                        const q = this.branchQ.trim().toLowerCase();
                        if (!q) return this.branches;
                        return this.branches.filter((b) =>
                            (b.name + ' ' + b.area).toLowerCase().includes(q)
                        );
                    }
                }"
            >
                <div class="border-b border-slate-100 px-3.5 py-2.5">
                    <p class="text-[12px] font-semibold text-navy-900">Shakhas</p>
                    <p class="mt-0.5 text-[10px] text-slate-500">Search, then open a branch to manage staff</p>
                    <div class="relative mt-2">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                        <input
                            type="search"
                            x-model="branchQ"
                            placeholder="Search shakha or area…"
                            class="h-8 w-full rounded-lg border-slate-200 pl-8 text-[12px] placeholder:text-slate-400"
                        >
                    </div>
                </div>
                <div class="max-h-[28rem] divide-y divide-slate-100 overflow-y-auto">
                    <template x-for="branch in filteredBranches" :key="branch.id">
                        <a
                            :href="branch.url"
                            class="flex items-start justify-between gap-2 px-3.5 py-2.5 hover:bg-slate-50"
                            :class="branch.selected ? 'bg-sky-50' : ''"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-[12px] font-medium text-navy-900" x-text="branch.name"></p>
                                <p class="truncate text-[10px] text-slate-500" x-text="branch.area || '—'"></p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600" x-text="branch.count"></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
                                    <span class="text-[10px] font-semibold text-[#2b579a]">+ Add</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </a>
                    </template>
                    <p x-show="filteredBranches.length === 0" class="px-3.5 py-8 text-center text-[12px] text-slate-400">
                        No shakhas match your search.
                    </p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-2.5">
                    <div>
                        <p class="text-[12px] font-semibold text-navy-900">Employees</p>
                        <p class="text-[10px] text-slate-500"><?php echo e($employees->count()); ?> matching</p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage && $selectedShakha): ?>
                        <a href="<?php echo e(route('shakha-employees.manage', $selectedShakha)); ?>" class="text-[11px] font-semibold text-[#2b579a] hover:underline">
                            Open <?php echo e($selectedShakha->name); ?> form →
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-slate-100 bg-slate-50/80">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-3.5 py-2.5">Photo</th>
                                <th class="px-3.5 py-2.5">Employee ID</th>
                                <th class="px-3.5 py-2.5">Name</th>
                                <th class="px-3.5 py-2.5">Designation</th>
                                <th class="px-3.5 py-2.5">Area / Shakha</th>
                                <th class="px-3.5 py-2.5">Status</th>
                                <th class="px-3.5 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="text-[12px]">
                                    <td class="px-3.5 py-2.5">
                                        <?php echo $__env->make('shakha-employees.partials.photo', ['employee' => $employee, 'size' => 'sm'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </td>
                                    <td class="px-3.5 py-2.5 font-semibold text-navy-900"><?php echo e($employee->employee_code); ?></td>
                                    <td class="px-3.5 py-2.5 text-slate-700"><?php echo e($employee->name); ?></td>
                                    <td class="px-3.5 py-2.5 text-slate-600"><?php echo e($employee->designation); ?></td>
                                    <td class="px-3.5 py-2.5 text-slate-600">
                                        <span class="block"><?php echo e($employee->shakha?->name); ?></span>
                                        <span class="text-[10px] text-slate-400"><?php echo e($employee->shakha?->area?->name); ?></span>
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee->isActive()): ?>
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Active</span>
                                        <?php else: ?>
                                            <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-600">Inactive</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-3.5 py-2.5 text-right">
                                        <a href="<?php echo e(route('shakha-employees.manage', $employee->shakha)); ?>" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Manage</a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
                                            <a href="<?php echo e(route('shakha-employees.edit', $employee)); ?>" class="ml-2 text-[11px] font-semibold text-slate-600 hover:underline">Edit</a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="7" class="px-3.5 py-10 text-center text-[12px] text-slate-400">
                                        <p>No employees yet for these filters.</p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
                                            <button type="button" @click="addOpen = true" class="mt-2 font-semibold text-[#2b579a] hover:underline">
                                                + Add the first employee
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/shakha-employees/index.blade.php ENDPATH**/ ?>