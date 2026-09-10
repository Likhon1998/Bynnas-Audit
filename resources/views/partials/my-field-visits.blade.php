@php
    $stats = $stats ?? [];
    $todayVisits = $todayVisits ?? [];
    $allocations = $allocations ?? [];
    $visitsUrl = $visitsUrl ?? null;
    $showMonthStats = $showMonthStats ?? true;
    $todayActiveList = collect($todayVisits)->reject(fn ($row) => ! empty($row['is_done']))->values();
    $todayDoneList = collect($todayVisits)->filter(fn ($row) => ! empty($row['is_done']))->values();
@endphp

{{-- Where to go today --}}
<div class="mb-4 overflow-hidden rounded-2xl border border-sky-200 bg-gradient-to-br from-sky-50 via-white to-violet-50 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-sky-100/80 px-4 py-3">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700">Where to go today</p>
            <p class="mt-0.5 text-[13px] font-semibold text-navy-900">{{ $todayLabel ?? now('Asia/Dhaka')->format('l, d M Y') }}</p>
        </div>
        <div class="flex items-center gap-2 text-[11px] text-slate-500">
            <span class="rounded-full bg-white px-2 py-0.5 font-semibold text-sky-800 ring-1 ring-sky-200">
                {{ count($todayVisits) }} visit{{ count($todayVisits) === 1 ? '' : 's' }}
            </span>
            @if (($stats['today_active'] ?? 0) > 0)
                <span class="rounded-full bg-amber-50 px-2 py-0.5 font-semibold text-amber-800 ring-1 ring-amber-200">
                    {{ $stats['today_active'] }} active
                </span>
            @endif
        </div>
    </div>

    @if (count($todayVisits) === 0)
        <div class="px-4 py-8 text-center">
            <p class="text-[13px] font-semibold text-slate-700">No visit scheduled for today</p>
            <p class="mt-1 text-[12px] text-slate-500">Check your monthly list below for upcoming shakha visits.</p>
        </div>
    @else
        <div class="divide-y divide-sky-100/80">
            @foreach ($todayActiveList as $row)
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3.5">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <p class="text-[14px] font-semibold {{ ! empty($row['risk']) ? \App\Support\ShakhaRiskTone::textClasses($row['risk']) : 'text-navy-900' }}">{{ $row['label'] }}</p>
                                @if (! empty($row['risk']))
                                    <x-shakha-risk-badge :category="$row['risk']" size="xs" />
                                @endif
                                @if (! empty($row['is_solo']))
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600 ring-1 ring-slate-200">Solo</span>
                                @elseif (! empty($row['team_label']))
                                    <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-semibold text-violet-800 ring-1 ring-violet-200">{{ $row['team_label'] }}</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-[11px] text-slate-500">
                                @if ($row['division'] || $row['area'])
                                    {{ collect([$row['division'] ?? null, $row['area'] ?? null])->filter()->implode(' · ') }}
                                    ·
                                @endif
                                {{ $row['purpose'] }}
                                · {{ $row['dates'] }}
                            </p>
                        </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold capitalize {{ $row['tone']['bg'] }} {{ $row['tone']['text'] }}">{{ $row['status_label'] }}</span>
                        <a href="{{ $row['execution_url'] }}" class="inline-flex h-9 items-center rounded-lg bg-navy-900 px-3.5 text-[12px] font-semibold text-white hover:bg-navy-800">
                            Go / Open
                        </a>
                    </div>
                </div>
            @endforeach
            @foreach ($todayDoneList as $row)
                <div class="flex flex-wrap items-center justify-between gap-3 bg-teal-50/40 px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-[13px] font-semibold text-teal-900">{{ $row['label'] }}</p>
                        <p class="text-[11px] text-teal-700/80">Completed · {{ $row['dates'] }}</p>
                    </div>
                    <a href="{{ $row['execution_url'] }}" class="text-[11px] font-semibold text-teal-800 hover:underline">View</a>
                </div>
            @endforeach
        </div>
    @endif
</div>

@if ($showMonthStats)
    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">This month</p>
            <p class="mt-0.5 text-[18px] font-semibold tabular-nums text-navy-900">{{ number_format($stats['visits_month'] ?? 0) }}</p>
            <p class="text-[10px] text-slate-500">{{ $monthLabel ?? 'Visits' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Shakhas</p>
            <p class="mt-0.5 text-[18px] font-semibold tabular-nums text-navy-900">{{ number_format($stats['shakhas_month'] ?? 0) }}</p>
            <p class="text-[10px] text-slate-500">Branches this month</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Completed</p>
            <p class="mt-0.5 text-[18px] font-semibold tabular-nums text-emerald-700">{{ number_format($stats['completed'] ?? 0) }}</p>
            <p class="text-[10px] text-slate-500">{{ $stats['month_completion_pct'] ?? 0 }}% of month</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Draft reports</p>
            <p class="mt-0.5 text-[18px] font-semibold tabular-nums text-navy-900">{{ number_format($stats['drafts'] ?? 0) }}</p>
            <p class="text-[10px] text-slate-500">{{ $slotsLeft ?? 0 }} slots free</p>
        </div>
    </div>
@endif

{{-- Monthly visits --}}
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/80 px-3.5 py-2.5">
        <div>
            <p class="text-[13px] font-semibold text-navy-900">My monthly visits</p>
            <p class="text-[10px] text-slate-500">{{ $monthLabel ?? 'This month' }} · {{ count($allocations) }} allocation{{ count($allocations) === 1 ? '' : 's' }}</p>
        </div>
        @if ($visitsUrl)
            <a href="{{ $visitsUrl }}" class="text-[11px] font-semibold text-brand-600 hover:underline">Full calendar →</a>
        @endif
    </div>

    <div class="divide-y divide-slate-100">
        @forelse ($allocations as $row)
            <div class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5 {{ ! empty($row['is_today']) ? 'bg-sky-50/70' : '' }}">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <p class="truncate text-[12px] font-semibold {{ ! empty($row['risk']) ? \App\Support\ShakhaRiskTone::textClasses($row['risk']) : 'text-navy-900' }}">{{ $row['label'] }}</p>
                        @if (! empty($row['is_today']))
                            <span class="rounded bg-sky-600 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">Today</span>
                        @endif
                        @if (! empty($row['risk']))
                            <x-shakha-risk-badge :category="$row['risk']" size="xs" />
                        @endif
                        @if (! empty($row['is_solo']))
                            <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold text-slate-600 ring-1 ring-slate-200">Solo</span>
                        @elseif (! empty($row['team_label']))
                            <span class="rounded-full bg-violet-50 px-1.5 py-0.5 text-[9px] font-semibold text-violet-800 ring-1 ring-violet-200">{{ $row['team_label'] }}</span>
                        @endif
                    </div>
                    <p class="mt-0.5 truncate text-[10px] text-slate-500">
                        @if ($row['division'] || $row['area'])
                            {{ collect([$row['division'] ?? null, $row['area'] ?? null])->filter()->implode(' · ') }}
                            ·
                        @endif
                        {{ $row['purpose'] }} · {{ $row['dates'] }}
                        @if ($row['days'])
                            · {{ $row['days'] }} day{{ (int) $row['days'] === 1 ? '' : 's' }}
                        @endif
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-1.5">
                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold capitalize {{ $row['tone']['bg'] }} {{ $row['tone']['text'] }}">{{ $row['status_label'] }}</span>
                    <a href="{{ $row['execution_url'] }}" class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">Open</a>
                </div>
            </div>
        @empty
            <p class="px-3.5 py-10 text-center text-[12px] text-slate-400">
                No visits allocated this month yet.
                @if ($visitsUrl)
                    <a href="{{ $visitsUrl }}" class="font-medium text-brand-600 hover:underline">Open Monthly Visits</a>
                @endif
            </p>
        @endforelse
    </div>
</div>
