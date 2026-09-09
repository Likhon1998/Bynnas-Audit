<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Spatie role → what the person can see / do in the app.
 * Positions (organogram) are HR titles; roles control login access.
 */
class RoleAccess
{
    /**
     * Built-in roles that cannot be deleted or renamed.
     *
     * @return list<string>
     */
    public static function systemRoles(): array
    {
        return ['superadmin', 'audit_manager', 'audit_officer'];
    }

    public static function isSystemRole(string $role): bool
    {
        return in_array($role, self::systemRoles(), true);
    }

    /**
     * @return array<string, array{label:string,summary:string,menus:list<string>,notes:string}>
     */
    public static function catalog(): array
    {
        return [
            'superadmin' => [
                'label' => 'Super Admin',
                'summary' => 'Full system control',
                'menus' => [
                    'Users & Access',
                    'Organogram',
                    'Annual Audit',
                    'Monthly Visits',
                    'Projects',
                    'KPI',
                    'Audit Reports',
                    'Checklists',
                    'Findings Matrix',
                    'Shakha / Areas',
                    'Map',
                    'Ops Dashboard',
                ],
                'notes' => 'Can create logins, assign roles, and manage every module.',
            ],
            'audit_manager' => [
                'label' => 'Audit Manager',
                'summary' => 'Operations & planning',
                'menus' => [
                    'Organogram',
                    'Annual Audit',
                    'Monthly Visits',
                    'Projects',
                    'KPI',
                    'Audit Reports',
                    'Checklists',
                    'Findings Matrix',
                    'Shakha / Areas',
                    'Map',
                    'Ops Dashboard',
                ],
                'notes' => 'Sees all branches. Cannot manage user logins (superadmin only).',
            ],
            'audit_officer' => [
                'label' => 'Audit Officer',
                'summary' => 'Field work on assigned branches',
                'menus' => [
                    'Dashboard (my work)',
                    'Map',
                    'Monthly Visits (execute)',
                    'Audit Reports',
                    'Checklists',
                    'Findings Matrix (enter)',
                    'Profile / Settings',
                ],
                'notes' => 'Shakhas from Monthly Visits allocations auto-apply. Admin can grant extra branches on Users & Access. Link organogram employee on login.',
            ],
        ];
    }

    /**
     * System + custom roles from DB for UI cheat sheets / role pickers.
     *
     * @return array<string, array{label:string,summary:string,menus:list<string>,notes:string}>
     */
    public static function catalogWithCustom(): array
    {
        $catalog = self::catalog();

        $roles = Role::query()->with('permissions')->orderBy('name')->get();

        foreach ($roles as $role) {
            if (isset($catalog[$role->name])) {
                continue;
            }

            $permissionNames = $role->permissions->pluck('name');
            $menus = self::menusFromPermissions($permissionNames);

            $catalog[$role->name] = [
                'label' => self::label($role->name),
                'summary' => 'Custom role',
                'menus' => $menus,
                'notes' => $permissionNames->count().' permission'.($permissionNames->count() === 1 ? '' : 's').' assigned.',
            ];
        }

        return $catalog;
    }

    public static function label(string $role): string
    {
        if (isset(self::catalog()[$role]['label'])) {
            return self::catalog()[$role]['label'];
        }

        return (string) Str::of($role)->replace(['_', '-'], ' ')->title();
    }

    /**
     * Permission key → human label, grouped for the role builder UI.
     *
     * @return array<string, array{label:string,permissions:array<string,string>}>
     */
    public static function permissionGroups(): array
    {
        return [
            'Access' => [
                'label' => 'Access',
                'permissions' => [
                    'users.manage' => 'Users & Access / Roles',
                ],
            ],
            'Organisation' => [
                'label' => 'Organisation',
                'permissions' => [
                    'organogram.view' => 'Organogram (view)',
                    'organogram.manage' => 'Organogram (manage)',
                    'shakhas.view_all' => 'All shakhas (no visit scope)',
                    'shakhas.manage' => 'Manage shakhas',
                    'areas.manage' => 'Manage areas',
                ],
            ],
            'Planning' => [
                'label' => 'Planning',
                'permissions' => [
                    'annual_audit.manage' => 'Annual Audit',
                    'monthly_visits.manage' => 'Monthly Visits (manage)',
                    'monthly_visits.execute' => 'Monthly Visits (execute)',
                    'projects.manage' => 'Projects',
                    'kpis.manage' => 'KPI',
                    'risk.manage' => 'Risk assessment',
                ],
            ],
            'Field work' => [
                'label' => 'Field work',
                'permissions' => [
                    'audits.create' => 'Audit reports (create)',
                    'audits.manage' => 'Audit reports (manage all)',
                    'findings.enter' => 'Findings (enter)',
                    'findings.view_all' => 'Findings (view all)',
                ],
            ],
            'Dashboard' => [
                'label' => 'Dashboard',
                'permissions' => [
                    'dashboard.ops' => 'Ops dashboard',
                    'dashboard.officer' => 'Officer dashboard',
                    'map.view' => 'Map',
                ],
            ],
        ];
    }

    /**
     * @param  Collection<int, string>|iterable<int, string>  $permissions
     * @return list<string>
     */
    public static function menusFromPermissions(iterable $permissions): array
    {
        $set = collect($permissions)->flip();
        $menus = [];

        $map = [
            'users.manage' => 'Users & Access',
            'organogram.view' => 'Organogram',
            'organogram.manage' => 'Organogram',
            'annual_audit.manage' => 'Annual Audit',
            'monthly_visits.manage' => 'Monthly Visits',
            'monthly_visits.execute' => 'Monthly Visits (execute)',
            'projects.manage' => 'Projects',
            'kpis.manage' => 'KPI',
            'risk.manage' => 'Risk',
            'shakhas.manage' => 'Shakha',
            'shakhas.view_all' => 'All shakhas',
            'areas.manage' => 'Areas',
            'audits.create' => 'Audit Reports',
            'audits.manage' => 'Audit Reports',
            'findings.enter' => 'Findings Matrix',
            'findings.view_all' => 'Findings Matrix',
            'dashboard.ops' => 'Ops Dashboard',
            'dashboard.officer' => 'Officer Dashboard',
            'map.view' => 'Map',
        ];

        foreach ($map as $permission => $label) {
            if ($set->has($permission) && ! in_array($label, $menus, true)) {
                $menus[] = $label;
            }
        }

        return $menus;
    }

    /**
     * Suggest a Spatie role from organogram position title.
     */
    public static function suggestedRoleFromPosition(?string $positionTitle): string
    {
        $title = mb_strtolower(trim((string) $positionTitle));

        if ($title === '') {
            return 'audit_officer';
        }

        if (str_contains($title, 'director') || str_contains($title, 'manager') || str_contains($title, 'joint')) {
            return 'audit_manager';
        }

        return 'audit_officer';
    }

    /**
     * Build a stable Spatie role name from a display label.
     */
    public static function slugFromLabel(string $label): string
    {
        $slug = Str::of($label)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return $slug !== '' ? $slug : 'custom_role';
    }
}
