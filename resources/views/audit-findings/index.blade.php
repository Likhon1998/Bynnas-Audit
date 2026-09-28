@php
    $monthName = date('F', mktime(0, 0, 0, $month, 1));
    $btn = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3 text-[12px] font-semibold transition hover:-translate-y-0.5';
    $cards = [
        ['label' => 'Branches', 'value' => $branchesInPeriod, 'hint' => 'with findings this month', 'tone' => 'from-[#1b3a70] to-[#2b579a]'],
        ['label' => 'Finding cells', 'value' => $findingsInPeriod, 'hint' => 'indicator × branch entries', 'tone' => 'from-sky-400 to-sky-600'],
        ['label' => 'Indicators hit', 'value' => $hitCount.' / '.count($rows), 'hint' => 'have at least one finding', 'tone' => 'from-rose-400 to-rose-600'],
        ['label' => 'New indicators', 'value' => $newIndicatorsThisMonthCount, 'hint' => 'added in '.$newIndicatorsMonthLabel, 'tone' => 'from-violet-400 to-violet-600'],
    ];
    $select = 'h-8 rounded-lg border-slate-200 py-0 pl-2.5 pr-8 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]';
@endphp
<x-app-layout>
    <div
        class="space-y-3 px-3 py-3 lg:px-5"
        style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;"
        x-data="findingsMatrix({ rows: @js($rows) })"
        x-effect="if (page > totalPages) page = totalPages"
    >
        <x-findings-header
            title="Findings Matrix"
            :subtitle="'Organization totals · indicators × shakhas · '.$monthName.' '.$year"
            active-tab="matrix"
            :month="$month"
            :year="$year"
            :month-strip="$monthStrip"
            :prev-year-url="$prevYearUrl"
            :next-year-url="$nextYearUrl"
        >
            <a
                href="{{ $exportYearUrl }}"
                class="{{ $btn }} border border-slate-200 bg-white text-slate-700 hover:border-emerald-300 hover:text-emerald-700"
                title="All 12 months of {{ $year }} — one sheet per month, indicators × shakhas"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 3v10m0 0-4-4m4 4 4-4M4 16h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                All of {{ $year }}
            </a>
            <a
                href="{{ $exportUrl }}"
                class="{{ $btn }} bg-emerald-600 text-white shadow-[0_6px_14px_rgba(5,150,105,0.3)] hover:bg-emerald-700"
                title="{{ $monthName }} {{ $year }} — indicators × shakhas matrix"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 3v10m0 0-4-4m4 4 4-4M4 16h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Download {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}
            </a>
        </x-findings-header>

        @if (session('status'))
            <div data-flash class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-[12px] font-medium text-emerald-800">
                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>{{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
            @foreach ($cards as $card)
                <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <span class="h-9 w-1.5 shrink-0 rounded-full bg-gradient-to-b {{ $card['tone'] }}"></span>
                    <div class="min-w-0">
                        <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-0.5 truncate text-[19px] font-bold leading-none tabular-nums text-navy-900">{{ $card['value'] }}</p>
                        <p class="mt-1 truncate text-[11px] text-slate-500">{{ $card['hint'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($newIndicatorsThisMonthCount > 0)
            <section class="rounded-2xl border border-violet-200 bg-gradient-to-r from-violet-50 to-white px-3 py-2 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-violet-600 px-2 py-0.5 text-[11px] font-bold text-white">নতুন</span>
                    <p class="text-[12.5px] font-semibold text-violet-900">এই মাসে {{ $newIndicatorsThisMonthCount }}টি নতুন indicator যোগ হয়েছে</p>
                    <button type="button" @click="showOnlyNew()" class="ml-auto text-[12px] font-semibold text-violet-700 hover:text-violet-900 hover:underline">শুধু নতুনগুলো দেখুন</button>
                </div>
                <div class="mt-1.5 flex flex-wrap gap-1.5">
                    @foreach ($newIndicatorsThisMonth->take(6) as $indicator)
                        <a
                            href="{{ route('audit-findings.show', ['indicator' => $indicator->id, 'month' => $month, 'year' => $year]) }}"
                            class="inline-flex max-w-full items-center gap-1.5 rounded-lg border border-violet-200 bg-white px-2 py-1 text-[12px] text-slate-700 transition hover:border-violet-400 hover:text-violet-800"
                            title="{{ $indicator->title }} · {{ bd_datetime($indicator->created_at, \App\Support\AppTime::DATETIME_SHORT) }}"
                        >
                            <span class="font-mono text-[11px] font-semibold text-violet-700">{{ $indicator->indicator_code }}</span>
                            <span class="max-w-[280px] truncate">{{ $indicator->title }}</span>
                        </a>
                    @endforeach
                    @if ($newIndicatorsThisMonthCount > 6)
                        <button type="button" @click="showOnlyNew()" class="rounded-lg px-2 py-1 text-[12px] font-semibold text-violet-700 hover:bg-violet-100">+{{ $newIndicatorsThisMonthCount - 6 }} more</button>
                    @endif
                </div>
            </section>
        @endif

        <section id="findings-matrix-table" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                <div class="relative min-w-[180px] flex-1">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="9" r="5.5"/><path d="m13.5 13.5 3 3" stroke-linecap="round"/></svg>
                    <input
                        type="search"
                        x-model="q"
                        @input="page = 1"
                        placeholder="Search code, indicator or category"
                        class="h-8 w-full rounded-lg border-slate-200 pl-8 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]"
                        autocomplete="off"
                    >
                </div>
                <select x-model="category" @change="onCategoryChange()" class="{{ $select }} max-w-[190px]" aria-label="Category">
                    <option value="">All categories</option>
                    <template x-for="c in categories" :key="c">
                        <option :value="c" x-text="c"></option>
                    </template>
                </select>
                <select x-model="subCategory" @change="page = 1" class="{{ $select }} max-w-[170px]" aria-label="Sub-category" :disabled="subCategories.length === 0">
                    <option value="">All sub-categories</option>
                    <template x-for="s in subCategories" :key="s">
                        <option :value="s" x-text="s"></option>
                    </template>
                </select>
                <select x-model="risk" @change="page = 1" class="{{ $select }}" aria-label="Risk" x-show="risks.length > 0">
                    <option value="">Any risk</option>
                    <template x-for="r in risks" :key="r">
                        <option :value="r" x-text="r"></option>
                    </template>
                </select>
                <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-[12px] font-semibold">
                    <button type="button" class="rounded-md px-2.5 py-1 transition" :class="scope === 'all' ? 'bg-[#1b3a70] text-white' : 'text-slate-600 hover:bg-slate-50'" @click="scope = 'all'; page = 1">All</button>
                    <button type="button" class="rounded-md px-2.5 py-1 transition" :class="scope === 'hit' ? 'bg-rose-600 text-white' : 'text-slate-600 hover:bg-slate-50'" @click="scope = 'hit'; page = 1">With findings</button>
                    <button type="button" class="rounded-md px-2.5 py-1 transition" :class="scope === 'new' ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-50'" @click="scope = 'new'; page = 1">নতুন</button>
                </div>
                <button type="button" x-show="isFiltered" x-cloak @click="resetFilters()" class="h-8 rounded-lg px-2.5 text-[12px] font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-800">Clear</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12.5px]">
                    <thead>
                        <tr class="border-b border-slate-200 text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">
                            <th class="whitespace-nowrap px-3 py-2">Code</th>
                            <th class="min-w-[260px] px-3 py-2">Indicator</th>
                            <th class="px-3 py-2">Risk</th>
                            <th class="whitespace-nowrap px-3 py-2 text-right">Amount</th>
                            <th class="px-3 py-2 text-right">Samples</th>
                            <th class="whitespace-nowrap px-3 py-2 text-right">Irregularities</th>
                            <th class="px-3 py-2 text-right">Branches</th>
                            <th class="w-6 px-2 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="row in paged" :key="row.indicator_id">
                            <tr
                                class="group cursor-pointer align-top transition hover:bg-sky-50/50"
                                :class="row.is_new ? 'bg-violet-50/40' : ''"
                                @click="window.location = row.branches_url"
                            >
                                <td class="whitespace-nowrap px-3 py-2.5 font-mono text-[12px] font-semibold" :class="isHit(row) ? 'text-[#2b579a]' : 'text-slate-400'" x-text="row.code"></td>
                                <td class="px-3 py-2.5">
                                    <a :href="row.branches_url" class="font-semibold leading-snug group-hover:text-[#2b579a]" :class="isHit(row) ? 'text-navy-900' : 'text-slate-500'" @click.stop>
                                        <span x-show="row.is_new" class="mr-1 rounded bg-violet-600 px-1.5 py-0.5 align-middle text-[10.5px] font-bold text-white">নতুন</span>
                                        <span x-text="row.title"></span>
                                    </a>
                                    <p class="mt-0.5 text-[11px] text-slate-400">
                                        <span x-text="clean(row.category) || 'Uncategorised'"></span>
                                        <span x-show="clean(row.sub_category)"> › <span x-text="row.sub_category"></span></span>
                                    </p>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span x-show="clean(row.risk_rating)" class="whitespace-nowrap rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1" :class="riskClass(row.risk_rating)" x-text="row.risk_rating"></span>
                                    <span x-show="! clean(row.risk_rating)" class="text-slate-300">—</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2.5 text-right tabular-nums" :class="row.total_amount > 0 ? 'text-slate-700' : 'text-slate-300'" x-text="row.total_amount_fmt"></td>
                                <td class="px-3 py-2.5 text-right tabular-nums" :class="row.total_samples_checked > 0 ? 'text-slate-700' : 'text-slate-300'" x-text="row.total_samples_checked"></td>
                                <td class="whitespace-nowrap px-3 py-2.5 text-right">
                                    <span class="font-bold tabular-nums" :class="row.total_irregularities > 0 ? 'text-rose-700' : 'text-slate-300'" x-text="row.total_irregularities"></span>
                                    <span
                                        x-show="rateOf(row) !== null"
                                        class="ml-1 rounded-full px-1.5 py-0.5 text-[10.5px] font-semibold"
                                        :class="rateTone(rateOf(row))"
                                        x-text="rateOf(row) + '%'"
                                        title="Irregularities ÷ samples checked"
                                    ></span>
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <span
                                        class="inline-flex min-w-[24px] justify-center rounded-md px-1.5 py-0.5 text-[12px] font-bold tabular-nums"
                                        :class="row.objected_branch_count > 0 ? 'bg-amber-50 text-amber-800 ring-1 ring-amber-200' : 'text-slate-300'"
                                        x-text="row.objected_branch_count"
                                    ></span>
                                </td>
                                <td class="px-2 py-2.5 text-slate-300 transition-colors group-hover:text-[#2b579a]">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filtered.length === 0">
                            <td colspan="8" class="px-3 py-10 text-center">
                                <template x-if="rows.length === 0">
                                    <div>
                                        <p class="text-[13px] font-semibold text-slate-600">No indicators yet</p>
                                        <p class="mt-0.5 text-[12px] text-slate-500">Import them with <span class="font-mono text-navy-800">php artisan audit:import-indicators path/to/file.xlsx</span></p>
                                    </div>
                                </template>
                                <template x-if="rows.length > 0">
                                    <div>
                                        <p class="text-[13px] font-semibold text-slate-600">No indicators match these filters</p>
                                        <button type="button" @click="resetFilters()" class="mt-1 text-[12px] font-semibold text-[#2b579a] hover:underline">Clear filters</button>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/60 px-3 py-2" x-show="filtered.length > 0">
                <p class="text-[12px] text-slate-500">
                    Showing <span class="font-semibold tabular-nums text-slate-700" x-text="fromRow + '–' + toRow"></span>
                    of <span class="font-semibold tabular-nums text-slate-700" x-text="filtered.length"></span>
                    <span x-show="filtered.length !== rows.length">(<span x-text="rows.length"></span> total)</span>
                </p>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-1.5 text-[12px] text-slate-500">
                        Rows
                        <select x-model.number="perPage" @change="page = 1" class="h-7 rounded-md border-slate-200 py-0 pl-2 pr-7 text-[12px]">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </label>
                    <div class="flex items-center gap-1" x-show="totalPages > 1">
                        <button type="button" @click="go(page - 1)" :disabled="page <= 1" class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40" aria-label="Previous page">‹</button>
                        <span class="px-1 text-[12px] tabular-nums text-slate-600"><span x-text="page"></span> / <span x-text="totalPages"></span></span>
                        <button type="button" @click="go(page + 1)" :disabled="page >= totalPages" class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40" aria-label="Next page">›</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            function findingsMatrix(cfg) {
                const clean = (v) => (v && v !== '—' ? v : '');

                return {
                    rows: cfg.rows || [],
                    q: '',
                    category: '',
                    subCategory: '',
                    risk: '',
                    scope: 'all',
                    page: 1,
                    perPage: 25,
                    clean,
                    get categories() {
                        return [...new Set(this.rows.map((r) => clean(r.category)).filter(Boolean))].sort();
                    },
                    get subCategories() {
                        return [...new Set(
                            this.rows
                                .filter((r) => ! this.category || r.category === this.category)
                                .map((r) => clean(r.sub_category))
                                .filter(Boolean)
                        )].sort();
                    },
                    get risks() {
                        return [...new Set(this.rows.map((r) => clean(r.risk_rating)).filter(Boolean))].sort();
                    },
                    get isFiltered() {
                        return this.q !== '' || this.category !== '' || this.subCategory !== '' || this.risk !== '' || this.scope !== 'all';
                    },
                    isHit(r) {
                        return r.objected_branch_count > 0 || r.total_irregularities > 0;
                    },
                    get filtered() {
                        const q = this.q.trim().toLowerCase();
                        return this.rows.filter((r) => {
                            if (this.scope === 'new' && ! r.is_new) return false;
                            if (this.scope === 'hit' && ! this.isHit(r)) return false;
                            if (this.category && r.category !== this.category) return false;
                            if (this.subCategory && r.sub_category !== this.subCategory) return false;
                            if (this.risk && r.risk_rating !== this.risk) return false;
                            if (! q) return true;
                            return (r.code + ' ' + r.title + ' ' + r.category + ' ' + r.sub_category + ' ' + r.risk_rating).toLowerCase().includes(q);
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
                    rateOf(r) {
                        if (! r.total_samples_checked || ! r.total_irregularities) return null;
                        return Math.round(r.total_irregularities / r.total_samples_checked * 1000) / 10;
                    },
                    rateTone(rate) {
                        if (rate >= 20) return 'bg-rose-50 text-rose-700';
                        if (rate >= 10) return 'bg-amber-50 text-amber-700';
                        return 'bg-emerald-50 text-emerald-700';
                    },
                    riskClass(rating) {
                        if (rating === 'Major') return 'bg-rose-50 text-rose-700 ring-rose-200';
                        if (rating === 'Minor') return 'bg-slate-100 text-slate-600 ring-slate-200';
                        return 'bg-amber-50 text-amber-700 ring-amber-200';
                    },
                    resetFilters() {
                        this.q = '';
                        this.category = '';
                        this.subCategory = '';
                        this.risk = '';
                        this.scope = 'all';
                        this.page = 1;
                    },
                    showOnlyNew() {
                        this.resetFilters();
                        this.scope = 'new';
                        this.$nextTick(() => document.getElementById('findings-matrix-table')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
                    },
                    onCategoryChange() {
                        this.subCategory = '';
                        this.page = 1;
                    },
                    go(p) {
                        this.page = Math.min(Math.max(1, p), this.totalPages);
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
