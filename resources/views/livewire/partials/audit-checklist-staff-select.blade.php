{{-- Shakha staff dropdown for মাঠকর্মী / মাঠকর্মকর্তা / FO name. --}}
@php
    $staff = collect($shakhaEmployees ?? []);
    $current = trim((string) ($value ?? ''));
    $known = $staff->contains(fn ($emp) => trim((string) $emp->name) === $current);
@endphp
<select wire:model.live="{{ $wireModel }}" class="h-8 w-full border-0 bg-transparent px-1 text-[13px] focus:ring-1 focus:ring-[#2b579a]">
    <option value="">শাখার কর্মী বাছুন</option>
    @if ($current !== '' && ! $known)
        <option value="{{ $current }}">{{ $current }}</option>
    @endif
    @foreach ($staff as $emp)
        <option value="{{ $emp->name }}">{{ $emp->name }}{{ $emp->designation ? ' — '.$emp->designation : '' }}</option>
    @endforeach
</select>
