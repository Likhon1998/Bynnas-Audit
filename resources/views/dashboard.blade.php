<x-app-layout>
@if (($mode ?? 'ops') === 'officer')
    @include('dashboard-officer')
@else
    @php
        $visitsUrl = route('monthly-visits.index', ['fy' => $pulse['fy_label'], 'month' => $pulse['month_index']]);
        $shakhasUrl = route('shakhas.index');
        $risk = $pulse['shakha_risk'] ?? [];
        $sights = $pulse['sights'] ?? [];

        // Row 1 — total shakha + risk breakdown
        $row1 = [
            [
                'label' => 'Total shakha',
                'value' => number_format($risk['active'] ?? 0),
                'meta' => 'Active branches',
                'href' => $shakhasUrl,
                'tone' => 'blue',
            ],
            [
                'label' => 'Significant',
                'value' => number_format($risk['significant'] ?? 0),
                'meta' => 'Highest risk',
                'href' => $shakhasUrl,
                'tone' => 'rose',
            ],
            [
                'label' => 'High',
                'value' => number_format($risk['high'] ?? 0),
                'meta' => 'High risk',
                'href' => $shakhasUrl,
                'tone' => 'orange',
            ],
            [
                'label' => 'Medium',
                'value' => number_format($risk['medium'] ?? 0),
                'meta' => 'Medium risk',
                'href' => $shakhasUrl,
                'tone' => 'amber',
            ],
            [
                'label' => 'Low',
                'value' => number_format($risk['low'] ?? 0),
                'meta' => 'Low risk',
                'href' => $shakhasUrl,
                'tone' => 'emerald',
            ],
        ];

        // Row 2 — annual / monthly plan shakhas, target %, KPI
        $row2 = [
            [
                'label' => 'Annual plan shakhas',
                'value' => number_format($sights['annual_plan_shakhas'] ?? 0),
                'meta' => 'FY '.$pulse['fy_label'].' · '.($sights['plan_status'] ?? $pulse['plan_status']),
                'href' => route('annual-audit.index'),
                'tone' => 'indigo',
            ],
            [
                'label' => 'Monthly plan shakhas',
                'value' => number_format($sights['monthly_plan_shakhas'] ?? 0),
                'meta' => $pulse['month_label'],
                'href' => $visitsUrl,
                'tone' => 'blue',
            ],
            [
                'label' => 'Key Performance Indicator (KPI) entered',
                'value' => number_format($sights['kpi_pct'] ?? 0, 1).'%',
                'meta' => number_format($sights['kpi_entered'] ?? 0).' of '.number_format($sights['kpi_total'] ?? 0).' · '.number_format($sights['kpi_missing'] ?? 0).' missing',
                'href' => route('kpis.index'),
                'tone' => 'violet',
            ],
        ];
    @endphp

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="mb-1 flex items-center gap-2">
                    <span class="h-2 w-8 rounded-full bg-gradient-to-r from-[#ff2d9b] via-[#7c3aed] to-[#2563eb]"></span>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Bynnas Audit</p>
                </div>
                <h1 class="text-[18px] font-semibold tracking-tight text-navy-900">Dashboard</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">
                    {{ $pulse['period_label'] }}
                    · plan <span class="font-medium text-slate-700">{{ $pulse['plan_status'] }}</span>
                </p>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-1.5">
                <select name="fy" class="h-8 rounded-lg border-slate-200 bg-white py-0 text-[12px]" onchange="this.form.submit()">
                    @foreach ($pulse['fy_options'] as $fy)
                        <option value="{{ $fy }}" @selected($fy === $pulse['fy_label'])>{{ $fy }}</option>
                    @endforeach
                </select>
                <select name="month" class="h-8 rounded-lg border-slate-200 bg-white py-0 text-[12px]" onchange="this.form.submit()">
                    @foreach ($pulse['month_options'] as $m)
                        <option value="{{ $m['index'] }}" @selected($m['index'] === $pulse['month_index'])>
                            {{ $m['label'] }} {{ $m['year'] }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-slate-50/60 to-rose-50/40 p-3.5 shadow-[0_8px_24px_rgba(15,23,42,0.05)]">
                <div class="mb-3 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-gradient-to-br from-rose-500 to-orange-400 shadow-sm shadow-rose-300"></span>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Shakha risk</p>
                    <span class="h-px flex-1 bg-gradient-to-r from-rose-200/80 to-transparent"></span>
                </div>
                @include('partials.dashboard-metric-cards', ['cards' => $row1, 'columns' => 5])
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-blue-50/30 to-violet-50/50 p-3.5 shadow-[0_8px_24px_rgba(15,23,42,0.05)]">
                <div class="mb-3 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-gradient-to-br from-blue-500 to-violet-500 shadow-sm shadow-blue-300"></span>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Plan · target · Key Performance Indicator (KPI)</p>
                    <span class="h-px flex-1 bg-gradient-to-r from-blue-200/80 to-transparent"></span>
                </div>
                @include('partials.dashboard-metric-cards', ['cards' => $row2, 'columns' => 3])
            </div>
        </div>
    </div>
@endif

@if (auth()->user()?->isSuperAdmin())
    <x-superadmin-chatbot />
@endif
</x-app-layout>
