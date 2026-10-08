@php
    $o = $overview;
    $k = $o['kpis'];
    $positionTone = [
        'awaiting_submit' => 'border-slate-200 bg-slate-50 text-slate-700',
        'in_review' => 'border-sky-200 bg-sky-50 text-sky-900',
        'review_ready' => 'border-violet-200 bg-violet-50 text-violet-900',
        'with_maker' => 'border-amber-200 bg-amber-50 text-amber-950',
        'confirmed' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'totally_fixed' => 'border-teal-200 bg-teal-50 text-teal-950',
        'maker_done' => 'border-emerald-300 bg-emerald-100 text-emerald-950',
    ];
    $avatarTones = ['bg-sky-100 text-sky-700', 'bg-violet-100 text-violet-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-800', 'bg-rose-100 text-rose-700', 'bg-teal-100 text-teal-700'];
    $initials = fn ($name) => collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('') ?: '?';
    $cards = [
        ['label' => 'Active auditors', 'value' => $k['active'].' / '.$k['auditors'], 'hint' => $k['online'].' online now', 'tone' => 'from-[#1b3a70] to-[#2b579a]', 'soft' => 'bg-sky-100/80', 'type' => 'people'],
        ['label' => 'Reports started', 'value' => $k['started'], 'hint' => 'New drafts opened', 'tone' => 'from-sky-400 to-sky-600', 'soft' => 'bg-sky-100/80', 'type' => 'started'],
        ['label' => 'Sent for review', 'value' => $k['sent'], 'hint' => 'First reviews and re-reviews', 'tone' => 'from-violet-400 to-violet-600', 'soft' => 'bg-violet-100/80', 'type' => 'submitted'],
        ['label' => 'Confirmed', 'value' => $k['confirmed'], 'hint' => 'Locked by a reviewer', 'tone' => 'from-emerald-400 to-emerald-600', 'soft' => 'bg-emerald-100/80', 'type' => 'approved'],
        ['label' => 'Sent back', 'value' => $k['returned'], 'hint' => 'Returned for changes', 'tone' => 'from-amber-400 to-orange-500', 'soft' => 'bg-amber-100/80', 'type' => 'returned'],
        ['label' => 'Visits finished', 'value' => $k['visits_done'], 'hint' => $k['emails'].' '.($k['emails'] === 1 ? 'email' : 'emails').' sent', 'tone' => 'from-rose-400 to-rose-600', 'soft' => 'bg-rose-100/80', 'type' => 'visit_done'],
    ];
@endphp
<x-app-layout>
    <div
        class="space-y-3 px-3 py-3 lg:px-5"
        style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;"
        x-data="{ q: '', filter: 'all' }"
    >
        @include('audit-review.partials.log-nav', [
            'active' => 'overview',
            'online' => $k['online'],
            'subtitle' => $o['range']['from']->format('d M').' – '.$o['range']['to']->format('d M Y').' · '.$k['events'].' '.($k['events'] === 1 ? 'activity' : 'activities').' · writing, review, visits and more. Watch only.',
            'ranges' => $o['ranges'],
            'rangeKey' => $o['range']['key'],
            'rangeUrl' => fn ($key) => route('audit-review.log', ['range' => $key]),
            'kpiCards' => $cards,
        ])

        <div class="grid items-start gap-3 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="min-w-0 space-y-3">
                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <div class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
                        <div>
                            <p class="text-[13px] font-semibold text-navy-900">Team activity</p>
                            <p class="text-[11.5px] text-slate-500">Everything auditors did, {{ strtolower($o['range']['label']) }}. Hover a bar for details.</p>
                        </div>
                    </div>
                    @include('audit-review.partials.activity-chart', ['trend' => $o['trend'], 'max' => $o['trend_max'], 'groups' => $o['groups']])
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                        <p class="mr-auto text-[13px] font-semibold text-navy-900">Auditors <span class="font-normal text-slate-400">{{ count($o['rows']) }}</span></p>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="9" r="5.5"/><path d="m13.5 13.5 3 3" stroke-linecap="round"/></svg>
                            <input type="search" x-model="q" placeholder="Find auditor" class="h-8 w-44 rounded-lg border-slate-200 pl-8 text-[12px] focus:border-sky-400 focus:ring-sky-400">
                        </div>
                        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-[12px] font-semibold">
                            <button type="button" class="rounded-md px-2.5 py-1 transition" :class="filter === 'all' ? 'bg-[#1b3a70] text-white' : 'text-slate-600 hover:bg-slate-50'" @click="filter = 'all'">All</button>
                            <button type="button" class="rounded-md px-2.5 py-1 transition" :class="filter === 'active' ? 'bg-sky-600 text-white' : 'text-slate-600 hover:bg-slate-50'" @click="filter = 'active'">Active</button>
                            <button type="button" class="rounded-md px-2.5 py-1 transition" :class="filter === 'online' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-50'" @click="filter = 'online'">Online</button>
                            <button type="button" class="rounded-md px-2.5 py-1 transition" :class="filter === 'quiet' ? 'bg-amber-500 text-white' : 'text-slate-600 hover:bg-slate-50'" @click="filter = 'quiet'">Quiet 7+ days</button>
                        </div>
                    </div>

                    <div class="hidden grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1.7fr)_100px_16px] gap-3 border-b border-slate-100 px-3 py-1.5 text-[10.5px] font-semibold uppercase tracking-wide text-slate-400 lg:grid">
                        <span>Auditor</span>
                        <span>Working on now</span>
                        <span>{{ $o['range']['label'] }}</span>
                        <span>Activity</span>
                        <span></span>
                    </div>

                    <ul class="divide-y divide-slate-100">
                        @forelse ($o['rows'] as $row)
                            @php
                                $quiet = $row['quiet_days'] === null || $row['quiet_days'] >= 7;
                                $sparkMax = max(1, ...$row['spark']);
                            @endphp
                            <li
                                x-show="(q === '' || @js(mb_strtolower($row['name'].' '.$row['email'])).includes(q.toLowerCase()))
                                    && (filter === 'all'
                                        || (filter === 'active' && {{ $row['activity'] > 0 ? 'true' : 'false' }})
                                        || (filter === 'online' && {{ $row['online'] ? 'true' : 'false' }})
                                        || (filter === 'quiet' && {{ $quiet ? 'true' : 'false' }}))"
                            >
                                <a href="{{ route('audit-review.log.auditor', ['user' => $row['id'], 'range' => $o['range']['key']]) }}" class="group grid items-center gap-3 px-3 py-2.5 transition hover:bg-sky-50/40 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1.7fr)_100px_16px]">
                                    <div class="flex min-w-0 items-center gap-2.5">
                                        <span class="relative inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[12px] font-semibold {{ $avatarTones[$row['id'] % count($avatarTones)] }}">
                                            {{ $initials($row['name']) }}
                                            <span @class([
                                                'absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full ring-2 ring-white',
                                                'bg-emerald-500' => $row['online'],
                                                'bg-amber-400' => ! $row['online'] && $quiet,
                                                'bg-slate-300' => ! $row['online'] && ! $quiet,
                                            ]) title="{{ $row['online'] ? 'Online now' : 'Last active '.$row['last_active_label'] }}"></span>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-[13px] font-semibold text-slate-800 group-hover:text-sky-800">{{ $row['name'] }}</p>
                                            <p class="truncate text-[11.5px] {{ $row['online'] ? 'font-semibold text-emerald-600' : ($quiet ? 'text-amber-700' : 'text-slate-500') }}">
                                                {{ $row['online'] ? 'Online now' : ($row['last_active'] ? 'Active '.$row['last_active_label'] : 'No activity yet') }}
                                                @if ($row['role'])
                                                    <span class="font-normal text-slate-300">·</span> <span class="font-normal text-slate-500">{{ $row['role'] }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="min-w-0 text-[11.5px] text-slate-600">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($row['drafts'])
                                                <span class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-1.5 py-0.5 font-semibold text-sky-800 ring-1 ring-sky-200">{{ $row['drafts'] }} writing{{ $row['draft_progress'] !== null ? ' · '.$row['draft_progress'].'%' : '' }}</span>
                                            @endif
                                            @if ($row['in_review'])
                                                <span class="rounded-md bg-violet-50 px-1.5 py-0.5 font-semibold text-violet-800 ring-1 ring-violet-200">{{ $row['in_review'] }} in review</span>
                                            @endif
                                            @if ($row['with_maker'])
                                                <span class="rounded-md bg-amber-50 px-1.5 py-0.5 font-semibold text-amber-800 ring-1 ring-amber-200">{{ $row['with_maker'] }} to fix</span>
                                            @endif
                                            @if ($row['reviewed'])
                                                <span class="rounded-md bg-emerald-50 px-1.5 py-0.5 font-semibold text-emerald-800 ring-1 ring-emerald-200">{{ $row['reviewed'] }} confirmed</span>
                                            @endif
                                            @if (! $row['drafts'] && ! $row['in_review'] && ! $row['with_maker'] && ! $row['reviewed'])
                                                <span class="text-slate-400">No reports yet</span>
                                            @endif
                                        </div>
                                        @if ($row['next_visit'])
                                            <p class="mt-1 truncate text-slate-500" title="Next visit">
                                                <svg class="-mt-0.5 mr-0.5 inline h-3 w-3 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z"/><circle cx="12" cy="10" r="2.3"/></svg>
                                                Next visit {{ $row['next_visit']['date']->format('d M') }} · {{ $row['next_visit']['place'] }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-5 gap-1 text-center">
                                        @foreach ([['started', 'Started', 'text-sky-700'], ['sent', 'Sent', 'text-violet-700'], ['confirmed', 'Confirmed', 'text-emerald-700'], ['returned', 'Returned', 'text-amber-700'], ['visits_done', 'Visits', 'text-rose-700']] as [$key, $label, $color])
                                            <div class="rounded-md bg-slate-50 px-1 py-1">
                                                <p class="text-[13px] font-bold leading-none {{ $row[$key] ? $color : 'text-slate-300' }}">{{ $row[$key] }}</p>
                                                <p class="mt-0.5 text-[9.5px] leading-tight text-slate-500">{{ $label }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div>
                                        <div class="flex h-7 items-end gap-px" title="{{ $row['activity'] }} {{ $row['activity'] === 1 ? 'activity' : 'activities' }}">
                                            @foreach ($row['spark'] as $value)
                                                <span class="min-w-0 flex-1 rounded-sm {{ $value ? 'bg-gradient-to-t from-sky-500 to-violet-400' : 'bg-slate-100' }}" style="height: {{ $value ? max(18, round($value / $sparkMax * 100)) : 10 }}%"></span>
                                            @endforeach
                                        </div>
                                        <p class="mt-0.5 text-[10.5px] text-slate-500"><span class="font-semibold text-slate-700">{{ $row['activity'] }}</span> {{ $row['activity'] === 1 ? 'activity' : 'activities' }}</p>
                                    </div>

                                    <svg class="hidden h-4 w-4 text-slate-300 transition-colors group-hover:text-sky-600 lg:block" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 15 5-5-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            </li>
                        @empty
                            <li class="px-3 py-10 text-center text-[12px] text-slate-500">No auditors yet.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <aside class="min-w-0 space-y-3 xl:sticky xl:top-[172px]">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                        <div>
                            <p class="text-[13px] font-semibold text-navy-900">Live feed</p>
                            <p class="text-[11.5px] text-slate-500">Latest things auditors did</p>
                        </div>
                        <span class="relative flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-60"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-sky-500"></span></span>
                    </div>
                    <ul class="max-h-[min(62vh,620px)] overflow-y-auto px-3 xl:max-h-[calc(100vh-340px)]">
                        @forelse ($o['feed'] as $event)
                            @include('audit-review.partials.activity-item', ['event' => $event, 'showWho' => true])
                        @empty
                            <li class="py-8 text-center text-[12px] text-slate-500">Nothing happened {{ strtolower($o['range']['label']) }}.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-[13px] font-semibold text-navy-900">Review pipeline now</p>
                        <a href="{{ route('audit-review.log.pipeline') }}" class="text-[11.5px] font-semibold text-sky-700 hover:underline">Open →</a>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($positionOptions as $key => $label)
                            <a href="{{ route('audit-review.log.pipeline', ['position' => $key]) }}" class="inline-flex items-center gap-1.5 rounded-lg border px-2 py-1 text-[11.5px] font-semibold transition hover:-translate-y-0.5 {{ $positionTone[$key] ?? 'border-slate-200 bg-white text-slate-700' }}">
                                <span class="tabular-nums">{{ $summary[$key] ?? 0 }}</span>
                                <span class="opacity-80">{{ \Illuminate\Support\Str::limit($label, 26) }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
