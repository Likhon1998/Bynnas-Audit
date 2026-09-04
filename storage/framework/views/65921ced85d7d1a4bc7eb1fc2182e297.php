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
        class="px-4 py-4 lg:px-6"
        style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;"
        x-data="{
            q: '',
            category: '',
            subCategory: '',
            risk: '',
            page: 1,
            perPage: 25,
            rows: <?php echo \Illuminate\Support\Js::from($indicatorRows)->toHtml() ?>,
            get categories() {
                return [...new Set(this.rows.map((r) => r.category).filter(Boolean))].sort();
            },
            get subCategories() {
                return [...new Set(
                    this.rows
                        .filter((r) => !this.category || r.category === this.category)
                        .map((r) => r.sub_category)
                        .filter(Boolean)
                )].sort();
            },
            get risks() {
                return [...new Set(this.rows.map((r) => r.risk_rating).filter(Boolean))].sort();
            },
            get filtered() {
                const q = this.q.trim().toLowerCase();
                return this.rows.filter((r) => this.matches(r, q));
            },
            get totalPages() {
                return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
            },
            get pagedIds() {
                const start = (this.page - 1) * this.perPage;
                return new Set(this.filtered.slice(start, start + this.perPage).map((r) => r.id));
            },
            get fromRow() {
                return this.filtered.length === 0 ? 0 : (this.page - 1) * this.perPage + 1;
            },
            get toRow() {
                return Math.min(this.page * this.perPage, this.filtered.length);
            },
            matches(r, q = null) {
                const needle = q === null ? this.q.trim().toLowerCase() : q;
                if (this.category && r.category !== this.category) return false;
                if (this.subCategory && r.sub_category !== this.subCategory) return false;
                if (this.risk && r.risk_rating !== this.risk) return false;
                if (!needle) return true;
                const hay = (r.indicator_code + ' ' + r.title + ' ' + r.category + ' ' + r.sub_category + ' ' + r.risk_rating).toLowerCase();
                return hay.includes(needle);
            },
            rowVisible(r) {
                return this.matches(r) && this.pagedIds.has(r.id);
            },
            resetFilters() {
                this.q = '';
                this.category = '';
                this.subCategory = '';
                this.risk = '';
                this.page = 1;
            },
            onCategoryChange() {
                this.subCategory = '';
                this.page = 1;
            },
            go(p) {
                this.page = Math.min(Math.max(1, p), this.totalPages);
            }
        }"
        x-effect="if (page > totalPages) page = totalPages"
    >
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="<?php echo e(route('audit-findings.index', ['month' => $month, 'year' => $year])); ?>" class="mb-1 inline-flex items-center gap-1 text-[11px] font-medium text-[#2b579a] hover:underline">
                    ← Consolidated totals
                </a>
                <h1 class="text-[16px] font-semibold text-navy-900">Enter findings</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    <?php echo e($shakha->name); ?><?php echo e($shakha->code ? ' ('.$shakha->code.')' : ''); ?>

                    · <?php echo e($shakha->area?->name); ?>

                    · <?php echo e(date('F', mktime(0, 0, 0, $month, 1))); ?> <?php echo e($year); ?>

                    · Filters are realtime · Leave blank = no cell
                </p>
            </div>
            <a href="<?php echo e(route('audits.index')); ?>" class="inline-flex h-8 items-center rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">Audit Reports</a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-3 flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <div class="min-w-[160px] flex-1">
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Search</label>
                <input type="search" x-model="q" @input="page = 1" class="h-8 w-full rounded-lg border-slate-200 py-0 text-[12px]" placeholder="Code / title…" autocomplete="off">
            </div>
            <div class="w-[180px]">
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Category</label>
                <select x-model="category" @change="onCategoryChange()" class="h-8 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                    <option value="">All</option>
                    <template x-for="c in categories" :key="c">
                        <option :value="c" x-text="c"></option>
                    </template>
                </select>
            </div>
            <div class="w-[180px]">
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Sub-category</label>
                <select x-model="subCategory" @change="page = 1" class="h-8 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                    <option value="">All</option>
                    <template x-for="s in subCategories" :key="s">
                        <option :value="s" x-text="s"></option>
                    </template>
                </select>
            </div>
            <div class="w-[120px]">
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Risk</label>
                <select x-model="risk" @change="page = 1" class="h-8 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                    <option value="">All</option>
                    <template x-for="r in risks" :key="r">
                        <option :value="r" x-text="r"></option>
                    </template>
                </select>
            </div>
            <div class="w-[90px]">
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Per page</label>
                <select x-model.number="perPage" @change="page = 1" class="h-8 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
            </div>
            <button type="button" @click="resetFilters()" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">Reset</button>
            <p class="ml-auto self-center text-[11px] text-slate-400">
                <span class="font-semibold tabular-nums text-slate-700" x-text="fromRow + '–' + toRow"></span>
                of <span x-text="filtered.length"></span>
            </p>
        </div>

        <form method="POST" action="<?php echo e(route('audit-findings.entry.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="shakha_id" value="<?php echo e($shakha->id); ?>">
            <input type="hidden" name="audit_month" value="<?php echo e($month); ?>">
            <input type="hidden" name="audit_year" value="<?php echo e($year); ?>">

            <div class="mb-3 flex justify-end">
                <button type="submit" class="h-9 rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072]">Save findings</button>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-[12px]">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="border-b border-slate-200 px-2 py-2">Code</th>
                                <th class="border-b border-slate-200 px-2 py-2 min-w-[180px]">Indicator</th>
                                <th class="border-b border-slate-200 px-2 py-2">Amount</th>
                                <th class="border-b border-slate-200 px-2 py-2">Samples</th>
                                <th class="border-b border-slate-200 px-2 py-2">Irregularities</th>
                                <th class="border-b border-slate-200 px-2 py-2 min-w-[160px]">Observation</th>
                                <th class="border-b border-slate-200 px-2 py-2">Staff</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="row in rows" :key="row.id">
                                <tr class="border-b border-slate-50 align-top hover:bg-sky-50/30" x-show="rowVisible(row)" x-cloak>
                                    <td class="px-2 py-1.5 font-mono text-[11px] text-slate-600" x-text="row.indicator_code"></td>
                                    <td class="px-2 py-1.5">
                                        <p class="font-medium text-navy-900" x-text="row.title"></p>
                                        <p class="text-[10px] text-slate-400">
                                            <span x-text="row.category || '—'"></span>
                                            <span x-show="row.sub_category"> · <span x-text="row.sub_category"></span></span>
                                            · <span x-text="row.risk_rating || '—'"></span>
                                        </p>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="number" step="0.01" :name="'findings['+row.id+'][amount]'" x-model="row.amount" class="h-8 w-28 rounded-md border-slate-200 py-0 text-[12px]">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="number" min="0" :name="'findings['+row.id+'][sample_size_checked]'" x-model="row.sample_size_checked" class="h-8 w-20 rounded-md border-slate-200 py-0 text-[12px]">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="number" min="0" :name="'findings['+row.id+'][irregularity_count]'" x-model="row.irregularity_count" class="h-8 w-20 rounded-md border-slate-200 py-0 text-[12px]">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <textarea :name="'findings['+row.id+'][observation]'" rows="2" class="w-full rounded-md border-slate-200 text-[12px]" x-model="row.observation"></textarea>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" :name="'findings['+row.id+'][responsible_staff_name]'" x-model="row.responsible_staff_name" class="h-8 w-36 rounded-md border-slate-200 py-0 text-[12px]">
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filtered.length === 0">
                                <td colspan="7" class="px-3 py-10 text-center text-[12px] text-slate-400">
                                    <span x-show="rows.length === 0">No indicators. Seed or import the catalog first.</span>
                                    <span x-show="rows.length > 0">No rows match these filters.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 px-3 py-2.5" x-show="filtered.length > 0">
                    <p class="text-[11px] text-slate-500">
                        Page <span class="font-semibold tabular-nums text-slate-700" x-text="page"></span>
                        / <span class="tabular-nums" x-text="totalPages"></span>
                    </p>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="go(1)" :disabled="page <= 1" class="h-7 rounded-md border border-slate-200 px-2 text-[11px] font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">First</button>
                        <button type="button" @click="go(page - 1)" :disabled="page <= 1" class="h-7 rounded-md border border-slate-200 px-2 text-[11px] font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">Prev</button>
                        <button type="button" @click="go(page + 1)" :disabled="page >= totalPages" class="h-7 rounded-md border border-slate-200 px-2 text-[11px] font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">Next</button>
                        <button type="button" @click="go(totalPages)" :disabled="page >= totalPages" class="h-7 rounded-md border border-slate-200 px-2 text-[11px] font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">Last</button>
                    </div>
                </div>
            </div>

            <div class="mt-3 flex justify-end">
                <button type="submit" class="h-9 rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072]">Save findings</button>
            </div>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audit-findings\entry.blade.php ENDPATH**/ ?>