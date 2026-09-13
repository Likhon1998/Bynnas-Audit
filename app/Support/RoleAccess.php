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
            'auditor_reviewer',
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
     * Short guide shown on Users & Access / Grant access.
     *
     * @return list<array{title:string,body:string}>
     */
    public static function accessModelGuide(): array
    {
        return [
            [
                'title' => '1 · Build roles',
                'body' => 'On Manage roles, create or edit a role and tick the permissions it should unlock. That is where access is defined.',
            ],
            [
                'title' => '2 · Assign a role',
                'body' => 'On Grant access, pick one role for the login. The user receives exactly that role’s access — no per-user permission ticks.',
            ],
            [
                'title' => 'Auditor vs reviewer',
                'body' => 'Audit Officer = maker only. Auditor + Reviewer = maker who can also review assigned reports. Map them under Assign reviewers.',
            ],
            [
                'title' => 'Need a special mix?',
                'body' => 'Create a custom role with the exact permissions, then assign that role to the person.',
            ],
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
                    'Roles',
                    'Organogram',
                    'Annual Audit',
                    'Monthly Visits',
                    'Working Calendar',
                    'Projects',
                    'KPI',
                    'Risk',
                    'Audit Reports',
                    'Checklists',
                    'Review Panel',
                    'Assign reviewers',
                    'Auditors log',
                    'Findings Matrix',
                    'Shakha / Areas',
                    'Map',
                    'Ops Dashboard',
                    'Superadmin chat',
                ],
                'notes' => 'Creates logins, manages roles, sees every report/review. Superadmin chat is role-locked (not a permission tick).',
            ],
            'director_audit' => [
                'label' => 'Director Audit',
                'summary' => 'Leadership oversight (no user logins)',
                'menus' => [
                    'Ops Dashboard',
                    'Organogram',
                    'Annual Audit',
                    'Monthly Visits',
                    'Working Calendar',
                    'Projects',
                    'KPI',
                    'Risk',
                    'Audit Reports',
                    'Checklists',
                    'Review Panel',
                    'Assign reviewers',
                    'Auditors log',
                    'Findings Matrix',
                    'Shakha / Areas',
                    'Map',
                ],
                'notes' => 'Same planning & review powers as managers. Cannot manage Users & Access / Roles.',
            ],
            'audit_manager' => [
                'label' => 'Audit Manager',
                'summary' => 'Planning & operations (JD / DD / AD)',
                'menus' => [
                    'Ops Dashboard',
                    'Organogram',
                    'Annual Audit',
                    'Monthly Visits',
                    'Working Calendar',
                    'Projects',
                    'KPI',
                    'Risk',
                    'Audit Reports',
                    'Checklists',
                    'Review Panel',
                    'Assign reviewers',
                    'Auditors log',
                    'Findings Matrix',
                    'Shakha / Areas',
                    'Map',
                ],
                'notes' => 'Joint / Deputy / Assistant Directors. Full planning, all branches, can assign reviewers and review.',
            ],
            'senior_officer' => [
                'label' => 'Senior Officer',
                'summary' => 'Field lead + can review assigned reports',
                'menus' => [
                    'Officer Dashboard',
                    'Organogram (view)',
                    'Annual Audit Plan (view)',
                    'Monthly Visits (execute)',
                    'Working Calendar (view)',
                    'Projects (view)',
                    'Audit Reports',
                    'Checklists',
                    'Review Panel (as reviewer)',
                    'Findings Matrix (full)',
                    'Findings Summary',
                    'Enter Findings',
                    'KPI',
                    'Risk',
                    'All shakhas',
                    'Map',
                ],
                'notes' => 'Maker + reviewer with matrix & summary access. Can view calendar and projects. Does not assign reviewers. Change access by editing this role.',
            ],
            'auditor_reviewer' => [
                'label' => 'Auditor + Reviewer',
                'summary' => 'Field auditor who can also review',
                'menus' => [
                    'Officer Dashboard',
                    'Map',
                    'Monthly Visits (execute)',
                    'Working Calendar (view)',
                    'Audit Reports',
                    'Checklists',
                    'Review Panel (as reviewer)',
                    'Enter Findings',
                    'Profile',
                ],
                'notes' => 'Same as Audit Officer plus Act as reviewer. Assign this role, then map them under Assign reviewers.',
            ],
            'audit_officer' => [
                'label' => 'Audit Officer (Auditor)',
                'summary' => 'Field maker on assigned branches',
                'menus' => [
                    'Officer Dashboard',
                    'Map',
                    'Monthly Visits (execute)',
                    'Working Calendar (view)',
                    'Audit Reports',
                    'Checklists',
                    'Review Panel (as maker — returned work)',
                    'Enter Findings',
                    'Profile',
                ],
                'notes' => 'Default auditor role. To also review: assign Auditor + Reviewer (or a custom role), then Assign reviewers.',
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
     * Permission key → human label + help, grouped for Access Panel / Roles builder.
     *
     * @return array<string, array{label:string,permissions:array<string, array{label:string,help:string}>}>
     */
    public static function permissionGroups(): array
    {
        return [
            'Access' => [
                'label' => 'Access',
                'permissions' => [
                    'users.manage' => [
                        'label' => 'Users & Access / Roles',
                        'help' => 'Create logins, grant access, build custom roles. Super Admin only by default.',
                    ],
                ],
            ],
            'Organisation' => [
                'label' => 'Organisation',
                'permissions' => [
                    'organogram.view' => [
                        'label' => 'Organogram (view)',
                        'help' => 'See the organisation chart and people.',
                    ],
                    'organogram.manage' => [
                        'label' => 'Organogram (manage)',
                        'help' => 'Add/edit positions and employees on the organogram.',
                    ],
                    'shakhas.view_all' => [
                        'label' => 'All shakhas (no visit scope)',
                        'help' => 'Browse every branch — not limited to Monthly Visits allocations.',
                    ],
                    'shakhas.manage' => [
                        'label' => 'Manage shakhas',
                        'help' => 'Create/edit shakhas, employees list, dossiers.',
                    ],
                    'areas.manage' => [
                        'label' => 'Manage areas',
                        'help' => 'Create/edit geographic areas used by shakhas.',
                    ],
                ],
            ],
            'Planning' => [
                'label' => 'Planning',
                'permissions' => [
                    'annual_audit.view' => [
                        'label' => 'Annual Audit Plan (view only)',
                        'help' => 'Open and export the yearly plan. Cannot change policies, generate, or edit months.',
                    ],
                    'annual_audit.manage' => [
                        'label' => 'Annual Audit Plan (manage)',
                        'help' => 'Full edit: policies, generate, toggle months, create FY, projects on the plan.',
                    ],
                    'monthly_visits.manage' => [
                        'label' => 'Monthly Visits (manage)',
                        'help' => 'Allocate visits / officers. Also unlocks Working Calendar view.',
                    ],
                    'monthly_visits.execute' => [
                        'label' => 'Monthly Visits (execute)',
                        'help' => 'Do assigned field visits. Unlocks Working Calendar view. Branches for reports come from allocations.',
                    ],
                    'calendar.view' => [
                        'label' => 'Working Calendar (view only)',
                        'help' => 'See holidays and off days. Cannot add or edit calendar entries.',
                    ],
                    'calendar.manage' => [
                        'label' => 'Working Calendar (manage off days)',
                        'help' => 'Add/edit holidays and weekend settings.',
                    ],
                    'projects.view' => [
                        'label' => 'Projects (view only)',
                        'help' => 'Browse projects and locations. Cannot create or edit.',
                    ],
                    'projects.manage' => [
                        'label' => 'Projects (manage)',
                        'help' => 'Create/edit projects and project locations.',
                    ],
                    'kpis.manage' => [
                        'label' => 'KPI',
                        'help' => 'Key performance indicators module.',
                    ],
                    'risk.manage' => [
                        'label' => 'Risk assessment',
                        'help' => 'Shakha risk tools and related assessment screens.',
                    ],
                ],
            ],
            'Field work' => [
                'label' => 'Field work & review',
                'permissions' => [
                    'audits.create' => [
                        'label' => 'Audit reports (create) — maker / auditor',
                        'help' => 'Write own reports + Checklists. Review Panel opens for returned fixes. Can also be made a reviewer separately.',
                    ],
                    'audits.manage' => [
                        'label' => 'Audit reports (manage)',
                        'help' => 'Leadership override on report workflows (with create). Does not replace Users & Access.',
                    ],
                    'audits.review' => [
                        'label' => 'Act as reviewer (Review Panel)',
                        'help' => 'Review reports assigned to this person (inbox, marks, send back, confirm). An auditor can hold this too.',
                    ],
                    'audits.review_assign' => [
                        'label' => 'Assign reviewers + Auditors log',
                        'help' => 'Map Auditor → Reviewer, watch the Auditors log (history only — no review actions from the log), and step into any report from Review Panel when needed.',
                    ],
                    'findings.enter' => [
                        'label' => 'Enter Findings',
                        'help' => 'Enter findings data for branches. Feeds the matrix and summary.',
                    ],
                    'findings.view_all' => [
                        'label' => 'Findings Matrix (full access)',
                        'help' => 'Full matrix across branches, indicator drill-down, and matrix Excel export.',
                    ],
                    'findings.summary.view' => [
                        'label' => 'Findings Summary (view)',
                        'help' => 'Open the Findings Summary page and download Excel.',
                    ],
                    'findings.summary.export_ppt' => [
                        'label' => 'Findings Summary — download PPT',
                        'help' => 'Download the PowerPoint export from Findings Summary.',
                    ],
                    'findings.summary.edit' => [
                        'label' => 'Findings Summary (edit)',
                        'help' => 'Edit accused staff / summary-linked finding fields on indicator detail.',
                    ],
                ],
            ],
            'Dashboard' => [
                'label' => 'Dashboard & map',
                'permissions' => [
                    'dashboard.ops' => [
                        'label' => 'Ops dashboard',
                        'help' => 'Leadership / operations home dashboard.',
                    ],
                    'dashboard.officer' => [
                        'label' => 'Officer dashboard',
                        'help' => 'Personal field-officer home dashboard.',
                    ],
                    'map.view' => [
                        'label' => 'Map',
                        'help' => 'Geographic map of shakhas / visits (when map feature is on).',
                    ],
                ],
            ],
        ];
    }

    public static function permissionLabel(string $permission): string
    {
        foreach (self::permissionGroups() as $group) {
            if (isset($group['permissions'][$permission])) {
                return $group['permissions'][$permission]['label'];
            }
        }

        return $permission;
    }

    public static function permissionHelp(string $permission): string
    {
        foreach (self::permissionGroups() as $group) {
            if (isset($group['permissions'][$permission])) {
                return $group['permissions'][$permission]['help'];
            }
        }

        return '';
    }

    /**
     * Permission → sidebar / feature labels unlocked (for live Access Panel preview).
     *
     * @return array<string, list<string>>
     */
    public static function permissionMenuMap(): array
    {
        return [
            'users.manage' => ['Users & Access', 'Roles'],
            'organogram.view' => ['Organogram'],
            'organogram.manage' => ['Organogram'],
            'annual_audit.view' => ['Annual Audit Plan (view)'],
            'annual_audit.manage' => ['Annual Audit Plan (manage)'],
            'monthly_visits.manage' => ['Monthly Visits', 'Working Calendar (view)'],
            'monthly_visits.execute' => ['Monthly Visits (execute)', 'Working Calendar (view)'],
            'calendar.view' => ['Working Calendar (view)'],
            'calendar.manage' => ['Working Calendar (manage)'],
            'projects.view' => ['Projects (view)'],
            'projects.manage' => ['Projects (manage)'],
            'kpis.manage' => ['KPI'],
            'risk.manage' => ['Risk'],
            'shakhas.manage' => ['Shakha', 'Shakha Employees'],
            'shakhas.view_all' => ['All shakhas', 'Shakha Employees'],
            'areas.manage' => ['Areas'],
            'audits.create' => ['Audit Reports', 'Checklists', 'Review Panel (as maker)'],
            'audits.manage' => ['Audit Reports', 'Checklists', 'Review Panel'],
            'audits.review' => ['Review Panel (as reviewer)'],
            'audits.review_assign' => ['Assign reviewers', 'Auditors log'],
            'findings.enter' => ['Enter Findings'],
            'findings.view_all' => ['Findings Matrix (full)'],
            'findings.summary.view' => ['Findings Summary'],
            'findings.summary.export_ppt' => ['Findings Summary PPT'],
            'findings.summary.edit' => ['Findings Summary (edit)'],
            'dashboard.ops' => ['Ops Dashboard'],
            'dashboard.officer' => ['Officer Dashboard'],
            'map.view' => ['Map'],
        ];
    }

    /**
     * @param  Collection<int, string>|iterable<int, string>  $permissions
     * @return list<string>
     */
    public static function menusFromPermissions(iterable $permissions): array
    {
        $set = collect($permissions)->map(fn ($p) => (string) $p)->flip();
        $menus = [];
        $map = self::permissionMenuMap();

        foreach ($map as $permission => $labels) {
            if (! $set->has($permission)) {
                continue;
            }
            foreach ($labels as $label) {
                if (! in_array($label, $menus, true)) {
                    $menus[] = $label;
                }
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
