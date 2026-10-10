@php
    $dash = $dash ?? '………………';
    $coreFields = ['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no'];
    $headers = array_values($tableHeaders['compliance'] ?? \App\Support\AuditTableHeaders::defaults()['compliance']);
    $extraCount = max(0, count($headers) - count($coreFields));
    $complianceWidths = $extraCount === 0 ? [9, 22, 13, 25, 19, 12] : [];
    $headingBlock = [
        'serial' => '',
        'title' => $page19_compliance_title ?? '',
        'title_en' => '',
        'period' => $page19_compliance_period ?? '',
        'followup_date' => $page19_compliance_followup_date ?? '',
    ];
@endphp

@include('audits.partials.compliance-heading', ['block' => $headingBlock, 'forDoc' => true])

<table class="doc-table" style="margin-bottom:5mm;width:100%;font-size:9pt;">
    <thead>
        <tr>
            @foreach ($headers as $hi => $header)
                <th style="font-size:9pt;{{ isset($complianceWidths[$hi]) ? 'width:'.$complianceWidths[$hi].'%;' : '' }}">{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($page19ComplianceRows ?? [] as $row)
            <tr>
                @foreach ($coreFields as $field)
                    <td class="{{ in_array($field, ['prev_para_no', 'first_discovery_period', 'current_para_no'], true) ? 'center' : '' }}" style="font-size:9pt;line-height:1.45;vertical-align:top;">{{ $row[$field] ?? '' }}</td>
                @endforeach
                @for ($ei = 0; $ei < $extraCount; $ei++)
                    <td style="font-size:9pt;line-height:1.45;vertical-align:top;">{{ $row['extra'][$ei] ?? '' }}</td>
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>
