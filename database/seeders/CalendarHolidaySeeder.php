<?php

namespace Database\Seeders;

use App\Models\CalendarHoliday;
use Illuminate\Database\Seeder;

class CalendarHolidaySeeder extends Seeder
{
    public function run(): void
    {
        // Representative BD national / government offs spanning FY 2026-2027 (Jul–Jun).
        // Admin can add/adjust later; seed ensures working-day logic works out of the box.
        $rows = [
            // 2026
            ['2026-02-21', 'Shaheed Day', 'national'],
            ['2026-03-17', 'Birthday of Bangabandhu', 'national'],
            ['2026-03-26', 'Independence Day', 'national'],
            ['2026-04-14', 'Bengali New Year', 'national'],
            ['2026-05-01', 'May Day', 'national'],
            ['2026-05-28', 'Jumatul Bidah (approx)', 'government'],
            ['2026-05-29', 'Eid-ul-Adha (approx)', 'national'],
            ['2026-05-30', 'Eid-ul-Adha holiday', 'government'],
            ['2026-05-31', 'Eid-ul-Adha holiday', 'government'],
            ['2026-08-15', 'National Mourning Day', 'national'],
            ['2026-10-10', 'Durga Puja (approx)', 'government'],
            ['2026-12-16', 'Victory Day', 'national'],
            ['2026-12-25', 'Christmas Day', 'national'],
            // 2027
            ['2027-02-21', 'Shaheed Day', 'national'],
            ['2027-03-17', 'Birthday of Bangabandhu', 'national'],
            ['2027-03-26', 'Independence Day', 'national'],
            ['2027-04-14', 'Bengali New Year', 'national'],
            ['2027-05-01', 'May Day', 'national'],
            ['2027-03-20', 'Eid-ul-Fitr (approx)', 'national'],
            ['2027-03-21', 'Eid-ul-Fitr holiday', 'government'],
            ['2027-03-22', 'Eid-ul-Fitr holiday', 'government'],
        ];

        foreach ($rows as [$date, $name, $type]) {
            CalendarHoliday::query()->updateOrCreate(
                ['holiday_date' => $date, 'type' => $type],
                ['name' => $name, 'is_active' => true]
            );
        }
    }
}
