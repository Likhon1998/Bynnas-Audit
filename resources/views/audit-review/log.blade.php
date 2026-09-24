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
@endphp
<x-app-layout>
    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
            <div>
                <div class="mb-1 flex items-center gap-1.5 text-[13px] text-slate-500">
                    <a href="{{ route('audit-review.index') }}" class="hover:text-brand-600">Review Panel</a>
                    <span>/</span>
                    <span class="text-slate-600">Auditors log</span>
                </div>
                <h1 class="text-lg font-semibold tracking-tight text-navy-900">Auditors log</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Watch only · pick one feature</p>
            </div>
            <a href="{{ route('audit-review.index') }}" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Back</a>
        </div>

        <p class="mb-3 text-[12px] text-slate-500">
            History and status only — no approve, annotate, or send here.
            To review, use the <a href="{{ route('audit-review.index') }}" class="font-semibold text-sky-800 underline hover:no-underline">Review Panel</a>.
        </p>

        <div class="mb-3 grid gap-2 sm:grid-cols-3">
            <a href="{{ route('audit-review.log.pipeline') }}" class="rounded-xl border border-sky-200 bg-sky-50/80 px-3 py-3 transition hover:border-sky-300 hover:shadow-sm">
                <p class="text-[13px] font-semibold text-sky-950">Pipeline by auditor</p>
                <p class="mt-1 text-[13px] text-sky-800/80">Where each report sits · filter by auditor, month, position</p>
                <p class="mt-3 text-[20px] font-semibold tabular-nums text-sky-950">{{ $summary['total'] ?? 0 }}</p>
                <p class="text-xs text-sky-700">reports in view</p>
            </a>
            <a href="{{ route('audit-review.log.activity') }}" class="rounded-xl border border-violet-200 bg-violet-50/80 px-3 py-3 transition hover:border-violet-300 hover:shadow-sm">
                <p class="text-[13px] font-semibold text-violet-950">Recent activity</p>
                <p class="mt-1 text-[13px] text-violet-800/80">Latest review events across the pipeline</p>
                <p class="mt-3 text-[20px] font-semibold tabular-nums text-violet-950">{{ $eventCount }}</p>
                <p class="text-xs text-violet-700">recent events</p>
            </a>
            <a href="{{ route('audit-review.assignments') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-3 transition hover:border-slate-300 hover:shadow-sm">
                <p class="text-[13px] font-semibold text-navy-900">Assign reviewers</p>
                <p class="mt-1 text-[13px] text-slate-500">Map each auditor → their reviewer</p>
                <p class="mt-3 text-[12px] font-semibold text-slate-700">Open assignments →</p>
            </a>
        </div>

        <div class="flex flex-wrap gap-1.5">
            @foreach ($positionOptions as $key => $label)
                <a href="{{ route('audit-review.log.pipeline', ['position' => $key]) }}"
                   class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[13px] font-semibold {{ $positionTone[$key] ?? 'border-slate-200 bg-white text-slate-700' }}">
                    <span class="tabular-nums">{{ $summary[$key] ?? 0 }}</span>
                    <span class="opacity-80">{{ \Illuminate\Support\Str::limit($label, 28) }}</span>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
