@php
    /** @var \App\Models\ShakhaEmployee|null $employee */
    $size = $size ?? 'md';
    $sizes = [
        'sm' => 'h-8 w-8 text-[10px]',
        'md' => 'h-10 w-10 text-[11px]',
        'lg' => 'h-16 w-16 text-[14px]',
    ];
    $class = $sizes[$size] ?? $sizes['md'];
    $url = $employee?->photoUrl();
    $initial = mb_strtoupper(mb_substr((string) ($employee?->name ?: '?'), 0, 1));
@endphp

@if ($url)
    <img
        src="{{ $url }}"
        alt="{{ $employee?->name }}"
        class="{{ $class }} shrink-0 rounded-full object-cover ring-1 ring-slate-200 {{ $classExtra ?? '' }}"
    >
@else
    <span class="{{ $class }} inline-flex shrink-0 items-center justify-center rounded-full bg-slate-100 font-semibold text-slate-500 ring-1 ring-slate-200 {{ $classExtra ?? '' }}">
        {{ $initial }}
    </span>
@endif
