@props([
    'name',
    'category' => null,
    'meta' => null,
    'href' => null,
])

@php
    $tone = \App\Support\ShakhaRiskTone::class;
    $text = $tone::textClasses($category);
    $tag = $href ? 'a' : 'span';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex min-w-0 max-w-full flex-wrap items-center gap-1.5']) }}>
    <{{ $tag }}
        @if ($href) href="{{ $href }}" @endif
        class="truncate font-semibold {{ $text }} {{ $href ? 'hover:underline' : '' }}"
    >{{ $name }}</{{ $tag }}>
    <x-shakha-risk-badge :category="$category" size="xs" />
    @if ($meta)
        <span class="truncate text-[10px] text-slate-400">{{ $meta }}</span>
    @endif
</span>
