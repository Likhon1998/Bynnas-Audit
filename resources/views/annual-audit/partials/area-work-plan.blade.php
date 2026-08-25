@php
    $fy = $plan->fy_label;
    $fyParts = explode('-', $fy);
    $startYear = substr($fyParts[0] ?? '2026', -2);
    $endYear = substr($fyParts[1] ?? '2027', -2);
    $initialMonthTotals = array_values($areaTotals['by_month'] ?? array_fill(0, 12, 0));
@endphp

<div
    x-data="{
        division: '',
        monthTotals: @js($initialMonthTotals),
        rowMeta: @js($rows->map(fn ($r) => ['division' => $r['division'] ?? ''])->values()),
        matches(rowDivision) {
            if (this.division && rowDivision !== this.division) return false;
            return true;
        },
        get visibleCount() {
            return this.rowMeta.filter((r) => this.matches(r.division)).length;
        },
        get grandTotal() {
            return this.monthTotals.reduce((a, b) => a + b, 0);
        },
        onTick(e) {
            const i = e.detail.month_index;
            if (i === undefined || i === null) return;
            this.monthTotals[i] = Math.max(0, (this.monthTotals[i] || 0) + e.detail.delta);
            this.monthTotals = [...this.monthTotals];
        },
    }"
    @audit-tick.window="onTick($event)"
>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 border-b border-slate-200 bg-slate-50/80 px-3 py-1.5">
        <p class="mr-auto text-[12px] font-semibold text-navy-900">
            Area Office Work Plan
            <span class="ml-1.5 font-normal text-slate-400">FY {{ $fy }}</span>
        </p>
        <select x-model="division" class="h-7 rounded-md border-slate-200 py-0 text-[11px]" title="Division">
            <option value="">All divisions</option>
            @foreach ($divisions as $divisionOption)
                <option value="{{ $divisionOption }}">{{ $divisionOption }}</option>
            @endforeach
        </select>
        <span class="text-[11px] text-slate-400" x-show="visibleCount > 0"><span x-text="visibleCount" class="font-medium text-slate-600"></span> areas</span>
        <a
            href="{{ route('annual-audit.export', ['mode' => 'area', 'fy' => $plan->fy_label]) }}"
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
                <tr class="bg-amber-200/90 text-[10px] font-semibold tracking-wide text-slate-800">
                    <th rowspan="2" class="border border-slate-300 px-1.5 py-1 text-center w-8">#</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-1 min-w-[160px] text-center">Area Name</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center">1st Quarter</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center">2nd Quarter</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center">3rd Quarter</th>
                    <th colspan="3" class="border border-slate-300 px-1 py-1 text-center">4th Quarter</th>
                    <th rowspan="2" class="border border-slate-300 px-1.5 py-1 text-center w-12">Total</th>
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
                        @endphp
                        <th class="border border-slate-300 px-0.5 py-1 text-center min-w-[42px]">
                            <div class="text-[10px] font-bold leading-none text-navy-900" x-text="monthTotals[{{ $monthIndex }}] ?? 0">{{ $initialMonthTotals[$monthIndex] ?? 0 }}</div>
                            <div class="mt-0.5 text-[8px] font-semibold uppercase leading-none text-slate-500">{{ $monthName }}-{{ $shortYear }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr
                        class="text-[12px]"
                        x-show="matches(@js($row['division'] ?? ''))"
                        @audit-tick="
                            const cell = $el.querySelector('[data-row-total]');
                            if (cell) cell.textContent = Number(cell.textContent || 0) + Number($event.detail.delta || 0);
                        "
                    >
                        <td class="border border-slate-300 px-1.5 py-0.5 text-center text-[11px] text-slate-500">{{ $row['sl'] }}</td>
                        <td class="border border-slate-300 px-2 py-0.5 font-medium text-navy-900">
                            {{ $row['name'] }}
                            <span class="ml-1 text-[9px] font-normal text-slate-400">{{ $row['division'] }}</span>
                        </td>
                        @foreach ($row['months'] as $monthIndex => $active)
                            <td class="border border-slate-300 px-0 py-0 text-center {{ $active ? 'bg-emerald-100' : 'bg-white' }}">
                                <x-audit-month-mark
                                    :active="(bool) $active"
                                    :manual="(bool) ($row['manual'][$monthIndex] ?? false)"
                                    :editable="$canEditSchedule"
                                    :category="$row['category']"
                                    :schedulable-type="$row['schedulable_type']"
                                    :schedulable-id="$row['id']"
                                    :month-index="$monthIndex"
                                    tab="area"
                                    :fy="$plan->fy_label"
                                />
                            </td>
                        @endforeach
                        <td class="border border-slate-300 px-1.5 py-0.5 text-center text-[12px] font-semibold text-navy-900" data-row-total>{{ $row['total'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="border border-slate-300 px-4 py-10 text-center text-[12px] text-slate-400">
                            No area schedules yet. Set frequency in Policies, then Generate Annual Plan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-slate-50 text-[11px] font-semibold text-navy-900">
                        <td class="border border-slate-300 px-1.5 py-1"></td>
                        <td class="border border-slate-300 px-2 py-1">Monthly total</td>
                        @foreach ($months as $monthIndex => $month)
                            <td class="border border-slate-300 px-1 py-1 text-center" x-text="monthTotals[{{ $monthIndex }}] ?? 0">{{ $initialMonthTotals[$monthIndex] ?? 0 }}</td>
                        @endforeach
                        <td class="border border-slate-300 px-1.5 py-1 text-center" x-text="grandTotal">{{ $areaTotals['grand'] ?? 0 }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
