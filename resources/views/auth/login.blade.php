<x-guest-layout>
    <div class="text-center">
        <h1 class="text-[18px] font-semibold leading-snug tracking-tight text-slate-900">Welcome to your audit workspace</h1>
        <p class="mt-1 text-[12px] text-slate-500">Next-gen audit readiness starts here</p>
    </div>

    <x-auth-session-status class="mt-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-3.5" x-data="{ showPassword: false }">
        @csrf

        <div>
            <x-input-label for="email" value="Email" class="text-[12px] font-semibold text-slate-700" />
            <x-text-input
                id="email"
                class="mt-1 block w-full rounded-lg border-slate-200 px-3 py-2 text-[13px] placeholder:text-slate-400"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="Enter your email"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password" value="Password" class="text-[12px] font-semibold text-slate-700" />
            <div class="relative mt-1">
                <x-text-input
                    id="password"
                    class="block w-full rounded-lg border-slate-200 px-3 py-2 pr-10 text-[13px] placeholder:text-slate-400"
                    type="password"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />
                <button type="button" class="absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400 hover:text-slate-600" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                    <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0113.4 13.4M9.9 5.1A10.5 10.5 0 0112 5c4.48 0 8.27 2.94 9.54 7a10.9 10.9 0 01-2.36 3.9M6.1 6.1A10.9 10.9 0 002.46 12c1.27 4.06 5.06 7 9.54 7 1.4 0 2.73-.27 3.95-.76" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        @if (Route::has('password.request'))
            <div>
                <a class="text-[12px] text-slate-400 hover:text-blue-600" href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
            </div>
        @endif

        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-gradient-to-r from-[#c026d3] via-[#7c3aed] to-[#2563eb] px-4 py-2 text-[13px] font-semibold text-white shadow-sm transition hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-[#7c3aed] focus:ring-offset-2">
            Login
        </button>
    </form>

    <p class="mt-4 text-center text-[11px] leading-snug text-slate-500">
        Need an account? Ask your Super Admin to create a login under Users &amp; Access.
    </p>
</x-guest-layout>
