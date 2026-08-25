@php
    // Employee free-day availability is passed as $employeeAvailability from the controller.
@endphp

<x-app-layout>
    <div
        class="px-4 py-5 lg:px-6"
        x-data="monthlyAllocate({
            items: @js($allocatePayload),
            employees: @js($employeeAvailability),
            calendar: @js($calendarPayload),
            openId: @js($openAllocateId),
            oldVisitorIds: @js(array_map('intval', (array) old('employee_ids', []))),
            oldStart: @js(old('start_date')),
            oldEnd: @js(old('end_date')),
            oldPurpose: @js(old('purpose')),
            oldRemarks: @js(old('remarks')),
            oldLastUpto: @js(old('last_audit_upto')),
            oldCountOffDays: @js((bool) old('count_off_days', false)),
            hasConflict: @js((bool) $conflictWarning),
            conflictWarning: @js($conflictWarning),
        })"
    >
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">Monthly Field Visits</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Monthly schedule from yearly plan — FY {{ $plan->fy_label }} · {{ $monthLabel }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('monthly-visits.schedule.print', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[12px] font-medium text-emerald-800 hover:bg-emerald-100">Print monthly plan</a>
                <a href="{{ route('monthly-visits.schedule.pdf', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">PDF</a>
                <a href="{{ route('monthly-visits.schedule.doc', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">DOC</a>
                <a href="{{ route('monthly-visits.schedule.excel', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Excel</a>
                <a href="{{ route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'schedule']) }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Reports</a>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-end gap-2 rounded-xl border border-slate-100 bg-white p-3 shadow-card">
            <form method="GET" action="{{ route('monthly-visits.index') }}" class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="mb-1 block text-[10px] font-medium text-slate-400">Financial Year</label>
                    <select name="fy" class="h-8 rounded-md border-slate-200 !py-0 pl-2.5 pr-8 text-[12px] leading-[1.25rem]" onchange="this.form.submit()">
                        @foreach ($availablePlans as $p)
                            <option value="{{ $p->fy_label }}" @selected($p->fy_label === $plan->fy_label)>{{ $p->fy_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-medium text-slate-400">Month</label>
                    <select name="month" class="h-8 rounded-md border-slate-200 !py-0 pl-2.5 pr-8 text-[12px] leading-[1.25rem]" onchange="this.form.submit()">
                        @foreach ($monthOptions as $opt)
                            <option value="{{ $opt['index'] }}" @selected((int) $opt['index'] === (int) $monthIndex)>{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <form method="POST" action="{{ route('monthly-visits.generate') }}">
                @csrf
                <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                <input type="hidden" name="month" value="{{ $monthIndex }}">
                <button type="submit" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                    Re-sync yearly plan
                </button>
            </form>
            <form
                method="POST"
                action="{{ route('monthly-visits.resolve-conflicts') }}"
                onsubmit="return confirm('Remove any same-person overlapping allocations for {{ $monthLabel }}?')"
            >
                @csrf
                <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                <input type="hidden" name="month" value="{{ $monthIndex }}">
                <button type="submit" class="inline-flex h-8 items-center rounded-md border border-rose-200 bg-rose-50 px-3 text-[12px] font-medium text-rose-800 hover:bg-rose-100">
                    Fix date conflicts
                </button>
            </form>
            <form
                method="POST"
                action="{{ route('monthly-visits.bulk-allocate') }}"
                onsubmit="return confirm('Auto-allocate {{ $monthLabel }} with any visit length (1, 2, 3, 4, 5, 6, 7… working days as needed). If the month is full, existing non-completed plans will be rebalanced so every office is covered — no same-person date overlaps.')"
            >
                @csrf
                <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                <input type="hidden" name="month" value="{{ $monthIndex }}">
                <button type="submit" class="inline-flex h-8 items-center rounded-md bg-emerald-600 px-3 text-[12px] font-medium text-white hover:bg-emerald-500">
                    Auto-allocate month
                </button>
            </form>
            <div class="min-w-[200px] flex-1 sm:max-w-xs">
                <label class="mb-1 block text-[10px] font-medium text-slate-400">Search lists</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                    <input
                        type="search"
                        x-model="listQuery"
                        placeholder="Branch, visitor, type, status…"
                        class="h-8 w-full rounded-md border-slate-200 py-0 pl-8 pr-8 text-[12px] leading-[1.25rem]"
                        autocomplete="off"
                    >
                    <button
                        type="button"
                        x-show="listQuery"
                        x-cloak
                        @click="listQuery = ''"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] font-medium text-slate-400 hover:text-slate-600"
                    >Clear</button>
                </div>
            </div>
            @unless ($plan->generated_at)
                <p class="w-full text-[11px] text-amber-700">Yearly plan is not generated yet — create it under Annual Audit first.</p>
            @endunless
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ([
                ['label' => 'Planned', 'value' => $performance['totals']['planned'], 'tone' => 'text-navy-900'],
                ['label' => 'Assigned', 'value' => $performance['totals']['assigned'], 'tone' => 'text-emerald-700'],
                ['label' => 'Unassigned', 'value' => $performance['totals']['pending'], 'tone' => 'text-amber-700'],
                ['label' => 'Completed', 'value' => $performance['totals']['completed'], 'tone' => 'text-sky-700'],
                ['label' => 'Cancelled', 'value' => $performance['totals']['cancelled'], 'tone' => 'text-rose-700'],
                ['label' => 'Overdue', 'value' => $performance['totals']['overdue'], 'tone' => 'text-orange-700'],
            ] as $kpi)
                <div class="rounded-xl border border-slate-100 bg-white px-3 py-2.5 shadow-card">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ $kpi['label'] }}</p>
                    <p class="mt-1 text-[18px] font-semibold tracking-tight {{ $kpi['tone'] }}">{{ number_format($kpi['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="mb-4 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-amber-100 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3">
                <div>
                    <p class="text-[13px] font-semibold text-navy-900">Unassigned from yearly plan</p>
                    <p class="text-[11px] text-slate-500">
                        <span x-text="visibleUnassignedCount"></span> / {{ $unassigned->count() }} showing
                        <span x-show="combinedUnassignedQuery" x-cloak class="text-amber-700"> · filtered</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <input
                        type="search"
                        x-model="unassignedQuery"
                        placeholder="Search unassigned…"
                        class="h-8 w-44 rounded-lg border-amber-200 bg-white py-0 pl-2.5 pr-2 text-[12px] leading-[1.25rem]"
                        autocomplete="off"
                    >
                    <button type="button" @click="showSpecial = !showSpecial" class="rounded-lg border border-amber-200 bg-white px-2.5 py-1.5 text-[12px] font-medium text-amber-800 hover:bg-amber-50">
                        + Special activity
                    </button>
                </div>
            </div>

            <div x-show="showSpecial" x-cloak class="border-b border-slate-100 bg-amber-50/40 px-4 py-3">
                <form method="POST" action="{{ route('monthly-visits.special.store') }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                    <input type="hidden" name="month" value="{{ $monthIndex }}">
                    <div>
                        <label class="mb-1 block text-[10px] font-medium text-slate-500">Activity type</label>
                        <select name="activity_type_id" required class="rounded-lg border-slate-200 text-[12px]">
                            @foreach ($activityTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[200px] flex-1">
                        <label class="mb-1 block text-[10px] font-medium text-slate-500">Entity / branch / project label</label>
                        <input type="text" name="entity_label" required placeholder="e.g. Special audit — Uttara Shakha" class="w-full rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <div class="min-w-[180px] flex-1">
                        <label class="mb-1 block text-[10px] font-medium text-slate-500">Reason / notes</label>
                        <input type="text" name="notes" placeholder="Special instruction" class="w-full rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <button type="submit" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white">Add special</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-3 py-2.5">#</th>
                            <th class="px-3 py-2.5">Branch / Entity</th>
                            <th class="px-3 py-2.5">Type</th>
                            <th class="px-3 py-2.5">Category</th>
                            <th class="px-3 py-2.5">Source</th>
                            <th class="px-3 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($unassigned as $i => $item)
                            @php
                                $unassignedSearch = strtolower(trim(implode(' ', [
                                    $item->entity_label,
                                    $item->activityType?->name,
                                    str_replace('_', ' ', $item->category),
                                    $item->isSpecial() ? 'special' : 'yearly',
                                ])));
                            @endphp
                            <tr
                                class="text-[12px] unassigned-row {{ $item->isSpecial() ? 'bg-amber-50/50' : '' }}"
                                data-search="{{ e($unassignedSearch) }}"
                                x-show="rowMatch($el.dataset.search, combinedUnassignedQuery)"
                            >
                                <td class="px-3 py-2 text-slate-500">{{ $i + 1 }}</td>
                                <td class="px-3 py-2 font-medium text-navy-900">{{ $item->entity_label }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $item->activityType?->name }}</td>
                                <td class="px-3 py-2 capitalize text-slate-500">{{ str_replace('_', ' ', $item->category) }}</td>
                                <td class="px-3 py-2">
                                    @if ($item->isSpecial())
                                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-800">Special</span>
                                    @else
                                        <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-800">Yearly</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button
                                        type="button"
                                        @click="openAllocate({{ $item->id }})"
                                        class="rounded-md bg-navy-900 px-2.5 py-1 text-[11px] font-medium text-white hover:bg-navy-800"
                                    >
                                        Allocate
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-[12px] text-slate-400">
                                    @if ($items->isEmpty())
                                        @if ($plan->generated_at)
                                            No yearly schedules for {{ $monthLabel }}. Check Annual Audit — this month may have no planned visits.
                                        @else
                                            Generate the yearly plan first, then return here.
                                        @endif
                                    @else
                                        All items for {{ $monthLabel }} are allocated.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                        @if ($unassigned->isNotEmpty())
                            <tr x-show="visibleUnassignedCount === 0 && combinedUnassignedQuery" x-cloak>
                                <td colspan="6" class="px-4 py-6 text-center text-[12px] text-slate-400">No unassigned rows match your search.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-sky-100 bg-gradient-to-r from-sky-50 to-teal-50 px-4 py-3">
                <div>
                    <p class="text-[13px] font-semibold text-navy-900">Monthly schedule — allocated visits</p>
                    <p class="text-[11px] text-slate-500">
                        <span x-text="visibleAssignedCount"></span> / {{ $assigned->count() }} showing · {{ $monthLabel }}
                        <span x-show="combinedAssignedQuery" x-cloak class="text-sky-700"> · filtered</span>
                    </p>
                </div>
                <input
                    type="search"
                    x-model="assignedQuery"
                    placeholder="Search allocated…"
                    class="h-8 w-48 rounded-lg border-sky-200 bg-white py-0 pl-2.5 pr-2 text-[12px] leading-[1.25rem]"
                    autocomplete="off"
                >
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-3 py-2.5">SL</th>
                            <th class="px-3 py-2.5">Visitor(s)</th>
                            <th class="px-3 py-2.5">Last audit upto</th>
                            <th class="px-3 py-2.5">Branch / Entity</th>
                            <th class="px-3 py-2.5">Visit date &amp; month</th>
                            <th class="px-3 py-2.5">Days</th>
                            <th class="px-3 py-2.5">Remarks</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-3 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($assigned as $i => $item)
                            @php
                                $a = $item->assignment;
                                $assignedSearch = strtolower(trim(implode(' ', [
                                    $a?->visitorNames(' '),
                                    $item->entity_label,
                                    $a?->last_audit_upto?->format('F Y'),
                                    $a?->visitDateRangeLabel(),
                                    $a?->remarks,
                                    $a?->purpose,
                                    $item->activityType?->name,
                                    str_replace('_', ' ', $a?->execution?->status ?? 'planned'),
                                    $item->isSpecial() ? 'special' : '',
                                ])));
                            @endphp
                            <tr
                                class="text-[12px] assigned-row"
                                data-search="{{ e($assignedSearch) }}"
                                x-show="rowMatch($el.dataset.search, combinedAssignedQuery)"
                            >
                                <td class="px-3 py-2 text-slate-500">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-3 py-2 font-medium text-navy-900 whitespace-pre-line">{{ $a?->visitorNames("\n") ?: '—' }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $a?->last_audit_upto?->format('F-Y') ?? '—' }}</td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ $item->entity_label }}
                                    @if ($item->isSpecial())
                                        <span class="ml-1 rounded bg-amber-100 px-1 text-[9px] font-medium text-amber-800">Special</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-600">{{ $a?->visitDateRangeLabel() }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $a?->duration_days ? str_pad((string) $a->duration_days, 2, '0', STR_PAD_LEFT).' days' : '—' }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $a?->remarks ?: ($a?->purpose ?? $item->activityType?->name) }}</td>
                                <td class="px-3 py-2 capitalize text-slate-600">{{ str_replace('_', ' ', $a?->execution?->status ?? 'planned') }}</td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <button type="button" @click="openAllocate({{ $item->id }})" class="font-medium text-brand-600 hover:underline">Edit</button>
                                    @if ($a)
                                        <span class="text-slate-300">·</span>
                                        <a href="{{ route('monthly-visits.execution', $a) }}" class="font-medium text-slate-600 hover:underline">Execute</a>
                                        <span class="text-slate-300">·</span>
                                        <a href="{{ route('monthly-visits.reschedule', $a) }}" class="font-medium text-slate-500 hover:underline">Reschedule</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-[12px] text-slate-400">No allocations yet for this month. Use Allocate on unassigned rows.</td>
                            </tr>
                        @endforelse
                        @if ($assigned->isNotEmpty())
                            <tr x-show="visibleAssignedCount === 0 && combinedAssignedQuery" x-cloak>
                                <td colspan="9" class="px-4 py-6 text-center text-[12px] text-slate-400">No allocated visits match your search.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Allocation modal --}}
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
            @keydown.escape.window="close()"
        >
            <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-[3px]" @click="close()"></div>
            <div
                class="relative z-10 flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
                @click.stop
            >
                <div class="flex shrink-0 items-start justify-between gap-3 border-b border-emerald-100 bg-gradient-to-r from-emerald-50 via-sky-50 to-teal-50 px-5 py-4">
                    <div>
                        <p class="text-[14px] font-semibold text-navy-900" x-text="current ? (current.status === 'assigned' ? 'Edit allocation' : 'Allocate field visit') : 'Allocate field visit'"></p>
                        <p class="mt-0.5 text-[12px] text-slate-600" x-text="current ? (current.entity_label + ' · ' + (current.activity || current.category)) : ''"></p>
                        <p class="mt-1 text-[10px] text-slate-500">Working days exclude Fri–Sat &amp; national/govt holidays · same person cannot cover two places on overlapping dates</p>
                    </div>
                    <button type="button" @click="close()" class="rounded-lg p-1.5 text-slate-400 hover:bg-white/80 hover:text-slate-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <template x-if="current">
                    <form method="POST" :action="current.assign_url" class="flex min-h-0 flex-1 flex-col">
                        @csrf
                        <input type="hidden" name="duration_mode" value="working">
                        <input type="hidden" name="duration_days" :value="autoDays">

                        <div class="grid min-h-0 flex-1 gap-0 overflow-y-auto lg:grid-cols-5">
                            {{-- Schedule column --}}
                            <div class="space-y-3 border-b border-slate-100 p-4 lg:col-span-2 lg:border-b-0 lg:border-r">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Visit window</p>
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Start date</label>
                                        <input type="date" name="start_date" required x-model="form.start_date" class="block w-full rounded-lg border-slate-200 text-[13px]">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-slate-600">End date</label>
                                        <input type="date" name="end_date" required x-model="form.end_date" class="block w-full rounded-lg border-slate-200 text-[13px]">
                                    </div>
                                </div>

                                <div class="rounded-xl border border-sky-100 bg-sky-50/70 px-3 py-2.5">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <p class="text-[11px] font-medium text-sky-800">Auto working days</p>
                                        <p class="text-[18px] font-semibold tabular-nums text-navy-900" x-text="autoDays"></p>
                                    </div>
                                    <p class="mt-0.5 text-[10px] text-sky-700/80" x-text="durationHint"></p>
                                </div>

                                <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-amber-100 bg-amber-50/60 px-3 py-2.5">
                                    <input type="checkbox" name="count_off_days" value="1" x-model="form.count_off_days" class="mt-0.5 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                    <span>
                                        <span class="block text-[12px] font-medium text-amber-950">Special request — count off days</span>
                                        <span class="mt-0.5 block text-[10px] text-amber-800/80">Include Fri/Sat &amp; national/govt holidays as working days for this visit only.</span>
                                    </span>
                                </label>

                                <div x-show="rangeHolidays.length" class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Off days in range</p>
                                    <ul class="mt-1 max-h-24 space-y-0.5 overflow-y-auto text-[11px] text-slate-600">
                                        <template x-for="h in rangeHolidays" :key="h.date + h.type">
                                            <li>
                                                <span class="font-medium tabular-nums" x-text="h.date"></span>
                                                <span x-text="' · ' + h.name"></span>
                                                <span class="text-slate-400" x-text="' (' + h.type + ')'"></span>
                                            </li>
                                        </template>
                                        <template x-for="d in rangeWeekends" :key="d">
                                            <li><span class="font-medium tabular-nums" x-text="d"></span> · Weekly off (Fri/Sat)</li>
                                        </template>
                                    </ul>
                                </div>

                                <div>
                                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Last audit / monitoring upto</label>
                                    <input type="hidden" name="last_audit_upto" :value="form.last_audit_upto">
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] font-semibold text-navy-900" x-text="form.last_audit_upto_label || 'No prior audit on record'"></div>
                                    <p class="mt-1 text-[10px] text-slate-400">
                                        From database: last month this office was audited/monitored
                                        (completed visit, else prior yearly schedule). Not editable.
                                    </p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Purpose (from yearly plan)</label>
                                    <input type="hidden" name="purpose" :value="form.purpose">
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] font-medium text-navy-900" x-text="form.purpose || '—'"></div>
                                    <p class="mt-1 text-[10px] text-slate-400">Taken from the annual activity type — not editable here.</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Remarks</label>
                                    <textarea name="remarks" rows="2" x-model="form.remarks" class="block w-full rounded-lg border-slate-200 text-[13px]"></textarea>
                                </div>
                            </div>

                            {{-- Staff column --}}
                            <div class="flex min-h-0 flex-col p-4 lg:col-span-3">
                                <div class="mb-2 flex flex-wrap items-end justify-between gap-2">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">All staff · free days this month</p>
                                        <p class="text-[10px] text-slate-500">Sorted by most free working days · select one or more for joint visits</p>
                                    </div>
                                    <input
                                        type="search"
                                        x-model="staffQuery"
                                        placeholder="Search staff…"
                                        class="h-8 w-full max-w-[180px] rounded-lg border-slate-200 py-0 text-[12px] sm:w-44"
                                    >
                                </div>

                                <div class="mb-2 flex flex-wrap gap-1.5 text-[10px]">
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700" x-text="selectedCountLabel"></span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-600" x-text="employees.length + ' employees'"></span>
                                </div>

                                <div class="min-h-[220px] flex-1 space-y-1 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/40 p-1.5">
                                    <template x-for="emp in filteredEmployees" :key="emp.id">
                                        <label
                                            class="flex cursor-pointer items-center gap-2.5 rounded-lg border px-2.5 py-2 transition"
                                            :class="rowClass(emp)"
                                        >
                                            <input
                                                type="checkbox"
                                                name="employee_ids[]"
                                                :value="emp.id"
                                                class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                x-model.number="visitorIds"
                                            >
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                    <span class="truncate text-[12px] font-semibold text-navy-900" x-text="emp.name"></span>
                                                    <span class="truncate text-[10px] text-slate-400" x-text="emp.title || ''"></span>
                                                </div>
                                                <p class="mt-0.5 text-[10px] text-rose-600" x-show="isBusyInRange(emp)" x-text="busyLabel(emp)"></p>
                                            </div>
                                            <div class="shrink-0 text-right">
                                                <p class="text-[13px] font-bold tabular-nums" :class="emp.free_days > 0 ? 'text-emerald-700' : 'text-rose-600'" x-text="emp.free_days"></p>
                                                <p class="text-[9px] uppercase tracking-wide text-slate-400">free</p>
                                                <p class="text-[9px] tabular-nums text-slate-400" x-text="emp.booked_days + ' booked'"></p>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                                <p class="mt-1.5 text-[10px] text-rose-600" x-show="visitorIds.length === 0">Select at least one visitor.</p>
                                <p class="mt-1 text-[10px] text-amber-700" x-show="hasLiveConflict">One or more selected staff already have an overlapping visit in this date range.</p>
                            </div>
                        </div>

                        <div x-show="hasConflict" class="shrink-0 border-t border-rose-100 bg-rose-50 px-4 py-2.5 text-[12px] text-rose-900">
                            <p class="font-semibold" x-text="conflictWarning"></p>
                            @if ($conflictFlash)
                                <ul class="mt-1 list-disc pl-4 text-[11px] text-rose-800">
                                    @foreach ($conflictFlash as $c)
                                        <li>
                                            {{ is_array($c) ? ($c['names'] ?? '') : '' }}:
                                            {{ is_array($c) ? ($c['dates'] ?? '') : '' }}
                                            ({{ is_array($c) ? ($c['entity'] ?? '') : '' }})
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <p class="mt-2 text-[11px] font-medium">Override is not allowed. Remove the busy visitor or change dates.</p>
                        </div>

                        <div class="flex shrink-0 justify-end gap-2 border-t border-slate-100 bg-white px-4 py-3">
                            <button type="button" @click="close()" class="rounded-lg px-3.5 py-2 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</button>
                            <button
                                type="submit"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="visitorIds.length === 0 || autoDays < 1 || hasLiveConflict"
                            >
                                Save allocation · <span x-text="autoDays"></span> working day<span x-text="autoDays === 1 ? '' : 's'"></span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

    <script>
        function monthlyAllocate(cfg) {
            const holidayMap = {};
            (cfg.calendar?.holidays || []).forEach((h) => { holidayMap[h.date] = h; });
            const weekendDays = cfg.calendar?.weekend_days || [5, 6];

            return {
                open: false,
                showSpecial: false,
                items: cfg.items || [],
                employees: cfg.employees || [],
                current: null,
                visitorIds: [],
                staffQuery: '',
                listQuery: '',
                unassignedQuery: '',
                assignedQuery: '',
                form: {
                    start_date: '',
                    end_date: '',
                    count_off_days: false,
                    last_audit_upto: '',
                    last_audit_upto_label: '',
                    purpose: '',
                    remarks: '',
                },
                hasConflict: !!cfg.hasConflict,
                conflictWarning: cfg.conflictWarning || '',
                init() {
                    if (cfg.openId) this.openAllocate(cfg.openId, true);
                },
                rowMatch(haystack, query) {
                    const q = (query || '').toLowerCase().trim();
                    if (!q) return true;
                    const text = (haystack || '').toLowerCase();
                    return q.split(/\s+/).every((token) => text.includes(token));
                },
                get combinedUnassignedQuery() {
                    return [this.listQuery, this.unassignedQuery].filter(Boolean).join(' ').trim();
                },
                get combinedAssignedQuery() {
                    return [this.listQuery, this.assignedQuery].filter(Boolean).join(' ').trim();
                },
                get visibleUnassignedCount() {
                    const q = this.combinedUnassignedQuery;
                    const rows = document.querySelectorAll('.unassigned-row');
                    if (!rows.length) return 0;
                    if (!q) return rows.length;
                    let n = 0;
                    rows.forEach((el) => {
                        if (this.rowMatch(el.dataset.search, q)) n++;
                    });
                    return n;
                },
                get visibleAssignedCount() {
                    const q = this.combinedAssignedQuery;
                    const rows = document.querySelectorAll('.assigned-row');
                    if (!rows.length) return 0;
                    if (!q) return rows.length;
                    let n = 0;
                    rows.forEach((el) => {
                        if (this.rowMatch(el.dataset.search, q)) n++;
                    });
                    return n;
                },
                openAllocate(id, useOld = false) {
                    const item = this.items.find((i) => Number(i.id) === Number(id));
                    if (!item) return;
                    this.current = item;
                    this.staffQuery = '';
                    this.visitorIds = useOld && cfg.oldVisitorIds?.length
                        ? cfg.oldVisitorIds.map(Number)
                        : (item.visitor_ids || []).map(Number);
                    this.form = {
                        start_date: (useOld && cfg.oldStart) || item.start_date,
                        end_date: (useOld && cfg.oldEnd) || item.end_date,
                        count_off_days: useOld ? !!cfg.oldCountOffDays : !!item.count_off_days,
                        last_audit_upto: item.last_audit_upto || '',
                        last_audit_upto_label: item.last_audit_upto_label || 'No prior audit on record',
                        purpose: item.purpose || item.activity || '',
                        remarks: (useOld && cfg.oldRemarks) || item.remarks || '',
                    };
                    if (!useOld) {
                        this.hasConflict = false;
                        this.conflictWarning = '';
                    }
                    this.open = true;
                },
                close() {
                    this.open = false;
                    this.current = null;
                },
                parseYmd(s) {
                    if (!s) return null;
                    const [y, m, d] = s.split('-').map(Number);
                    return new Date(y, m - 1, d);
                },
                fmt(date) {
                    const y = date.getFullYear();
                    const m = String(date.getMonth() + 1).padStart(2, '0');
                    const d = String(date.getDate()).padStart(2, '0');
                    return `${y}-${m}-${d}`;
                },
                isOffDay(date) {
                    const key = this.fmt(date);
                    if (holidayMap[key]) return true;
                    return weekendDays.includes(date.getDay());
                },
                eachDay(startStr, endStr, fn) {
                    const start = this.parseYmd(startStr);
                    const end = this.parseYmd(endStr);
                    if (!start || !end || end < start) return;
                    const cur = new Date(start);
                    while (cur <= end) {
                        fn(new Date(cur));
                        cur.setDate(cur.getDate() + 1);
                    }
                },
                get autoDays() {
                    let n = 0;
                    this.eachDay(this.form.start_date, this.form.end_date, (d) => {
                        if (this.form.count_off_days || !this.isOffDay(d)) n++;
                    });
                    return n;
                },
                get durationHint() {
                    if (!this.form.start_date || !this.form.end_date) return 'Pick start and end dates';
                    if (this.autoDays < 1) return 'No countable days in this range';
                    return this.form.count_off_days
                        ? 'Special request: every calendar day counts'
                        : 'Fri/Sat & holidays excluded automatically';
                },
                get rangeHolidays() {
                    const list = [];
                    this.eachDay(this.form.start_date, this.form.end_date, (d) => {
                        const key = this.fmt(d);
                        if (holidayMap[key]) list.push(holidayMap[key]);
                    });
                    return list;
                },
                get rangeWeekends() {
                    const list = [];
                    this.eachDay(this.form.start_date, this.form.end_date, (d) => {
                        if (weekendDays.includes(d.getDay()) && !holidayMap[this.fmt(d)]) {
                            list.push(this.fmt(d));
                        }
                    });
                    return list;
                },
                overlaps(aStart, aEnd, bStart, bEnd) {
                    return aStart <= bEnd && aEnd >= bStart;
                },
                isBusyInRange(emp) {
                    const s = this.form.start_date;
                    const e = this.form.end_date;
                    if (!s || !e) return false;
                    return (emp.busy_ranges || []).some((r) => this.overlaps(s, e, r.start, r.end));
                },
                busyLabel(emp) {
                    const hit = (emp.busy_ranges || []).find((r) =>
                        this.overlaps(this.form.start_date, this.form.end_date, r.start, r.end)
                    );
                    if (!hit) return '';
                    return `Busy ${hit.start} → ${hit.end}` + (hit.entity ? ` · ${hit.entity}` : '');
                },
                get hasLiveConflict() {
                    return this.visitorIds.some((id) => {
                        const emp = this.employees.find((e) => Number(e.id) === Number(id));
                        return emp && this.isBusyInRange(emp);
                    });
                },
                get filteredEmployees() {
                    const q = (this.staffQuery || '').toLowerCase().trim();
                    let list = [...this.employees];
                    if (q) {
                        list = list.filter((e) =>
                            (e.name || '').toLowerCase().includes(q) ||
                            (e.title || '').toLowerCase().includes(q)
                        );
                    }
                    return list.sort((a, b) => {
                        const aBusy = this.isBusyInRange(a) ? 1 : 0;
                        const bBusy = this.isBusyInRange(b) ? 1 : 0;
                        if (aBusy !== bBusy) return aBusy - bBusy;
                        return (b.free_days || 0) - (a.free_days || 0);
                    });
                },
                get selectedCountLabel() {
                    return `${this.visitorIds.length} selected`;
                },
                rowClass(emp) {
                    const selected = this.visitorIds.includes(Number(emp.id));
                    const busy = this.isBusyInRange(emp);
                    if (selected && busy) return 'border-amber-300 bg-amber-50 ring-1 ring-amber-200';
                    if (selected) return 'border-emerald-300 bg-emerald-50 ring-1 ring-emerald-200';
                    if (busy) return 'border-rose-100 bg-rose-50/50 opacity-90';
                    return 'border-transparent bg-white hover:border-slate-200';
                },
            };
        }
    </script>
</x-app-layout>
