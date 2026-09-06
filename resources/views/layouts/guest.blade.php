<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Bynnas Audit</title>
        <link rel="icon" type="image/png" href="{{ asset('images/bynnas-logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-canvas font-sans antialiased text-slate-800">
        <x-app-loader />
        <a href="{{ url('/') }}" class="absolute left-6 top-6 z-10 flex items-center gap-2.5">
            <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-slate-950 shadow-sm ring-1 ring-slate-800">
                <x-application-logo class="h-10 w-10" />
            </span>
            <span class="text-lg font-semibold tracking-tight text-slate-800">Bynnas Audit</span>
        </a>

        <div class="flex min-h-screen items-center justify-center px-4 py-16">
            <div class="w-full max-w-[460px] rounded-2xl bg-white/90 p-8 shadow-[0_20px_60px_rgba(80,90,140,0.12)] backdrop-blur-md sm:p-10">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
