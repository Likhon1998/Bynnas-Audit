<x-app-layout>
    @php
        /** @var \App\Models\User|null $user */
        $editing = (bool) $user;
        $selectedRole = old('role', $user?->roleKey() ?: ($suggestedRole ?? 'audit_officer'));
        if (! in_array($selectedRole, $roles, true)) {
            $selectedRole = $suggestedRole ?? 'audit_officer';
        }
        $selectedShakhas = collect(old('shakha_ids', $user?->assignedShakhas?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
        $defaultEmployeeId = (int) old('employee_id', $user?->employee_id ?: ($prefillEmployeeId ?? 0));
        $createEmployee = (bool) old('create_employee', false);
        $prefillEmployee = $employees->firstWhere('id', $defaultEmployeeId);
        $rolePermissionMap = $rolePermissionMap ?? [];
        $permissionMenuMap = $permissionMenuMap ?? \App\Support\RoleAccess::permissionMenuMap();
    @endphp

    <div class="px-3 py-3 lg:px-5">
        <div class="mb-3">
            <a href="{{ route('users.index') }}" class="text-[13px] font-medium text-[#2b579a] hover:underline">← Back to users</a>
            <h1 class="mt-1 text-lg font-semibold tracking-tight text-navy-900">
                {{ $editing ? 'Edit access' : 'Grant access' }}
            </h1>
            <p class="mt-0.5 text-[12px] text-slate-500">
                Choose the person → assign one role → optionally add extra branches
            </p>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif

        <form
            method="POST"
            action="{{ $editing ? route('users.update', $user) : route('users.store') }}"
            class="mx-auto max-w-4xl space-y-3"
            x-data="grantAccess({
                createEmployee: {{ $createEmployee && ! $editing ? 'true' : 'false' }},
                role: @js($selectedRole),
                rolePermissionMap: @js($rolePermissionMap),
                permissionMenuMap: @js($permissionMenuMap),
                roleCatalog: @js($roleCatalog),
            })"
        >
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            {{-- 1. Who --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                    <p class="text-[13px] font-bold uppercase tracking-[0.12em] text-slate-500">1 · Who</p>
                    <p class="text-[12px] font-semibold text-navy-900">Person &amp; login</p>
                </div>
                <div class="space-y-3 p-4">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[13px] font-semibold uppercase tracking-wide text-slate-500">Name</label>
                            <input type="text" name="name" value="{{ old('name', $user?->name ?? $prefillEmployee?->name) }}" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                            @error('name') <p class="mt-1 text-[13px] text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[13px] font-semibold uppercase tracking-wide text-slate-500">Email (login)</label>
                            <input type="email" name="email" value="{{ old('email', $user?->email ?? $prefillEmployee?->email) }}" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                            @error('email') <p class="mt-1 text-[13px] text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[13px] font-semibold uppercase tracking-wide text-slate-500">Mail send email</label>
                        <input type="email" name="mail_from_email" value="{{ old('mail_from_email', $user?->mail_from_email) }}" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" placeholder="Optional sender for audit emails">
                        @error('mail_from_email') <p class="mt-1 text-[13px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[13px] font-semibold uppercase tracking-wide text-slate-500">
                                Password {{ $editing ? '(leave blank to keep)' : '' }}
                            </label>
                            <input type="password" name="password" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" @unless($editing) required @endunless autocomplete="new-password">
                            @error('password') <p class="mt-1 text-[13px] text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[13px] font-semibold uppercase tracking-wide text-slate-500">Confirm password</label>
                            <input type="password" name="password_confirmation" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" @unless($editing) required @endunless autocomplete="new-password">
                        </div>
                    </div>

                    @unless ($editing)
                        <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-3">
                            <label class="flex items-center gap-2 text-[12px] font-medium text-slate-800">
                                <input type="checkbox" name="create_employee" value="1" class="rounded border-slate-300 text-[#2b579a]" x-model="createEmployee">
                                Also create organogram employee
                            </label>
                            <div class="mt-3" x-show="createEmployee" x-cloak>
                                <label class="mb-1 block text-[13px] font-semibold uppercase tracking-wide text-slate-500">Position</label>
                                <select name="position_id" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" :required="createEmployee">
                                    <option value="">— Select position —</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}" @selected((int) old('position_id') === $position->id)>
                                            {{ $position->serial }}. {{ $position->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id') <p class="mt-1 text-[13px] text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endunless

                    <div x-show="!createEmployee" x-cloak>
                        <label class="mb-1 block text-[13px] font-semibold uppercase tracking-wide text-slate-500">Link organogram employee</label>
                        <select name="employee_id" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" :disabled="createEmployee">
                            <option value="">— Not linked —</option>
                            @foreach ($employees as $employee)
                                @php
                                    $taken = $employee->user && (! $editing || $employee->user->id !== $user?->id);
                                @endphp
                                <option value="{{ $employee->id }}" @selected($defaultEmployeeId === $employee->id) @disabled($taken)>
                                    {{ $employee->name }}
                                    @if ($employee->position) · {{ $employee->position->title }} @endif
                                    @if ($taken) (already has login) @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Required for monthly-visit allocations and field report branches.</p>
                        @error('employee_id') <p class="mt-1 text-[13px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-start gap-2 text-[12px] text-slate-700">
                        <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-slate-300 text-[#2b579a]" @checked(old('is_active', $user?->is_active ?? true))>
                        <span>
                            <span class="font-semibold">Account active</span>
                            <span class="mt-0.5 block text-xs text-slate-500">Uncheck to block login without deleting.</span>
                        </span>
                    </label>
                </div>
            </section>

            {{-- 2. Role --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                    <p class="text-[13px] font-bold uppercase tracking-[0.12em] text-slate-500">2 · Role</p>
                    <p class="text-[12px] font-semibold text-navy-900">Assign one role (access comes from the role)</p>
                </div>
                <div class="space-y-3 p-4">
                    <div class="rounded-lg border border-sky-100 bg-sky-50/50 px-3 py-2.5 text-[13px] leading-relaxed text-sky-950">
                        Access is <strong>role-based</strong>. Define permissions on
                        <a href="{{ route('roles.index') }}" class="font-semibold text-[#2b579a] hover:underline">Manage roles</a>,
                        then assign that role here. Do not tick permissions per person.
                    </div>

                    <div>
                        <label class="mb-1 block text-[13px] font-semibold uppercase tracking-wide text-slate-500">Role</label>
                        <select name="role" x-model="role" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ $roleCatalog[$role]['label'] ?? \App\Support\RoleAccess::label($role) }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-[13px] text-slate-500" x-text="roleSummary"></p>
                        @error('role') <p class="mt-1 text-[13px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-lg border border-emerald-100 bg-emerald-50/40 px-3 py-2.5">
                        <p class="text-[13px] font-semibold text-emerald-900">This role unlocks</p>
                        <p class="mt-0.5 text-[13px] leading-relaxed text-emerald-950/90" x-text="selectedMenuLabel"></p>
                        <p class="mt-1.5 text-xs text-emerald-800/80" x-text="permissionCountLabel"></p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('roles.create') }}" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">+ Create role</a>
                        <a href="{{ route('roles.index') }}" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">Edit role permissions</a>
                    </div>
                </div>
            </section>

            {{-- 3. Where --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                    <p class="text-[13px] font-bold uppercase tracking-[0.12em] text-slate-500">3 · Where (optional)</p>
                    <p class="text-[12px] font-semibold text-navy-900">Extra shakha access</p>
                </div>
                <div class="p-4">
                    <p class="mb-2 text-[13px] text-slate-500">
                        Field staff already get branches from <strong>Monthly Visits</strong> allocations.
                        Use this only to grant <strong>extra</strong> shakhas beyond those visits.
                        Roles with “All shakhas” still open every branch.
                    </p>
                    <div class="mb-2">
                        <input type="search" x-model="shakhaQuery" placeholder="Filter shakhas…" class="h-8 w-full max-w-sm rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <div class="max-h-64 overflow-y-auto rounded-lg border border-slate-200 p-2">
                        @forelse ($shakhas as $shakha)
                            <label
                                class="flex items-center gap-2 rounded px-1.5 py-1 text-[12px] hover:bg-slate-50"
                                x-show="shakhaMatch(@js(strtolower($shakha->name.' '.$shakha->code.' '.($shakha->area?->name ?? '').' '.($shakha->area?->division ?? ''))))"
                            >
                                <input
                                    type="checkbox"
                                    name="shakha_ids[]"
                                    value="{{ $shakha->id }}"
                                    class="rounded border-slate-300 text-[#2b579a]"
                                    @checked(in_array((int) $shakha->id, $selectedShakhas, true))
                                >
                                <span class="min-w-0 flex-1">
                                    <span class="font-medium {{ $shakha->riskCategory() ? \App\Support\ShakhaRiskTone::textClasses($shakha->riskCategory()) : 'text-navy-900' }}">{{ $shakha->name }}</span>
                                    <span class="text-xs text-slate-500">
                                        {{ $shakha->code }}
                                        @if ($shakha->area)
                                            · {{ $shakha->area->division }} · {{ $shakha->area->name }}
                                        @endif
                                    </span>
                                    <x-shakha-risk-badge class="ml-1" :category="$shakha->riskCategory()" size="xs" />
                                </span>
                            </label>
                        @empty
                            <p class="px-2 py-4 text-center text-[13px] text-slate-500">No shakhas yet.</p>
                        @endforelse
                    </div>
                    @error('shakha_ids') <p class="mt-1 text-[13px] text-rose-600">{{ $message }}</p> @enderror
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                @if ($editing && $user->id !== auth()->id())
                    <button
                        type="submit"
                        form="delete-user"
                        class="text-[12px] font-medium text-rose-600 hover:underline"
                        data-bynnas-confirm="Permanently delete this login? The organogram employee (if linked) will be kept."
                        data-bynnas-confirm-title="Delete login?"
                        data-bynnas-confirm-ok="Delete login"
                        data-bynnas-confirm-tone="rose"
                    >Delete login</button>
                @else
                    <span class="text-[13px] text-slate-500">Role access is applied immediately after save.</span>
                @endif
                <button type="submit" class="inline-flex h-9 items-center rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072]">
                    {{ $editing ? 'Save access' : 'Grant access' }}
                </button>
            </div>
        </form>

        @if ($editing && $user->id !== auth()->id())
            <form id="delete-user" method="POST" action="{{ route('users.destroy', $user) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>

    <script>
        function grantAccess(cfg) {
            return {
                createEmployee: !!cfg.createEmployee,
                role: cfg.role || 'audit_officer',
                rolePermissionMap: cfg.rolePermissionMap || {},
                permissionMenuMap: cfg.permissionMenuMap || {},
                roleCatalog: cfg.roleCatalog || {},
                shakhaQuery: '',
                shakhaMatch(haystack) {
                    const q = (this.shakhaQuery || '').toLowerCase().trim();
                    if (!q) return true;
                    return q.split(/\s+/).every((t) => (haystack || '').includes(t));
                },
                rolePermissions() {
                    if (this.role === 'superadmin') {
                        const all = new Set();
                        Object.values(this.rolePermissionMap).forEach((list) => {
                            (list || []).forEach((p) => all.add(p));
                        });
                        return Array.from(all);
                    }
                    return (this.rolePermissionMap[this.role] || []).slice();
                },
                menusFromPermissions(list) {
                    const menus = [];
                    (list || []).forEach((perm) => {
                        (this.permissionMenuMap[perm] || []).forEach((label) => {
                            if (!menus.includes(label)) menus.push(label);
                        });
                    });
                    return menus;
                },
                get roleSummary() {
                    const meta = this.roleCatalog[this.role] || {};
                    return meta.notes || meta.summary || 'Access is controlled by this role’s permissions.';
                },
                get selectedMenuLabel() {
                    if (this.role === 'superadmin') return 'Full system access (all menus + superadmin chat)';
                    const menus = this.menusFromPermissions(this.rolePermissions());
                    return menus.length ? menus.join(' · ') : 'No permissions on this role yet — edit the role first.';
                },
                get permissionCountLabel() {
                    if (this.role === 'superadmin') return 'All permissions';
                    const n = this.rolePermissions().length;
                    return n + ' permission' + (n === 1 ? '' : 's') + ' on this role';
                },
            };
        }
    </script>
</x-app-layout>
