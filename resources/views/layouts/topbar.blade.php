<header class="flex items-center gap-3 border-b border-slate-100/80 bg-white/80 px-4 py-2.5 backdrop-blur lg:px-6">
    <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-50 lg:hidden" @click="sidebarOpen = true">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <button type="button" @click="openSearch()" class="flex min-w-0 flex-1 items-center gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50 px-3.5 py-2 text-left text-[13px] text-slate-400 transition hover:border-slate-300 hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200" aria-label="Open global search">
        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
        </svg>
        <span class="truncate">Search keyword...</span>
        <span class="search-shortcut ml-auto hidden items-center rounded-md border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-medium tracking-wide text-slate-400 sm:inline-flex">Ctrl + K</span>
    </button>

    <div class="flex items-center gap-1">
        <button
            type="button"
            @click="toggleTheme()"
            class="rounded-lg p-1.5 text-slate-500 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200"
            :title="darkMode ? 'Use light theme' : 'Use dark theme'"
            :aria-label="darkMode ? 'Use light theme' : 'Use dark theme'"
        >
            <svg x-show="!darkMode" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
            </svg>
            <svg x-show="darkMode" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2M3 12h2m14 0h2m-3.22-6.78-1.42 1.42M7.64 16.36l-1.42 1.42m0-12.56 1.42 1.42m8.72 9.72 1.42 1.42M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="ml-1 flex items-center rounded-full focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-500 text-[11px] font-medium text-white">
                        {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                    </span>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="px-3 py-2.5">
                    <p class="text-[13px] font-medium text-slate-800">{{ Auth::user()->name }}</p>
                    <p class="truncate text-[11px] text-slate-400">{{ Auth::user()->email }}</p>
                </div>
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
