@props(['title', 'subtitle' => null, 'back' => null, 'backLabel' => 'Back'])

<div class="sticky top-0 z-30 -mx-3 -mt-3 bg-canvas/95 px-3 pb-2 pt-3 backdrop-blur lg:-mx-5 lg:px-5">
    <header class="flex flex-wrap items-center gap-x-3 gap-y-2 rounded-xl border border-slate-200 border-l-4 border-l-amber-500 bg-white px-3 py-2 shadow-sm">
        @if ($back)
            <a
                href="{{ $back }}"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:-translate-y-0.5 hover:border-amber-500 hover:text-amber-600"
                title="{{ $backLabel }}"
                aria-label="{{ $backLabel }}"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.5 15 7.5 10l5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @else
            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-[0_6px_14px_rgba(234,88,12,0.35)]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 3h10v2h3a1 1 0 011 1v2a5 5 0 01-4.6 5A5 5 0 0113 15.9V18h3v3H8v-3h3v-2.1A5 5 0 017.6 13 5 5 0 013 8V6a1 1 0 011-1h3V3zm0 4H5v1a3 3 0 002 2.8V7zm10 0v3.8A3 3 0 0019 8V7h-2z"/></svg>
            </span>
        @endif
        <div class="min-w-0 flex-1">
            <h1 class="truncate text-[14px] font-semibold text-navy-900">{{ $title }}</h1>
            @if ($subtitle)
                <p class="truncate text-[11.5px] text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        {{ $slot }}
    </header>
</div>
