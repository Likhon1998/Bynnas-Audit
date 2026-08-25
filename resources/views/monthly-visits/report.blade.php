<x-app-layout>
    <div class="px-4 py-5 lg:px-6 print:px-0">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-2 print:hidden">
            <div>
                <a href="{{ route('monthly-visits.index', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="text-[11px] font-medium text-brand-600 hover:underline">← Monthly visits</a>
                <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">
                    @if ($type === 'performance')
                        Monthly Performance Report
                    @elseif ($type === 'workload')
                        Staff Workload
                    @elseif ($type === 'projects')
                        Project Audit &amp; Monitoring Visit Plan
                    @else
                        Field Visit &amp; Inspection Monthly Schedule
                    @endif
                </h1>
                <p class="mt-0.5 text-[11px] text-slate-500">FY {{ $plan->fy_label }} · {{ $monthLabel }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'schedule']) }}" class="rounded-lg border px-2 py-1 text-[11px] {{ $type === 'schedule' ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200' }}">Schedule</a>
                <a href="{{ route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'projects']) }}" class="rounded-lg border px-2 py-1 text-[11px] {{ $type === 'projects' ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200' }}">Projects</a>
                <a href="{{ route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'performance']) }}" class="rounded-lg border px-2 py-1 text-[11px] {{ $type === 'performance' ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200' }}">Performance</a>
                <a href="{{ route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'workload']) }}" class="rounded-lg border px-2 py-1 text-[11px] {{ $type === 'workload' ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200' }}">Workload</a>
                <button type="button" onclick="window.print()" class="rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-800">Print / PDF</button>
            </div>
        </div>

        <div class="hidden print:mb-4 print:block">
            <p class="text-[16px] font-semibold text-navy-900">Field Visit &amp; Inspection — {{ $monthLabel }}</p>
            <p class="text-[12px] text-slate-600">Financial Year {{ $plan->fy_label }}</p>
        </div>

        @if (in_array($type, ['schedule', 'projects'], true))
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card print:shadow-none print:border-slate-300">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2">SL</th>
                                <th class="px-3 py-2">Visitor</th>
                                <th class="px-3 py-2">Last Audit Upto</th>
                                <th class="px-3 py-2">{{ $type === 'projects' ? 'Project / Location' : 'Shakha / Project / Entity' }}</th>
                                <th class="px-3 py-2">Visit Date</th>
                                <th class="px-3 py-2">Days</th>
                                <th class="px-3 py-2">Purpose</th>
                                <th class="px-3 py-2">Remarks</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($assigned as $i => $item)
                                @php $a = $item->assignment; @endphp
                                <tr class="text-[12px]">
                                    <td class="px-3 py-2 text-slate-500">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-3 py-2 font-medium text-navy-900 whitespace-pre-line">{{ $a?->visitorNames("\n") }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ $a?->last_audit_upto?->format('F-Y') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-slate-700">
                                        {{ $item->entity_label }}
                                        @if ($item->isSpecial())
                                            <span class="text-[10px] text-amber-700">(Additional / Special)</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-slate-600">{{ $a?->visitDateRangeLabel() }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ $a?->duration_days ? str_pad((string) $a->duration_days, 2, '0', STR_PAD_LEFT).' days' : '—' }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ $a?->purpose }}</td>
                                    <td class="px-3 py-2 text-slate-500">{{ $a?->remarks ?: '—' }}</td>
                                    <td class="px-3 py-2 capitalize text-slate-600">{{ str_replace('_', ' ', $a?->execution?->status ?? 'planned') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-4 py-8 text-center text-[12px] text-slate-400">No assigned visits for this report.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif ($type === 'performance')
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">Category</th>
                            <th class="px-3 py-2 text-right">Planned</th>
                            <th class="px-3 py-2 text-right">Assigned</th>
                            <th class="px-3 py-2 text-right">Completed</th>
                            <th class="px-3 py-2 text-right">Unassigned</th>
                            <th class="px-3 py-2 text-right">Cancelled</th>
                            <th class="px-3 py-2 text-right">Overdue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($performance['byCategory'] as $category => $row)
                            <tr class="text-[12px]">
                                <td class="px-3 py-2 font-medium capitalize text-navy-900">{{ str_replace('_', ' ', $category) }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['planned'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['assigned'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['completed'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['pending'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['cancelled'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['overdue'] }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-slate-50 text-[12px] font-semibold">
                            <td class="px-3 py-2">TOTAL</td>
                            <td class="px-3 py-2 text-right">{{ $performance['totals']['planned'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $performance['totals']['assigned'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $performance['totals']['completed'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $performance['totals']['pending'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $performance['totals']['cancelled'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $performance['totals']['overdue'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">Person</th>
                            <th class="px-3 py-2">Designation</th>
                            <th class="px-3 py-2 text-right">Activities</th>
                            <th class="px-3 py-2 text-right">Total Days</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($workload as $row)
                            <tr class="text-[12px]">
                                <td class="px-3 py-2 font-medium text-navy-900">{{ $row['employee']?->name }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $row['employee']?->position?->title ?? '—' }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['activities'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['total_days'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-[12px] text-slate-400">No assignments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
