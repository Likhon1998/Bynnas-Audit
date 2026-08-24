@props(['href', 'active' => false])

@php
    $classes = $active
        ? 'bg-brand-50 text-brand-600 font-semibold'
        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition '.$classes]) }}>
    @if ($active)
        <span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-brand-500"></span>
    @endif
    {{ $slot }}
</a>
