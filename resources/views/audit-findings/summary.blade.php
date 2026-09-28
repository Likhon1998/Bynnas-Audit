@php
    $btn = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3 text-[12px] font-semibold text-white transition hover:-translate-y-0.5';
    $cards = [
        ['label' => 'Indicators with findings', 'value' => number_format($totals['indicators']), 'tone' => 'from-[#1b3a70] to-[#2b579a]'],
        ['label' => 'Branches', 'value' => number_format($totals['branches']), 'tone' => 'from-amber-400 to-orange-500'],
        ['label' => 'Irregularities', 'value' => number_format($totals['irregularities']), 'tone' => 'from-rose-400 to-rose-600'],
        ['label' => 'Amount involved', 'value' => number_format((float) $totals['amount'], 2), 'tone' => 'from-emerald-400 to-emerald-600'],
    ];
@endphp
<x-app-layout>
    <div class="space-y-3 px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <x-findings-header
            title="Findings Summary"
            :subtitle="'Report findings by heading, with branches and accused staff · '.$periodLabel"
            active-tab="summary"
            :month="$month"
            :year="$year"
            :month-strip="$monthStrip"
            :prev-year-url="$prevYearUrl"
            :next-year-url="$nextYearUrl"
        >
            <a href="{{ $exportUrl }}" class="{{ $btn }} bg-emerald-600 shadow-[0_6px_14px_rgba(5,150,105,0.3)] hover:bg-emerald-700">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 3v10m0 0-4-4m4 4 4-4M4 16h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Excel
            </a>
            @canany(['findings.summary.export_ppt', 'findings.view_all'])
                <a href="{{ $exportPptUrl }}" class="{{ $btn }} bg-[#c43e1c] shadow-[0_6px_14px_rgba(196,62,28,0.3)] hover:bg-[#a83316]">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 3v10m0 0-4-4m4 4 4-4M4 16h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Download PPT
                </a>
            @endcanany
        </x-findings-header>

        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
            @foreach ($cards as $card)
                <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <span class="h-8 w-1.5 shrink-0 rounded-full bg-gradient-to-b {{ $card['tone'] }}"></span>
                    <div class="min-w-0">
                        <p class="truncate text-[10.5px] font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-0.5 truncate text-[19px] font-bold leading-none tabular-nums text-navy-900">{{ $card['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="max-h-[calc(100vh-15rem)] overflow-auto">
                <table class="min-w-full border-separate border-spacing-0 text-left text-[12px]">
                    <thead>
                        <tr class="text-xs font-semibold uppercase tracking-wide text-slate-200">
                            <th class="sticky top-0 z-20 whitespace-nowrap border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5">#</th>
                            <th class="sticky top-0 z-20 min-w-[140px] border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5">Heading</th>
                            <th class="sticky top-0 z-20 whitespace-nowrap border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5">Code</th>
                            <th class="sticky top-0 z-20 min-w-[220px] border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5">Indicator</th>
                            <th class="sticky top-0 z-20 whitespace-nowrap border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5 text-right">Amount</th>
                            <th class="sticky top-0 z-20 whitespace-nowrap border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5 text-right">Sample</th>
                            <th class="sticky top-0 z-20 whitespace-nowrap border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5 text-right">Irreg.</th>
                            <th class="sticky top-0 z-20 whitespace-nowrap border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5 text-right">%</th>
                            <th class="sticky top-0 z-20 whitespace-nowrap border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5 text-right">Branches</th>
                            <th class="sticky top-0 z-20 min-w-[190px] border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5">Shakha</th>
                            <th class="sticky top-0 z-20 min-w-[180px] border-b border-slate-700 bg-[#0B1F36] px-3 py-2.5">অভিযুক্ত কর্মী</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($flatRows as $index => $row)
                            @php
                                $rate = (float) ($row['percentage'] ?? 0);
                                $rateClass = $rate >= 20 ? 'bg-rose-50 text-rose-700' : ($rate >= 10 ? 'bg-amber-50 text-amber-700' : 'bg-slate-50 text-slate-600');
                                $branchRows = array_values((array) ($row['branch_rows'] ?? []));
                                if ($branchRows === []) {
                                    $branchRows = [['label' => '—', 'accused_kormi' => '']];
                                }
                                $rowSpan = max(1, count($branchRows));
                                $rowBg = $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/60';
                            @endphp
                            @foreach ($branchRows as $bIndex => $branch)
                                <tr class="{{ $rowBg }} hover:bg-sky-50/40">
                                    @if ($bIndex === 0)
                                        <td class="px-3 py-2.5 align-top tabular-nums text-slate-500" rowspan="{{ $rowSpan }}">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2.5 align-top" rowspan="{{ $rowSpan }}">
                                            <p class="font-medium text-slate-800">{{ $row['category'] }}</p>
                                            @if (($row['sub_category'] ?? '') !== '' && ($row['sub_category'] ?? '') !== '—')
                                                <p class="text-xs text-slate-500">{{ $row['sub_category'] }}</p>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 align-top whitespace-nowrap font-mono text-[13px] text-sky-700" rowspan="{{ $rowSpan }}">{{ $row['code'] }}</td>
                                        <td class="px-3 py-2.5 align-top" rowspan="{{ $rowSpan }}">
                                            <a href="{{ $row['url'] }}" class="font-semibold text-[#2b579a] hover:underline">{{ $row['title'] }}</a>
                                        </td>
                                        <td class="px-3 py-2.5 text-right align-top tabular-nums whitespace-nowrap" rowspan="{{ $rowSpan }}">{{ $row['amount_fmt'] }}</td>
                                        <td class="px-3 py-2.5 text-right align-top tabular-nums" rowspan="{{ $rowSpan }}">{{ number_format($row['samples']) }}</td>
                                        <td class="px-3 py-2.5 text-right align-top text-[13px] font-semibold tabular-nums text-rose-700" rowspan="{{ $rowSpan }}">{{ number_format($row['irregularities']) }}</td>
                                        <td class="px-3 py-2.5 text-right align-top" rowspan="{{ $rowSpan }}">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $rateClass }}">{{ $row['percentage_fmt'] }}</span>
                                        </td>
                                        <td class="px-3 py-2.5 text-right align-top font-semibold tabular-nums" rowspan="{{ $rowSpan }}">{{ number_format($row['branch_count']) }}</td>
                                    @endif
                                    <td class="whitespace-nowrap border-t border-slate-100 px-3 py-2.5 align-top text-[13px] text-slate-700">
                                        {{ $branch['label'] ?? '—' }}
                                    </td>
                                    <td class="border-t border-slate-100 px-3 py-2.5 align-top text-[13px] font-medium text-violet-900">
                                        @php
                                            $people = array_values((array) ($branch['accused_people'] ?? []));
                                        @endphp
                                        @if ($people === [])
                                            {{ filled($branch['accused_kormi'] ?? null) ? $branch['accused_kormi'] : '—' }}
                                        @else
                                            <div class="flex flex-col gap-1">
                                                @foreach ($people as $person)
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        @if (! empty($person['dossier_url']))
                                                            <a href="{{ $person['dossier_url'] }}" class="hover:underline" title="সব বছরের আর্থিক রিপোর্ট দেখুন">{{ $person['label'] }}</a>
                                                        @else
                                                            <span>{{ $person['label'] }}</span>
                                                        @endif
                                                        @if ((int) ($person['report_count'] ?? 0) >= 2)
                                                            <span
                                                                class="inline-flex rounded-full bg-rose-100 px-1.5 py-0.5 text-xs font-bold text-rose-800"
                                                                title="{{ (int) $person['report_count'] }} বার আর্থিক রিপোর্ট (সব বছর)"
                                                            >{{ (int) $person['report_count'] }} বার</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="11" class="px-3 py-12 text-center text-[13px] text-slate-500">
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
