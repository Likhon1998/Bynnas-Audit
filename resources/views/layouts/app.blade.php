<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="bynnas-timezone" content="{{ bd_zone() }}">
        <meta name="bynnas-today" content="{{ bd_today() }}">

        <title>{{ $title ?? config('app.name', 'Bynnas Audit') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/bynnas-logo.png') }}?v=3">
        <script>
            try {
                document.documentElement.classList.remove('dark');
                localStorage.removeItem('bynnasTheme');
            } catch (e) {}
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @stack('styles')
    </head>
        <body
            class="bynnas-dense font-sans text-sm font-normal leading-normal antialiased text-slate-700"
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: false,
                init() {
                    try {
                        this.sidebarCollapsed = localStorage.getItem('bynnasSidebarCollapsedV2') === '1';
                    } catch (e) {
                        this.sidebarCollapsed = false;
                    }
                },
                toggleSidebarCollapsed() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    try {
                        localStorage.setItem('bynnasSidebarCollapsedV2', this.sidebarCollapsed ? '1' : '0');
                    } catch (e) {}
                    this.$nextTick(() => {
                        window.dispatchEvent(new Event('resize'));
                    });
                },
            }"
            @keydown.window.escape="sidebarOpen = false"
        >
        <x-app-loader />
        <div class="flex h-screen overflow-hidden bg-canvas">
            @include('layouts.sidebar')

            <div class="relative flex min-w-0 flex-1 flex-col">
                <button
                    type="button"
                    class="absolute left-3 top-3 z-20 rounded-lg border border-slate-200 bg-white p-1.5 text-slate-500 shadow-sm hover:bg-slate-50 lg:hidden"
                    @click="sidebarOpen = true"
                    aria-label="Open menu"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <main class="flex min-h-0 flex-1 flex-col overflow-hidden pt-10 lg:pt-0">
                    <div class="flex h-full min-h-0 flex-1 flex-col overflow-y-auto">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <x-nice-confirm />

        @stack('scripts')
        @livewireScripts
    </body>
</html>
