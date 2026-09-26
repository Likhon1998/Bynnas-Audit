<x-app-layout>
    <div
        class="px-3 py-3 lg:px-5"
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
            oldLockSchedule: @js((bool) old('lock_schedule', true)),
            hasConflict: @js((bool) $conflictWarning),
            conflictWarning: @js($conflictWarning),
        })"
    >
        {{-- Header + period controls on one compact row --}}
        <div class="mb-2 flex flex-wrap items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1.5">
            <h1 class="shrink-0 text-[13px] font-semibold tracking-tight text-navy-900">
                {{ ($officerView ?? false) ? 'My monthly visits' : 'Monthly Field Visits' }}
            </h1>
            @if ($officerView ?? false)
                <span class="hidden text-[11px] text-slate-500 sm:inline">Your allocated shakhas</span>
            @endif

            <form method="GET" action="{{ route('monthly-visits.index') }}" class="flex items-center gap-1.5">
                <select name="fy" class="h-7 rounded-md border-slate-200 !py-0 pl-2 pr-6 text-[12px]" onchange="this.form.submit()" title="Financial year">
                    @foreach ($availablePlans as $p)
                        <option value="{{ $p->fy_label }}" @selected($p->fy_label === $plan->fy_label)>{{ $p->fy_label }}</option>
                    @endforeach
                </select>
                <select name="month" class="h-7 w-[7.25rem] rounded-md border-slate-200 !py-0 pl-2 pr-6 text-[12px]" onchange="this.form.submit()" title="Month">
                    @foreach ($monthOptions as $opt)
                        <option value="{{ $opt['index'] }}" @selected((int) $opt['index'] === (int) $monthIndex)>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </form>

            <div class="relative min-w-[10rem] flex-1">
                <svg class="pointer-events-none absolute left-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                <input
                    type="search"
                    x-model="listQuery"
                    placeholder="Search branch, visitor, status…"
                    class="h-7 w-full rounded-md border-slate-200 py-0 pl-7 pr-7 text-[12px]"
                    autocomplete="off"
                >
                <button
                    type="button"
                    x-show="listQuery"
                    x-cloak
                    @click="listQuery = ''"
                    class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[11px] text-slate-500 hover:text-slate-700"
                >Clear</button>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-1">
                @can('monthly_visits.manage')
                    <a href="{{ route('monthly-visits.people', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="inline-flex h-7 items-center rounded-md border border-navy-900 bg-navy-900 px-2 text-[12px] font-semibold text-white hover:bg-navy-800">
                        Who visits where
                    </a>
                @endcan
                @canany(['calendar.manage', 'monthly_visits.manage', 'monthly_visits.execute'])
                    <a href="{{ route('calendar.index', ['month' => (int) ($fy->months()[$monthIndex]['month'] ?? now('Asia/Dhaka')->month), 'year' => (int) ($fy->months()[$monthIndex]['year'] ?? now('Asia/Dhaka')->year)]) }}" class="inline-flex h-7 items-center rounded-md border border-sky-200 bg-sky-50 px-2 text-[12px] font-medium text-sky-800 hover:bg-sky-100">
                        Calendar
                    </a>
                @endcanany
                <details class="relative">
                    <summary class="inline-flex h-7 cursor-pointer list-none items-center gap-1 rounded-md border border-slate-200 bg-white px-2 text-[12px] font-medium text-slate-700 hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                        Export
                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="absolute right-0 z-40 mt-1 w-40 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                        <a href="{{ route('monthly-visits.schedule.print', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="block px-3 py-1.5 text-[12px] text-slate-700 hover:bg-slate-50">Print</a>
                        <a href="{{ route('monthly-visits.schedule.pdf', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="block px-3 py-1.5 text-[12px] text-slate-700 hover:bg-slate-50">PDF</a>
                        <a href="{{ route('monthly-visits.schedule.doc', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="block px-3 py-1.5 text-[12px] text-slate-700 hover:bg-slate-50">DOC</a>
                        <a href="{{ route('monthly-visits.schedule.excel', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="block px-3 py-1.5 text-[12px] text-slate-700 hover:bg-slate-50">Excel</a>
                        <a href="{{ route('monthly-visits.report', ['fy' => $plan->fy_label, 'month' => $monthIndex, 'type' => 'schedule']) }}" class="block border-t border-slate-100 px-3 py-1.5 text-[12px] text-slate-700 hover:bg-slate-50">Reports</a>
                    </div>
                </details>
                @can('monthly_visits.manage')
                    <form method="POST" action="{{ route('monthly-visits.generate') }}">
                        @csrf
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <input type="hidden" name="month" value="{{ $monthIndex }}">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2 text-[12px] font-medium text-slate-700 hover:bg-slate-50" title="Pull latest rows from yearly plan">
                            Re-sync
                        </button>
                    </form>
                    <form
                        method="POST"
                        action="{{ route('monthly-visits.bulk-allocate') }}"
                        data-bynnas-confirm="Auto-allocate {{ $monthLabel }} with conflict-safe dates? Existing non-completed plans may be rebalanced so every office is covered. The same person will not be placed on overlapping dates."
                        data-bynnas-confirm-title="Auto-allocate {{ $monthLabel }}?"
                        data-bynnas-confirm-ok="Auto-allocate"
                        data-bynnas-confirm-tone="emerald"
                    >
                        @csrf
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <input type="hidden" name="month" value="{{ $monthIndex }}">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md bg-emerald-600 px-2.5 text-[12px] font-medium text-white hover:bg-emerald-500">
                            Auto-allocate
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-3 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-[12px] text-rose-800">{{ $errors->first() }}</div>
        @endif
        @unless ($plan->generated_at)
            <div class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] text-amber-900">
                Yearly plan is not generated yet — create it under Annual Audit first.
            </div>
        @endunless

        @if (($officerView ?? false) && ! ($employeeLinked ?? true))
            <div class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] text-amber-900">
                Your login is not linked to an organogram employee, so no visit allocations can be matched. Ask Super Admin to link your employee on Users &amp; Access.
            </div>
        @endif

        {{-- Status chips --}}
        <div class="mb-2 flex flex-wrap items-center gap-1.5 text-[12px]">
            @foreach ([
                ['label' => ($officerView ?? false) ? 'My visits' : 'Planned', 'value' => $performance['totals']['planned'], 'class' => 'bg-white text-navy-900 ring-slate-200'],
                ...(($officerView ?? false) ? [] : [
                    ['label' => 'Assigned', 'value' => $performance['totals']['assigned'], 'class' => 'bg-emerald-50 text-emerald-800 ring-emerald-100'],
                    ['label' => 'Unassigned', 'value' => $performance['totals']['pending'], 'class' => 'bg-amber-50 text-amber-900 ring-amber-100'],
                ]),
                ['label' => 'Completed', 'value' => $performance['totals']['completed'], 'class' => 'bg-sky-50 text-sky-800 ring-sky-100'],
                ['label' => 'Cancelled', 'value' => $performance['totals']['cancelled'], 'class' => 'bg-rose-50 text-rose-800 ring-rose-100'],
                ['label' => 'Late', 'value' => $performance['totals']['overdue'], 'class' => 'bg-orange-50 text-orange-900 ring-orange-100'],
            ] as $chip)
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 font-medium ring-1 {{ $chip['class'] }}">
                    {{ $chip['label'] }}
                    <strong class="tabular-nums">{{ number_format($chip['value']) }}</strong>
                </span>
            @endforeach
        </div>

        <div class="grid min-h-0 items-start gap-2 {{ ($officerView ?? false) ? '' : 'xl:grid-cols-12' }}">
            {{-- Unassigned --}}
            @can('monthly_visits.manage')
            <section class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm xl:col-span-4">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3 py-2" :class="queueMode === 'allocated' ? 'bg-sky-50/70' : 'bg-amber-50/60'">
                    <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5">
                        <button
                            type="button"
                            @click="queueMode = 'waiting'"
                            class="h-7 rounded-md px-2.5 text-[12px] font-semibold"
                            :class="queueMode === 'waiting' ? 'bg-navy-900 text-white' : 'text-slate-600 hover:bg-slate-50'"
                        >Waiting {{ $unassigned->count() }}</button>
                        <button
                            type="button"
                            @click="queueMode = 'allocated'"
                            class="h-7 rounded-md px-2.5 text-[12px] font-semibold"
                            :class="queueMode === 'allocated' ? 'bg-navy-900 text-white' : 'text-slate-600 hover:bg-slate-50'"
                        >Allocated {{ $assigned->count() }}</button>
                    </div>
                    <button
                        type="button"
                        x-show="queueMode === 'waiting'"
                        @click="showSpecial = !showSpecial"
                        class="h-7 rounded-md border border-slate-200 bg-white px-2 text-[13px] font-medium text-slate-700 hover:bg-slate-50"
                        x-text="showSpecial ? 'Cancel special' : '+ Special'"
                    ></button>
                </div>
                <p class="border-b border-slate-100 px-3 py-1.5 text-[12px] text-slate-500">
                    <span x-show="queueMode === 'waiting'">Risky shakhas are listed first. Allocate moves them to Allocated, where you can still edit.</span>
                    <span x-show="queueMode === 'allocated'" x-cloak>Allocated visits. Edit one to change the visitor or dates.</span>
                </p>

                <div x-show="queueMode === 'waiting'">
                <div x-show="showSpecial" x-cloak class="border-b border-slate-100 bg-slate-50 px-3 py-2.5">
                    <form method="POST" action="{{ route('monthly-visits.special.store') }}" class="grid gap-2 sm:grid-cols-2">
                        @csrf
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <input type="hidden" name="month" value="{{ $monthIndex }}">
                        <div>
                            <label class="mb-0.5 block text-xs font-medium text-slate-500">Type</label>
                            <select name="activity_type_id" required class="h-8 w-full rounded-md border-slate-200 text-[12px]">
                                @foreach ($activityTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-0.5 block text-xs font-medium text-slate-500">Branch / entity</label>
                            <input type="text" name="entity_label" required placeholder="e.g. Special audit — Uttara" class="h-8 w-full rounded-md border-slate-200 text-[12px]">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-0.5 block text-xs font-medium text-slate-500">Notes</label>
                            <div class="flex gap-2">
                                <input type="text" name="notes" placeholder="Optional" class="h-8 min-w-0 flex-1 rounded-md border-slate-200 text-[12px]">
                                <button type="submit" class="h-8 shrink-0 rounded-md bg-navy-900 px-3 text-[12px] font-medium text-white hover:bg-navy-800">Add</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-hidden">
                    <table class="w-full table-fixed text-left">
                        <thead class="sticky top-0 z-10 border-b border-slate-100 bg-white">
                            <tr class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="w-8 px-2 py-2">#</th>
                                <th class="px-2 py-2">Branch / entity</th>
                                <th class="w-[5.5rem] px-2 py-2 text-right"></th>
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
                                    class="text-[12px] unassigned-row hover:bg-slate-50"
                                    data-search="{{ e($unassignedSearch) }}"
                                    x-show="rowVisible('unassigned', $el)"
                                >
                                    <td class="px-2 py-2 align-top text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-2 py-2 align-top">
                                        @if ($item->schedulable instanceof \App\Models\Shakha)
                                            <x-shakha-name
                                                :name="$item->entity_label"
                                                :category="$item->schedulable->riskCategory()"
                                                class="text-[12px]"
                                            />
                                        @else
                                            <p class="break-words font-medium text-navy-900">{{ $item->entity_label }}</p>
                                        @endif
                                        <p class="mt-0.5 text-[11px] text-slate-500">{{ $item->activityType?->name ?: str_replace('_', ' ', $item->category) }}</p>
                                    </td>
                                    <td class="px-2 py-2 text-right align-middle">
                                        <button
                                            type="button"
                                            @click="openAllocate({{ $item->id }})"
                                            class="inline-flex h-7 items-center rounded-md bg-navy-900 px-2.5 text-[12px] font-semibold text-white hover:bg-navy-800"
                                        >Allocate</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-8 text-center text-[13px] text-slate-500">
                                        @if ($items->isEmpty())
                                            @if ($plan->generated_at)
                                                No yearly schedules for {{ $monthLabel }}.
                                            @else
                                                Generate the yearly plan first.
                                            @endif
                                        @else
                                            All items for {{ $monthLabel }} are allocated.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                            @if ($unassigned->isNotEmpty())
                                <tr x-show="visibleUnassignedCount === 0 && listQuery" x-cloak>
                                    <td colspan="3" class="px-3 py-6 text-center text-[13px] text-slate-500">No matches.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/80 px-3 py-2 text-[12px]">
                    <span class="text-slate-500" x-text="pageLabel('unassigned')"></span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button type="button" @click="shiftPage('unassigned', -1)" :disabled="unassignedPage <= 1" class="h-7 rounded-md border border-slate-200 bg-white px-2 font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40">Previous</button>
                        <template x-for="n in pageNumbers('unassigned')" :key="'unassigned-page-' + n">
                            <button
                                type="button"
                                @click="goPage('unassigned', n)"
                                class="inline-flex h-7 min-w-7 items-center justify-center rounded-md border px-1.5 font-semibold tabular-nums"
                                :class="n === unassignedPage ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                x-text="n"
                            ></button>
                        </template>
                        <button type="button" @click="shiftPage('unassigned', 1)" :disabled="unassignedPage >= pageCount('unassigned')" class="h-7 rounded-md border border-slate-200 bg-white px-2 font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40">Next</button>
                    </div>
                </div>
                </div>

                <div x-show="queueMode === 'allocated'" x-cloak>
                    <div class="overflow-x-hidden">
                        <table class="w-full table-fixed text-left">
                            <thead class="sticky top-0 z-10 border-b border-slate-100 bg-white">
                                <tr class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="w-8 px-2 py-2">#</th>
                                    <th class="px-2 py-2">Branch / entity</th>
                                    <th class="w-[5.5rem] px-2 py-2 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($assigned as $i => $item)
                                    @php
                                        $a = $item->assignment;
                                        $queueSearch = strtolower(trim(implode(' ', [
                                            $item->entity_label,
                                            $a?->visitorNames(' '),
                                            $item->activityType?->name,
                                            str_replace('_', ' ', $item->category),
                                        ])));
                                    @endphp
                                    <tr
                                        class="text-[12px] queue-row hover:bg-slate-50"
                                        data-search="{{ e($queueSearch) }}"
                                        x-show="rowVisible('queue', $el)"
                                    >
                                        <td class="px-2 py-2 align-top text-slate-400">{{ $i + 1 }}</td>
                                        <td class="px-2 py-2 align-top">
                                            @if ($item->schedulable instanceof \App\Models\Shakha)
                                                <x-shakha-name
                                                    :name="$item->entity_label"
                                                    :category="$item->schedulable->riskCategory()"
                                                    class="text-[12px]"
                                                />
                                            @else
                                                <p class="break-words font-medium text-navy-900">{{ $item->entity_label }}</p>
                                            @endif
                                            <p class="mt-0.5 text-[11px] text-slate-500">{{ $a?->visitorList()->map(fn ($e) => trim($e->name.($e->position?->title ? ' · '.$e->position->title : '')))->implode(', ') ?: 'No visitor' }}</p>
                                        </td>
                                        <td class="px-2 py-2 text-right align-middle">
                                            <button
                                                type="button"
                                                @click="openAllocate({{ $item->id }})"
                                                class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50"
                                            >Edit</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-8 text-center text-[13px] text-slate-500">Nothing allocated yet.</td>
                                    </tr>
                                @endforelse
                                @if ($assigned->isNotEmpty())
                                    <tr x-show="visibleQueueCount === 0 && listQuery" x-cloak>
                                        <td colspan="3" class="px-3 py-6 text-center text-[13px] text-slate-500">No matches.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/80 px-3 py-2 text-[12px]">
                        <span class="text-slate-500" x-text="pageLabel('queue')"></span>
                        <div class="flex flex-wrap items-center gap-1">
                            <button type="button" @click="shiftPage('queue', -1)" :disabled="queuePage <= 1" class="h-7 rounded-md border border-slate-200 bg-white px-2 font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40">Previous</button>
                            <template x-for="n in pageNumbers('queue')" :key="'queue-page-' + n">
                                <button
                                    type="button"
                                    @click="goPage('queue', n)"
                                    class="inline-flex h-7 min-w-7 items-center justify-center rounded-md border px-1.5 font-semibold tabular-nums"
                                    :class="n === queuePage ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                    x-text="n"
                                ></button>
                            </template>
                            <button type="button" @click="shiftPage('queue', 1)" :disabled="queuePage >= pageCount('queue')" class="h-7 rounded-md border border-slate-200 bg-white px-2 font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40">Next</button>
                        </div>
                    </div>
                </div>
            </section>
            @endcan

            {{-- Assigned --}}
            <section class="flex min-h-0 flex-col rounded-xl border border-slate-200 bg-white shadow-sm {{ ($officerView ?? false) ? '' : 'xl:col-span-8' }}">
                <div class="border-b border-slate-100 bg-sky-50/50 px-3 py-2">
                    <h2 class="text-[13px] font-semibold text-navy-900">{{ ($officerView ?? false) ? 'My allocated shakhas' : 'Allocated this month' }}</h2>
                    <p class="text-[12px] text-slate-500">
                        <span x-text="visibleAssignedCount"></span> of {{ $assigned->count() }} assigned
                        <span x-show="listQuery" x-cloak> · filtered</span>
                    </p>
                </div>
                <div class="overflow-visible">
                    <table class="w-full table-fixed text-left">
                        <thead class="sticky top-0 z-10 border-b border-slate-100 bg-white">
                            <tr class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="w-8 px-2 py-2">#</th>
                                <th class="w-[18%] px-2 py-2">Visitor</th>
                                <th class="px-2 py-2">Branch / entity</th>
                                <th class="w-[7.5rem] px-2 py-2">When</th>
                                <th class="w-[6.75rem] px-2 py-2">Status</th>
                                <th class="w-[6.25rem] px-2 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($assigned as $i => $item)
                                @php
                                    $a = $item->assignment;
                                    $rawStatus = $a?->execution?->status ?? 'planned';
                                    $workLink = $a ? ($visitWorkLinks[$a->id] ?? null) : null;
                                    if ($a && app(\App\Services\MonthlyWorklistService::class)->assignmentIsLate($a, $workLink)) {
                                        $rawStatus = 'delayed';
                                    }
                                    $statusLabel = match ($rawStatus) {
                                        'ongoing', 'in_progress' => 'Ongoing',
                                        'in_review' => 'In review',
                                        'completed' => 'Completed',
                                        'delayed' => 'Delayed',
                                        'cancelled' => 'Cancelled',
                                        'rescheduled' => 'Rescheduled',
                                        default => 'Planned',
                                    };
                                    $statusClass = match ($rawStatus) {
                                        'completed' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                                        'in_progress', 'ongoing' => 'bg-sky-50 text-sky-800 ring-sky-100',
                                        'in_review' => 'bg-violet-50 text-violet-800 ring-violet-100',
                                        'delayed', 'rescheduled' => 'bg-amber-50 text-amber-900 ring-amber-100',
                                        'cancelled' => 'bg-rose-50 text-rose-800 ring-rose-100',
                                        default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                    };
                                    $when = '—';
                                    if ($a?->start_date && $a?->end_date) {
                                        $when = $a->start_date->isSameDay($a->end_date)
                                            ? $a->start_date->format('d M')
                                            : $a->start_date->format('d').'–'.$a->end_date->format('d M');
                                    }
                                    $assignedSearch = strtolower(trim(implode(' ', [
                                        $a?->visitorNames(' '),
                                        $item->entity_label,
                                        $a?->last_audit_upto?->format('F Y'),
                                        $a?->visitDateRangeLabel(),
                                        $a?->remarks,
                                        $a?->purpose,
                                        $item->activityType?->name,
                                        $statusLabel,
                                        $item->isSpecial() ? 'special' : '',
                                    ])));
                                @endphp
                                <tr
                                    class="text-[12px] assigned-row hover:bg-slate-50"
                                    data-search="{{ e($assignedSearch) }}"
                                    x-show="rowVisible('assigned', $el)"
                                >
                                    <td class="px-2 py-2 align-top text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-2 py-2 align-top leading-snug text-navy-900">
                                        @forelse ($a?->visitorList() ?? collect() as $visitor)
                                            <p class="font-medium">{{ $visitor->name }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $visitor->position?->title ?: 'No position' }}</p>
                                        @empty
                                            <p class="text-slate-400">—</p>
                                        @endforelse
                                    </td>
                                    <td class="px-2 py-2 align-top">
                                        @if ($item->schedulable instanceof \App\Models\Shakha)
                                            <x-shakha-name
                                                :name="$item->entity_label"
                                                :category="$item->schedulable->riskCategory()"
                                                class="text-[12px]"
                                            />
                                        @else
                                            <p class="break-words font-semibold text-navy-900">{{ $item->entity_label }}</p>
                                        @endif
                                        <p class="mt-0.5 truncate text-[11px] text-slate-500">
                                            {{ $a?->purpose ?? $item->activityType?->name }}
                                            @if ($a?->last_audit_upto) · last {{ $a->last_audit_upto->format('M Y') }} @endif
                                        </p>
                                    </td>
                                    <td class="px-2 py-2 align-top text-slate-700">
                                        <p class="font-medium tabular-nums">{{ $when }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $a?->duration_days ? $a->duration_days.' days' : '' }}</p>
                                    </td>
                                    <td class="px-2 py-2 align-top">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $statusClass }}">{{ $statusLabel }}</span>
                                        @if ($a?->is_locked)
                                            <span class="mt-1 inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800 ring-1 ring-amber-200">Locked</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-right align-middle">
                                        @php $work = $a ? ($visitWorkLinks[$a->id] ?? null) : null; @endphp
                                        <details class="visit-actions relative inline-block text-left" @toggle="placeActionMenu($event)">
                                            <summary class="inline-flex h-7 cursor-pointer list-none items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-semibold text-slate-700 hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                                                Actions
                                                <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </summary>
                                            <div data-action-menu class="fixed z-[80] hidden w-44 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 text-left shadow-xl">
                                                @if (($officerView ?? false) && $work && ($work['supports_audit_work'] ?? $work['is_shakha'] ?? false) && ($work['checklist_url'] ?? null))
                                                    <p class="px-3 py-1 text-[11px] font-semibold {{ ($work['checklist_ready'] ?? false) ? 'text-emerald-700' : 'text-amber-800' }}">
                                                        Checklist {{ $work['checklist_done'] }}/{{ $work['checklist_required'] }}
                                                    </p>
                                                    <a href="{{ $work['checklist_url'] }}" class="block px-3 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Checklist</a>
                                                    <a href="{{ $work['report_url'] }}" class="block px-3 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Report</a>
                                                @endif
                                                @can('monthly_visits.manage')
                                                    @if ($a?->is_locked && ! $a->canBeModifiedBy(auth()->user()))
                                                        <p class="px-3 py-1.5 text-[12px] text-amber-800" title="Locked by {{ $a->lockedBy?->name ?? 'admin' }}">Locked</p>
                                                    @else
                                                        <button type="button" @click="openAllocate({{ $item->id }})" class="block w-full px-3 py-1.5 text-left text-[12px] font-medium text-slate-700 hover:bg-slate-50">Edit allocation</button>
                                                    @endif
                                                @endcan
                                                @if ($a)
                                                    @can('monthly_visits.manage')
                                                        @if ($a->canBeModifiedBy(auth()->user()))
                                                            <a href="{{ route('monthly-visits.reschedule', $a) }}" class="block px-3 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Move dates</a>
                                                            @if ($a->is_locked)
                                                                <form method="POST" action="{{ route('monthly-visits.unlock', $a) }}">
                                                                    @csrf
                                                                    <button type="submit" class="block w-full px-3 py-1.5 text-left text-[12px] font-medium text-amber-800 hover:bg-amber-50">Unlock to correct</button>
                                                                </form>
                                                            @endif
                                                        @endif
                                                    @endcan
                                                @endif
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-8 text-center text-[13px] text-slate-500">
                                        @if ($officerView ?? false)
                                            No visits allocated to you for {{ $monthLabel }}.
                                        @else
                                            No allocations yet. Allocate from the left list or use Auto-allocate.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                            @if ($assigned->isNotEmpty())
                                <tr x-show="visibleAssignedCount === 0 && listQuery" x-cloak>
                                    <td colspan="6" class="px-3 py-6 text-center text-[13px] text-slate-500">No matches.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/80 px-3 py-2 text-[12px]">
                    <span class="text-slate-500" x-text="pageLabel('assigned')"></span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button type="button" @click="shiftPage('assigned', -1)" :disabled="assignedPage <= 1" class="h-7 rounded-md border border-slate-200 bg-white px-2 font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40">Previous</button>
                        <template x-for="n in pageNumbers('assigned')" :key="'assigned-page-' + n">
                            <button
                                type="button"
                                @click="goPage('assigned', n)"
                                class="inline-flex h-7 min-w-7 items-center justify-center rounded-md border px-1.5 font-semibold tabular-nums"
                                :class="n === assignedPage ? 'border-navy-900 bg-navy-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                                x-text="n"
                            ></button>
                        </template>
                        <button type="button" @click="shiftPage('assigned', 1)" :disabled="assignedPage >= pageCount('assigned')" class="h-7 rounded-md border border-slate-200 bg-white px-2 font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40">Next</button>
                    </div>
                </div>
            </section>
        </div>

        {{-- Allocation modal (managers only) --}}
        @can('monthly_visits.manage')
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
            @keydown.escape.window="close()"
        >
            <div class="absolute inset-0 bg-slate-900/50" @click="close()"></div>
            <div class="relative z-10 flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl" @click.stop>
                <div class="flex shrink-0 items-start justify-between gap-2 border-b border-slate-100 px-3 py-2.5">
                    <div>
                        <p class="text-[14px] font-semibold text-navy-900" x-text="current ? (current.status === 'assigned' ? 'Edit allocation' : 'Allocate visit') : 'Allocate visit'"></p>
                        <p class="mt-0.5 text-[12px] text-slate-600" x-text="current ? (current.entity_label + ' · ' + (current.activity || current.category)) : ''"></p>
                        <p class="mt-1 text-xs text-slate-500">
                            Working days follow
                            <a href="{{ route('calendar.index') }}" class="font-semibold text-sky-700 underline hover:text-sky-900">Working Calendar</a>
                            (weekly offs + holidays + internal offs) · one person cannot cover overlapping dates
                        </p>
                    </div>
                    <button type="button" @click="close()" class="rounded-md p-1.5 text-slate-400 hover:bg-slate-50 hover:text-slate-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div
                    x-show="hasConflict || hasLiveConflict"
                    x-cloak
                    class="shrink-0 border-b border-rose-200 bg-rose-50 px-3 py-2 text-[12px] text-rose-950"
                >
                    <p class="font-semibold">Cannot save — same person at two places</p>
                    <p class="mt-0.5 text-[13px]" x-show="hasConflict" x-text="conflictWarning"></p>
                    <ul class="mt-1 list-disc pl-4 text-[13px]" x-show="hasLiveConflict && liveConflictLines.length">
                        <template x-for="line in liveConflictLines" :key="line">
                            <li x-text="line"></li>
                        </template>
                    </ul>
                </div>

                <template x-if="current">
                    <form method="POST" :action="current.assign_url" class="flex min-h-0 flex-1 flex-col">
                        @csrf
                        <input type="hidden" name="duration_mode" value="working">
                        <input type="hidden" name="duration_days" :value="autoDays">

                        <div class="grid min-h-0 flex-1 gap-0 overflow-y-auto lg:grid-cols-5">
                            <div class="space-y-3 border-b border-slate-100 p-4 lg:col-span-2 lg:border-b-0 lg:border-r">
                                <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-500">Visit window</p>
                                <input type="hidden" name="start_date" required :value="form.start_date">
                                <input type="hidden" name="end_date" required :value="form.end_date">
                                @include('calendar.partials.working-range-picker')

                                <div class="flex items-baseline justify-between rounded-md border border-slate-200 bg-slate-50 px-3 py-2">
                                    <div>
                                        <p class="text-[13px] font-medium text-slate-600">Working days</p>
                                        <p class="text-xs text-slate-500" x-text="durationHint"></p>
                                    </div>
                                    <p class="text-[18px] font-semibold tabular-nums text-navy-900" x-text="autoDays"></p>
                                </div>

                                <label class="flex cursor-pointer items-start gap-2 rounded-md border border-slate-200 px-3 py-2">
                                    <input type="checkbox" name="count_off_days" value="1" x-model="form.count_off_days" class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span>
                                        <span class="block text-[12px] font-medium text-slate-800">Count off days</span>
                                        <span class="block text-xs text-slate-500">Include weekly offs &amp; calendar holidays in duration</span>
                                    </span>
                                </label>
                                <div class="rounded-md border border-amber-100 bg-amber-50/70 px-3 py-2 text-[12px] text-amber-950">
                                    Saving locks this visit. The allocated auditor cannot change the visitor or the dates.
                                </div>

                                <div x-show="rangeHolidays.length || rangeWeekends.length" class="rounded-md border border-slate-100 bg-slate-50 px-3 py-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Off days in range (from Working Calendar)</p>
                                    <ul class="mt-1 max-h-24 space-y-0.5 overflow-y-auto text-[13px] text-slate-600">
                                        <template x-for="h in rangeHolidays" :key="h.date + h.type">
                                            <li>
                                                <span class="tabular-nums" x-text="h.date"></span>
                                                <span x-text="' · ' + h.name"></span>
                                                <span class="text-slate-400" x-text="h.type_label ? (' · ' + h.type_label) : ''"></span>
                                            </li>
                                        </template>
                                        <template x-for="d in rangeWeekends" :key="d">
                                            <li><span class="tabular-nums" x-text="d"></span> · Weekly off</li>
                                        </template>
                                    </ul>
                                </div>

                                <div>
                                    <label class="mb-1 block text-[13px] font-medium text-slate-600">Last audit upto</label>
                                    <input type="hidden" name="last_audit_upto" :value="form.last_audit_upto">
                                    <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] font-medium text-navy-900" x-text="form.last_audit_upto_label || 'No prior audit on record'"></div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[13px] font-medium text-slate-600">Purpose</label>
                                    <input type="hidden" name="purpose" :value="form.purpose">
                                    <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] font-medium text-navy-900" x-text="form.purpose || '—'"></div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[13px] font-medium text-slate-600">Remarks</label>
                                    <textarea name="remarks" rows="2" x-model="form.remarks" class="block w-full rounded-md border-slate-200 text-[13px]"></textarea>
                                </div>
                            </div>

                            <div class="flex min-h-0 flex-col p-4 lg:col-span-3">
                                <div class="mb-2 flex flex-wrap items-end justify-between gap-2">
                                    <div>
                                        <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-500">Staff</p>
                                        <p class="text-xs text-slate-500">Free working days this month (Working Calendar) · select one or more</p>
                                    </div>
                                    <input type="search" x-model="staffQuery" placeholder="Search staff…" class="h-8 w-full max-w-[180px] rounded-md border-slate-200 py-0 text-[12px]">
                                </div>

                                <div class="mb-2 flex flex-wrap gap-1.5 text-xs">
                                    <span class="rounded bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700" x-text="selectedCountLabel"></span>
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-slate-600" x-text="employees.length + ' employees'"></span>
                                </div>

                                <div class="min-h-[220px] flex-1 space-y-2 overflow-y-auto rounded-md border border-slate-200 bg-slate-50/50 p-1.5">
                                    <template x-for="group in staffByPosition" :key="group.title">
                                        <div>
                                            <p class="sticky top-0 z-10 bg-slate-100 px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-600" x-text="group.title"></p>
                                            <div class="mt-1 space-y-1">
                                                <template x-for="emp in group.people" :key="emp.id">
                                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-md border px-2.5 py-2 transition" :class="rowClass(emp)">
                                                        <input
                                                            type="checkbox"
                                                            name="employee_ids[]"
                                                            :value="emp.id"
                                                            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                            x-model.number="visitorIds"
                                                        >
                                                        <div class="min-w-0 flex-1">
                                                            <span class="truncate text-[12px] font-semibold text-navy-900" x-text="emp.name"></span>
                                                            <p class="mt-0.5 text-xs text-rose-600" x-show="isBusyInRange(emp)" x-text="busyLabel(emp)"></p>
                                                        </div>
                                                        <div class="shrink-0 text-right">
                                                            <p class="text-[13px] font-bold tabular-nums" :class="emp.free_days > 0 ? 'text-emerald-700' : 'text-rose-600'" x-text="emp.free_days"></p>
                                                            <p class="text-xs uppercase text-slate-400">free</p>
                                                        </div>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <p class="mt-1.5 text-xs text-rose-600" x-show="visitorIds.length === 0">Select at least one visitor.</p>
                                <p class="mt-1 text-xs text-amber-700" x-show="hasLiveConflict">Selected staff have an overlapping visit in this date range.</p>
                            </div>
                        </div>

                        <div x-show="hasConflict || hasLiveConflict" class="shrink-0 border-t border-rose-200 bg-rose-50 px-3 py-2.5 text-[12px] text-rose-950">
                            <p class="font-semibold">Cannot allocate — same person at two places</p>
                            <p class="mt-1 text-[13px]" x-show="hasConflict" x-text="conflictWarning"></p>
                            <ul class="mt-1 list-disc pl-4 text-[13px] text-rose-900" x-show="hasLiveConflict && liveConflictLines.length">
                                <template x-for="line in liveConflictLines" :key="line">
                                    <li x-text="line"></li>
                                </template>
                            </ul>
                            @if ($conflictFlash)
                                <ul class="mt-1 list-disc pl-4 text-[13px] text-rose-900">
                                    @foreach ($conflictFlash as $c)
                                        <li>
                                            <strong>{{ is_array($c) ? ($c['names'] ?? 'Staff') : '' }}</strong>
                                            already booked
                                            {{ is_array($c) ? ($c['dates'] ?? '') : '' }}
                                            at {{ is_array($c) ? ($c['entity'] ?? 'another place') : '' }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <p class="mt-2 text-[13px] font-medium">Change visitors or dates. Overlap is never allowed.</p>
                        </div>

                        <div class="flex shrink-0 justify-end gap-2 border-t border-slate-100 px-3 py-2.5">
                            <button type="button" @click="close()" class="rounded-md px-3 py-2 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</button>
                            <button
                                type="submit"
                                class="rounded-md bg-emerald-600 px-4 py-2 text-[12px] font-semibold text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="visitorIds.length === 0 || autoDays < 1 || hasLiveConflict"
                            >
                                Save · <span x-text="autoDays"></span> day<span x-text="autoDays === 1 ? '' : 's'"></span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
        @endcan
    </div>

    @include('calendar.partials.working-range-picker-js')
    <script>
        function monthlyAllocate(cfg) {
            const cal = window.WorkingCalendarUi.create(cfg.calendar || {});
            const { holidayMap, weekendDays, offDayHint } = cal;

            return {
                open: false,
                showSpecial: false,
                items: cfg.items || [],
                employees: cfg.employees || [],
                current: null,
                visitorIds: [],
                staffQuery: '',
                listQuery: '',
                queueMode: 'waiting',
                pageSize: 8,
                unassignedPage: 1,
                assignedPage: 1,
                queuePage: 1,
                pickerYear: (window.bynnasTime?.nowParts?.() || { year: new Date().getFullYear() }).year,
                pickerMonth: (window.bynnasTime?.nowParts?.() || { month: new Date().getMonth() + 1 }).month,
                pickerOpen: null,
                pickerStyle: 'top: 120px; left: 24px;',
                pickerWeekdays: cal.weekdayLabels,
                form: {
                    start_date: '',
                    end_date: '',
                    count_off_days: false,
                    lock_schedule: true,
                    last_audit_upto: '',
                    last_audit_upto_label: '',
                    purpose: '',
                    remarks: '',
                },
                hasConflict: !!cfg.hasConflict,
                conflictWarning: cfg.conflictWarning || '',
                init() {
                    this.$watch('listQuery', () => {
                        this.unassignedPage = 1;
                        this.assignedPage = 1;
                        this.queuePage = 1;
                    });
                    if (cfg.openId) this.openAllocate(cfg.openId, true);
                },
                placeActionMenu(event) {
                    const details = event.currentTarget;
                    if (!(details instanceof HTMLDetailsElement)) return;
                    document.querySelectorAll('details.visit-actions[open]').forEach((el) => {
                        if (el !== details) el.removeAttribute('open');
                    });
                    const menu = details.querySelector('[data-action-menu]');
                    const summary = details.querySelector('summary');
                    if (!(menu instanceof HTMLElement) || !(summary instanceof HTMLElement)) return;
                    if (!details.open) {
                        menu.style.display = 'none';
                        return;
                    }
                    menu.style.display = 'block';
                    menu.style.visibility = 'hidden';
                    const rect = summary.getBoundingClientRect();
                    const width = 176;
                    const height = menu.offsetHeight || 220;
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const top = spaceBelow < height + 16
                        ? Math.max(8, rect.top - height - 8)
                        : rect.bottom + 6;
                    const left = Math.min(Math.max(8, rect.right - width), window.innerWidth - width - 8);
                    menu.style.top = `${top}px`;
                    menu.style.left = `${left}px`;
                    menu.style.visibility = 'visible';
                },
                get pickerMonthLabel() {
                    return cal.monthLabel(this.pickerYear, this.pickerMonth);
                },
                get pickerCells() {
                    return cal.buildPickerCells(this.pickerYear, this.pickerMonth, this.form.start_date, this.form.end_date);
                },
                get rangeStartLabel() {
                    return cal.formatLabel(this.form.start_date);
                },
                get rangeEndLabel() {
                    return cal.formatLabel(this.form.end_date);
                },
                get pickerHint() {
                    if (this.pickerOpen === 'start') return 'Choose start · emerald = working day, grey/colour = off';
                    if (this.pickerOpen === 'end') return 'Choose end · emerald = working day, grey/colour = off';
                    return '';
                },
                get pickerTitle() {
                    return this.pickerOpen === 'end' ? 'Pick end date' : 'Pick start date';
                },
                openDatePicker(which) {
                    const ymd = which === 'end' ? this.form.end_date : this.form.start_date;
                    const synced = cal.syncMonthFrom(ymd);
                    this.pickerYear = synced.year;
                    this.pickerMonth = synced.month;
                    this.pickerOpen = which;
                    this.$nextTick(() => this.positionDatePicker());
                },
                positionDatePicker() {
                    const btn = this.pickerOpen === 'end' ? this.$refs.endDateBtn : this.$refs.startDateBtn;
                    if (!btn) return;
                    const rect = btn.getBoundingClientRect();
                    const width = 300;
                    const left = Math.min(Math.max(8, rect.left), window.innerWidth - width - 8);
                    let top = rect.bottom + 6;
                    const approxHeight = 360;
                    if (top + approxHeight > window.innerHeight - 8) {
                        top = Math.max(8, rect.top - approxHeight - 6);
                    }
                    this.pickerStyle = `top:${top}px; left:${left}px;`;
                },
                closeDatePicker() {
                    this.pickerOpen = null;
                },
                pickerPrev() {
                    const next = cal.shiftMonth(this.pickerYear, this.pickerMonth, -1);
                    this.pickerYear = next.year;
                    this.pickerMonth = next.month;
                },
                pickerNext() {
                    const next = cal.shiftMonth(this.pickerYear, this.pickerMonth, 1);
                    this.pickerYear = next.year;
                    this.pickerMonth = next.month;
                },
                pickerDayClass(cell) {
                    return cal.pickerDayClass(cell);
                },
                pickerNumClass(cell) {
                    return cal.pickerNumClass(cell);
                },
                pickWorkingDay(cell) {
                    if (!this.pickerOpen) return;
                    if (!cell.inMonth) {
                        const d = cal.parseYmd(cell.date);
                        if (d) {
                            this.pickerYear = d.getFullYear();
                            this.pickerMonth = d.getMonth() + 1;
                        }
                    }
                    const result = cal.applySinglePick(this.pickerOpen, this.form.start_date, this.form.end_date, cell.date);
                    this.form.start_date = result.start;
                    this.form.end_date = result.end;
                    this.pickerOpen = null;
                },
                rowMatch(haystack, query) {
                    const q = (query || '').toLowerCase().trim();
                    if (!q) return true;
                    const text = (haystack || '').toLowerCase();
                    return q.split(/\s+/).every((token) => text.includes(token));
                },
                matchingRows(kind) {
                    const selector = kind === 'unassigned'
                        ? '.unassigned-row'
                        : (kind === 'queue' ? '.queue-row' : '.assigned-row');
                    return Array.from(document.querySelectorAll(selector))
                        .filter((el) => this.rowMatch(el.dataset.search, this.listQuery));
                },
                pageOf(kind) {
                    if (kind === 'unassigned') return this.unassignedPage;
                    if (kind === 'queue') return this.queuePage;
                    return this.assignedPage;
                },
                totalOf(kind) {
                    if (kind === 'unassigned') return this.visibleUnassignedCount;
                    if (kind === 'queue') return this.visibleQueueCount;
                    return this.visibleAssignedCount;
                },
                setPage(kind, page) {
                    if (kind === 'unassigned') this.unassignedPage = page;
                    else if (kind === 'queue') this.queuePage = page;
                    else this.assignedPage = page;
                },
                pageCount(kind) {
                    return Math.max(1, Math.ceil(this.totalOf(kind) / this.pageSize));
                },
                rowVisible(kind, el) {
                    if (!this.rowMatch(el.dataset.search, this.listQuery)) return false;
                    const rows = this.matchingRows(kind);
                    const index = rows.indexOf(el);
                    const start = (Math.max(1, this.pageOf(kind)) - 1) * this.pageSize;
                    return index >= start && index < start + this.pageSize;
                },
                shiftPage(kind, delta) {
                    const pages = this.pageCount(kind);
                    this.setPage(kind, Math.min(pages, Math.max(1, this.pageOf(kind) + delta)));
                },
                goPage(kind, page) {
                    const pages = this.pageCount(kind);
                    this.setPage(kind, Math.min(pages, Math.max(1, Number(page) || 1)));
                },
                pageNumbers(kind) {
                    const total = this.pageCount(kind);
                    return Array.from({ length: total }, (_, index) => index + 1);
                },
                pageLabel(kind) {
                    const total = this.totalOf(kind);
                    const page = this.pageOf(kind);
                    if (!total) return '0 shown';
                    const start = (page - 1) * this.pageSize + 1;
                    const end = Math.min(total, page * this.pageSize);
                    return start + '–' + end + ' of ' + total;
                },
                get visibleUnassignedCount() {
                    const q = this.listQuery;
                    const rows = document.querySelectorAll('.unassigned-row');
                    if (!rows.length) return 0;
                    if (!q) return rows.length;
                    let n = 0;
                    rows.forEach((el) => { if (this.rowMatch(el.dataset.search, q)) n++; });
                    return n;
                },
                get visibleQueueCount() {
                    const q = this.listQuery;
                    const rows = document.querySelectorAll('.queue-row');
                    if (!rows.length) return 0;
                    if (!q) return rows.length;
                    let n = 0;
                    rows.forEach((el) => { if (this.rowMatch(el.dataset.search, q)) n++; });
                    return n;
                },
                get visibleAssignedCount() {
                    const q = this.listQuery;
                    const rows = document.querySelectorAll('.assigned-row');
                    if (!rows.length) return 0;
                    if (!q) return rows.length;
                    let n = 0;
                    rows.forEach((el) => { if (this.rowMatch(el.dataset.search, q)) n++; });
                    return n;
                },
                openAllocate(id, useOld = false) {
                    const item = this.items.find((i) => Number(i.id) === Number(id));
                    if (!item) return;
                    if (item.is_locked && item.can_modify === false) {
                        alert(item.locked_by_name
                            ? ('This visit is locked by ' + item.locked_by_name + '.')
                            : 'This visit is locked.');
                        return;
                    }
                    this.current = item;
                    this.staffQuery = '';
                    this.visitorIds = useOld && cfg.oldVisitorIds?.length
                        ? cfg.oldVisitorIds.map(Number)
                        : (item.visitor_ids || []).map(Number);
                    this.form = {
                        start_date: (useOld && cfg.oldStart) || item.start_date,
                        end_date: (useOld && cfg.oldEnd) || item.end_date,
                        count_off_days: useOld ? !!cfg.oldCountOffDays : !!item.count_off_days,
                        lock_schedule: useOld ? !!cfg.oldLockSchedule : (item.status === 'assigned' ? !!item.is_locked : true),
                        last_audit_upto: item.last_audit_upto || '',
                        last_audit_upto_label: item.last_audit_upto_label || 'No prior audit on record',
                        purpose: item.purpose || item.activity || '',
                        remarks: (useOld && cfg.oldRemarks) || item.remarks || '',
                    };
                    const synced = cal.syncMonthFrom(this.form.start_date);
                    this.pickerYear = synced.year;
                    this.pickerMonth = synced.month;
                    this.pickerOpen = null;
                    if (!useOld) {
                        this.hasConflict = false;
                        this.conflictWarning = '';
                    }
                    this.open = true;
                },
                close() {
                    this.open = false;
                    this.current = null;
                    this.pickerOpen = null;
                },
                parseYmd(s) {
                    return cal.parseYmd(s);
                },
                fmt(date) {
                    return cal.fmt(date);
                },
                isOffDay(date) {
                    return cal.isOffDay(date);
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
                        ? 'Special: every calendar day counts'
                        : offDayHint;
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
                get liveConflictLines() {
                    return this.visitorIds
                        .map((id) => {
                            const emp = this.employees.find((e) => Number(e.id) === Number(id));
                            if (!emp || !this.isBusyInRange(emp)) return null;
                            return `${emp.name}: ${this.busyLabel(emp)}`;
                        })
                        .filter(Boolean);
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
                get staffByPosition() {
                    const groups = new Map();
                    this.filteredEmployees.forEach((emp) => {
                        const title = (emp.title || 'No position').trim();
                        if (!groups.has(title)) groups.set(title, []);
                        groups.get(title).push(emp);
                    });
                    return Array.from(groups.entries())
                        .sort((a, b) => a[0].localeCompare(b[0]))
                        .map(([title, people]) => ({ title, people }));
                },
                get selectedCountLabel() {
                    return `${this.visitorIds.length} selected`;
                },
                rowClass(emp) {
                    const selected = this.visitorIds.includes(Number(emp.id));
                    const busy = this.isBusyInRange(emp);
                    if (selected && busy) return 'border-amber-300 bg-amber-50';
                    if (selected) return 'border-emerald-300 bg-emerald-50';
                    if (busy) return 'border-rose-100 bg-rose-50/40';
                    return 'border-transparent bg-white hover:border-slate-200';
                },
            };
        }
    </script>
</x-app-layout>
