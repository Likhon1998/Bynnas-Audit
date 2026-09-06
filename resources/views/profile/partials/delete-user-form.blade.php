<section class="flex flex-wrap items-center justify-between gap-2">
    <div class="flex min-w-0 items-center gap-1.5">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-rose-100 text-rose-600">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
        </span>
        <div class="min-w-0">
            <h2 class="text-[12px] font-semibold text-navy-900">{{ __('Delete Account') }}</h2>
            <p class="truncate text-[9px] text-slate-500">{{ __('Permanently removes your login. Download anything you need first.') }}</p>
        </div>
    </div>

    <x-danger-button
        class="h-7 rounded-lg px-3 text-[10px] font-semibold uppercase tracking-wide"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-5">
            @csrf
            @method('delete')

            <h2 class="text-[14px] font-semibold text-navy-900">
                {{ __('Delete your account?') }}
            </h2>
            <p class="mt-1 text-[11px] text-slate-500">
                {{ __('This cannot be undone. Enter your password to confirm.') }}
            </p>

            <div class="mt-4">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block h-8 w-3/4 rounded-lg border-slate-200 text-[12px] focus:border-rose-400 focus:ring-rose-400"
                    placeholder="{{ __('Password') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1 text-[10px]" />
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <x-secondary-button class="h-7 rounded-lg px-3 text-[11px]" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-danger-button class="h-7 rounded-lg px-3 text-[11px]">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
