<x-app-layout>
    <div class="px-3 py-3 lg:px-5">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Findings Summary</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">
                    {{ $periodLabel }} · Only indicators used in audit reports for this month
                </p>
                @include('audit-findings.partials.view-tabs', ['activeTab' => 'summary', 'month' => $month, 'year' => $year])
            </div>
            <div class="ml-auto flex flex-wrap items-center justify-end gap-1.5">
                <form method="GET" action="{{ route('audit-findings.summary') }}" class="flex flex-wrap items-center gap-1.5">
                    <select name="month" class="h-8 rounded-md border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m === $month)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                    <select name="year" class="h-8 rounded-md border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                        @foreach ($yearOptions as $y)
                            <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
                <a
                    href="{{ $exportUrl }}"
                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-md bg-emerald-700 px-3.5 text-[12px] font-semibold text-white shadow-sm hover:bg-emerald-800"
                    title="Download Excel workbook"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    Excel
                </a>
                <a
                    href="{{ $exportPptUrl }}"
                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-md bg-[#c43e1c] px-3.5 text-[12px] font-semibold text-white shadow-sm hover:bg-[#a83316]"
                    title="Download DSK presentation (PowerPoint)"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4zM8 6v12M4 10h16"/></svg>
                    Download PPT
                </a>
            </div>
        </div>

        <div class="mb-3 grid grid-cols-4 gap-1 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm sm:grid-cols-6 lg:grid-cols-12">
            @foreach ($monthStrip as $chip)
                <a
                    href="{{ $chip['url'] }}"
                    class="rounded-md px-1 py-1.5 text-center text-[11px] font-semibold transition
                        {{ $chip['active'] ? 'bg-navy-900 text-white' : ($chip['has_data'] ? 'bg-sky-50 text-sky-900 hover:bg-sky-100' : 'text-slate-400 hover:bg-slate-50') }}"
                >{{ $chip['label'] }}</a>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-3 py-2">
                <p class="text-[13px] font-semibold text-slate-800">{{ $periodLabel }} — report findings</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-[12px] text-slate-800">
                    <thead>
                        <tr class="bg-slate-100 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                            <th class="border border-slate-200 px-3 py-2 whitespace-nowrap">Heading</th>
                            <th class="border border-slate-200 px-3 py-2 whitespace-nowrap">Sub-heading</th>
                            <th class="border border-slate-200 px-3 py-2 whitespace-nowrap">Code</th>
                            <th class="border border-slate-200 px-3 py-2 min-w-[240px]">Indicator</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Amount</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Sample size</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Irregularities</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Irregularity %</th>
                            <th class="border border-slate-200 px-3 py-2 text-right whitespace-nowrap">Total branch</th>
                            <th class="border border-slate-200 px-3 py-2 min-w-[200px]">Branch name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $flatRows = collect($underheadingGroups)->flatMap(function ($group) {
                                return collect($group['rows'])->map(fn ($row) => array_merge($row, [
                                    'category' => $group['category'],
                                    'sub_category' => $group['sub_category'],
                                ]));
                            });
                        @endphp
                        @forelse ($flatRows as $index => $row)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/80' }}">
                                <td class="border border-slate-200 px-3 py-2 align-top text-slate-700">{{ $row['category'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 align-top text-slate-700">{{ $row['sub_category'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 align-top font-mono text-[11px] text-slate-600 whitespace-nowrap">{{ $row['code'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 align-top">
                                    <a href="{{ $row['url'] }}" class="font-medium text-slate-800 hover:underline">{{ $row['title'] }}</a>
                                </td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums whitespace-nowrap">{{ $row['amount_fmt'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums">{{ number_format($row['samples']) }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums font-semibold">{{ number_format($row['irregularities']) }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums whitespace-nowrap">{{ $row['percentage_fmt'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right align-top tabular-nums">{{ number_format($row['branch_count']) }}</td>
                                <td class="border border-slate-200 px-3 py-2 align-top text-[11px] leading-snug text-slate-700">
                                    {{ $row['branches'] !== '' ? $row['branches'] : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="border border-slate-200 px-3 py-10 text-center text-[12px] text-slate-400">
                                    No report findings for {{ $periodLabel }}. Sync or complete an audit report first.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
