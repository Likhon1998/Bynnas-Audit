@php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
    $editable = $editable ?? false;
    $wireModel = $wireModel ?? null;
@endphp
<table class="w-full border-collapse text-[10px]">
    <tr>
        <td colspan="2" class="border border-black bg-[#4472C4] p-[1.5mm] text-center text-[9px] font-bold text-white">
            রেটিং (Rating)
        </td>
    </tr>
    <tr>
        @if ($editable && $wireModel)
            <td colspan="2" class="border border-black bg-[#F8CBAD] p-[1.5mm] text-center font-bold">
                <select wire:model.live="{{ $wireModel }}" class="w-full border-0 bg-transparent text-center text-[10px] font-bold">
                    <option value="">—</option>
                    @foreach ($findingRatings ?? [] as $option)
                        @if ($option !== '')
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endif
                    @endforeach
                </select>
            </td>
        @else
            <td class="w-1/2 border border-black bg-[#F8CBAD] p-[1.5mm] text-center font-bold">{{ $label }}</td>
            <td class="w-1/2 border border-black bg-[#F8CBAD] p-[1.5mm] text-center text-[11px] font-bold">{{ $code }}</td>
        @endif
    </tr>
</table>
