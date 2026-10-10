<?php

namespace App\Http\Controllers;

use App\Models\CalendarHoliday;
use App\Models\CalendarSetting;
use App\Services\WorkingCalendarService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(
        private WorkingCalendarService $calendar,
    ) {}

    public function index(Request $request): View
    {
        $year = max(2000, min(2100, (int) $request->integer('year', now('Asia/Dhaka')->year)));
        $month = max(1, min(12, (int) $request->integer('month', now('Asia/Dhaka')->month)));

        $monthStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Dhaka')->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        $weekendDays = $this->calendar->weekendDays();
        $holidays = CalendarHoliday::query()
            ->whereBetween('holiday_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->orderBy('holiday_date')
            ->get();

        $holidaysByDate = $holidays->groupBy(fn (CalendarHoliday $h) => $h->holiday_date->toDateString());

        $days = [];
        $cursor = $gridStart->copy();
        while ($cursor->lte($gridEnd)) {
            $dateKey = $cursor->toDateString();
            $inMonth = $cursor->month === $month;
            $isWeekend = in_array((int) $cursor->dayOfWeek, $weekendDays, true);
            $dayHolidays = ($holidaysByDate->get($dateKey) ?? collect())
                ->filter(fn (CalendarHoliday $h) => $h->is_active)
                ->values();

            $days[] = [
                'date' => $dateKey,
                'day' => (int) $cursor->day,
                'in_month' => $inMonth,
                'is_today' => $cursor->isToday(),
                'dow' => (int) $cursor->dayOfWeek,
                'is_weekend' => $isWeekend,
                'is_off' => $isWeekend || $dayHolidays->isNotEmpty(),
                'holidays' => $dayHolidays->map(fn (CalendarHoliday $h) => [
                    'id' => $h->id,
                    'name' => $h->name,
                    'type' => $h->type,
                    'type_label' => CalendarHoliday::typeLabel($h->type),
                    'notes' => $h->notes,
                    'is_active' => $h->is_active,
                ])->all(),
            ];
            $cursor->addDay();
        }

        $monthHolidays = CalendarHoliday::query()
            ->whereBetween('holiday_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('holiday_date')
            ->get();

        $prev = $monthStart->copy()->subMonth();
        $next = $monthStart->copy()->addMonth();

        return view('calendar.index', [
            'year' => $year,
            'month' => $month,
            'periodLabel' => $monthStart->format('F Y'),
            'days' => $days,
            'weekendDays' => $weekendDays,
            'weekdayLabels' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            'monthHolidays' => $monthHolidays,
            'canManage' => $request->user()?->can('calendar.manage') ?? false,
            'types' => [
                CalendarHoliday::TYPE_NGO => CalendarHoliday::typeLabel(CalendarHoliday::TYPE_NGO),
                CalendarHoliday::TYPE_NATIONAL => CalendarHoliday::typeLabel(CalendarHoliday::TYPE_NATIONAL),
                CalendarHoliday::TYPE_GOVERNMENT => CalendarHoliday::typeLabel(CalendarHoliday::TYPE_GOVERNMENT),
            ],
            'prevUrl' => route('calendar.index', ['month' => $prev->month, 'year' => $prev->year]),
            'nextUrl' => route('calendar.index', ['month' => $next->month, 'year' => $next->year]),
            'todayUrl' => route('calendar.index', [
                'month' => now('Asia/Dhaka')->month,
                'year' => now('Asia/Dhaka')->year,
            ]),
            'yearOptions' => range(now('Asia/Dhaka')->year + 2, now('Asia/Dhaka')->year - 8),
            'stats' => [
                'working' => $this->calendar->countWorkingDays($monthStart, $monthEnd, false),
                'off' => $monthStart->daysInMonth - $this->calendar->countWorkingDays($monthStart, $monthEnd, false),
                'custom' => $monthHolidays->where('type', CalendarHoliday::TYPE_NGO)->where('is_active', true)->count(),
            ],
        ]);
    }

    /** Longest off-day range that can be added in one go. */
    public const MAX_RANGE_DAYS = 31;

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'holiday_date' => ['required', 'date'],
            'holiday_date_to' => ['nullable', 'date', 'after_or_equal:holiday_date'],
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(CalendarHoliday::TYPES)],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'holiday_date_to.after_or_equal' => 'The "To" date must be on or after the "From" date.',
        ]);

        $from = Carbon::parse($data['holiday_date'], 'Asia/Dhaka')->startOfDay();
        $to = filled($data['holiday_date_to'] ?? null)
            ? Carbon::parse($data['holiday_date_to'], 'Asia/Dhaka')->startOfDay()
            : $from->copy();
        $span = (int) $from->diffInDays($to) + 1;

        if ($span > self::MAX_RANGE_DAYS) {
            return back()->withErrors([
                'holiday_date_to' => 'A range can cover at most '.self::MAX_RANGE_DAYS.' days.',
            ])->withInput();
        }

        $dates = [];
        for ($cursor = $from->copy(); $cursor->lte($to); $cursor->addDay()) {
            $dates[] = $cursor->toDateString();
        }

        $taken = CalendarHoliday::query()
            ->whereBetween('holiday_date', [$from->toDateString(), $to->toDateString()])
            ->where('type', $data['type'])
            ->get(['holiday_date'])
            ->map(fn (CalendarHoliday $h) => $h->holiday_date->toDateString())
            ->all();
        $newDates = array_values(array_diff($dates, $taken));

        if ($newDates === []) {
            return back()->withErrors([
                'holiday_date' => $span === 1
                    ? 'An off day of this type already exists on that date.'
                    : 'Every date in that range already has an off day of this type.',
            ])->withInput();
        }

        foreach ($newDates as $date) {
            CalendarHoliday::query()->create([
                'holiday_date' => $date,
                'name' => $data['name'],
                'type' => $data['type'],
                'notes' => $data['notes'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'created_by' => $request->user()?->id,
            ]);
        }

        $this->calendar->forgetCache();

        $status = $span === 1
            ? 'Off day added to the calendar.'
            : count($newDates).' off days added ('.$from->format('d M').' – '.$to->format('d M Y').').';
        $skipped = count($dates) - count($newDates);
        if ($skipped > 0) {
            $status .= ' '.$skipped.' '.($skipped === 1 ? 'date was' : 'dates were').' already marked and left as is.';
        }

        return redirect()
            ->route('calendar.index', ['month' => $from->month, 'year' => $from->year])
            ->with('status', $status);
    }

    public function update(Request $request, CalendarHoliday $holiday): RedirectResponse
    {
        $data = $request->validate([
            'holiday_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(CalendarHoliday::TYPES)],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $clash = CalendarHoliday::query()
            ->where('id', '!=', $holiday->id)
            ->whereDate('holiday_date', $data['holiday_date'])
            ->where('type', $data['type'])
            ->exists();

        if ($clash) {
            return back()->withErrors([
                'holiday_date' => 'Another off day of this type already exists on that date.',
            ])->withInput();
        }

        $holiday->update([
            'holiday_date' => $data['holiday_date'],
            'name' => $data['name'],
            'type' => $data['type'],
            'notes' => $data['notes'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->calendar->forgetCache();

        return redirect()
            ->route('calendar.index', [
                'month' => (int) date('n', strtotime($data['holiday_date'])),
                'year' => (int) date('Y', strtotime($data['holiday_date'])),
            ])
            ->with('status', 'Off day updated.');
    }

    public function destroy(CalendarHoliday $holiday): RedirectResponse
    {
        $month = (int) $holiday->holiday_date->format('n');
        $year = (int) $holiday->holiday_date->format('Y');
        $holiday->delete();
        $this->calendar->forgetCache();

        return redirect()
            ->route('calendar.index', compact('month', 'year'))
            ->with('status', 'Off day removed.');
    }

    public function toggle(CalendarHoliday $holiday): RedirectResponse
    {
        $holiday->update(['is_active' => ! $holiday->is_active]);
        $this->calendar->forgetCache();

        return redirect()
            ->route('calendar.index', [
                'month' => (int) $holiday->holiday_date->format('n'),
                'year' => (int) $holiday->holiday_date->format('Y'),
            ])
            ->with('status', $holiday->is_active ? 'Off day activated.' : 'Off day deactivated.');
    }

    public function updateWeekends(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'weekend_days' => ['nullable', 'array'],
            'weekend_days.*' => ['integer', 'between:0,6'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        CalendarSetting::setWeekendDays($data['weekend_days'] ?? []);
        $this->calendar->forgetCache();

        return redirect()
            ->route('calendar.index', [
                'month' => (int) ($data['month'] ?? now('Asia/Dhaka')->month),
                'year' => (int) ($data['year'] ?? now('Asia/Dhaka')->year),
            ])
            ->with('status', 'Weekend / weekly off days updated for the organisation.');
    }
}
