@props(['alt' => 'Bynnas'])

<img
    src="{{ asset('images/bynnas-logo.png') }}?v=3"
    alt="{{ $alt }}"
    {{ $attributes->merge(['class' => 'h-9 w-9 object-contain']) }}
>
