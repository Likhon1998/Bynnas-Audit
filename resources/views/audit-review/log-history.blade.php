@php
    $positionTone = [
        'awaiting_submit' => 'border-slate-200 bg-slate-50 text-slate-700',
        'in_review' => 'border-sky-200 bg-sky-50 text-sky-900',
        'review_ready' => 'border-violet-200 bg-violet-50 text-violet-900',
        'with_maker' => 'border-amber-200 bg-amber-50 text-amber-950',
        'confirmed' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'totally_fixed' => 'border-teal-200 bg-teal-50 text-teal-950',
        'maker_done' => 'border-emerald-300 bg-emerald-100 text-emerald-950',
    ];
    $posKey = $report->workflowPositionKey();
@endphp
<x-app-layout>
    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="{{ route('audit-review.index') }}" class="hover:text-brand-600">Review Panel</a>
                    <span>/</span>
                    <a href="{{ route('audit-review.log') }}" class="hover:text-brand-600">Auditors log</a>
                    <span>/</span>
                    <span class="text-slate-600">#{{ $report->id }}</span>
                </div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">
                    {{ $report->entityDisplayName() }} · {{ $report->periodLabel() }}
                </h1>
                <p class="mt-0.5 text-[12px] text-slate-500">
                    Maker: {{ $report->user?->name ?: '—' }}
                    · Reviewer: {{ $report->reviewer?->name ?: '—' }}
                    · <span class="font-semibold text-slate-700">{{ $report->statusLabel() }}</span>
                    · {{ $reviewContext['round_label'] ?? $report->currentReviewRoundLabel() }}
                </p>
            </div>
            <div class="flex flex-wrap gap-1.5">
                <a href="{{ $documentUrl }}" target="_blank" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">View PDF</a>
                <a href="{{ $downloadReviewUrl }}" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Download pack</a>
                <a href="{{ route('audit-review.log.pipeline') }}" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Back to pipeline</a>
            </div>
        </div>

        <div class="mb-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
            <p class="text-[12px] font-semibold text-slate-800">Watch only</p>
            <p class="mt-0.5 text-[12px] text-slate-600">
                This Auditors log history cannot approve, annotate, send, or reopen. To review a report yourself, use the Review Panel inbox.
            </p>
            @if ($canStepIn)
                <a href="{{ route('audit-review.show', $report) }}" class="mt-2 inline-flex h-8 items-center rounded-md bg-navy-900 px-3 text-[11px] font-semibold text-white hover:bg-slate-800">
                    Open in Review Panel
                </a>
            @endif
        </div>

        <div class="mb-3 flex flex-wrap gap-2">
            <span class="inline-flex rounded-md border px-2.5 py-1 text-[11px] font-semibold {{ $positionTone[$posKey] ?? 'border-slate-200 bg-white text-slate-700' }}">
                {{ $report->workflowPositionLabel() }}
            </span>
            @if ($report->memo_no)
                <span class="inline-flex rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] text-slate-600">Memo {{ $report->memo_no }}</span>
            @endif
        </div>

        <div class="grid gap-3 lg:grid-cols-2">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/70 px-3 py-2.5">
                    <h2 class="text-[13px] font-semibold text-navy-900">Timeline</h2>
                    <p class="text-[11px] text-slate-500">Every review event on this report</p>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($reviewContext['timeline'] ?? [] as $event)
                        <li class="px-3 py-2.5">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[12px] font-semibold text-slate-800">{{ $event['label'] }}</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">{{ $event['round_label'] ?? '' }} · {{ $event['actor'] ?? 'System' }}</p>
                                    @if (! empty($event['body']))
                                        <p class="mt-1 text-[11px] text-slate-400">{{ $event['body'] }}</p>
                                    @endif
                                </div>
                                <p class="shrink-0 text-[11px] text-slate-500">{{ $event['at'] ?? '' }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="px-3 py-10 text-center text-[12px] text-slate-400">No review events yet.</li>
                    @endforelse
                </ul>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/70 px-3 py-2.5">
                    <h2 class="text-[13px] font-semibold text-navy-900">Marks &amp; comments</h2>
                    <p class="text-[11px] text-slate-500">Read-only · current and earlier rounds</p>
                </div>
                <div class="max-h-[28rem] space-y-2 overflow-y-auto p-3">
                    @php
                        $allAsks = collect($reviewContext['current_asks'] ?? [])
                            ->merge($reviewContext['prior_asks'] ?? [])
                            ->values();
                    @endphp
                    @forelse ($allAsks as $ask)
                        <div class="rounded-lg border border-slate-100 bg-slate-50/60 px-3 py-2">
                            <p class="text-[12px] font-medium text-slate-900">{{ $ask['body'] ?: ($ask['quote'] ?: 'Mark') }}</p>
                            <p class="mt-0.5 text-[10px] text-slate-400">
                                Round {{ $ask['review_round'] ?? '?' }} · {{ $ask['author'] ?? 'Reviewer' }}
                                @if (! empty($ask['addressed']))
                                    · <span class="font-semibold text-emerald-700">Maker marked done</span>
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="py-8 text-center text-[12px] text-slate-400">No marks recorded yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
