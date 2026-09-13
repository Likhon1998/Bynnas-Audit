<?php

namespace Tests\Unit;

use App\Support\AppTime;
use Carbon\Carbon;
use Tests\TestCase;

class AppTimeTest extends TestCase
{
    public function test_app_timezone_is_bangladesh(): void
    {
        AppTime::ensureConfigured();

        $this->assertSame('Asia/Dhaka', config('app.timezone'));
        $this->assertSame('Asia/Dhaka', date_default_timezone_get());
        $this->assertSame('Asia/Dhaka', bd_zone());
        $this->assertSame('+06:00', bd_now()->format('P'));
    }

    public function test_datetime_formats_in_bangladesh_zone(): void
    {
        $utc = Carbon::parse('2026-09-13 10:30:00', 'UTC');

        $this->assertSame('13 Sep 2026, 04:30 PM', bd_datetime($utc));
        $this->assertSame('13 Sep 2026', bd_date($utc));
        $this->assertSame('04:30 PM', bd_time($utc));
    }
}
