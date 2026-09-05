@php
    /**
     * Finding serial cell — same rendering as page-4 ১.১ / ১.২ headlines.
     *
     * @var bool $editable
     * @var string|null $wireModel  e.g. page6Findings.0.serial
     * @var string $value
     * @var string|null $inputStyle
     */
    $editable = $editable ?? false;
    $wireModel = $wireModel ?? null;
    $value = $value ?? '';
    $inputStyle = $inputStyle ?? null;
@endphp
@if ($editable && filled($wireModel))
    <input
        type="text"
        wire:model.live="{{ $wireModel }}"
        class="finding-serial-input w-full border-0 text-center font-bold {{ $inputStyle ? '' : 'bg-sky-50/40' }}"
        @if ($inputStyle) style="{{ $inputStyle }}" @endif
    >
@else
    @include('audits.partials.bn-num', ['value' => $value, 'variant' => 'serial'])
@endif
