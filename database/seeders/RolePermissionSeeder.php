<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.manage',
            'organogram.view',
            'organogram.manage',
            'annual_audit.manage',
            'monthly_visits.manage',
            'monthly_visits.execute',
            'projects.manage',
            'kpis.manage',
            'risk.manage',
            'shakhas.manage',
            'shakhas.view_all',
            'areas.manage',
            'audits.manage',
            'audits.create',
            'findings.view_all',
            'findings.enter',
            'dashboard.ops',
            'dashboard.officer',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $superadmin = Role::findOrCreate('superadmin');
        $manager = Role::findOrCreate('audit_manager');
        $officer = Role::findOrCreate('audit_officer');

        $superadmin->syncPermissions(Permission::all());

        $manager->syncPermissions([
            'organogram.view',
            'organogram.manage',
            'annual_audit.manage',
            'monthly_visits.manage',
            'monthly_visits.execute',
            'projects.manage',
            'kpis.manage',
            'risk.manage',
            'shakhas.manage',
            'shakhas.view_all',
            'areas.manage',
            'audits.manage',
            'audits.create',
            'findings.view_all',
            'findings.enter',
            'dashboard.ops',
        ]);

        $officer->syncPermissions([
            'audits.create',
            'findings.enter',
            'monthly_visits.execute',
            'dashboard.officer',
        ]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@bynnasaudit.com'],
            [
                'name' => 'Bynnas Admin',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
                'is_superadmin' => true,
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['superadmin']);

        User::query()
            ->where('is_superadmin', true)
            ->each(function (User $user) {
                if (! $user->hasRole('superadmin')) {
                    $user->assignRole('superadmin');
                }
            });
    }
}
