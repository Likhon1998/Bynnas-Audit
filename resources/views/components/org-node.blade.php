@props(['name', 'title', 'accent' => '#4C6FFF'])

@php
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<div {{ $attributes->merge(['class' => 'flex min-w-[220px] items-center gap-2.5 rounded-xl border border-slate-100 bg-white px-3 py-2 text-left shadow-card']) }}>
    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white" style="background-color: {{ $accent }}">
        {{ $initials }}
    </div>
    <div class="min-w-0">
        <p class="truncate text-sm font-semibold text-slate-800">{{ $name }}</p>
        <p class="truncate text-[11px] text-slate-400">{{ $title }}</p>
    </div>
</div>
