<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Spatie role → what the person can see / do in the app.
 * Organogram positions map to roles; admin issues the login.
 */
class RoleAccess
{
    /** Default password for organogram logins created by admin / seeder. */
    public const DEFAULT_PASSWORD = '12345678';

    /**
     * Built-in roles that cannot be deleted or renamed.
     *
     * @return list<string>
     */
    public static function systemRoles(): array
    {
        return [
            'superadmin',
            'director_audit',
            'audit_manager',
            'senior_officer',
            'audit_officer',
        ];
    }

    public static function isSystemRole(string $role): bool
    {
        return in_array($role, self::systemRoles(), true);
    }

    public static function isPersonalAccessRole(string $role): bool
    {
        return (bool) preg_match('/^u\d+_access$/', $role);
    }

    public static function personalAccessRoleName(int $userId): string
    {
        return 'u'.$userId.'_access';
    }

    /**
     * Roles an admin may assign on the grant-access form (excludes per-user shadow roles).
     *
     * @return list<string>
     */
    public static function assignableRoleNames(): array
    {
        return Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->reject(fn ($name) => self::isPersonalAccessRole((string) $name))
            ->map(fn ($name) => (string) $name)
            ->values()
            ->all();
    }

    /**
     * Permission names currently attached to a Spatie role (empty if role missing).
     *
     * @return list<string>
     */
    public static function permissionNamesForRole(string $role): array
    {
        $model = Role::query()->where('name', $role)->first();
        if (! $model) {
            return [];
        }

        return $model->permissions()
            ->pluck('name')
            ->map(fn ($n) => (string) $n)
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function allPermissionNames(): array
    {
        $names = [];
        foreach (self::permissionGroups() as $group) {
            foreach (array_keys($group['permissions']) as $permission) {
                $names[] = $permission;
            }
        }

        return $names;
    }

    /**
     * @return array<string, list<string>>
     */
    public static function rolePermissionMap(): array
    {
        $map = [];
        foreach (self::assignableRoleNames() as $role) {
            $map[$role] = self::permissionNamesForRole($role);
        }

        return $map;
    }

    /**
     * Organogram position slug → Spatie role.
     *
     * @return array<string, string>
     */
    public static function positionRoleMap(): array
    {
        return [
            'director-audit' => 'director_audit',
            'joint-director-audit' => 'audit_manager',
            'deputy-director-audit' => 'audit_manager',
            'assistant-director-audit' => 'audit_manager',
            'senior-officer-audit' => 'senior_officer',
            'officer-audit' => 'audit_officer',
            'audit-officer' => 'audit_officer',
        ];
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
                'notes' => 'Creates logins and assigns roles for every organogram person.',
            ],
            'director_audit' => [
                'label' => 'Director Audit',
                'summary' => 'Leadership oversight',
                'menus' => [
                    'Ops Dashboard',
                    'Organogram',
                    'Annual Audit',
                    'Monthly Visits',
                    'Projects',
                    'KPI',
                    'Audit Reports',
                    'Findings Matrix',
                    'Shakha / Areas',
                    'Map',
                ],
                'notes' => 'Sees organisation-wide performance. Cannot manage user logins.',
            ],
            'audit_manager' => [
                'label' => 'Audit Manager',
                'summary' => 'Planning & operations (JD / DD / AD)',
                'menus' => [
                    'Ops Dashboard',
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
                ],
                'notes' => 'Joint / Deputy / Assistant Directors. Full planning and all branches.',
            ],
            'senior_officer' => [
                'label' => 'Senior Officer',
                'summary' => 'Field lead + matrix visibility',
                'menus' => [
                    'Officer Dashboard',
                    'Monthly Visits (execute)',
                    'Audit Reports',
                    'Findings Matrix',
                    'KPI',
                    'Risk',
                    'All shakhas',
                    'Map',
                ],
                'notes' => 'Personal visit dashboard, can view findings across branches and enter findings.',
            ],
            'audit_officer' => [
                'label' => 'Audit Officer',
                'summary' => 'Field work on assigned branches',
                'menus' => [
                    'Officer Dashboard',
                    'Map',
                    'Monthly Visits (execute)',
                    'Audit Reports',
                    'Checklists',
                    'Findings (enter)',
                    'Profile',
                ],
                'notes' => 'Shakhas come from Monthly Visits allocations. Admin can grant extra branches.',
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
            if (isset($catalog[$role->name]) || self::isPersonalAccessRole($role->name)) {
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
                    'calendar.manage' => 'Working Calendar (manage off days)',
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
                    'audits.review' => 'Audit reports (review panel)',
                    'audits.review_assign' => 'Audit reports (assign reviewers)',
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
            'calendar.manage' => 'Calendar',
            'projects.manage' => 'Projects',
            'kpis.manage' => 'KPI',
            'risk.manage' => 'Risk',
            'shakhas.manage' => 'Shakha',
            'shakhas.view_all' => 'All shakhas',
            'areas.manage' => 'Areas',
            'audits.create' => 'Audit Reports',
            'audits.manage' => 'Audit Reports',
            'audits.review' => 'Review Panel',
            'audits.review_assign' => 'Reviewer Assignments',
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
     * Suggest a Spatie role from organogram position slug and/or title.
     */
    public static function suggestedRoleFromPosition(?string $positionTitle, ?string $positionSlug = null): string
    {
        $slug = trim((string) $positionSlug);
        $map = self::positionRoleMap();
        if ($slug !== '' && isset($map[$slug])) {
            return $map[$slug];
        }

        $title = mb_strtolower(trim((string) $positionTitle));
        if ($title === '') {
            return 'audit_officer';
        }

        if (str_contains($title, 'director') && ! str_contains($title, 'assistant') && ! str_contains($title, 'deputy') && ! str_contains($title, 'joint')) {
            return 'director_audit';
        }

        if (str_contains($title, 'director') || str_contains($title, 'manager') || str_contains($title, 'joint')) {
            return 'audit_manager';
        }

        if (str_contains($title, 'senior')) {
            return 'senior_officer';
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
