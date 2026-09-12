{{-- Shared helpers for colorful Working Calendar range pickers (allocate / reschedule). --}}
<script>
    window.WorkingCalendarUi = (function () {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        function create(calendar) {
            const holidayMap = {};
            (calendar?.holidays || []).forEach((h) => { holidayMap[h.date] = h; });
            const weekendDays = calendar?.weekend_days || [5, 6];
            const weekendLabels = (calendar?.weekend_labels || []).join(', ') || 'weekly offs';
            const weekdayLabels = calendar?.weekday_labels || ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const offDayHint = weekendLabels + ' & calendar holidays excluded';

            function parseYmd(s) {
                if (!s) return null;
                const [y, m, d] = s.split('-').map(Number);
                return new Date(y, m - 1, d);
            }

            function fmt(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function isOffDay(date) {
                const key = fmt(date);
                if (holidayMap[key]) return true;
                return weekendDays.includes(date.getDay());
            }

            function holidayFor(ymd) {
                return holidayMap[ymd] || null;
            }

            function buildPickerCells(year, month, startStr, endStr) {
                const first = new Date(year, month - 1, 1);
                const startPad = new Date(first);
                startPad.setDate(1 - first.getDay()); // Sunday-start grid
                const cells = [];
                const cur = new Date(startPad);
                for (let i = 0; i < 42; i++) {
                    const ymd = fmt(cur);
                    const holiday = holidayFor(ymd);
                    const weekend = weekendDays.includes(cur.getDay());
                    const inMonth = cur.getMonth() === month - 1;
                    const isStart = startStr && ymd === startStr;
                    const isEnd = endStr && ymd === endStr;
                    const inRange = !!(startStr && endStr && ymd >= startStr && ymd <= endStr);
                    const today = fmt(new Date()) === ymd;
                    let title = ymd;
                    let dot = '';
                    if (holiday) {
                        title += ' · ' + holiday.name + (holiday.type_label ? ' (' + holiday.type_label + ')' : '');
                        if (holiday.type === 'ngo') dot = 'bg-amber-500';
                        else if (holiday.type === 'national') dot = 'bg-sky-500';
                        else if (holiday.type === 'government') dot = 'bg-violet-500';
                        else dot = 'bg-slate-400';
                    } else if (weekend) {
                        title += ' · Weekly off';
                        dot = 'bg-slate-400';
                    } else if (inMonth) {
                        title += ' · Working day';
                        dot = 'bg-emerald-500';
                    }

                    cells.push({
                        date: ymd,
                        day: cur.getDate(),
                        inMonth,
                        weekend,
                        holiday,
                        isOff: !!(holiday || weekend),
                        isStart,
                        isEnd,
                        inRange,
                        isToday: today,
                        title,
                        dot: inMonth ? dot : '',
                    });
                    cur.setDate(cur.getDate() + 1);
                }
                return cells;
            }

            function pickerDayClass(cell) {
                const classes = [];
                if (!cell.inMonth) classes.push('bg-slate-50/80 opacity-45');
                else if (cell.inRange) classes.push('bg-teal-100/90');
                else if (cell.holiday?.type === 'ngo') classes.push('bg-amber-50');
                else if (cell.holiday?.type === 'national') classes.push('bg-sky-50');
                else if (cell.holiday?.type === 'government') classes.push('bg-violet-50');
                else if (cell.weekend) classes.push('bg-slate-100');
                else classes.push('bg-white hover:bg-emerald-50');
                if (cell.isStart || cell.isEnd) classes.push('ring-2 ring-inset ring-teal-600');
                return classes.join(' ');
            }

            function pickerNumClass(cell) {
                if (!cell.inMonth) return 'text-slate-300';
                if (cell.isStart || cell.isEnd) return 'bg-teal-700 text-white shadow-sm';
                if (cell.isToday) return 'bg-amber-400 text-amber-950';
                if (cell.isOff) return 'text-slate-500';
                return 'text-emerald-800';
            }

            function applySinglePick(which, startStr, endStr, ymd) {
                if (which === 'start') {
                    const end = endStr && endStr < ymd ? ymd : (endStr || ymd);
                    return { start: ymd, end };
                }
                const start = startStr && ymd < startStr ? ymd : (startStr || ymd);
                return { start, end: ymd };
            }

            function formatLabel(ymd) {
                const d = parseYmd(ymd);
                if (!d) return 'Pick date';
                return d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
            }

            function monthLabel(year, month) {
                return monthNames[month - 1] + ' ' + year;
            }

            function shiftMonth(year, month, delta) {
                const d = new Date(year, month - 1 + delta, 1);
                return { year: d.getFullYear(), month: d.getMonth() + 1 };
            }

            function syncMonthFrom(ymd, fallbackDate) {
                const d = parseYmd(ymd) || fallbackDate || new Date();
                return { year: d.getFullYear(), month: d.getMonth() + 1 };
            }

            return {
                holidayMap,
                weekendDays,
                weekendLabels,
                weekdayLabels,
                offDayHint,
                parseYmd,
                fmt,
                isOffDay,
                holidayFor,
                buildPickerCells,
                pickerDayClass,
                pickerNumClass,
                applySinglePick,
                formatLabel,
                monthLabel,
                shiftMonth,
                syncMonthFrom,
            };
        }

        return { create };
    })();
</script>
