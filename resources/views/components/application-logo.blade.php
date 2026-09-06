@props(['alt' => 'Bynnas'])

<img
    src="{{ asset('images/bynnas-logo.png') }}"
    alt="{{ $alt }}"
    {{ $attributes->merge(['class' => 'h-9 w-9 object-contain']) }}
>
