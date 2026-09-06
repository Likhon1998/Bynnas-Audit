@php
    $stats = $stats ?? [];
    $timeline = $timeline ?? ['days_in_month' => 30, 'lanes' => [], 'today_day' => null];
    $fyStrip = $fyMonthStrip ?? [];
    $allocations = $allocations ?? [];
    $firstName = explode(' ', trim(auth()->user()->name))[0] ?? auth()->user()->name;
@endphp

<div class="relative min-h-[70vh] px-4 py-4 lg:px-6">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-56 bg-gradient-to-b from-sky-50/90 via-white to-transparent"></div>

    <div class="relative space-y-4">
        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sky-700/80">My field work</p>
                <h1 class="mt-0.5 text-[20px] font-semibold tracking-tight text-navy-900">Hello, {{ $firstName }}</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">
                    {{ auth()->user()->roleLabel() }}
                    @if (auth()->user()->employee?->position)
                        · {{ auth()->user()->employee->position->title }}
                    @endif
                    · FY {{ $fy->label ?? '' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-1.5">
                    <input type="hidden" name="fy" value="{{ $fy->label ?? '' }}">
                    <select name="month" class="h-8 rounded-lg border-slate-200 bg-white py-0 text-[12px] shadow-sm" onchange="this.form.submit()">
                        @foreach ($monthOptions ?? [] as $opt)
                            <option value="{{ $opt['index'] }}" @selected((int) $opt['index'] === (int) $monthIndex)>
                                {{ $opt['label'] }} {{ $opt['year'] }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @can('monthly_visits.execute')
                    <a href="{{ route('monthly-visits.index', ['fy' => $fy->label ?? null, 'month' => $monthIndex ?? 0]) }}" class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">Visits</a>
                @endcan
                <a href="{{ route('audits.index') }}" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-medium text-white shadow-sm hover:bg-navy-800">Reports</a>
            </div>
        </div>

        @unless (auth()->user()->employee_id)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-[12px] text-amber-900">
                Your login is not linked to an organogram employee — visit allocations cannot appear until Super Admin links you.
            </div>
        @endunless

        {{-- KPI strip --}}
        <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-sky-200/70 bg-gradient-to-br from-sky-50 via-white to-sky-100/50 px-3.5 py-3.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700/80">Shakhas this month</p>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/10 text-sky-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-[28px] font-semibold tabular-nums leading-none text-navy-900">{{ number_format($stats['shakhas_month'] ?? 0) }}</p>
                <p class="mt-2 text-[11px] text-slate-500">{{ $monthLabel ?? '' }} · where you are allocated</p>
            </div>
            <div class="rounded-2xl border border-indigo-200/70 bg-gradient-to-br from-indigo-50 via-white to-indigo-100/50 px-3.5 py-3.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-700/80">Visits scheduled</p>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 15h8M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-[28px] font-semibold tabular-nums leading-none text-navy-900">{{ number_format($stats['visits_month'] ?? 0) }}</p>
                <p class="mt-2 text-[11px] text-slate-500">
                    <span class="font-medium text-teal-700">{{ $stats['completed'] ?? 0 }} done</span>
                    · {{ $stats['in_progress'] ?? 0 }} active
                    · {{ $stats['planned'] ?? 0 }} planned
                </p>
            </div>
            <div class="rounded-2xl border border-teal-200/70 bg-gradient-to-br from-teal-50 via-white to-teal-100/50 px-3.5 py-3.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-teal-700/80">All accessible</p>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-500/10 text-teal-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-[28px] font-semibold tabular-nums leading-none text-navy-900">{{ number_format($stats['total_access'] ?? 0) }}</p>
                <p class="mt-2 text-[11px] text-slate-500">Visits + any extra admin access</p>
            </div>
            <div class="rounded-2xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-white to-amber-100/50 px-3.5 py-3.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700/80">Draft reports</p>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-[28px] font-semibold tabular-nums leading-none text-navy-900">{{ number_format($stats['drafts'] ?? 0) }}</p>
                <p class="mt-2 text-[11px] text-slate-500">{{ $slotsLeft ?? 0 }} report slots free</p>
            </div>
        </div>

        {{-- FY month strip --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-2.5">
                <div>
                    <p class="text-[13px] font-semibold text-navy-900">Allocation year</p>
                    <p class="text-[10px] text-slate-500">Tap a month to see where you were allocated</p>
                </div>
                <p class="text-[11px] text-slate-400">FY {{ $fy->label ?? '' }}</p>
            </div>
            <div class="grid grid-cols-4 gap-1.5 p-2.5 sm:grid-cols-6 lg:grid-cols-12">
                @foreach ($fyStrip as $m)
                    <a
                        href="{{ route('dashboard', ['fy' => $fy->label ?? null, 'month' => $m['index']]) }}"
                        class="group relative rounded-xl px-1.5 py-2 text-center transition
                            {{ $m['is_selected']
                                ? 'bg-navy-900 text-white shadow-md shadow-navy-900/20'
                                : ($m['count'] > 0 ? 'bg-sky-50 text-sky-900 hover:bg-sky-100' : 'bg-slate-50 text-slate-400 hover:bg-slate-100') }}"
                    >
                        <p class="text-[10px] font-semibold uppercase tracking-wide opacity-80">{{ $m['label'] }}</p>
                        <p class="mt-0.5 text-[16px] font-semibold tabular-nums leading-none">{{ $m['count'] }}</p>
                        @if ($m['is_current'] && ! $m['is_selected'])
                            <span class="absolute inset-x-2 bottom-1 mx-auto h-0.5 rounded-full bg-sky-400"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Month timeline --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-wrap items-end justify-between gap-2 border-b border-slate-100 px-3.5 py-2.5">
                <div>
                    <p class="text-[13px] font-semibold text-navy-900">{{ $monthLabel ?? 'Month' }} timeline</p>
                    <p class="text-[10px] text-slate-500">Full-month view of your visit windows</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-500">
                    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-sky-500"></span> Planned</span>
                    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span> In progress</span>
                    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-teal-500"></span> Done</span>
                    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-rose-500"></span> Delayed</span>
                </div>
            </div>

            @if (empty($timeline['lanes']))
                <div class="px-3.5 py-12 text-center">
                    <p class="text-[13px] font-medium text-slate-600">No visits allocated in {{ $monthLabel ?? 'this month' }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">When a manager allocates you on Monthly Visits, your schedule appears here.</p>
                </div>
            @else
                <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-1.5">
                    <div class="relative ml-[9.5rem] h-5 sm:ml-44">
                        @for ($d = 1; $d <= ($timeline['days_in_month'] ?? 30); $d++)
                            @if ($d === 1 || $d % 5 === 0 || $d === ($timeline['days_in_month'] ?? 30))
                                <span
                                    class="absolute top-0 -translate-x-1/2 text-[9px] tabular-nums {{ ($timeline['today_day'] ?? null) === $d ? 'font-bold text-sky-700' : 'text-slate-400' }}"
                                    style="left: {{ (($d - 0.5) / max(1, $timeline['days_in_month'] ?? 1)) * 100 }}%"
                                >{{ $d }}</span>
                            @endif
                        @endfor
                        @if ($timeline['today_day'] ?? null)
                            <span
                                class="absolute top-0 bottom-0 w-px bg-sky-400/70"
                                style="left: {{ (($timeline['today_day'] - 0.5) / max(1, $timeline['days_in_month'] ?? 1)) * 100 }}%"
                                title="Today"
                            ></span>
                        @endif
                    </div>
                </div>

                <div class="divide-y divide-slate-50">
                    @foreach ($timeline['lanes'] as $lane)
                        <div class="flex items-center gap-2 px-3 py-2.5 hover:bg-slate-50/60">
                            <div class="w-[9rem] shrink-0 sm:w-40">
                                <p class="truncate text-[12px] font-semibold text-navy-900" title="{{ $lane['label'] }}">{{ $lane['label'] }}</p>
                                <p class="truncate text-[10px] text-slate-400">{{ $lane['dates_label'] }}</p>
                            </div>
                            <div class="relative min-h-[28px] flex-1 rounded-md bg-slate-100/80">
                                @if ($timeline['today_day'] ?? null)
                                    <span
                                        class="absolute inset-y-0 w-px bg-sky-300/60"
                                        style="left: {{ (($timeline['today_day'] - 0.5) / max(1, $timeline['days_in_month'] ?? 1)) * 100 }}%"
                                    ></span>
                                @endif
                                <a
                                    href="{{ $lane['execution_url'] }}"
                                    class="absolute top-1 bottom-1 flex items-center overflow-hidden rounded-md px-2 text-[10px] font-semibold text-white shadow-sm {{ $lane['tone']['bar'] }} transition hover:brightness-110"
                                    style="left: {{ $lane['offset_pct'] }}%; width: {{ max($lane['width_pct'], 4) }}%;"
                                    title="{{ $lane['label'] }} · {{ $lane['status_label'] }}"
                                >
                                    <span class="truncate capitalize">{{ $lane['status_label'] }}</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="grid gap-4 lg:grid-cols-5">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm lg:col-span-3">
                <div class="border-b border-slate-100 px-3.5 py-2.5">
                    <p class="text-[13px] font-semibold text-navy-900">Where I’m allocated · {{ $monthLabel ?? '' }}</p>
                    <p class="text-[10px] text-slate-500">{{ count($allocations) }} visit{{ count($allocations) === 1 ? '' : 's' }} this month</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($allocations as $row)
                        <div class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5">
                            <div class="flex min-w-0 items-start gap-2.5">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $row['tone']['dot'] }}"></span>
                                <div class="min-w-0">
                                    <p class="truncate text-[12px] font-semibold text-navy-900">{{ $row['label'] }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $row['purpose'] }} · {{ $row['dates'] }}@if($row['days']) · {{ $row['days'] }}d @endif</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold capitalize {{ $row['tone']['bg'] }} {{ $row['tone']['text'] }}">{{ $row['status_label'] }}</span>
                                <a href="{{ $row['execution_url'] }}" class="inline-flex h-7 items-center rounded-md border border-slate-200 px-2.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50">Open</a>
                                @if ($row['shakha_id'])
                                    <a href="{{ route('audit-findings.entry', ['shakha' => $row['shakha_id']]) }}" class="inline-flex h-7 items-center rounded-md bg-[#2b579a] px-2.5 text-[11px] font-semibold text-white hover:bg-[#204072]">Findings</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="px-3.5 py-10 text-center text-[12px] text-slate-400">Nothing allocated for this month yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4 lg:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-3.5 py-2.5">
                        <p class="text-[13px] font-semibold text-navy-900">Ongoing drafts</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($myDrafts as $draft)
                            <a href="{{ route('audits.index') }}" class="flex items-center justify-between gap-2 px-3.5 py-2.5 hover:bg-slate-50">
                                <p class="truncate text-[12px] font-medium text-navy-900">{{ $draft->shakha_display_name ?: ($draft->shakha?->name ?? 'Draft') }}</p>
                                <span class="text-[11px] tabular-nums text-slate-500">{{ (int) $draft->progress_pct }}%</span>
                            </a>
                        @empty
                            <p class="px-3.5 py-6 text-center text-[12px] text-slate-400">No drafts yet</p>
                        @endforelse
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-slate-50 via-white to-sky-50/40 shadow-sm">
                    <div class="border-b border-slate-100/80 px-3.5 py-2.5">
                        <p class="text-[13px] font-semibold text-navy-900">Branch access</p>
                        <p class="text-[10px] text-slate-500">{{ ($assignedShakhas ?? collect())->count() }} shakha{{ ($assignedShakhas ?? collect())->count() === 1 ? '' : 's' }} you can work on</p>
                    </div>
                    <div class="max-h-56 divide-y divide-slate-100/80 overflow-y-auto">
                        @forelse ($assignedShakhas ?? [] as $shakha)
                            <div class="flex items-center justify-between gap-2 px-3.5 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-[11px] font-semibold text-navy-900">{{ $shakha->name }}</p>
                                    <p class="text-[9px] text-slate-400">{{ $shakha->code }}</p>
                                </div>
                                <a href="{{ route('audits.index') }}" class="text-[10px] font-semibold text-[#2b579a] hover:underline">Report</a>
                            </div>
                        @empty
                            <p class="px-3.5 py-6 text-center text-[11px] text-slate-400">No branches yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
