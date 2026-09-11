<x-app-layout>
    <div
        class="px-4 py-4 lg:px-6"
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
        {{-- Header --}}
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div class="min-w-0">
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">
                    {{ ($officerView ?? false) ? 'My monthly visits' : 'Monthly Field Visits' }}
                </h1>
                <p class="text-[11px] text-slate-500">
                    FY {{ $plan->fy_label }} · {{ $monthLabel }}
                    @if ($officerView ?? false)
                        · only shakhas allocated to you
                    @endif
                </p>
            </div>
            <details class="relative">
                <summary class="inline-flex h-8 cursor-pointer list-none items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
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

        {{-- Controls: period · search · actions --}}
        <div class="mb-3 flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-white p-2">
            <form method="GET" action="{{ route('monthly-visits.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="fy" class="h-8 rounded-md border-slate-200 !py-0 pl-2 pr-7 text-[12px]" onchange="this.form.submit()" title="Financial year">
                    @foreach ($availablePlans as $p)
                        <option value="{{ $p->fy_label }}" @selected($p->fy_label === $plan->fy_label)>{{ $p->fy_label }}</option>
                    @endforeach
                </select>
                <select name="month" class="h-8 rounded-md border-slate-200 !py-0 pl-2 pr-7 text-[12px]" onchange="this.form.submit()" title="Month">
                    @foreach ($monthOptions as $opt)
                        <option value="{{ $opt['index'] }}" @selected((int) $opt['index'] === (int) $monthIndex)>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </form>

            <div class="relative min-w-[180px] flex-1">
                <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                <input
                    type="search"
                    x-model="listQuery"
                    placeholder="Search branch, visitor, type, status…"
                    class="h-8 w-full rounded-md border-slate-200 py-0 pl-8 pr-8 text-[12px]"
                    autocomplete="off"
                >
                <button
                    type="button"
                    x-show="listQuery"
                    x-cloak
                    @click="listQuery = ''"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] text-slate-400 hover:text-slate-600"
                >Clear</button>
            </div>

            @can('monthly_visits.manage')
            <form method="POST" action="{{ route('monthly-visits.generate') }}">
                @csrf
                <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                <input type="hidden" name="month" value="{{ $monthIndex }}">
                <button type="submit" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50" title="Pull latest rows from yearly plan">
                    Re-sync
                </button>
            </form>
            <form
                method="POST"
                action="{{ route('monthly-visits.bulk-allocate') }}"
                onsubmit="return confirm('Auto-allocate {{ $monthLabel }} with conflict-safe dates? Existing non-completed plans may be rebalanced so every office is covered. Same person will never be placed on overlapping dates.')"
            >
                @csrf
                <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                <input type="hidden" name="month" value="{{ $monthIndex }}">
                <button type="submit" class="inline-flex h-8 items-center rounded-md bg-emerald-600 px-3 text-[12px] font-medium text-white hover:bg-emerald-500">
                    Auto-allocate
                </button>
            </form>
            @endcan
        </div>

        @if (($officerView ?? false) && ! ($employeeLinked ?? true))
            <div class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] text-amber-900">
                Your login is not linked to an organogram employee, so no visit allocations can be matched. Ask Super Admin to link your employee on Users &amp; Access.
            </div>
        @endif

        {{-- Compact status strip --}}
        <div class="mb-3 flex flex-wrap items-center gap-x-4 gap-y-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px]">
            <span class="text-slate-500">{{ ($officerView ?? false) ? 'My visits' : 'Planned' }} <strong class="text-navy-900">{{ number_format($performance['totals']['planned']) }}</strong></span>
            @unless ($officerView ?? false)
                <span class="text-slate-300">|</span>
                <span class="text-slate-500">Assigned <strong class="text-emerald-700">{{ number_format($performance['totals']['assigned']) }}</strong></span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-500">Unassigned <strong class="text-amber-700">{{ number_format($performance['totals']['pending']) }}</strong></span>
            @endunless
            <span class="text-slate-300">|</span>
            <span class="text-slate-500">Completed <strong class="text-sky-700">{{ number_format($performance['totals']['completed']) }}</strong></span>
            <span class="text-slate-300">|</span>
            <span class="text-slate-500">Cancelled <strong class="text-rose-700">{{ number_format($performance['totals']['cancelled']) }}</strong></span>
            <span class="text-slate-300">|</span>
            <span class="text-slate-500">Overdue <strong class="text-orange-700">{{ number_format($performance['totals']['overdue']) }}</strong></span>
        </div>

        <div class="grid gap-3 {{ ($officerView ?? false) ? '' : 'xl:grid-cols-12' }}">
            {{-- Unassigned --}}
            @can('monthly_visits.manage')
            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white xl:col-span-5">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3 py-2">
                    <div>
                        <h2 class="text-[13px] font-semibold text-navy-900">Unassigned</h2>
                        <p class="text-[11px] text-slate-500">
                            <span x-text="visibleUnassignedCount"></span> / {{ $unassigned->count() }}
                            <span x-show="listQuery" x-cloak> · filtered</span>
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="showSpecial = !showSpecial"
                        class="h-7 rounded-md border border-slate-200 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                        x-text="showSpecial ? 'Cancel special' : '+ Special'"
                    ></button>
                </div>

                <div x-show="showSpecial" x-cloak class="border-b border-slate-100 bg-slate-50 px-3 py-2.5">
                    <form method="POST" action="{{ route('monthly-visits.special.store') }}" class="grid gap-2 sm:grid-cols-2">
                        @csrf
                        <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                        <input type="hidden" name="month" value="{{ $monthIndex }}">
                        <div>
                            <label class="mb-0.5 block text-[10px] font-medium text-slate-500">Type</label>
                            <select name="activity_type_id" required class="h-8 w-full rounded-md border-slate-200 text-[12px]">
                                @foreach ($activityTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-0.5 block text-[10px] font-medium text-slate-500">Branch / entity</label>
                            <input type="text" name="entity_label" required placeholder="e.g. Special audit — Uttara" class="h-8 w-full rounded-md border-slate-200 text-[12px]">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-0.5 block text-[10px] font-medium text-slate-500">Notes</label>
                            <div class="flex gap-2">
                                <input type="text" name="notes" placeholder="Optional" class="h-8 min-w-0 flex-1 rounded-md border-slate-200 text-[12px]">
                                <button type="submit" class="h-8 shrink-0 rounded-md bg-navy-900 px-3 text-[12px] font-medium text-white hover:bg-navy-800">Add</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="max-h-[28rem] overflow-auto">
                    <table class="min-w-full text-left">
                        <thead class="sticky top-0 border-b border-slate-100 bg-slate-50">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-3 py-2 w-8">#</th>
                                <th class="px-3 py-2">Branch / Entity</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2 w-20"></th>
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
                                    class="text-[12px] unassigned-row"
                                    data-search="{{ e($unassignedSearch) }}"
                                    x-show="rowMatch($el.dataset.search, listQuery)"
                                >
                                    <td class="px-3 py-2 text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2 {{ ($item->schedulable instanceof \App\Models\Shakha && $item->schedulable->riskCategory()) ? \App\Support\ShakhaRiskTone::softBgClasses($item->schedulable->riskCategory()) : '' }}">
                                        @if ($item->schedulable instanceof \App\Models\Shakha)
                                            <x-shakha-name
                                                :name="$item->entity_label"
                                                :category="$item->schedulable->riskCategory()"
                                                class="text-[12px]"
                                            />
                                        @else
                                            <p class="font-medium text-navy-900">{{ $item->entity_label }}</p>
                                        @endif
                                        <p class="text-[10px] text-slate-400">
                                            {{ $item->isSpecial() ? 'Special' : 'Yearly' }}
                                            · {{ str_replace('_', ' ', $item->category) }}
                                        </p>
                                    </td>
                                    <td class="px-3 py-2 text-slate-600">{{ $item->activityType?->name }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <button
                                            type="button"
                                            @click="openAllocate({{ $item->id }})"
                                            class="rounded-md bg-navy-900 px-2 py-1 text-[11px] font-medium text-white hover:bg-navy-800"
                                        >Allocate</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-8 text-center text-[12px] text-slate-400">
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
                                    <td colspan="4" class="px-3 py-6 text-center text-[12px] text-slate-400">No matches.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>
            @endcan

            {{-- Assigned --}}
            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white {{ ($officerView ?? false) ? '' : 'xl:col-span-7' }}">
                <div class="border-b border-slate-100 px-3 py-2">
                    <h2 class="text-[13px] font-semibold text-navy-900">{{ ($officerView ?? false) ? 'My allocated shakhas' : 'Allocated schedule' }}</h2>
                    <p class="text-[11px] text-slate-500">
                        <span x-text="visibleAssignedCount"></span> / {{ $assigned->count() }}
                        <span x-show="listQuery" x-cloak> · filtered</span>
                    </p>
                </div>
                <div class="max-h-[28rem] overflow-auto">
                    <table class="min-w-full text-left">
                        <thead class="sticky top-0 border-b border-slate-100 bg-slate-50">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-3 py-2 w-8">#</th>
                                <th class="px-3 py-2">Visitor</th>
                                <th class="px-3 py-2">Branch / Entity</th>
                                <th class="px-3 py-2">Dates</th>
                                <th class="px-3 py-2">Days</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($assigned as $i => $item)
                                @php
                                    $a = $item->assignment;
                                    $status = str_replace('_', ' ', $a?->execution?->status ?? 'planned');
                                    $assignedSearch = strtolower(trim(implode(' ', [
                                        $a?->visitorNames(' '),
                                        $item->entity_label,
                                        $a?->last_audit_upto?->format('F Y'),
                                        $a?->visitDateRangeLabel(),
                                        $a?->remarks,
                                        $a?->purpose,
                                        $item->activityType?->name,
                                        $status,
                                        $item->isSpecial() ? 'special' : '',
                                    ])));
                                @endphp
                                <tr
                                    class="text-[12px] assigned-row"
                                    data-search="{{ e($assignedSearch) }}"
                                    x-show="rowMatch($el.dataset.search, listQuery)"
                                >
                                    <td class="px-3 py-2 text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2 font-medium text-navy-900 whitespace-pre-line">{{ $a?->visitorNames("\n") ?: '—' }}</td>
                                    <td class="px-3 py-2 {{ ($item->schedulable instanceof \App\Models\Shakha && $item->schedulable->riskCategory()) ? \App\Support\ShakhaRiskTone::softBgClasses($item->schedulable->riskCategory()) : '' }}">
                                        @if ($item->schedulable instanceof \App\Models\Shakha)
                                            <x-shakha-name
                                                :name="$item->entity_label"
                                                :category="$item->schedulable->riskCategory()"
                                                class="text-[12px]"
                                            />
                                        @else
                                            <p class="font-semibold text-navy-900">{{ $item->entity_label }}</p>
                                        @endif
                                        <p class="mt-0.5 text-[10px] text-slate-400">
                                            {{ $a?->purpose ?? $item->activityType?->name }}
                                            @if ($item->isSpecial()) · Special @endif
                                            @if ($a?->last_audit_upto) · Last {{ $a->last_audit_upto->format('M-Y') }} @endif
                                        </p>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-slate-600">{{ $a?->visitDateRangeLabel() }}</td>
                                    <td class="px-3 py-2 tabular-nums text-slate-600">{{ $a?->duration_days ?: '—' }}</td>
                                    <td class="px-3 py-2 capitalize text-slate-600">
                                        <span>{{ $status }}</span>
                                        @if ($a?->is_locked)
                                            <span class="ml-1 inline-flex rounded bg-amber-50 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-amber-800 ring-1 ring-amber-200">Locked</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap">
                                        @can('monthly_visits.manage')
                                            @if ($a?->is_locked && ! $a->canBeModifiedBy(auth()->user()))
                                                <span class="text-[11px] text-amber-700" title="Locked by {{ $a->lockedBy?->name ?? 'admin' }}">Locked</span>
                                            @else
                                                <button type="button" @click="openAllocate({{ $item->id }})" class="font-medium text-brand-600 hover:underline">Edit</button>
                                                @if ($a)
                                                    <span class="text-slate-300">·</span>
                                                @endif
                                            @endif
                                        @endcan
                                        @if ($a)
                                            <a href="{{ route('monthly-visits.execution', $a) }}" class="font-medium text-slate-600 hover:underline">Execute</a>
                                            @can('monthly_visits.manage')
                                                @if ($a->canBeModifiedBy(auth()->user()))
                                                    <span class="text-slate-300">·</span>
                                                    <a href="{{ route('monthly-visits.reschedule', $a) }}" class="font-medium text-slate-500 hover:underline">Move</a>
                                                    @if ($a->is_locked)
                                                        <span class="text-slate-300">·</span>
                                                        <form method="POST" action="{{ route('monthly-visits.unlock', $a) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="font-medium text-amber-700 hover:underline">Unlock</button>
                                                        </form>
                                                    @else
                                                        <span class="text-slate-300">·</span>
                                                        <form method="POST" action="{{ route('monthly-visits.lock', $a) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="font-medium text-slate-500 hover:underline">Lock</button>
                                                        </form>
                                                    @endif
                                                @endif
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-8 text-center text-[12px] text-slate-400">
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
                                    <td colspan="7" class="px-3 py-6 text-center text-[12px] text-slate-400">No matches.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
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
                <div class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-4 py-3">
                    <div>
                        <p class="text-[14px] font-semibold text-navy-900" x-text="current ? (current.status === 'assigned' ? 'Edit allocation' : 'Allocate visit') : 'Allocate visit'"></p>
                        <p class="mt-0.5 text-[12px] text-slate-600" x-text="current ? (current.entity_label + ' · ' + (current.activity || current.category)) : ''"></p>
                        <p class="mt-1 text-[10px] text-slate-500">Working days skip Fri–Sat &amp; holidays · one person cannot cover overlapping dates</p>
                    </div>
                    <button type="button" @click="close()" class="rounded-md p-1.5 text-slate-400 hover:bg-slate-50 hover:text-slate-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div
                    x-show="hasConflict || hasLiveConflict"
                    x-cloak
                    class="shrink-0 border-b border-rose-200 bg-rose-50 px-4 py-2.5 text-[12px] text-rose-950"
                >
                    <p class="font-semibold">Cannot save — same person at two places</p>
                    <p class="mt-0.5 text-[11px]" x-show="hasConflict" x-text="conflictWarning"></p>
                    <ul class="mt-1 list-disc pl-4 text-[11px]" x-show="hasLiveConflict && liveConflictLines.length">
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
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Visit window</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Start</label>
                                        <input type="date" name="start_date" required x-model="form.start_date" class="block w-full rounded-md border-slate-200 text-[13px]">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-slate-600">End</label>
                                        <input type="date" name="end_date" required x-model="form.end_date" class="block w-full rounded-md border-slate-200 text-[13px]">
                                    </div>
                                </div>

                                <div class="flex items-baseline justify-between rounded-md border border-slate-200 bg-slate-50 px-3 py-2">
                                    <div>
                                        <p class="text-[11px] font-medium text-slate-600">Working days</p>
                                        <p class="text-[10px] text-slate-500" x-text="durationHint"></p>
                                    </div>
                                    <p class="text-[18px] font-semibold tabular-nums text-navy-900" x-text="autoDays"></p>
                                </div>

                                <label class="flex cursor-pointer items-start gap-2 rounded-md border border-slate-200 px-3 py-2">
                                    <input type="checkbox" name="count_off_days" value="1" x-model="form.count_off_days" class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span>
                                        <span class="block text-[12px] font-medium text-slate-800">Count off days</span>
                                        <span class="block text-[10px] text-slate-500">Include Fri/Sat &amp; holidays in duration</span>
                                    </span>
                                </label>
                                <label class="mt-2 flex cursor-pointer items-start gap-2 rounded-lg border border-amber-100 bg-amber-50/60 px-3 py-2">
                                    <input type="hidden" name="lock_schedule" :value="form.lock_schedule ? 1 : 0">
                                    <input type="checkbox" value="1" x-model="form.lock_schedule" class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                    <span>
                                        <span class="block text-[12px] font-medium text-amber-950">Lock schedule</span>
                                        <span class="block text-[10px] text-amber-800/80">Others cannot edit or move this visit after save</span>
                                    </span>
                                </label>

                                <div x-show="rangeHolidays.length || rangeWeekends.length" class="rounded-md border border-slate-100 bg-slate-50 px-3 py-2">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Off days in range</p>
                                    <ul class="mt-1 max-h-20 space-y-0.5 overflow-y-auto text-[11px] text-slate-600">
                                        <template x-for="h in rangeHolidays" :key="h.date + h.type">
                                            <li><span class="tabular-nums" x-text="h.date"></span><span x-text="' · ' + h.name"></span></li>
                                        </template>
                                        <template x-for="d in rangeWeekends" :key="d">
                                            <li><span class="tabular-nums" x-text="d"></span> · Weekly off</li>
                                        </template>
                                    </ul>
                                </div>

                                <div>
                                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Last audit upto</label>
                                    <input type="hidden" name="last_audit_upto" :value="form.last_audit_upto">
                                    <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] font-medium text-navy-900" x-text="form.last_audit_upto_label || 'No prior audit on record'"></div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Purpose</label>
                                    <input type="hidden" name="purpose" :value="form.purpose">
                                    <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] font-medium text-navy-900" x-text="form.purpose || '—'"></div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Remarks</label>
                                    <textarea name="remarks" rows="2" x-model="form.remarks" class="block w-full rounded-md border-slate-200 text-[13px]"></textarea>
                                </div>
                            </div>

                            <div class="flex min-h-0 flex-col p-4 lg:col-span-3">
                                <div class="mb-2 flex flex-wrap items-end justify-between gap-2">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Staff</p>
                                        <p class="text-[10px] text-slate-500">Free days this month · select one or more</p>
                                    </div>
                                    <input type="search" x-model="staffQuery" placeholder="Search staff…" class="h-8 w-full max-w-[180px] rounded-md border-slate-200 py-0 text-[12px]">
                                </div>

                                <div class="mb-2 flex flex-wrap gap-1.5 text-[10px]">
                                    <span class="rounded bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700" x-text="selectedCountLabel"></span>
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-slate-600" x-text="employees.length + ' employees'"></span>
                                </div>

                                <div class="min-h-[220px] flex-1 space-y-1 overflow-y-auto rounded-md border border-slate-200 bg-slate-50/50 p-1.5">
                                    <template x-for="emp in filteredEmployees" :key="emp.id">
                                        <label class="flex cursor-pointer items-center gap-2.5 rounded-md border px-2.5 py-2 transition" :class="rowClass(emp)">
                                            <input
                                                type="checkbox"
                                                name="employee_ids[]"
                                                :value="emp.id"
                                                class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                x-model.number="visitorIds"
                                            >
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-x-2">
                                                    <span class="truncate text-[12px] font-semibold text-navy-900" x-text="emp.name"></span>
                                                    <span class="truncate text-[10px] text-slate-400" x-text="emp.title || ''"></span>
                                                </div>
                                                <p class="mt-0.5 text-[10px] text-rose-600" x-show="isBusyInRange(emp)" x-text="busyLabel(emp)"></p>
                                            </div>
                                            <div class="shrink-0 text-right">
                                                <p class="text-[13px] font-bold tabular-nums" :class="emp.free_days > 0 ? 'text-emerald-700' : 'text-rose-600'" x-text="emp.free_days"></p>
                                                <p class="text-[9px] uppercase text-slate-400">free</p>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                                <p class="mt-1.5 text-[10px] text-rose-600" x-show="visitorIds.length === 0">Select at least one visitor.</p>
                                <p class="mt-1 text-[10px] text-amber-700" x-show="hasLiveConflict">Selected staff have an overlapping visit in this date range.</p>
                            </div>
                        </div>

                        <div x-show="hasConflict || hasLiveConflict" class="shrink-0 border-t border-rose-200 bg-rose-50 px-4 py-3 text-[12px] text-rose-950">
                            <p class="font-semibold">Cannot allocate — same person at two places</p>
                            <p class="mt-1 text-[11px]" x-show="hasConflict" x-text="conflictWarning"></p>
                            <ul class="mt-1 list-disc pl-4 text-[11px] text-rose-900" x-show="hasLiveConflict && liveConflictLines.length">
                                <template x-for="line in liveConflictLines" :key="line">
                                    <li x-text="line"></li>
                                </template>
                            </ul>
                            @if ($conflictFlash)
                                <ul class="mt-1 list-disc pl-4 text-[11px] text-rose-900">
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
                            <p class="mt-2 text-[11px] font-medium">Change visitors or dates. Overlap is never allowed.</p>
                        </div>

                        <div class="flex shrink-0 justify-end gap-2 border-t border-slate-100 px-4 py-3">
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
                    if (cfg.openId) this.openAllocate(cfg.openId, true);
                },
                rowMatch(haystack, query) {
                    const q = (query || '').toLowerCase().trim();
                    if (!q) return true;
                    const text = (haystack || '').toLowerCase();
                    return q.split(/\s+/).every((token) => text.includes(token));
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
                        ? 'Special: every calendar day counts'
                        : 'Fri/Sat & holidays excluded';
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
