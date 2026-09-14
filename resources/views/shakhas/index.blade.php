<x-app-layout>
    <div
        class="px-4 py-3 lg:px-6"
        x-data="{
            q: '',
            area: '',
            division: '',
            kpi: 'all',
            riskTab: 'all',
            page: 1,
            pageSize: 25,
            rows: @js($rows),
            riskMap: @js(\App\Support\ShakhaRiskTone::jsMap()),
            riskBadge(risk) {
                return (this.riskMap[risk] || this.riskMap['Not assessed'] || {}).badge
                    || 'bg-slate-50 text-slate-500 ring-1 ring-slate-200';
            },
            riskText(risk) {
                return (this.riskMap[risk] || this.riskMap['Not assessed'] || {}).text || 'text-navy-900';
            },
            riskSoft(risk) {
                return (this.riskMap[risk] || {}).soft || '';
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
                this.page = 1;
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
            get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.pageSize)); },
            get pageRows() {
                if (this.page > this.totalPages) this.page = this.totalPages;
                const start = (this.page - 1) * this.pageSize;
                return this.filtered.slice(start, start + this.pageSize);
            },
            get rangeLabel() {
                if (!this.filtered.length) return '0';
                const start = (this.page - 1) * this.pageSize + 1;
                const end = Math.min(this.page * this.pageSize, this.filtered.length);
                return start + '–' + end;
            },
            setFilter(key, value) {
                this[key] = value;
                this.page = 1;
            },
            goTo(p) {
                this.page = Math.min(this.totalPages, Math.max(1, p));
            },
            clearFilters() {
                this.q = '';
                this.area = '';
                this.division = '';
                this.kpi = 'all';
                this.page = 1;
            }
        }"
    >
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-navy-900">All Shakha</h1>
                <p class="mt-0.5 text-[13px] text-slate-500">
                    {{ $rows->count() }} branches · KPI FY {{ $fyLabel }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('risk.manage')
                    <a
                        href="{{ route('shakhas.risk.laws') }}"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Risk laws
                    </a>
                    <a
                        href="{{ route('shakhas.risk.export', ['fy' => $fyLabel]) }}"
                        class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[12px] font-medium text-emerald-800 hover:bg-emerald-100"
                    >
                        Export Risk Excel
                    </a>
                @endcan
                @can('shakhas.manage')
                    <a href="{{ route('shakhas.create') }}" class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                        <span class="text-[13px] leading-none">+</span>
                        Add Shakha
                    </a>
                @endcan
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif


        {{-- Compact filters + risk tabs --}}
        <div class="mb-2 space-y-1.5">
            <div class="flex flex-wrap items-center gap-1.5">
                <div class="relative min-w-[180px] flex-1 basis-48">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                    <input
                        type="search"
                        x-model="q"
                        @input="page = 1"
                        placeholder="Search name, code, area…"
                        class="h-8 w-full rounded-lg border-slate-200 py-0 pl-8 pr-2 text-[12px] shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    >
                </div>
                <select
                    :value="division"
                    @change="setDivision($event.target.value)"
                    class="h-8 w-[140px] rounded-lg border-slate-200 py-0 text-[12px]"
                    title="Division"
                >
                    <option value="">All divisions</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division }}">{{ $division }}</option>
                    @endforeach
                </select>
                <select
                    x-model="area"
                    @change="page = 1"
                    class="h-8 w-[150px] rounded-lg border-slate-200 py-0 text-[12px]"
                    :disabled="!division"
                    title="Area"
                >
                    <option value="" x-text="division ? 'All areas' : 'Area (pick division)'"></option>
                    <template x-for="name in areasForDivision" :key="name">
                        <option :value="name" x-text="name"></option>
                    </template>
                </select>
                <select x-model="kpi" @change="page = 1" class="h-8 w-[120px] rounded-lg border-slate-200 py-0 text-[12px]" title="KPI {{ $fyLabel }}">
                    <option value="all">KPI: All</option>
                    <option value="ready">KPI ready</option>
                    <option value="missing">KPI missing</option>
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-1.5 rounded-lg border border-slate-100 bg-white p-1">
                @php
                    $tabs = [
                        [
                            'key' => 'all',
                            'label' => 'All',
                            'count' => $riskCounts['all'],
                            'active' => 'bg-navy-900 text-white ring-1 ring-navy-900',
                            'idle' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200',
                            'countActive' => 'bg-white/20 text-white',
                            'countIdle' => 'bg-white text-slate-600',
                        ],
                        [
                            'key' => 'Low Risk',
                            'label' => 'Low',
                            'count' => $riskCounts['Low Risk'],
                            'active' => 'bg-emerald-600 text-white ring-1 ring-emerald-600',
                            'idle' => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 hover:bg-emerald-100',
                            'countActive' => 'bg-white/20 text-white',
                            'countIdle' => 'bg-emerald-100 text-emerald-800',
                        ],
                        [
                            'key' => 'Medium Risk',
                            'label' => 'Medium',
                            'count' => $riskCounts['Medium Risk'],
                            'active' => 'bg-amber-500 text-white ring-1 ring-amber-500',
                            'idle' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200 hover:bg-amber-100',
                            'countActive' => 'bg-white/20 text-white',
                            'countIdle' => 'bg-amber-100 text-amber-800',
                        ],
                        [
                            'key' => 'High Risk',
                            'label' => 'High',
                            'count' => $riskCounts['High Risk'],
                            'active' => 'bg-orange-500 text-white ring-1 ring-orange-500',
                            'idle' => 'bg-orange-50 text-orange-800 ring-1 ring-orange-200 hover:bg-orange-100',
                            'countActive' => 'bg-white/20 text-white',
                            'countIdle' => 'bg-orange-100 text-orange-800',
                        ],
                        [
                            'key' => 'Significant Risk',
                            'label' => 'Significant',
                            'count' => $riskCounts['Significant Risk'],
                            'active' => 'bg-rose-600 text-white ring-1 ring-rose-600',
                            'idle' => 'bg-rose-50 text-rose-800 ring-1 ring-rose-200 hover:bg-rose-100',
                            'countActive' => 'bg-white/20 text-white',
                            'countIdle' => 'bg-rose-100 text-rose-800',
                        ],
                        [
                            'key' => 'Not assessed',
                            'label' => 'N/A',
                            'count' => $riskCounts['Not assessed'],
                            'active' => 'bg-slate-600 text-white ring-1 ring-slate-600',
                            'idle' => 'bg-slate-50 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100',
                            'countActive' => 'bg-white/20 text-white',
                            'countIdle' => 'bg-slate-200 text-slate-700',
                        ],
                    ];
                @endphp
                @foreach ($tabs as $tab)
                    <button
                        type="button"
                        @click="setFilter('riskTab', @js($tab['key']))"
                        class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[13px] font-semibold transition"
                        :class="riskTab === @js($tab['key']) ? @js($tab['active']) : @js($tab['idle'])"
                    >
                        {{ $tab['label'] }}
                        <span
                            class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold tabular-nums"
                            :class="riskTab === @js($tab['key']) ? @js($tab['countActive']) : @js($tab['countIdle'])"
                        >{{ $tab['count'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2 text-[13px] text-slate-500">
            <p>
                Showing <span class="font-semibold text-navy-900" x-text="rangeLabel"></span>
                of <span class="font-semibold text-navy-900" x-text="visibleCount"></span>
                <span x-show="visibleCount !== {{ $rows->count() }}"> (filtered from {{ $rows->count() }})</span>
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-1.5">
                    <span>Per page</span>
                    <select x-model.number="pageSize" @change="page = 1" class="h-7 rounded-md border-slate-200 py-0 text-[13px]">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </label>
                <button type="button" @click="clearFilters()" class="font-medium text-brand-600 hover:underline">Clear</button>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="text-xs font-semibold uppercase tracking-wide text-slate-500">
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
                        <template x-for="(row, index) in pageRows" :key="row.id">
                            <tr class="text-[12px]" :class="riskSoft(row.risk)">
                                <td class="px-4 py-2.5 tabular-nums text-slate-400" x-text="(page - 1) * pageSize + index + 1"></td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="font-semibold" :class="riskText(row.risk)" x-text="row.name"></span>
                                        <span
                                            class="inline-flex rounded-full px-1.5 py-0.5 text-xs font-semibold"
                                            :class="riskBadge(row.risk)"
                                            x-text="(riskMap[row.risk] || riskMap['Not assessed'] || {}).short || 'N/A'"
                                        ></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-slate-600" x-text="row.area || '—'"></td>
                                <td class="px-4 py-2.5 text-slate-600" x-text="row.division || '—'"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="row.code || '—'"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="row.opening"></td>
                                <td class="px-4 py-2.5">
                                    <template x-if="row.kpi_ready">
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Ready</span>
                                    </template>
                                    <template x-if="!row.kpi_ready">
                                        @can('kpis.manage')
                                            <a
                                                :href="row.kpi_url"
                                                class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800 hover:bg-amber-100"
                                                title="Enter annual KPI first"
                                            >Not ready — enter KPI</a>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800">Not ready</span>
                                        @endcan
                                    </template>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="riskBadge(row.risk)"
                                        :title="row.risk_score != null ? ('Score ' + row.risk_score + (row.risk_period ? ' · ' + row.risk_period : '')) : ''"
                                        x-text="row.risk"
                                    ></span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="row.added_on"></td>
                                <td class="px-4 py-2.5 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        @can('risk.manage')
                                            <template x-if="row.kpi_ready">
                                                <a
                                                    :href="row.risk_url"
                                                    class="inline-flex rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[13px] font-medium text-slate-600 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-800"
                                                >Risk</a>
                                            </template>
                                            <template x-if="!row.kpi_ready">
                                                <span
                                                    class="inline-flex cursor-not-allowed rounded-lg border border-slate-100 bg-slate-50 px-2.5 py-1 text-[13px] font-medium text-slate-400"
                                                    title="Complete annual KPI for this FY first, then open Risk."
                                                >Risk locked</span>
                                            </template>
                                        @endcan
                                        <a
                                            :href="row.staff_url"
                                            class="inline-flex rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[13px] font-medium text-slate-600 hover:border-sky-200 hover:bg-sky-50 hover:text-sky-800"
                                        >Staff</a>
                                        @can('shakhas.manage')
                                            <a
                                                :href="row.edit_url"
                                                class="inline-flex rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[13px] font-medium text-slate-600 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"
                                            >Edit</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filtered.length === 0">
                            <td colspan="10" class="px-4 py-10 text-center text-[13px] text-slate-500">
                                No shakhas match these filters.
                                <button type="button" @click="clearFilters(); riskTab = 'all'" class="font-medium text-brand-600 hover:underline">Reset</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                x-show="filtered.length > 0"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3"
            >
                <p class="text-[13px] text-slate-500">
                    Page <span class="font-semibold text-navy-900" x-text="page"></span>
                    of <span class="font-semibold text-navy-900" x-text="totalPages"></span>
                </p>
                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        @click="goTo(1)"
                        :disabled="page <= 1"
                        class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[13px] font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >First</button>
                    <button
                        type="button"
                        @click="goTo(page - 1)"
                        :disabled="page <= 1"
                        class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[13px] font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >Prev</button>
                    <button
                        type="button"
                        @click="goTo(page + 1)"
                        :disabled="page >= totalPages"
                        class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[13px] font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >Next</button>
                    <button
                        type="button"
                        @click="goTo(totalPages)"
                        :disabled="page >= totalPages"
                        class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[13px] font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >Last</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
