<x-app-layout>
    @php
        /** @var \App\Models\User|null $user */
        $editing = (bool) $user;
        $selectedRole = old('role', $user?->roleLabel() ?: 'audit_officer');
        $selectedShakhas = collect(old('shakha_ids', $user?->assignedShakhas?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
    @endphp

    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4">
            <a href="{{ route('users.index') }}" class="text-[11px] font-medium text-[#2b579a] hover:underline">← Back to users</a>
            <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">
                {{ $editing ? 'Edit user access' : 'Create user access' }}
            </h1>
            <p class="mt-0.5 text-[12px] text-slate-500">Login credentials + role + employee link + shakha access</p>
        </div>

        <form
            method="POST"
            action="{{ $editing ? route('users.update', $user) : route('users.store') }}"
            class="mx-auto max-w-3xl space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
        >
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user?->name) }}" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                    @error('name') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Email (login)</label>
                    <input type="email" name="email" value="{{ old('email', $user?->email) }}" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                    @error('email') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
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

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Role</label>
                    <select name="role" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected($selectedRole === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[10px] text-slate-400">
                        superadmin = full · audit_manager = ops · audit_officer = assigned shakhas only
                    </p>
                    @error('role') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Link employee</label>
                    <select name="employee_id" class="h-9 w-full rounded-lg border-slate-200 text-[13px]">
                        <option value="">— Not linked —</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected((int) old('employee_id', $user?->employee_id) === $employee->id)>
                                {{ $employee->name }}@if($employee->position) · {{ $employee->position->name }}@endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[10px] text-slate-400">Links visit assignments → accessible shakhas</p>
                    @error('employee_id') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1 flex items-center gap-2 text-[12px] text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-[#2b579a]" @checked(old('is_active', $user?->is_active ?? true))>
                    Account active
                </label>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Explicit shakha access</label>
                <p class="mb-2 text-[11px] text-slate-500">Required for officers (plus auto from monthly visit assignments). Managers/superadmin already see all.</p>
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
                        onclick="return confirm('Delete this user account?')"
                    >Delete user</button>
                @else
                    <span></span>
                @endif
                <button type="submit" class="inline-flex h-9 items-center rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072]">
                    {{ $editing ? 'Save changes' : 'Create user' }}
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
