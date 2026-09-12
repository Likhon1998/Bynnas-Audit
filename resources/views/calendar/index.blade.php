<x-app-layout>
    <div
        class="px-3 py-3 lg:px-5"
        x-data="calendarPage({
            canManage: {{ $canManage ? 'true' : 'false' }},
            days: @js($days),
            types: @js($types),
            weekendDays: @js($weekendDays),
        })"
    >
        {{-- Hero --}}
        <div class="relative mb-4 overflow-hidden rounded-2xl border border-teal-200/70 bg-gradient-to-br from-teal-600 via-cyan-600 to-sky-700 px-4 py-4 text-white shadow-sm sm:px-5 sm:py-5">
            <div class="pointer-events-none absolute -right-8 -top-10 h-40 w-40 rounded-full bg-amber-300/20 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-12 left-1/3 h-36 w-36 rounded-full bg-emerald-300/20 blur-2xl"></div>
            <div class="relative flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-teal-50 ring-1 ring-white/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
                        Manual · Customizable
                    </div>
                    <h1 class="text-[20px] font-semibold tracking-tight sm:text-[22px]">Working Calendar</h1>
                    <p class="mt-1 max-w-xl text-[12px] text-teal-50/90">
                        Paint your organisation’s real off days — weekly offs, national & government holidays, and internal offs. Used for staff free days and monthly visit allocation.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <form method="GET" action="{{ route('calendar.index') }}" class="flex flex-wrap items-center gap-1.5">
                        <select name="month" class="h-8 rounded-lg border-0 bg-white/95 py-0 text-[12px] font-medium text-slate-800 shadow-sm" onchange="this.form.submit()">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected($m === $month)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                        <select name="year" class="h-8 rounded-lg border-0 bg-white/95 py-0 text-[12px] font-medium text-slate-800 shadow-sm" onchange="this.form.submit()">
                            @foreach ($yearOptions as $y)
                                <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </form>
                    <a href="{{ $prevUrl }}" class="inline-flex h-8 items-center rounded-lg bg-white/15 px-2.5 text-[12px] font-medium text-white ring-1 ring-white/25 hover:bg-white/25">←</a>
                    <a href="{{ $todayUrl }}" class="inline-flex h-8 items-center rounded-lg bg-amber-300 px-2.5 text-[12px] font-semibold text-teal-950 hover:bg-amber-200">Today</a>
                    <a href="{{ $nextUrl }}" class="inline-flex h-8 items-center rounded-lg bg-white/15 px-2.5 text-[12px] font-medium text-white ring-1 ring-white/25 hover:bg-white/25">→</a>
                    @if ($canManage)
                        <button type="button" @click="openCreate()" class="inline-flex h-8 items-center gap-1 rounded-lg bg-white px-3 text-[12px] font-semibold text-teal-800 shadow-sm hover:bg-teal-50">
                            + Add off day
                        </button>
                    @endif
                </div>
            </div>

            <div class="relative mt-4 grid grid-cols-3 gap-2 sm:max-w-lg">
                <div class="rounded-xl bg-white/15 px-3 py-2 ring-1 ring-white/20">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-teal-100">Working</p>
                    <p class="text-[18px] font-semibold tabular-nums">{{ $stats['working'] }}</p>
                </div>
                <div class="rounded-xl bg-rose-400/25 px-3 py-2 ring-1 ring-rose-200/30">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-rose-100">Off days</p>
                    <p class="text-[18px] font-semibold tabular-nums">{{ $stats['off'] }}</p>
                </div>
                <div class="rounded-xl bg-amber-300/25 px-3 py-2 ring-1 ring-amber-200/40">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-amber-100">Internal</p>
                    <p class="text-[18px] font-semibold tabular-nums">{{ $stats['custom'] }}</p>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="mb-3 grid gap-3 lg:grid-cols-[1fr_300px]">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-amber-50/40 px-3 py-3">
                    <div>
                        <p class="text-[15px] font-semibold text-navy-900">{{ $periodLabel }}</p>
                        <p class="text-[11px] text-slate-500">
                            @if ($canManage)
                                Click any day to mark or edit an off day
                            @else
                                View-only · ask an authorised user to change offs
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold">
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-emerald-700 ring-1 ring-emerald-100"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Working</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-slate-600 ring-1 ring-slate-200"><span class="h-2 w-2 rounded-full bg-slate-400"></span> Weekly off</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-1 text-sky-700 ring-1 ring-sky-100"><span class="h-2 w-2 rounded-full bg-sky-500"></span> National</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2 py-1 text-violet-700 ring-1 ring-violet-100"><span class="h-2 w-2 rounded-full bg-violet-500"></span> Government</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-amber-800 ring-1 ring-amber-100"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Internal</span>
                    </div>
                </div>

                <div class="grid grid-cols-7 border-b border-slate-100 text-center text-[10px] font-bold uppercase tracking-wide">
                    @foreach ($weekdayLabels as $dow => $label)
                        <div class="px-1 py-2.5 {{ in_array($dow, $weekendDays, true) ? 'bg-slate-200/80 text-slate-600' : 'bg-teal-50/80 text-teal-800' }}">
                            {{ $label }}
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7 auto-rows-fr bg-slate-50/40">
                    <template x-for="day in days" :key="day.date">
                        <button
                            type="button"
                            class="relative min-h-[96px] border-b border-r border-slate-100 px-1.5 py-1.5 text-left transition"
                            :class="dayCellClass(day)"
                            :disabled="!canManage"
                            @click="canManage && openDay(day)"
                        >
                            <div class="flex items-start justify-between gap-1">
                                <span
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-full text-[12px] font-bold"
                                    :class="dayNumberClass(day)"
                                    x-text="day.day"
                                ></span>
                                <span
                                    x-show="day.is_weekend && day.in_month"
                                    class="rounded bg-slate-200/90 px-1 py-0.5 text-[8px] font-bold uppercase tracking-wide text-slate-600"
                                >Weekly</span>
                                <span
                                    x-show="day.is_today"
                                    class="rounded bg-teal-600 px-1 py-0.5 text-[8px] font-bold uppercase tracking-wide text-white"
                                >Today</span>
                            </div>
                            <div class="mt-1.5 space-y-1">
                                <template x-for="h in day.holidays.slice(0, 2)" :key="h.id">
                                    <p
                                        class="truncate rounded-md px-1.5 py-1 text-[9px] font-semibold leading-tight text-white shadow-sm"
                                        :class="holidayChipClass(h.type)"
                                        x-text="h.name"
                                    ></p>
                                </template>
                                <p
                                    x-show="day.holidays.length > 2"
                                    class="text-[9px] font-semibold text-slate-500"
                                    x-text="'+' + (day.holidays.length - 2) + ' more'"
                                ></p>
                                <p
                                    x-show="canManage && day.in_month && day.holidays.length === 0 && !day.is_weekend"
                                    class="text-[9px] font-medium text-teal-600/70 opacity-0 transition group-hover:opacity-100"
                                    style="opacity: 0.55"
                                >+ mark off</p>
                            </div>
                        </button>
                    </template>
                </div>
            </section>

            <aside class="space-y-3">
                <div class="overflow-hidden rounded-2xl border border-teal-100 bg-gradient-to-b from-teal-50 to-white p-3 shadow-sm">
                    <p class="text-[12px] font-semibold text-teal-900">Weekly off days</p>
                    <p class="mt-0.5 text-[10px] text-teal-700/80">Tap weekdays your organisation does not work</p>

                    @if ($canManage)
                        <form method="POST" action="{{ route('calendar.weekends') }}" class="mt-3 space-y-1.5">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            @foreach ($weekdayLabels as $dow => $label)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-[12px] transition hover:bg-white {{ in_array($dow, $weekendDays, true) ? 'bg-white font-semibold text-teal-900 ring-1 ring-teal-100' : 'text-slate-600' }}">
                                    <input
                                        type="checkbox"
                                        name="weekend_days[]"
                                        value="{{ $dow }}"
                                        class="rounded border-teal-300 text-teal-700 focus:ring-teal-600"
                                        @checked(in_array($dow, $weekendDays, true))
                                    >
                                    <span>{{ $label }}</span>
                                    @if (in_array($dow, $weekendDays, true))
                                        <span class="ml-auto rounded bg-slate-200 px-1.5 py-0.5 text-[9px] font-bold uppercase text-slate-600">Off</span>
                                    @endif
                                </label>
                            @endforeach
                            <button type="submit" class="mt-2 inline-flex h-9 w-full items-center justify-center rounded-lg bg-teal-700 text-[12px] font-semibold text-white hover:bg-teal-800">
                                Save weekly offs
                            </button>
                        </form>
                    @else
                        <ul class="mt-3 space-y-1.5 text-[12px] text-slate-700">
                            @forelse ($weekendDays as $dow)
                                <li class="rounded-lg bg-white px-2 py-1.5 ring-1 ring-teal-100">{{ $weekdayLabels[$dow] ?? 'Day '.$dow }}</li>
                            @empty
                                <li class="text-slate-400">No weekly offs configured</li>
                            @endforelse
                        </ul>
                    @endif
                </div>

                <div class="overflow-hidden rounded-2xl border border-amber-100 bg-gradient-to-b from-amber-50 to-white p-3 shadow-sm">
                    <p class="text-[12px] font-semibold text-amber-950">This month’s marked offs</p>
                    <p class="mt-0.5 text-[10px] text-amber-800/70">Manual entries & holidays</p>
                    <div class="mt-2 max-h-[360px] space-y-2 overflow-y-auto">
                        @forelse ($monthHolidays as $holiday)
                            @php
                                $chip = match ($holiday->type) {
                                    'national' => 'bg-sky-500',
                                    'government' => 'bg-violet-500',
                                    'ngo' => 'bg-amber-500',
                                    default => 'bg-slate-400',
                                };
                            @endphp
                            <div class="rounded-xl border border-white bg-white px-2.5 py-2 shadow-sm {{ $holiday->is_active ? '' : 'opacity-60' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="mb-1 flex items-center gap-1.5">
                                            <span class="h-2 w-2 rounded-full {{ $chip }}"></span>
                                            <p class="text-[11px] font-semibold text-slate-800">{{ $holiday->holiday_date->format('d M Y') }}</p>
                                        </div>
                                        <p class="truncate text-[12px] font-medium text-navy-900">{{ $holiday->name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ \App\Models\CalendarHoliday::typeLabel($holiday->type) }}{{ $holiday->is_active ? '' : ' · inactive' }}</p>
                                    </div>
                                    @if ($canManage)
                                        <div class="flex shrink-0 gap-1">
                                            <button
                                                type="button"
                                                class="rounded-md bg-slate-50 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 hover:bg-slate-100"
                                                @click="openEdit(@js([
                                                    'id' => $holiday->id,
                                                    'holiday_date' => $holiday->holiday_date->toDateString(),
                                                    'name' => $holiday->name,
                                                    'type' => $holiday->type,
                                                    'notes' => $holiday->notes,
                                                    'is_active' => $holiday->is_active,
                                                ]))"
                                            >Edit</button>
                                            <form method="POST" action="{{ route('calendar.toggle', $holiday) }}" onsubmit="return confirm('Toggle this off day?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800 hover:bg-amber-100">{{ $holiday->is_active ? 'Off' : 'On' }}</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-amber-200 bg-white/70 px-3 py-6 text-center">
                                <p class="text-[11px] font-medium text-amber-900/80">No marked offs yet</p>
                                <p class="mt-1 text-[10px] text-amber-800/60">Click a day on the grid to add an internal off day</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>

        {{-- Modal --}}
        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 p-4"
            @keydown.escape.window="modalOpen = false"
        >
            <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl" @click.outside="modalOpen = false">
                <div class="bg-gradient-to-r from-teal-700 to-cyan-700 px-4 py-3 text-white">
                    <p class="text-[13px] font-semibold" x-text="editingId ? 'Edit off day' : 'Add manual off day'"></p>
                    <p class="text-[10px] text-teal-100">Saved to Working Calendar for visits & free days</p>
                </div>
                <form method="POST" :action="formAction" class="space-y-3 px-4 py-4">
                    @csrf
                    <template x-if="editingId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="is_active" :value="form.is_active ? 1 : 0">

                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Date</label>
                        <input type="date" name="holiday_date" x-model="form.holiday_date" required class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Name</label>
                        <input type="text" name="name" x-model="form.name" required maxlength="160" placeholder="e.g. Staff training day / Founders Day" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Type</label>
                        <select name="type" x-model="form.type" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                            <template x-for="(label, key) in types" :key="key">
                                <option :value="key" x-text="label"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Notes</label>
                        <input type="text" name="notes" x-model="form.notes" maxlength="255" placeholder="Optional" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <label class="flex items-center gap-2 text-[12px] text-slate-700">
                        <input type="checkbox" x-model="form.is_active" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                        Active (counts as off day)
                    </label>

                    <div class="flex items-center justify-between gap-2 pt-1">
                        <button
                            type="button"
                            x-show="editingId"
                            class="rounded-md px-2.5 py-1.5 text-[12px] font-semibold text-rose-600 hover:bg-rose-50"
                            @click="confirmDelete()"
                        >Delete</button>
                        <div class="ml-auto flex gap-2">
                            <button type="button" class="rounded-md border border-slate-200 px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50" @click="modalOpen = false">Cancel</button>
                            <button type="submit" class="rounded-md bg-teal-700 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-teal-800" x-text="editingId ? 'Save changes' : 'Add off day'"></button>
                        </div>
                    </div>
                </form>
                <form x-ref="deleteForm" method="POST" :action="deleteAction" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>

    <script>
        function calendarPage({ canManage, days, types, weekendDays }) {
            const storeUrl = @js(route('calendar.store'));
            const holidayBase = @js(url('/calendar/holidays'));

            return {
                canManage,
                days,
                types,
                weekendDays: weekendDays || [],
                modalOpen: false,
                editingId: null,
                form: {
                    holiday_date: '',
                    name: '',
                    type: 'ngo',
                    notes: '',
                    is_active: true,
                },
                get formAction() {
                    return this.editingId ? holidayBase + '/' + this.editingId : storeUrl;
                },
                get deleteAction() {
                    return this.editingId ? holidayBase + '/' + this.editingId : '#';
                },
                dayCellClass(day) {
                    const classes = [];
                    if (!day.in_month) classes.push('bg-slate-50/80 opacity-55');
                    else if (day.is_weekend) classes.push('bg-slate-100/90');
                    else if (day.holidays?.some((h) => h.type === 'ngo')) classes.push('bg-amber-50');
                    else if (day.holidays?.some((h) => h.type === 'national')) classes.push('bg-sky-50');
                    else if (day.holidays?.some((h) => h.type === 'government')) classes.push('bg-violet-50');
                    else classes.push('bg-white hover:bg-teal-50/70');
                    if (day.is_today) classes.push('ring-2 ring-inset ring-teal-500');
                    if (this.canManage && day.in_month) classes.push('cursor-pointer');
                    return classes.join(' ');
                },
                dayNumberClass(day) {
                    if (!day.in_month) return 'text-slate-300';
                    if (day.is_today) return 'bg-teal-600 text-white shadow-sm';
                    if (day.is_weekend) return 'bg-slate-200 text-slate-600';
                    if (day.holidays?.length) return 'bg-white text-slate-800 ring-1 ring-slate-200';
                    return 'text-slate-800';
                },
                holidayChipClass(type) {
                    if (type === 'national') return 'bg-sky-500';
                    if (type === 'government') return 'bg-violet-500';
                    if (type === 'ngo') return 'bg-amber-500';
                    return 'bg-slate-400';
                },
                openCreate(date = null) {
                    this.editingId = null;
                    this.form = {
                        holiday_date: date || new Date().toISOString().slice(0, 10),
                        name: '',
                        type: 'ngo',
                        notes: '',
                        is_active: true,
                    };
                    this.modalOpen = true;
                },
                openDay(day) {
                    if (day.holidays && day.holidays.length >= 1) {
                        this.openEdit({
                            id: day.holidays[0].id,
                            holiday_date: day.date,
                            name: day.holidays[0].name,
                            type: day.holidays[0].type,
                            notes: day.holidays[0].notes || '',
                            is_active: day.holidays[0].is_active,
                        });
                        return;
                    }
                    this.openCreate(day.date);
                },
                openEdit(holiday) {
                    this.editingId = holiday.id;
                    this.form = {
                        holiday_date: holiday.holiday_date,
                        name: holiday.name,
                        type: holiday.type,
                        notes: holiday.notes || '',
                        is_active: !!holiday.is_active,
                    };
                    this.modalOpen = true;
                },
                confirmDelete() {
                    if (!this.editingId) return;
                    if (confirm('Remove this off day from the calendar?')) {
                        this.$refs.deleteForm.submit();
                    }
                },
            };
        }
    </script>
</x-app-layout>
