{{-- Shared professional dashboard metric cards with distinct shaded tones. --}}
@php
    $tones = [
        'magenta' => ['surface' => 'from-pink-50 via-white to-fuchsia-50 border-pink-200/80 hover:border-pink-300', 'accent' => 'from-pink-500 to-fuchsia-500', 'value' => 'text-pink-700', 'icon' => 'bg-pink-100 text-pink-600'],
        'fuchsia' => ['surface' => 'from-fuchsia-50 via-white to-purple-50 border-fuchsia-200/80 hover:border-fuchsia-300', 'accent' => 'from-fuchsia-500 to-purple-500', 'value' => 'text-fuchsia-700', 'icon' => 'bg-fuchsia-100 text-fuchsia-600'],
        'violet' => ['surface' => 'from-violet-50 via-white to-purple-50 border-violet-200/80 hover:border-violet-300', 'accent' => 'from-violet-500 to-purple-500', 'value' => 'text-violet-700', 'icon' => 'bg-violet-100 text-violet-600'],
        'indigo' => ['surface' => 'from-indigo-50 via-white to-blue-50 border-indigo-200/80 hover:border-indigo-300', 'accent' => 'from-indigo-500 to-blue-500', 'value' => 'text-indigo-700', 'icon' => 'bg-indigo-100 text-indigo-600'],
        'blue' => ['surface' => 'from-blue-50 via-white to-sky-50 border-blue-200/80 hover:border-blue-300', 'accent' => 'from-blue-500 to-sky-500', 'value' => 'text-blue-700', 'icon' => 'bg-blue-100 text-blue-600'],
        'sky' => ['surface' => 'from-sky-50 via-white to-cyan-50 border-sky-200/80 hover:border-sky-300', 'accent' => 'from-sky-500 to-cyan-500', 'value' => 'text-sky-700', 'icon' => 'bg-sky-100 text-sky-600'],
        'cyan' => ['surface' => 'from-cyan-50 via-white to-teal-50 border-cyan-200/80 hover:border-cyan-300', 'accent' => 'from-cyan-500 to-teal-500', 'value' => 'text-cyan-700', 'icon' => 'bg-cyan-100 text-cyan-600'],
        'teal' => ['surface' => 'from-teal-50 via-white to-emerald-50 border-teal-200/80 hover:border-teal-300', 'accent' => 'from-teal-500 to-emerald-500', 'value' => 'text-teal-700', 'icon' => 'bg-teal-100 text-teal-600'],
        'emerald' => ['surface' => 'from-emerald-50 via-white to-green-50 border-emerald-200/80 hover:border-emerald-300', 'accent' => 'from-emerald-500 to-green-500', 'value' => 'text-emerald-700', 'icon' => 'bg-emerald-100 text-emerald-600'],
        'amber' => ['surface' => 'from-amber-50 via-white to-yellow-50 border-amber-200/80 hover:border-amber-300', 'accent' => 'from-amber-500 to-yellow-500', 'value' => 'text-amber-700', 'icon' => 'bg-amber-100 text-amber-600'],
        'orange' => ['surface' => 'from-orange-50 via-white to-amber-50 border-orange-200/80 hover:border-orange-300', 'accent' => 'from-orange-500 to-amber-500', 'value' => 'text-orange-700', 'icon' => 'bg-orange-100 text-orange-600'],
        'rose' => ['surface' => 'from-rose-50 via-white to-red-50 border-rose-200/80 hover:border-rose-300', 'accent' => 'from-rose-500 to-red-500', 'value' => 'text-rose-700', 'icon' => 'bg-rose-100 text-rose-600'],
    ];
    $columns = (int) ($columns ?? 6);
    $lgCols = match ($columns) {
        4 => 'lg:grid-cols-4',
        5 => 'lg:grid-cols-5',
        default => 'lg:grid-cols-6',
    };
@endphp

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 {{ $lgCols }}">
    @foreach ($cards as $card)
        @php
            $tone = $tones[$card['tone'] ?? 'violet'] ?? $tones['violet'];
        @endphp
        <a
            href="{{ $card['href'] }}"
            class="group relative min-h-[118px] overflow-hidden rounded-2xl border bg-gradient-to-br px-4 py-3.5 shadow-[0_6px_20px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_12px_28px_rgba(15,23,42,0.11)] {{ $tone['surface'] }}"
        >
            <span class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $tone['accent'] }}"></span>
            <span class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-white/70 blur-xl transition group-hover:scale-110"></span>
            <div class="relative flex items-start justify-between gap-2">
                <p class="pt-0.5 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-500">{{ $card['label'] }}</p>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm {{ $tone['icon'] }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l5-5 4 4 7-8"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 7h5v5"/></svg>
                </span>
            </div>
            <p class="relative mt-2 text-[24px] font-bold tabular-nums leading-none tracking-tight {{ $tone['value'] }}">{{ $card['value'] }}</p>
            <p class="relative mt-2 truncate text-[10px] font-medium text-slate-500">{{ $card['meta'] }}</p>
        </a>
    @endforeach
</div>
