@php
    use App\Support\AuditDocumentLayout as Doc;
    $bnDigits = $bnDigits ?? ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $toBn = $toBn ?? function (int $n) use ($bnDigits) {
        return implode('', array_map(fn ($d) => $bnDigits[(int) $d], str_split((string) $n)));
    };
    $widths = Doc::staffColumnWidths($staffColumns);
@endphp

<table class="doc-table compact staff-table">
    <colgroup>
        @foreach ($widths as $w)
            <col style="width:{{ $w }}%;">
        @endforeach
    </colgroup>
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
                <td class="center">{!! $toBn($idx + 1) !!}</td>
                @foreach ($staffColumns as $cIdx => $col)
                    @php
                        $cell = trim((string) ($row['cells'][$cIdx] ?? ''));
                        $align = Doc::alignClass(Doc::staffColumnAlign($col));
                    @endphp
                    <td class="{{ $align }}">{!! $cell !== '' ? e($cell) : '&nbsp;' !!}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
