<div class="mt-2 grid auto-cols-[minmax(150px,1fr)] grid-flow-col gap-2 overflow-x-auto pb-0.5">
    @foreach ($cards as $card)
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm transition hover:shadow-[0_12px_24px_rgba(15,33,71,0.12)]">
            <span class="pointer-events-none absolute -right-6 -top-6 h-16 w-16 rounded-full {{ $card['soft'] }} transition group-hover:scale-110"></span>
            <div class="relative flex items-center gap-2">
                <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br text-white shadow-[0_6px_14px_rgba(15,33,71,0.2)] {{ $card['tone'] }}">
                    @if ($card['type'] === 'people')
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c.6-3 2.8-4.8 5.5-4.8s4.9 1.8 5.5 4.8" stroke-linecap="round"/><circle cx="17" cy="9" r="2.4"/><path d="M16 14.3c2.3.1 4 1.6 4.5 4.2" stroke-linecap="round"/></svg>
                    @else
                        @include('audit-review.partials.activity-icon', ['type' => $card['type'], 'class' => 'h-4 w-4'])
                    @endif
                </span>
                <p class="text-[10.5px] font-semibold uppercase leading-tight tracking-wide text-slate-500">{{ $card['label'] }}</p>
            </div>
            <p class="relative mt-1 text-[20px] font-bold leading-none text-navy-900">{{ $card['value'] }}</p>
            <p class="relative mt-1 truncate text-[11px] text-slate-500">{{ $card['hint'] }}</p>
        </div>
    @endforeach
</div>
