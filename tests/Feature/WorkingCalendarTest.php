<?php

namespace Tests\Feature;

use App\Models\CalendarHoliday;
use App\Models\CalendarSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkingCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('calendar.manage');
        Permission::findOrCreate('monthly_visits.execute');
        $role = Role::findOrCreate('director_audit');
        $role->givePermissionTo(['calendar.manage', 'monthly_visits.execute']);
    }

    public function test_manager_can_view_calendar_and_add_ngo_off_day(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('director_audit');

        CalendarSetting::setWeekendDays([5, 6]);

        $this->actingAs($user)
            ->get(route('calendar.index', ['month' => 9, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Working Calendar')
            ->assertSee('Add off day');

        $this->actingAs($user)
            ->post(route('calendar.store'), [
                'holiday_date' => '2026-09-15',
                'name' => 'Staff capacity building',
                'type' => CalendarHoliday::TYPE_NGO,
                'notes' => 'All shakhas closed',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertTrue(
            CalendarHoliday::query()
                ->whereDate('holiday_date', '2026-09-15')
                ->where('name', 'Staff capacity building')
                ->where('type', 'ngo')
                ->where('is_active', true)
                ->exists()
        );
    }

    public function test_manager_can_add_off_day_range_in_one_go(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $user->assignRole('director_audit');

        CalendarHoliday::query()->create([
            'holiday_date' => '2026-10-06',
            'name' => 'Already marked',
            'type' => CalendarHoliday::TYPE_NGO,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('calendar.store'), [
                'holiday_date' => '2026-10-04',
                'holiday_date_to' => '2026-10-08',
                'name' => 'Durga Puja',
                'type' => CalendarHoliday::TYPE_NGO,
                'is_active' => 1,
            ])
            ->assertRedirect(route('calendar.index', ['month' => 10, 'year' => 2026]))
            ->assertSessionHas('status', '4 off days added (04 Oct – 08 Oct 2026). 1 date was already marked and left as is.');

        $this->assertSame(
            ['2026-10-04', '2026-10-05', '2026-10-07', '2026-10-08'],
            CalendarHoliday::query()->where('name', 'Durga Puja')->orderBy('holiday_date')->get()
                ->map(fn (CalendarHoliday $h) => $h->holiday_date->toDateString())->all()
        );
        $this->assertSame('Already marked', CalendarHoliday::query()->whereDate('holiday_date', '2026-10-06')->value('name'));
    }

    public function test_off_day_range_rejects_reversed_or_too_long_ranges(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $user->assignRole('director_audit');

        $this->actingAs($user)
            ->post(route('calendar.store'), [
                'holiday_date' => '2026-10-08',
                'holiday_date_to' => '2026-10-04',
                'name' => 'Backwards',
                'type' => CalendarHoliday::TYPE_NGO,
            ])
            ->assertSessionHasErrors('holiday_date_to');

        $this->actingAs($user)
            ->post(route('calendar.store'), [
                'holiday_date' => '2026-10-01',
                'holiday_date_to' => '2026-11-15',
                'name' => 'Too long',
                'type' => CalendarHoliday::TYPE_NGO,
            ])
            ->assertSessionHasErrors('holiday_date_to');

        $this->assertSame(0, CalendarHoliday::query()->count());
    }

    public function test_manager_can_change_weekend_days(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('director_audit');

        $this->actingAs($user)
            ->put(route('calendar.weekends'), [
                'weekend_days' => [4, 5],
                'month' => 9,
                'year' => 2026,
            ])
            ->assertRedirect(route('calendar.index', ['month' => 9, 'year' => 2026]));

        $this->assertSame([4, 5], CalendarSetting::weekendDays());
    }

    public function test_user_without_permission_cannot_manage_calendar(): void
    {
        Permission::findOrCreate('dashboard.officer');
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->givePermissionTo('dashboard.officer');

        $this->actingAs($user)
            ->get(route('calendar.index'))
            ->assertForbidden();
    }
}
