@php
    use App\Support\AuditDocumentLayout as Doc;
    use App\Support\BanglaNumerals;
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
                <td class="center">
                    @include('audits.partials.bn-num', ['value' => BanglaNumerals::fromInt($idx + 1), 'variant' => 'index'])
                </td>
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
