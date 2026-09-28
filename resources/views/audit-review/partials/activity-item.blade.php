@php
    $toneMap = [
        'sky' => ['icon' => 'from-sky-400 to-sky-600 shadow-[0_4px_10px_rgba(2,132,199,0.35)]', 'text' => 'text-sky-700'],
        'teal' => ['icon' => 'from-teal-400 to-teal-600 shadow-[0_4px_10px_rgba(13,148,136,0.35)]', 'text' => 'text-teal-700'],
        'violet' => ['icon' => 'from-violet-400 to-violet-600 shadow-[0_4px_10px_rgba(124,58,237,0.35)]', 'text' => 'text-violet-700'],
        'amber' => ['icon' => 'from-amber-400 to-orange-500 shadow-[0_4px_10px_rgba(234,88,12,0.35)]', 'text' => 'text-amber-700'],
        'emerald' => ['icon' => 'from-emerald-400 to-emerald-600 shadow-[0_4px_10px_rgba(5,150,105,0.35)]', 'text' => 'text-emerald-700'],
        'orange' => ['icon' => 'from-orange-400 to-orange-600 shadow-[0_4px_10px_rgba(234,88,12,0.35)]', 'text' => 'text-orange-700'],
        'rose' => ['icon' => 'from-rose-400 to-rose-600 shadow-[0_4px_10px_rgba(225,29,72,0.35)]', 'text' => 'text-rose-700'],
        'indigo' => ['icon' => 'from-indigo-400 to-indigo-600 shadow-[0_4px_10px_rgba(79,70,229,0.35)]', 'text' => 'text-indigo-700'],
        'slate' => ['icon' => 'from-slate-400 to-slate-600 shadow-[0_4px_10px_rgba(71,85,105,0.3)]', 'text' => 'text-slate-700'],
    ];
    $tone = $toneMap[$event['tone']] ?? $toneMap['slate'];
    $now = \App\Support\AppTime::now();
@endphp
<li class="relative flex gap-2.5 py-2">
    <span class="relative z-10 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br text-white {{ $tone['icon'] }}">
        @include('audit-review.partials.activity-icon', ['type' => $event['type']])
    </span>
    <div class="min-w-0 flex-1">
        <p class="text-[12.5px] leading-snug text-slate-700">
            @if (! empty($showWho))
                <a href="{{ route('audit-review.log.auditor', $event['user_id']) }}" class="font-semibold text-navy-900 hover:underline">{{ $event['who'] }}</a>
                <span class="text-slate-400">·</span>
            @endif
            <span class="font-semibold {{ $tone['text'] }}">{{ $event['label'] }}</span>
            @if ($event['by'])
                <span class="text-slate-500">by {{ $event['by'] }}</span>
            @endif
        </p>
        @if ($event['detail'])
            <p class="truncate text-[11.5px] text-slate-500" title="{{ $event['detail'] }}">{{ $event['detail'] }}</p>
        @endif
        @if ($event['body'])
            <p class="mt-1 line-clamp-2 rounded-md bg-slate-50 px-2 py-1 text-[11.5px] text-slate-600 ring-1 ring-slate-100">“{{ $event['body'] }}”</p>
        @endif
    </div>
    <div class="shrink-0 text-right">
        <p class="whitespace-nowrap text-[11px] text-slate-400" title="{{ $event['at']->format('d M Y, h:i A') }}">
            @if ($event['date_only'])
                {{ $event['at']->format('d M') }}
            @elseif (! empty($timeOnly))
                {{ $event['at']->format('h:i A') }}
            @else
                {{ $event['at']->diffForHumans($now, ['short' => true, 'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]) }}
            @endif
        </p>
        @if ($event['url'])
            <a href="{{ $event['url'] }}" class="text-[11px] font-semibold text-sky-700 hover:underline">Open</a>
        @endif
    </div>
</li>
