<x-app-layout>
    @php
        /** @var \App\Models\User|null $user */
        $editing = (bool) $user;
        $selectedRole = old('role', $user?->roleKey() ?: ($suggestedRole ?? 'audit_officer'));
        if (! in_array($selectedRole, $roles, true)) {
            $selectedRole = $suggestedRole ?? 'audit_officer';
        }
        $selectedPermissions = collect(old('permissions', $selectedPermissions ?? []))
            ->map(fn ($p) => (string) $p)
            ->unique()
            ->values()
            ->all();
        $selectedShakhas = collect(old('shakha_ids', $user?->assignedShakhas?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
        $defaultEmployeeId = (int) old('employee_id', $user?->employee_id ?: ($prefillEmployeeId ?? 0));
        $createEmployee = (bool) old('create_employee', false);
        $prefillEmployee = $employees->firstWhere('id', $defaultEmployeeId);
        $rolePermissionMap = $rolePermissionMap ?? [];
    @endphp

    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4">
            <a href="{{ route('users.index') }}" class="text-[11px] font-medium text-[#2b579a] hover:underline">← Back to users</a>
            <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">
                {{ $editing ? 'Edit access' : 'Grant access' }}
            </h1>
            <p class="mt-0.5 text-[12px] text-slate-500">
                Choose the person → select what they can do → optionally add extra branches
            </p>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif

        <form
            method="POST"
            action="{{ $editing ? route('users.update', $user) : route('users.store') }}"
            class="mx-auto max-w-4xl space-y-4"
            x-data="grantAccess({
                createEmployee: {{ $createEmployee && ! $editing ? 'true' : 'false' }},
                role: @js($selectedRole),
                permissions: @js($selectedPermissions),
                rolePermissionMap: @js($rolePermissionMap),
                roleMenus: @js(collect($roleCatalog)->mapWithKeys(fn ($r, $k) => [$k => $r['menus']])),
            })"
        >
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            {{-- 1. Who --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-2.5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-500">1 · Who</p>
                    <p class="text-[12px] font-semibold text-navy-900">Person &amp; login</p>
                </div>
                <div class="space-y-3 p-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Name</label>
                            <input type="text" name="name" value="{{ old('name', $user?->name ?? $prefillEmployee?->name) }}" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                            @error('name') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Email (login)</label>
                            <input type="email" name="email" value="{{ old('email', $user?->email ?? $prefillEmployee?->email) }}" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                            @error('email') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Mail send email</label>
                        <input type="email" name="mail_from_email" value="{{ old('mail_from_email', $user?->mail_from_email) }}" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" placeholder="Optional sender for audit emails">
                        @error('mail_from_email') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                Password {{ $editing ? '(leave blank to keep)' : '' }}
                            </label>
                            <input type="password" name="password" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" @unless($editing) required @endunless autocomplete="new-password">
                            @error('password') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Confirm password</label>
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
                                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Position</label>
                                <select name="position_id" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" :required="createEmployee">
                                    <option value="">— Select position —</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}" @selected((int) old('position_id') === $position->id)>
                                            {{ $position->serial }}. {{ $position->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endunless

                    <div x-show="!createEmployee" x-cloak>
                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Link organogram employee</label>
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
                        <p class="mt-1 text-[10px] text-slate-400">Required for monthly-visit allocations and field report branches.</p>
                        @error('employee_id') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-start gap-2 text-[12px] text-slate-700">
                        <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-slate-300 text-[#2b579a]" @checked(old('is_active', $user?->is_active ?? true))>
                        <span>
                            <span class="font-semibold">Account active</span>
                            <span class="mt-0.5 block text-[10px] text-slate-400">Uncheck to block login without deleting.</span>
                        </span>
                    </label>
                </div>
            </section>

            {{-- 2. Access --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-2.5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-500">2 · What they can do</p>
                    <p class="text-[12px] font-semibold text-navy-900">Role preset + selected access</p>
                </div>
                <div class="space-y-4 p-4">
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Access profile (preset)</label>
                        <select name="role" x-model="role" @change="applyPreset()" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ $roleCatalog[$role]['label'] ?? \App\Support\RoleAccess::label($role) }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-[11px] text-slate-500">
                            Changing the preset reloads the checklist below. Then tick/untick what this person may use.
                        </p>
                        @error('role') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-lg border border-sky-100 bg-sky-50/50 px-3 py-2 text-[11px] text-sky-950">
                        <p class="font-semibold">Menus this access unlocks</p>
                        <p class="mt-0.5 text-sky-800/80" x-text="selectedMenuLabel"></p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Select access</p>
                        <div class="flex gap-2 text-[11px]">
                            <button type="button" @click="applyPreset()" class="font-semibold text-[#2b579a] hover:underline">Reset to preset</button>
                            <button type="button" @click="selectNone()" class="font-semibold text-slate-500 hover:underline">Clear all</button>
                        </div>
                    </div>

                    <div class="grid gap-3 lg:grid-cols-2">
                        @foreach ($permissionGroups as $groupKey => $group)
                            <div class="rounded-lg border border-slate-200 p-3">
                                <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $group['label'] }}</p>
                                <div class="space-y-1.5">
                                    @foreach ($group['permissions'] as $permKey => $permLabel)
                                        <label class="flex cursor-pointer items-start gap-2 rounded-md px-1.5 py-1 hover:bg-slate-50">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permKey }}"
                                                class="mt-0.5 rounded border-slate-300 text-[#2b579a]"
                                                :disabled="role === 'superadmin'"
                                                x-model="permissions"
                                            >
                                            <span class="text-[12px] text-navy-900">{{ $permLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    @error('permissions.*') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror

                    <p class="text-[10px] text-slate-400" x-show="isCustomized" x-cloak>
                        Customized from the preset — saved as a personal access package for this user only.
                    </p>
                </div>
            </section>

            {{-- 3. Where --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-2.5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-500">3 · Where (optional)</p>
                    <p class="text-[12px] font-semibold text-navy-900">Extra shakha access</p>
                </div>
                <div class="p-4">
                    <p class="mb-2 text-[11px] text-slate-500">
                        Field staff already get branches from <strong>Monthly Visits</strong> allocations.
                        Use this only to grant <strong>extra</strong> shakhas beyond those visits.
                        “All shakhas” permission above still opens every branch.
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
                                    <span class="text-[10px] text-slate-400">
                                        {{ $shakha->code }}
                                        @if ($shakha->area)
                                            · {{ $shakha->area->division }} · {{ $shakha->area->name }}
                                        @endif
                                    </span>
                                    <x-shakha-risk-badge class="ml-1" :category="$shakha->riskCategory()" size="xs" />
                                </span>
                            </label>
                        @empty
                            <p class="px-2 py-4 text-center text-[12px] text-slate-400">No shakhas yet.</p>
                        @endforelse
                    </div>
                    @error('shakha_ids') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                @if ($editing && $user->id !== auth()->id())
                    <button
                        type="submit"
                        form="delete-user"
                        class="text-[12px] font-medium text-rose-600 hover:underline"
                        onclick="return confirm('Permanently delete this login? The organogram employee (if linked) will be kept.')"
                    >Delete login</button>
                @else
                    <span class="text-[11px] text-slate-400">Access is applied immediately after save.</span>
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
                permissions: (cfg.permissions || []).slice(),
                rolePermissionMap: cfg.rolePermissionMap || {},
                roleMenus: cfg.roleMenus || {},
                shakhaQuery: '',
                applyPreset() {
                    if (this.role === 'superadmin') {
                        this.permissions = Object.values(this.rolePermissionMap['superadmin'] || []).length
                            ? (this.rolePermissionMap['superadmin'] || []).slice()
                            : this.permissions;
                        // Superadmin is full access via role — keep all known keys checked for UI.
                        const all = [];
                        Object.values(this.rolePermissionMap).forEach((list) => {
                            (list || []).forEach((p) => { if (!all.includes(p)) all.push(p); });
                        });
                        document.querySelectorAll('input[name="permissions[]"]').forEach((el) => {
                            if (!all.includes(el.value)) all.push(el.value);
                        });
                        this.permissions = all;
                        return;
                    }
                    this.permissions = (this.rolePermissionMap[this.role] || []).slice();
                },
                selectNone() {
                    if (this.role === 'superadmin') return;
                    this.permissions = [];
                },
                shakhaMatch(haystack) {
                    const q = (this.shakhaQuery || '').toLowerCase().trim();
                    if (!q) return true;
                    return q.split(/\s+/).every((t) => (haystack || '').includes(t));
                },
                get isCustomized() {
                    if (this.role === 'superadmin') return false;
                    const preset = (this.rolePermissionMap[this.role] || []).slice().sort();
                    const current = this.permissions.slice().sort();
                    if (preset.length !== current.length) return true;
                    return preset.some((p, i) => p !== current[i]);
                },
                get selectedMenuLabel() {
                    const menus = this.roleMenus[this.role] || [];
                    if (this.role === 'superadmin') return 'Full system access';
                    if (!this.isCustomized && menus.length) return menus.join(' · ');
                    const count = this.permissions.length;
                    return count ? (count + ' permission' + (count === 1 ? '' : 's') + ' selected') : 'No permissions selected';
                },
            };
        }
    </script>
</x-app-layout>
