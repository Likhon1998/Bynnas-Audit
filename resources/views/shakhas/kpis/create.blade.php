<x-app-layout>
    @php
        $val = function (string $key, $default = 0) use ($existing) {
            return old($key, $existing?->{$key} ?? $default);
        };
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
    @endphp

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="{{ route('shakhas.index') }}" class="hover:text-brand-600">All Shakha</a>
                    <span>/</span>
                    <span class="text-slate-600">Monthly KPI Input</span>
                </div>
                <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">{{ $shakha->name }}</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    {{ $shakha->area?->name }} · {{ $shakha->area?->division }}
                    @if ($shakha->code)
                        · Code {{ $shakha->code }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('shakhas.index') }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Back</a>
                @if ($existing)
                    <a href="{{ route('shakhas.kpis.show', [$shakha, $month, $year]) }}" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">View report</a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="mb-4 rounded-xl border border-sky-100 bg-sky-50/80 px-4 py-3 text-[12px] text-sky-900">
            <p class="font-semibold">Raw monthly figures only</p>
            <p class="mt-0.5 text-sky-800/80">Enter snapshot balances and this month’s activity. Ratios (OTR, PAR, dropout %, etc.) are calculated automatically on the report — they are never stored.</p>
        </div>

        <form method="POST" action="{{ route('shakhas.kpis.store', $shakha) }}" class="space-y-4">
            @csrf

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Reporting period</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">One record per shakha / month / year (updates if already saved).</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-medium text-slate-600">Month <span class="text-rose-500">*</span></label>
                        <select name="report_month" required class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            @foreach ($months as $num => $label)
                                <option value="{{ $num }}" @selected((int) old('report_month', $month) === $num)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-medium text-slate-600">Year <span class="text-rose-500">*</span></label>
                        <input type="number" name="report_year" min="2000" max="2100" required value="{{ old('report_year', $year) }}" class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Snapshot balances</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Month-end stock figures (as of the last day of the month).</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        'total_samities' => 'Total Samities',
                        'total_members' => 'Total Members',
                        'total_borrowers' => 'Total Borrowers',
                        'total_od_borrowers' => 'Total OD Borrowers',
                        'field_officer_count' => 'Field Officer Count',
                        'savings_balance' => 'Savings Balance (Tk)',
                        'loan_outstanding' => 'Loan Outstanding (Tk)',
                        'total_od_taka' => 'Total OD Taka',
                        'due_loanee_loan_outstanding' => 'Due Loanee Loan Outstanding (Tk)',
                    ] as $name => $label)
                        <div>
                            <label class="mb-1.5 block text-[11px] font-medium text-slate-600">{{ $label }} <span class="text-rose-500">*</span></label>
                            <input
                                type="number"
                                name="{{ $name }}"
                                min="0"
                                step="{{ str_contains($name, 'taka') || str_contains($name, 'balance') || str_contains($name, 'outstanding') || str_contains($name, 'amount') || str_contains($name, 'recovery') || str_contains($name, 'recoverable') || str_contains($name, 'collection') || str_contains($name, 'withdrawal') || str_contains($name, 'disbursement') ? '0.01' : '1' }}"
                                required
                                value="{{ $val($name) }}"
                                class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            >
                            <x-input-error :messages="$errors->get($name)" class="mt-1" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Monthly activity</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Flow figures for this calendar month only (used for FY cumulatives).</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        'monthly_members_admitted' => 'Members Admitted',
                        'monthly_members_dropout' => 'Members Dropout',
                        'monthly_savings_collection' => 'Savings Collection (Tk)',
                        'monthly_savings_withdrawal' => 'Savings Withdrawal (Tk)',
                        'monthly_disbursement_amount' => 'Disbursement Amount (Tk)',
                        'monthly_loan_recovery' => 'Loan Recovery (Tk)',
                        'monthly_current_recovery' => 'Current Recovery (Tk)',
                        'monthly_recoverable' => 'Recoverable (Tk)',
                    ] as $name => $label)
                        <div>
                            <label class="mb-1.5 block text-[11px] font-medium text-slate-600">{{ $label }} <span class="text-rose-500">*</span></label>
                            <input
                                type="number"
                                name="{{ $name }}"
                                min="0"
                                step="{{ str_starts_with($name, 'monthly_members') ? '1' : '0.01' }}"
                                required
                                value="{{ $val($name) }}"
                                class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            >
                            <x-input-error :messages="$errors->get($name)" class="mt-1" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-card">
                <p class="text-[11px] text-slate-500">Saving will upsert this period and open the calculated KPI report.</p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('shakhas.index') }}" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-1.5 text-[12px] font-semibold text-white hover:bg-emerald-500">
                        Save KPI data
                    </button>
                </div>
            </div>
        </form>

        @if ($periods->isNotEmpty())
            <div class="mt-6 rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Previously saved periods</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-[12px]">
                        <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-4 py-2.5">Period</th>
                                <th class="px-4 py-2.5">Members</th>
                                <th class="px-4 py-2.5">Loan OS</th>
                                <th class="px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($periods as $period)
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-navy-900">{{ $period->periodLabel() }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ number_format($period->total_members) }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ number_format((float) $period->loan_outstanding, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <a href="{{ route('shakhas.kpis.create', ['shakha' => $shakha, 'month' => $period->report_month, 'year' => $period->report_year]) }}" class="text-slate-500 hover:text-brand-600">Edit</a>
                                        <span class="mx-1 text-slate-300">·</span>
                                        <a href="{{ route('shakhas.kpis.show', [$shakha, $period->report_month, $period->report_year]) }}" class="font-medium text-brand-600 hover:underline">Report</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
