@props(['name', 'title', 'accent' => '#4C6FFF', 'photoUrl' => null])

@php
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<div {{ $attributes->merge(['class' => 'flex min-w-[168px] max-w-[200px] items-center gap-2 rounded-lg border border-slate-100 bg-white px-2.5 py-1.5 text-left shadow-sm']) }}>
    @if ($photoUrl)
        <img
            src="{{ $photoUrl }}"
            alt="{{ $name }}"
            class="h-8 w-8 shrink-0 rounded-full object-cover ring-1 ring-slate-200"
        >
    @else
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-medium text-white" style="background-color: {{ $accent }}">
            {{ $initials }}
        </div>
    @endif
    <div class="min-w-0">
        <p class="truncate text-[12px] font-medium leading-tight text-slate-800">{{ $name }}</p>
        <p class="truncate text-xs leading-tight text-slate-500">{{ $title }}</p>
    </div>
</div>
