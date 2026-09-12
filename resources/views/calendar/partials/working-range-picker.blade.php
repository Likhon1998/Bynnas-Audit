{{--
  Start / End fields with Working Calendar popups (fixed so they are not clipped).
  Parent Alpine must provide:
    rangeStartLabel, rangeEndLabel,
    pickerOpen ('start'|'end'|null), openDatePicker(which), closeDatePicker(),
    pickerStyle, pickerMonthLabel, pickerWeekdays, pickerCells,
    pickerPrev(), pickerNext(), pickWorkingDay(cell),
    pickerDayClass(cell), pickerNumClass(cell), pickerHint, pickerTitle
--}}
<div class="grid grid-cols-2 gap-2">
    <div>
        <label class="mb-1 block text-[11px] font-medium text-slate-600">Start</label>
        <button
            type="button"
            x-ref="startDateBtn"
            @click="openDatePicker('start')"
            class="flex h-9 w-full items-center justify-between gap-1 rounded-md border px-2.5 text-left text-[13px] transition"
            :class="pickerOpen === 'start' ? 'border-teal-500 bg-teal-50 ring-2 ring-teal-200' : 'border-slate-200 bg-white hover:border-teal-300'"
        >
            <span class="truncate font-medium tabular-nums text-navy-900" x-text="rangeStartLabel"></span>
            <svg class="h-4 w-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M7 3a1 1 0 011 1v1h8V4a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 7.5v11A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-11A2.5 2.5 0 014.5 5H6V4a1 1 0 011-1zm12.5 6h-15v9.5a.5.5 0 00.5.5h14a.5.5 0 00.5-.5V9z"/>
            </svg>
        </button>
    </div>

    <div>
        <label class="mb-1 block text-[11px] font-medium text-slate-600">End</label>
        <button
            type="button"
            x-ref="endDateBtn"
            @click="openDatePicker('end')"
            class="flex h-9 w-full items-center justify-between gap-1 rounded-md border px-2.5 text-left text-[13px] transition"
            :class="pickerOpen === 'end' ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-200' : 'border-slate-200 bg-white hover:border-sky-300'"
        >
            <span class="truncate font-medium tabular-nums text-navy-900" x-text="rangeEndLabel"></span>
            <svg class="h-4 w-4 shrink-0 text-sky-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M7 3a1 1 0 011 1v1h8V4a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 7.5v11A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-11A2.5 2.5 0 014.5 5H6V4a1 1 0 011-1zm12.5 6h-15v9.5a.5.5 0 00.5.5h14a.5.5 0 00.5-.5V9z"/>
            </svg>
        </button>
    </div>
</div>

{{-- Single shared popup (fixed to viewport) --}}
<template x-teleport="body">
    <div
        x-show="pickerOpen"
        x-cloak
        class="fixed inset-0 z-[80]"
        @keydown.escape.window="closeDatePicker()"
    >
        <div class="absolute inset-0 bg-slate-900/20" @click="closeDatePicker()"></div>
        <div
            class="absolute w-[300px] overflow-hidden rounded-xl border border-teal-100 bg-white shadow-2xl"
            :style="pickerStyle"
            @click.stop
        >
            @include('calendar.partials.working-date-popup')
        </div>
    </div>
</template>
