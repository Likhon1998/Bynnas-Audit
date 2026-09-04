@php
    use App\Support\CustomTableSchema;
    $table = CustomTableSchema::normalize(is_array($block ?? []) ? $block : []);
    $columns = $table['columns'];
    $rows = $table['rows'];
    $leafCount = CustomTableSchema::leafCount($columns);
    $widths = CustomTableSchema::leafWidths($columns);
    $headerMatrix = CustomTableSchema::headerMatrix($columns);
    $paint = CustomTableSchema::bodyPaintPlan($table);
@endphp

@if (($table['title'] ?? '') !== '')
    <p class="bold" style="margin:3mm 0 1mm;">{{ $table['title'] }}</p>
@endif

<table class="doc-table" style="margin-bottom:3mm;width:100%;border-collapse:collapse;table-layout:fixed;">
    @if ($leafCount > 0)
        <colgroup>
            @foreach ($widths as $w)
                <col style="width: {{ $w }}%;">
            @endforeach
        </colgroup>
    @endif
    <thead>
        @foreach ($headerMatrix as $hRow)
            <tr>
                @foreach ($hRow as $hCell)
                    <th
                        class="bold center"
                        style="background:#d9d9d9;border:1px solid #333;padding:2px 3px;vertical-align:middle;"
                        colspan="{{ $hCell['colspan'] }}"
                        rowspan="{{ $hCell['rowspan'] }}"
                    >{{ $hCell['text'] }}</th>
                @endforeach
            </tr>
        @endforeach
    </thead>
    <tbody>
        @foreach ($rows as $rIndex => $row)
            @php $isTotal = (bool) ($row['is_total'] ?? false); @endphp
            <tr>
                @for ($c = 0; $c < $leafCount; $c++)
                    @php
                        $cell = $paint[$rIndex][$c] ?? null;
                        if (! $cell || ($cell['skip'] ?? false)) {
                            continue;
                        }
                        $rs = max(1, (int) ($cell['rowspan'] ?? 1));
                        $cs = max(1, (int) ($cell['colspan'] ?? 1));
                        $vAlign = $rs > 1 ? 'middle' : 'top';
                        $align = ($c === 2 && $cs === 1 && $rs === 1) ? 'left' : 'center';
                    @endphp
                    <td
                        class="{{ $isTotal ? 'bold' : '' }}"
                        style="border:1px solid #333;padding:2px 3px;text-align:{{ $align }};vertical-align:{{ $vAlign }};"
                        rowspan="{{ $rs }}"
                        colspan="{{ $cs }}"
                    >{{ $cell['text'] ?? '' }}</td>
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>
