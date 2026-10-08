@php
    $tabs = [
        'overview' => ['label' => 'Overview', 'url' => route('audit-review.log')],
        'pipeline' => ['label' => 'Pipeline', 'url' => route('audit-review.log.pipeline')],
        'activity' => ['label' => 'Review events', 'url' => route('audit-review.log.activity')],
    ];
    $active = $active ?? 'overview';
@endphp
<div class="sticky top-0 z-30 -mx-3 -mt-3 bg-canvas/95 px-3 pb-2 pt-3 backdrop-blur lg:-mx-5 lg:px-5">
    <header class="flex flex-wrap items-center gap-x-3 gap-y-2 rounded-xl border border-slate-200 border-l-4 border-l-sky-600 bg-white px-3 py-2 shadow-sm">
        <a
            href="{{ $back ?? route('audit-review.index') }}"
            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:-translate-y-0.5 hover:border-sky-500 hover:text-sky-600"
            title="{{ $backLabel ?? 'Back to Review Panel' }}"
            aria-label="{{ $backLabel ?? 'Back to Review Panel' }}"
        >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.5 15 7.5 10l5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="flex min-w-0 items-center gap-2 text-[14px] font-semibold text-navy-900">
                <span class="truncate">{!! $title ?? 'Auditors log' !!}</span>
                @if (! empty($online))
                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10.5px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                        <span class="relative flex h-1.5 w-1.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span></span>
                        {{ $online }} online
                    </span>
                @endif
            </h1>
            <p class="truncate text-[11.5px] text-slate-500">{{ $subtitle ?? 'What every auditor is doing: writing, review, branch visits and more. Watch only.' }}</p>
        </div>
        @isset($ranges)
            <div class="inline-flex shrink-0 rounded-lg border border-slate-200 bg-white p-0.5 text-[12px] font-semibold">
                @foreach ($ranges as $key => $label)
                    <a
                        href="{{ $rangeUrl($key) }}"
                        title="{{ $label }}"
                        @class([
                            'rounded-md px-2.5 py-1.5 transition',
                            'bg-[#1b3a70] text-white shadow-[0_4px_10px_rgba(27,58,112,0.3)]' => $rangeKey === (string) $key,
                            'text-slate-600 hover:bg-slate-50' => $rangeKey !== (string) $key,
                        ])
                    >{{ ['7' => '7 days', '30' => '30 days', '90' => '90 days', 'year' => 'This year'][$key] ?? $label }}</a>
                @endforeach
            </div>
        @endisset
        <nav class="flex shrink-0 flex-wrap items-center gap-1.5">
            <div class="inline-flex rounded-lg bg-slate-100 p-0.5 text-[12px] font-semibold">
                @foreach ($tabs as $key => $tab)
                    <a
                        href="{{ $tab['url'] }}"
                        @class([
                            'rounded-md px-2.5 py-1.5 transition',
                            'bg-white text-navy-900 shadow-sm ring-1 ring-slate-200' => $active === $key,
                            'text-slate-500 hover:text-slate-800' => $active !== $key,
                        ])
                    >{{ $tab['label'] }}</a>
                @endforeach
            </div>
            <a href="{{ route('audit-review.assignments') }}" class="inline-flex h-8 items-center gap-1.5 whitespace-nowrap rounded-lg bg-violet-600 px-3 text-[12px] font-semibold text-white shadow-[0_6px_14px_rgba(124,58,237,0.35)] transition hover:-translate-y-0.5 hover:bg-violet-700">Assign reviewers</a>
        </nav>
    </header>
    @isset($kpiCards)
        @include('audit-review.partials.kpi-cards', ['cards' => $kpiCards])
    @endisset
</div>
