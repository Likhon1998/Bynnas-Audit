@php
    $tabMonth = $month ?? (int) bd_now()->month;
    $tabYear = $year ?? (int) bd_now()->year;
    $activeTab = $activeTab ?? 'matrix';
    $canMatrix = auth()->user()?->can('findings.view_all');
    $canSummary = auth()->user()?->canany(['findings.summary.view', 'findings.view_all']);
@endphp

@if ($canMatrix || $canSummary)
<nav class="mt-2 inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5" aria-label="Findings views">
    @if ($canMatrix)
        <a
            href="{{ route('audit-findings.index', ['month' => $tabMonth, 'year' => $tabYear]) }}"
            class="rounded-md px-3 py-1.5 text-[12px] font-semibold transition {{ $activeTab === 'matrix' ? 'bg-white text-navy-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}"
        >Findings Matrix</a>
    @endif
    @if ($canSummary)
        <a
            href="{{ route('audit-findings.summary', ['month' => $tabMonth, 'year' => $tabYear]) }}"
            class="rounded-md px-3 py-1.5 text-[12px] font-semibold transition {{ $activeTab === 'summary' ? 'bg-white text-navy-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}"
        >Findings Summary</a>
    @endif
</nav>
@endif
