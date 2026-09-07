<x-app-layout>
    @php
        /** @var \App\Models\User|null $user */
        $editing = (bool) $user;
        $selectedRole = old('role', $user?->roleKey() ?: ($suggestedRole ?? 'audit_officer'));
        $selectedShakhas = collect(old('shakha_ids', $user?->assignedShakhas?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
        $defaultEmployeeId = (int) old('employee_id', $user?->employee_id ?: ($prefillEmployeeId ?? 0));
        $createEmployee = (bool) old('create_employee', false);
        $prefillEmployee = $employees->firstWhere('id', $defaultEmployeeId);
    @endphp

    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4">
            <a href="{{ route('users.index') }}" class="text-[11px] font-medium text-[#2b579a] hover:underline">← Back to users</a>
            <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">
                {{ $editing ? 'Edit user access' : 'Create employee login' }}
            </h1>
            <p class="mt-0.5 text-[12px] text-slate-500">Credentials + role + link employee · monthly visits auto-grant branches · optional extra shakhas</p>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif

        <form
            method="POST"
            action="{{ $editing ? route('users.update', $user) : route('users.store') }}"
            class="mx-auto max-w-3xl space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            x-data="{
                createEmployee: {{ $createEmployee && ! $editing ? 'true' : 'false' }},
                role: '{{ $selectedRole }}',
                roleMenus: {{ Js::from(collect($roleCatalog)->mapWithKeys(fn ($r, $k) => [$k => $r['menus']])) }}
            }"
        >
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

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
                <input
                    type="email"
                    name="mail_from_email"
                    value="{{ old('mail_from_email', $user?->mail_from_email) }}"
                    class="h-9 w-full rounded-lg border-slate-200 text-[13px]"
                    placeholder="e.g. officer@gmail.com"
                >
                <p class="mt-1 text-[10px] text-slate-500">Used as the sender when this user emails a completed audit report. Leave blank to use login email.</p>
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

            <div>
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Role access</label>
                <select name="role" x-model="role" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}">{{ $roleCatalog[$role]['label'] ?? $role }}</option>
                    @endforeach
                </select>
                <div class="mt-2 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-[11px] text-slate-600">
                    <p class="font-medium text-navy-900" x-text="(roleMenus[role] || []).length ? 'Can access:' : ''"></p>
                    <ul class="mt-1 list-inside list-disc text-slate-500">
                        <template x-for="item in (roleMenus[role] || [])" :key="item">
                            <li x-text="item"></li>
                        </template>
                    </ul>
                    @foreach ($roleCatalog as $key => $info)
                        <p class="mt-1 text-[10px] text-slate-400" x-show="role === '{{ $key }}'">{{ $info['notes'] }}</p>
                    @endforeach
                </div>
                @error('role') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>

            @unless ($editing)
                <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-3">
                    <label class="flex items-center gap-2 text-[12px] font-medium text-slate-800">
                        <input type="checkbox" name="create_employee" value="1" class="rounded border-slate-300 text-[#2b579a]" x-model="createEmployee">
                        Also create organogram employee (name + email + position)
                    </label>
                    <p class="mt-1 text-[10px] text-slate-500">Use this when the person is not in the organogram yet. Otherwise link an existing employee below.</p>

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
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Link employee</label>
                <select name="employee_id" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" :disabled="createEmployee">
                    <option value="">— Not linked —</option>
                    @foreach ($employees as $employee)
                        @php
                            $taken = $employee->user && (! $editing || $employee->user->id !== $user?->id);
                        @endphp
                        <option
                            value="{{ $employee->id }}"
                            @selected($defaultEmployeeId === $employee->id)
                            @disabled($taken)
                        >
                            {{ $employee->name }}
                            @if ($employee->position) · {{ $employee->position->title }} @endif
                            @if ($taken) (already has login) @endif
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-[10px] text-slate-400">Links monthly visit assignments → accessible shakhas for officers</p>
                @error('employee_id') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 flex items-start gap-2 text-[12px] text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-slate-300 text-[#2b579a]" @checked(old('is_active', $user?->is_active ?? true))>
                    <span>
                        <span class="font-semibold">Account active</span>
                        <span class="mt-0.5 block text-[10px] text-slate-400">Uncheck to deactivate — blocks login without deleting the account.</span>
                    </span>
                </label>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Extra shakha access (optional)</label>
                <p class="mb-2 text-[11px] text-slate-500">
                    Leave empty for normal officers — branch access comes automatically from <strong>Monthly Visits</strong> when they are allocated as visitor each month.
                    Use this list only to grant <strong>extra</strong> branches beyond those visit allocations.
                    Managers / Super Admin already see all.
                </p>
                <div class="max-h-56 overflow-y-auto rounded-lg border border-slate-200 p-2">
                    @foreach ($shakhas as $shakha)
                        <label class="flex items-center gap-2 rounded px-1.5 py-1 text-[12px] hover:bg-slate-50">
                            <input
                                type="checkbox"
                                name="shakha_ids[]"
                                value="{{ $shakha->id }}"
                                class="rounded border-slate-300 text-[#2b579a]"
                                @checked(in_array((int) $shakha->id, $selectedShakhas, true))
                            >
                            <span class="font-medium text-navy-900">{{ $shakha->name }}</span>
                            <span class="text-[10px] text-slate-400">{{ $shakha->code }}</span>
                        </label>
                    @endforeach
                </div>
                @error('shakha_ids') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                @if ($editing && $user->id !== auth()->id())
                    <button
                        type="submit"
                        form="delete-user"
                        class="text-[12px] font-medium text-rose-600 hover:underline"
                        onclick="return confirm('Permanently delete this login? The organogram employee (if linked) will be kept.')"
                    >Delete login</button>
                @else
                    <span></span>
                @endif
                <button type="submit" class="inline-flex h-9 items-center rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072]">
                    {{ $editing ? 'Save changes' : 'Create login' }}
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
</x-app-layout>
