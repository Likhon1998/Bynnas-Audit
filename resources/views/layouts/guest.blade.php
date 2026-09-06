<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Bynnas Audit</title>
        <link rel="icon" type="image/png" href="{{ asset('images/bynnas-logo.png') }}?v=3">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-canvas font-sans antialiased text-slate-800 h-screen overflow-hidden">
        <x-app-loader />
        <a href="{{ url('/') }}" class="absolute left-5 top-4 z-10 flex items-center gap-2">
            <img
                src="{{ asset('images/bynnas-logo.png') }}?v=3"
                alt="Bynnas"
                class="h-9 w-9 object-contain"
            >
            <span class="text-[13px] font-semibold tracking-tight text-slate-800">Bynnas Audit</span>
        </a>

        <div class="flex h-screen items-center justify-center overflow-hidden px-4 py-6">
            <div class="w-full max-w-[400px] rounded-2xl bg-white/95 px-6 py-6 shadow-[0_16px_40px_rgba(80,90,140,0.12)] backdrop-blur-md sm:px-7 sm:py-7">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
