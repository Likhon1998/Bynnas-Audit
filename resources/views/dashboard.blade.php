<x-app-layout>
@if (($mode ?? 'ops') === 'officer')
    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">My audit work</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Assigned shakhas · your drafts · reports only for your branches</p>
            </div>
            <a href="{{ route('audits.index') }}" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-medium text-white hover:bg-navy-800">Audit Reports</a>
        </div>

        <div class="mb-4 grid gap-2.5 sm:grid-cols-3">
            <div class="rounded-xl border border-sky-200/70 bg-gradient-to-br from-sky-50 via-white to-sky-100/60 px-3.5 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700/80">Assigned shakhas</p>
                <p class="mt-1 text-[22px] font-semibold tabular-nums text-navy-900">{{ $assignedShakhas->count() }}</p>
            </div>
            <div class="rounded-xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-white to-amber-100/60 px-3.5 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700/80">My drafts</p>
                <p class="mt-1 text-[22px] font-semibold tabular-nums text-navy-900">{{ $myDrafts->count() }}</p>
            </div>
            <div class="rounded-xl border border-teal-200/70 bg-gradient-to-br from-teal-50 via-white to-teal-100/60 px-3.5 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-teal-700/80">Slots free</p>
                <p class="mt-1 text-[22px] font-semibold tabular-nums text-navy-900">{{ $slotsLeft }}</p>
            </div>
        </div>

        <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-3.5 py-2.5">
                <p class="text-[13px] font-semibold text-navy-900">Your assigned shakhas</p>
                <p class="text-[10px] text-slate-500">Explicit access + visits where you are assigned</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($assignedShakhas as $shakha)
                    <div class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-[12px] font-semibold text-navy-900">{{ $shakha->name }}</p>
                            <p class="text-[10px] text-slate-500">{{ $shakha->code }} @if($shakha->area)· {{ $shakha->area->name }}@endif</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('audits.index') }}" class="inline-flex h-7 items-center rounded-md bg-[#2b579a] px-2.5 text-[11px] font-semibold text-white">Make report</a>
                            <a href="{{ route('audit-findings.entry', ['shakha' => $shakha->id]) }}" class="inline-flex h-7 items-center rounded-md border border-slate-200 px-2.5 text-[11px] font-medium text-slate-700">Findings</a>
                        </div>
                    </div>
                @empty
                    <p class="px-3.5 py-8 text-center text-[12px] text-slate-400">No shakhas assigned yet. Ask superadmin to link your employee and assign branches.</p>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-3.5 py-2.5">
                <p class="text-[13px] font-semibold text-navy-900">My ongoing drafts</p>
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
    </div>
@else
    @php
        $tones = [
            'amber' => 'from-amber-50 via-white to-amber-100/60 border-amber-200/70 text-amber-800',
            'rose' => 'from-rose-50 via-white to-rose-100/60 border-rose-200/70 text-rose-800',
            'orange' => 'from-orange-50 via-white to-orange-100/60 border-orange-200/70 text-orange-800',
            'slate' => 'from-slate-50 via-white to-slate-100/70 border-slate-200/80 text-slate-700',
            'sky' => 'from-sky-50 via-white to-sky-100/60 border-sky-200/70 text-sky-800',
            'teal' => 'from-teal-50 via-white to-teal-100/60 border-teal-200/70 text-teal-800',
            'indigo' => 'from-indigo-50 via-white to-indigo-100/60 border-indigo-200/70 text-indigo-800',
        ];
    @endphp

    <div class="px-4 py-4 lg:px-6" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        {{-- Header --}}
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Operations pulse</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">
                    {{ $pulse['period_label'] }}
                    · plan
                    <span class="font-medium text-slate-700">{{ $pulse['plan_status'] }}</span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-1.5">
                    <select name="fy" class="h-8 rounded-lg border-slate-200 bg-white py-0 text-[12px] shadow-sm" onchange="this.form.submit()">
                        @foreach ($pulse['fy_options'] as $fy)
                            <option value="{{ $fy }}" @selected($fy === $pulse['fy_label'])>{{ $fy }}</option>
                        @endforeach
                    </select>
                    <select name="month" class="h-8 rounded-lg border-slate-200 bg-white py-0 text-[12px] shadow-sm" onchange="this.form.submit()">
                        @foreach ($pulse['month_options'] as $m)
                            <option value="{{ $m['index'] }}" @selected($m['index'] === $pulse['month_index'])>
                                {{ $m['label'] }} {{ $m['year'] }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('monthly-visits.index', ['fy' => $pulse['fy_label'], 'month' => $pulse['month_index']]) }}" class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">Visits</a>
                <a href="{{ route('audits.index') }}" class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">Reports</a>
                <a href="{{ route('audit-findings.index', ['month' => $pulse['calendar_month'], 'year' => $pulse['calendar_year']]) }}" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-2.5 text-[12px] font-medium text-white hover:bg-navy-800">Findings</a>
            </div>
        </div>

        {{-- Plan vs execution strip --}}
        <div class="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Planned visits', 'value' => $pulse['visit']['planned'], 'meta' => 'Work items this FY month', 'tone' => 'slate'],
                ['label' => 'Assigned', 'value' => $pulse['visit']['assigned'], 'meta' => $pulse['visit']['unassigned'].' still unassigned', 'tone' => 'sky'],
                ['label' => 'Completed', 'value' => $pulse['visit']['completed'], 'meta' => $pulse['visit']['execution_pct'].'% of planned', 'tone' => 'teal'],
                ['label' => 'Delayed / overdue', 'value' => max($pulse['visit']['delayed'], $pulse['visit']['overdue_end']), 'meta' => 'Needs follow-up this week', 'tone' => 'rose'],
            ] as $card)
                <div class="rounded-xl border bg-gradient-to-br {{ $tones[$card['tone']] }} px-3.5 py-3 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-wide opacity-75">{{ $card['label'] }}</p>
                    <p class="mt-1 text-[22px] font-semibold tabular-nums leading-none text-navy-900">{{ number_format($card['value']) }}</p>
                    <p class="mt-2 text-[11px] text-slate-500">{{ $card['meta'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Act this week --}}
        <div class="mb-2 flex items-center justify-between gap-2">
            <h2 class="text-[12px] font-semibold uppercase tracking-wide text-slate-500">Act this week</h2>
            <p class="text-[11px] text-slate-400">Broken process first · then risk · then money</p>
        </div>
        <div class="mb-5 grid gap-2.5 lg:grid-cols-5 sm:grid-cols-2">
            @foreach ($pulse['act'] as $item)
                <a href="{{ $item['href'] }}" class="group rounded-xl border bg-gradient-to-br {{ $tones[$item['tone']] ?? $tones['slate'] }} px-3.5 py-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-[11px] font-semibold uppercase tracking-wide opacity-80">{{ $item['label'] }}</p>
                    <p class="mt-1.5 text-[24px] font-semibold tabular-nums leading-none text-navy-900">{{ number_format($item['count']) }}</p>
                    <p class="mt-2 text-[11px] leading-snug text-slate-500">{{ $item['meta'] }}</p>
                    @if (! empty($item['samples']))
                        <ul class="mt-2 space-y-0.5 border-t border-black/5 pt-2">
                            @foreach ($item['samples'] as $sample)
                                <li class="truncate text-[10px] text-slate-500">{{ $sample }}</li>
                            @endforeach
                        </ul>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="mb-5 grid gap-3 lg:grid-cols-3">
            {{-- My work --}}
            <div class="overflow-hidden rounded-xl border border-sky-100/80 bg-gradient-to-br from-sky-50/70 via-white to-white shadow-sm lg:col-span-1">
                <div class="flex items-center justify-between gap-2 border-b border-sky-100/70 px-3.5 py-2.5">
                    <div>
                        <p class="text-[13px] font-semibold text-navy-900">My work</p>
                        <p class="text-[10px] text-slate-500">
                            {{ $pulse['my_work']['ongoing'] }} ongoing · {{ $pulse['my_work']['slots_left'] }} slot{{ $pulse['my_work']['slots_left'] === 1 ? '' : 's' }} free
                        </p>
                    </div>
                    <a href="{{ route('audits.index') }}" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Open</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($pulse['my_work']['drafts'] as $draft)
                        <a href="{{ $draft['href'] }}" class="flex items-center justify-between gap-2 px-3.5 py-2.5 hover:bg-sky-50/50">
                            <p class="min-w-0 truncate text-[12px] font-medium text-navy-900">{{ $draft['label'] }}</p>
                            <span class="shrink-0 text-[11px] tabular-nums text-slate-500">{{ $draft['progress'] }}%</span>
                        </a>
                    @empty
                        <p class="px-3.5 py-6 text-center text-[12px] text-slate-400">No drafts in your queue</p>
                    @endforelse
                </div>
            </div>

            {{-- Impact strip --}}
            <div class="overflow-hidden rounded-xl border border-teal-100/80 bg-gradient-to-br from-teal-50/70 via-white to-white shadow-sm lg:col-span-2">
                <div class="border-b border-teal-100/70 px-3.5 py-2.5">
                    <p class="text-[13px] font-semibold text-navy-900">Impact this period</p>
                    <p class="text-[10px] text-slate-500">Objected findings · calendar {{ date('F', mktime(0, 0, 0, $pulse['calendar_month'], 1)) }} {{ $pulse['calendar_year'] }}</p>
                </div>
                <div class="grid gap-2 p-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-teal-100/80 bg-white/70 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-teal-700/80">Objected টাকা</p>
                        <p class="mt-1 text-[18px] font-semibold tabular-nums text-navy-900">{{ $pulse['impact']['total_amount_fmt'] }}</p>
                    </div>
                    <div class="rounded-lg border border-rose-100/80 bg-white/70 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-700/80">Major-risk hits</p>
                        <p class="mt-1 text-[18px] font-semibold tabular-nums text-navy-900">{{ number_format($pulse['impact']['major_risk_hits']) }}</p>
                    </div>
                    <div class="rounded-lg border border-indigo-100/80 bg-white/70 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-700/80">Branches objected</p>
                        <p class="mt-1 text-[18px] font-semibold tabular-nums text-navy-900">{{ number_format($pulse['impact']['branches_objected']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Impact lists --}}
        <div class="mb-5 grid gap-3 lg:grid-cols-3">
            <div class="overflow-hidden rounded-xl border border-sky-100/80 bg-gradient-to-br from-sky-50/60 via-white to-white shadow-sm">
                <div class="border-b border-sky-100/70 px-3.5 py-2.5">
                    <p class="text-[13px] font-semibold text-navy-900">Top indicators</p>
                    <p class="text-[10px] text-slate-500">By objected amount</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($pulse['impact']['top_indicators'] as $row)
                        <a href="{{ $row['url'] }}" class="flex items-start justify-between gap-2 px-3.5 py-2.5 hover:bg-sky-50/50">
                            <div class="min-w-0">
                                <p class="truncate text-[12px] font-medium text-navy-900">{{ $row['title'] }}</p>
                                <p class="mt-0.5 font-mono text-[10px] text-slate-400">{{ $row['code'] }}</p>
                            </div>
                            <p class="shrink-0 text-[12px] font-semibold tabular-nums text-sky-800">{{ $row['amount_fmt'] }}</p>
                        </a>
                    @empty
                        <p class="px-3.5 py-6 text-center text-[12px] text-slate-400">No objected indicators</p>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-teal-100/80 bg-gradient-to-br from-teal-50/60 via-white to-white shadow-sm">
                <div class="border-b border-teal-100/70 px-3.5 py-2.5">
                    <p class="text-[13px] font-semibold text-navy-900">Top branches</p>
                    <p class="text-[10px] text-slate-500">Hot follow-up</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($pulse['impact']['top_branches'] as $row)
                        <div class="flex items-start justify-between gap-2 px-3.5 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-[12px] font-medium text-navy-900">{{ $row['name'] }}</p>
                                <p class="mt-0.5 text-[10px] text-slate-400">{{ $row['cells'] }} cells</p>
                            </div>
                            <p class="shrink-0 text-[12px] font-semibold tabular-nums text-teal-800">{{ $row['amount_fmt'] }}</p>
                        </div>
                    @empty
                        <p class="px-3.5 py-6 text-center text-[12px] text-slate-400">No branch findings</p>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-indigo-100/80 bg-gradient-to-br from-indigo-50/60 via-white to-white shadow-sm">
                <div class="border-b border-indigo-100/70 px-3.5 py-2.5">
                    <p class="text-[13px] font-semibold text-navy-900">By category</p>
                    <p class="text-[10px] text-slate-500">Themes with objections</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($pulse['impact']['categories'] as $row)
                        <div class="flex items-start justify-between gap-2 px-3.5 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-[12px] font-medium text-navy-900">{{ $row['name'] }}</p>
                                <p class="mt-0.5 text-[10px] text-slate-400">{{ $row['hits'] }} indicators</p>
                            </div>
                            <p class="shrink-0 text-[12px] font-semibold tabular-nums text-indigo-800">{{ $row['amount_fmt'] }}</p>
                        </div>
                    @empty
                        <p class="px-3.5 py-6 text-center text-[12px] text-slate-400">No category hits</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- System health --}}
        <details class="overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-white shadow-sm" open>
            <summary class="cursor-pointer list-none px-3.5 py-2.5">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-[13px] font-semibold text-navy-900">System health</p>
                        <p class="text-[10px] text-slate-500">Readiness & catalog — secondary to this week’s actions</p>
                    </div>
                    <span class="text-[11px] font-medium text-slate-400">Toggle</span>
                </div>
            </summary>
            <div class="grid gap-2 border-t border-slate-100 p-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($pulse['health'] as $row)
                    <a href="{{ $row['href'] }}" class="rounded-lg border border-slate-200/80 bg-white/80 px-3 py-2.5 hover:border-slate-300 hover:bg-slate-50">
                        <p class="text-[11px] font-semibold text-slate-600">{{ $row['label'] }}</p>
                        <p class="mt-1 text-[18px] font-semibold tabular-nums text-navy-900">{{ number_format($row['count']) }}</p>
                        <p class="mt-1 text-[10px] leading-snug text-slate-400">{{ $row['meta'] }}</p>
                    </a>
                @endforeach
            </div>
        </details>
    </div>
@endif
</x-app-layout>
