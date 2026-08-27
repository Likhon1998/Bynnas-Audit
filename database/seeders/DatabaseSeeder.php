<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->where('email', 'admin@bynnas.com')->delete();

        User::query()->updateOrCreate(
            ['email' => 'admin@bynnasaudit.com'],
            [
                'name' => 'Bynnas Admin',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
                'is_superadmin' => true,
            ]
        );

        $this->call([
            OrganogramSeeder::class,
            OrganizationSeeder::class,
            ShakhaSeeder::class,
            AnnualAuditSeeder::class,
            ActivityTypeSeeder::class,
            CalendarHolidaySeeder::class,
            AuditIndicatorSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
