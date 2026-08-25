@props(['name', 'title', 'accent' => '#4C6FFF'])

@php
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<div {{ $attributes->merge(['class' => 'flex min-w-[168px] max-w-[200px] items-center gap-2 rounded-lg border border-slate-100 bg-white px-2.5 py-1.5 text-left shadow-sm']) }}>
    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-medium text-white" style="background-color: {{ $accent }}">
        {{ $initials }}
    </div>
    <div class="min-w-0">
        <p class="truncate text-[12px] font-medium leading-tight text-slate-800">{{ $name }}</p>
        <p class="truncate text-[10px] leading-tight text-slate-400">{{ $title }}</p>
    </div>
</div>
