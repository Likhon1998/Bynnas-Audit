@php
    /**
     * Finding serial cell — same rendering as page-4 ১.১ / ১.২ headlines.
     *
     * @var bool $editable
     * @var string|null $wireModel  e.g. page6Findings.0.serial
     * @var string $value
     */
    $editable = $editable ?? false;
    $wireModel = $wireModel ?? null;
    $value = $value ?? '';
@endphp
@if ($editable && filled($wireModel))
    <input
        type="text"
        wire:model.live="{{ $wireModel }}"
        class="finding-serial-input w-full border-0 bg-sky-50/40 text-center font-bold"
    >
@else
    @include('audits.partials.bn-num', ['value' => $value, 'variant' => 'serial'])
@endif
