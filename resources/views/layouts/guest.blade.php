<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Bynnas Audit</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-canvas font-sans antialiased text-slate-800">
        <a href="{{ url('/') }}" class="absolute left-6 top-6 z-10 flex items-center gap-2.5">
            <x-application-logo class="h-9 w-9" />
            <span class="text-lg font-semibold tracking-tight text-slate-800">Bynnas Audit</span>
        </a>

        <div class="flex min-h-screen items-center justify-center px-4 py-16">
            <div class="w-full max-w-[460px] rounded-2xl bg-white/90 p-8 shadow-[0_20px_60px_rgba(80,90,140,0.12)] backdrop-blur-md sm:p-10">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
