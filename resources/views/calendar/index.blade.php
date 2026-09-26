<x-app-layout>
    <div
        class="flex h-full min-h-0 flex-col gap-2 px-3 py-2 lg:px-4"
        x-data="calendarPage({
            canManage: {{ $canManage ? 'true' : 'false' }},
            days: @js($days),
            types: @js($types),
            weekendDays: @js($weekendDays),
        })"
    >
        <div class="flex shrink-0 flex-wrap items-center justify-between gap-2">
            <div class="flex min-w-0 flex-wrap items-center gap-2">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Working Calendar</p>
                    <h1 class="text-[16px] font-semibold leading-tight tracking-tight text-navy-900">{{ $periodLabel }}</h1>
                </div>
                <div class="flex items-center gap-1 text-[11px] font-semibold">
                    <span class="rounded-md bg-emerald-50 px-2 py-1 text-emerald-800 ring-1 ring-emerald-100">Working <span class="tabular-nums">{{ $stats['working'] }}</span></span>
                    <span class="rounded-md bg-rose-50 px-2 py-1 text-rose-800 ring-1 ring-rose-100">Off <span class="tabular-nums">{{ $stats['off'] }}</span></span>
                    <span class="rounded-md bg-amber-50 px-2 py-1 text-amber-900 ring-1 ring-amber-100">Internal <span class="tabular-nums">{{ $stats['custom'] }}</span></span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-1">
                <form method="GET" action="{{ route('calendar.index') }}" class="flex items-center gap-1">
                    <select name="month" class="h-8 rounded-md border-slate-200 py-0 pl-2 pr-7 text-[12px] font-medium text-slate-800" onchange="this.form.submit()">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m === $month)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                    <select name="year" class="h-8 rounded-md border-slate-200 py-0 pl-2 pr-7 text-[12px] font-medium text-slate-800" onchange="this.form.submit()">
                        @foreach ($yearOptions as $y)
                            <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ $prevUrl }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-[13px] text-slate-700 hover:bg-slate-50" aria-label="Previous month">←</a>
                <a href="{{ $todayUrl }}" class="inline-flex h-8 items-center rounded-md bg-teal-700 px-2.5 text-[12px] font-semibold text-white hover:bg-teal-800">Today</a>
                <a href="{{ $nextUrl }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-[13px] text-slate-700 hover:bg-slate-50" aria-label="Next month">→</a>
                @if ($canManage)
                    <button type="button" @click="openCreate()" class="inline-flex h-8 items-center rounded-md border border-teal-200 bg-teal-50 px-2.5 text-[12px] font-semibold text-teal-800 hover:bg-teal-100">
                        + Add off day
                    </button>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="flex min-h-0 flex-1 flex-col gap-2 lg:flex-row">
            <section class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex shrink-0 flex-wrap items-center gap-x-3 gap-y-1 border-b border-slate-100 px-2.5 py-1.5 text-[11px] font-semibold">
                    <span class="text-slate-500">{{ $canManage ? 'Click a day to mark an off' : 'View only' }}</span>
                    <span class="inline-flex items-center gap-1 text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Working</span>
                    <span class="inline-flex items-center gap-1 text-slate-600"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Weekly</span>
                    <span class="inline-flex items-center gap-1 text-sky-700"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span> National</span>
                    <span class="inline-flex items-center gap-1 text-violet-700"><span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span> Government</span>
                    <span class="inline-flex items-center gap-1 text-amber-800"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Internal</span>
                </div>

                <div class="grid shrink-0 grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    @foreach ($weekdayLabels as $dow => $label)
                        <div class="px-1 py-1.5 {{ in_array($dow, $weekendDays, true) ? 'text-rose-700' : '' }}">{{ $label }}</div>
                    @endforeach
                </div>

                <div
                    class="grid min-h-0 flex-1 grid-cols-7 bg-slate-50/50"
                    :style="'grid-template-rows: repeat(' + Math.max(1, Math.ceil(days.length / 7)) + ', minmax(0, 1fr))'"
                >
                    <template x-for="day in days" :key="day.date">
                        <button
                            type="button"
                            class="group relative flex min-h-0 flex-col overflow-hidden border-b border-r border-slate-100 px-1 py-1 text-left"
                            :class="dayCellClass(day)"
                            :disabled="!canManage"
                            @click="canManage && openDay(day)"
                        >
                            <div class="flex items-center justify-between gap-1">
                                <span
                                    class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-bold"
                                    :class="dayNumberClass(day)"
                                    x-text="day.day"
                                ></span>
                                <span
                                    x-show="day.is_today"
                                    class="rounded bg-teal-600 px-1 py-px text-[9px] font-bold uppercase tracking-wide text-white"
                                >Today</span>
                                <span
                                    x-show="day.is_weekend && day.in_month && !day.is_today && day.holidays.length === 0"
                                    class="text-[9px] font-semibold uppercase tracking-wide text-rose-600"
                                >Off</span>
                            </div>
                            <div class="mt-0.5 min-h-0 space-y-0.5">
                                <template x-for="h in day.holidays.slice(0, 2)" :key="h.id">
                                    <p
                                        class="truncate rounded px-1 py-px text-[10px] font-semibold leading-tight text-white"
                                        :class="holidayChipClass(h.type)"
                                        :title="h.name"
                                        x-text="h.name"
                                    ></p>
                                </template>
                                <p
                                    x-show="day.holidays.length > 2"
                                    class="text-[10px] font-semibold text-slate-500"
                                    x-text="'+' + (day.holidays.length - 2)"
                                ></p>
                            </div>
                        </button>
                    </template>
                </div>
            </section>

            <aside class="flex max-h-[42vh] shrink-0 flex-col gap-2 lg:max-h-none lg:w-[228px]">
                <div class="shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
                    <p class="text-[12px] font-semibold text-navy-900">Weekly offs</p>

                    @if ($canManage)
                        <form method="POST" action="{{ route('calendar.weekends') }}" class="mt-1.5">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <div class="grid grid-cols-2 gap-0.5">
                            @foreach ($weekdayLabels as $dow => $label)
                                <label class="flex cursor-pointer items-center gap-1.5 rounded px-1 py-0.5 text-[11px] {{ in_array($dow, $weekendDays, true) ? 'bg-slate-100 font-semibold text-slate-800' : 'text-slate-600' }}">
                                    <input
                                        type="checkbox"
                                        name="weekend_days[]"
                                        value="{{ $dow }}"
                                        class="rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                                        @checked(in_array($dow, $weekendDays, true))
                                    >
                                    <span class="truncate">{{ $label }}</span>
                                </label>
                            @endforeach
                            </div>
                            <button type="submit" class="mt-1.5 inline-flex h-7 w-full items-center justify-center rounded-md bg-teal-700 text-[11px] font-semibold text-white hover:bg-teal-800">
                                Save weekly offs
                            </button>
                        </form>
                    @else
                        <ul class="mt-1.5 space-y-1 text-[12px] text-slate-700">
                            @forelse ($weekendDays as $dow)
                                <li class="rounded bg-slate-50 px-2 py-1">{{ $weekdayLabels[$dow] ?? 'Day '.$dow }}</li>
                            @empty
                                <li class="text-slate-400">No weekly offs</li>
                            @endforelse
                        </ul>
                    @endif
                </div>

                <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
                    <p class="shrink-0 text-[12px] font-semibold text-navy-900">Marked offs</p>
                    <div class="mt-1.5 min-h-0 flex-1 space-y-1 overflow-y-auto">
                        @forelse ($monthHolidays as $holiday)
                            @php
                                $chip = match ($holiday->type) {
                                    'national' => 'bg-sky-500',
                                    'government' => 'bg-violet-500',
                                    'ngo' => 'bg-amber-500',
                                    default => 'bg-slate-400',
                                };
                            @endphp
                            <div class="rounded-md border border-slate-100 px-2 py-1.5 {{ $holiday->is_active ? '' : 'opacity-60' }}">
                                <div class="flex items-start justify-between gap-1">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $chip }}"></span>
                                            <p class="text-[11px] font-semibold text-slate-700">{{ $holiday->holiday_date->format('d M') }}</p>
                                        </div>
                                        <p class="truncate text-[12px] font-medium text-navy-900">{{ $holiday->name }}</p>
                                    </div>
                                    @if ($canManage)
                                        <div class="flex shrink-0 gap-0.5">
                                            <button
                                                type="button"
                                                class="rounded px-1 py-0.5 text-[10px] font-semibold text-slate-600 hover:bg-slate-100"
                                                @click="openEdit(@js([
                                                    'id' => $holiday->id,
                                                    'holiday_date' => $holiday->holiday_date->toDateString(),
                                                    'name' => $holiday->name,
                                                    'type' => $holiday->type,
                                                    'notes' => $holiday->notes,
                                                    'is_active' => $holiday->is_active,
                                                ]))"
                                            >Edit</button>
                                            <form
                                                method="POST"
                                                action="{{ route('calendar.toggle', $holiday) }}"
                                                data-bynnas-confirm="This marked day will count as an off day, or stop counting, until you change it again."
                                                data-bynnas-confirm-title="Change this off day?"
                                                data-bynnas-confirm-ok="Change"
                                                data-bynnas-confirm-tone="amber"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded px-1 py-0.5 text-[10px] font-semibold text-amber-800 hover:bg-amber-50">{{ $holiday->is_active ? 'Off' : 'On' }}</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="px-1 py-4 text-center text-[12px] text-slate-500">No marked offs this month. Click a day on the calendar.</p>
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
            <div class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl" @click.outside="modalOpen = false">
                <div class="bg-gradient-to-r from-teal-700 to-cyan-700 px-3 py-2.5 text-white">
                    <p class="text-[13px] font-semibold" x-text="editingId ? 'Edit off day' : 'Add manual off day'"></p>
                    <p class="text-xs text-teal-100">Saved to Working Calendar for visits & free days</p>
                </div>
                <form method="POST" :action="formAction" class="space-y-3 px-3 py-3">
                    @csrf
                    <template x-if="editingId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="is_active" :value="form.is_active ? 1 : 0">

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Date</label>
                        <input type="date" name="holiday_date" x-model="form.holiday_date" required class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Name</label>
                        <input type="text" name="name" x-model="form.name" required maxlength="160" placeholder="e.g. Staff training day / Founders Day" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Type</label>
                        <select name="type" x-model="form.type" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                            <template x-for="(label, key) in types" :key="key">
                                <option :value="key" x-text="label"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</label>
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
                    const holiday = day.holidays?.[0]?.type;
                    if (!day.in_month) {
                        classes.push('bg-slate-50 text-slate-300');
                    } else if (holiday === 'national') {
                        classes.push('bg-sky-50');
                    } else if (holiday === 'government') {
                        classes.push('bg-violet-50');
                    } else if (holiday === 'ngo') {
                        classes.push('bg-amber-50');
                    } else if (day.is_weekend) {
                        classes.push('bg-rose-50/80');
                    } else {
                        classes.push('bg-white hover:bg-slate-50');
                    }
                    if (day.is_today) classes.push('ring-2 ring-inset ring-teal-600');
                    if (this.canManage && day.in_month) classes.push('cursor-pointer');
                    return classes.join(' ');
                },
                dayNumberClass(day) {
                    if (!day.in_month) return 'text-slate-300';
                    if (day.is_today) return 'bg-teal-700 text-white';
                    const holiday = day.holidays?.[0]?.type;
                    if (holiday === 'national') return 'bg-sky-600 text-white';
                    if (holiday === 'government') return 'bg-violet-600 text-white';
                    if (holiday === 'ngo') return 'bg-amber-600 text-white';
                    if (day.is_weekend) return 'text-rose-700';
                    return 'text-slate-800';
                },
                holidayChipClass(type) {
                    if (type === 'national') return 'bg-sky-600';
                    if (type === 'government') return 'bg-violet-600';
                    if (type === 'ngo') return 'bg-amber-600';
                    return 'bg-slate-500';
                },
                openCreate(date = null) {
                    this.editingId = null;
                    this.form = {
                        holiday_date: date || (window.bynnasTime?.todayYmd?.() || new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Dhaka' })),
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
                async confirmDelete() {
                    if (!this.editingId) return;
                    const ok = await window.bynnasConfirm({
                        title: 'Remove this off day?',
                        message: 'It will no longer count as an off day on the Working Calendar.',
                        okLabel: 'Remove',
                        tone: 'rose',
                    });
                    if (ok) this.$refs.deleteForm.submit();
                },
            };
        }
    </script>
</x-app-layout>
