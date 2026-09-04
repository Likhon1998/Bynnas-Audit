@php
    $editable = $editable ?? false;
    $cellPad = $cellPad ?? 'border border-slate-800 px-1.5 py-1';
    $variant = $variant ?? 'stats'; // stats | stats_alt
    $headers = $tableHeaders[$variant] ?? \App\Support\AuditTableHeaders::defaults()[$variant];
    $inputClass = 'w-full border-0 bg-transparent px-0.5 text-center text-[10px] font-semibold text-white placeholder-white/60 focus:bg-white/15 focus:ring-1 focus:ring-white/40';
@endphp
<thead>
    <tr>
        @foreach ($headers as $hi => $label)
            <x-audit-th
                :editable="$editable"
                :wire="'tableHeaders.'.$variant.'.'.$hi"
                class="{{ $cellPad }} bg-[#5b2a86] font-semibold text-white"
                :input-class="$inputClass"
            >{{ $label }}</x-audit-th>
        @endforeach
        @if ($editable)
            <th class="{{ $cellPad }} bg-[#5b2a86] text-white"></th>
        @endif
    </tr>
</thead>
