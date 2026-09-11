@php
    use App\Support\AuditDocumentLayout as Doc;
    use App\Livewire\MakeAuditReport;
    $widths = Doc::findingColumnWidths();

    $blocks = $reportBlocks ?? [];
    if ($blocks === []) {
        $sections = $reportSections ?? [];
        if ($sections === []) {
            $sections = [[
                'serial' => '১.০',
                'title' => $financial_section_title ?? '১.০ আর্থিক নিরীক্ষা (Financial Audit) :',
                'findings' => $financialFindings ?? [],
            ]];
        }
        foreach ($sections as $section) {
            $blocks[] = [
                'type' => 'section',
                'serial' => $section['serial'] ?? '১.০',
                'title' => $section['title'] ?? '',
            ];
            foreach (($section['findings'] ?? []) as $finding) {
                $blocks[] = array_merge(['type' => 'finding'], is_array($finding) ? $finding : []);
            }
        }
        $blocks[] = [
            'type' => 'criteria',
            'label' => 'প্রচলিত নিয়ম (Criteria):',
            'body' => $financial_criteria ?? '',
        ];
        $blocks[] = [
            'type' => 'observation',
            'label' => 'পর্যবেক্ষণ (Observation) :',
            'body' => '',
        ];
        $blocks[] = [
            'type' => 'stats',
            'heading' => 'Report Rating Box:',
            'rows' => $vatObservationRows ?? [],
        ];
        $blocks[] = [
            'type' => 'stats',
            'heading' => 'Report Rating Box:',
            'rows' => $taxObservationRows ?? [],
        ];
    }
@endphp

@foreach ($blocks as $bIndex => $block)
    @php $type = $block['type'] ?? ''; @endphp

    @if ($type === 'section')
        <p class="section-heading bold finding-heading" style="{{ $bIndex > 0 ? 'margin-top:4mm;' : '' }}">{!! \App\Support\BanglaNumerals::highlight($block['title'] ?? ($block['serial'] ?? ''), 'serial') !!}</p>

    @elseif ($type === 'finding')
        @php $anchor = MakeAuditReport::findingAnchorId($block['serial'] ?? ''); @endphp
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
                        @include('audits.partials.bn-num', ['value' => $block['serial'] ?? '', 'variant' => 'serial'])
                    </td>
                    <td class="bold center">{{ $block['title'] ?? 'শিরোনাম' }}</td>
                    <td class="body-cell">
                        {{ $block['body'] ?? '' }}
                        @if (($block['amount'] ?? '') !== '')
                            <br><span class="bold">টাকার পরিমাণ:</span> {{ $block['amount'] }}
                        @endif
                    </td>
                    <td class="rating-cell" valign="middle">
                        @if ($forDoc ?? false)
                            @include('audits.partials.rating-box-doc', ['rating' => $block['rating'] ?? ''])
                        @else
                            @include('audits.partials.rating-box-pdf', ['rating' => $block['rating'] ?? ''])
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

    @elseif ($type === 'criteria')
        <p class="bold" style="margin:3mm 0 1mm;">{{ $block['label'] ?? 'প্রচলিত নিয়ম (Criteria):' }}</p>
        <p class="justify" style="margin:0;">{{ $block['body'] ?? $financial_criteria ?? '' }}</p>

    @elseif ($type === 'observation')
        @if (($block['label'] ?? '') !== '')
            <p class="bold" style="margin:3mm 0 1mm;">{{ $block['label'] }}</p>
        @endif
        @if (($block['body'] ?? '') !== '')
            <p class="justify" style="margin:0 0 2mm;">{{ $block['body'] }}</p>
        @else
            <p style="margin:0 0 2mm;border-bottom:1px dotted #111;line-height:1;">&nbsp;</p>
        @endif

    @elseif (in_array($type, ['stats', 'vat', 'tax'], true))
        @php
            $obsHeading = (string) ($block['heading'] ?? 'Report Rating Box:');
            if (in_array($obsHeading, ['ভ্যাট সংক্রান্ত:', 'ট্যাক্স সংক্রান্ত:', 'সারণী:', 'নতুন সারণী:'], true)) {
                $obsHeading = 'Report Rating Box:';
            }
            $obsRows = array_values((array) ($block['rows'] ?? (
                $type === 'tax' ? ($taxObservationRows ?? []) : ($vatObservationRows ?? [])
            )));
        @endphp
        @if ($obsHeading !== '')
            <p class="bold obs-label">{{ $obsHeading }}</p>
        @endif
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
                @foreach ($obsRows as $row)
                    <tr>
                        <td class="center">{{ $row['total_population'] ?? '' }}</td>
                        <td class="center">{{ $row['sample_size'] ?? '' }}</td>
                        <td class="center">{{ $row['instances_found'] ?? '' }}</td>
                        <td class="center">{{ $row['percentage'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif ($type === 'custom_table')
        @include('audits.partials.custom-table-pdf', ['block' => $block])

    @elseif ($type === 'compliance_table')
        @php
            $coreFields = ['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no'];
            $headers = array_values((array) ($block['headers'] ?? \App\Support\AuditTableHeaders::defaults()['compliance']));
            if (count($headers) < 6) {
                $headers = array_values(\App\Support\AuditTableHeaders::defaults()['compliance']);
            }
            $extraCount = max(0, count($headers) - count($coreFields));
            $complianceRows = array_values((array) ($block['rows'] ?? []));
        @endphp
        @include('audits.partials.compliance-heading', ['block' => $block, 'forDoc' => true])
        <table class="doc-table" style="margin-bottom:5mm;font-size:7px;">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($complianceRows as $row)
                    <tr>
                        @foreach ($coreFields as $field)
                            <td class="{{ in_array($field, ['prev_para_no', 'first_discovery_period', 'current_para_no'], true) ? 'center' : '' }}">{{ $row[$field] ?? '' }}</td>
                        @endforeach
                        @for ($ei = 0; $ei < $extraCount; $ei++)
                            <td>{{ $row['extra'][$ei] ?? '' }}</td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif ($type === 'it_checklist')
        @php
            $hItR1 = array_values((array) ($block['headers_r1'] ?? \App\Support\AuditTableHeaders::defaults()['it_r1']));
            $hItR2 = array_values((array) ($block['headers_r2'] ?? \App\Support\AuditTableHeaders::defaults()['it_r2']));
            $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));
            $itRows = array_values((array) ($block['rows'] ?? []));
        @endphp
        <div class="it-checklist-block" style="text-align:center;margin:4mm 0 3mm;">
            <p class="bold finding-heading" style="margin:0 0 1.5mm;font-size:12pt;line-height:1.35;">{!! \App\Support\BanglaNumerals::highlight($block['title'] ?? '', 'serial') !!}</p>
            @foreach (['org_line1', 'org_line2', 'org_line3'] as $orgKey)
                @if (trim((string) ($block[$orgKey] ?? '')) !== '')
                    <p style="margin:0;font-size:10.5pt;line-height:1.45;">{{ $block[$orgKey] }}</p>
                @endif
            @endforeach
            <p style="margin:2mm 0 1mm;font-size:10pt;line-height:1.45;">
                <span class="bold">কর্মসূচীর নাম :</span> {{ $block['program'] ?? '' }}
                &nbsp;&nbsp;&nbsp;
                <span class="bold">শাখার নাম :</span> {{ $block['branch'] ?? '' }}
            </p>
            <p class="bold" style="margin:0 0 3mm;font-size:10.5pt;">{{ $block['instruction'] ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন' }}</p>
        </div>
        <table class="doc-table it-checklist-table" style="margin-bottom:5mm;font-size:9.5pt;width:100%;border-collapse:collapse;table-layout:fixed;">
            <colgroup>
                <col style="width:6%;">
                <col style="width:34%;">
                <col style="width:5.5%;">
                <col style="width:5.5%;">
                <col style="width:5.5%;">
                <col style="width:13%;">
                <col style="width:14%;">
                <col style="width:{{ $extraHeaders === [] ? '16.5%' : '11%' }};">
                @foreach ($extraHeaders as $unused)
                    <col style="width:5.5%;">
                @endforeach
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">{{ $hItR1[0] ?? 'ক্রমিক' }}</th>
                    <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">{{ $hItR1[1] ?? 'বিবরণ' }}</th>
                    <th colspan="3" style="padding:3px 4px;vertical-align:middle;">{{ $hItR1[2] ?? 'Compliance' }}</th>
                    <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">{{ $hItR1[3] ?? 'Action Owner' }}</th>
                    <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">{{ $hItR1[4] ?? 'Management Comments' }}</th>
                    <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">{{ $hItR1[5] ?? 'Recommendation' }}</th>
                    @foreach ($extraHeaders as $eh)
                        <th rowspan="2" style="padding:3px 4px;vertical-align:middle;">{{ $eh }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($hItR2 as $label)
                        <th style="padding:3px 2px;vertical-align:middle;">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($itRows as $row)
                    @php $compliance = (string) ($row['compliance'] ?? ''); @endphp
                    <tr>
                        <td class="center" style="padding:3px 2px;vertical-align:middle;font-weight:700;">{{ $row['sl_no'] ?? '' }}</td>
                        <td style="padding:3px 4px;vertical-align:top;text-align:left;">{{ $row['description'] ?? '' }}</td>
                        <td class="center it-tick-cell" style="padding:3px 2px;vertical-align:middle;">{!! $compliance === 'yes' ? '<span class="it-tick" style="font-family:dejavusans;font-size:12pt;font-weight:bold;">✓</span>' : '&nbsp;' !!}</td>
                        <td class="center it-tick-cell" style="padding:3px 2px;vertical-align:middle;">{!! $compliance === 'no' ? '<span class="it-tick" style="font-family:dejavusans;font-size:12pt;font-weight:bold;">✓</span>' : '&nbsp;' !!}</td>
                        <td class="center it-tick-cell" style="padding:3px 2px;vertical-align:middle;">{!! $compliance === 'na' ? '<span class="it-tick" style="font-family:dejavusans;font-size:12pt;font-weight:bold;">✓</span>' : '&nbsp;' !!}</td>
                        <td style="padding:3px 4px;vertical-align:top;">{{ $row['action_owner'] ?? '' }}</td>
                        <td style="padding:3px 4px;vertical-align:top;">{{ $row['management_comments'] ?? '' }}</td>
                        <td style="padding:3px 4px;vertical-align:top;">{{ $row['recommendation'] ?? '' }}</td>
                        @foreach ($extraHeaders as $ei => $unused)
                            <td style="padding:3px 4px;vertical-align:top;">{{ $row['extra'][$ei] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif ($type === 'external_audit')
        @php
            $coreFields = ['area_of_observation', 'year_of_reporting', 'external_observation', 'compliance', 'internal_index_no'];
            $headers = array_values((array) ($block['headers'] ?? \App\Support\AuditTableHeaders::defaults()['external_audit']));
            if (count($headers) < 5) {
                $headers = array_values(\App\Support\AuditTableHeaders::defaults()['external_audit']);
            }
            $extraCount = max(0, count($headers) - count($coreFields));
            $extRows = array_values((array) ($block['rows'] ?? []));
        @endphp
        <div class="external-audit-block" style="text-align:center;margin:4mm 0 3mm;">
            <p class="bold finding-heading" style="margin:0 0 2mm;font-size:12pt;text-decoration:underline;line-height:1.35;">{!! \App\Support\BanglaNumerals::highlight($block['title'] ?? '', 'serial') !!}</p>
            <p style="margin:0 0 3mm;font-size:10.5pt;font-weight:700;line-height:1.45;">
                {{ $block['branch_label'] ?? 'Name of Branch----' }} {{ $block['branch'] ?? '' }}
            </p>
        </div>
        <table class="doc-table external-audit-table" style="margin-bottom:5mm;font-size:9.5pt;width:100%;border-collapse:collapse;table-layout:fixed;">
            <colgroup>
                <col style="width:14%;">
                <col style="width:11%;">
                <col style="width:{{ $extraCount === 0 ? '38%' : '32%' }};">
                <col style="width:22%;">
                <col style="width:15%;">
                @for ($ei = 0; $ei < $extraCount; $ei++)
                    <col style="width:8%;">
                @endfor
            </colgroup>
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th style="background:#f0e4d4;padding:3px 4px;vertical-align:middle;font-size:9pt;">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($extRows as $row)
                    <tr>
                        @foreach ($coreFields as $field)
                            <td class="{{ in_array($field, ['area_of_observation', 'year_of_reporting', 'internal_index_no'], true) ? 'center' : '' }}" style="padding:3px 4px;vertical-align:top;font-size:9.5pt;line-height:1.4;">{{ $row[$field] ?? '' }}</td>
                        @endforeach
                        @for ($ei = 0; $ei < $extraCount; $ei++)
                            <td style="padding:3px 4px;vertical-align:top;font-size:9.5pt;">{{ $row['extra'][$ei] ?? '' }}</td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif ($type === 'audit_score')
        @php
            $scoreRows = array_values((array) ($block['rows'] ?? []));
            $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));
            $extraCount = count($extraHeaders);
            $adjustments = array_values((array) ($block['adjustments'] ?? []));
            $subsequent = array_values((array) ($block['subsequent'] ?? []));
            $summary = \App\Support\AuditScoreSheet::summarize($scoreRows, $adjustments, $subsequent);
            $auditScoreDisplay = $summary['audit_score_display'] !== '' ? $summary['audit_score_display'] : '—';
            $gradeDisplay = $summary['grade'] !== '' ? $summary['grade'] : '—';
            $initialDisplay = \App\Support\AuditScoreSheet::formatPercent($summary['initial']) ?: '—';
            $finalDisplay = \App\Support\AuditScoreSheet::formatPercent($summary['final']) ?: '—';
            $adjustedDisplay = \App\Support\AuditScoreSheet::formatPercent($summary['adjusted']) ?: '—';
            $colspanAll = 9 + $extraCount;
            $colspanLeft = 8 + $extraCount;
        @endphp
        <table style="width:100%;border-collapse:collapse;margin:1mm 0 2.5mm;font-size:9.5pt;">
            <tr>
                <td style="width:58%;vertical-align:top;padding:0 3mm 0 0;">
                    <p style="margin:0 0 0.8mm;"><span class="bold">Branch Name &amp; Code:</span> {{ $block['branch_name_code'] ?? '' }}</p>
                    <p style="margin:0 0 0.8mm;"><span class="bold">Branch Category:</span> {{ $block['branch_category'] ?? '' }}</p>
                    <p style="margin:0;"><span class="bold">Audit period:</span> {{ $block['audit_period'] ?? '' }}</p>
                </td>
                <td style="width:42%;vertical-align:top;padding:0;">
                    <table style="width:100%;border-collapse:collapse;font-size:9.5pt;">
                        <tr>
                            <td style="border:1px solid #222;background:#E7E6E6;padding:2px 4px;font-weight:700;width:48%;">Audit Score</td>
                            <td style="border:1px solid #222;background:#C6EFCE;padding:2px 4px;text-align:center;font-weight:700;">{{ $auditScoreDisplay }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #222;background:#E7E6E6;padding:2px 4px;font-weight:700;">Performance Grade</td>
                            <td style="border:1px solid #222;background:#F4B183;padding:2px 4px;text-align:center;font-weight:700;">{{ $gradeDisplay }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="doc-table audit-score-table" style="margin-bottom:4mm;font-size:8.5pt;width:100%;border-collapse:collapse;table-layout:fixed;">
            <thead>
                <tr>
                    <th rowspan="2" style="background:#1F4E79;color:#fff;padding:2px 3px;vertical-align:middle;">Observation Title</th>
                    <th rowspan="2" style="background:#1F4E79;color:#fff;padding:2px 3px;vertical-align:middle;">Category / Grade</th>
                    <th colspan="3" style="background:#1F4E79;color:#fff;padding:2px 3px;">Sample Score</th>
                    <th rowspan="2" style="background:#1F4E79;color:#fff;padding:2px 3px;vertical-align:middle;">Instance size (D)</th>
                    <th colspan="2" style="background:#1F4E79;color:#fff;padding:2px 3px;">Achieved Score</th>
                    <th rowspan="2" style="background:#1F4E79;color:#fff;padding:2px 3px;vertical-align:middle;">Audit Score<br>G = (F/C)</th>
                    @foreach ($extraHeaders as $eh)
                        <th rowspan="2" style="background:#1F4E79;color:#fff;padding:2px 3px;vertical-align:middle;">{{ $eh }}</th>
                    @endforeach
                </tr>
                <tr>
                    <th style="background:#2E75B6;color:#fff;padding:2px;">Sample size (A)</th>
                    <th style="background:#2E75B6;color:#fff;padding:2px;">Risk weight (B)</th>
                    <th style="background:#2E75B6;color:#fff;padding:2px;">Risk weighted score<br>C = (A*B)</th>
                    <th style="background:#2E75B6;color:#fff;padding:2px;">Samples not reported<br>E = (A-D)</th>
                    <th style="background:#2E75B6;color:#fff;padding:2px;">Risk weighted score<br>F = (E*B)</th>
                </tr>
                <tr>
                    <th colspan="{{ $colspanAll }}" style="background:#F8CBAD;text-align:left;padding:2px 4px;font-weight:700;">{{ $block['section_label'] ?? 'Sample-based observations' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($scoreRows as $row)
                    @php
                        $computed = \App\Support\AuditScoreSheet::computeRow(is_array($row) ? $row : []);
                        $cat = (string) ($computed['category'] ?? '');
                        $calcBg = \App\Support\AuditScoreSheet::calcCellBg($cat);
                        $gBg = \App\Support\AuditScoreSheet::gCellBg($cat);
                        $inBg = \App\Support\AuditScoreSheet::inputCellBg($cat);
                    @endphp
                    <tr>
                        <td style="padding:2px 3px;vertical-align:top;border:1px solid #222;">{{ $computed['title'] ?? '' }}</td>
                        <td class="center" style="padding:2px 3px;border:1px solid #222;background:{{ $inBg }};">{{ $computed['category'] ?? '' }}</td>
                        <td class="center" style="padding:2px 3px;border:1px solid #222;background:{{ $inBg }};">{{ $computed['sample_size'] ?? '' }}</td>
                        <td class="center" style="padding:2px 3px;border:1px solid #222;background:{{ $inBg }};">{{ $computed['risk_weight'] ?? '' }}</td>
                        <td class="center" style="padding:2px 3px;border:1px solid #222;background:{{ $calcBg }};font-weight:700;">{{ $computed['risk_weighted_c'] ?? '' }}</td>
                        <td class="center" style="padding:2px 3px;border:1px solid #222;background:{{ $inBg }};">{{ $computed['instance_size'] ?? '' }}</td>
                        <td class="center" style="padding:2px 3px;border:1px solid #222;background:{{ $calcBg }};font-weight:700;">{{ $computed['samples_not_reported_e'] ?? '' }}</td>
                        <td class="center" style="padding:2px 3px;border:1px solid #222;background:{{ $calcBg }};font-weight:700;">{{ $computed['risk_weighted_f'] ?? '' }}</td>
                        <td class="center" style="padding:2px 3px;border:1px solid #222;background:{{ $gBg }};font-weight:700;">{{ $computed['audit_score_g'] ?? '' }}</td>
                        @foreach ($extraHeaders as $ei => $unused)
                            <td style="padding:2px 3px;border:1px solid #222;">{{ $row['extra'][$ei] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
                <tr>
                    <td colspan="{{ $colspanLeft }}" style="background:#1F4E79;color:#fff;padding:2px 4px;font-weight:700;border:1px solid #222;">Initial Audit Score</td>
                    <td class="center" style="background:#1F4E79;color:#fff;padding:2px;font-weight:700;border:1px solid #222;">{{ $initialDisplay }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $colspanAll }}" style="background:#F8CBAD;padding:2px 4px;font-weight:700;border:1px solid #222;">Other Considerations:</td>
                </tr>
                @foreach ($adjustments as $adj)
                    <tr>
                        <td colspan="{{ $colspanLeft }}" style="padding:2px 4px;border:1px solid #222;">{{ $adj['label'] ?? '' }}</td>
                        <td class="center" style="padding:2px;border:1px solid #222;">{{ \App\Support\AuditScoreSheet::formatPercent(\App\Support\AuditScoreSheet::parseNumber($adj['value'] ?? null)) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="{{ $colspanLeft }}" style="background:#1F4E79;color:#fff;padding:2px 4px;font-weight:700;border:1px solid #222;">Final Audit Score</td>
                    <td class="center" style="background:#1F4E79;color:#fff;padding:2px;font-weight:700;border:1px solid #222;">{{ $finalDisplay }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $colspanAll }}" style="background:#F8CBAD;padding:2px 4px;font-weight:700;border:1px solid #222;">Other Relevant Considerations:</td>
                </tr>
                @foreach ($subsequent as $sub)
                    <tr>
                        <td colspan="{{ $colspanLeft }}" style="padding:2px 4px;border:1px solid #222;">{{ $sub['label'] ?? '' }}</td>
                        <td class="center" style="padding:2px;border:1px solid #222;">{{ \App\Support\AuditScoreSheet::formatPercent(\App\Support\AuditScoreSheet::parseNumber($sub['value'] ?? null)) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="{{ $colspanLeft }}" style="background:#1F4E79;color:#fff;padding:2px 4px;font-weight:700;border:1px solid #222;">Adjusted Audit Score</td>
                    <td class="center" style="background:#1F4E79;color:#fff;padding:2px;font-weight:700;border:1px solid #222;">{{ $adjustedDisplay }}</td>
                </tr>
            </tbody>
        </table>

    @elseif ($type === 'jobab_table')
        @php
            $jobabRows = array_values((array) ($block['rows'] ?? []));
        @endphp
        <table class="doc-table" style="margin:3mm 0;width:100%;border-collapse:collapse;">
            <tbody>
                @foreach ($jobabRows as $row)
                    @php $cells = array_values((array) ($row['cells'] ?? [])); @endphp
                    <tr>
                        @foreach ($cells as $ci => $cell)
                            <td
                                class="{{ $ci === 0 ? 'bold' : '' }}"
                                style="border:1px solid #333;padding:3px 4px;vertical-align:top;{{ $ci === 0 && count($cells) === 2 ? 'width:38%;' : '' }}"
                            >{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach
