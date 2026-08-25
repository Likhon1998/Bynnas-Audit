<?php

namespace App\Services;

use App\Models\CalendarHoliday;
use App\Models\Employee;
use App\Models\MonthlyAssignment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkingCalendarService
{
    /** @var array<int, true>|null */
    protected ?array $holidayLookup = null;

    /** @var list<array{date:string,name:string,type:string}>|null */
    protected ?array $holidayList = null;

    /**
     * @return list<int>
     */
    public function weekendDays(): array
    {
        return array_values(array_map('intval', config('working_calendar.weekend_days', [5, 6])));
    }

    /**
     * @return list<array{date:string,name:string,type:string}>
     */
    public function holidaysBetween(Carbon $start, Carbon $end): array
    {
        $this->ensureHolidayCache($start->copy()->subMonth(), $end->copy()->addMonth());

        return collect($this->holidayList ?? [])
            ->filter(fn ($h) => $h['date'] >= $start->toDateString() && $h['date'] <= $end->toDateString())
            ->values()
            ->all();
    }

    public function isWeekend(Carbon $date): bool
    {
        return in_array((int) $date->dayOfWeek, $this->weekendDays(), true);
    }

    public function isHoliday(Carbon $date): bool
    {
        $this->ensureHolidayCache($date->copy()->startOfYear(), $date->copy()->endOfYear());

        return isset($this->holidayLookup[$date->toDateString()]);
    }

    public function isOffDay(Carbon $date): bool
    {
        return $this->isWeekend($date) || $this->isHoliday($date);
    }

    public function isWorkingDay(Carbon $date, bool $countOffDays = false): bool
    {
        if ($countOffDays) {
            return true;
        }

        return ! $this->isOffDay($date);
    }

    /**
     * Count working days in [start, end] inclusive.
     * Off days (Fri/Sat + national/govt holidays) are excluded unless $countOffDays.
     */
    public function countWorkingDays(Carbon $start, Carbon $end, bool $countOffDays = false): int
    {
        if ($end->lt($start)) {
            return 0;
        }

        $this->ensureHolidayCache($start, $end);

        $days = 0;
        $cursor = $start->copy()->startOfDay();
        $last = $end->copy()->startOfDay();

        while ($cursor->lte($last)) {
            if ($this->isWorkingDay($cursor, $countOffDays)) {
                $days++;
            }
            $cursor->addDay();
        }

        return max(0, $days);
    }

    /**
     * @return list<string> Y-m-d working dates in range
     */
    public function workingDates(Carbon $start, Carbon $end, bool $countOffDays = false): array
    {
        if ($end->lt($start)) {
            return [];
        }

        $this->ensureHolidayCache($start, $end);
        $dates = [];
        $cursor = $start->copy()->startOfDay();
        $last = $end->copy()->startOfDay();

        while ($cursor->lte($last)) {
            if ($this->isWorkingDay($cursor, $countOffDays)) {
                $dates[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        return $dates;
    }

    /**
     * Free working days in a month window for each employee (not booked by assignments).
     *
     * @return list<array{
     *   id:int,name:string,title:?string,free_days:int,booked_days:int,working_days:int,
     *   busy_ranges:list<array{start:string,end:string,entity:?string}>
     * }>
     */
    public function employeeAvailabilityForMonth(Carbon $monthStart, Carbon $monthEnd, ?int $ignoreAssignmentId = null): array
    {
        $workingDates = $this->workingDates($monthStart, $monthEnd, false);
        $workingSet = array_fill_keys($workingDates, true);
        $workingCount = count($workingDates);

        $assignments = MonthlyAssignment::query()
            ->when($ignoreAssignmentId, fn ($q) => $q->where('id', '!=', $ignoreAssignmentId))
            ->whereDate('start_date', '<=', $monthEnd->toDateString())
            ->whereDate('end_date', '>=', $monthStart->toDateString())
            ->with(['workItem', 'employee', 'visitors'])
            ->get();

        /** @var array<int, array<string, true>> $bookedByEmployee */
        $bookedByEmployee = [];
        /** @var array<int, list<array{start:string,end:string,entity:?string}>> $rangesByEmployee */
        $rangesByEmployee = [];

        foreach ($assignments as $assignment) {
            $rangeStart = Carbon::parse($assignment->start_date)->max($monthStart);
            $rangeEnd = Carbon::parse($assignment->end_date)->min($monthEnd);
            $bookedDates = $this->workingDates($rangeStart, $rangeEnd, (bool) $assignment->count_off_days);

            foreach ($assignment->visitorList() as $employee) {
                $id = (int) $employee->id;
                foreach ($bookedDates as $d) {
                    if (isset($workingSet[$d]) || $assignment->count_off_days) {
                        if (isset($workingSet[$d])) {
                            $bookedByEmployee[$id][$d] = true;
                        }
                    }
                }
                $rangesByEmployee[$id][] = [
                    'start' => $assignment->start_date->toDateString(),
                    'end' => $assignment->end_date->toDateString(),
                    'entity' => $assignment->workItem?->entity_label,
                ];
            }
        }

        return Employee::query()
            ->with('position')
            ->orderBy('name')
            ->get()
            ->map(function (Employee $employee) use ($workingCount, $bookedByEmployee, $rangesByEmployee) {
                $id = (int) $employee->id;
                $booked = count($bookedByEmployee[$id] ?? []);

                return [
                    'id' => $id,
                    'name' => $employee->name,
                    'title' => $employee->position?->title,
                    'working_days' => $workingCount,
                    'booked_days' => $booked,
                    'free_days' => max(0, $workingCount - $booked),
                    'busy_ranges' => $rangesByEmployee[$id] ?? [],
                ];
            })
            ->sortByDesc('free_days')
            ->values()
            ->all();
    }

    /**
     * Payload for Alpine calendar helpers in the allocate modal.
     *
     * @return array{weekend_days:list<int>,holidays:list<array{date:string,name:string,type:string}>}
     */
    public function modalCalendarPayload(Carbon $from, Carbon $to): array
    {
        return [
            'weekend_days' => $this->weekendDays(),
            'holidays' => $this->holidaysBetween($from, $to),
        ];
    }

    protected function ensureHolidayCache(Carbon $from, Carbon $to): void
    {
        static $loadedFrom = null;
        static $loadedTo = null;

        if (
            $this->holidayLookup !== null
            && $loadedFrom
            && $loadedTo
            && $from->gte($loadedFrom)
            && $to->lte($loadedTo)
        ) {
            return;
        }

        $padFrom = $from->copy()->subMonths(2)->startOfMonth();
        $padTo = $to->copy()->addMonths(2)->endOfMonth();

        $rows = CalendarHoliday::query()
            ->where('is_active', true)
            ->whereBetween('holiday_date', [$padFrom->toDateString(), $padTo->toDateString()])
            ->orderBy('holiday_date')
            ->get(['holiday_date', 'name', 'type']);

        $lookup = [];
        $list = [];
        foreach ($rows as $row) {
            $date = $row->holiday_date->toDateString();
            $lookup[$date] = true;
            $list[] = [
                'date' => $date,
                'name' => $row->name,
                'type' => $row->type,
            ];
        }

        $this->holidayLookup = $lookup;
        $this->holidayList = $list;
        $loadedFrom = $padFrom;
        $loadedTo = $padTo;
    }
}
