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
        $rowsJson = $rows;
    ?>

    <div
        class="px-4 py-4 lg:px-6"
        style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;"
        x-data="{
            q: '',
            category: '',
            subCategory: '',
            risk: '',
            onlyNew: false,
            page: 1,
            perPage: 25,
            rows: <?php echo \Illuminate\Support\Js::from($rowsJson)->toHtml() ?>,
            get categories() {
                return [...new Set(this.rows.map((r) => r.category).filter((v) => v && v !== '—'))].sort();
            },
            get subCategories() {
                return [...new Set(
                    this.rows
                        .filter((r) => !this.category || r.category === this.category)
                        .map((r) => r.sub_category)
                        .filter((v) => v && v !== '—')
                )].sort();
            },
            get risks() {
                return [...new Set(this.rows.map((r) => r.risk_rating).filter((v) => v && v !== '—'))].sort();
            },
            get filtered() {
                const q = this.q.trim().toLowerCase();
                return this.rows.filter((r) => {
                    if (this.onlyNew && !r.is_new) return false;
                    if (this.category && r.category !== this.category) return false;
                    if (this.subCategory && r.sub_category !== this.subCategory) return false;
                    if (this.risk && r.risk_rating !== this.risk) return false;
                    if (!q) return true;
                    const hay = (r.code + ' ' + r.title + ' ' + r.category + ' ' + r.sub_category + ' ' + r.risk_rating).toLowerCase();
                    return hay.includes(q);
                });
            },
            get totalPages() {
                return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
            },
            get paged() {
                const start = (this.page - 1) * this.perPage;
                return this.filtered.slice(start, start + this.perPage);
            },
            get fromRow() {
                return this.filtered.length === 0 ? 0 : (this.page - 1) * this.perPage + 1;
            },
            get toRow() {
                return Math.min(this.page * this.perPage, this.filtered.length);
            },
            get hitVisible() {
                return this.filtered.filter((r) => r.objected_branch_count > 0 || r.total_irregularities > 0).length;
            },
            riskClass(rating) {
                if (rating === 'Major') return 'bg-rose-50 text-rose-700';
                if (rating === 'Minor') return 'bg-slate-100 text-slate-600';
                return 'bg-amber-50 text-amber-700';
            },
            resetFilters() {
                this.q = '';
                this.category = '';
                this.subCategory = '';
                this.risk = '';
                this.onlyNew = false;
                this.page = 1;
            },
            showOnlyNew() {
                this.onlyNew = true;
                this.page = 1;
                this.$nextTick(() => {
                    document.getElementById('findings-matrix-table')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
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
        <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Audit Findings Consolidated</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Indicator totals across branches · Report Rating Box data syncs from audit reports · Front columns = org totals
                </p>
            </div>
            <form method="GET" action="<?php echo e(route('audit-findings.index')); ?>" class="flex flex-wrap items-center gap-1.5">
                <select name="month" class="h-8 rounded-lg border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($m = 1; $m <= 12; $m++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($m); ?>" <?php if($m === $month): echo 'selected'; endif; ?>><?php echo e(date('F', mktime(0, 0, 0, $m, 1))); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <select name="year" class="h-8 rounded-lg border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $yearOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($y); ?>" <?php if($y === $year): echo 'selected'; endif; ?>><?php echo e($y); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </form>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Period</p>
                <p class="mt-0.5 text-[14px] font-semibold text-navy-900"><?php echo e(date('F', mktime(0, 0, 0, $month, 1))); ?> <?php echo e($year); ?></p>
            </div>
            <div class="rounded-lg border border-sky-100 bg-sky-50/80 px-3 py-2">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700">Branches</p>
                <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-sky-900"><?php echo e($branchesInPeriod); ?></p>
            </div>
            <div class="rounded-lg border border-amber-100 bg-amber-50/80 px-3 py-2">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700">Finding cells</p>
                <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-amber-900"><?php echo e($findingsInPeriod); ?></p>
            </div>
            <div class="rounded-lg border border-rose-100 bg-rose-50/80 px-3 py-2">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-700">Indicators hit</p>
                <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-rose-900">
                    <span x-text="hitVisible"></span>
                    <span class="text-[11px] font-medium text-rose-700/70">/ <?php echo e($hitCount); ?></span>
                </p>
            </div>
            <div class="rounded-lg border border-violet-100 bg-violet-50/80 px-3 py-2">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-violet-700">New indicators</p>
                <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-violet-900"><?php echo e($newIndicatorsThisMonthCount); ?></p>
                <p class="mt-0.5 text-[10px] text-violet-700/80"><?php echo e($newIndicatorsMonthLabel); ?> · নতুন যোগ</p>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($newIndicatorsThisMonthCount > 0): ?>
            <div class="mb-3 overflow-hidden rounded-xl border border-violet-100 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-2 border-b border-violet-50 px-3 py-2">
                    <div>
                        <p class="text-[13px] font-semibold text-navy-900">এই মাসে নতুন indicator</p>
                        <p class="text-[10px] text-slate-500"><?php echo e($newIndicatorsMonthLabel); ?> · <?php echo e($newIndicatorsThisMonthCount); ?>টি যোগ হয়েছে</p>
                    </div>
                    <button
                        type="button"
                        @click="showOnlyNew()"
                        class="text-[11px] font-semibold text-[#2b579a] hover:underline"
                    >সব দেখুন</button>
                </div>
                <div class="max-h-44 divide-y divide-slate-100 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $newIndicatorsThisMonth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a
                            href="<?php echo e(route('audit-findings.show', ['indicator' => $indicator->id, 'month' => $month, 'year' => $year])); ?>"
                            class="flex items-start gap-2 px-3 py-2 hover:bg-violet-50/50"
                        >
                            <span class="mt-0.5 rounded bg-violet-50 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-violet-800">নতুন</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[12px] font-semibold text-navy-900"><?php echo e($indicator->title); ?></p>
                                <p class="mt-0.5 truncate text-[10px] text-slate-500">
                                    <span class="font-mono"><?php echo e($indicator->indicator_code); ?></span>
                                    · <?php echo e($indicator->created_at?->timezone('Asia/Dhaka')->format('d M, h:i A')); ?>

                                </p>
                            </div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-3 flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <div class="min-w-[160px] flex-1">
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Search</label>
                <input
                    type="search"
                    x-model="q"
                    @input="page = 1"
                    placeholder="Code, title, category…"
                    class="h-8 w-full rounded-lg border-slate-200 py-0 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]"
                    autocomplete="off"
                >
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
            <button
                type="button"
                @click="onlyNew = !onlyNew; page = 1"
                class="h-8 rounded-lg border px-3 text-[12px]"
                :class="onlyNew ? 'border-violet-300 bg-violet-50 font-semibold text-violet-800' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
            >নতুন only</button>
            <button type="button" @click="resetFilters()" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">Reset</button>
            <p class="ml-auto self-center text-[11px] text-slate-400">
                <span class="font-semibold tabular-nums text-slate-700" x-text="fromRow + '–' + toRow"></span>
                of <span class="tabular-nums" x-text="filtered.length"></span>
                <span class="text-slate-300">·</span>
                <span x-text="rows.length"></span> total
            </p>
        </div>

        <div id="findings-matrix-table" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-3 py-2">
                <p class="text-[12px] font-semibold text-navy-900">Organization totals (Excel left columns)</p>
                <p class="text-[10px] text-slate-500">Realtime filter · paginated · নতুন indicators appear in this list</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-[12px]">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="border-b border-slate-200 px-3 py-2">Category</th>
                            <th class="border-b border-slate-200 px-3 py-2">Sub-category</th>
                            <th class="border-b border-slate-200 px-3 py-2">Code</th>
                            <th class="border-b border-slate-200 px-3 py-2 min-w-[220px]">Indicator</th>
                            <th class="border-b border-slate-200 px-3 py-2">Risk</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Amount</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Samples</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Irregularities</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Objected branches</th>
                            <th class="border-b border-slate-200 px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in paged" :key="row.indicator_id">
                            <tr class="border-b border-slate-50 hover:bg-sky-50/40" :class="row.is_new ? 'bg-violet-50/40' : (row.objected_branch_count > 0 ? '' : 'opacity-70')">
                                <td class="px-3 py-2 text-[11px] text-slate-600" x-text="row.category"></td>
                                <td class="px-3 py-2 text-[11px] text-slate-500" x-text="row.sub_category"></td>
                                <td class="px-3 py-2 font-mono text-[11px] text-slate-700" x-text="row.code"></td>
                                <td class="px-3 py-2 font-medium text-navy-900">
                                    <span class="inline-flex flex-wrap items-center gap-1.5">
                                        <span x-show="row.is_new" class="rounded bg-violet-100 px-1.5 py-0.5 text-[10px] font-semibold text-violet-800">নতুন</span>
                                        <span x-text="row.title"></span>
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold" :class="riskClass(row.risk_rating)" x-text="row.risk_rating"></span>
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums" x-text="row.total_amount_fmt"></td>
                                <td class="px-3 py-2 text-right tabular-nums" x-text="row.total_samples_checked"></td>
                                <td class="px-3 py-2 text-right tabular-nums font-semibold" :class="row.total_irregularities > 0 ? 'text-rose-700' : 'text-slate-500'" x-text="row.total_irregularities"></td>
                                <td class="px-3 py-2 text-right tabular-nums" x-text="row.objected_branch_count"></td>
                                <td class="px-3 py-2 text-right">
                                    <a :href="row.branches_url" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Branches</a>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filtered.length === 0">
                            <td colspan="10" class="px-3 py-10 text-center text-[12px] text-slate-400">
                                <span x-show="rows.length === 0">
                                    No indicators yet. Import with
                                    <span class="font-medium text-navy-800">php artisan audit:import-indicators path/to/file.xlsx</span>
                                </span>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audit-findings/index.blade.php ENDPATH**/ ?>