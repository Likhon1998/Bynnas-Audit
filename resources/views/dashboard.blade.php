<x-app-layout>
    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">
                    Welcome back, {{ Auth::user()->name }}
                </h1>
                <p class="mt-0.5 text-[11px] text-slate-500">Bynnas Audit overview</p>
            </div>
            <a href="{{ route('organogram') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                View Organogram
            </a>
        </div>

        <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                @php
                    $tones = [
                        'violet' => 'border-l-violet-500 text-violet-600 bg-violet-50',
                        'emerald' => 'border-l-emerald-500 text-emerald-600 bg-emerald-50',
                        'sky' => 'border-l-sky-500 text-sky-600 bg-sky-50',
                        'orange' => 'border-l-orange-500 text-orange-600 bg-orange-50',
                    ];
                    $tone = $tones[$stat['tone']];
                    [$accent, $iconText, $iconBg] = explode(' ', $tone);
                @endphp
                <a
                    href="{{ $stat['href'] }}"
                    class="rounded-lg border border-slate-200 border-l-[3px] {{ $accent }} bg-white px-3 py-2.5 transition hover:border-slate-300 hover:bg-slate-50/80"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-slate-500">{{ $stat['label'] }}</p>
                            <p class="mt-0.5 text-[20px] font-semibold leading-none tracking-tight text-navy-900">{{ $stat['value'] }}</p>
                        </div>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md {{ $iconBg }} {{ $iconText }}">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                @if ($stat['tone'] === 'violet')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                @elseif ($stat['tone'] === 'emerald')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                @elseif ($stat['tone'] === 'sky')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a3 3 0 00-3-3H7a3 3 0 00-3 3v2m16-11a3 3 0 11-6 0 3 3 0 016 0zM9 9a3 3 0 11-6 0 3 3 0 016 0z" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                @endif
                            </svg>
                        </span>
                    </div>
                    <p class="mt-2 truncate text-[11px] text-slate-500">{{ $stat['meta'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
