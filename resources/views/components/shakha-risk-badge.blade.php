@props([
    'category' => null,
    'showLabel' => true,
    'size' => 'sm', // sm|xs
])

@php
    $tone = \App\Support\ShakhaRiskTone::class;
    $label = $tone::shortLabel($category);
    $classes = $tone::badgeClasses($category);
    $pad = $size === 'xs'
        ? 'px-1.5 py-0.5 text-[9px]'
        : 'px-2 py-0.5 text-[10px]';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-semibold {$pad} {$classes}"]) }}>
    @if ($showLabel)
        {{ $label }}
    @else
        {{ $slot }}
    @endif
</span>
