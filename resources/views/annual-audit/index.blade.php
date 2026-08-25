<x-app-layout>
    @php
        $tabs = [
            'policies' => [
                'label' => $plan->generated_at ? 'Policies' : '1. Policies',
                'idle' => 'bg-rose-50 text-rose-700 hover:bg-rose-100',
                'active' => 'bg-rose-600 text-white shadow-md ring-2 ring-rose-600/30',
            ],
            'total' => ['label' => 'Total', 'idle' => 'bg-slate-100 text-slate-600 hover:bg-slate-200', 'active' => 'bg-navy-900 text-white shadow-md ring-2 ring-navy-900/20'],
            'shakha' => ['label' => 'Shakha Audit', 'idle' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100', 'active' => 'bg-emerald-600 text-white shadow-md ring-2 ring-emerald-600/30'],
            'area' => ['label' => 'Area Office', 'idle' => 'bg-amber-50 text-amber-800 hover:bg-amber-100', 'active' => 'bg-amber-500 text-white shadow-md ring-2 ring-amber-500/30'],
            'pksf' => ['label' => 'PKSF & Maternity', 'idle' => 'bg-orange-50 text-orange-700 hover:bg-orange-100', 'active' => 'bg-orange-500 text-white shadow-md ring-2 ring-orange-500/30'],
            'hq' => ['label' => 'HQ', 'idle' => 'bg-sky-50 text-sky-700 hover:bg-sky-100', 'active' => 'bg-sky-600 text-white shadow-md ring-2 ring-sky-600/30'],
            'project_audit' => ['label' => 'Project Audit', 'idle' => 'bg-teal-50 text-teal-700 hover:bg-teal-100', 'active' => 'bg-teal-600 text-white shadow-md ring-2 ring-teal-600/30'],
            'project_monitoring' => ['label' => 'Project Monitoring', 'idle' => 'bg-cyan-50 text-cyan-700 hover:bg-cyan-100', 'active' => 'bg-cyan-600 text-white shadow-md ring-2 ring-cyan-600/30'],
        ];
        $canEditSchedule = $canEditSchedule ?? true;
    @endphp

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-3 flex flex-nowrap items-center gap-2 overflow-x-auto pb-0.5">
            <h1 class="shrink-0 text-[14px] font-semibold tracking-tight text-navy-900">Annual Audit &amp; Monitoring</h1>
            <span class="hidden h-4 w-px shrink-0 bg-slate-200 sm:block"></span>
            <label class="inline-flex shrink-0 items-center gap-1 text-[11px] text-slate-400">
                FY
                <select
                    class="h-7 rounded-md border-slate-200 py-0 text-[12px] font-medium text-navy-900"
                    onchange="window.location = this.value"
                >
                    @foreach ($availablePlans as $availablePlan)
                        <option
                            value="{{ route('annual-audit.index', array_filter(['fy' => $availablePlan->fy_label, 'tab' => $tab])) }}"
                            @selected($availablePlan->fy_label === $plan->fy_label)
                        >
                            {{ $availablePlan->fy_label }} ({{ $availablePlan->status }})
                        </option>
                    @endforeach
                </select>
            </label>
            <span class="hidden shrink-0 text-[11px] capitalize text-slate-400 sm:inline">{{ $plan->status }}</span>
            @if ($plan->generated_at)
                <span class="hidden shrink-0 text-[11px] text-slate-400 lg:inline">· {{ $plan->generated_at->format('d M Y H:i') }}</span>
            @endif

            <div class="ml-auto flex shrink-0 flex-nowrap items-center gap-1.5">
                @if ($canDeletePlan ?? false)
                    <form
                        method="POST"
                        action="{{ route('annual-audit.years.destroy') }}"
                        class="inline"
                        onsubmit="return confirm('Delete the entire FY {{ $plan->fy_label }} report?\n\nThis permanently removes all schedules and policies for that year. This cannot be undone.')"
                    >
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md border border-rose-200 bg-rose-50 px-2 text-[11px] font-medium text-rose-700 hover:bg-rose-100">
                            Delete FY
                        </button>
                    </form>
                @endif
                @unless ($nextPlanExists)
                    <form method="POST" action="{{ route('annual-audit.years.store') }}" class="inline">
                        @csrf
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 text-[11px] font-medium text-emerald-800 hover:bg-emerald-100">
                            Create {{ $nextFyLabel }}
                        </button>
                    </form>
                @endunless
                <a
                    href="{{ route('annual-audit.export', ['mode' => 'all', 'fy' => $plan->fy_label]) }}"
                    class="inline-flex h-7 items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 text-[11px] font-medium text-emerald-800 hover:bg-emerald-100"
                    title="Download Total through Project Monitoring in one Excel file"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Full Report
                </a>
                <form method="POST" action="{{ route('annual-audit.generate') }}" class="inline">
                    @csrf
                    <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                    <button
                        type="submit"
                        class="inline-flex h-7 items-center rounded-md bg-navy-900 px-2.5 text-[11px] font-medium text-white hover:bg-navy-800"
                        title="Uses frequencies from Policies to build the yearly schedule"
                    >
                        {{ $plan->generated_at ? 'Regenerate' : '2. Generate Plan' }}
                    </button>
                </form>
                @if ($plan->generated_at)
                    <form method="POST" action="{{ route('annual-audit.sync-missing') }}" class="inline">
                        @csrf
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <button
                            type="submit"
                            class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                            title="Add only new shakha / area / project rows without changing existing schedules"
                        >
                            Sync new items
                        </button>
                    </form>
                @endif
                @if ($plan->status !== 'published')
                    <form method="POST" action="{{ route('annual-audit.publish') }}" class="inline">
                        @csrf
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50">
                            Publish
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        @unless ($plan->generated_at)
            <div class="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 rounded-lg border border-rose-100 bg-rose-50/70 px-3 py-2 text-[12px] text-rose-900">
                <span class="font-semibold">Setup this FY:</span>
                <a href="{{ route('annual-audit.index', ['fy' => $plan->fy_label, 'tab' => 'policies']) }}" class="font-medium underline decoration-rose-300 underline-offset-2 hover:text-rose-700">1. Set Policies</a>
                <span class="text-rose-300">→</span>
                <span>2. Generate Plan</span>
                <span class="text-rose-300">→</span>
                <span class="text-rose-700/80">3. Review report tabs</span>
            </div>
        @endunless

        <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ([
                ['label' => 'Planned', 'value' => $kpis['planned']],
                ['label' => 'Completed', 'value' => $kpis['completed']],
                ['label' => 'Pending', 'value' => $kpis['pending']],
                ['label' => 'Shakha', 'value' => $kpis['shakha']],
                ['label' => 'Area', 'value' => $kpis['area']],
                ['label' => 'Projects', 'value' => $kpis['project_audit'] + $kpis['project_monitoring']],
            ] as $kpi)
                <div class="rounded-xl border border-slate-100 bg-white px-3 py-2.5 shadow-card">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ $kpi['label'] }}</p>
                    <p class="mt-1 text-[18px] font-semibold tracking-tight text-navy-900">{{ number_format($kpi['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="mb-3 flex flex-wrap gap-1.5">
            @foreach ($tabs as $key => $tabMeta)
                <a
                    href="{{ route('annual-audit.index', array_filter(['fy' => $plan->fy_label, 'tab' => $key, 'division' => $filters['division'], 'area_id' => $filters['area_id']])) }}"
                    class="whitespace-nowrap rounded-lg px-3 py-1.5 text-[12px] font-semibold transition {{ $tab === $key ? $tabMeta['active'] : $tabMeta['idle'] }}"
                >
                    {{ $tabMeta['label'] }}
                </a>
            @endforeach
        </div>

        @if ($tab === 'pksf')
            <p class="mb-3 text-[11px] text-slate-500">
                Click any month cell to schedule or remove. Nothing is fixed — admin controls each month.
            </p>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            @if ($tab === 'hq')
                @include('annual-audit.partials.hq-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'rows' => $rows,
                    'hqTotals' => $hqTotals,
                    'canEditSchedule' => $canEditSchedule,
                ])
            @elseif ($tab === 'shakha')
                @include('annual-audit.partials.shakha-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'shakhaGroups' => $shakhaGroups,
                    'shakhaTotals' => $shakhaTotals,
                    'divisions' => $divisions,
                    'areas' => $areas,
                    'canEditSchedule' => $canEditSchedule,
                ])
            @elseif ($tab === 'pksf')
                @include('annual-audit.partials.pksf-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'rows' => $rows,
                    'pksfTotals' => $pksfTotals,
                    'canEditSchedule' => $canEditSchedule,
                    'highlightProjectId' => $highlightProjectId ?? null,
                ])
            @elseif ($tab === 'area')
                @include('annual-audit.partials.area-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'rows' => $rows,
                    'areaTotals' => $areaTotals,
                    'divisions' => $divisions,
                    'canEditSchedule' => $canEditSchedule,
                ])
            @elseif (in_array($tab, ['project_audit', 'project_monitoring'], true))
                @include('annual-audit.partials.project-work-plan', [
                    'mode' => $tab === 'project_audit' ? 'audit' : 'monitoring',
                    'plan' => $plan,
                    'months' => $months,
                    'projectGroups' => $projectGroups,
                    'divisions' => $divisions,
                    'canEditSchedule' => $canEditSchedule,
                    'highlightProjectId' => $highlightProjectId ?? null,
                ])
            @elseif ($tab === 'total')
                @include('annual-audit.partials.total-work-plan', [
                    'plan' => $plan,
                    'months' => $months,
                    'categoryTotals' => $categoryTotals,
                ])
            @elseif ($tab === 'policies')
                <form method="POST" action="{{ route('annual-audit.policies') }}" class="p-4">
                    @csrf
                    <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                    <p class="mb-3 text-[12px] text-slate-600">
                        <span class="font-semibold text-navy-900">Step 1 — set times per year.</span>
                        That is the only policy setting. Months are placed evenly across the FY when you generate;
                        change any cell later on the report tabs.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="border-b border-slate-100 bg-slate-50/80">
                                <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    <th class="px-3 py-2.5">Category</th>
                                    <th class="px-3 py-2.5">Times / Year</th>
                                    <th class="px-3 py-2.5">When generating</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($policies as $policy)
                                    @php
                                        $hints = [
                                            'shakha_audit' => 'Months are rotated across branches so visits are spread out.',
                                            'area_office' => 'Same months for every area (evenly spaced).',
                                            'pksf_maternity' => 'Same months for each PKSF / Maternity location.',
                                            'hq_concern' => 'Same months for each HQ department.',
                                            'project_audit' => 'Same months for each project-audit location.',
                                            'project_monitoring' => 'Same months for each monitoring location.',
                                        ];
                                    @endphp
                                    <tr class="text-[12px]">
                                        <td class="px-3 py-2.5 font-medium capitalize text-navy-900">{{ str_replace('_', ' ', $policy->category) }}</td>
                                        <td class="px-3 py-2.5">
                                            @if ($policy->category === 'shakha_audit')
                                                <select name="policies[{{ $policy->id }}][frequency_per_year]" class="w-24 rounded-lg border-slate-200 text-[12px]">
                                                    @foreach ([2, 3, 4, 6, 12] as $freq)
                                                        <option value="{{ $freq }}" @selected((int) $policy->frequency_per_year === $freq)>{{ $freq }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="number" min="1" max="12" name="policies[{{ $policy->id }}][frequency_per_year]" value="{{ $policy->frequency_per_year }}" class="w-24 rounded-lg border-slate-200 text-[12px]">
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $hints[$policy->category] ?? 'Evenly spaced months.' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                            Save policies
                        </button>
                        <button type="submit" name="regenerate" value="1" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                            Save &amp; regenerate plan
                        </button>
                    </div>
                </form>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-slate-100 bg-slate-50/80">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-3 py-2.5">Project</th>
                                <th class="px-3 py-2.5">Division</th>
                                <th class="px-3 py-2.5">Location</th>
                                @foreach ($months as $month)
                                    <th class="px-1 py-2.5 text-center">{{ $month['label'] }}</th>
                                @endforeach
                                <th class="px-3 py-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($rows as $row)
                                <tr
                                    class="text-[12px]"
                                    @audit-tick="
                                        const cell = $el.querySelector('[data-row-total]');
                                        if (cell) cell.textContent = Number(cell.textContent || 0) + Number($event.detail.delta || 0);
                                    "
                                >
                                    <td class="px-3 py-1.5 font-medium text-navy-900">{{ $row['project'] }}</td>
                                    <td class="px-3 py-1.5 text-slate-600">{{ $row['division'] ?: '—' }}</td>
                                    <td class="px-3 py-1.5 text-slate-600">{{ $row['location'] }}</td>
                                    @foreach ($row['months'] as $monthIndex => $active)
                                        <td class="px-1 py-1 text-center">
                                            <x-audit-month-mark
                                                :active="(bool) $active"
                                                :manual="(bool) ($row['manual'][$monthIndex] ?? false)"
                                                :editable="$canEditSchedule"
                                                :category="$row['category']"
                                                :schedulable-type="$row['schedulable_type']"
                                                :schedulable-id="$row['id']"
                                                :month-index="$monthIndex"
                                                :tab="$tab"
                                                :fy="$plan->fy_label"
                                            />
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-1.5 text-right font-semibold text-navy-900" data-row-total>{{ $row['total'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="px-4 py-10 text-center text-[12px] text-slate-400">
                                        No schedule rows yet. Set frequency in <span class="font-medium text-navy-800">Policies</span>, then click <span class="font-medium text-navy-800">Generate Annual Plan</span>, or click months directly.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
