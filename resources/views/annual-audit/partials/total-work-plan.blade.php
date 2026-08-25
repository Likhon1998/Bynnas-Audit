@php
    $fy = $plan->fy_label;
    $fyParts = explode('-', $fy);
    $startYear = substr($fyParts[0] ?? '2026', -2);
    $endYear = substr($fyParts[1] ?? '2027', -2);

    $rowStyles = [
        'Shakha Audit' => [
            'bar' => 'bg-emerald-500',
            'label' => 'bg-emerald-50 text-emerald-900',
            'cell' => 'bg-emerald-100 text-emerald-900',
            'empty' => 'bg-emerald-50/30 text-emerald-200',
        ],
        'Area Office' => [
            'bar' => 'bg-amber-500',
            'label' => 'bg-amber-50 text-amber-900',
            'cell' => 'bg-amber-100 text-amber-900',
            'empty' => 'bg-amber-50/30 text-amber-200',
        ],
        'PKSF & Maternity' => [
            'bar' => 'bg-orange-500',
            'label' => 'bg-orange-50 text-orange-900',
            'cell' => 'bg-orange-100 text-orange-900',
            'empty' => 'bg-orange-50/30 text-orange-200',
        ],
        'HQ Concern' => [
            'bar' => 'bg-sky-500',
            'label' => 'bg-sky-50 text-sky-900',
            'cell' => 'bg-sky-100 text-sky-900',
            'empty' => 'bg-sky-50/30 text-sky-200',
        ],
        'Project Audit' => [
            'bar' => 'bg-teal-500',
            'label' => 'bg-teal-50 text-teal-900',
            'cell' => 'bg-teal-100 text-teal-900',
            'empty' => 'bg-teal-50/30 text-teal-200',
        ],
        'Project Monitoring' => [
            'bar' => 'bg-cyan-500',
            'label' => 'bg-cyan-50 text-cyan-900',
            'cell' => 'bg-cyan-100 text-cyan-900',
            'empty' => 'bg-cyan-50/30 text-cyan-200',
        ],
    ];

    $monthTotals = array_fill(0, 12, 0);
    $grandTotal = 0;
    foreach ($categoryTotals as $row) {
        foreach ($row['by_month'] as $i => $count) {
            $monthTotals[$i] += (int) $count;
        }
        $grandTotal += (int) $row['planned'];
    }
@endphp

<div>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 border-b border-slate-200 bg-slate-50/80 px-3 py-1.5">
        <p class="mr-auto text-[12px] font-semibold text-navy-900">
            Annual Total — Audit &amp; Monitoring
            <span class="ml-1.5 font-normal text-slate-400">FY {{ $fy }}</span>
        </p>
        <a
            href="{{ route('annual-audit.export', ['mode' => 'total', 'fy' => $plan->fy_label]) }}"
            class="inline-flex h-7 items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 text-[11px] font-medium text-emerald-800 hover:bg-emerald-100"
        >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export Excel
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left">
            <thead>
                <tr class="bg-sky-100 text-[10px] font-semibold tracking-wide text-navy-900">
                    <th rowspan="2" class="border border-slate-300 px-3 py-1.5 min-w-[150px]">Category</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center bg-emerald-200/80">1st Quarter</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center bg-amber-200/80">2nd Quarter</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center bg-sky-200/80">3rd Quarter</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center bg-teal-200/80">4th Quarter</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-1.5 text-center bg-sky-200 text-navy-900 min-w-[56px]">Total</th>
                </tr>
                <tr class="bg-slate-100 text-[9px] font-semibold tracking-wide text-slate-600">
                    @foreach ($months as $monthIndex => $month)
                        @php
                            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
                            $monthName = match ($month['month']) {
                                7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                                default => $month['label'],
                            };
                            $qBg = match (true) {
                                $monthIndex <= 2 => 'bg-emerald-50',
                                $monthIndex <= 5 => 'bg-amber-50',
                                $monthIndex <= 8 => 'bg-sky-50',
                                default => 'bg-teal-50',
                            };
                        @endphp
                        <th class="border border-slate-300 px-0.5 py-1 text-center min-w-[48px] {{ $qBg }}">
                            <div class="text-[11px] font-bold leading-none text-navy-900">{{ $monthTotals[$monthIndex] }}</div>
                            <div class="mt-0.5 text-[8px] font-semibold uppercase leading-none text-slate-500">{{ $monthName }}-{{ $shortYear }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($categoryTotals as $row)
                    @php $style = $rowStyles[$row['label']] ?? [
                        'bar' => 'bg-slate-400',
                        'label' => 'bg-slate-50 text-navy-900',
                        'cell' => 'bg-slate-100 text-navy-900',
                        'empty' => 'bg-white text-slate-300',
                    ]; @endphp
                    <tr class="text-[12px]">
                        <td class="border border-slate-300 px-0 py-0">
                            <div class="flex h-full min-h-[36px] items-center gap-2 {{ $style['label'] }} px-2.5">
                                <span class="h-5 w-1 shrink-0 rounded-full {{ $style['bar'] }}"></span>
                                <span class="font-semibold">{{ $row['label'] }}</span>
                            </div>
                        </td>
                        @foreach ($row['by_month'] as $count)
                            <td class="border border-slate-300 px-1 py-1.5 text-center font-semibold {{ $count ? $style['cell'] : $style['empty'] }}">
                                {{ $count ?: '—' }}
                            </td>
                        @endforeach
                        <td class="border border-slate-300 px-2 py-1.5 text-center font-bold text-navy-900 bg-slate-50">
                            {{ number_format($row['planned']) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gradient-to-r from-emerald-100 via-amber-50 to-sky-100 text-[12px] font-semibold text-navy-900">
                    <td class="border border-slate-300 px-3 py-2">Grand Total</td>
                    @foreach ($monthTotals as $total)
                        <td class="border border-slate-300 px-1 py-2 text-center {{ $total ? 'text-navy-900' : 'text-slate-400' }}">
                            {{ $total ?: '—' }}
                        </td>
                    @endforeach
                    <td class="border border-slate-300 px-2 py-2 text-center bg-sky-200 text-navy-900">{{ number_format($grandTotal) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
