@php
    $stats = $stats ?? [];
    $todayVisits = $todayVisits ?? [];
    $allocations = $allocations ?? [];
    $firstName = explode(' ', trim(auth()->user()->name))[0] ?? auth()->user()->name;
    $visitsUrl = auth()->user()->canAny(['monthly_visits.manage', 'monthly_visits.execute'])
        ? route('monthly-visits.index', ['fy' => $fy->label ?? null, 'month' => $monthIndex ?? 0])
        : null;
    $reportsUrl = auth()->user()->canAny(['audits.create', 'audits.manage'])
        ? route('audits.index')
        : null;
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
            @if ($visitsUrl)
                <a href="{{ $visitsUrl }}" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-2.5 text-[12px] font-semibold text-white hover:bg-navy-800">
                    Monthly visits
                </a>
            @endif
            @if ($reportsUrl)
                <a href="{{ $reportsUrl }}" class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">
                    Reports
                </a>
            @endif
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

    @include('partials.my-field-visits', [
        'stats' => $stats,
        'todayVisits' => $todayVisits,
        'allocations' => $allocations,
        'todayLabel' => $todayLabel ?? null,
        'monthLabel' => $monthLabel ?? null,
        'visitsUrl' => $visitsUrl,
        'slotsLeft' => $slotsLeft ?? ($stats['slots_left'] ?? 0),
        'showMonthStats' => true,
    ])
</div>
