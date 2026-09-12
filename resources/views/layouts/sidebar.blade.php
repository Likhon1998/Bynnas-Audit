<aside
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        sidebarCollapsed ? 'sidebar-collapsed sidebar-is-collapsed' : '',
    ]"
    class="sidebar-shell fixed inset-y-0 left-0 z-40 flex w-[188px] shrink-0 flex-col overflow-hidden text-white transition-[width,transform] duration-200 ease-out lg:static lg:translate-x-0"
>
    <div class="relative z-10 flex items-center gap-2 px-2.5 pb-1.5 pt-3.5" :class="sidebarCollapsed && 'lg:flex-col lg:gap-2 lg:px-1.5'">
        <a href="{{ route('dashboard') }}" class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg" title="Bynnas Audit">
            <img src="{{ asset('images/bynnas-logo.png') }}?v=3" alt="Bynnas" class="h-9 w-9 object-contain">
        </a>
        <div class="sidebar-brand-text min-w-0 flex-1" :class="sidebarCollapsed && 'lg:hidden'">
            <p class="truncate text-[13px] font-semibold leading-tight tracking-tight">
                <span class="text-white">Bynnas</span>
                <span class="text-sky-300"> Audit</span>
            </p>
            <p class="mt-0.5 truncate text-[9px] tracking-wide text-slate-400">Secure • Analyze</p>
        </div>
        <button
            type="button"
            class="sidebar-collapse-btn hidden h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-white/15 bg-white/[0.04] text-slate-300 transition hover:border-white/30 hover:bg-white/10 hover:text-white lg:flex"
            @click.stop.prevent="toggleSidebarCollapsed()"
            :title="sidebarCollapsed ? 'Show sidebar' : 'Hide sidebar'"
            :aria-label="sidebarCollapsed ? 'Show sidebar' : 'Hide sidebar'"
            :aria-expanded="(!sidebarCollapsed).toString()"
        >
            <svg
                class="h-4 w-4 transition-transform duration-200"
                :class="sidebarCollapsed && 'rotate-180'"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.4"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <div class="sidebar-scroll relative z-10 min-h-0 flex-1 overflow-y-auto px-2 pb-3 pt-3" :class="sidebarCollapsed && 'lg:px-1.5'">
        <div class="sidebar-section-label mb-1.5 flex items-center gap-1.5 px-1.5" :class="sidebarCollapsed && 'lg:hidden'">
            <span class="h-px w-2.5 rounded-full bg-sky-400/80"></span>
            <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-slate-400">Main</p>
        </div>
        <div class="sidebar-section-rule mb-1.5 hidden h-px bg-white/10 lg:mx-1" :class="sidebarCollapsed ? 'lg:block' : 'lg:hidden'"></div>
        <nav class="space-y-0.5">
            <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" title="Dashboard">
                <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-sky-100' : 'text-sky-400' }}" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 4.75A.75.75 0 014.75 4h6.5a.75.75 0 01.75.75v6.5a.75.75 0 01-.75.75h-6.5A.75.75 0 014 11.25v-6.5zM14 4.75a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75v3.5a.75.75 0 01-.75.75h-4.5a.75.75 0 01-.75-.75v-3.5zM4 14.75A.75.75 0 014.75 14h4.5a.75.75 0 01.75.75v4.5a.75.75 0 01-.75.75h-4.5a.75.75 0 01-.75-.75v-4.5z"/>
                    <path class="{{ request()->routeIs('dashboard') ? 'text-amber-300' : 'text-amber-400' }}" fill="currentColor" d="M12 13.75a.75.75 0 01.75-.75h6.5a.75.75 0 01.75.75v5.5a.75.75 0 01-.75.75h-6.5a.75.75 0 01-.75-.75v-5.5z"/>
                </svg>
                <span class="sidebar-link-label truncate">Dashboard</span>
            </x-sidebar-link>

            @if (config('features.map'))
                @can('map.view')
                    <x-sidebar-link :href="route('map.index')" :active="request()->routeIs('map.*')" title="Map">
                        <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('map.*') ? 'text-emerald-100' : 'text-emerald-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9.4 4.2l5.2-1.73a1 1 0 01.9.12l4.9 3.43a1 1 0 01.4.8v11.3a1 1 0 01-1.3.95l-4.9-1.63a1 1 0 00-.62 0l-5.2 1.73a1 1 0 01-.9-.12L3.98 15.6a1 1 0 01-.4-.8V3.5a1 1 0 011.3-.95l4.52 1.5a1 1 0 00.6.15z"/>
                            <path class="{{ request()->routeIs('map.*') ? 'text-amber-300' : 'text-amber-400' }}" fill="currentColor" d="M10 7.5v11l4-1.3V6.2l-4 1.3z"/>
                        </svg>
                        <span class="sidebar-link-label truncate">Map</span>
                    </x-sidebar-link>
                @endcan
            @endif

            @canany(['organogram.view', 'organogram.manage'])
                <x-sidebar-link :href="route('organogram')" :active="request()->routeIs('organogram')" title="Organogram">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('organogram') ? 'text-violet-100' : 'text-violet-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 3.5a3 3 0 110 6 3 3 0 010-6zM5.5 14a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0zM13.5 14a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0z"/>
                        <path class="{{ request()->routeIs('organogram') ? 'text-amber-300' : 'text-amber-400' }}" fill="currentColor" d="M8.2 19.5c.4-1.7 1.8-3 3.8-3s3.4 1.3 3.8 3H8.2zM3.8 20.2c.3-1.3 1.3-2.3 2.7-2.6-.2.5-.3 1-.3 1.6v1H3.8zM17.5 17.6c1.4.3 2.4 1.3 2.7 2.6H17.8c0-.6-.1-1.1-.3-1.6z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Organogram</span>
                </x-sidebar-link>
            @endcanany

            @can('annual_audit.manage')
                <x-sidebar-link :href="route('annual-audit.index')" :active="request()->routeIs('annual-audit.*')" title="Annual Audit Plan">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('annual-audit.*') ? 'text-indigo-100' : 'text-indigo-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 3a1 1 0 011 1v1h8V4a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 7.5v11A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-11A2.5 2.5 0 014.5 5H6V4a1 1 0 011-1zm12.5 6h-15v9.5a.5.5 0 00.5.5h14a.5.5 0 00.5-.5V9z"/>
                        <path class="{{ request()->routeIs('annual-audit.*') ? 'text-amber-300' : 'text-amber-400' }}" fill="currentColor" d="M7 12h10v1.5H7V12zm0 3.5h6V17H7v-1.5z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Annual Audit Plan</span>
                </x-sidebar-link>
            @endcan

            @canany(['monthly_visits.manage', 'monthly_visits.execute'])
                <x-sidebar-link :href="route('monthly-visits.index')" :active="request()->routeIs('monthly-visits.*')" title="Monthly Visits">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('monthly-visits.*') ? 'text-cyan-100' : 'text-cyan-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 3a1 1 0 011 1v1h8V4a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 7.5v11A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-11A2.5 2.5 0 014.5 5H6V4a1 1 0 011-1zm12.5 6h-15v9.5a.5.5 0 00.5.5h14a.5.5 0 00.5-.5V9z"/>
                        <path class="{{ request()->routeIs('monthly-visits.*') ? 'text-rose-200' : 'text-rose-400' }}" fill="currentColor" d="M7 12.5h2.5v2.5H7v-2.5zm4 0h2.5v2.5H11v-2.5zm4 0H17.5v2.5H15v-2.5z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Monthly Visits</span>
                </x-sidebar-link>
            @endcanany

            @canany(['calendar.manage', 'monthly_visits.manage', 'monthly_visits.execute'])
                <x-sidebar-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')" title="Working Calendar">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('calendar.*') ? 'text-amber-200' : 'text-teal-500' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 3a1 1 0 011 1v1h8V4a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 7.5v11A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-11A2.5 2.5 0 014.5 5H6V4a1 1 0 011-1zm12.5 6h-15v9.5a.5.5 0 00.5.5h14a.5.5 0 00.5-.5V9z"/>
                        <path class="{{ request()->routeIs('calendar.*') ? 'text-white' : 'text-amber-500' }}" fill="currentColor" d="M7 12.5h3v3H7v-3zm5.5 0h3v3h-3v-3z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Working Calendar</span>
                </x-sidebar-link>
            @endcanany

            @can('projects.manage')
                <x-sidebar-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" title="Projects">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('projects.*') ? 'text-amber-100' : 'text-amber-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3.75 7A1.75 1.75 0 015.5 5.25h4.1l1.4 1.5h7.5A1.75 1.75 0 0120.25 8.5v9.75A1.75 1.75 0 0118.5 20H5.5A1.75 1.75 0 013.75 18.25V7z"/>
                        <path class="{{ request()->routeIs('projects.*') ? 'text-teal-200' : 'text-teal-400' }}" fill="currentColor" d="M4.5 10.5h15V18a.75.75 0 01-.75.75H5.25A.75.75 0 014.5 18v-7.5z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Projects</span>
                </x-sidebar-link>
            @endcan

            @can('kpis.manage')
                <x-sidebar-link :href="route('kpis.index')" :active="request()->routeIs('kpis.*')" title="Key Performance Indicator (KPI)">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('kpis.*') ? 'text-fuchsia-100' : 'text-fuchsia-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5 3.75A1.75 1.75 0 016.75 2h10.5A1.75 1.75 0 0119 3.75v16.5A1.75 1.75 0 0117.25 22H6.75A1.75 1.75 0 015 20.25V3.75z"/>
                        <path class="{{ request()->routeIs('kpis.*') ? 'text-sky-200' : 'text-sky-400' }}" fill="currentColor" d="M8 7h1.5v1.5H8V7zm3.25 0H14v1.5h-2.75V7zM8 11h8v1.5H8V11zm0 3.5h5V16H8v-1.5z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Key Performance Indicator (KPI)</span>
                </x-sidebar-link>
            @endcan

            @canany(['audits.create', 'audits.manage'])
                <x-sidebar-link :href="route('audits.index')" :active="request()->routeIs('audits.*')" title="Audit Reports">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('audits.*') ? 'text-blue-100' : 'text-blue-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 2.75A1.75 1.75 0 018.75 1h5.69c.46 0 .9.18 1.23.51l4.82 4.82c.33.33.51.77.51 1.23v11.69A1.75 1.75 0 0119.25 21H8.75A1.75 1.75 0 017 19.25V2.75z"/>
                        <path class="{{ request()->routeIs('audits.*') ? 'text-emerald-200' : 'text-emerald-400' }}" fill="currentColor" d="M9.5 11h7v1.4h-7V11zm0 3.2h5v1.4h-5v-1.4z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Audit Reports</span>
                </x-sidebar-link>
            @endcanany

            @canany(['audits.review', 'audits.review_assign', 'audits.create', 'audits.manage'])
                @php
                    $reviewActionTotal = 0;
                    try {
                        $reviewActionTotal = (int) (app(\App\Services\AuditReportReviewService::class)->actionCounts(auth()->user())['total'] ?? 0);
                    } catch (\Throwable $e) {
                        $reviewActionTotal = 0;
                    }
                @endphp
                <x-sidebar-link :href="route('audit-review.index')" :active="request()->routeIs('audit-review.*')" title="Review Panel">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('audit-review.*') ? 'text-amber-100' : 'text-amber-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path fill="currentColor" d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Review Panel</span>
                    @if ($reviewActionTotal > 0)
                        <span class="sidebar-link-label ml-auto inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white">{{ $reviewActionTotal > 99 ? '99+' : $reviewActionTotal }}</span>
                    @endif
                </x-sidebar-link>
            @endcanany

            @canany(['audits.create', 'audits.manage'])
                <x-sidebar-link :href="route('checklists.index')" :active="request()->routeIs('checklists.*')" title="Checklists">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('checklists.*') ? 'text-lime-100' : 'text-lime-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.5 3.5A1.5 1.5 0 0110 2h4a1.5 1.5 0 011.5 1.5V5H18a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h2.5V3.5zM10 5h4V4h-4v1z"/>
                        <path class="{{ request()->routeIs('checklists.*') ? 'text-emerald-200' : 'text-emerald-500' }}" fill="currentColor" d="M10.2 12.1l1.4 1.4 3.4-3.5 1.1 1.1-4.5 4.6-2.5-2.5 1.1-1.1z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Checklists</span>
                </x-sidebar-link>
            @endcanany

            @can('findings.view_all')
                <x-sidebar-link
                    :href="route('audit-findings.index')"
                    :active="request()->routeIs('audit-findings.*') && ! request()->routeIs('audit-findings.entry*')"
                    title="Findings Matrix"
                >
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('audit-findings.*') && ! request()->routeIs('audit-findings.entry*') ? 'text-rose-100' : 'text-rose-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 5.5A1.5 1.5 0 015.5 4h13A1.5 1.5 0 0120 5.5v1A1.5 1.5 0 0118.5 8h-13A1.5 1.5 0 014 6.5v-1zM4 11.5A1.5 1.5 0 015.5 10H14a1.5 1.5 0 011.5 1.5v1A1.5 1.5 0 0114 14H5.5A1.5 1.5 0 014 12.5v-1z"/>
                        <path class="{{ request()->routeIs('audit-findings.*') && ! request()->routeIs('audit-findings.entry*') ? 'text-amber-300' : 'text-amber-400' }}" fill="currentColor" d="M4 17.5A1.5 1.5 0 015.5 16h13a1.5 1.5 0 011.5 1.5v1a1.5 1.5 0 01-1.5 1.5h-13A1.5 1.5 0 014 18.5v-1z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Findings Matrix</span>
                </x-sidebar-link>
            @elsecan('findings.enter')
                <x-sidebar-link
                    :href="route('audit-findings.entry')"
                    :active="request()->routeIs('audit-findings.entry*')"
                    title="Enter Findings"
                >
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('audit-findings.entry*') ? 'text-rose-100' : 'text-rose-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 5.5A1.5 1.5 0 015.5 4h13A1.5 1.5 0 0120 5.5v1A1.5 1.5 0 0118.5 8h-13A1.5 1.5 0 014 6.5v-1zM4 11.5A1.5 1.5 0 015.5 10H14a1.5 1.5 0 011.5 1.5v1A1.5 1.5 0 0114 14H5.5A1.5 1.5 0 014 12.5v-1z"/>
                        <path class="{{ request()->routeIs('audit-findings.entry*') ? 'text-amber-300' : 'text-amber-400' }}" fill="currentColor" d="M4 17.5A1.5 1.5 0 015.5 16h13a1.5 1.5 0 011.5 1.5v1a1.5 1.5 0 01-1.5 1.5h-13A1.5 1.5 0 014 18.5v-1z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Enter Findings</span>
                </x-sidebar-link>
            @endcan

            @canany(['shakhas.manage', 'shakhas.view_all', 'areas.manage'])
                <div x-data="{ shakhaOpen: {{ request()->routeIs('shakhas.*') || request()->routeIs('areas.*') || request()->routeIs('shakha-employees.*') ? 'true' : 'false' }} }">
                    <div :class="sidebarCollapsed && 'lg:hidden'">
                        <button
                            type="button"
                            @click="shakhaOpen = !shakhaOpen"
                            class="group relative flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-[12px] tracking-tight transition"
                            :class="shakhaOpen ? 'bg-white/[0.06] text-white' : 'text-slate-300 hover:bg-white/[0.04] hover:text-white'"
                        >
                            <svg class="h-3.5 w-3.5 shrink-0 {{ (request()->routeIs('shakhas.*') || request()->routeIs('areas.*') || request()->routeIs('shakha-employees.*')) ? 'text-orange-200' : 'text-orange-400' }}" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M4 10.25h16v9.5A1.25 1.25 0 0118.75 21H5.25A1.25 1.25 0 014 19.75v-9.5z"/>
                                <path class="{{ (request()->routeIs('shakhas.*') || request()->routeIs('areas.*') || request()->routeIs('shakha-employees.*')) ? 'text-sky-200' : 'text-sky-400' }}" fill="currentColor" d="M3.5 8.5L12 3.75 20.5 8.5H3.5zM8 13.5h1.5V17H8v-3.5zm3.25 0h1.5V17h-1.5v-3.5zm3.25 0H16V17h-1.5v-3.5z"/>
                            </svg>
                            <span class="sidebar-link-label min-w-0 flex-1 truncate text-left">Shakha</span>
                            <svg class="sidebar-chevron h-3 w-3 shrink-0 text-slate-500 transition" :class="shakhaOpen ? 'rotate-180 text-slate-300' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="shakhaOpen" x-cloak class="mt-0.5 space-y-0.5 border-l border-white/10 py-0.5 pl-2 ml-3">
                            @canany(['shakhas.manage', 'shakhas.view_all'])
                                <a href="{{ route('shakhas.index') }}" class="block rounded-md px-2 py-1 text-[11px] {{ request()->routeIs('shakhas.index') ? 'bg-blue-500/20 text-white' : 'text-slate-400 hover:bg-white/[0.04] hover:text-white' }}">All Shakha</a>
                                <a href="{{ route('shakha-employees.index') }}" class="block rounded-md px-2 py-1 text-[11px] {{ request()->routeIs('shakha-employees.*') ? 'bg-blue-500/20 text-white' : 'text-slate-400 hover:bg-white/[0.04] hover:text-white' }}">Shakha Employees</a>
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
                </div>
            @endcanany
        </nav>

        <div class="sidebar-section-label mb-1.5 mt-4 flex items-center gap-1.5 px-1.5" :class="sidebarCollapsed && 'lg:hidden'">
            <span class="h-px w-2.5 rounded-full bg-sky-400/80"></span>
            <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-slate-400">Settings</p>
        </div>
        <div class="sidebar-section-rule mb-1.5 mt-3 hidden h-px bg-white/10 lg:mx-1" :class="sidebarCollapsed ? 'lg:block' : 'lg:hidden'"></div>
        <nav class="space-y-0.5">
            @if(auth()->user()?->can('users.manage') || auth()->user()?->isSuperAdmin())
                <x-sidebar-link :href="route('users.index')" :active="request()->routeIs('users.*')" title="Users & Access">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('users.*') ? 'text-violet-100' : 'text-violet-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 4.5a3.5 3.5 0 110 7 3.5 3.5 0 010-7zM15.5 8a2.5 2.5 0 110 5 2.5 2.5 0 010-5z"/>
                        <path class="{{ request()->routeIs('users.*') ? 'text-cyan-200' : 'text-cyan-400' }}" fill="currentColor" d="M3.5 19.5c.6-2.8 2.9-4.7 5.5-4.7s4.9 1.9 5.5 4.7H3.5zM15 15.2c1.9.3 3.4 1.7 3.9 3.5h-3.2c-.1-.6-.3-1.1-.7-1.6-.4-.5-.9-.9-1.5-1.1.5-.3 1-.5 1.5-.8z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Users & Access</span>
                </x-sidebar-link>
                <x-sidebar-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" title="Roles">
                    <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('roles.*') ? 'text-amber-100' : 'text-amber-400' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14.5 3.5a5 5 0 014.1 7.8l-6.3 6.3H9v2.5H6.5V22H3.5v-3.2l6.9-6.9A5 5 0 0114.5 3.5z"/>
                        <path class="{{ request()->routeIs('roles.*') ? 'text-violet-200' : 'text-violet-400' }}" fill="currentColor" d="M16.2 6.2a1.6 1.6 0 11-2.26 2.26A1.6 1.6 0 0116.2 6.2z"/>
                    </svg>
                    <span class="sidebar-link-label truncate">Roles</span>
                </x-sidebar-link>
            @endif

            <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" title="Profile">
                <svg class="h-3.5 w-3.5 shrink-0 {{ request()->routeIs('profile.*') ? 'text-sky-100' : 'text-sky-400' }}" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 8.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7z"/>
                    <path class="{{ request()->routeIs('profile.*') ? 'text-amber-300' : 'text-amber-400' }}" fill="currentColor" d="M12 2.5c.6 0 1.1.1 1.6.4l.9-1.5 1.7 1-.4 1.7c.5.4.9.9 1.2 1.4l1.7-.3.9 1.7-1.4 1c.1.5.1 1.1 0 1.6l1.4 1-.9 1.7-1.7-.3c-.3.5-.7 1-1.2 1.4l.4 1.7-1.7 1-.9-1.5c-.5.3-1 .4-1.6.4s-1.1-.1-1.6-.4l-.9 1.5-1.7-1 .4-1.7c-.5-.4-.9-.9-1.2-1.4l-1.7.3-.9-1.7 1.4-1c-.1-.5-.1-1.1 0-1.6l-1.4-1 .9-1.7 1.7.3c.3-.5.7-1 1.2-1.4l-.4-1.7 1.7-1 .9 1.5c.5-.3 1-.4 1.6-.4zm0 4a5.5 5.5 0 110 11 5.5 5.5 0 010-11z"/>
                </svg>
                <span class="sidebar-link-label truncate">Profile</span>
            </x-sidebar-link>
        </nav>
    </div>

    <div class="relative z-10 border-t border-white/10 px-2 py-2" x-data="{ profileOpen: false }" :class="sidebarCollapsed && 'lg:px-1.5'">
        <button
            type="button"
            @click="profileOpen = !profileOpen"
            class="flex w-full items-center gap-2 rounded-lg px-1.5 py-1.5 text-left transition hover:bg-white/[0.04]"
            :class="sidebarCollapsed && 'lg:justify-center lg:px-0'"
            :title="sidebarCollapsed ? '{{ e(Auth::user()->name) }}' : ''"
        >
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-blue-500 text-[11px] font-semibold text-white shadow-md shadow-violet-500/30">
                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
            </span>
            <span class="sidebar-user-meta min-w-0 flex-1" :class="sidebarCollapsed && 'lg:hidden'">
                <span class="block truncate text-[11px] font-medium text-white">{{ Auth::user()->name }}</span>
                <span class="block truncate text-[10px] text-slate-400">{{ Auth::user()->roleLabel() }}</span>
            </span>
            <svg class="sidebar-chevron h-3 w-3 shrink-0 text-slate-500 transition" :class="[profileOpen ? 'rotate-180' : '', sidebarCollapsed && 'lg:hidden']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div
            x-show="profileOpen"
            x-cloak
            class="mt-1 space-y-0.5 rounded-lg border border-white/10 bg-[#0b1f3f]/90 p-1 backdrop-blur"
            :class="sidebarCollapsed && 'lg:absolute lg:bottom-full lg:left-full lg:mb-0 lg:ml-1 lg:w-36 lg:shadow-xl'"
        >
            <a href="{{ route('profile.edit') }}" class="block rounded-md px-2 py-1 text-[11px] text-slate-300 hover:bg-white/[0.05] hover:text-white">Profile</a>
            <button
                type="button"
                @click="toggleTheme()"
                class="block w-full rounded-md px-2 py-1 text-left text-[11px] text-slate-300 hover:bg-white/[0.05] hover:text-white"
                x-text="darkMode ? 'Light theme' : 'Dark theme'"
            ></button>
            <form
                method="POST"
                action="{{ route('logout') }}"
                @submit="
                    const meta = document.querySelector('meta[name=csrf-token]');
                    const input = $el.querySelector('input[name=_token]');
                    if (meta && input && meta.content) {
                        input.value = meta.content;
                    }
                "
            >
                @csrf
                <button type="submit" class="block w-full rounded-md px-2 py-1 text-left text-[11px] text-slate-300 hover:bg-white/[0.05] hover:text-white">Log Out</button>
            </form>
        </div>
    </div>
</aside>

<div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-slate-950/60 lg:hidden" @click="sidebarOpen = false"></div>
