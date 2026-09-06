<?php

namespace App\Http\Controllers;

use App\Support\RoleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleManagementController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('roles.index', [
            'roles' => $roles,
            'catalog' => RoleAccess::catalogWithCustom(),
            'permissionGroups' => RoleAccess::permissionGroups(),
        ]);
    }

    public function create(): View
    {
        return view('roles.form', [
            'role' => null,
            'permissionGroups' => RoleAccess::permissionGroups(),
            'selectedPermissions' => [],
            'isSystem' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $role = DB::transaction(function () use ($data) {
            $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
            $role->syncPermissions($data['permissions']);

            return $role;
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role created: '.RoleAccess::label($role->name).'.');
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');

        return view('roles.form', [
            'role' => $role,
            'permissionGroups' => RoleAccess::permissionGroups(),
            'selectedPermissions' => $role->permissions->pluck('name')->all(),
            'isSystem' => RoleAccess::isSystemRole($role->name),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $isSystem = RoleAccess::isSystemRole($role->name);

        if ($role->name === 'superadmin') {
            return back()->withErrors(['role' => 'Super Admin always has all permissions and cannot be edited.']);
        }

        $data = $this->validated($request, $role, renameAllowed: ! $isSystem);

        DB::transaction(function () use ($role, $data, $isSystem) {
            if (! $isSystem && $data['name'] !== $role->name) {
                $role->name = $data['name'];
                $role->save();
            }

            $role->syncPermissions($data['permissions']);
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role updated: '.RoleAccess::label($role->fresh()->name).'.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (RoleAccess::isSystemRole($role->name)) {
            return back()->withErrors(['role' => 'Built-in roles cannot be deleted.']);
        }

        if ($role->users()->count() > 0) {
            return back()->withErrors(['role' => 'Reassign users before deleting this role.']);
        }

        $label = RoleAccess::label($role->name);
        $role->syncPermissions([]);
        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role deleted: '.$label.'.');
    }

    /**
     * @return array{name:string,permissions:list<string>}
     */
    protected function validated(Request $request, ?Role $role = null, bool $renameAllowed = true): array
    {
        $allowedPermissions = Permission::query()->pluck('name')->all();

        $nameRules = $renameAllowed
            ? [
                'required',
                'string',
                'max:60',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('roles', 'name')->ignore($role?->id),
                Rule::notIn(RoleAccess::systemRoles()),
            ]
            : ['nullable', 'string'];

        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'name' => $nameRules,
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($allowedPermissions)],
        ], [
            'name.regex' => 'Role key must start with a letter and use only lowercase letters, numbers, and underscores.',
            'name.not_in' => 'That key is reserved for a built-in role.',
        ]);

        $permissions = array_values(array_unique($data['permissions'] ?? []));

        if ($permissions === []) {
            throw ValidationException::withMessages([
                'permissions' => 'Select at least one permission.',
            ]);
        }

        $name = $renameAllowed
            ? ($data['name'] ?: RoleAccess::slugFromLabel($data['label']))
            : $role->name;

        if ($renameAllowed && RoleAccess::isSystemRole($name)) {
            throw ValidationException::withMessages([
                'name' => 'That key is reserved for a built-in role.',
            ]);
        }

        if ($renameAllowed && Role::query()->where('name', $name)->when($role, fn ($q) => $q->where('id', '!=', $role->id))->exists()) {
            throw ValidationException::withMessages([
                'name' => 'A role with this key already exists.',
            ]);
        }

        return [
            'name' => $name,
            'permissions' => $permissions,
        ];
    }
}
