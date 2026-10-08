<x-app-layout>
    <div class="px-3 py-3 lg:px-5" x-data="{ guideOpen: false }">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-navy-900">Users &amp; Access</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Assign a role to each login · create roles &amp; permissions separately · optional extra branches</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="guideOpen = true"
                    class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-700 hover:border-sky-200 hover:bg-sky-50 hover:text-sky-900"
                >
                    <svg class="h-3.5 w-3.5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 7.5h.01"/></svg>
                    Access guide
                </button>
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
            <div data-flash class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @error('user')
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-800">{{ $message }}</div>
        @enderror

        {{-- Access guide: knowledge panel, opened from the header --}}
        <div x-show="guideOpen" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="guideOpen = false">
            <div
                x-show="guideOpen"
                x-transition.opacity.duration.200ms
                class="absolute inset-0 bg-slate-900/35 backdrop-blur-[2px]"
                @click="guideOpen = false"
            ></div>
            <aside
                x-show="guideOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-[0_0_40px_rgba(15,33,71,0.25)]"
                role="dialog"
                aria-label="Access guide"
            >
                <div class="flex items-start gap-3 border-b border-slate-100 bg-gradient-to-br from-sky-50 to-white px-4 py-3.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#1b3a70] to-[#2b579a] text-white shadow-[0_6px_14px_rgba(27,58,112,0.28)]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 7.5h.01"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-[15px] font-semibold tracking-tight text-navy-900">Access guide</h2>
                        <p class="mt-0.5 text-[12px] text-slate-500">How logins, roles and permissions fit together</p>
                    </div>
                    <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="guideOpen = false" aria-label="Close">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4">
                    <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-slate-400">How access works</p>
                    <ol class="mt-2 space-y-2.5">
                        @foreach (\App\Support\RoleAccess::accessModelGuide() as $i => $tip)
                            <li class="flex gap-2.5">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-sky-100 text-[11px] font-bold text-sky-800">{{ $i + 1 }}</span>
                                <div class="min-w-0">
                                    <p class="text-[12.5px] font-semibold text-navy-900">{{ preg_replace('/^\d+\s*·\s*/u', '', $tip['title']) }}</p>
                                    <p class="mt-0.5 text-[12.5px] leading-relaxed text-slate-600">{{ $tip['body'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <div class="mt-5 flex items-center justify-between">
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-slate-400">Roles · {{ count($roleCatalog) }}</p>
                        <a href="{{ route('roles.index') }}" class="text-[12px] font-semibold text-[#2b579a] hover:underline">Manage roles →</a>
                    </div>
                    <div class="mt-2 space-y-1.5" x-data="{ openRole: null }">
                        @foreach ($roleCatalog as $key => $role)
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white" :class="openRole === @js($key) ? 'shadow-[0_6px_18px_rgba(15,33,71,0.08)]' : ''">
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left hover:bg-slate-50" @click="openRole = openRole === @js($key) ? null : @js($key)">
                                    <div class="min-w-0 flex-1">
                                        <p class="flex items-center gap-1.5 text-[12.5px] font-semibold text-navy-900">
                                            {{ $role['label'] }}
                                            @unless (\App\Support\RoleAccess::isSystemRole($key))
                                                <span class="rounded-full bg-sky-50 px-1.5 py-px text-[10.5px] font-semibold text-sky-700">Custom</span>
                                            @endunless
                                        </p>
                                        <p class="truncate text-[11.5px] text-slate-500">{{ $role['summary'] }}</p>
                                    </div>
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform" :class="openRole === @js($key) ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </button>
                                <div x-show="openRole === @js($key)" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/60 px-3 py-2.5">
                                    @if (! empty($role['menus']))
                                        <p class="text-[11px] font-semibold text-slate-500">Opens</p>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach ($role['menus'] as $menu)
                                                <span class="rounded-md bg-white px-1.5 py-0.5 text-[11px] font-medium text-slate-600 ring-1 ring-slate-200">{{ $menu }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if (filled($role['notes'] ?? null))
                                        <p class="mt-2 text-[11.5px] leading-relaxed text-slate-500">{{ $role['notes'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
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
                        <th class="w-px whitespace-nowrap px-3 py-2 text-right">Actions</th>
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
                                <span class="whitespace-nowrap rounded-full bg-sky-50 px-2 py-0.5 text-xs font-semibold text-sky-800">{{ $user->roleLabel() }}</span>
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
                            <td class="whitespace-nowrap px-3 py-2.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('users.edit', $user) }}"
                                        class="inline-flex h-7 items-center gap-1 rounded-lg border border-[#2b579a]/20 bg-[#2b579a]/[0.06] px-2.5 text-[12px] font-semibold text-[#2b579a] transition-colors hover:bg-[#2b579a] hover:text-white"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                        Edit access
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form
                                            method="POST"
                                            action="{{ route('users.toggle-active', $user) }}"
                                            data-bynnas-confirm="{{ $user->is_active ? $user->name.' will not be able to sign in until reactivated.' : $user->name.' will be able to sign in again.' }}"
                                            data-bynnas-confirm-title="{{ $user->is_active ? 'Deactivate login?' : 'Reactivate login?' }}"
                                            data-bynnas-confirm-ok="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                            data-bynnas-confirm-tone="{{ $user->is_active ? 'amber' : 'emerald' }}"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            @if ($user->is_active)
                                                <button type="submit" class="inline-flex h-7 w-[6.75rem] items-center justify-center gap-1 rounded-lg border border-amber-200 bg-amber-50 text-[12px] font-semibold text-amber-800 transition-colors hover:bg-amber-500 hover:text-white">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>
                                                    Deactivate
                                                </button>
                                            @else
                                                <button type="submit" class="inline-flex h-7 w-[6.75rem] items-center justify-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 text-[12px] font-semibold text-emerald-800 transition-colors hover:bg-emerald-600 hover:text-white">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/></svg>
                                                    Activate
                                                </button>
                                            @endif
                                        </form>
                                        <form
                                            method="POST"
                                            action="{{ route('users.destroy', $user) }}"
                                            data-bynnas-confirm="{{ $user->name }}'s login will be deleted permanently. The organogram employee record (if linked) is kept."
                                            data-bynnas-confirm-title="Delete login?"
                                            data-bynnas-confirm-ok="Delete login"
                                            data-bynnas-confirm-tone="rose"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-rose-200 bg-white text-rose-600 transition-colors hover:border-rose-600 hover:bg-rose-600 hover:text-white" title="Delete login" aria-label="Delete login">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex h-7 w-[8.875rem] items-center justify-center rounded-lg border border-dashed border-slate-200 text-[11.5px] font-medium text-slate-400" title="You cannot deactivate or delete your own login">This is you</span>
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
