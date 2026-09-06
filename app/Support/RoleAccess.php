<?php

namespace App\Support;

/**
 * Spatie role → what the person can see / do in the app.
 * Positions (organogram) are HR titles; roles control login access.
 */
class RoleAccess
{
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
                    'Ops Dashboard',
                ],
                'notes' => 'Sees all branches. Cannot manage user logins (superadmin only).',
            ],
            'audit_officer' => [
                'label' => 'Audit Officer',
                'summary' => 'Field work on assigned branches',
                'menus' => [
                    'Dashboard (my work)',
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

    public static function label(string $role): string
    {
        return self::catalog()[$role]['label'] ?? $role;
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
}
