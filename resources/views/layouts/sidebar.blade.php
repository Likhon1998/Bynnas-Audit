<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="sidebar-shell fixed inset-y-0 left-0 z-40 flex w-[188px] shrink-0 flex-col overflow-hidden text-white transition-transform duration-200 lg:static lg:translate-x-0"
>
    <div class="relative z-10 flex items-center gap-2 px-3 pb-1.5 pt-3.5">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 shadow-md shadow-blue-500/30">
            <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-2.8 8.4-7 10-4.2-1.6-7-5.5-7-10V6l7-3z" />
            </svg>
        </span>
        <div class="min-w-0">
            <p class="truncate text-[13px] font-semibold leading-tight tracking-tight">
                <span class="text-white">Bynnas</span>
                <span class="text-sky-300"> Audit</span>
            </p>
            <p class="mt-0.5 truncate text-[9px] tracking-wide text-slate-400">Secure • Analyze</p>
        </div>
    </div>

    <div class="sidebar-scroll relative z-10 min-h-0 flex-1 overflow-y-auto px-2 pb-3 pt-3">
        <div class="mb-1.5 flex items-center gap-1.5 px-1.5">
            <span class="h-px w-2.5 rounded-full bg-sky-400/80"></span>
            <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-slate-400">Main</p>
        </div>
        <nav class="space-y-0.5">
            <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm8 1a1 1 0 011-1h6a1 1 0 011 1v3a1 1 0 01-1 1h-6a1 1 0 01-1-1v-3z" />
                </svg>
                Dashboard
            </x-sidebar-link>

            @can('users.manage')
                <x-sidebar-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Users & Access
                </x-sidebar-link>
            @endcan

            @canany(['organogram.view', 'organogram.manage'])
                <x-sidebar-link :href="route('organogram')" :active="request()->routeIs('organogram')">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('organogram') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a3 3 0 00-3-3H7a3 3 0 00-3 3v2m16-11a3 3 0 11-6 0 3 3 0 016 0zM9 9a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Organogram
                </x-sidebar-link>
            @endcanany

            @can('annual_audit.manage')
                <x-sidebar-link :href="route('annual-audit.index')" :active="request()->routeIs('annual-audit.*')">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('annual-audit.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                    </svg>
                    Annual Audit
                </x-sidebar-link>
            @endcan

            @canany(['monthly_visits.manage', 'monthly_visits.execute'])
                <x-sidebar-link :href="route('monthly-visits.index')" :active="request()->routeIs('monthly-visits.*')">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('monthly-visits.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 15h8M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                    </svg>
                    Monthly Visits
                </x-sidebar-link>
            @endcanany

            @can('projects.manage')
                <x-sidebar-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('projects.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    Projects
                </x-sidebar-link>
            @endcan

            @can('kpis.manage')
                <x-sidebar-link :href="route('kpis.index')" :active="request()->routeIs('kpis.*')">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('kpis.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6M7 7h.01M12 7h.01M17 7h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>
                    KPI
                </x-sidebar-link>
            @endcan

            @canany(['audits.create', 'audits.manage'])
                <x-sidebar-link :href="route('audits.index')" :active="request()->routeIs('audits.*')">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('audits.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Audit Reports
                </x-sidebar-link>
            @endcanany

            @canany(['findings.view_all', 'findings.enter'])
                <x-sidebar-link :href="route('audit-findings.index')" :active="request()->routeIs('audit-findings.*')">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('audit-findings.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h10M4 14h16M4 18h10" />
                    </svg>
                    Findings Matrix
                </x-sidebar-link>
            @endcanany

            @canany(['shakhas.manage', 'shakhas.view_all', 'areas.manage'])
                <div x-data="{ shakhaOpen: {{ request()->routeIs('shakhas.*') || request()->routeIs('areas.*') ? 'true' : 'false' }} }">
                    <button
                        type="button"
                        @click="shakhaOpen = !shakhaOpen"
                        class="group relative flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-[12px] tracking-tight transition"
                        :class="shakhaOpen ? 'bg-white/[0.06] text-white' : 'text-slate-300 hover:bg-white/[0.04] hover:text-white'"
                    >
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400 group-hover:text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                        <span class="min-w-0 flex-1 truncate text-left">Shakha</span>
                        <svg class="h-3 w-3 shrink-0 text-slate-500 transition" :class="shakhaOpen ? 'rotate-180 text-slate-300' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="shakhaOpen" x-cloak class="mt-0.5 space-y-0.5 border-l border-white/10 py-0.5 pl-2 ml-3">
                        @canany(['shakhas.manage', 'shakhas.view_all'])
                            <a href="{{ route('shakhas.index') }}" class="block rounded-md px-2 py-1 text-[11px] {{ request()->routeIs('shakhas.index') ? 'bg-blue-500/20 text-white' : 'text-slate-400 hover:bg-white/[0.04] hover:text-white' }}">All Shakha</a>
                        @endcanany
                        @can('shakhas.manage')
                            <a href="{{ route('shakhas.create') }}" class="block rounded-md px-2 py-1 text-[11px] {{ request()->routeIs('shakhas.create') ? 'bg-blue-500/20 text-white' : 'text-slate-400 hover:bg-white/[0.04] hover:text-white' }}">Add Shakha</a>
                        @endcan
                        @can('areas.manage')
                            <a href="{{ route('areas.index') }}" class="block rounded-md px-2 py-1 text-[11px] {{ request()->routeIs('areas.index') ? 'bg-blue-500/20 text-white' : 'text-slate-400 hover:bg-white/[0.04] hover:text-white' }}">All Areas</a>
                            <a href="{{ route('areas.create') }}" class="block rounded-md px-2 py-1 text-[11px] {{ request()->routeIs('areas.create') ? 'bg-blue-500/20 text-white' : 'text-slate-400 hover:bg-white/[0.04] hover:text-white' }}">Add Area</a>
                        @endcan
                    </div>
                </div>
            @endcanany
        </nav>

        <div class="mb-1.5 mt-4 flex items-center gap-1.5 px-1.5">
            <span class="h-px w-2.5 rounded-full bg-sky-400/80"></span>
            <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-slate-400">Others</p>
        </div>
        <nav class="space-y-0.5">
            <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('profile.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </x-sidebar-link>
        </nav>
    </div>

    <div class="relative z-10 border-t border-white/10 px-2 py-2" x-data="{ profileOpen: false }">
        <button type="button" @click="profileOpen = !profileOpen" class="flex w-full items-center gap-2 rounded-lg px-1.5 py-1.5 text-left transition hover:bg-white/[0.04]">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-blue-500 text-[11px] font-semibold text-white shadow-md shadow-violet-500/30">
                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-[11px] font-medium text-white">{{ Auth::user()->name }}</span>
                <span class="block truncate text-[10px] text-slate-400">{{ Auth::user()->roleLabel() }}</span>
            </span>
            <svg class="h-3 w-3 shrink-0 text-slate-500 transition" :class="profileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="profileOpen" x-cloak class="mt-1 space-y-0.5 rounded-lg border border-white/10 bg-[#0b1f3f]/90 p-1 backdrop-blur">
            <a href="{{ route('profile.edit') }}" class="block rounded-md px-2 py-1 text-[11px] text-slate-300 hover:bg-white/[0.05] hover:text-white">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full rounded-md px-2 py-1 text-left text-[11px] text-slate-300 hover:bg-white/[0.05] hover:text-white">Log Out</button>
            </form>
        </div>
    </div>
</aside>

<div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-slate-950/60 lg:hidden" @click="sidebarOpen = false"></div>
