<x-app-layout>
    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Roles</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Create custom roles · pick permissions · assign on Users &amp; Access</p>
            </div>
            <a href="{{ route('roles.create') }}" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-medium text-white hover:bg-navy-800">
                + New role
            </a>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @error('role')
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-800">{{ $message }}</div>
        @enderror

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-left text-[12px]">
                <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Role</th>
                        <th class="px-3 py-2">Permissions</th>
                        <th class="px-3 py-2">Users</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($roles as $role)
                        @php
                            $meta = $catalog[$role->name] ?? null;
                            $isSystem = \App\Support\RoleAccess::isSystemRole($role->name);
                        @endphp
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-3 py-2.5">
                                <p class="font-semibold text-navy-900">{{ $meta['label'] ?? \App\Support\RoleAccess::label($role->name) }}</p>
                                <p class="text-[11px] text-slate-500">
                                    <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">{{ $role->name }}</code>
                                    @if ($isSystem)
                                        <span class="ml-1 text-[10px] font-semibold text-slate-400">Built-in</span>
                                    @else
                                        <span class="ml-1 text-[10px] font-semibold text-sky-600">Custom</span>
                                    @endif
                                </p>
                                @if (! empty($meta['notes']))
                                    <p class="mt-1 text-[10px] text-slate-400">{{ $meta['notes'] }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-600">
                                @if ($role->name === 'superadmin')
                                    All
                                @else
                                    {{ $role->permissions->count() }}
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-600">{{ $role->users_count }}</td>
                            <td class="px-3 py-2.5">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @if ($role->name === 'superadmin')
                                        <span class="text-[11px] text-slate-400">Locked</span>
                                    @else
                                        <a href="{{ route('roles.edit', $role) }}" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Edit</a>
                                    @endif
                                    @unless ($isSystem)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="text-[11px] font-semibold text-rose-600 hover:underline"
                                                onclick="return confirm('Delete this role? Users must be reassigned first.')"
                                            >Delete</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
