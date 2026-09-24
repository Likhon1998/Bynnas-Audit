<x-app-layout>
    <div class="px-3 py-3 lg:px-5">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-navy-900">Users &amp; Access</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Assign a role to each login · create roles &amp; permissions separately · optional extra branches</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form
                    method="POST"
                    action="{{ route('users.sync-auditors') }}"
                    data-bynnas-confirm="Assign every auditor the Audit Officer role. Mapped reviewers get Auditor + Reviewer."
                    data-bynnas-confirm-title="Apply correct auditor roles?"
                    data-bynnas-confirm-ok="Assign roles"
                    data-bynnas-confirm-tone="sky"
                >
                    @csrf
                    <button type="submit" class="inline-flex h-8 items-center rounded-lg border border-sky-200 bg-sky-50 px-3 text-[12px] font-semibold text-sky-900 hover:bg-sky-100">
                        Fix auditor access
                    </button>
                </form>
                <a href="{{ route('roles.index') }}" class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">
                    Manage roles
                </a>
                <a href="{{ route('users.create') }}" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-medium text-white hover:bg-navy-800">
                    + Grant access
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @error('user')
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-800">{{ $message }}</div>
        @enderror

        {{-- Role cheat sheet --}}
        <div class="mb-3 rounded-xl border border-sky-100 bg-sky-50/40 px-3.5 py-3">
            <p class="text-[13px] font-bold uppercase tracking-wide text-sky-800">How access works</p>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach (\App\Support\RoleAccess::accessModelGuide() as $tip)
                    <div class="rounded-lg border border-sky-100 bg-white/80 px-2.5 py-2">
                        <p class="text-[12px] font-semibold text-navy-900">{{ $tip['title'] }}</p>
                        <p class="mt-0.5 text-[13px] leading-relaxed text-slate-600">{{ $tip['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($roleCatalog as $key => $role)
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-[12px] font-semibold text-navy-900">{{ $role['label'] }}</p>
                        @unless (\App\Support\RoleAccess::isSystemRole($key))
                            <span class="rounded-full bg-sky-50 px-1.5 py-0.5 text-xs font-semibold text-sky-700">Custom</span>
                        @endunless
                    </div>
                    <p class="text-xs text-slate-500">{{ $role['summary'] }}</p>
                    @if (! empty($role['menus']))
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-500">
                            <span class="font-semibold text-slate-600">Opens:</span>
                            {{ implode(' · ', array_slice($role['menus'], 0, 8)) }}{{ count($role['menus']) > 8 ? ' · …' : '' }}
                        </p>
                    @endif
                    <p class="mt-1.5 text-xs leading-relaxed text-slate-400">{{ $role['notes'] }}</p>
                </div>
            @endforeach
        </div>
        <div class="mb-3">
            <a href="{{ route('roles.index') }}" class="text-[13px] font-semibold text-[#2b579a] hover:underline">Manage roles →</a>
        </div>

        @if ($employeesWithoutLogin->isNotEmpty())
            <div class="mb-3 overflow-hidden rounded-xl border border-amber-200 bg-amber-50/60 shadow-sm">
                <div class="border-b border-amber-100 px-3.5 py-2.5">
                    <p class="text-[13px] font-semibold text-amber-900">Employees without login ({{ $employeesWithoutLogin->count() }})</p>
                    <p class="text-xs text-amber-800/80">In organogram but cannot sign in until you allocate credentials</p>
                </div>
                <div class="divide-y divide-amber-100/80">
                    @foreach ($employeesWithoutLogin->take(12) as $employee)
                        <div class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2">
                            <div class="min-w-0">
                                <p class="truncate text-[12px] font-medium text-navy-900">{{ $employee->name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $employee->position?->title ?? '—' }}
                                    @if ($employee->email) · {{ $employee->email }} @endif
                                </p>
                            </div>
                            <a
                                href="{{ route('users.create', ['employee_id' => $employee->id]) }}"
                                class="inline-flex h-7 items-center rounded-md bg-[#2b579a] px-2.5 text-[13px] font-semibold text-white hover:bg-[#204072]"
                            >Grant access</a>
                        </div>
                    @endforeach
                </div>
                @if ($employeesWithoutLogin->count() > 12)
                    <p class="border-t border-amber-100 px-3.5 py-2 text-[13px] text-amber-800/70">+ {{ $employeesWithoutLogin->count() - 12 }} more — use New login and pick the employee</p>
                @endif
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-left text-[12px]">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">User</th>
                        <th class="px-3 py-2">Role</th>
                        <th class="px-3 py-2">Employee</th>
                        <th class="px-3 py-2">Assigned shakhas</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-3 py-2.5">
                                <p class="font-semibold text-navy-900">{{ $user->name }}</p>
                                <p class="text-[13px] text-slate-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="rounded-full bg-sky-50 px-2 py-0.5 text-xs font-semibold text-sky-800">{{ $user->roleLabel() }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-slate-600">
                                {{ $user->employee?->name ?? '—' }}
                                @if ($user->employee?->position)
                                    <span class="text-xs text-slate-500">· {{ $user->employee->position->title }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-600">
                                @if ($user->can('shakhas.view_all') || $user->hasAnyRole(['superadmin', 'audit_manager']))
                                    <span class="text-[13px] text-slate-500">All (by role)</span>
                                @else
                                    {{ $user->assignedShakhas->count() }} explicit
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                @if ($user->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Active</span>
                                @else
                                    <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">Deactivated</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="text-[13px] font-semibold text-[#2b579a] hover:underline">Edit access</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.toggle-active', $user) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="text-[13px] font-semibold {{ $user->is_active ? 'text-amber-700 hover:underline' : 'text-emerald-700 hover:underline' }}"
                                                onclick="return confirm('{{ $user->is_active ? 'Deactivate this login? They will not be able to sign in.' : 'Reactivate this login?' }}')"
                                            >{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="text-[13px] font-semibold text-rose-600 hover:underline"
                                                onclick="return confirm('Permanently delete this login? The organogram employee record (if linked) will be kept.')"
                                            >Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-3 text-center text-slate-500">No users yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
