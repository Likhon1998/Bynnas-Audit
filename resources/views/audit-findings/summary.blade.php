<x-app-layout>
    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        {{-- Header --}}
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Findings Summary</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">{{ $periodLabel }}</p>
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
                <a href="{{ $exportUrl }}" class="inline-flex h-9 items-center gap-1.5 rounded-md bg-emerald-700 px-3 text-[12px] font-semibold text-white hover:bg-emerald-800">Excel</a>
                <a href="{{ $exportPptUrl }}" class="inline-flex h-9 items-center gap-1.5 rounded-md bg-[#c43e1c] px-3 text-[12px] font-semibold text-white hover:bg-[#a83316]">Download PPT</a>
            </div>
        </div>

        {{-- Month strip --}}
        <div class="mb-3 grid grid-cols-4 gap-1 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm sm:grid-cols-6 lg:grid-cols-12">
            @foreach ($monthStrip as $chip)
                <a
                    href="{{ $chip['url'] }}"
                    class="rounded-md px-1 py-1.5 text-center text-[11px] font-semibold transition
                        {{ $chip['active'] ? 'bg-navy-900 text-white' : ($chip['has_data'] ? 'bg-sky-50 text-sky-900 hover:bg-sky-100' : 'text-slate-400 hover:bg-slate-50') }}"
                >{{ $chip['label'] }}</a>
            @endforeach
        </div>

        {{-- Indicator table only --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#0B1F36] text-[10px] font-semibold uppercase tracking-wide text-slate-200">
                            <th class="px-3 py-2.5">#</th>
                            <th class="px-3 py-2.5">Heading</th>
                            <th class="px-3 py-2.5">Code</th>
                            <th class="px-3 py-2.5 min-w-[220px]">Indicator</th>
                            <th class="px-3 py-2.5 text-right">Amount</th>
                            <th class="px-3 py-2.5 text-right">Sample</th>
                            <th class="px-3 py-2.5 text-right">Irreg.</th>
                            <th class="px-3 py-2.5 text-right">%</th>
                            <th class="px-3 py-2.5 text-right">Branches</th>
                            <th class="px-3 py-2.5 min-w-[180px]">Branch names</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($flatRows as $index => $row)
                            @php
                                $rate = (float) ($row['percentage'] ?? 0);
                                $rateClass = $rate >= 20 ? 'bg-rose-50 text-rose-700' : ($rate >= 10 ? 'bg-amber-50 text-amber-700' : 'bg-slate-50 text-slate-600');
                            @endphp
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/60' }} hover:bg-sky-50/40">
                                <td class="px-3 py-2.5 tabular-nums text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-3 py-2.5 align-top">
                                    <p class="font-medium text-slate-800">{{ $row['category'] }}</p>
                                    @if (($row['sub_category'] ?? '') !== '' && ($row['sub_category'] ?? '') !== '—')
                                        <p class="text-[10px] text-slate-400">{{ $row['sub_category'] }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 align-top whitespace-nowrap font-mono text-[11px] text-sky-700">{{ $row['code'] }}</td>
                                <td class="px-3 py-2.5 align-top">
                                    <a href="{{ $row['url'] }}" class="font-semibold text-[#2b579a] hover:underline">{{ $row['title'] }}</a>
                                </td>
                                <td class="px-3 py-2.5 text-right align-top tabular-nums whitespace-nowrap">{{ $row['amount_fmt'] }}</td>
                                <td class="px-3 py-2.5 text-right align-top tabular-nums">{{ number_format($row['samples']) }}</td>
                                <td class="px-3 py-2.5 text-right align-top text-[13px] font-semibold tabular-nums text-rose-700">{{ number_format($row['irregularities']) }}</td>
                                <td class="px-3 py-2.5 text-right align-top">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $rateClass }}">{{ $row['percentage_fmt'] }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-right align-top font-semibold tabular-nums">{{ number_format($row['branch_count']) }}</td>
                                <td class="px-3 py-2.5 align-top text-[11px] leading-snug text-slate-600">{{ $row['branches'] !== '' ? $row['branches'] : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-3 py-12 text-center text-[12px] text-slate-400">
                                    No report findings for {{ $periodLabel }}. Complete an audit report first.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
