<x-app-layout>
    <div
        class="px-4 py-5 lg:px-6"
        x-data="rescheduleCalendar({
            calendar: @js($calendarPayload),
            start: @js(old('start_date', $assignment->start_date?->toDateString())),
            end: @js(old('end_date', $assignment->end_date?->toDateString())),
            countOff: @js((bool) old('count_off_days', $assignment->count_off_days)),
        })"
    >
        <div class="mb-4">
            <a href="{{ route('monthly-visits.index', ['fy' => $assignment->workItem->fy_label, 'month' => $assignment->workItem->month_index]) }}" class="text-[11px] font-medium text-brand-600 hover:underline">← Back</a>
            <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">Reschedule visit</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">
                {{ $assignment->workItem?->entity_label }} · Working days follow the Working Calendar unless special request.
            </p>
            <p class="mt-1 text-[12px] text-slate-600">
                Current: {{ $assignment->visitDateRangeLabel() }}
                @if ($assignment->original_start_date)
                    · First planned: {{ $assignment->original_start_date->format('d M') }}–{{ $assignment->original_end_date?->format('d M Y') }}
                @endif
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="mb-3 max-w-xl">
            @include('calendar.partials.source-banner', [
                'weekendLabels' => $calendarPayload['weekend_labels'] ?? [],
                'calendarManageUrl' => $calendarPayload['manage_url'] ?? route('calendar.index'),
            ])
        </div>

        <div class="max-w-xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <form method="POST" action="{{ route('monthly-visits.reschedule.store', $assignment) }}" class="space-y-3 px-4 py-4">
                @csrf
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Visitor(s) — one or more</label>
                    <div class="max-h-40 space-y-1 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50/50 p-2">
                        @php $selected = collect(old('employee_ids', $assignment->visitorList()->pluck('id')->all()))->map(fn ($id) => (int) $id); @endphp
                        @foreach ($employees as $employee)
                            @php
                                $avail = collect($employeeAvailability)->firstWhere('id', $employee->id);
                            @endphp
                            <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-[12px] hover:bg-white">
                                <input
                                    type="checkbox"
                                    name="employee_ids[]"
                                    value="{{ $employee->id }}"
                                    class="rounded border-slate-300 text-emerald-600"
                                    @checked($selected->contains((int) $employee->id))
                                >
                                <span class="min-w-0 flex-1">
                                    <span class="font-medium text-navy-900">{{ $employee->name }}</span>
                                    @if ($employee->position)
                                        <span class="text-slate-400">— {{ $employee->position->title }}</span>
                                    @endif
                                </span>
                                @if ($avail)
                                    <span class="shrink-0 text-[10px] font-semibold tabular-nums {{ ($avail['free_days'] ?? 0) > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                        {{ $avail['free_days'] }} free
                                    </span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="mb-1.5 text-[11px] font-medium text-slate-600">New visit dates</p>
                    <input type="hidden" name="start_date" required :value="start">
                    <input type="hidden" name="end_date" required :value="end">
                    @include('calendar.partials.working-range-picker')
                </div>

                <div class="flex items-baseline justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <div>
                        <p class="text-[11px] font-medium text-slate-600">Working days</p>
                        <p class="text-[10px] text-slate-500" x-text="hint"></p>
                    </div>
                    <p class="text-[18px] font-semibold tabular-nums text-navy-900" x-text="days"></p>
                </div>
                <label class="flex items-start gap-2 rounded-lg border border-amber-100 bg-amber-50/60 px-3 py-2 text-[12px] text-amber-950">
                    <input type="checkbox" name="count_off_days" value="1" class="mt-0.5 rounded border-amber-300 text-amber-600" x-model="countOff">
                    <span>Special request — count weekly offs &amp; calendar holidays as working days</span>
                </label>
                <label class="flex items-start gap-2 rounded-lg border border-amber-100 bg-amber-50/60 px-3 py-2 text-[12px] text-amber-950">
                    <input type="hidden" name="lock_schedule" value="0">
                    <input type="checkbox" name="lock_schedule" value="1" class="mt-0.5 rounded border-amber-300 text-amber-600" @checked(old('lock_schedule', $assignment->is_locked ?? true))>
                    <span>Keep schedule locked — others cannot change this visit</span>
                </label>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Reschedule reason (required)</label>
                    <textarea name="reschedule_reason" required rows="2" class="block w-full rounded-lg border-slate-200 text-[13px]">{{ old('reschedule_reason') }}</textarea>
                </div>
                <p class="text-[11px] text-slate-500">Same person cannot be scheduled at two places on overlapping dates — conflicts are blocked.</p>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="submit" class="rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Save reschedule</button>
                </div>
            </form>
        </div>
    </div>

    @include('calendar.partials.working-range-picker-js')
    <script>
        function rescheduleCalendar(cfg) {
            const cal = window.WorkingCalendarUi.create(cfg.calendar || {});
            const synced = cal.syncMonthFrom(cfg.start);

            return {
                start: cfg.start || '',
                end: cfg.end || '',
                countOff: !!cfg.countOff,
                pickerYear: synced.year,
                pickerMonth: synced.month,
                pickerOpen: null,
                pickerStyle: 'top: 120px; left: 24px;',
                pickerWeekdays: cal.weekdayLabels,
                get pickerMonthLabel() {
                    return cal.monthLabel(this.pickerYear, this.pickerMonth);
                },
                get pickerCells() {
                    return cal.buildPickerCells(this.pickerYear, this.pickerMonth, this.start, this.end);
                },
                get rangeStartLabel() {
                    return cal.formatLabel(this.start);
                },
                get rangeEndLabel() {
                    return cal.formatLabel(this.end);
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
                    const ymd = which === 'end' ? this.end : this.start;
                    const next = cal.syncMonthFrom(ymd);
                    this.pickerYear = next.year;
                    this.pickerMonth = next.month;
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
                    const result = cal.applySinglePick(this.pickerOpen, this.start, this.end, cell.date);
                    this.start = result.start;
                    this.end = result.end;
                    this.pickerOpen = null;
                },
                get days() {
                    const a = cal.parseYmd(this.start);
                    const b = cal.parseYmd(this.end);
                    if (!a || !b || b < a) return 0;
                    let n = 0;
                    const cur = new Date(a);
                    while (cur <= b) {
                        if (this.countOff || !cal.isOffDay(cur)) n++;
                        cur.setDate(cur.getDate() + 1);
                    }
                    return n;
                },
                get hint() {
                    if (!this.start || !this.end) return 'Pick start and end dates';
                    if (this.days < 1) return 'No countable days in this range';
                    return this.countOff ? 'Special: every calendar day counts' : cal.offDayHint;
                },
            };
        }
    </script>
</x-app-layout>
