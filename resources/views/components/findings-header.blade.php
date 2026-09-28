@props([
    'title',
    'subtitle' => null,
    'back' => null,
    'backLabel' => 'Back',
    'activeTab' => null,
    'month' => null,
    'year' => null,
    'monthStrip' => null,
    'prevYearUrl' => null,
    'nextYearUrl' => null,
])

<div class="sticky top-0 z-30 -mx-3 -mt-3 bg-canvas/95 px-3 pb-2 pt-3 backdrop-blur lg:-mx-5 lg:px-5">
    <header class="rounded-xl border border-slate-200 border-l-4 border-l-[#2b579a] bg-white shadow-sm">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-3 py-2">
            @if ($back)
                <a
                    href="{{ $back }}"
                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:-translate-y-0.5 hover:border-[#2b579a] hover:text-[#2b579a]"
                    title="{{ $backLabel }}"
                    aria-label="{{ $backLabel }}"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.5 15 7.5 10l5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @else
                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#1b3a70] to-[#2b579a] text-white shadow-[0_6px_14px_rgba(43,87,154,0.3)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3.5" y="3.5" width="17" height="17" rx="2.5"/><path d="M3.5 9h17M3.5 14.5h17M9 3.5v17M14.5 3.5v17"/></svg>
                </span>
            @endif
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-[14px] font-semibold text-navy-900">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="truncate text-[11.5px] text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($activeTab)
                @include('audit-findings.partials.view-tabs', ['activeTab' => $activeTab, 'month' => $month, 'year' => $year])
            @endif
            @if (trim((string) $slot) !== '')
                <div class="flex shrink-0 flex-wrap items-center gap-1.5">{{ $slot }}</div>
            @endif
        </div>

        @if ($monthStrip)
            <div class="flex items-center gap-1.5 border-t border-slate-100 px-2 py-1.5">
                <div class="inline-flex shrink-0 items-center rounded-lg border border-slate-200 text-[12px] font-semibold">
                    <a href="{{ $prevYearUrl }}" class="inline-flex h-7 w-6 items-center justify-center rounded-l-lg text-slate-500 hover:bg-slate-50 hover:text-[#2b579a]" aria-label="Previous year">‹</a>
                    <span class="px-1.5 tabular-nums text-navy-900">{{ $year }}</span>
                    <a href="{{ $nextYearUrl }}" class="inline-flex h-7 w-6 items-center justify-center rounded-r-lg text-slate-500 hover:bg-slate-50 hover:text-[#2b579a]" aria-label="Next year">›</a>
                </div>
                <nav class="grid min-w-0 flex-1 grid-cols-6 gap-1 sm:grid-cols-12" aria-label="Months">
                    @foreach ($monthStrip as $chip)
                        <a
                            href="{{ $chip['url'] }}"
                            @class([
                                'relative flex h-7 items-center justify-center gap-1 rounded-md text-[12px] font-semibold transition',
                                'bg-[#1b3a70] text-white shadow-sm' => $chip['active'],
                                'bg-sky-50 text-sky-900 hover:bg-sky-100' => ! $chip['active'] && $chip['has_data'],
                                'text-slate-400 hover:bg-slate-50 hover:text-slate-600' => ! $chip['active'] && ! $chip['has_data'],
                            ])
                            title="{{ $chip['full'] ?? $chip['label'] }}{{ ! empty($chip['cells']) ? ' · '.$chip['cells'].' finding cells' : '' }}{{ ! empty($chip['branches']) ? ' · '.$chip['branches'].' branches' : '' }}"
                        >
                            {{ $chip['label'] }}
                            @if (! empty($chip['cells']))
                                <span @class(['text-[10.5px] font-medium tabular-nums', 'text-sky-200' => $chip['active'], 'text-sky-600' => ! $chip['active']])>{{ $chip['cells'] }}</span>
                            @elseif ($chip['has_data'])
                                <span @class(['h-1.5 w-1.5 rounded-full', 'bg-sky-200' => $chip['active'], 'bg-sky-500' => ! $chip['active']])></span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>
        @endif
    </header>
</div>
