@props([
    'editable' => false,
    'wire' => null,
    'inputClass' => null,
])

@php
    $inputClass = $inputClass ?: 'w-full min-w-[4rem] border-0 bg-transparent px-0.5 text-center text-[10px] font-semibold focus:bg-sky-50 focus:ring-1 focus:ring-sky-400';
@endphp

<th {{ $attributes }}>
    @if ($editable && $wire)
        <input type="text" wire:model.live="{{ $wire }}" class="{{ $inputClass }}">
    @else
        {{ $slot }}
    @endif
</th>
