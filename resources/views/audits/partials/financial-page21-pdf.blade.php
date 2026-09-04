@php
    $rowFields = ['area_of_observation', 'compliance_area', 'year_of_reporting', 'external_observation', 'compliance', 'internal_index_no'];
    $headers = [
        'Area of Observation',
        'Compliance Area',
        'Year of reporting',
        'External Audit observation',
        'Compliance',
        'Internal audit report (Index No)',
    ];
@endphp

<p class="bold" style="margin:0 0 2mm;font-size:10px;">{{ $page21_section_title ?? '' }}</p>

<p style="margin:0 0 2mm;font-size:8px;">
    <span class="bold">Year of reporting:</span> {{ $page21_year_of_reporting ?? '' }}
    &nbsp;&nbsp;
    <span class="bold">Name of Branch:</span> {{ $page21_branch_name ?? '' }}
</p>

<table class="doc-table" style="margin-bottom:5mm;font-size:7px;">
    <thead>
        <tr>
            @foreach ($headers as $index => $header)
                <th style="background-color:{{ $index % 2 === 0 ? '#fce5cd' : '#f5d5b8' }};">{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($page21ExternalAuditRows ?? [] as $row)
            <tr>
                @foreach ($rowFields as $field)
                    <td>{{ $row[$field] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

<p class="bold" style="margin:8mm 0 2mm;font-size:9px;">{{ $page21_sign_label ?? 'নিরীক্ষা কর্মকর্তার স্বাক্ষরঃ' }}</p>
<p style="margin:6mm 0 0;font-size:8px;"><span class="bold">নাম :</span> {{ $page21_sign_name ?? '' }}</p>
<p style="margin:0;font-size:8px;"><span class="bold">পদবী :</span> {{ $page21_sign_designation ?? '' }}</p>
