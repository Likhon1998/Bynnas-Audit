@php
    $groupColors = [
        'report' => 'bg-sky-500',
        'review' => 'bg-violet-500',
        'visit' => 'bg-orange-400',
        'other' => 'bg-teal-500',
    ];
    $count = count($trend);
    $labelEvery = $count > 20 ? 5 : ($count > 10 ? 2 : 1);
@endphp
<div>
    <div class="flex h-36 items-end gap-[3px] border-b border-slate-200 pb-px">
        @foreach ($trend as $i => $bin)
            @php $total = $bin['report'] + $bin['review'] + $bin['visit'] + $bin['other']; @endphp
            <div
                class="group relative flex h-full min-w-0 flex-1 flex-col justify-end"
                title="{{ $bin['label'] }} · {{ $total }} {{ $total === 1 ? 'activity' : 'activities' }}{{ $total ? ' ('.collect($groups)->map(fn ($label, $g) => $bin[$g] ? $bin[$g].' '.strtolower($label) : null)->filter()->join(', ').')' : '' }}"
            >
                @if ($total === 0)
                    <div class="h-[3px] rounded-sm bg-slate-100"></div>
                @else
                    <div class="flex flex-col-reverse overflow-hidden rounded-t-md transition group-hover:brightness-110" style="height: {{ max(6, round($total / $max * 100)) }}%">
                        @foreach (['report', 'review', 'visit', 'other'] as $g)
                            @if ($bin[$g] > 0)
                                <div class="{{ $groupColors[$g] }}" style="flex: {{ $bin[$g] }} 1 0%"></div>
                            @endif
                        @endforeach
                    </div>
                    <span class="pointer-events-none absolute -top-5 left-1/2 hidden -translate-x-1/2 rounded bg-navy-900 px-1.5 py-0.5 text-[10px] font-semibold text-white group-hover:block">{{ $total }}</span>
                @endif
            </div>
        @endforeach
    </div>
    <div class="mt-1 flex gap-[3px]">
        @foreach ($trend as $i => $bin)
            <div class="min-w-0 flex-1 truncate text-center text-[10px] text-slate-400">{{ $i % $labelEvery === 0 ? \Illuminate\Support\Str::before($bin['label'], ' –') : '' }}</div>
        @endforeach
    </div>
    <div class="mt-2 flex flex-wrap gap-3 text-[11.5px] text-slate-600">
        @foreach ($groups as $g => $label)
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm {{ $groupColors[$g] }}"></span>{{ $label }}</span>
        @endforeach
    </div>
</div>
