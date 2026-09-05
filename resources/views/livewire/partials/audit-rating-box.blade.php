@php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
    $editable = $editable ?? false;
    $wireModel = $wireModel ?? null;
    $cellBg = $style['bg'] ?? '#FCE4D6';
    $cellColor = $style['color'] ?? '#111111';
@endphp
<table class="w-full border-collapse text-[10px]">
    <tr>
        <td colspan="2" class="border border-black bg-[#4472C4] p-[1.5mm] text-center text-[9px] font-bold text-white">
            রেটিং (Rating)
        </td>
    </tr>
    <tr>
        @if ($editable && $wireModel)
            <td colspan="2" class="border border-black p-[1.5mm] text-center font-bold" style="background: {{ $cellBg }}; color: {{ $cellColor }};">
                <select
                    wire:model.live="{{ $wireModel }}"
                    class="w-full border-0 bg-transparent text-center text-[10px] font-bold"
                    style="color: {{ $cellColor }};"
                >
                    <option value="">—</option>
                    @foreach ($findingRatings ?? [] as $option)
                        @if ($option !== '')
                            <option value="{{ $option }}" style="background:#ffffff;color:#111111;">{{ $option }}</option>
                        @endif
                    @endforeach
                </select>
            </td>
        @else
            <td class="w-1/2 border border-black p-[1.5mm] text-center font-bold" style="background: {{ $cellBg }}; color: {{ $cellColor }};">{{ $label }}</td>
            <td class="w-1/2 border border-black p-[1.5mm] text-center text-[11px] font-bold" style="background: {{ $cellBg }}; color: {{ $cellColor }};">{{ $code }}</td>
        @endif
    </tr>
</table>
