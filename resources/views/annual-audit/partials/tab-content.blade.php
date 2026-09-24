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
    @if ($canManageAnnual)
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
                        <tr class="text-xs font-semibold uppercase tracking-wide text-slate-500">
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
        <div class="p-4">
            <p class="mb-3 text-[12px] text-slate-500">View only — policy frequencies for this FY.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2.5">Category</th>
                            <th class="px-3 py-2.5">Times / Year</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($policies as $policy)
                            <tr class="text-[12px]">
                                <td class="px-3 py-2.5 font-medium capitalize text-navy-900">{{ str_replace('_', ' ', $policy->category) }}</td>
                                <td class="px-3 py-2.5 text-slate-700">{{ $policy->frequency_per_year }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif
