@php
    $stats = $stats ?? [];
    $todayVisits = $todayVisits ?? [];
    $firstName = explode(' ', trim(auth()->user()->name))[0] ?? auth()->user()->name;
    $visitsUrl = route('monthly-visits.index', ['fy' => $fy->label ?? null, 'month' => $monthIndex ?? 0]);
    $reportsUrl = route('audits.index');
    $delayedOrOverdue = max((int) ($stats['delayed'] ?? 0), (int) ($stats['overdue'] ?? 0));
    $cards = [
        [
            'label' => 'Visits today',
            'value' => number_format($stats['visits_today'] ?? 0),
            'meta' => ($todayLabel ?? 'Today').' · on your calendar',
            'href' => $visitsUrl,
            'tone' => 'magenta',
        ],
        [
            'label' => 'Active today',
            'value' => number_format($stats['today_active'] ?? 0),
            'meta' => number_format($stats['today_completed'] ?? 0).' done today',
            'href' => $visitsUrl,
            'tone' => 'fuchsia',
        ],
        [
            'label' => 'Monthly visits',
            'value' => number_format($stats['visits_month'] ?? 0),
            'meta' => ($monthLabel ?? 'This month').' scheduled',
            'href' => $visitsUrl,
            'tone' => 'violet',
        ],
        [
            'label' => 'Shakhas this month',
            'value' => number_format($stats['shakhas_month'] ?? 0),
            'meta' => 'Branches you cover',
            'href' => $visitsUrl,
            'tone' => 'indigo',
        ],
        [
            'label' => 'Planned',
            'value' => number_format($stats['planned'] ?? 0),
            'meta' => 'Not started yet',
            'href' => $visitsUrl,
            'tone' => 'blue',
        ],
        [
            'label' => 'In progress',
            'value' => number_format($stats['in_progress'] ?? 0),
            'meta' => 'Active field work',
            'href' => $visitsUrl,
            'tone' => 'cyan',
        ],
        [
            'label' => 'Completed',
            'value' => number_format($stats['completed'] ?? 0),
            'meta' => ($stats['month_completion_pct'] ?? 0).'% of month',
            'href' => $visitsUrl,
            'tone' => 'sky',
        ],
        [
            'label' => 'Delayed / overdue',
            'value' => number_format($delayedOrOverdue),
            'meta' => 'Needs your follow-up',
            'href' => $visitsUrl,
            'tone' => 'rose',
        ],
        [
            'label' => 'Critical risk',
            'value' => number_format($stats['risk_critical'] ?? (($stats['risk_significant'] ?? 0) + ($stats['risk_high'] ?? 0))),
            'meta' => 'Significant + High in access',
            'href' => $reportsUrl,
            'tone' => 'magenta',
        ],
        [
            'label' => 'Not assessed',
            'value' => number_format($stats['risk_not_assessed'] ?? 0),
            'meta' => 'Need risk score',
            'href' => $reportsUrl,
            'tone' => 'sky',
        ],
        [
            'label' => 'Branch access',
            'value' => number_format($stats['total_access'] ?? 0),
            'meta' => 'Shakhas you can work on',
            'href' => $reportsUrl,
            'tone' => 'indigo',
        ],
        [
            'label' => 'Draft reports',
            'value' => number_format($stats['drafts'] ?? 0),
            'meta' => ($slotsLeft ?? 0).' slots free',
            'href' => $reportsUrl,
            'tone' => 'violet',
        ],
    ];
@endphp

<div class="px-4 py-5 lg:px-6">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <div class="mb-1 flex items-center gap-2">
                <span class="h-2 w-8 rounded-full bg-gradient-to-r from-[#ff2d9b] via-[#7c3aed] to-[#2563eb]"></span>
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">My work</p>
            </div>
            <h1 class="text-[18px] font-semibold tracking-tight text-navy-900">Hello, {{ $firstName }}</h1>
            <p class="mt-0.5 text-[12px] text-slate-500">
                {{ auth()->user()->roleLabel() }}
                @if (auth()->user()->employee?->position)
                    · {{ auth()->user()->employee->position->title }}
                @endif
                · FY {{ $fy->label ?? '' }}
            </p>
        </div>
        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-1.5">
            <input type="hidden" name="fy" value="{{ $fy->label ?? '' }}">
            <select name="month" class="h-8 rounded-lg border-slate-200 bg-white py-0 text-[12px]" onchange="this.form.submit()">
                @foreach ($monthOptions ?? [] as $opt)
                    <option value="{{ $opt['index'] }}" @selected((int) $opt['index'] === (int) $monthIndex)>
                        {{ $opt['label'] }} {{ $opt['year'] }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @unless (auth()->user()->employee_id)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-[12px] text-amber-900">
            Your login is not linked to an organogram employee — visit allocations cannot appear until Super Admin links you.
        </div>
    @endunless

    @include('partials.dashboard-metric-cards', ['cards' => $cards])

    <div class="mt-4 overflow-hidden rounded-xl border border-[#e9d5ff]/60 bg-white/95 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-2.5">
            <div>
                <p class="text-[13px] font-semibold text-navy-900">Today’s visits</p>
                <p class="text-[10px] text-slate-500">{{ $todayLabel ?? now('Asia/Dhaka')->format('d M Y') }} · {{ count($todayVisits) }} window{{ count($todayVisits) === 1 ? '' : 's' }}</p>
            </div>
            <a href="{{ $visitsUrl }}" class="text-[11px] font-semibold text-[#7c3aed] hover:underline">Open visits</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($todayVisits as $row)
                <div class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5">
                    <div class="min-w-0">
                        <p class="truncate text-[12px] font-semibold text-navy-900">{{ $row['label'] }}</p>
                        <p class="truncate text-[10px] text-slate-500">{{ $row['purpose'] }} · {{ $row['dates'] }}</p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold capitalize {{ $row['tone']['bg'] }} {{ $row['tone']['text'] }}">{{ $row['status_label'] }}</span>
                        <a href="{{ $row['execution_url'] }}" class="inline-flex h-7 items-center rounded-lg bg-gradient-to-r from-[#c026d3] via-[#7c3aed] to-[#2563eb] px-2.5 text-[11px] font-semibold text-white">Open</a>
                    </div>
                </div>
            @empty
                <p class="px-3.5 py-8 text-center text-[12px] text-slate-400">No visits scheduled for today.</p>
            @endforelse
        </div>
    </div>
</div>
