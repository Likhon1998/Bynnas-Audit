@php
    use App\Support\AuditDocumentLayout as Doc;
    use App\Livewire\MakeAuditReport;
    $widths = Doc::findingColumnWidths();
@endphp

<p class="section-heading bold">{{ $financial_section_title }}</p>

@foreach ($financialFindings as $finding)
    @php $anchor = MakeAuditReport::findingAnchorId($finding['serial'] ?? ''); @endphp
    @if ($anchor !== '')
        <a id="{{ $anchor }}" name="{{ $anchor }}"></a>
    @endif
    <table class="doc-table finding-table" style="margin-bottom:2mm;">
        <colgroup>
            @foreach ($widths as $w)
                <col style="width:{{ $w }}%;">
            @endforeach
        </colgroup>
        <tbody>
            <tr>
                <td class="bold center">
                    @include('audits.partials.bn-num', ['value' => $finding['serial'] ?? '', 'variant' => 'serial'])
                </td>
                <td class="bold center">{{ $finding['title'] ?? 'শিরোনাম' }}</td>
                <td class="body-cell">{{ $finding['body'] ?? '' }}</td>
                <td class="rating-cell" valign="middle">
                    @if ($forDoc ?? false)
                        @include('audits.partials.rating-box-doc', ['rating' => $finding['rating'] ?? ''])
                    @else
                        @include('audits.partials.rating-box-pdf', ['rating' => $finding['rating'] ?? ''])
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
@endforeach

<p class="bold" style="margin:3mm 0 1mm;">প্রচলিত নিয়ম (Criteria):</p>
<p class="justify" style="margin:0;">{{ $financial_criteria }}</p>

<p class="bold" style="margin:3mm 0 1mm;">পর্যবেক্ষণ (Observation) :</p>
<p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1;">&nbsp;</p>

<p class="bold obs-label">ভ্যাট সংক্রান্ত:</p>
<table class="doc-table obs-table" style="margin-bottom:3mm;">
    <colgroup>
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
    </colgroup>
    <thead>
        <tr>
            <th>Total Population</th>
            <th>Sample Size(Checked)</th>
            <th>Instantans Found</th>
            <th>Persentange(%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($vatObservationRows as $row)
            <tr>
                <td class="center">{{ $row['total_population'] ?? '' }}</td>
                <td class="center">{{ $row['sample_size'] ?? '' }}</td>
                <td class="center">{{ $row['instances_found'] ?? '' }}</td>
                <td class="center">{{ $row['percentage'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="bold obs-label">ট্যাক্স সংক্রান্ত:</p>
<table class="doc-table obs-table">
    <colgroup>
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
    </colgroup>
    <thead>
        <tr>
            <th>Total Population</th>
            <th>Sample Size(Checked)</th>
            <th>Instantans Found</th>
            <th>Persentange(%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($taxObservationRows as $row)
            <tr>
                <td class="center">{{ $row['total_population'] ?? '' }}</td>
                <td class="center">{{ $row['sample_size'] ?? '' }}</td>
                <td class="center">{{ $row['instances_found'] ?? '' }}</td>
                <td class="center">{{ $row['percentage'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
