<x-app-layout>
    @php
        $btn = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3 text-[12px] font-semibold text-white transition hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-50';
        $tones = [
            'emerald' => 'bg-emerald-600 hover:bg-emerald-700 shadow-[0_6px_14px_rgba(5,150,105,0.35)]',
            'sky' => 'bg-sky-600 hover:bg-sky-700 shadow-[0_6px_14px_rgba(2,132,199,0.35)]',
            'violet' => 'bg-violet-600 hover:bg-violet-700 shadow-[0_6px_14px_rgba(124,58,237,0.35)]',
            'navy' => 'bg-[#2b579a] hover:bg-[#204072] shadow-[0_6px_14px_rgba(43,87,154,0.35)]',
        ];
        $avatarTones = ['bg-sky-100 text-sky-700', 'bg-violet-100 text-violet-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-800', 'bg-rose-100 text-rose-700', 'bg-teal-100 text-teal-700'];
        $initials = fn ($name) => collect(preg_split('/\s+/u', trim((string) $name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->join('') ?: '?';
        $roleLabel = fn ($user) => $user->roles->pluck('name')->map(fn ($name) => \Illuminate\Support\Str::headline($name))->join(', ');

        $isMonth = $mode === 'month';
        $monthDate = \Illuminate\Support\Carbon::create($year, $month, 1);
        $monthName = $monthDate->format('F Y');
        $today = \App\Support\AppTime::now();
        $reviewerNames = $reviewers->pluck('name', 'id');
        $asStrings = fn ($source) => (object) $auditors->mapWithKeys(fn ($a) => [$a->id => (string) ($source->get($a->id) ?? '')])->all();
        $fixedMap = $asStrings($fixed);
        $original = $isMonth ? $asStrings($monthly) : $fixedMap;
        $previousMap = $asStrings($previousMonthly);
        $fixedCount = $auditors->filter(fn ($a) => $fixed->get($a->id))->count();
        $initialCovered = $auditors->filter(fn ($a) => ($isMonth ? $monthly->get($a->id) : null) ?: $fixed->get($a->id))->count();
        $monthUrl = fn ($y, $m) => route('audit-review.assignments', ['mode' => 'month', 'year' => $y, 'month' => $m]);
    @endphp

    <div
        class="space-y-3 px-3 py-3 lg:px-5"
        style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;"
        x-data="reviewerAssign({
            mode: @js($mode),
            original: @js($original),
            fixed: @js($fixedMap),
            previous: @js($previousMap),
            auditors: @js($auditors->map(fn ($a) => ['id' => $a->id, 'text' => mb_strtolower($a->name.' '.$a->email)])->values()),
            reviewers: @js($reviewers->map(fn ($r) => ['id' => (string) $r->id, 'name' => $r->name])->values()),
        })"
    >
        <div class="sticky top-0 z-30 -mx-3 -mt-3 bg-canvas/95 px-3 pb-2 pt-3 backdrop-blur lg:-mx-5 lg:px-5">
        <header class="flex flex-wrap items-center gap-x-3 gap-y-2 rounded-xl border border-slate-200 border-l-4 border-l-violet-600 bg-white px-3 py-2 shadow-sm">
            <a
                href="{{ route('audit-review.index') }}"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:-translate-y-0.5 hover:border-violet-500 hover:text-violet-600"
                title="Back to Review Panel"
                aria-label="Back to Review Panel"
                @click="leave($event, $el.href)"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.5 15 7.5 10l5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-[14px] font-semibold text-navy-900">
                    Reviewer assignments
                    @if ($isMonth)
                        <span class="font-medium text-slate-400">·</span>
                        <span class="font-medium text-violet-700">{{ $monthName }}</span>
                    @endif
                </h1>
                <p class="truncate text-[11.5px] text-slate-500">
                    @if ($isMonth)
                        Reports for {{ $monthName }} go to the reviewer picked here. Anyone left on "Same as fixed" uses their fixed reviewer.
                    @else
                        One reviewer per auditor for every month. Change it any time; a monthly pick overrides it for that month only.
                    @endif
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-1.5">
                <a href="{{ route('audit-review.log') }}" class="{{ $btn }} {{ $tones['sky'] }}" @click="leave($event, $el.href)">Auditors log</a>
                <a href="{{ route('audit-review.log.pipeline') }}" class="{{ $btn }} {{ $tones['violet'] }}" @click="leave($event, $el.href)">Pipeline</a>
            </div>
        </header>
        </div>

        @if (session('status'))
            <div data-flash class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-[12px] font-medium text-emerald-800">
                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>{{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-[12px] font-medium text-rose-700">{{ $errors->first() }}</div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
            <div class="grid gap-2 md:grid-cols-2">
                <a
                    href="{{ route('audit-review.assignments') }}"
                    @click="leave($event, $el.href)"
                    @class([
                        'group flex items-center gap-3 rounded-xl border-2 px-3 py-2.5 transition hover:-translate-y-0.5',
                        'border-[#2b579a] bg-gradient-to-br from-sky-50 to-white shadow-[0_10px_22px_rgba(43,87,154,0.16)]' => ! $isMonth,
                        'border-slate-200 hover:border-slate-300 hover:shadow-sm' => $isMonth,
                    ])
                >
                    <span @class([
                        'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                        'bg-gradient-to-br from-[#1b3a70] to-[#2b579a] text-white shadow-[0_6px_14px_rgba(43,87,154,0.35)]' => ! $isMonth,
                        'bg-slate-100 text-slate-500' => $isMonth,
                    ])>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="5" y="10.5" width="14" height="9.5" rx="2"/><path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5" stroke-linecap="round"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-2 text-[13px] font-semibold text-navy-900">
                            Fixed · any time
                            @unless ($isMonth)
                                <span class="rounded-md bg-[#2b579a] px-1.5 py-0.5 text-[10.5px] font-semibold text-white">Editing</span>
                            @endunless
                        </p>
                        <p class="text-[11.5px] leading-snug text-slate-500">Same reviewer every month until you change it.</p>
                    </div>
                    <span class="shrink-0 text-right">
                        <span class="block text-[16px] font-bold leading-none text-navy-900">{{ $fixedCount }}</span>
                        <span class="text-[10.5px] text-slate-500">set</span>
                    </span>
                </a>

                <a
                    href="{{ $monthUrl($isMonth ? $year : $today->year, $isMonth ? $month : $today->month) }}"
                    @click="leave($event, $el.href)"
                    @class([
                        'group flex items-center gap-3 rounded-xl border-2 px-3 py-2.5 transition hover:-translate-y-0.5',
                        'border-violet-500 bg-gradient-to-br from-violet-50 to-white shadow-[0_10px_22px_rgba(124,58,237,0.16)]' => $isMonth,
                        'border-slate-200 hover:border-slate-300 hover:shadow-sm' => ! $isMonth,
                    ])
                >
                    <span @class([
                        'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                        'bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white shadow-[0_6px_14px_rgba(124,58,237,0.35)]' => $isMonth,
                        'bg-slate-100 text-slate-500' => ! $isMonth,
                    ])>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="4" y="5.5" width="16" height="14.5" rx="2"/><path d="M4 10h16M8.5 3.5v4M15.5 3.5v4" stroke-linecap="round"/><path d="M9 14.5h2.5v2.5H9z" fill="currentColor" stroke="none"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-2 text-[13px] font-semibold text-navy-900">
                            Monthly
                            @if ($isMonth)
                                <span class="rounded-md bg-violet-600 px-1.5 py-0.5 text-[10.5px] font-semibold text-white">Editing</span>
                            @endif
                        </p>
                        <p class="text-[11.5px] leading-snug text-slate-500">A different reviewer for one month only.</p>
                    </div>
                    <span class="shrink-0 text-right">
                        <span class="block text-[16px] font-bold leading-none text-violet-800">{{ $monthlyTotal }}</span>
                        <span class="text-[10.5px] text-slate-500">monthly picks</span>
                    </span>
                </a>
            </div>

            @if ($isMonth)
                <div class="mt-2 flex flex-wrap items-center gap-2 border-t border-slate-100 px-1 pt-2">
                    <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white">
                        <a href="{{ $monthUrl($year - 1, $month) }}" @click="leave($event, $el.href)" class="inline-flex h-8 w-8 items-center justify-center rounded-l-lg text-slate-500 hover:bg-slate-50 hover:text-violet-600" title="Previous year" aria-label="Previous year">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.5 15 7.5 10l5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                        <span class="px-2 text-[13px] font-semibold text-navy-900">{{ $year }}</span>
                        <a href="{{ $monthUrl($year + 1, $month) }}" @click="leave($event, $el.href)" class="inline-flex h-8 w-8 items-center justify-center rounded-r-lg text-slate-500 hover:bg-slate-50 hover:text-violet-600" title="Next year" aria-label="Next year">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 15 5-5-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                    <div class="flex flex-1 flex-wrap gap-1">
                        @foreach (range(1, 12) as $m)
                            @php
                                $planned = (int) ($monthsWithPlan[$m] ?? 0);
                                $isActive = $m === $month;
                                $isNow = $year === (int) $today->year && $m === (int) $today->month;
                            @endphp
                            <a
                                href="{{ $monthUrl($year, $m) }}"
                                @click="leave($event, $el.href)"
                                title="{{ \Illuminate\Support\Carbon::create($year, $m, 1)->format('F Y') }}{{ $planned ? ' · '.$planned.' monthly '.($planned === 1 ? 'pick' : 'picks') : '' }}"
                                @class([
                                    'relative inline-flex h-8 min-w-[46px] items-center justify-center gap-1 rounded-lg px-2 text-[12px] font-semibold transition hover:-translate-y-0.5',
                                    'bg-violet-600 text-white shadow-[0_6px_14px_rgba(124,58,237,0.35)]' => $isActive,
                                    'bg-violet-50 text-violet-800 ring-1 ring-violet-200 hover:bg-violet-100' => ! $isActive && $planned,
                                    'bg-slate-50 text-slate-600 ring-1 ring-slate-200 hover:bg-white' => ! $isActive && ! $planned,
                                ])
                            >
                                {{ \Illuminate\Support\Carbon::create($year, $m, 1)->format('M') }}
                                @if ($planned)
                                    <span @class(['rounded px-1 text-[10px] leading-4', 'bg-white/25' => $isActive, 'bg-violet-200/70' => ! $isActive])>{{ $planned }}</span>
                                @endif
                                @if ($isNow)
                                    <span @class(['absolute -top-1 -right-1 h-2 w-2 rounded-full ring-2 ring-white', 'bg-amber-300' => $isActive, 'bg-emerald-500' => ! $isActive]) title="This month"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
            <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_12px_24px_rgba(15,33,71,0.12)]">
                <span class="pointer-events-none absolute -right-6 -top-6 h-20 w-20 rounded-full bg-sky-100/80 transition group-hover:scale-110"></span>
                <div class="relative flex items-center gap-2.5">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#1b3a70] to-[#2b579a] text-white shadow-[0_6px_14px_rgba(43,87,154,0.35)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c.6-3 2.8-4.8 5.5-4.8s4.9 1.8 5.5 4.8" stroke-linecap="round"/><circle cx="17" cy="9" r="2.4"/><path d="M16 14.3c2.3.1 4 1.6 4.5 4.2" stroke-linecap="round"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Auditors</p>
                        <p class="text-[22px] font-bold leading-none text-navy-900">{{ $auditors->count() }}</p>
                    </div>
                </div>
                <p class="relative mt-2 text-[11.5px] text-slate-500">People who write audit reports</p>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50 px-3 py-2.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_12px_24px_rgba(5,150,105,0.18)]">
                <span class="pointer-events-none absolute -right-6 -top-6 h-20 w-20 rounded-full bg-emerald-100/80 transition group-hover:scale-110"></span>
                <div class="relative flex items-center gap-2.5">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-[0_6px_14px_rgba(5,150,105,0.35)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3.5 5 6.2v5.3c0 4.2 2.9 7.6 7 9 4.1-1.4 7-4.8 7-9V6.2L12 3.5Z" stroke-linejoin="round"/><path d="m8.8 12 2.2 2.2 4.2-4.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Have a reviewer</p>
                        <p class="flex items-baseline gap-1.5 leading-none">
                            <span class="text-[22px] font-bold text-emerald-800" x-text="assignedCount">{{ $initialCovered }}</span>
                            <span class="text-[12px] font-semibold text-emerald-600" x-text="coverage + '% covered'"></span>
                        </p>
                    </div>
                </div>
                <div class="relative mt-2 h-1.5 overflow-hidden rounded-full bg-emerald-100">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500" :style="`width: ${coverage}%`"></div>
                </div>
            </div>

            <button
                type="button"
                class="group relative overflow-hidden rounded-2xl border px-3 py-2.5 text-left shadow-sm transition hover:-translate-y-0.5"
                :class="unassignedCount ? 'border-amber-300 bg-gradient-to-br from-white to-amber-50 hover:shadow-[0_12px_24px_rgba(217,119,6,0.2)]' : 'border-emerald-200 bg-gradient-to-br from-white to-emerald-50 hover:shadow-[0_12px_24px_rgba(5,150,105,0.18)]'"
                @click="filter = unassignedCount ? 'open' : 'all'"
            >
                <span class="pointer-events-none absolute -right-6 -top-6 h-20 w-20 rounded-full transition group-hover:scale-110" :class="unassignedCount ? 'bg-amber-100/80' : 'bg-emerald-100/80'"></span>
                <div class="relative flex items-center gap-2.5">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white" :class="unassignedCount ? 'bg-gradient-to-br from-amber-400 to-orange-500 shadow-[0_6px_14px_rgba(234,88,12,0.35)]' : 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-[0_6px_14px_rgba(5,150,105,0.35)]'">
                        <svg x-show="unassignedCount" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v5" stroke-linecap="round"/><circle cx="12" cy="16.5" r="0.6" fill="currentColor"/><path d="M10.3 4.3 3.2 17a2 2 0 0 0 1.7 3h14.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z" stroke-linejoin="round"/></svg>
                        <svg x-show="! unassignedCount" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m5 12.5 4.2 4.2L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wide" :class="unassignedCount ? 'text-amber-700' : 'text-emerald-700'">Need a reviewer</p>
                        <p class="text-[22px] font-bold leading-none" :class="unassignedCount ? 'text-amber-900' : 'text-emerald-800'" x-text="unassignedCount">{{ $auditors->count() - $initialCovered }}</p>
                    </div>
                </div>
                <p class="relative mt-2 text-[11.5px] font-medium" :class="unassignedCount ? 'text-amber-800' : 'text-emerald-700'">
                    <span x-show="unassignedCount">Show them <span class="inline-block transition group-hover:translate-x-0.5">→</span></span>
                    <span x-show="! unassignedCount" x-cloak>Everyone is covered</span>
                </p>
            </button>

            @if ($isMonth)
                <div class="group relative overflow-hidden rounded-2xl border border-violet-200 bg-gradient-to-br from-white to-violet-50 px-3 py-2.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_12px_24px_rgba(124,58,237,0.18)]">
                    <span class="pointer-events-none absolute -right-6 -top-6 h-20 w-20 rounded-full bg-violet-100/80 transition group-hover:scale-110"></span>
                    <div class="relative flex items-center gap-2.5">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white shadow-[0_6px_14px_rgba(124,58,237,0.35)]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="4" y="5.5" width="16" height="14.5" rx="2"/><path d="M4 10h16M8.5 3.5v4M15.5 3.5v4" stroke-linecap="round"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700">Special this month</p>
                            <p class="text-[22px] font-bold leading-none text-violet-900" x-text="monthlyCount">{{ $monthly->count() }}</p>
                        </div>
                    </div>
                    <p class="relative mt-2 text-[11.5px] text-violet-700" x-text="(auditors.length - monthlyCount) + ' follow the fixed list'"></p>
                </div>
            @else
                <div class="group relative overflow-hidden rounded-2xl border border-violet-200 bg-gradient-to-br from-white to-violet-50 px-3 py-2.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_12px_24px_rgba(124,58,237,0.18)]">
                    <span class="pointer-events-none absolute -right-6 -top-6 h-20 w-20 rounded-full bg-violet-100/80 transition group-hover:scale-110"></span>
                    <div class="relative flex items-center gap-2.5">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white shadow-[0_6px_14px_rgba(124,58,237,0.35)]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" stroke-linejoin="round"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700">Reviewers available</p>
                            <p class="text-[22px] font-bold leading-none text-violet-900">{{ $reviewers->count() }}</p>
                        </div>
                    </div>
                    <p class="relative mt-2 text-[11.5px] text-violet-700">
                        <span x-text="activeReviewers + ' in use'"></span>
                        <span class="mx-0.5 text-violet-400">·</span>
                        <span x-text="activeReviewers ? 'about ' + avgLoad + (avgLoad === 1 ? ' auditor' : ' auditors') + ' each' : 'none picked yet'"></span>
                    </p>
                </div>
            @endif
        </div>

        <div class="grid items-start gap-3 lg:grid-cols-[minmax(0,1fr)_300px]">
            <form
                method="POST"
                action="{{ route('audit-review.assignments.save') }}"
                class="overflow-hidden rounded-2xl border bg-white shadow-sm {{ $isMonth ? 'border-violet-200' : 'border-slate-200' }}"
                @submit="saving = true"
            >
                @csrf
                <input type="hidden" name="mode" value="{{ $mode }}">
                @if ($isMonth)
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                @endif

                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                    <div class="relative min-w-[180px] flex-1">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="9" r="5.5"/><path d="m13.5 13.5 3 3" stroke-linecap="round"/></svg>
                        <input
                            type="search"
                            x-model="search"
                            placeholder="Search auditor by name or email"
                            class="h-8 w-full rounded-lg border-slate-200 pl-8 text-[12px] focus:border-violet-400 focus:ring-violet-400"
                        >
                    </div>
                    <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-[12px] font-semibold">
                        <button type="button" class="rounded-md px-2.5 py-1 transition" :class="filter === 'all' ? 'bg-[#1b3a70] text-white' : 'text-slate-600 hover:bg-slate-50'" @click="filter = 'all'">All <span class="opacity-70">{{ $auditors->count() }}</span></button>
                        <button type="button" class="rounded-md px-2.5 py-1 transition" :class="filter === 'open' ? 'bg-amber-500 text-white' : 'text-slate-600 hover:bg-slate-50'" @click="filter = 'open'">Need reviewer <span class="opacity-70" x-text="unassignedCount"></span></button>
                        <button type="button" class="rounded-md px-2.5 py-1 transition" :class="filter === 'set' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-50'" @click="filter = 'set'">Assigned <span class="opacity-70" x-text="assignedCount"></span></button>
                    </div>
                </div>

                @if ($auditors->isNotEmpty() && $reviewers->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 px-3 py-2 text-[12px]">
                        <div class="flex flex-wrap items-center gap-2" x-show="unassignedCount > 0" x-cloak>
                            <span class="font-medium text-slate-600">Quick fill:</span>
                            <span class="text-slate-500">give everyone without a reviewer to</span>
                            <select x-model="bulk" class="h-8 min-w-[160px] rounded-lg border-slate-200 py-0 pl-2.5 text-[12px] focus:border-violet-400 focus:ring-violet-400">
                                <option value="">Choose reviewer…</option>
                                @foreach ($reviewers as $reviewer)
                                    <option value="{{ $reviewer->id }}">{{ $reviewer->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="{{ str_replace(['h-8', 'px-3'], ['h-7', 'px-2.5'], $btn) }} {{ $tones['navy'] }}" :disabled="! bulk" @click="fillOpen()">Apply</button>
                        </div>
                        @if ($isMonth)
                            <div class="ml-auto flex flex-wrap gap-1.5">
                                <button
                                    type="button"
                                    class="{{ str_replace(['h-8', 'px-3'], ['h-7', 'px-2.5'], $btn) }} {{ $tones['violet'] }}"
                                    :disabled="! hasPrevious"
                                    title="{{ $previousMonthly->isEmpty() ? 'No monthly picks in '.$monthDate->copy()->subMonth()->format('F Y') : 'Copy the monthly picks from '.$monthDate->copy()->subMonth()->format('F Y') }}"
                                    @click="copyPrevious()"
                                >Copy {{ $monthDate->copy()->subMonth()->format('M Y') }}</button>
                                <button
                                    type="button"
                                    class="inline-flex h-7 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-semibold text-slate-600 transition hover:-translate-y-0.5 hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-50"
                                    :disabled="monthlyCount === 0"
                                    @click="resetMonth()"
                                >All use fixed</button>
                            </div>
                        @endif
                    </div>
                @endif

                <ul class="divide-y divide-slate-100">
                    @forelse ($auditors as $i => $auditor)
                        @php
                            $fixedId = (string) ($fixed->get($auditor->id) ?? '');
                            $fixedName = $fixedId !== '' ? ($reviewerNames[$fixedId] ?? 'reviewer #'.$fixedId) : null;
                        @endphp
                        <li
                            class="grid items-center gap-x-3 gap-y-2 px-3 py-2.5 transition sm:grid-cols-[minmax(0,1fr)_minmax(0,260px)_104px]"
                            :class="changed({{ $auditor->id }}) ? 'bg-violet-50/60' : (effective({{ $auditor->id }}) ? '' : 'bg-amber-50/40')"
                            x-show="visible({{ $auditor->id }})"
                        >
                            <div class="flex min-w-0 items-center gap-2.5">
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[12px] font-semibold {{ $avatarTones[$auditor->id % count($avatarTones)] }}">{{ $initials($auditor->name) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-[13px] font-semibold text-slate-800">{{ $auditor->name }}</p>
                                    <p class="truncate text-[11.5px] text-slate-500">
                                        {{ $auditor->email }}
                                        @if ($roleLabel($auditor) !== '')
                                            <span class="text-slate-300">·</span> {{ $roleLabel($auditor) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="hidden h-4 w-4 shrink-0 text-slate-300 sm:block" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h11m0 0-4-4m4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <input type="hidden" name="assignments[{{ $i }}][auditor_user_id]" value="{{ $auditor->id }}">
                                <select
                                    name="assignments[{{ $i }}][reviewer_user_id]"
                                    x-model="picks[{{ $auditor->id }}]"
                                    class="h-8 w-full rounded-lg py-0 pl-2.5 text-[12px] focus:border-violet-400 focus:ring-violet-400"
                                    :class="picks[{{ $auditor->id }}] ? 'border-slate-200 text-slate-800' : (effective({{ $auditor->id }}) ? 'border-slate-200 text-slate-500' : 'border-amber-300 text-amber-800')"
                                    aria-label="Reviewer for {{ $auditor->name }}"
                                >
                                    @if ($isMonth)
                                        <option value="">{{ $fixedName ? 'Same as fixed ('.$fixedName.')' : 'Same as fixed (none set)' }}</option>
                                    @else
                                        <option value="">No reviewer yet</option>
                                    @endif
                                    @foreach ($reviewers as $reviewer)
                                        @continue((int) $reviewer->id === (int) $auditor->id)
                                        <option value="{{ $reviewer->id }}" @selected($original->{$auditor->id} === (string) $reviewer->id)>{{ $reviewer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:text-right">
                                <span x-show="changed({{ $auditor->id }})" x-cloak class="inline-flex rounded-md bg-violet-100 px-1.5 py-0.5 text-[11px] font-semibold text-violet-700 ring-1 ring-violet-200">Changed</span>
                                @if ($isMonth)
                                    <span x-show="! changed({{ $auditor->id }}) && picks[{{ $auditor->id }}]" class="inline-flex rounded-md bg-violet-50 px-1.5 py-0.5 text-[11px] font-semibold text-violet-700 ring-1 ring-violet-200">This month</span>
                                    <span x-show="! changed({{ $auditor->id }}) && ! picks[{{ $auditor->id }}] && effective({{ $auditor->id }})" class="inline-flex rounded-md bg-emerald-50 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">Uses fixed</span>
                                @else
                                    <span x-show="! changed({{ $auditor->id }}) && picks[{{ $auditor->id }}]" class="inline-flex rounded-md bg-emerald-50 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">Assigned</span>
                                @endif
                                <span x-show="! changed({{ $auditor->id }}) && ! effective({{ $auditor->id }})" class="inline-flex rounded-md bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200">Not set</span>
                            </div>
                        </li>
                    @empty
                        <li class="px-3 py-10 text-center">
                            <p class="text-[13px] font-semibold text-slate-700">No auditors yet</p>
                            <p class="mt-0.5 text-[12px] text-slate-500">Give a role the "create audit reports" permission and its users will show up here.</p>
                        </li>
                    @endforelse
                    @if ($auditors->isNotEmpty())
                        <li class="px-3 py-8 text-center" x-show="visibleCount === 0" x-cloak>
                            <p class="text-[13px] font-semibold text-slate-700">No auditor matches</p>
                            <p class="mt-0.5 text-[12px] text-slate-500">Try another name, or <button type="button" class="font-semibold text-violet-600 hover:underline" @click="search = ''; filter = 'all'">show everyone</button>.</p>
                        </li>
                    @endif
                </ul>

                @if ($auditors->isNotEmpty())
                    <div class="sticky bottom-0 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 bg-white/95 px-3 py-2 backdrop-blur">
                        <p class="text-[12px]">
                            <span x-show="changeCount > 0" x-cloak class="font-semibold text-violet-700"><span x-text="changeCount"></span> unsaved <span x-text="changeCount === 1 ? 'change' : 'changes'"></span></span>
                            <span x-show="changeCount === 0" class="text-slate-500">No unsaved changes</span>
                        </p>
                        <div class="flex gap-1.5">
                            <button
                                type="button"
                                class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-600 transition hover:-translate-y-0.5 hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-50"
                                :disabled="changeCount === 0"
                                @click="undo()"
                            >Undo changes</button>
                            <button type="submit" class="{{ $btn }} {{ $tones['emerald'] }}" :disabled="changeCount === 0 || saving">
                                <span x-text="saving ? 'Saving…' : @js($isMonth ? 'Save '.$monthName : 'Save assignments')">{{ $isMonth ? 'Save '.$monthName : 'Save assignments' }}</span>
                            </button>
                        </div>
                    </div>
                @endif
            </form>

            <aside class="space-y-3 lg:sticky lg:top-[80px]">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                        <p class="text-[13px] font-semibold text-navy-900">{{ $isMonth ? 'Workload in '.$monthName : 'Reviewer workload' }}</p>
                        <p class="text-[11.5px] text-slate-500">Auditors each reviewer looks after. Updates as you pick.</p>
                    </div>
                    <ul class="max-h-[min(50vh,480px)] divide-y divide-slate-100 overflow-y-auto">
                        @forelse ($reviewers as $reviewer)
                            <li class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10.5px] font-semibold {{ $avatarTones[$reviewer->id % count($avatarTones)] }}">{{ $initials($reviewer->name) }}</span>
                                    <p class="min-w-0 flex-1 truncate text-[12.5px] font-medium text-slate-700">{{ $reviewer->name }}</p>
                                    <span class="text-[12px] font-semibold text-navy-900" x-text="load('{{ $reviewer->id }}')"></span>
                                </div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-sky-500 transition-all" :style="`width: ${loadPercent('{{ $reviewer->id }}')}%`"></div>
                                </div>
                            </li>
                        @empty
                            <li class="px-3 py-4 text-[12px] text-slate-500">No one has review access yet. Give a role the "review audit reports" permission first.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <p class="text-[13px] font-semibold text-navy-900">How it works</p>
                    <ul class="mt-1.5 space-y-1.5 text-[12px] text-slate-600">
                        <li class="flex gap-2">
                            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-[#2b579a] text-white">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="5" y="10.5" width="14" height="9.5" rx="2"/><path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5"/></svg>
                            </span>
                            <span><span class="font-semibold text-slate-800">Fixed</span> is the everyday reviewer. It applies to every month and can be changed at any time.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-violet-600 text-white">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="4" y="5.5" width="16" height="14.5" rx="2"/><path d="M4 10h16"/></svg>
                            </span>
                            <span><span class="font-semibold text-slate-800">Monthly</span> replaces the fixed reviewer for one month only, then things go back to fixed.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-white">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12.5 4.2 4.2L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span>A report goes to the reviewer set for its audit month. Reports already sent keep their reviewer.</span>
                        </li>
                    </ul>
                    <p class="mt-2 rounded-lg bg-slate-50 px-2 py-1.5 text-[11.5px] text-slate-500">Nobody can review their own reports, so each auditor's own name is left out of their list.</p>
                </section>
            </aside>
        </div>
    </div>

    @push('scripts')
        <script>
            function reviewerAssign(cfg) {
                return {
                    mode: cfg.mode,
                    original: { ...cfg.original },
                    picks: { ...cfg.original },
                    fixed: cfg.fixed || {},
                    previous: cfg.previous || {},
                    auditors: cfg.auditors,
                    reviewers: cfg.reviewers,
                    search: '',
                    filter: 'all',
                    bulk: '',
                    saving: false,

                    effective(id) {
                        const pick = String(this.picks[id] ?? '');
                        if (pick || this.mode !== 'month') return pick;
                        return String(this.fixed[id] ?? '');
                    },
                    get assignedCount() {
                        return this.auditors.filter((a) => this.effective(a.id)).length;
                    },
                    get unassignedCount() {
                        return this.auditors.length - this.assignedCount;
                    },
                    get monthlyCount() {
                        return this.auditors.filter((a) => this.picks[a.id]).length;
                    },
                    get hasPrevious() {
                        return this.auditors.some((a) => this.previous[a.id]);
                    },
                    get coverage() {
                        return this.auditors.length ? Math.round((this.assignedCount / this.auditors.length) * 100) : 0;
                    },
                    get activeReviewers() {
                        return this.reviewers.filter((r) => this.load(r.id) > 0).length;
                    },
                    get avgLoad() {
                        const active = this.activeReviewers;
                        return active ? Math.round((this.assignedCount / active) * 10) / 10 : 0;
                    },
                    get changeCount() {
                        return this.auditors.filter((a) => this.changed(a.id)).length;
                    },
                    get visibleCount() {
                        return this.auditors.filter((a) => this.visible(a.id)).length;
                    },
                    changed(id) {
                        return String(this.picks[id] ?? '') !== String(this.original[id] ?? '');
                    },
                    visible(id) {
                        const covered = this.effective(id);
                        const settled = ! this.changed(id);
                        if (this.filter === 'open' && covered && settled) return false;
                        if (this.filter === 'set' && ! covered && settled) return false;
                        const q = this.search.trim().toLowerCase();
                        if (! q) return true;
                        const row = this.auditors.find((a) => a.id === id);
                        return row ? row.text.includes(q) : true;
                    },
                    load(reviewerId) {
                        return this.auditors.filter((a) => this.effective(a.id) === String(reviewerId)).length;
                    },
                    loadPercent(reviewerId) {
                        const max = Math.max(1, ...this.reviewers.map((r) => this.load(r.id)));
                        return Math.round((this.load(reviewerId) / max) * 100);
                    },
                    fillOpen() {
                        if (! this.bulk) return;
                        this.auditors.forEach((a) => {
                            if (! this.effective(a.id) && String(a.id) !== String(this.bulk)) {
                                this.picks[a.id] = String(this.bulk);
                            }
                        });
                        this.bulk = '';
                    },
                    copyPrevious() {
                        this.auditors.forEach((a) => {
                            this.picks[a.id] = String(this.previous[a.id] ?? '');
                        });
                    },
                    resetMonth() {
                        this.auditors.forEach((a) => {
                            this.picks[a.id] = '';
                        });
                    },
                    undo() {
                        this.picks = { ...this.original };
                    },
                    async leave(event, url) {
                        if (this.changeCount === 0) return;
                        event.preventDefault();
                        const n = this.changeCount;
                        const ok = await window.bynnasConfirm({
                            title: 'Leave without saving?',
                            message: `You have ${n} unsaved ${n === 1 ? 'change' : 'changes'} on this page. They will be lost if you leave now.`,
                            okLabel: 'Leave page',
                            tone: 'amber',
                        });
                        if (ok) window.location.href = url;
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
