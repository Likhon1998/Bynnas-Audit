<section class="flex h-full min-h-0 flex-col">
    <header class="mb-2 shrink-0">
        <div class="flex items-center gap-1.5">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-gradient-to-br from-[#7c3aed]/15 to-[#2563eb]/15 text-[#2563eb]">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </span>
            <div>
                <h2 class="text-[12px] font-semibold text-navy-900">{{ __('Update Password') }}</h2>
                <p class="text-[9px] text-slate-500">{{ __('Keep your account secure.') }}</p>
            </div>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="flex min-h-0 flex-1 flex-col justify-between gap-2">
        @csrf
        @method('put')

        <div class="space-y-2">
            <div>
                <x-input-label for="update_password_current_password" :value="__('Current Password')" class="text-[10px] font-semibold text-slate-600" />
                <x-text-input
                    id="update_password_current_password"
                    name="current_password"
                    type="password"
                    class="mt-0.5 block h-8 w-full rounded-lg border-slate-200 bg-white/90 px-2.5 text-[12px] shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                    autocomplete="current-password"
                />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1 text-[10px]" />
            </div>

            <div>
                <x-input-label for="update_password_password" :value="__('New Password')" class="text-[10px] font-semibold text-slate-600" />
                <x-text-input
                    id="update_password_password"
                    name="password"
                    type="password"
                    class="mt-0.5 block h-8 w-full rounded-lg border-slate-200 bg-white/90 px-2.5 text-[12px] shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1 text-[10px]" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="text-[10px] font-semibold text-slate-600" />
                <x-text-input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="mt-0.5 block h-8 w-full rounded-lg border-slate-200 bg-white/90 px-2.5 text-[12px] shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1 text-[10px]" />
            </div>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <button type="submit" class="inline-flex h-7 items-center rounded-lg bg-gradient-to-r from-[#c026d3] via-[#7c3aed] to-[#2563eb] px-3 text-[11px] font-semibold text-white shadow-sm hover:brightness-105">
                {{ __('Save') }}
            </button>
            @if (session('status') === 'password-updated')
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
