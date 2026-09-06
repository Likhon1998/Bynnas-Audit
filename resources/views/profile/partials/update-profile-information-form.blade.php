<section class="flex h-full min-h-0 flex-col">
    <header class="mb-2 shrink-0">
        <div class="flex items-center gap-1.5">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-gradient-to-br from-[#ff2d9b]/15 via-[#7c3aed]/15 to-[#2563eb]/15 text-[#7c3aed]">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
            <div>
                <h2 class="text-[12px] font-semibold text-navy-900">{{ __('Profile Information') }}</h2>
                <p class="text-[9px] text-slate-500">{{ __('Update name and email.') }}</p>
            </div>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="flex min-h-0 flex-1 flex-col justify-between gap-2">
        @csrf
        @method('patch')

        <div class="space-y-2">
            <div>
                <x-input-label for="name" :value="__('Name')" class="text-[10px] font-semibold text-slate-600" />
                <x-text-input
                    id="name"
                    name="name"
                    type="text"
                    class="mt-0.5 block h-8 w-full rounded-lg border-slate-200 bg-white/90 px-2.5 text-[12px] shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                    :value="old('name', $user->name)"
                    required
                    autofocus
                    autocomplete="name"
                />
                <x-input-error class="mt-1 text-[10px]" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" class="text-[10px] font-semibold text-slate-600" />
                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    class="mt-0.5 block h-8 w-full rounded-lg border-slate-200 bg-white/90 px-2.5 text-[12px] shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                    :value="old('email', $user->email)"
                    required
                    autocomplete="username"
                />
                <x-input-error class="mt-1 text-[10px]" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <p class="mt-1 text-[10px] text-amber-800">
                        {{ __('Email unverified.') }}
                        <button form="send-verification" class="font-semibold text-[#7c3aed] underline">{{ __('Resend') }}</button>
                    </p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <button type="submit" class="inline-flex h-7 items-center rounded-lg bg-gradient-to-r from-[#c026d3] via-[#7c3aed] to-[#2563eb] px-3 text-[11px] font-semibold text-white shadow-sm hover:brightness-105">
                {{ __('Save') }}
            </button>
            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-[10px] font-medium text-emerald-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
