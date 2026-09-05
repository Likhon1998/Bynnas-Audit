<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Bynnas Audit') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @stack('styles')
    </head>
        <body
            class="font-sans text-[13px] font-normal leading-relaxed antialiased text-slate-700"
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: false,
                searchOpen: false,
                init() {
                    try {
                        this.sidebarCollapsed = localStorage.getItem('bynnasSidebarCollapsed') === '1';
                    } catch (e) {
                        this.sidebarCollapsed = false;
                    }
                },
                toggleSidebarCollapsed() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    try {
                        localStorage.setItem('bynnasSidebarCollapsed', this.sidebarCollapsed ? '1' : '0');
                    } catch (e) {}
                },
            }"
            @keydown.window.prevent.ctrl.k="searchOpen = true"
            @keydown.window.escape="searchOpen = false; sidebarOpen = false"
        >
        <x-app-loader />
        <div class="flex h-screen overflow-hidden bg-canvas">
            @include('layouts.sidebar')

            <div class="flex min-w-0 flex-1 flex-col">
                @include('layouts.topbar')

                <main class="flex min-h-0 flex-1 flex-col overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <div
            x-show="searchOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-start justify-center bg-slate-900/40 px-4 pt-24"
            @click.self="searchOpen = false"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl" @click.stop>
                <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                    <input type="text" placeholder="Search keyword..." class="w-full border-0 p-0 text-[13px] text-slate-700 placeholder:text-slate-400 focus:ring-0" autofocus>
                </div>
                <div class="p-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">Dashboard</a>
                    <a href="{{ route('organogram') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">Organogram</a>
                    <a href="{{ route('shakhas.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">All Shakha</a>
                    <a href="{{ route('areas.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">All Areas</a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">Settings</a>
                </div>
            </div>
        </div>

        @stack('scripts')
        @livewireScripts
    </body>
</html>
