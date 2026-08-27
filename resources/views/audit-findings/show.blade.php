<x-app-layout>
    <div class="px-4 py-4 lg:px-6" style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;">
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <a href="{{ route('audit-findings.index', ['month' => $month, 'year' => $year]) }}" class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-[15px] font-semibold text-navy-900">{{ $indicator->indicator_code }} — {{ $indicator->title }}</h1>
                <p class="text-[11px] text-slate-500">
                    {{ $indicator->category ?: '—' }}
                    @if ($indicator->sub_category)
                        · {{ $indicator->sub_category }}
                    @endif
                    · {{ $indicator->risk_rating ?: '—' }}
                    · {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}
                </p>
            </div>
        </div>

        @if ($orgRow)
            <div class="mb-3 grid gap-2 sm:grid-cols-4">
                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                    <p class="text-[10px] font-semibold uppercase text-slate-400">Total amount</p>
                    <p class="text-[14px] font-bold tabular-nums text-navy-900">{{ number_format($orgRow->total_amount, 2) }}</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                    <p class="text-[10px] font-semibold uppercase text-slate-400">Samples</p>
                    <p class="text-[14px] font-bold tabular-nums text-navy-900">{{ $orgRow->total_samples_checked }}</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                    <p class="text-[10px] font-semibold uppercase text-slate-400">Irregularities</p>
                    <p class="text-[14px] font-bold tabular-nums text-rose-700">{{ $orgRow->total_irregularities }}</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                    <p class="text-[10px] font-semibold uppercase text-slate-400">Objected branches</p>
                    <p class="text-[14px] font-bold tabular-nums text-navy-900">{{ $orgRow->objected_branch_count }}</p>
                </div>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-3 py-2">
                <p class="text-[12px] font-semibold text-navy-900">Branch blocks (Excel X-axis)</p>
                <p class="text-[10px] text-slate-500">Only branches with stored finding cells · {{ $branches->count() }} rows</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="border-b border-slate-200 px-3 py-2">Branch</th>
                            <th class="border-b border-slate-200 px-3 py-2">Area</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Amount</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Samples</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Irregularities</th>
                            <th class="border-b border-slate-200 px-3 py-2">Observation</th>
                            <th class="border-b border-slate-200 px-3 py-2">Staff</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branches as $finding)
                            <tr class="border-b border-slate-50">
                                <td class="px-3 py-2 font-medium text-navy-900">
                                    {{ $finding->shakha?->name ?? '—' }}
                                    <span class="mt-0.5 block font-mono text-[10px] text-slate-400">{{ $finding->shakha?->code }}</span>
                                </td>
                                <td class="px-3 py-2 text-[11px] text-slate-600">{{ $finding->shakha?->area?->name ?? '—' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ $finding->amount !== null ? number_format((float) $finding->amount, 2) : '—' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ $finding->sample_size_checked ?? '—' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums font-semibold text-rose-700">{{ $finding->irregularity_count ?? '—' }}</td>
                                <td class="max-w-[280px] px-3 py-2 text-[11px] text-slate-600">{{ $finding->observation ?: '—' }}</td>
                                <td class="px-3 py-2 text-[11px] text-slate-600">{{ $finding->responsible_staff_name ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-10 text-center text-[12px] text-slate-400">No branch findings for this indicator in the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
