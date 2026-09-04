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

    <div
        class="px-4 py-5 lg:px-6"
        x-data="{
            q: '',
            area: '',
            division: '',
            kpi: 'all',
            riskTab: 'all',
            rows: <?php echo \Illuminate\Support\Js::from($rows)->toHtml() ?>,
            riskBadge(risk) {
                return {
                    'Low Risk': 'bg-emerald-50 text-emerald-700',
                    'Medium Risk': 'bg-amber-50 text-amber-700',
                    'High Risk': 'bg-orange-50 text-orange-700',
                    'Significant Risk': 'bg-rose-50 text-rose-700',
                }[risk] || 'bg-slate-50 text-slate-400';
            },
            get areasForDivision() {
                const names = this.rows
                    .filter((row) => !this.division || row.division === this.division)
                    .map((row) => row.area)
                    .filter(Boolean);
                return [...new Set(names)].sort((a, b) => a.localeCompare(b));
            },
            setDivision(value) {
                this.division = value;
                if (this.area && !this.areasForDivision.includes(this.area)) {
                    this.area = '';
                }
            },
            get filtered() {
                const q = this.q.trim().toLowerCase();
                return this.rows.filter((row) => {
                    if (this.riskTab !== 'all' && row.risk !== this.riskTab) return false;
                    if (this.area && row.area !== this.area) return false;
                    if (this.division && row.division !== this.division) return false;
                    if (this.kpi === 'ready' && !row.kpi_ready) return false;
                    if (this.kpi === 'missing' && row.kpi_ready) return false;
                    if (!q) return true;
                    const hay = (row.name + ' ' + row.code + ' ' + row.area + ' ' + row.division).toLowerCase();
                    return hay.includes(q);
                });
            },
            get visibleCount() { return this.filtered.length; },
            clearFilters() {
                this.q = '';
                this.area = '';
                this.division = '';
                this.kpi = 'all';
            }
        }"
    >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">All Shakha</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Manage branches across areas and divisions · <?php echo e($rows->count()); ?> total · KPI FY <?php echo e($fyLabel); ?>

                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="<?php echo e(route('shakhas.risk.export', ['fy' => $fyLabel])); ?>"
                    class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[12px] font-medium text-emerald-800 hover:bg-emerald-100"
                >
                    Export Risk Excel
                </a>
                <a href="<?php echo e(route('shakhas.create')); ?>" class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                    <span class="text-[13px] leading-none">+</span>
                    Add Shakha
                </a>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-3 rounded-xl border border-sky-100 bg-sky-50 px-3.5 py-2.5 text-[12px] text-sky-900">
            <span class="font-semibold">KPI required before risk.</span>
            Enter annual KPI for FY <?php echo e($fyLabel); ?> first. Until then, Risk stays locked and the row shows KPI Not ready.
        </div>

        
        <div class="mb-3 flex flex-wrap gap-1.5 rounded-xl border border-slate-100 bg-white p-1.5 shadow-card">
            <?php
                $tabs = [
                    ['key' => 'all', 'label' => 'All', 'count' => $riskCounts['all'], 'active' => 'bg-navy-900 text-white', 'idle' => 'text-slate-600 hover:bg-slate-50'],
                    ['key' => 'Low Risk', 'label' => 'Low', 'count' => $riskCounts['Low Risk'], 'active' => 'bg-emerald-600 text-white', 'idle' => 'text-emerald-700 hover:bg-emerald-50'],
                    ['key' => 'Medium Risk', 'label' => 'Medium', 'count' => $riskCounts['Medium Risk'], 'active' => 'bg-amber-500 text-white', 'idle' => 'text-amber-700 hover:bg-amber-50'],
                    ['key' => 'High Risk', 'label' => 'High', 'count' => $riskCounts['High Risk'], 'active' => 'bg-orange-500 text-white', 'idle' => 'text-orange-700 hover:bg-orange-50'],
                    ['key' => 'Significant Risk', 'label' => 'Significant', 'count' => $riskCounts['Significant Risk'], 'active' => 'bg-rose-600 text-white', 'idle' => 'text-rose-700 hover:bg-rose-50'],
                    ['key' => 'Not assessed', 'label' => 'Not assessed', 'count' => $riskCounts['Not assessed'], 'active' => 'bg-slate-600 text-white', 'idle' => 'text-slate-600 hover:bg-slate-50'],
                ];
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <button
                    type="button"
                    @click="riskTab = <?php echo \Illuminate\Support\Js::from($tab['key'])->toHtml() ?>"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[12px] font-medium transition"
                    :class="riskTab === <?php echo \Illuminate\Support\Js::from($tab['key'])->toHtml() ?> ? <?php echo \Illuminate\Support\Js::from($tab['active'])->toHtml() ?> : <?php echo \Illuminate\Support\Js::from($tab['idle'])->toHtml() ?>"
                >
                    <?php echo e($tab['label']); ?>

                    <span
                        class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold"
                        :class="riskTab === <?php echo \Illuminate\Support\Js::from($tab['key'])->toHtml() ?> ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'"
                    ><?php echo e($tab['count']); ?></span>
                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        
        <div class="mb-3 grid gap-2 rounded-xl border border-slate-100 bg-white p-3 shadow-card sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Search</label>
                <input
                    type="search"
                    x-model="q"
                    placeholder="Name, code, area…"
                    class="h-9 w-full rounded-lg border-slate-200 text-[12px] shadow-sm focus:border-brand-500 focus:ring-brand-500"
                >
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Division</label>
                <select
                    :value="division"
                    @change="setDivision($event.target.value)"
                    class="h-9 w-full rounded-lg border-slate-200 text-[12px]"
                >
                    <option value="">All divisions</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($division); ?>"><?php echo e($division); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Area</label>
                <select
                    x-model="area"
                    class="h-9 w-full rounded-lg border-slate-200 text-[12px]"
                    :disabled="!division"
                >
                    <option value="" x-text="division ? 'All areas in division' : 'Select division first'"></option>
                    <template x-for="name in areasForDivision" :key="name">
                        <option :value="name" x-text="name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">KPI (<?php echo e($fyLabel); ?>)</label>
                <select x-model="kpi" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                    <option value="all">All</option>
                    <option value="ready">KPI ready</option>
                    <option value="missing">KPI missing</option>
                </select>
            </div>
        </div>

        <div class="mb-2 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
            <p>Showing <span class="font-semibold text-navy-900" x-text="visibleCount"></span> of <?php echo e($rows->count()); ?></p>
            <button type="button" @click="clearFilters()" class="font-medium text-brand-600 hover:underline">Clear filters</button>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            <th class="w-14 px-4 py-2.5">#</th>
                            <th class="px-4 py-2.5">Shakha Name</th>
                            <th class="px-4 py-2.5">Area</th>
                            <th class="px-4 py-2.5">Division</th>
                            <th class="px-4 py-2.5">Code</th>
                            <th class="px-4 py-2.5">Opening date</th>
                            <th class="px-4 py-2.5">KPI Ready</th>
                            <th class="px-4 py-2.5">Risk Status</th>
                            <th class="px-4 py-2.5">Added On</th>
                            <th class="px-4 py-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(row, index) in filtered" :key="row.id">
                            <tr class="text-[12px]">
                                <td class="px-4 py-2.5 tabular-nums text-slate-400" x-text="index + 1"></td>
                                <td class="px-4 py-2.5 font-medium text-navy-900" x-text="row.name"></td>
                                <td class="px-4 py-2.5 text-slate-600" x-text="row.area || '—'"></td>
                                <td class="px-4 py-2.5 text-slate-600" x-text="row.division || '—'"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="row.code || '—'"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="row.opening"></td>
                                <td class="px-4 py-2.5">
                                    <template x-if="row.kpi_ready">
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Ready</span>
                                    </template>
                                    <template x-if="!row.kpi_ready">
                                        <a
                                            :href="row.kpi_url"
                                            class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-800 hover:bg-amber-100"
                                            title="Enter annual KPI first"
                                        >Not ready — enter KPI</a>
                                    </template>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-medium"
                                        :class="riskBadge(row.risk)"
                                        :title="row.risk_score != null ? ('Score ' + row.risk_score + (row.risk_period ? ' · ' + row.risk_period : '')) : ''"
                                        x-text="row.risk"
                                    ></span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="row.added_on"></td>
                                <td class="px-4 py-2.5 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <template x-if="row.kpi_ready">
                                            <a
                                                :href="row.risk_url"
                                                class="inline-flex rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-600 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-800"
                                            >Risk</a>
                                        </template>
                                        <template x-if="!row.kpi_ready">
                                            <span
                                                class="inline-flex cursor-not-allowed rounded-lg border border-slate-100 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-400"
                                                title="Complete annual KPI for this FY first, then open Risk."
                                            >Risk locked</span>
                                        </template>
                                        <a
                                            :href="row.edit_url"
                                            class="inline-flex rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-600 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"
                                        >Edit</a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filtered.length === 0">
                            <td colspan="10" class="px-4 py-10 text-center text-[12px] text-slate-400">
                                No shakhas match these filters.
                                <button type="button" @click="clearFilters(); riskTab = 'all'" class="font-medium text-brand-600 hover:underline">Reset</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\shakhas\index.blade.php ENDPATH**/ ?>