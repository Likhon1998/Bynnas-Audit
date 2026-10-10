@php
    use App\Support\AuditDocumentLayout as Doc;
    use App\Livewire\MakeAuditReport;
    $rows = $rows ?? [];
    $showTitle = $showTitle ?? false;
    $titleMarginTop = $titleMarginTop ?? '5mm';
    $widths = Doc::tocColumnWidths();
    // Real page of each heading, measured from a first PDF render (AuditReportPdfService).
    $measuredPages = is_array($tocPageMap ?? null) ? $tocPageMap : [];
    $pageFor = function (string $anchor, $typed) use ($measuredPages) {
        if ($anchor !== '' && isset($measuredPages[$anchor])) {
            return \App\Support\BanglaNumerals::fromInt((int) $measuredPages[$anchor]);
        }

        return ($typed ?? '') !== '' ? (string) $typed : '';
    };
@endphp

@if ($showTitle)
    <h3 @if($titleMarginTop === '0') style="margin-top:0;" @endif>@include('audits.partials.pdf-anchor', ['id' => 'toc', 'label' => 'সূচিপত্র', 'level' => 0])সূচিপত্র</h3>
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
                $anchor = $isSection
                    ? MakeAuditReport::sectionAnchorId($row['serial'] ?? '')
                    : MakeAuditReport::findingAnchorId($row['serial'] ?? '');
                $findingText = ($row['finding'] ?? '') !== '' ? $row['finding'] : '—';
                $pageNo = $pageFor($anchor, $row['page_no'] ?? '');
                $linked = $anchor !== '' && isset($measuredPages[$anchor]);
            @endphp
            @if ($isSection)
                <tr>
                    <td class="center bold section">
                        @include('audits.partials.bn-num', ['value' => $row['serial'] !== '' ? $row['serial'] : '—', 'variant' => 'serial-section'])
                    </td>
                    @if ($linked)
                        <td colspan="4" class="section left-align"><a href="#{{ $anchor }}" style="color:#111;text-decoration:none;">{{ $findingText }}</a></td>
                        <td class="section toc-page">
                            <a href="#{{ $anchor }}" class="bn-page-link" style="color:#111;text-decoration:none;">{{ $pageNo }}</a>
                        </td>
                    @else
                        <td colspan="5" class="section left-align">{{ $findingText }}</td>
                    @endif
                </tr>
            @else
                <tr>
                    <td class="center bold">
                        @include('audits.partials.bn-num', ['value' => $row['serial'] !== '' ? $row['serial'] : '—', 'variant' => 'serial'])
                    </td>
                    <td class="align-top left-align">
                        @if ($anchor !== '')
                            <a href="#{{ $anchor }}" style="color:#111; text-decoration:underline;">{{ $findingText }}</a>
                        @else
                            {{ $findingText }}
                        @endif
                    </td>
                    <td class="right-align">{!! ($row['amount'] ?? '') !== '' ? e($row['amount']) : '&nbsp;' !!}</td>
                    <td class="rating-cell" valign="middle">
                        @if ($forDoc ?? false)
                            @include('audits.partials.toc-rating-cell-doc', ['isSection' => false, 'rating' => $rating])
                        @else
                            @include('audits.partials.toc-rating-cell-pdf', ['isSection' => false, 'rating' => $rating])
                        @endif
                    </td>
                    <td class="center">{!! ($row['status'] ?? '') !== '' ? e($row['status']) : '&nbsp;' !!}</td>
                    <td class="toc-page">
                        @if ($pageNo !== '')
                            @if ($anchor !== '')
                                <a href="#{{ $anchor }}" class="bn-page-link" style="color:#111;text-decoration:none;">{{ $pageNo }}</a>
                            @else
                                {{ $pageNo }}
                            @endif
                        @else
                            &nbsp;
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
