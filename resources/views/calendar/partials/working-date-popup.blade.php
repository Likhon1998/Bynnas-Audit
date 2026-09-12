{{-- Inner colorful month grid used inside Start/End popups --}}
<div class="bg-gradient-to-b from-teal-50/90 to-white">
    <div class="flex items-center justify-between gap-2 bg-gradient-to-r from-teal-700 to-cyan-700 px-2.5 py-2 text-white">
        <button type="button" @click.stop="pickerPrev()" class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-white/15 text-[14px] hover:bg-white/25" aria-label="Previous month">‹</button>
        <div class="min-w-0 text-center">
            <p class="text-[12px] font-semibold tracking-tight" x-text="pickerMonthLabel"></p>
            <p class="text-[9px] font-medium uppercase tracking-[0.12em] text-teal-100" x-text="pickerTitle"></p>
        </div>
        <button type="button" @click.stop="pickerNext()" class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-white/15 text-[14px] hover:bg-white/25" aria-label="Next month">›</button>
    </div>

    <div class="grid grid-cols-7 border-b border-slate-100 text-center text-[9px] font-bold uppercase tracking-wide text-slate-500">
        <template x-for="label in pickerWeekdays" :key="'hd-' + label">
            <div class="px-0.5 py-1.5" x-text="label"></div>
        </template>
    </div>

    <div class="grid grid-cols-7 gap-px bg-slate-100 p-px">
        <template x-for="cell in pickerCells" :key="pickerOpen + '-' + cell.date">
            <button
                type="button"
                class="relative min-h-[36px] px-0.5 py-1 text-center transition focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal-400"
                :class="pickerDayClass(cell)"
                :title="cell.title"
                @click.stop="pickWorkingDay(cell)"
            >
                <span
                    class="mx-auto flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold tabular-nums"
                    :class="pickerNumClass(cell)"
                    x-text="cell.day"
                ></span>
                <span
                    x-show="cell.dot"
                    class="mx-auto mt-0.5 block h-1 w-1 rounded-full"
                    :class="cell.dot"
                ></span>
            </button>
        </template>
    </div>

    <div class="flex flex-wrap items-center gap-1 border-t border-slate-100 px-2 py-1.5 text-[9px] font-semibold">
        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-1.5 py-0.5 text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Working</span>
        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-slate-600"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Off</span>
        <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-1.5 py-0.5 text-sky-700"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span> Holiday</span>
        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-1.5 py-0.5 text-amber-800"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Internal</span>
    </div>
    <p class="border-t border-slate-50 px-2.5 py-1.5 text-[10px] text-slate-500" x-text="pickerHint"></p>
</div>
