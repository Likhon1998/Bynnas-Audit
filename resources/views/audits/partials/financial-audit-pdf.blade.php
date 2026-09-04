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
