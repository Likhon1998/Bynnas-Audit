<x-app-layout>
    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="{{ route('shakha-employees.index') }}" class="hover:text-brand-600">Shakha Employees</a>
                    <span>/</span>
                    @if ($employee->shakha)
                        <a href="{{ route('shakha-employees.manage', $employee->shakha) }}" class="hover:text-brand-600">{{ $employee->shakha->name }}</a>
                        <span>/</span>
                    @endif
                    <span class="text-slate-600">Dossier</span>
                </div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">কর্মী ডসিয়ার</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Fixed employee ID — financial reports across all months/years</p>
            </div>
            <a href="{{ route('shakha-employees.edit', $employee) }}" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Edit employee</a>
        </div>

        <section class="mb-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center gap-4 px-4 py-4">
                @include('shakha-employees.partials.photo', ['employee' => $employee, 'size' => 'lg'])
                <div class="min-w-0 flex-1">
                    <p class="text-[18px] font-semibold text-navy-900">{{ $employee->name }}</p>
                    <p class="mt-0.5 font-mono text-[13px] text-sky-800">ID: {{ $employee->employee_code }}</p>
                    <p class="mt-1 text-[12px] text-slate-500">
                        {{ $employee->shakha?->name ?: '—' }}
                        @if ($employee->shakha?->code)
                            ({{ $employee->shakha->code }})
                        @endif
                        · {{ $employee->statusLabel() }}
                        · {{ $employee->designation }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div class="rounded-xl bg-rose-50 px-3 py-2 text-center">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-700">আর্থিক রিপোর্ট</p>
                        <p class="text-[22px] font-bold tabular-nums text-rose-800">{{ $reportCount }}</p>
                    </div>
                    <div class="rounded-xl bg-sky-50 px-3 py-2 text-center">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700">শাখায়</p>
                        <p class="text-[22px] font-bold tabular-nums text-sky-900">{{ $shakhaCount }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 px-3 py-2 text-center">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-800">স্থানান্তর</p>
                        <p class="text-[22px] font-bold tabular-nums text-amber-900">{{ $transferCount }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-3 py-2 text-center">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Finding cells</p>
                        <p class="text-[22px] font-bold tabular-nums text-slate-700">{{ $findingCount }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2.5">
                <p class="text-[13px] font-semibold text-navy-900">আর্থিক রিপোর্টের তালিকা</p>
                <p class="text-[11px] text-slate-500">সব সময় · transfer করেও পুরনো রেকর্ড থাকে</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-4 py-2.5">#</th>
                            <th class="px-4 py-2.5">মাস/বছর</th>
                            <th class="px-4 py-2.5">শাখা (রিপোর্ট)</th>
                            <th class="px-4 py-2.5">Indicator</th>
                            <th class="px-4 py-2.5 text-right">Amount</th>
                            <th class="px-4 py-2.5">পর্যবেক্ষণ</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($findings as $index => $finding)
                            @php
                                $visitKey = $finding->audit_year.'-'.$finding->audit_month.'-'.$finding->shakha_id;
                                $reportUrl = $reportLinks[$visitKey] ?? null;
                                $monthLabel = date('M Y', mktime(0, 0, 0, (int) $finding->audit_month, 1, (int) $finding->audit_year));
                            @endphp
                            <tr class="hover:bg-sky-50/40">
                                <td class="px-4 py-2.5 tabular-nums text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5 whitespace-nowrap font-medium text-slate-800">{{ $monthLabel }}</td>
                                <td class="px-4 py-2.5 text-slate-700">
                                    {{ $finding->shakha?->name ?: '—' }}
                                    @if ($finding->shakha?->code)
                                        <span class="text-slate-400">({{ $finding->shakha->code }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <p class="font-medium text-slate-800">{{ $finding->indicator?->title ?: '—' }}</p>
                                    <p class="font-mono text-[10px] text-sky-700">{{ $finding->indicator?->indicator_code }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">
                                    {{ $finding->amount !== null ? number_format((float) $finding->amount, 2) : '—' }}
                                </td>
                                <td class="max-w-[280px] px-4 py-2.5 text-slate-600">
                                    <p class="line-clamp-2">{{ $finding->observation ?: '—' }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    @if ($reportUrl)
                                        <a href="{{ $reportUrl }}" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Open report</a>
                                    @else
                                        <span class="text-[11px] text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400">No financial findings linked to this employee ID yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2.5">
                <p class="text-[13px] font-semibold text-navy-900">স্থানান্তর ইতিহাস</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-4 py-2.5">তারিখ</th>
                            <th class="px-4 py-2.5">From</th>
                            <th class="px-4 py-2.5">To</th>
                            <th class="px-4 py-2.5">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transfers as $transfer)
                            <tr>
                                <td class="px-4 py-2.5 whitespace-nowrap text-slate-700">{{ $transfer->transferred_at?->format('d M Y') }}</td>
                                <td class="px-4 py-2.5">{{ $transfer->fromShakha?->name ?: '—' }}</td>
                                <td class="px-4 py-2.5">{{ $transfer->toShakha?->name ?: '—' }}</td>
                                <td class="px-4 py-2.5 text-slate-500">{{ $transfer->note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-400">No transfers recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
