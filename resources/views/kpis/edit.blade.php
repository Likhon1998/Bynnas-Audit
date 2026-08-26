<x-app-layout>
    @php
        $val = fn (string $key, $default = 0) => old($key, $existing?->{$key} ?? $default);
        $priorJune = 'June-'.$fy->startYear();
    @endphp

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="{{ route('kpis.index', ['fy' => $fyLabel]) }}" class="hover:text-brand-600">Annual KPI</a>
                    <span>/</span>
                    <span class="text-slate-600">{{ $existing ? 'Edit' : 'Enter' }}</span>
                </div>
                <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">{{ $shakha->name }}</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    {{ $shakha->area?->name }} · FY {{ $fyLabel }}
                    · Raw figures only (ratios calculated on Excel export)
                </p>
            </div>
            <a href="{{ route('kpis.index', ['fy' => $fyLabel]) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Back to list</a>
        </div>

        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('kpis.store', $shakha) }}" class="space-y-4">
            @csrf
            <input type="hidden" name="fy_label" value="{{ $fyLabel }}">

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Branch info</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-medium text-slate-600">Opening date</label>
                        <input type="date" name="opening_date" value="{{ old('opening_date', optional($shakha->opening_date ?? $shakha->opened_at)->format('Y-m-d')) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-[11px] font-medium text-slate-600">Focal person name</label>
                        <input type="text" name="focal_person_name" value="{{ old('focal_person_name', $shakha->focal_person_name) }}" class="block w-full rounded-lg border-slate-200 text-[13px]" placeholder="e.g. Md. Rafiqul Islam">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Snapshot</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Month-end / year-end stock figures</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        'fo_count' => 'FO #',
                        'total_samities' => 'Total Samities',
                        'total_members' => 'Total Members',
                        'total_borrowers' => 'Total Borrowers',
                        'total_od_borrowers' => 'Total OD Borrowers',
                        'savings_balance' => 'Savings Balance (Tk)',
                        'loan_outstanding' => 'Loan Outstanding (Tk)',
                        'recoverable' => 'Recoverable (Tk)',
                        'current_recovery' => 'Current Recovery (Tk)',
                        'due_recovery' => 'Due Recovery (Tk)',
                        'total_od_taka' => 'Total OD Taka',
                        'due_loanee_loan_outstanding' => 'Due Loanee Loan Outstanding (Tk)',
                    ] as $name => $label)
                        <div>
                            <label class="mb-1.5 block text-[11px] font-medium text-slate-600">{{ $label }}</label>
                            <input type="number" name="{{ $name }}" min="0" step="{{ str_contains($name, 'count') || str_contains($name, 'samities') || str_contains($name, 'members') || str_contains($name, 'borrowers') ? '1' : '0.01' }}" required value="{{ $val($name) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Fiscal year activity</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Yellow columns in your Excel — increases are calculated automatically</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        'fy_savings_collection' => 'FY Savings Collection',
                        'fy_savings_withdrawal' => 'FY Savings Withdrawal',
                        'fy_members_admission' => 'FY Members Admission',
                        'fy_members_dropout' => 'FY Members Dropout',
                        'fy_disbursement_borrowers' => 'FY Disbursement Borrowers',
                        'fy_fully_repayment_borrowers' => 'FY Fully Repayment Borrowers',
                        'fy_disbursement_amount' => 'FY Disbursement Amount',
                        'fy_loan_recovery' => 'FY Loan Recovery',
                    ] as $name => $label)
                        <div>
                            <label class="mb-1.5 block text-[11px] font-medium text-slate-600">{{ $label }}</label>
                            <input type="number" name="{{ $name }}" min="0" step="{{ str_contains($name, 'borrowers') || str_contains($name, 'admission') || str_contains($name, 'dropout') ? '1' : '0.01' }}" required value="{{ $val($name) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Funds & dues</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        'own_fund_until_prior_june' => "Own Fund Until {$priorJune}",
                        'surplus_deficit_fy' => "Surplus/Deficit (FY {$fyLabel})",
                        'new_due' => 'New Due',
                        'due_increase_this_month' => 'Due Increase This Month',
                    ] as $name => $label)
                        <div>
                            <label class="mb-1.5 block text-[11px] font-medium text-slate-600">{{ $label }}</label>
                            <input type="number" name="{{ $name }}" step="0.01" required value="{{ $val($name) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                            <p class="mt-1 text-[10px] text-slate-400">Negative values allowed where applicable</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-card">
                <p class="text-[11px] text-slate-500">After saving you return to the KPI list. Use <strong>Export Excel</strong> when ready for all branches.</p>
                <div class="flex gap-2">
                    <a href="{{ route('kpis.index', ['fy' => $fyLabel]) }}" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-navy-900 px-4 py-1.5 text-[12px] font-semibold text-white hover:bg-navy-800">Save KPI</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
