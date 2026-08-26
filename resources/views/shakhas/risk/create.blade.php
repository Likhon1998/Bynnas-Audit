<x-app-layout>
    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="{{ route('shakhas.index') }}" class="hover:text-brand-600">All Shakha</a>
                    <span>/</span>
                    <span class="text-slate-600">Risk Branch Analysis</span>
                </div>
                <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">{{ $shakha->name }}</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    {{ $shakha->area?->name }} · {{ $shakha->area?->division }}
                    @if ($shakha->code)
                        · {{ $shakha->code }}
                    @endif
                </p>
            </div>
            <a href="{{ route('shakhas.index') }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">
                Back to list
            </a>
        </div>

        <div class="mb-4 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-[12px] text-sky-900">
            <p class="font-semibold">Auto-mapped from annual KPI</p>
            <p class="mt-0.5 text-sky-800">
                Overdue principal = KPI Total OD Taka. Profitability uses KPI Surplus/Deficit.
                Total income and total expenditure are entered manually for OSS.
            </p>
        </div>

        @unless ($kpi)
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[12px] text-amber-950">
                <p class="font-semibold">Annual KPI is required first</p>
                <p class="mt-0.5 text-amber-900">
                    There is no KPI for <span class="font-medium">{{ $shakha->name }}</span> in FY {{ $fy->label }}.
                    Enter the annual KPI, then return here to run risk analysis.
                </p>
                <a href="{{ route('kpis.edit', ['shakha' => $shakha, 'fy' => $fy->label]) }}" class="mt-2 inline-flex rounded-lg bg-amber-700 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-amber-800">
                    Enter KPI for FY {{ $fy->label }} →
                </a>
            </div>
        @endunless

        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="GET" action="{{ route('shakhas.risk.create', $shakha) }}" class="mb-4 flex flex-wrap items-end gap-2 rounded-xl border border-slate-100 bg-white p-4 shadow-card">
            <div>
                <label for="month" class="mb-1 block text-[11px] font-medium text-slate-600">Assessment month</label>
                <select id="month" name="month" class="h-9 rounded-lg border-slate-200 text-[13px]">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($m === $month)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="year" class="mb-1 block text-[11px] font-medium text-slate-600">Year</label>
                <select id="year" name="year" class="h-9 rounded-lg border-slate-200 text-[13px]">
                    @for ($y = now()->year + 1; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="h-9 rounded-lg border border-slate-200 bg-slate-50 px-3 text-[12px] font-medium text-slate-700 hover:bg-slate-100">
                Load period
            </button>

            <div class="ml-auto flex flex-wrap items-center gap-2 text-[11px]">
                <span class="rounded-md bg-slate-100 px-2 py-1 font-medium text-slate-600">FY {{ $fy->label }}</span>
                @if ($kpi)
                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 font-medium text-emerald-700">Annual KPI found</span>
                    <span class="text-slate-500">OTR {{ number_format(($otr ?? 0) * 100, 2) }}%</span>
                    <span class="text-slate-500">DR/NPLR {{ number_format(($dr ?? 0) * 100, 2) }}%</span>
                    <span class="text-slate-500">Surplus {{ number_format((float) ($surplus ?? 0), 2) }}</span>
                    <span class="text-slate-500">OD {{ number_format((float) ($totalOdTaka ?? 0), 2) }}</span>
                @else
                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-800">
                        No annual KPI for FY {{ $fy->label }}
                    </span>
                    <a href="{{ route('kpis.edit', $shakha) }}?fy={{ urlencode($fy->label) }}" class="font-semibold text-brand-600 hover:underline">
                        Enter KPI →
                    </a>
                @endif
            </div>
        </form>

        <form
            method="POST"
            action="{{ route('shakhas.risk.store', $shakha) }}"
            class="rounded-2xl border border-slate-100 bg-white shadow-card"
        >
            @csrf
            <input type="hidden" name="assessment_month" value="{{ $month }}">
            <input type="hidden" name="assessment_year" value="{{ $year }}">

            <div class="border-b border-slate-100 px-5 py-3.5">
                <p class="text-[13px] font-semibold text-navy-900">Operational &amp; audit inputs</p>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Total OD Taka and Surplus/Deficit come from KPI. Enter income, expenditure, and other operational fields.
                    @if ($existing)
                        <span class="text-brand-600">Existing score: {{ $existing->total_weighted_score }} · {{ $existing->risk_category }}</span>
                    @endif
                </p>
            </div>

            <div
                class="grid gap-4 px-5 py-5 sm:grid-cols-2"
                x-data="{
                    income: @js((float) old('total_income', $existing?->total_income ?? 0)),
                    expenditure: @js((float) old('total_expenditure', $existing?->total_expenditure ?? 0)),
                    get ossPct() {
                        return this.expenditure > 0 ? (this.income / this.expenditure) * 100 : 0;
                    }
                }"
            >
                <div class="sm:col-span-2 grid gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-3 sm:grid-cols-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Surplus/Deficit (KPI)</p>
                        <p class="mt-1 text-[13px] font-semibold tabular-nums text-navy-900">{{ number_format((float) ($surplus ?? 0), 2) }}</p>
                        <p class="mt-0.5 text-[10px] text-slate-500">{{ ((float) ($surplus ?? 0)) >= 0 ? 'Profit' : 'Loss' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total OD Taka → overdue</p>
                        <p class="mt-1 text-[13px] font-semibold tabular-nums text-navy-900">{{ number_format((float) ($totalOdTaka ?? 0), 2) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">NPLR / DR</p>
                        <p class="mt-1 text-[13px] font-semibold tabular-nums text-navy-900">{{ number_format(($nplr ?? 0) * 100, 2) }}%</p>
                    </div>
                </div>

                <div>
                    <label for="total_income" class="mb-1.5 block text-[11px] font-medium text-slate-600">Total income <span class="text-rose-500">*</span></label>
                    <input id="total_income" name="total_income" type="number" step="0.01" min="0" required
                        x-model.number="income"
                        value="{{ old('total_income', $existing?->total_income) }}"
                        class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <p class="mt-1 text-[10px] text-slate-400">Not on KPI — needed for OSS (with expenditure).</p>
                    <x-input-error :messages="$errors->get('total_income')" class="mt-1" />
                </div>

                <div>
                    <label for="total_expenditure" class="mb-1.5 block text-[11px] font-medium text-slate-600">Total expenditure <span class="text-rose-500">*</span></label>
                    <input id="total_expenditure" name="total_expenditure" type="number" step="0.01" min="0" required
                        x-model.number="expenditure"
                        value="{{ old('total_expenditure', $existing?->total_expenditure) }}"
                        class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <p class="mt-1 text-[10px] text-slate-400">Not on KPI — enter manually. OSS: <span class="font-medium text-slate-600" x-text="ossPct.toFixed(2) + '%'"></span></p>
                    <x-input-error :messages="$errors->get('total_expenditure')" class="mt-1" />
                </div>

                <div>
                    <label for="write_off_principal_amount" class="mb-1.5 block text-[11px] font-medium text-slate-600">Write-off principal amount <span class="text-rose-500">*</span></label>
                    <input id="write_off_principal_amount" name="write_off_principal_amount" type="number" step="0.01" min="0" required
                        value="{{ old('write_off_principal_amount', $existing?->write_off_principal_amount) }}"
                        class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <x-input-error :messages="$errors->get('write_off_principal_amount')" class="mt-1" />
                </div>

                <div>
                    <label for="savings_adjustment_amount" class="mb-1.5 block text-[11px] font-medium text-slate-600">Savings adjustment amount <span class="text-rose-500">*</span></label>
                    <input id="savings_adjustment_amount" name="savings_adjustment_amount" type="number" step="0.01" min="0" required
                        value="{{ old('savings_adjustment_amount', $existing?->savings_adjustment_amount) }}"
                        class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <x-input-error :messages="$errors->get('savings_adjustment_amount')" class="mt-1" />
                </div>

                @php
                    $distanceYes = (bool) old(
                        'distance_from_area_office_km',
                        ($existing?->distance_from_area_office_km ?? 0) > 0
                    );
                @endphp
                <div>
                    <p class="mb-1.5 block text-[11px] font-medium text-slate-600">More than 20 km from area office? <span class="text-rose-500">*</span></p>
                    <div class="flex gap-2">
                        <label class="inline-flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700">
                            <input type="radio" name="distance_from_area_office_km" value="1" class="text-brand-600 focus:ring-brand-500" @checked($distanceYes)>
                            Yes
                        </label>
                        <label class="inline-flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700">
                            <input type="radio" name="distance_from_area_office_km" value="0" class="text-brand-600 focus:ring-brand-500" @checked(! $distanceYes)>
                            No
                        </label>
                    </div>
                    <p class="mt-1 text-[10px] text-slate-400">Yes adds risk points in the scoring matrix.</p>
                    <x-input-error :messages="$errors->get('distance_from_area_office_km')" class="mt-1" />
                </div>

                <div class="sm:col-span-2 grid gap-3 sm:grid-cols-2">
                    <label class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3 text-[12px] text-slate-700">
                        <input type="hidden" name="has_both_bm_and_abm" value="0">
                        <input
                            type="checkbox"
                            name="has_both_bm_and_abm"
                            value="1"
                            class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            @checked(old('has_both_bm_and_abm', $existing?->has_both_bm_and_abm))
                        >
                        <span>
                            <span class="font-medium text-navy-900">Has both BM and ABM</span>
                            <span class="mt-0.5 block text-[11px] text-slate-500">Unchecked adds risk points in the scoring matrix.</span>
                        </span>
                    </label>

                    <label class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3 text-[12px] text-slate-700">
                        <input type="hidden" name="special_audit_last_two_years" value="0">
                        <input
                            type="checkbox"
                            name="special_audit_last_two_years"
                            value="1"
                            class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            @checked(old('special_audit_last_two_years', $existing?->special_audit_last_two_years))
                        >
                        <span>
                            <span class="font-medium text-navy-900">Special audit in last two years</span>
                            <span class="mt-0.5 block text-[11px] text-slate-500">Used as an audit-coverage factor in scoring.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3.5">
                <p class="text-[11px] text-slate-400">Score bands: 0–25 Low · 26–45 Medium · 46–65 High · 66+ Significant</p>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('shakhas.index') }}" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-white">Cancel</a>
                    <button
                        type="submit"
                        @disabled(! $kpi)
                        class="inline-flex items-center rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Calculate &amp; save risk
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
