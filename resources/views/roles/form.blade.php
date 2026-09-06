<x-app-layout>
    @php
        $editing = (bool) $role;
        $defaultLabel = old('label', $editing ? \App\Support\RoleAccess::label($role->name) : '');
        $defaultName = old('name', $editing ? $role->name : '');
        $checked = collect(old('permissions', $selectedPermissions))->all();
    @endphp

    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4">
            <a href="{{ route('roles.index') }}" class="text-[11px] font-medium text-[#2b579a] hover:underline">← Back to roles</a>
            <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">
                {{ $editing ? 'Edit role' : 'Create role' }}
            </h1>
            <p class="mt-0.5 text-[12px] text-slate-500">
                @if ($isSystem)
                    Built-in role — you can change permissions, but not the name or key.
                @else
                    Name the role, then tick what this login can do.
                @endif
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ $editing ? route('roles.update', $role) : route('roles.store') }}"
            class="mx-auto max-w-3xl space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            x-data="{
                label: @js($defaultLabel),
                name: @js($defaultName),
                lockName: {{ $isSystem || $editing ? 'true' : 'false' }},
                slugify(value) {
                    return String(value || '')
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '_')
                        .replace(/^_|_$/g, '')
                        || 'custom_role';
                }
            }"
        >
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Display name</label>
                    <input
                        type="text"
                        name="label"
                        x-model="label"
                        @input="if (! lockName) name = slugify(label)"
                        class="h-9 w-full rounded-lg border-slate-200 text-[13px]"
                        required
                        maxlength="80"
                        placeholder="e.g. Area Manager"
                    >
                    @error('label') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Role key</label>
                    <input
                        type="text"
                        name="name"
                        x-model="name"
                        class="h-9 w-full rounded-lg border-slate-200 font-mono text-[12px] {{ $isSystem ? 'bg-slate-50 text-slate-500' : '' }}"
                        @readonly($isSystem)
                        required
                        maxlength="60"
                        pattern="[a-z][a-z0-9_]*"
                        placeholder="area_manager"
                    >
                    <p class="mt-1 text-[10px] text-slate-400">Lowercase key used internally (auto from name).</p>
                    @error('name') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-2">
                    <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Permissions</label>
                    @error('permissions') <p class="text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-3">
                    @foreach ($permissionGroups as $group)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <p class="mb-2 text-[12px] font-semibold text-navy-900">{{ $group['label'] }}</p>
                            <div class="grid gap-1.5 sm:grid-cols-2">
                                @foreach ($group['permissions'] as $key => $permLabel)
                                    <label class="flex items-start gap-2 rounded px-1 py-1 text-[12px] hover:bg-slate-50">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $key }}"
                                            class="mt-0.5 rounded border-slate-300 text-[#2b579a]"
                                            @checked(in_array($key, $checked, true))
                                        >
                                        <span>
                                            <span class="font-medium text-slate-800">{{ $permLabel }}</span>
                                            <span class="mt-0.5 block font-mono text-[10px] text-slate-400">{{ $key }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-100 pt-3">
                <button type="submit" class="inline-flex h-9 items-center rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072]">
                    {{ $editing ? 'Save role' : 'Create role' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
