<x-app-layout>
    <div class="px-4 py-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm8 1a1 1 0 011-1h6a1 1 0 011 1v3a1 1 0 01-1 1h-6a1 1 0 01-1-1v-3z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>
                    <p class="text-sm text-slate-400">Overview of Bynnas Audit</p>
                </div>
            </div>
            <a href="{{ route('organogram') }}" class="inline-flex items-center rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600">
                View Organogram
            </a>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-card">
                    <p class="text-sm text-slate-400">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $stat['change'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
