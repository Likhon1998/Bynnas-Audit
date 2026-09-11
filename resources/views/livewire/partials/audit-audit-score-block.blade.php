{{-- Sample-based Audit Score — Excel-matched layout, auto C/E/F/G, extra columns --}}
@php
    use App\Support\AuditScoreSheet;
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $blockIndex = (int) ($blockIndex ?? 0);
    $rows = array_values((array) ($block['rows'] ?? []));
    $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));
    $extraCount = count($extraHeaders);
    $adjustments = array_values((array) ($block['adjustments'] ?? AuditScoreSheet::defaultAdjustments()));
    $subsequent = array_values((array) ($block['subsequent'] ?? AuditScoreSheet::defaultSubsequent()));
    $summary = AuditScoreSheet::summarize($rows, $adjustments, $subsequent);
    $auditScoreDisplay = $summary['audit_score_display'] !== '' ? $summary['audit_score_display'] : '—';
    $gradeDisplay = $summary['grade'] !== '' ? $summary['grade'] : '—';
    $initialDisplay = AuditScoreSheet::formatPercent($summary['initial']) ?: '—';
    $finalDisplay = AuditScoreSheet::formatPercent($summary['final']) ?: '—';
    $adjustedDisplay = AuditScoreSheet::formatPercent($summary['adjusted']) ?: '—';
    $fs = $compact ? '8.5px' : '10.5px';
    $pad = $compact ? '1px 3px' : '3px 4px';
    $coreColspan = 9 + $extraCount;
    $fullColspan = $coreColspan + ($editable ? 1 : 0);
@endphp

<div class="mt-[2mm] mb-[4mm] audit-score-sheet" wire:key="audit-score-{{ $blockIndex }}" id="{{ \App\Livewire\MakeAuditReport::sectionAnchorId((string) ($block['serial'] ?? 'audit-score')) }}" style="font-size:{{ $fs }};line-height:1.35;">
    @if ($editable)
        <div class="mb-2 flex flex-wrap items-center gap-2" style="font-size:11px;">
            <p class="font-semibold text-indigo-900">Audit Score Sheet</p>
            <button type="button" wire:click="fillAuditScoreBlockFromReport({{ $blockIndex }})" class="rounded border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">রিপোর্ট থেকে ভরুন</button>
            <button type="button" wire:click="addAuditScoreBlockRow({{ $blockIndex }})" class="rounded bg-indigo-700 px-2 py-1 text-[11px] font-semibold text-white">+ সারি</button>
            <button type="button" wire:click="addAuditScoreBlockColumn({{ $blockIndex }})" class="rounded bg-indigo-700 px-2 py-1 text-[11px] font-semibold text-white">+ কলাম</button>
            <button type="button" wire:click="addAuditScoreAdjustmentRow({{ $blockIndex }})" class="rounded bg-indigo-700 px-2 py-1 text-[11px] font-semibold text-white">+ Adjustment</button>
            <button type="button" wire:click="recalculateAuditScoreBlock({{ $blockIndex }})" class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-800">হিসাব করুন</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'up')" class="ml-auto text-slate-600 hover:underline">↑</button>
            <button type="button" wire:click="moveBlock({{ $blockIndex }}, 'down')" class="text-slate-600 hover:underline">↓</button>
            <button type="button" wire:click="removeBlock({{ $blockIndex }})" class="text-rose-600 hover:underline">মুছুন</button>
        </div>
    @endif

    {{-- Header meta + summary box --}}
    <table style="width:100%;border-collapse:collapse;margin-bottom:3mm;table-layout:fixed;">
        <tr>
            <td style="width:58%;vertical-align:top;padding:0 {{ $compact ? '2mm' : '3mm' }} 0 0;">
                @if ($editable)
                    <div style="margin-bottom:2px;"><span style="font-weight:700;">Branch Name &amp; Code:</span>
                        <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.branch_name_code" class="ml-1 rounded border border-slate-200 px-1" style="width:70%;font-size:{{ $fs }};"></div>
                    <div style="margin-bottom:2px;"><span style="font-weight:700;">Branch Category:</span>
                        <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.branch_category" class="ml-1 rounded border border-slate-200 px-1" style="width:55%;font-size:{{ $fs }};"></div>
                    <div><span style="font-weight:700;">Audit period:</span>
                        <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.audit_period" class="ml-1 rounded border border-slate-200 px-1" style="width:65%;font-size:{{ $fs }};"></div>
                @else
                    <p style="margin:0 0 1.5px;"><span style="font-weight:700;">Branch Name &amp; Code:</span> {{ $block['branch_name_code'] ?? '' }}</p>
                    <p style="margin:0 0 1.5px;"><span style="font-weight:700;">Branch Category:</span> {{ $block['branch_category'] ?? '' }}</p>
                    <p style="margin:0;"><span style="font-weight:700;">Audit period:</span> {{ $block['audit_period'] ?? '' }}</p>
                @endif
            </td>
            <td style="width:42%;vertical-align:top;padding:0;">
                <table style="width:100%;border-collapse:collapse;font-size:{{ $fs }};">
                    <tr>
                        <td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SUMMARY_LABEL }};padding:{{ $pad }};font-weight:700;width:48%;">Audit Score</td>
                        <td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_CALC_GREEN }};padding:{{ $pad }};text-align:center;font-weight:700;">{{ $auditScoreDisplay }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SUMMARY_LABEL }};padding:{{ $pad }};font-weight:700;">Performance Grade</td>
                        <td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_GRADE }};padding:{{ $pad }};text-align:center;font-weight:700;">{{ $gradeDisplay }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="overflow-x-auto">
        <table class="audit-score-table" style="width:100%;border-collapse:collapse;table-layout:fixed;font-size:{{ $fs }};">
            <colgroup>
                <col style="width:{{ $extraCount ? '28%' : '32%' }};">
                <col style="width:8%;">
                <col style="width:7%;">
                <col style="width:6%;">
                <col style="width:8%;">
                <col style="width:7%;">
                <col style="width:8%;">
                <col style="width:8%;">
                <col style="width:7%;">
                @foreach ($extraHeaders as $unused)
                    <col style="width:6%;">
                @endforeach
                @if ($editable)<col style="width:3%;">@endif
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;vertical-align:middle;">Observation Title</th>
                    <th rowspan="2" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;vertical-align:middle;">Category / Grade</th>
                    <th colspan="3" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">Sample Score</th>
                    <th rowspan="2" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;vertical-align:middle;">Instance size (D)</th>
                    <th colspan="2" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">Achieved Score</th>
                    <th rowspan="2" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;vertical-align:middle;">Audit Score<br>G = (F/C)</th>
                    @foreach ($extraHeaders as $ei => $eh)
                        <th rowspan="2" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;vertical-align:middle;">
                            @if ($editable)
                                <div class="flex items-start gap-0.5">
                                    <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.extra_headers.{{ $ei }}" class="w-full border-0 bg-transparent text-center text-white" style="font-size:{{ $fs }};font-weight:700;">
                                    <button type="button" wire:click="removeAuditScoreBlockColumn({{ $blockIndex }}, {{ $ei }})" class="text-rose-200">×</button>
                                </div>
                            @else
                                {{ $eh }}
                            @endif
                        </th>
                    @endforeach
                    @if ($editable)<th rowspan="2" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER }};"></th>@endif
                </tr>
                <tr>
                    <th style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER_SUB }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">Sample size (A)</th>
                    <th style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER_SUB }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">Risk weight (B)</th>
                    <th style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER_SUB }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">Risk weighted score<br>C = (A*B)</th>
                    <th style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER_SUB }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">Samples not reported<br>E = (A-D)</th>
                    <th style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_HEADER_SUB }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">Risk weighted score<br>F = (E*B)</th>
                </tr>
                <tr>
                    <th colspan="{{ $fullColspan }}" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SECTION }};padding:{{ $pad }};text-align:left;font-weight:700;">
                        @if ($editable)
                            <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.section_label" class="w-full border-0 bg-transparent font-bold" style="font-size:{{ $fs }};">
                        @else
                            {{ $block['section_label'] ?? 'Sample-based observations' }}
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $rowIndex => $row)
                    @php
                        $computed = AuditScoreSheet::computeRow(is_array($row) ? $row : []);
                        $cat = (string) ($computed['category'] ?? '');
                        $calcBg = AuditScoreSheet::calcCellBg($cat);
                        $gBg = AuditScoreSheet::gCellBg($cat);
                        $inBg = AuditScoreSheet::inputCellBg($cat);
                    @endphp
                    <tr>
                        <td style="border:1px solid #222;padding:{{ $pad }};vertical-align:top;">
                            @if ($editable)
                                <textarea wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.title" rows="2" class="w-full border-0 bg-transparent p-0" style="font-size:{{ $fs }};line-height:1.35;"></textarea>
                            @else
                                <span style="white-space:pre-wrap;">{{ $computed['title'] ?? '' }}</span>
                            @endif
                        </td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;vertical-align:middle;background:{{ $inBg }};">
                            @if ($editable)
                                <select wire:change="setAuditScoreCategory({{ $blockIndex }}, {{ $rowIndex }}, $event.target.value)" class="w-full border-0 bg-transparent text-center" style="font-size:{{ $fs }};">
                                    @foreach (AuditScoreSheet::categoryOptions() as $opt)
                                        <option value="{{ $opt }}" @selected($cat === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @else
                                {{ $cat }}
                            @endif
                        </td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;vertical-align:middle;background:{{ $inBg }};">
                            @if ($editable)
                                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.sample_size" class="w-full border-0 bg-transparent text-center" style="font-size:{{ $fs }};">
                            @else
                                {{ $computed['sample_size'] ?? '' }}
                            @endif
                        </td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;vertical-align:middle;background:{{ $inBg }};">
                            @if ($editable)
                                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.risk_weight" class="w-full border-0 bg-transparent text-center" style="font-size:{{ $fs }};">
                            @else
                                {{ $computed['risk_weight'] ?? '' }}
                            @endif
                        </td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;vertical-align:middle;background:{{ $calcBg }};font-weight:700;">{{ $computed['risk_weighted_c'] ?? '' }}</td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;vertical-align:middle;background:{{ $inBg }};">
                            @if ($editable)
                                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.instance_size" class="w-full border-0 bg-transparent text-center" style="font-size:{{ $fs }};">
                            @else
                                {{ $computed['instance_size'] ?? '' }}
                            @endif
                        </td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;vertical-align:middle;background:{{ $calcBg }};font-weight:700;">{{ $computed['samples_not_reported_e'] ?? '' }}</td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;vertical-align:middle;background:{{ $calcBg }};font-weight:700;">{{ $computed['risk_weighted_f'] ?? '' }}</td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;vertical-align:middle;background:{{ $gBg }};font-weight:700;">{{ $computed['audit_score_g'] ?? '' }}</td>
                        @foreach ($extraHeaders as $ei => $unused)
                            <td style="border:1px solid #222;padding:{{ $pad }};vertical-align:top;">
                                @if ($editable)
                                    <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.rows.{{ $rowIndex }}.extra.{{ $ei }}" class="w-full border-0 bg-transparent text-center" style="font-size:{{ $fs }};">
                                @else
                                    {{ $row['extra'][$ei] ?? '' }}
                                @endif
                            </td>
                        @endforeach
                        @if ($editable)
                            <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;">
                                @if (count($rows) > 1)
                                    <button type="button" wire:click="removeAuditScoreBlockRow({{ $blockIndex }}, {{ $rowIndex }})" class="text-rose-600">×</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach

                <tr>
                    <td colspan="{{ 8 + $extraCount }}" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SCORE_ROW }};color:#fff;padding:{{ $pad }};font-weight:700;">Initial Audit Score</td>
                    <td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SCORE_ROW }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">{{ $initialDisplay }}</td>
                    @if ($editable)<td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SCORE_ROW }};"></td>@endif
                </tr>
                <tr>
                    <td colspan="{{ $fullColspan }}" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SECTION }};padding:{{ $pad }};font-weight:700;">Other Considerations:</td>
                </tr>
                @foreach ($adjustments as $adjIndex => $adj)
                    <tr>
                        <td colspan="{{ 8 + $extraCount }}" style="border:1px solid #222;padding:{{ $pad }};">
                            @if ($editable)
                                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.adjustments.{{ $adjIndex }}.label" class="w-full border-0 bg-transparent" style="font-size:{{ $fs }};">
                            @else
                                {{ $adj['label'] ?? '' }}
                            @endif
                        </td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;">
                            @if ($editable)
                                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.adjustments.{{ $adjIndex }}.value" class="w-full border-0 bg-transparent text-center" style="font-size:{{ $fs }};" placeholder="-5">
                            @else
                                {{ AuditScoreSheet::formatPercent(AuditScoreSheet::parseNumber($adj['value'] ?? null)) }}
                            @endif
                        </td>
                        @if ($editable)
                            <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;">
                                <button type="button" wire:click="removeAuditScoreAdjustmentRow({{ $blockIndex }}, {{ $adjIndex }})" class="text-rose-600">×</button>
                            </td>
                        @endif
                    </tr>
                @endforeach
                <tr>
                    <td colspan="{{ 8 + $extraCount }}" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SCORE_ROW }};color:#fff;padding:{{ $pad }};font-weight:700;">Final Audit Score</td>
                    <td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SCORE_ROW }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">{{ $finalDisplay }}</td>
                    @if ($editable)<td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SCORE_ROW }};"></td>@endif
                </tr>
                <tr>
                    <td colspan="{{ $fullColspan }}" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SECTION }};padding:{{ $pad }};font-weight:700;">Other Relevant Considerations:</td>
                </tr>
                @foreach ($subsequent as $subIndex => $sub)
                    <tr>
                        <td colspan="{{ 8 + $extraCount }}" style="border:1px solid #222;padding:{{ $pad }};">
                            @if ($editable)
                                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.subsequent.{{ $subIndex }}.label" class="w-full border-0 bg-transparent" style="font-size:{{ $fs }};">
                            @else
                                {{ $sub['label'] ?? '' }}
                            @endif
                        </td>
                        <td style="border:1px solid #222;padding:{{ $pad }};text-align:center;">
                            @if ($editable)
                                <input type="text" wire:model.blur="reportBlocks.{{ $blockIndex }}.subsequent.{{ $subIndex }}.value" class="w-full border-0 bg-transparent text-center" style="font-size:{{ $fs }};" placeholder="0">
                            @else
                                {{ AuditScoreSheet::formatPercent(AuditScoreSheet::parseNumber($sub['value'] ?? null)) }}
                            @endif
                        </td>
                        @if ($editable)<td style="border:1px solid #222;padding:{{ $pad }};"></td>@endif
                    </tr>
                @endforeach
                <tr>
                    <td colspan="{{ 8 + $extraCount }}" style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SCORE_ROW }};color:#fff;padding:{{ $pad }};font-weight:700;">Adjusted Audit Score</td>
                    <td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SCORE_ROW }};color:#fff;padding:{{ $pad }};text-align:center;font-weight:700;">{{ $adjustedDisplay }}</td>
                    @if ($editable)<td style="border:1px solid #222;background:{{ AuditScoreSheet::COLOR_SCORE_ROW }};"></td>@endif
                </tr>
            </tbody>
        </table>
    </div>
</div>
