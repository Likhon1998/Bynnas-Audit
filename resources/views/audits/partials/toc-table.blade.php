@php
    use App\Support\AuditDocumentLayout as Doc;
    use App\Livewire\MakeAuditReport;
    $rows = $rows ?? [];
    $showTitle = $showTitle ?? false;
    $titleMarginTop = $titleMarginTop ?? '5mm';
    $widths = Doc::tocColumnWidths();
@endphp

@if ($showTitle)
    <h3 @if($titleMarginTop === '0') style="margin-top:0;" @endif>সূচিপত্র</h3>
@endif

<table class="doc-table compact toc-table">
    <colgroup>
        @foreach ($widths as $w)
            <col style="width:{{ $w }}%;">
        @endforeach
    </colgroup>
    <thead>
        <tr>
            <th>ক্রমিক নং</th>
            <th class="left-align">নিরীক্ষায় প্রাপ্ত ঘটনা সমূহ</th>
            <th>টাকা</th>
            <th>রেটিং</th>
            <th>বর্তমান অবস্থা</th>
            <th>পৃষ্ঠা নাম্বার</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            @php
                $isSection = ($row['type'] ?? 'item') === 'section';
                $rating = $row['rating'] ?? '';
                $anchor = ! $isSection ? MakeAuditReport::findingAnchorId($row['serial'] ?? '') : '';
                $findingText = ($row['finding'] ?? '') !== '' ? $row['finding'] : '—';
                $pageNo = ($row['page_no'] ?? '') !== '' ? $row['page_no'] : '';
            @endphp
            @if ($isSection)
                <tr>
                    <td class="center bold section">{{ $row['serial'] !== '' ? $row['serial'] : '—' }}</td>
                    <td colspan="5" class="section left-align">{{ $row['finding'] !== '' ? $row['finding'] : '—' }}</td>
                </tr>
            @else
                <tr>
                    <td class="center bold">{{ $row['serial'] !== '' ? $row['serial'] : '—' }}</td>
                    <td class="align-top left-align">
                        @if ($anchor !== '')
                            <a href="#{{ $anchor }}" style="color:#111; text-decoration:underline;">{{ $findingText }}</a>
                        @else
                            {{ $findingText }}
                        @endif
                    </td>
                    <td class="right-align">{!! ($row['amount'] ?? '') !== '' ? e($row['amount']) : '&nbsp;' !!}</td>
                    <td class="rating-cell">
                        @include('audits.partials.toc-rating-cell-pdf', ['isSection' => false, 'rating' => $rating])
                    </td>
                    <td class="center">{!! ($row['status'] ?? '') !== '' ? e($row['status']) : '&nbsp;' !!}</td>
                    <td class="center">
                        @if ($anchor !== '' && $pageNo !== '')
                            <a href="#{{ $anchor }}" style="color:#111; text-decoration:underline;">{{ $pageNo }}</a>
                        @else
                            {!! $pageNo !== '' ? e($pageNo) : '&nbsp;' !!}
                        @endif
                    </td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="6" class="center">কোনো সূচিপত্র এন্ট্রি নেই</td>
            </tr>
        @endforelse
    </tbody>
</table>
