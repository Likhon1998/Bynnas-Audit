<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use App\Models\Shakha;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with(['roles', 'employee.position', 'assignedShakhas'])
            ->orderBy('name')
            ->get();

        $employeesWithoutLogin = Employee::query()
            ->with('position')
            ->whereDoesntHave('user')
            ->orderBy('name')
            ->get();

        return view('users.index', [
            'users' => $users,
            'employeesWithoutLogin' => $employeesWithoutLogin,
            'roleCatalog' => RoleAccess::catalog(),
        ]);
    }

    public function create(Request $request): View
    {
        $prefillEmployeeId = $request->integer('employee_id') ?: null;
        $suggestedRole = 'audit_officer';

        if ($prefillEmployeeId) {
            $employee = Employee::query()->with('position')->find($prefillEmployeeId);
            $suggestedRole = RoleAccess::suggestedRoleFromPosition($employee?->position?->title);
        }

        return view('users.form', array_merge($this->formData(), [
            'prefillEmployeeId' => $prefillEmployeeId,
            'suggestedRole' => $suggestedRole,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = DB::transaction(function () use ($data) {
            $employeeId = $data['employee_id'] ?: null;

            if (! empty($data['create_employee'])) {
                $employee = Employee::query()->create([
                    'position_id' => $data['position_id'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'sort_order' => ((int) Employee::query()
                        ->where('position_id', $data['position_id'])
                        ->max('sort_order')) + 1,
                ]);
                $employeeId = $employee->id;
            }

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'employee_id' => $employeeId,
                'is_active' => $data['is_active'],
                'is_superadmin' => $data['role'] === 'superadmin',
                'email_verified_at' => now(),
            ]);

            $user->syncRoles([$data['role']]);
            $user->assignedShakhas()->sync($data['shakha_ids'] ?? []);

            return $user;
        });

        return redirect()
            ->route('users.index')
            ->with('status', 'Login created for '.$user->name.' ('.RoleAccess::label($data['role']).').');
    }

    public function edit(User $user): View
    {
        $user->load(['roles', 'assignedShakhas', 'employee.position']);

        return view('users.form', array_merge($this->formData(), [
            'user' => $user,
            'prefillEmployeeId' => null,
        ]));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'employee_id' => $data['employee_id'] ?: null,
            'is_active' => $data['is_active'],
            'is_superadmin' => $data['role'] === 'superadmin',
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->syncRoles([$data['role']]);
        $user->assignedShakhas()->sync($data['shakha_ids'] ?? []);

        return redirect()
            ->route('users.index')
            ->with('status', 'Access updated for '.$user->name.'.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'User deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'roleCatalog' => RoleAccess::catalog(),
            'employees' => Employee::query()->with(['position', 'user'])->orderBy('name')->get(),
            'positions' => Position::query()->orderBy('serial')->get(),
            'shakhas' => Shakha::query()->orderBy('name')->get(['id', 'name', 'code']),
            'user' => null,
            'suggestedRole' => 'audit_officer',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?User $user = null): array
    {
        $roleNames = Role::query()->pluck('name')->all();
        $creatingEmployee = ! $user && $request->boolean('create_employee');

        $request->merge([
            'is_active' => $request->boolean('is_active'),
            'create_employee' => $creatingEmployee,
            'employee_id' => $creatingEmployee ? null : ($request->input('employee_id') ?: null),
        ]);

        $emailRules = [
            'required',
            'email',
            'max:190',
            Rule::unique('users', 'email')->ignore($user?->id),
        ];

        if ($creatingEmployee) {
            $emailRules[] = Rule::unique('employees', 'email');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => $emailRules,
            'password' => [
                $user ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],
            'role' => ['required', Rule::in($roleNames)],
            'employee_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
                Rule::prohibitedIf($creatingEmployee),
            ],
            'create_employee' => ['sometimes', 'boolean'],
            'position_id' => [
                Rule::requiredIf($creatingEmployee),
                'nullable',
                'integer',
                'exists:positions,id',
            ],
            'is_active' => ['required', 'boolean'],
            'shakha_ids' => ['nullable', 'array'],
            'shakha_ids.*' => ['integer', 'exists:shakhas,id'],
        ], [
            'position_id.required' => 'Select an organogram position when creating a new employee.',
            'employee_id.prohibited' => 'Clear the employee link when creating a new employee.',
        ]);

        if (! empty($data['employee_id'])) {
            $taken = User::query()
                ->where('employee_id', $data['employee_id'])
                ->when($user, fn ($q) => $q->where('id', '!=', $user->id))
                ->exists();

            if ($taken) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'employee_id' => 'That employee already has a login account.',
                ]);
            }
        }

        return $data;
    }
}
