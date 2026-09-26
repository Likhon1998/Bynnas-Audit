<x-app-layout>
    @php
        $tabs = [
            'policies' => ['label' => $plan->generated_at ? 'Policies' : '1. Policies'],
            'total' => ['label' => 'Total'],
            'shakha' => ['label' => 'Shakha Audit'],
            'area' => ['label' => 'Area Office'],
            'pksf' => ['label' => 'PKSF & Maternity'],
            'hq' => ['label' => 'HQ'],
            'project_audit' => ['label' => 'Project Audit'],
            'project_monitoring' => ['label' => 'Project Monitoring'],
        ];
        $canEditSchedule = $canEditSchedule ?? true;
        $canManageAnnual = $canManageAnnual ?? $canEditSchedule;
    @endphp

    <style>
        .annual-tab{display:inline-flex;align-items:center;min-height:2rem;border-radius:.375rem;border:1px solid transparent;padding:.35rem .75rem;font-size:12px;font-weight:700;line-height:1.2;color:#fff;box-shadow:0 1px 2px rgba(15,23,42,.12)}
        .annual-tab[data-key="policies"]{background:#be123c}
        .annual-tab[data-key="total"]{background:#1e293b}
        .annual-tab[data-key="shakha"]{background:#047857}
        .annual-tab[data-key="area"]{background:#b45309}
        .annual-tab[data-key="pksf"]{background:#c2410c}
        .annual-tab[data-key="hq"]{background:#0369a1}
        .annual-tab[data-key="project_audit"]{background:#0f766e}
        .annual-tab[data-key="project_monitoring"]{background:#0e7490}
        .annual-tab[data-on="1"]{box-shadow:0 0 0 2px #fff,0 0 0 4px #0f172a}
        .project-plan-fit{overflow-x:hidden}
        .project-plan-fit .project-month button,
        .project-plan-fit .project-month span.inline-flex{height:1.35rem;width:1.35rem}
        .annual-wait{display:flex;min-height:240px;flex-direction:column;align-items:center;justify-content:center;gap:.65rem;padding:2.5rem 1rem;text-align:center}
        .annual-wait__spin{width:2.25rem;height:2.25rem;border-radius:999px;border:3px solid #dbe4f3;border-top-color:#2b579a;animation:annual-wait-spin .7s linear infinite}
        @keyframes annual-wait-spin{to{transform:rotate(360deg)}}
        @media (prefers-reduced-motion:reduce){.annual-wait__spin{animation:none;border-top-color:#2b579a}}
    </style>

    <div class="px-3 py-3 text-[12px] leading-snug lg:px-5">
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <h1 class="shrink-0 text-[15px] font-semibold tracking-tight text-navy-900">Annual Audit &amp; Monitoring</h1>
            @unless ($canManageAnnual)
                <span class="inline-flex shrink-0 items-center rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-800">View only</span>
            @endunless
            <span class="hidden h-4 w-px shrink-0 bg-slate-200 sm:block"></span>
            <label class="inline-flex shrink-0 items-center gap-1 text-[12px] text-slate-500">
                FY
                <select
                    class="h-7 rounded-md border-slate-200 py-0 text-[11px] font-medium text-navy-900"
                    onchange="window.location = this.value"
                >
                    @foreach ($availablePlans as $availablePlan)
                        <option
                            value="{{ route('annual-audit.index', array_filter(['fy' => $availablePlan->fy_label, 'tab' => $tab])) }}"
                            @selected($availablePlan->fy_label === $plan->fy_label)
                        >
                            {{ $availablePlan->fy_label }} ({{ $availablePlan->status }})
                        </option>
                    @endforeach
                </select>
            </label>
            <span class="hidden shrink-0 text-[12px] capitalize text-slate-500 sm:inline">{{ $plan->status }}</span>
            @if ($plan->generated_at)
                <span class="hidden shrink-0 text-[12px] text-slate-500 lg:inline">· {{ bd_datetime($plan->generated_at) }}</span>
            @endif

            <div class="ml-auto flex flex-wrap items-center justify-end gap-1.5">
                @if ($canManageAnnual && ($canDeletePlan ?? false))
                    <form
                        method="POST"
                        action="{{ route('annual-audit.years.destroy') }}"
                        class="inline"
                        data-bynnas-confirm="Delete the entire FY {{ $plan->fy_label }} report? This permanently removes all schedules and policies for that year."
                        data-bynnas-confirm-title="Delete financial year?"
                        data-bynnas-confirm-ok="Delete FY"
                        data-bynnas-confirm-tone="rose"
                    >
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md border border-rose-200 bg-rose-50 px-2 text-[12px] font-medium text-rose-700 hover:bg-rose-100">
                            Delete FY
                        </button>
                    </form>
                @endif
                @if ($canManageAnnual)
                    @unless ($nextPlanExists)
                        <form method="POST" action="{{ route('annual-audit.years.store') }}" class="inline">
                            @csrf
                            <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                            <button type="submit" class="inline-flex h-7 items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 text-[12px] font-medium text-emerald-800 hover:bg-emerald-100">
                                Create {{ $nextFyLabel }}
                            </button>
                        </form>
                    @endunless
                @endif
                <a
                    href="{{ route('annual-audit.export', ['mode' => 'all', 'fy' => $plan->fy_label]) }}"
                    class="inline-flex h-7 items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 text-[12px] font-medium text-emerald-800 hover:bg-emerald-100"
                    title="Download Total through Project Monitoring in one Excel file"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Full Report
                </a>
                @if ($canManageAnnual)
                    <form method="POST" action="{{ route('annual-audit.generate') }}" class="inline">
                        @csrf
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <button
                            type="submit"
                            class="inline-flex h-7 items-center rounded-md bg-navy-900 px-2.5 text-[12px] font-medium text-white hover:bg-navy-800"
                            title="Uses frequencies from Policies to build the yearly schedule"
                        >
                            {{ $plan->generated_at ? 'Regenerate' : '2. Generate Plan' }}
                        </button>
                    </form>
                    @if ($plan->generated_at)
                        <form method="POST" action="{{ route('annual-audit.sync-missing') }}" class="inline">
                            @csrf
                            <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                            <input type="hidden" name="tab" value="{{ $tab }}" data-annual-sync-tab>
                            <button
                                type="submit"
                                class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50"
                                title="Add only new shakha / area / project rows without changing existing schedules"
                            >
                                Sync new items
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        @unless ($plan->generated_at)
            <div class="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 rounded-lg border border-rose-100 bg-rose-50/70 px-3 py-2 text-[12px] text-rose-900">
                @if ($canManageAnnual)
                    <span class="font-semibold">Setup this FY:</span>
                    <a href="{{ route('annual-audit.index', ['fy' => $plan->fy_label, 'tab' => 'policies']) }}" class="font-medium underline decoration-rose-300 underline-offset-2 hover:text-rose-700">1. Set Policies</a>
                    <span class="text-rose-300">→</span>
                    <span>2. Generate Plan</span>
                    <span class="text-rose-300">→</span>
                    <span class="text-rose-700/80">3. Review report tabs</span>
                @else
                    <span class="font-semibold">Plan not generated yet.</span>
                    <span class="text-rose-700/80">Ask a planner with Annual Audit manage access to generate this FY.</span>
                @endif
            </div>
        @endunless

        <div class="mb-3 grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9">
            @foreach ([
                ['label' => 'Planned', 'value' => $kpis['planned'], 'tone' => 'indigo'],
                ['label' => 'Completed', 'value' => $kpis['completed'], 'tone' => 'emerald'],
                ['label' => 'Pending', 'value' => $kpis['pending'], 'tone' => 'amber'],
                ['label' => 'Shakha', 'value' => $kpis['shakha'], 'tone' => 'violet'],
                ['label' => 'Area', 'value' => $kpis['area'], 'tone' => 'sky'],
                ['label' => 'PKSF & Maternity', 'value' => $kpis['pksf'], 'tone' => 'orange'],
                ['label' => 'HQ', 'value' => $kpis['hq'], 'tone' => 'rose'],
                ['label' => 'Project Audit', 'value' => $kpis['project_audit'], 'tone' => 'teal'],
                ['label' => 'Project Monitoring', 'value' => $kpis['project_monitoring'], 'tone' => 'cyan'],
            ] as $kpi)
                @php
                    $tone = match ($kpi['tone']) {
                        'emerald' => [
                            'card' => 'from-emerald-50 via-white to-green-50 border-emerald-200/90',
                            'bar' => 'from-emerald-500 to-green-400',
                            'label' => 'text-emerald-700',
                            'value' => 'text-emerald-800',
                            'glow' => 'bg-emerald-200/50',
                        ],
                        'amber' => [
                            'card' => 'from-amber-50 via-white to-yellow-50 border-amber-200/90',
                            'bar' => 'from-amber-500 to-orange-400',
                            'label' => 'text-amber-700',
                            'value' => 'text-amber-800',
                            'glow' => 'bg-amber-200/50',
                        ],
                        'violet' => [
                            'card' => 'from-violet-50 via-white to-purple-50 border-violet-200/90',
                            'bar' => 'from-violet-500 to-fuchsia-400',
                            'label' => 'text-violet-700',
                            'value' => 'text-violet-800',
                            'glow' => 'bg-violet-200/50',
                        ],
                        'sky' => [
                            'card' => 'from-sky-50 via-white to-blue-50 border-sky-200/90',
                            'bar' => 'from-sky-500 to-blue-400',
                            'label' => 'text-sky-700',
                            'value' => 'text-sky-800',
                            'glow' => 'bg-sky-200/50',
                        ],
                        'orange' => [
                            'card' => 'from-orange-50 via-white to-amber-50 border-orange-200/90',
                            'bar' => 'from-orange-500 to-amber-400',
                            'label' => 'text-orange-700',
                            'value' => 'text-orange-800',
                            'glow' => 'bg-orange-200/50',
                        ],
                        'rose' => [
                            'card' => 'from-rose-50 via-white to-pink-50 border-rose-200/90',
                            'bar' => 'from-rose-500 to-pink-400',
                            'label' => 'text-rose-700',
                            'value' => 'text-rose-800',
                            'glow' => 'bg-rose-200/50',
                        ],
                        'teal' => [
                            'card' => 'from-teal-50 via-white to-emerald-50 border-teal-200/90',
                            'bar' => 'from-teal-500 to-cyan-400',
                            'label' => 'text-teal-700',
                            'value' => 'text-teal-800',
                            'glow' => 'bg-teal-200/50',
                        ],
                        'cyan' => [
                            'card' => 'from-cyan-50 via-white to-sky-50 border-cyan-200/90',
                            'bar' => 'from-cyan-500 to-sky-400',
                            'label' => 'text-cyan-700',
                            'value' => 'text-cyan-800',
                            'glow' => 'bg-cyan-200/50',
                        ],
                        default => [
                            'card' => 'from-indigo-50 via-white to-blue-50 border-indigo-200/90',
                            'bar' => 'from-indigo-500 to-blue-400',
                            'label' => 'text-indigo-700',
                            'value' => 'text-indigo-800',
                            'glow' => 'bg-indigo-200/50',
                        ],
                    };
                @endphp
                <div class="group relative overflow-hidden rounded-xl border bg-gradient-to-br px-3 py-2.5 shadow-[0_8px_24px_rgba(15,23,42,0.08)] transition duration-150 hover:-translate-y-0.5 hover:shadow-[0_14px_32px_rgba(15,23,42,0.12)] {{ $tone['card'] }}">
                    <span class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $tone['bar'] }}"></span>
                    <span class="pointer-events-none absolute -right-6 -top-6 h-16 w-16 rounded-full {{ $tone['glow'] }} blur-2xl transition group-hover:scale-125"></span>
                    <p class="relative text-[10px] font-bold uppercase tracking-[0.08em] {{ $tone['label'] }}">{{ $kpi['label'] }}</p>
                    <p class="relative mt-1 text-[17px] font-bold tabular-nums tracking-tight {{ $tone['value'] }}">{{ number_format($kpi['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div
            class="mb-3"
            x-data="annualAuditTabs({
                tab: @js($tab),
                fy: @js($plan->fy_label),
                panelUrl: @js($panelUrl),
                keys: @js(array_keys($tabs)),
                labels: @js(collect($tabs)->map(fn ($meta) => $meta['label'])->all()),
            })"
        >
            <div class="mb-3 flex flex-wrap gap-1.5" role="tablist">
                @foreach ($tabs as $key => $tabMeta)
                    <button
                        type="button"
                        role="tab"
                        data-key="{{ $key }}"
                        data-on="{{ $tab === $key ? '1' : '0' }}"
                        :data-on="tab === @js($key) ? '1' : '0'"
                        :aria-selected="tab === @js($key)"
                        @pointerenter="warm(@js($key))"
                        @focus="warm(@js($key))"
                        @click="go(@js($key))"
                        class="annual-tab whitespace-nowrap"
                    >
                        {{ $tabMeta['label'] }}
                    </button>
                @endforeach
            </div>

            <p x-show="tab === 'pksf'" x-cloak class="mb-3 text-[12px] text-slate-500">
                Click any month cell to schedule or remove. Nothing is fixed — admin controls each month.
            </p>

            <div class="relative overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                <div
                    x-show="!loaded[tab]"
                    x-cloak
                    class="annual-wait"
                    role="status"
                    aria-live="polite"
                >
                    <div class="annual-wait__spin" aria-hidden="true"></div>
                    <p class="text-[13px] font-semibold text-navy-900">Please wait</p>
                    <p class="text-[12px] text-slate-500">Loading <span class="font-medium text-slate-700" x-text="labels[tab] || 'this section'"></span>…</p>
                </div>
                @foreach (array_keys($tabs) as $key)
                    <div
                        x-show="tab === @js($key) && loaded[@js($key)]"
                        @if ($key !== $tab) x-cloak @endif
                        data-annual-panel="{{ $key }}"
                        @if ($key === $tab)
                            x-init="markLoaded(@js($key))"
                        @endif
                    >
                        @if ($key === $tab)
                            @include('annual-audit.partials.tab-content')
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function annualAuditTabs({ tab, fy, panelUrl, keys, labels }) {
                return {
                    tab,
                    fy,
                    panelUrl,
                    keys,
                    labels,
                    loaded: { [tab]: true },
                    loading: null,
                    inflight: {},
                    warm(key) {
                        if (!key || key === this.tab || this.loaded[key]) return;
                        this.ensure(key);
                    },
                    go(key) {
                        if (this.tab === key) return;
                        this.tab = key;
                        const url = new URL(window.location.href);
                        url.searchParams.set('fy', this.fy);
                        url.searchParams.set('tab', key);
                        window.history.replaceState({}, '', url.toString());
                        this.ensure(key);
                        document.querySelectorAll('input[name="tab"][data-annual-sync-tab]').forEach((el) => {
                            el.value = key;
                        });
                    },
                    markLoaded(key) {
                        this.loaded[key] = true;
                    },
                    ensure(key) {
                        if (this.loaded[key]) return Promise.resolve();
                        if (this.inflight[key]) return this.inflight[key];
                        const panel = document.querySelector('[data-annual-panel="' + key + '"]');
                        if (! panel) return Promise.resolve();
                        this.loading = key;
                        const job = (async () => {
                            try {
                                const url = new URL(this.panelUrl, window.location.origin);
                                url.searchParams.set('fy', this.fy);
                                url.searchParams.set('tab', key);
                                const res = await fetch(url.toString(), {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                                });
                                if (! res.ok) throw new Error('panel failed');
                                panel.innerHTML = await res.text();
                                if (window.Alpine && typeof Alpine.initTree === 'function') {
                                    Alpine.initTree(panel);
                                }
                                this.loaded[key] = true;
                            } catch (e) {
                                panel.innerHTML = '<div class="p-4 text-[12px] text-rose-700">Could not load this tab. Please refresh.</div>';
                            } finally {
                                delete this.inflight[key];
                                if (this.loading === key) this.loading = null;
                            }
                        })();
                        this.inflight[key] = job;
                        return job;
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
