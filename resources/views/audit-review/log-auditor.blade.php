@php
    $p = $profile;
    $k = $p['kpis'];
    $now = \App\Support\AppTime::now();
    $avatarTones = ['bg-sky-100 text-sky-700', 'bg-violet-100 text-violet-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-800', 'bg-rose-100 text-rose-700', 'bg-teal-100 text-teal-700'];
    $initials = collect(preg_split('/\s+/u', trim((string) $auditor->name)))->filter()->take(2)->map(fn ($x) => mb_strtoupper(mb_substr($x, 0, 1)))->join('') ?: '?';
    $role = $auditor->roles->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::headline($n))->join(', ');
    $positionTone = [
        'awaiting_submit' => 'bg-slate-50 text-slate-700 ring-slate-200',
        'in_review' => 'bg-sky-50 text-sky-800 ring-sky-200',
        'review_ready' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'with_maker' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'confirmed' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'totally_fixed' => 'bg-teal-50 text-teal-800 ring-teal-200',
        'maker_done' => 'bg-emerald-100 text-emerald-900 ring-emerald-300',
    ];
    $visitTone = [
        'planned' => 'bg-slate-50 text-slate-700 ring-slate-200',
        'in_progress' => 'bg-sky-50 text-sky-800 ring-sky-200',
        'ongoing' => 'bg-sky-50 text-sky-800 ring-sky-200',
        'completed' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'delayed' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'rescheduled' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
    ];
    $cards = [
        ['label' => 'Active days', 'value' => $k['active_days'], 'hint' => $k['events'].' '.($k['events'] === 1 ? 'activity' : 'activities'), 'tone' => 'from-[#1b3a70] to-[#2b579a]', 'type' => 'note'],
        ['label' => 'Reports started', 'value' => $k['started'], 'hint' => $k['completed'].' finished writing', 'tone' => 'from-sky-400 to-sky-600', 'type' => 'started'],
        ['label' => 'Sent for review', 'value' => $k['sent'], 'hint' => 'First reviews and re-reviews', 'tone' => 'from-violet-400 to-violet-600', 'type' => 'submitted'],
        ['label' => 'Sent back', 'value' => $k['returned'], 'hint' => 'Returned for changes', 'tone' => 'from-amber-400 to-orange-500', 'type' => 'returned'],
        ['label' => 'Confirmed', 'value' => $k['confirmed'], 'hint' => 'Locked by a reviewer', 'tone' => 'from-emerald-400 to-emerald-600', 'type' => 'approved'],
        ['label' => 'Visits finished', 'value' => $k['visits_done'], 'hint' => $k['emails'].' '.($k['emails'] === 1 ? 'email' : 'emails').' sent', 'tone' => 'from-rose-400 to-rose-600', 'type' => 'visit_done'],
    ];
    $dayLabel = function (string $date) use ($now) {
        $d = \Carbon\Carbon::parse($date);
        if ($d->isSameDay($now)) return 'Today';
        if ($d->isSameDay($now->copy()->subDay())) return 'Yesterday';
        return $d->format('l, d M Y');
    };
@endphp
<x-app-layout>
    <div class="space-y-3 px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        @include('audit-review.partials.log-nav', [
            'active' => 'overview',
            'back' => route('audit-review.log', ['range' => $p['range']['key']]),
            'backLabel' => 'Back to Auditors log',
            'title' => 'Auditors log <span class="font-medium text-slate-400">·</span> <span class="font-medium text-sky-700">'.e($auditor->name).'</span>',
            'subtitle' => $p['range']['from']->format('d M').' – '.$p['range']['to']->format('d M Y').' · '.$k['events'].' '.($k['events'] === 1 ? 'activity' : 'activities').' · writing, review, visits, emails and checklists',
            'ranges' => $p['ranges'],
            'rangeKey' => $p['range']['key'],
            'rangeUrl' => fn ($key) => route('audit-review.log.auditor', ['user' => $auditor->id, 'range' => $key]),
        ])

        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0f2147] via-[#1b3a70] to-[#2b579a] px-4 py-3 text-white shadow-[0_14px_32px_rgba(15,33,71,0.25)]">
            <div class="relative flex flex-wrap items-center gap-4">
                <span class="relative inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-[18px] font-bold ring-4 ring-white/15 {{ $avatarTones[$auditor->id % count($avatarTones)] }}">
                    {{ $initials }}
                    <span @class(['absolute -bottom-1 -right-1 h-4 w-4 rounded-full ring-2 ring-[#1b3a70]', 'bg-emerald-400' => $p['online'], 'bg-slate-400' => ! $p['online']])></span>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[17px] font-semibold">{{ $auditor->name }}</p>
                    <p class="truncate text-[12px] text-slate-300">{{ $auditor->email }}{{ $role ? ' · '.$role : '' }}</p>
                    <div class="mt-1.5 flex flex-wrap gap-1.5 text-[11px] font-semibold">
                        <span @class(['rounded-full px-2 py-0.5', 'bg-emerald-400 text-emerald-950' => $p['online'], 'bg-white/10 ring-1 ring-white/15' => ! $p['online']])>
                            {{ $p['online'] ? 'Online now' : ($p['last_seen'] ? 'Last signed in '.$p['last_seen']->diffForHumans($now) : 'Not signed in recently') }}
                        </span>
                        <span class="rounded-full bg-white/10 px-2 py-0.5 ring-1 ring-white/15">Reviewer this month: {{ $monthReviewer?->name ?: 'not set' }}</span>
                        @if ($fixedReviewer && $monthReviewer && (int) $fixedReviewer->id !== (int) $monthReviewer->id)
                            <span class="rounded-full bg-white/10 px-2 py-0.5 ring-1 ring-white/15">Fixed: {{ $fixedReviewer->name }}</span>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="min-w-[64px] rounded-xl bg-[#0f2147]/60 px-3 py-1.5 ring-1 ring-white/15">
                        <p class="text-[18px] font-bold leading-none">{{ $p['totals']['reports'] }}</p>
                        <p class="mt-0.5 text-[10.5px] font-medium text-sky-100">Reports</p>
                    </div>
                    <div class="min-w-[64px] rounded-xl bg-[#0f2147]/60 px-3 py-1.5 ring-1 ring-white/15">
                        <p class="text-[18px] font-bold leading-none">{{ $p['totals']['drafts'] }}</p>
                        <p class="mt-0.5 text-[10.5px] font-medium text-sky-100">Writing</p>
                    </div>
                    <div class="min-w-[64px] rounded-xl bg-[#0f2147]/60 px-3 py-1.5 ring-1 ring-white/15">
                        <p class="text-[18px] font-bold leading-none">{{ $p['totals']['reviewed'] }}</p>
                        <p class="mt-0.5 text-[10.5px] font-medium text-sky-100">Confirmed</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-6">
            @foreach ($cards as $card)
                <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_12px_24px_rgba(15,33,71,0.12)]">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br text-white shadow-[0_6px_14px_rgba(15,33,71,0.2)] {{ $card['tone'] }}">
                            @include('audit-review.partials.activity-icon', ['type' => $card['type'], 'class' => 'h-4 w-4'])
                        </span>
                        <p class="text-[10.5px] font-semibold uppercase leading-tight tracking-wide text-slate-500">{{ $card['label'] }}</p>
                    </div>
                    <p class="mt-1.5 text-[22px] font-bold leading-none text-navy-900">{{ $card['value'] }}</p>
                    <p class="mt-1 truncate text-[11px] text-slate-500">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid items-start gap-3 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="min-w-0 space-y-3">
                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <p class="text-[13px] font-semibold text-navy-900">Activity over time</p>
                    <p class="mb-2 text-[11.5px] text-slate-500">{{ $p['range']['label'] }}. Hover a bar for details.</p>
                    @include('audit-review.partials.activity-chart', ['trend' => $p['trend'], 'max' => $p['trend_max'], 'groups' => $p['groups']])
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                        <p class="text-[13px] font-semibold text-navy-900">Timeline</p>
                        <p class="text-[11.5px] text-slate-500">Newest first, grouped by day</p>
                    </div>
                    <div class="px-3 py-1">
                        @forelse ($p['days'] as $date => $events)
                            <div class="py-2">
                                <div class="sticky top-[72px] z-10 -mx-3 mb-1 flex items-center gap-2 bg-white/95 px-3 py-1 backdrop-blur">
                                    <span class="text-[12px] font-semibold text-navy-900">{{ $dayLabel($date) }}</span>
                                    <span class="rounded-full bg-slate-100 px-1.5 text-[10.5px] font-semibold text-slate-500">{{ $events->count() }}</span>
                                    <span class="h-px flex-1 bg-slate-100"></span>
                                </div>
                                <ul class="relative before:absolute before:bottom-3 before:left-[13px] before:top-3 before:w-px before:bg-slate-200">
                                    @foreach ($events as $event)
                                        @include('audit-review.partials.activity-item', ['event' => $event, 'timeOnly' => true])
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <div class="py-10 text-center">
                                <p class="text-[13px] font-semibold text-slate-700">No activity {{ strtolower($p['range']['label']) }}</p>
                                <p class="mt-0.5 text-[12px] text-slate-500">Try a longer range above.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="min-w-0 space-y-3 xl:sticky xl:top-[80px]">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                        <p class="text-[13px] font-semibold text-navy-900">Reports</p>
                        <a href="{{ route('audit-review.log.pipeline', ['auditor_id' => $auditor->id]) }}" class="text-[11.5px] font-semibold text-sky-700 hover:underline">In pipeline →</a>
                    </div>
                    <ul class="max-h-[340px] divide-y divide-slate-100 overflow-y-auto">
                        @forelse ($p['reports'] as $report)
                            @php
                                $isDraft = $report->status === \App\Models\AuditReport::STATUS_DRAFT;
                                $pct = max(0, min(100, (int) $report->progress_pct));
                            @endphp
                            <li>
                                <a href="{{ route('audit-review.log.show', $report) }}" class="block px-3 py-2 transition hover:bg-sky-50/40">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-[12.5px] font-semibold text-slate-800">{{ $report->entityDisplayName() }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $report->periodLabel() }} · #{{ $report->id }}{{ $report->reviewer ? ' · '.$report->reviewer->name : '' }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-md px-1.5 py-0.5 text-[10.5px] font-semibold ring-1 {{ $isDraft ? 'bg-sky-50 text-sky-800 ring-sky-200' : ($positionTone[$report->workflowPositionKey()] ?? 'bg-slate-50 text-slate-700 ring-slate-200') }}">
                                            {{ $isDraft ? 'Writing' : \Illuminate\Support\Str::limit($report->workflowPositionLabel(), 22) }}
                                        </span>
                                    </div>
                                    @if ($isDraft)
                                        <div class="mt-1.5 flex items-center gap-2">
                                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-violet-500" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-[10.5px] font-semibold text-slate-500">{{ $pct }}%</span>
                                        </div>
                                    @endif
                                    <p class="mt-1 text-[10.5px] text-slate-400">Updated {{ $report->updated_at?->diffForHumans($now) }}</p>
                                </a>
                            </li>
                        @empty
                            <li class="px-3 py-6 text-center text-[12px] text-slate-500">No reports yet.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                        <p class="text-[13px] font-semibold text-navy-900">Branch visits</p>
                        <p class="text-[11.5px] text-slate-500">In this range and the next 45 days</p>
                    </div>
                    <ul class="max-h-[340px] divide-y divide-slate-100 overflow-y-auto">
                        @forelse ($p['visits'] as $visit)
                            @php
                                $status = $visit->execution?->status ?? 'planned';
                                $upcoming = $visit->start_date->greaterThan($now);
                            @endphp
                            <li class="flex items-center gap-2.5 px-3 py-2">
                                <span class="flex w-10 shrink-0 flex-col items-center rounded-lg {{ $upcoming ? 'bg-orange-50 text-orange-700 ring-1 ring-orange-200' : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200' }} py-1">
                                    <span class="text-[14px] font-bold leading-none">{{ $visit->start_date->format('d') }}</span>
                                    <span class="text-[9.5px] font-semibold uppercase">{{ $visit->start_date->format('M') }}</span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[12.5px] font-semibold text-slate-800">{{ $visit->workItem?->entity_label ?: 'Branch visit' }}</p>
                                    <p class="truncate text-[11px] text-slate-500">
                                        {{ $visit->workItem?->activityType?->name ?: 'Visit' }}
                                        · {{ $visit->start_date->format('d M') }}{{ $visit->end_date && ! $visit->end_date->isSameDay($visit->start_date) ? ' – '.$visit->end_date->format('d M') : '' }}
                                        @if ((int) $visit->employee_id !== (int) $auditor->employee_id)
                                            · team member
                                        @endif
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-md px-1.5 py-0.5 text-[10.5px] font-semibold ring-1 {{ $visitTone[$status] ?? $visitTone['planned'] }}">{{ \Illuminate\Support\Str::headline($status) }}</span>
                            </li>
                        @empty
                            <li class="px-3 py-6 text-center text-[12px] text-slate-500">
                                {{ $auditor->employee_id ? 'No visits in this window.' : 'This user is not linked to an employee, so visits cannot be shown.' }}
                            </li>
                        @endforelse
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
