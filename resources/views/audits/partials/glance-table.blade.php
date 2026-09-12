@php
    use App\Support\AuditDocumentLayout as Doc;
    $dash = $dash ?? '………………';
    $pairCount = 2;
    foreach (($glanceRows ?? []) as $row) {
        if (! is_array($row)) {
            continue;
        }
        if (isset($row['pairs']) && is_array($row['pairs']) && $row['pairs'] !== []) {
            $pairCount = max($pairCount, count($row['pairs']));
        }
    }
    $pairCount = max(1, min(4, $pairCount));
    $widths = Doc::glanceColumnWidths($pairCount);
@endphp

<table class="doc-table compact glance-table">
    <colgroup>
        @foreach ($widths as $w)
            <col style="width:{{ $w }}%;">
        @endforeach
    </colgroup>
    <tbody>
        @foreach ($glanceRows as $row)
            @php
                $pairs = array_values((array) ($row['pairs'] ?? []));
                if ($pairs === []) {
                    $pairs = [
                        ['label' => $row['left_label'] ?? '', 'value' => $row['left_value'] ?? ''],
                        ['label' => $row['right_label'] ?? '', 'value' => $row['right_value'] ?? ''],
                    ];
                }
                while (count($pairs) < $pairCount) {
                    $pairs[] = ['label' => '', 'value' => ''];
                }
                $pairs = array_slice($pairs, 0, $pairCount);
            @endphp
            <tr>
                @foreach ($pairs as $pair)
                    <td class="left-align">{{ ($pair['label'] ?? '') !== '' ? $pair['label'] : '—' }}</td>
                    <td class="center bold">
                        @if (($pair['value'] ?? '') !== '')
                            {!! \App\Support\BanglaNumerals::highlight($pair['value'], 'stat') !!}
                        @else
                            {{ $dash }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
