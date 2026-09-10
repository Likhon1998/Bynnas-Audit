<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Every organogram employee gets a login (admin-issued default password).
 * Role is taken from position slug → RoleAccess::positionRoleMap().
 */
class OrganogramLoginSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::query()
            ->with('position')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->get();

        $created = 0;
        $updated = 0;

        foreach ($employees as $employee) {
            $email = strtolower(trim((string) $employee->email));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $role = RoleAccess::suggestedRoleFromPosition(
                $employee->position?->title,
                $employee->position?->slug
            );

            $existingByEmployee = User::query()->where('employee_id', $employee->id)->first();
            $existingByEmail = User::query()->where('email', $email)->first();

            if ($existingByEmployee && $existingByEmail && $existingByEmployee->id !== $existingByEmail->id) {
                // Prefer the employee-linked account; leave email conflict alone.
                $user = $existingByEmployee;
            } else {
                $user = $existingByEmployee ?: $existingByEmail;
            }

            if (! $user) {
                $user = User::query()->create([
                    'name' => $employee->name,
                    'email' => $email,
                    'password' => Hash::make(RoleAccess::DEFAULT_PASSWORD),
                    'email_verified_at' => now(),
                    'is_superadmin' => false,
                    'is_active' => true,
                    'employee_id' => $employee->id,
                ]);
                $created++;
            } else {
                $user->fill([
                    'name' => $employee->name,
                    'email' => $user->email ?: $email,
                    'employee_id' => $employee->id,
                    'is_active' => true,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
                if (! $user->password) {
                    $user->password = Hash::make(RoleAccess::DEFAULT_PASSWORD);
                }
                $user->save();
                $updated++;
            }

            if ($user->isSuperAdmin() || $user->hasRole('superadmin')) {
                continue;
            }

            $user->syncRoles([$role]);
        }

        $this->command?->info(sprintf(
            'Organogram logins: %d created, %d refreshed (default password: %s).',
            $created,
            $updated,
            RoleAccess::DEFAULT_PASSWORD
        ));
    }
}
