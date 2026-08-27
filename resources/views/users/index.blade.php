<x-app-layout>
    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">User access</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Create login credentials · link employee · assign roles & shakhas</p>
            </div>
            <a href="{{ route('users.create') }}" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-medium text-white hover:bg-navy-800">
                + New user
            </a>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @error('user')
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-800">{{ $message }}</div>
        @enderror

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-left text-[12px]">
                <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
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
                                <p class="text-[11px] text-slate-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-800">{{ $user->roleLabel() }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-slate-600">
                                {{ $user->employee?->name ?? '—' }}
                                @if ($user->employee?->position)
                                    <span class="text-[10px] text-slate-400">· {{ $user->employee->position->name }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-600">
                                @if ($user->hasAnyRole(['superadmin', 'audit_manager']))
                                    <span class="text-[11px] text-slate-400">All (by role)</span>
                                @else
                                    {{ $user->assignedShakhas->count() }} explicit
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                @if ($user->is_active)
                                    <span class="text-[11px] font-semibold text-emerald-700">Active</span>
                                @else
                                    <span class="text-[11px] font-semibold text-rose-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                <a href="{{ route('users.edit', $user) }}" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-slate-400">No users yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
