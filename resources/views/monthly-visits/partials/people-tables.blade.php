<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" data-roster="this">
    <div class="border-b border-slate-100 bg-slate-50 px-3 py-2">
        <h2 class="text-[13px] font-semibold text-navy-900">{{ $monthLabel }}</h2>
    </div>
    <div class="overflow-auto">
        <table class="w-full border-collapse text-left text-[12px]">
            <thead class="sticky top-0 z-10">
                <tr class="bg-[#1e3a5f] text-[11px] font-semibold uppercase tracking-wide text-white">
                    <th class="w-10 border border-[#16324f] px-2 py-2 text-center">#</th>
                    <th class="border border-[#16324f] px-2 py-2">Shakha / place</th>
                    <th class="border border-[#16324f] px-2 py-2">Type</th>
                    <th class="border border-[#16324f] px-2 py-2">Person</th>
                    <th class="border border-[#16324f] px-2 py-2">Position</th>
                    <th class="border border-[#16324f] px-2 py-2">From</th>
                    <th class="border border-[#16324f] px-2 py-2">To</th>
                    <th class="border border-[#16324f] px-2 py-2 text-center">Days</th>
                    <th class="border border-[#16324f] px-2 py-2">Last audit</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shakhaRows as $row)
                    @php
                        $typeTone = match (true) {
                            str_contains(strtolower($row['type']), 'pksf'), str_contains(strtolower($row['type']), 'maternity') => 'bg-orange-50 text-orange-800',
                            str_contains(strtolower($row['type']), 'hq') => 'bg-sky-50 text-sky-800',
                            str_contains(strtolower($row['type']), 'area') => 'bg-amber-50 text-amber-900',
                            str_contains(strtolower($row['type']), 'project') => 'bg-teal-50 text-teal-800',
                            str_contains(strtolower($row['type']), 'monitor') => 'bg-cyan-50 text-cyan-800',
                            default => 'bg-emerald-50 text-emerald-800',
                        };
                        $rowBg = $loop->iteration % 2 === 0 ? 'bg-slate-50/80' : 'bg-white';
                    @endphp
                    <tr
                        class="{{ $rowBg }} hover:bg-sky-50/70"
                        data-visit-row
                        data-person-ids="{{ $row['person_ids'] }}"
                        data-type="{{ e($row['type']) }}"
                        @if ($interactive) x-show="rowVisible($el)" @endif
                    >
                        <td class="border border-slate-200 px-2 py-1.5 text-center tabular-nums text-slate-400" @if ($interactive) x-text="shownIndex($el)" @endif>{{ $loop->iteration }}</td>
                        <td class="border border-slate-200 px-2 py-1.5 font-semibold text-navy-900">{{ $row['place'] }}</td>
                        <td class="border border-slate-200 px-2 py-1.5">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $typeTone }}">{{ $row['type'] }}</span>
                        </td>
                        <td class="border border-slate-200 px-2 py-1.5 font-medium text-navy-900">
                            @forelse ($row['people'] as $person)
                                <div>{{ $person['name'] }}</div>
                            @empty
                                —
                            @endforelse
                        </td>
                        <td class="border border-slate-200 px-2 py-1.5 text-slate-600">
                            @forelse ($row['people'] as $person)
                                <div>{{ $person['title'] !== '' ? $person['title'] : '—' }}</div>
                            @empty
                                —
                            @endforelse
                        </td>
                        <td class="whitespace-nowrap border border-slate-200 px-2 py-1.5 tabular-nums text-slate-700">{{ $row['from'] }}</td>
                        <td class="whitespace-nowrap border border-slate-200 px-2 py-1.5 tabular-nums text-slate-700">{{ $row['to'] }}</td>
                        <td class="border border-slate-200 px-2 py-1.5 text-center font-semibold tabular-nums text-navy-900">{{ $row['days'] > 0 ? $row['days'] : '—' }}</td>
                        <td class="whitespace-nowrap border border-slate-200 px-2 py-1.5 font-medium text-[#1e3a5f]">{{ $row['last_audit'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="border border-slate-200 px-3 py-8 text-center text-[13px] text-slate-500">No visits in this month.</td>
                    </tr>
                @endforelse
                @if ($interactive && $shakhaRows->isNotEmpty())
                    <tr x-show="visibleCount('this') === 0" x-cloak>
                        <td colspan="9" class="border border-slate-200 px-3 py-8 text-center text-[13px] text-slate-500">No visits for that person and type.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
