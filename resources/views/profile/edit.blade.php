<x-app-layout>
    <div class="px-4 py-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-slate-900">{{ __('Profile') }}</h1>
            <p class="text-sm text-slate-400">Manage your account settings</p>
        </div>

        <div class="mx-auto max-w-3xl space-y-6">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-card sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-card sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-card sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
