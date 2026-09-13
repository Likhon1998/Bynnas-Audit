<?php

namespace App\Services;

use App\Models\AuditReviewerAssignment;
use App\Models\User;
use App\Support\RoleAccess;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AuditorAccessSyncService
{
    /**
     * Assign every Audit Officer the correct role (role-based access only).
     * Mapped reviewers get auditor_reviewer; others get audit_officer.
     *
     * @return array{synced:int, with_review:int, package:list<string>, users:list<array{id:int,name:string,email:string,permissions:list<string>}>}
     */
    public function syncAll(): array
    {
        Artisan::call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $officerDefaults = RoleAccess::permissionNamesForRole('audit_officer');
        sort($officerDefaults);

        $reviewerIds = AuditReviewerAssignment::query()
            ->pluck('reviewer_user_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $auditors = User::query()
            ->where('is_superadmin', false)
            ->where(function ($q) {
                $q->whereIn('access_profile', ['audit_officer', 'auditor_reviewer'])
                    ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['audit_officer', 'auditor_reviewer']))
                    ->orWhereHas('roles', fn ($r) => $r->where('name', 'like', 'u%_access'));
            })
            ->orderBy('id')
            ->get()
            ->filter(function (User $user) {
                $profile = (string) ($user->access_profile ?: '');
                $roles = $user->getRoleNames()->map(fn ($n) => (string) $n)->all();

                if (in_array($profile, ['audit_officer', 'auditor_reviewer'], true)) {
                    return true;
                }

                if (array_intersect($roles, ['audit_officer', 'auditor_reviewer']) !== []) {
                    return true;
                }

                // Legacy personal packages that were officer-shaped.
                $onlyPersonal = count($roles) === 1 && RoleAccess::isPersonalAccessRole($roles[0]);

                return $onlyPersonal
                    && $user->can('audits.create')
                    && ! $user->can('audits.manage')
                    && ! $user->can('users.manage')
                    && ! $user->can('dashboard.ops');
            })
            ->values();

        $users = [];
        $withReview = 0;

        foreach ($auditors as $user) {
            $asReviewer = in_array((int) $user->id, $reviewerIds, true);
            $roleName = $asReviewer ? 'auditor_reviewer' : 'audit_officer';

            if ($asReviewer) {
                $withReview++;
            }

            $user->forceFill([
                'is_superadmin' => false,
                'access_profile' => $roleName,
            ])->save();
            $user->syncRoles([$roleName]);
            $user->syncPermissions([]);
            Role::query()->where('name', RoleAccess::personalAccessRoleName((int) $user->id))->delete();

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $fresh = $user->fresh();
            $users[] = [
                'id' => (int) $fresh->id,
                'name' => (string) $fresh->name,
                'email' => (string) $fresh->email,
                'permissions' => $fresh->getAllPermissions()->pluck('name')->sort()->values()->all(),
            ];
        }

        return [
            'synced' => count($users),
            'with_review' => $withReview,
            'package' => $officerDefaults,
            'users' => $users,
        ];
    }
}
