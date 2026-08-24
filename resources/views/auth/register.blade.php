<x-guest-layout>
    <div class="text-center">
        <h1 class="text-[28px] font-bold leading-tight tracking-tight text-slate-900">Create your account</h1>
        <p class="mt-2 text-sm text-slate-500">Join Bynnas Audit and start your workspace</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Name" class="text-sm font-semibold text-slate-800" />
            <x-text-input id="name" class="mt-1.5 block w-full rounded-lg border-slate-200 px-3.5 py-2.5 text-sm" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Enter your name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" class="text-sm font-semibold text-slate-800" />
            <x-text-input id="email" class="mt-1.5 block w-full rounded-lg border-slate-200 px-3.5 py-2.5 text-sm" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Enter your email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Password" class="text-sm font-semibold text-slate-800" />
            <x-text-input id="password" class="mt-1.5 block w-full rounded-lg border-slate-200 px-3.5 py-2.5 text-sm" type="password" name="password" required autocomplete="new-password" placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm Password" class="text-sm font-semibold text-slate-800" />
            <x-text-input id="password_confirmation" class="mt-1.5 block w-full rounded-lg border-slate-200 px-3.5 py-2.5 text-sm" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2">
            Sign up
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-[#2563EB] hover:text-[#1D4ED8]">Login</a>
    </p>
</x-guest-layout>
