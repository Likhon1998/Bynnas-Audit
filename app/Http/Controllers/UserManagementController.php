<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Shakha;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('users.form', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'employee_id' => $data['employee_id'] ?: null,
            'is_active' => $data['is_active'],
            'is_superadmin' => $data['role'] === 'superadmin',
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$data['role']]);
        $user->assignedShakhas()->sync($data['shakha_ids'] ?? []);

        return redirect()
            ->route('users.index')
            ->with('status', 'User account created.');
    }

    public function edit(User $user): View
    {
        $user->load(['roles', 'assignedShakhas']);

        return view('users.form', array_merge($this->formData(), [
            'user' => $user,
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
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles([$data['role']]);
        $user->assignedShakhas()->sync($data['shakha_ids'] ?? []);

        return redirect()
            ->route('users.index')
            ->with('status', 'User account updated.');
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
            'employees' => Employee::query()->with('position')->orderBy('name')->get(),
            'shakhas' => Shakha::query()->orderBy('name')->get(['id', 'name', 'code']),
            'user' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?User $user = null): array
    {
        $roleNames = Role::query()->pluck('name')->all();

        $request->merge([
            'is_active' => $request->boolean('is_active'),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [
                $user ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],
            'role' => ['required', Rule::in($roleNames)],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'is_active' => ['required', 'boolean'],
            'shakha_ids' => ['nullable', 'array'],
            'shakha_ids.*' => ['integer', 'exists:shakhas,id'],
        ]);
    }
}
