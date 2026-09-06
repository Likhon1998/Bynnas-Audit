{{-- Shared branded dashboard metric cards (logo pink → purple → blue) --}}
@php
    $tones = [
        'magenta' => 'from-[#fff1f8] via-white to-[#f3e8ff] border-[#f0abd8]/50 hover:border-[#e879c0]/70',
        'fuchsia' => 'from-[#fdf4ff] via-white to-[#ede9fe] border-[#e9d5ff]/60 hover:border-[#d8b4fe]/80',
        'violet' => 'from-[#f5f3ff] via-white to-[#eef2ff] border-[#ddd6fe]/60 hover:border-[#c4b5fd]/80',
        'indigo' => 'from-[#eef2ff] via-white to-[#e0e7ff] border-[#c7d2fe]/60 hover:border-[#a5b4fc]/80',
        'blue' => 'from-[#eff6ff] via-white to-[#e0f2fe] border-[#bfdbfe]/60 hover:border-[#93c5fd]/80',
        'cyan' => 'from-[#ecfeff] via-white to-[#e0f2fe] border-[#a5f3fc]/50 hover:border-[#67e8f9]/70',
        'rose' => 'from-[#fff1f2] via-white to-[#fce7f3] border-[#fecdd3]/60 hover:border-[#fda4af]/80',
        'sky' => 'from-[#f0f9ff] via-white to-[#e0e7ff] border-[#bae6fd]/60 hover:border-[#7dd3fc]/80',
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
            class="group relative overflow-hidden rounded-xl border bg-gradient-to-br px-3.5 py-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $tone }}"
        >
            <span class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#ff2d9b] via-[#7c3aed] to-[#2563eb] opacity-90"></span>
            <span class="pointer-events-none absolute -right-6 -top-6 h-16 w-16 rounded-full bg-gradient-to-br from-[#ff2d9b]/10 via-[#7c3aed]/10 to-[#2563eb]/10 blur-md transition group-hover:from-[#ff2d9b]/20 group-hover:via-[#7c3aed]/15 group-hover:to-[#2563eb]/20"></span>
            <p class="relative text-[11px] font-semibold tracking-wide text-slate-600">{{ $card['label'] }}</p>
            <p class="relative mt-2 bg-gradient-to-r from-[#c026a0] via-[#6d28d9] to-[#1d4ed8] bg-clip-text text-[22px] font-semibold tabular-nums leading-none text-transparent">{{ $card['value'] }}</p>
            <p class="relative mt-2 text-[10px] text-slate-500">{{ $card['meta'] }}</p>
        </a>
    @endforeach
</div>
