@php
    $monthName = date('F', mktime(0, 0, 0, $month, 1));
    $samples = (int) ($orgRow->total_samples_checked ?? 0);
    $irregularities = (int) ($orgRow->total_irregularities ?? 0);
    $rate = $samples > 0 ? round($irregularities / $samples * 100, 1) : null;
    $rateTone = $rate === null ? 'bg-slate-100 text-slate-500' : ($rate >= 20 ? 'bg-rose-50 text-rose-700 ring-rose-200' : ($rate >= 10 ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200'));
    $riskTone = match ($indicator->risk_rating) {
        'Major' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'Minor' => 'bg-slate-100 text-slate-600 ring-slate-200',
        null, '' => null,
        default => 'bg-amber-50 text-amber-700 ring-amber-200',
    };
    $cards = [
        ['label' => 'Total amount', 'value' => number_format((float) ($orgRow->total_amount ?? 0), 2), 'hint' => 'Across all branches', 'tone' => 'from-[#1b3a70] to-[#2b579a]'],
        ['label' => 'Samples checked', 'value' => number_format($samples), 'hint' => 'Items the auditors tested', 'tone' => 'from-sky-400 to-sky-600'],
        ['label' => 'Irregularities', 'value' => number_format($irregularities), 'hint' => $rate !== null ? $rate.'% of samples' : 'No samples recorded', 'tone' => 'from-rose-400 to-rose-600'],
        ['label' => 'Objected branches', 'value' => number_format((int) ($orgRow->objected_branch_count ?? 0)), 'hint' => count($branchRows).' with stored findings', 'tone' => 'from-amber-400 to-orange-500'],
    ];
@endphp
<x-app-layout>
    <div
        class="space-y-3 px-3 py-3 lg:px-5"
        style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;"
        x-data="findingBranchRows({ rows: @js($branchRows ?? []) })"
    >
        <x-findings-header
            :title="$indicator->indicator_code.' — '.$indicator->title"
            :subtitle="collect([$indicator->category, $indicator->sub_category, $monthName.' '.$year])->filter(fn ($v) => filled($v) && $v !== '—')->join(' › ')"
            :back="route('audit-findings.index', ['month' => $month, 'year' => $year])"
            back-label="Back to Findings Matrix"
            :month="$month"
            :year="$year"
            :month-strip="$monthStrip"
            :prev-year-url="$prevYearUrl"
            :next-year-url="$nextYearUrl"
        >
            @if ($riskTone)
                <span class="rounded-full px-2.5 py-1 text-[11.5px] font-semibold ring-1 {{ $riskTone }}">{{ $indicator->risk_rating }} risk</span>
            @endif
            @if ($rate !== null)
                <span class="rounded-full px-2.5 py-1 text-[11.5px] font-semibold ring-1 {{ $rateTone }}" title="Irregularities ÷ samples checked">{{ $rate }}% irregular</span>
            @endif
        </x-findings-header>

        @if (session('status'))
            <div data-flash class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-[12px] font-medium text-emerald-800">
                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>{{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
            @foreach ($cards as $card)
                <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <span class="h-9 w-1.5 shrink-0 rounded-full bg-gradient-to-b {{ $card['tone'] }}"></span>
                    <div class="min-w-0">
                        <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-0.5 truncate text-[19px] font-bold leading-none tabular-nums text-navy-900">{{ $card['value'] }}</p>
                        <p class="mt-1 truncate text-[11px] text-slate-500">{{ $card['hint'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                <div>
                    <p class="text-[13px] font-semibold text-navy-900">Branches with findings <span class="font-normal text-slate-400">{{ count($branchRows) }}</span></p>
                    <p class="text-[11px] text-slate-500">One row per branch with a stored finding for {{ $monthName }} {{ $year }}. Responsible staff come from the audit report, the same as on the Summary.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12.5px]">
                    <thead>
                        <tr class="border-b border-slate-200 text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-3 py-2">Branch</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                            <th class="px-3 py-2 text-right">Samples</th>
                            <th class="px-3 py-2 text-right">Irregularities</th>
                            <th class="px-3 py-2">Observation</th>
                            <th class="min-w-[200px] px-3 py-2">Responsible staff</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="align-top transition hover:bg-sky-50/40">
                                <td class="px-3 py-2.5">
                                    <p class="font-semibold text-navy-900" x-text="row.shakha_name"></p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">
                                        <span class="font-mono" x-text="row.shakha_code"></span>
                                        <span x-show="row.area_name && row.area_name !== '—'"> · <span x-text="row.area_name"></span></span>
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2.5 text-right tabular-nums text-slate-700" x-text="row.amount"></td>
                                <td class="px-3 py-2.5 text-right tabular-nums text-slate-700" x-text="row.sample_size_checked"></td>
                                <td class="whitespace-nowrap px-3 py-2.5 text-right">
                                    <span class="font-bold tabular-nums text-rose-700" x-text="row.irregularity_count"></span>
                                    <span
                                        x-show="rateOf(row) !== null"
                                        class="ml-1 rounded-full px-1.5 py-0.5 text-[10.5px] font-semibold"
                                        :class="rateTone(rateOf(row))"
                                        x-text="rateOf(row) + '%'"
                                    ></span>
                                </td>
                                <td class="max-w-[340px] px-3 py-2.5 text-[12.5px] leading-relaxed text-slate-600">
                                    <p class="line-clamp-3" :title="row.observation" x-text="row.observation"></p>
                                </td>
                                <td class="px-3 py-2.5 text-[12.5px] font-medium text-violet-900">
                                    <template x-if="row.accused_people.length === 0">
                                        <span x-text="row.accused_text || '—'"></span>
                                    </template>
                                    <div class="flex flex-col gap-1" x-show="row.accused_people.length > 0">
                                        <template x-for="(person, pIdx) in row.accused_people" :key="pIdx">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <template x-if="person.dossier_url">
                                                    <a :href="person.dossier_url" class="hover:underline" title="সব বছরের আর্থিক রিপোর্ট দেখুন" x-text="person.label"></a>
                                                </template>
                                                <template x-if="! person.dossier_url">
                                                    <span x-text="person.label"></span>
                                                </template>
                                                <span
                                                    x-show="person.report_count >= 2"
                                                    class="inline-flex rounded-full bg-rose-100 px-1.5 py-0.5 text-[11px] font-bold text-rose-800"
                                                    :title="person.report_count + ' বার আর্থিক রিপোর্ট (সব বছর)'"
                                                    x-text="person.report_count + ' বার'"
                                                ></span>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0">
                            <td colspan="6" class="px-3 py-10 text-center">
                                <p class="text-[13px] font-semibold text-slate-600">No branch findings for this indicator in {{ $monthName }} {{ $year }}</p>
                                <p class="mt-0.5 text-[12px] text-slate-500">Pick another month above. Months with a number have findings.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            function findingBranchRows(cfg) {
                return {
                    rows: cfg.rows || [],
                    toNumber(value) {
                        const n = parseFloat(String(value ?? '').replace(/,/g, ''));
                        return Number.isFinite(n) ? n : null;
                    },
                    rateOf(row) {
                        const samples = this.toNumber(row.sample_size_checked);
                        const irregular = this.toNumber(row.irregularity_count);
                        if (! samples || irregular === null) return null;
                        return Math.round(irregular / samples * 1000) / 10;
                    },
                    rateTone(rate) {
                        if (rate === null) return '';
                        if (rate >= 20) return 'bg-rose-50 text-rose-700';
                        if (rate >= 10) return 'bg-amber-50 text-amber-700';
                        return 'bg-emerald-50 text-emerald-700';
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
