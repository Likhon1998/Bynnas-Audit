@php
    $bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $toBn = function (int $n) use ($bnDigits) {
        return implode('', array_map(fn ($d) => $bnDigits[(int) $d], str_split((string) $n)));
    };
    $dash = '………………';
@endphp

<div class="a4-sheet">
    <div class="a4-inner official-preview text-black">
        <div class="a4-body">
            <div class="mb-[4mm] flex h-[14mm] items-center">
                @if (! empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Logo" class="h-[14mm] w-[14mm] object-contain">
                @endif
            </div>

            <p class="text-[12.5px] font-bold leading-[1.45]">
                এক নজরে {{ $shakha_display_name ?: '………………' }} শাখার তথ্য ({{ $glance_as_of ?: '………………' }}):
            </p>

            <p class="mt-[2mm] text-[11.5px] leading-[1.45]">
                শাখা গঠনের তারিখ:
                <span class="dotted">
                    {{ $branch_opening_date ? \Carbon\Carbon::parse($branch_opening_date)->format('d/m/Y') : '……………………' }}
                </span>
                ইং
            </p>

            <table class="a4-table a4-table-compact mt-[2.5mm] text-[11px] leading-[1.35]">
                <tbody>
                    @foreach ($glanceRows as $row)
                        <tr>
                            <td class="w-[28%]">{{ $row['left_label'] !== '' ? $row['left_label'] : '—' }}</td>
                            <td class="w-[22%] text-center font-semibold">{{ $row['left_value'] !== '' ? $row['left_value'] : $dash }}</td>
                            <td class="w-[28%]">{{ $row['right_label'] !== '' ? $row['right_label'] : '—' }}</td>
                            <td class="w-[22%] text-center font-semibold">{{ $row['right_value'] !== '' ? $row['right_value'] : $dash }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="mt-[5mm] text-[11.5px] font-semibold leading-[1.45]">
                শাখার কর্মীর তথ্য :
                <span class="dotted font-normal">
                    {{ $staff_info_as_of ? \Carbon\Carbon::parse($staff_info_as_of)->format('d/m/Y') : '……………………' }}
                </span>
                ইং
            </p>

            <table class="a4-table a4-table-compact mt-[2mm] text-[10px] leading-[1.3]">
                <thead>
                    <tr>
                        <th>ক্রমিক নং</th>
                        @foreach ($staffColumns as $col)
                            <th>{{ $col !== '' ? $col : '—' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffRows as $idx => $row)
                        <tr>
                            <td class="text-center">{{ $toBn($idx + 1) }}</td>
                            @foreach ($staffColumns as $cIdx => $col)
                                <td class="text-center">{{ $row['cells'][$cIdx] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @include('livewire.partials.audit-toc-table-preview', [
                'rows' => $tocPage2Rows ?? [],
                'showTitle' => true,
                'compact' => true,
            ])
        </div>

        <p class="a4-page-num">2</p>
    </div>
</div>
