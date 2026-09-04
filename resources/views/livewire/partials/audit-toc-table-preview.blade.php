@php
    use App\Livewire\MakeAuditReport;
    /** @var \Illuminate\Support\Collection|array $rows */
    $rows = $rows ?? [];
    $compact = $compact ?? false;
    $hToc = $tableHeaders['toc'] ?? \App\Support\AuditTableHeaders::defaults()['toc'];
    $tocPreviewWidths = ['w-[12mm]', '', 'w-[16mm]', 'w-[24mm]', 'w-[18mm]', 'w-[14mm]'];
@endphp

<div class="{{ ($showTitle ?? true) ? 'mt-[5mm]' : 'mt-0' }}">
    @if ($showTitle ?? true)
        <h3 class="mb-[2mm] text-center text-[13px] font-bold underline decoration-1 underline-offset-4">সূচিপত্র</h3>
    @endif

    <table class="a4-table {{ $compact ? 'a4-table-compact' : '' }} text-[10px] leading-[1.3]">
        <thead>
            <tr>
                @foreach ($hToc as $hi => $label)
                    <x-audit-th :editable="false" class="{{ $tocPreviewWidths[$hi] ?? '' }}">{{ $label }}</x-audit-th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $isSection = ($row['type'] ?? 'item') === 'section';
                    $rating = $row['rating'] ?? '';
                    $anchor = ! $isSection ? MakeAuditReport::findingAnchorId($row['serial'] ?? '') : '';
                    $findingText = ($row['finding'] ?? '') !== '' ? $row['finding'] : '—';
                    $pageNo = $row['page_no'] ?? '';
                @endphp
                <tr class="{{ $isSection ? 'bg-[#efefef]' : '' }}">
                    <td class="text-center font-semibold">
                        @include('audits.partials.bn-num', [
                            'value' => $row['serial'] !== '' ? $row['serial'] : '—',
                            'variant' => $isSection ? 'serial-section' : 'serial',
                        ])
                    </td>
                    <td class="{{ $isSection ? 'font-bold' : '' }}">
                        @if (! $isSection && $anchor !== '')
                            <a href="#{{ $anchor }}" class="text-navy-900 underline decoration-slate-400 underline-offset-2 hover:text-[#2b579a]">{{ $findingText }}</a>
                        @else
                            {{ $findingText }}
                        @endif
                    </td>
                    <td class="text-right">{{ $isSection ? '' : ($row['amount'] !== '' ? $row['amount'] : '') }}</td>
                    <td class="p-0 align-top">
                        @if (! $isSection && $rating !== '')
                            @include('livewire.partials.audit-rating-box', ['rating' => $rating, 'editable' => false])
                        @endif
                    </td>
                    <td class="text-center">{{ $isSection ? '' : ($row['status'] ?? '') }}</td>
                    <td class="text-center">
                        @if (! $isSection && $pageNo !== '')
                            @if ($anchor !== '')
                                <a href="#{{ $anchor }}" class="bn-page-link">
                                    @include('audits.partials.bn-num', ['value' => $pageNo, 'variant' => 'page'])
                                </a>
                            @else
                                @include('audits.partials.bn-num', ['value' => $pageNo, 'variant' => 'page'])
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-2 py-3 text-center text-slate-500">কোনো সূচিপত্র এন্ট্রি নেই</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
