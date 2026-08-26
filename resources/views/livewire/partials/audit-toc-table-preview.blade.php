@php
    /** @var \Illuminate\Support\Collection|array $rows */
    $rows = $rows ?? [];
    $compact = $compact ?? false;
@endphp

<div class="{{ ($showTitle ?? true) ? 'mt-[5mm]' : 'mt-0' }}">
    @if ($showTitle ?? true)
        <h3 class="mb-[2mm] text-center text-[13px] font-bold underline decoration-1 underline-offset-4">সূচিপত্র</h3>
    @endif

    <table class="a4-table {{ $compact ? 'a4-table-compact' : '' }} text-[10px] leading-[1.3]">
        <thead>
            <tr>
                <th class="w-[12mm]">ক্রমিক নং</th>
                <th>নিরীক্ষায় প্রাপ্ত ঘটনা সমূহ</th>
                <th class="w-[16mm]">টাকা</th>
                <th class="w-[24mm]">রেটিং</th>
                <th class="w-[18mm]">বর্তমান অবস্থা</th>
                <th class="w-[14mm]">পৃষ্ঠা নাম্বার</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $isSection = ($row['type'] ?? 'item') === 'section';
                    $rating = $row['rating'] ?? '';
                    $style = \App\Livewire\MakeAuditReport::findingRatingStyle($rating);
                @endphp
                <tr class="{{ $isSection ? 'bg-[#efefef]' : '' }}">
                    <td class="text-center font-semibold">{{ $row['serial'] !== '' ? $row['serial'] : '—' }}</td>
                    <td class="{{ $isSection ? 'font-bold' : '' }}">{{ $row['finding'] !== '' ? $row['finding'] : '—' }}</td>
                    <td class="text-right">{{ $isSection ? '' : ($row['amount'] !== '' ? $row['amount'] : '') }}</td>
                    <td
                        class="text-center font-semibold"
                        style="{{ $isSection || $rating === '' ? '' : 'background: '.$style['bg'].'; color: '.$style['color'].';' }}"
                    >{{ $isSection ? '' : $rating }}</td>
                    <td class="text-center">{{ $isSection ? '' : ($row['status'] ?? '') }}</td>
                    <td class="text-center">{{ $isSection ? '' : ($row['page_no'] ?? '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-2 py-3 text-center text-slate-500">কোনো সূচিপত্র এন্ট্রি নেই</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
