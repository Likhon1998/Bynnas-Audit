<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\RoleAccess;
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
            'annual_audit.view',
            'monthly_visits.manage',
            'monthly_visits.execute',
            'projects.manage',
            'projects.view',
            'kpis.manage',
            'risk.manage',
            'shakhas.manage',
            'shakhas.view_all',
            'areas.manage',
            'audits.manage',
            'audits.create',
            'audits.review',
            'audits.review_assign',
            'findings.view_all',
            'findings.enter',
            'findings.summary.view',
            'findings.summary.export_ppt',
            'findings.summary.edit',
            'dashboard.ops',
            'dashboard.officer',
            'map.view',
            'calendar.manage',
            'calendar.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $superadmin = Role::findOrCreate('superadmin');
        $director = Role::findOrCreate('director_audit');
        $manager = Role::findOrCreate('audit_manager');
        $senior = Role::findOrCreate('senior_officer');
        $officerReviewer = Role::findOrCreate('auditor_reviewer');
        $officer = Role::findOrCreate('audit_officer');

        $superadmin->syncPermissions(Permission::all());

        $leadership = [
            'organogram.view',
            'organogram.manage',
            'annual_audit.manage',
            'annual_audit.view',
            'monthly_visits.manage',
            'monthly_visits.execute',
            'projects.manage',
            'projects.view',
            'kpis.manage',
            'risk.manage',
            'shakhas.manage',
            'shakhas.view_all',
            'areas.manage',
            'audits.manage',
            'audits.create',
            'audits.review',
            'audits.review_assign',
            'findings.view_all',
            'findings.enter',
            'findings.summary.view',
            'findings.summary.export_ppt',
            'findings.summary.edit',
            'dashboard.ops',
            'map.view',
            'calendar.manage',
            'calendar.view',
        ];

        $director->syncPermissions($leadership);
        $manager->syncPermissions($leadership);

        $senior->syncPermissions([
            'organogram.view',
            'annual_audit.view',
            'monthly_visits.execute',
            'calendar.view',
            'projects.view',
            'kpis.manage',
            'risk.manage',
            'shakhas.view_all',
            'audits.create',
            'audits.review',
            'findings.view_all',
            'findings.enter',
            'findings.summary.view',
            'findings.summary.export_ppt',
            'findings.summary.edit',
            'dashboard.officer',
            'map.view',
        ]);

        $officerPerms = [
            'audits.create',
            'findings.enter',
            'monthly_visits.execute',
            'calendar.view',
            'dashboard.officer',
            'map.view',
        ];

        $officer->syncPermissions($officerPerms);
        $officerReviewer->syncPermissions(array_merge($officerPerms, ['audits.review']));

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@bynnasaudit.com'],
            [
                'name' => 'Bynnas Admin',
                'password' => Hash::make(RoleAccess::DEFAULT_PASSWORD),
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
