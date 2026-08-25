@props(['href', 'active' => false])

@php
    $classes = $active
        ? 'sidebar-link-active text-white font-medium shadow-[0_6px_16px_rgba(37,99,235,0.3)]'
        : 'text-slate-300 hover:bg-white/[0.04] hover:text-white';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'group relative flex items-center gap-2 rounded-lg px-2 py-1.5 text-[12px] tracking-tight transition '.$classes]) }}>
    @if ($active)
        <span class="absolute left-0 top-1/2 h-4 w-[2px] -translate-y-1/2 rounded-r-full bg-sky-300"></span>
    @endif
    {{ $slot }}
</a>
