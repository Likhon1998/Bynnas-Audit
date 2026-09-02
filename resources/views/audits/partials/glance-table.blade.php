@php
    use App\Support\AuditDocumentLayout as Doc;
    $dash = $dash ?? '………………';
    $widths = Doc::glanceColumnWidths();
@endphp

<table class="doc-table compact glance-table">
    <colgroup>
        @foreach ($widths as $w)
            <col style="width:{{ $w }}%;">
        @endforeach
    </colgroup>
    <tbody>
        @foreach ($glanceRows as $row)
            <tr>
                <td class="left-align">{{ $row['left_label'] !== '' ? $row['left_label'] : '—' }}</td>
                <td class="center bold">
                    @if ($row['left_value'] !== '')
                        {!! \App\Support\BanglaNumerals::highlight($row['left_value'], 'stat') !!}
                    @else
                        {{ $dash }}
                    @endif
                </td>
                <td class="left-align">{{ $row['right_label'] !== '' ? $row['right_label'] : '—' }}</td>
                <td class="center bold">
                    @if ($row['right_value'] !== '')
                        {!! \App\Support\BanglaNumerals::highlight($row['right_value'], 'stat') !!}
                    @else
                        {{ $dash }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
