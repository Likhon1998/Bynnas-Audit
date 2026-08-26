<x-app-layout>
    @php
        $branchOptions = $shakhas->values()->map(function ($shakha, $index) use ($fyLabel) {
            return [
                'id' => (string) $shakha->id,
                'serial' => $index + 1,
                'name' => $shakha->name,
                'code' => (string) ($shakha->code ?: ''),
                'area' => (string) ($shakha->area?->name ?: ''),
                'division' => (string) ($shakha->area?->division ?: ''),
                'focal' => (string) ($shakha->focal_person_name ?: ''),
                'has' => $shakha->annualKpis->isNotEmpty(),
                'opening' => optional($shakha->opening_date ?? $shakha->opened_at)->format('d M Y') ?: '',
                'editUrl' => route('kpis.edit', ['shakha' => $shakha, 'fy' => $fyLabel]),
            ];
        })->values();
        $areaNames = $shakhas->map(fn ($s) => $s->area?->name)->filter()->unique()->sort()->values();
    @endphp

    <div
        class="px-4 py-5 lg:px-6"
        x-data="{
            q: '',
            area: '',
            status: 'all',
            open: false,
            highlight: 0,
            branches: @js($branchOptions),
            get filtered() {
                const q = this.q.trim().toLowerCase();
                return this.branches.filter((b) => {
                    if (this.area && b.area !== this.area) return false;
                    if (this.status === 'entered' && !b.has) return false;
                    if (this.status === 'pending' && b.has) return false;
                    if (!q) return true;
                    const hay = (b.name + ' ' + b.code + ' ' + b.area + ' ' + b.division + ' ' + b.focal + ' ' + b.opening).toLowerCase();
                    return hay.includes(q);
                });
            },
            get visibleCount() { return this.filtered.length; },
            pick(b) {
                this.q = b.name;
                this.open = false;
                this.$nextTick(() => {
                    const el = document.getElementById('kpi-row-' + b.id);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.classList.add('ring-2', 'ring-brand-500');
                        setTimeout(() => el.classList.remove('ring-2', 'ring-brand-500'), 1600);
                    }
                });
            },
            onKey(e) {
                const list = this.filtered;
                if (!this.open && (e.key === 'ArrowDown' || e.key === 'Enter')) {
                    this.open = true;
                    return;
                }
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.highlight = Math.min(this.highlight + 1, Math.max(list.length - 1, 0));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.highlight = Math.max(this.highlight - 1, 0);
                } else if (e.key === 'Enter' && list[this.highlight]) {
                    e.preventDefault();
                    this.pick(list[this.highlight]);
                } else if (e.key === 'Escape') {
                    this.open = false;
                }
            }
        }"
        @click.outside="open = false"
    >
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Annual Shakha KPI</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">Enter once per financial year for each branch · export one consolidated Excel like your template</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('kpis.index') }}" class="flex items-center gap-1.5">
                    <label class="text-[11px] font-medium text-slate-500">FY</label>
                    <select name="fy" onchange="this.form.submit()" class="h-8 rounded-lg border-slate-200 py-0 text-[12px]">
                        @foreach ($fyOptions as $opt)
                            <option value="{{ $opt }}" @selected($opt === $fyLabel)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </form>
                <a
                    href="{{ route('kpis.export', ['fy' => $fyLabel]) }}"
                    class="inline-flex h-8 items-center rounded-lg bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-500"
                    title="Exports branches with KPI entered for this FY"
                >
                    Export Excel
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif

        <div class="mb-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-100 bg-white px-4 py-3 shadow-card">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Fiscal year</p>
                <p class="mt-1 text-[18px] font-semibold text-navy-900">{{ $fyLabel }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-white px-4 py-3 shadow-card">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Branches entered</p>
                <p class="mt-1 text-[18px] font-semibold text-navy-900">{{ $progress['entered'] }} / {{ $progress['total'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-white px-4 py-3 shadow-card">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Showing now</p>
                <p class="mt-1 text-[18px] font-semibold text-navy-900"><span x-text="visibleCount"></span> / {{ $progress['total'] }}</p>
            </div>
        </div>

        <div class="mb-4 grid gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-card sm:grid-cols-[1fr_180px_150px]">
            <div class="relative">
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Find branch</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                    <input
                        type="search"
                        x-model="q"
                        @focus="open = true; highlight = 0"
                        @input="open = true; highlight = 0"
                        @keydown="onKey($event)"
                        placeholder="Type branch, area, code, focal person…"
                        class="h-9 w-full rounded-lg border-slate-200 py-0 pl-8 pr-8 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        autocomplete="off"
                    >
                    <button
                        type="button"
                        x-show="q"
                        x-cloak
                        @click="q = ''; open = false"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] font-medium text-slate-400 hover:text-slate-600"
                    >Clear</button>
                </div>

                <div
                    x-show="open && filtered.length"
                    x-cloak
                    class="absolute z-30 mt-1 max-h-72 w-full overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg"
                >
                    <template x-for="(b, idx) in filtered.slice(0, 40)" :key="b.id">
                        <button
                            type="button"
                            @click="pick(b)"
                            @mouseenter="highlight = idx"
                            class="flex w-full items-start gap-2 px-3 py-2 text-left hover:bg-sky-50"
                            :class="highlight === idx ? 'bg-sky-50' : ''"
                        >
                            <span class="mt-0.5 w-6 shrink-0 text-[11px] tabular-nums text-slate-400" x-text="b.serial"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[12px] font-semibold text-navy-900" x-text="b.name"></span>
                                <span class="mt-0.5 block truncate text-[10px] text-slate-500">
                                    <span x-text="b.area || 'No area'"></span>
                                    <span x-show="b.code"> · <span x-text="b.code"></span></span>
                                    <span x-show="b.opening"> · Opened <span x-text="b.opening"></span></span>
                                </span>
                            </span>
                            <span
                                class="mt-0.5 shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-semibold"
                                :class="b.has ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                x-text="b.has ? 'Entered' : 'Pending'"
                            ></span>
                        </button>
                    </template>
                    <p class="border-t border-slate-100 px-3 py-1.5 text-[10px] text-slate-400" x-show="filtered.length > 40">
                        Showing first 40 of <span x-text="filtered.length"></span> — keep typing to narrow
                    </p>
                </div>
                <p x-show="open && q && !filtered.length" x-cloak class="absolute z-30 mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-[12px] text-slate-500 shadow-lg">
                    No branch matches “<span x-text="q"></span>”
                </p>
            </div>

            <div>
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Area</label>
                <select x-model="area" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                    <option value="">All areas</option>
                    @foreach ($areaNames as $areaName)
                        <option value="{{ $areaName }}">{{ $areaName }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">KPI status</label>
                <select x-model="status" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                    <option value="all">All</option>
                    <option value="pending">Pending only</option>
                    <option value="entered">Entered only</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead class="border-b border-slate-100 bg-slate-50/90 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="w-14 px-4 py-2.5">#</th>
                            <th class="px-4 py-2.5">Area</th>
                            <th class="px-4 py-2.5">Branch</th>
                            <th class="px-4 py-2.5">Code</th>
                            <th class="px-4 py-2.5">Opening date</th>
                            <th class="px-4 py-2.5">Focal person</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="b in filtered" :key="b.id">
                            <tr
                                :id="'kpi-row-' + b.id"
                                class="scroll-mt-24 hover:bg-sky-50/40"
                            >
                                <td class="px-4 py-2.5 tabular-nums text-slate-400" x-text="b.serial"></td>
                                <td class="px-4 py-2.5 text-slate-600" x-text="b.area || '—'"></td>
                                <td class="px-4 py-2.5 font-medium text-navy-900" x-text="b.name"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="b.code || '—'"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="b.opening || '—'"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="b.focal || '—'"></td>
                                <td class="px-4 py-2.5">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-medium"
                                        :class="b.has ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                        x-text="b.has ? 'Entered' : 'Pending'"
                                    ></span>
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <a :href="b.editUrl" class="font-semibold text-brand-600 hover:underline" x-text="b.has ? 'Edit' : 'Enter data'"></a>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <p x-show="!filtered.length" x-cloak class="px-4 py-10 text-center text-[12px] text-slate-400">
                    No branches match your filters.
                </p>
                @if ($shakhas->isEmpty())
                    <p class="px-4 py-10 text-center text-[12px] text-slate-400">
                        No active shakhas.
                        <a href="{{ route('shakhas.create') }}" class="font-medium text-brand-600 hover:underline">Add a shakha</a>
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
