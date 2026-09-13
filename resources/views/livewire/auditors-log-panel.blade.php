@php
    $filters = $log['filters'];
    $summary = $log['summary'];
    $positionOptions = $log['position_options'];
    $positionTone = [
        'awaiting_submit' => 'border-slate-200 bg-slate-50 text-slate-700',
        'in_review' => 'border-sky-200 bg-sky-50 text-sky-900',
        'review_ready' => 'border-violet-200 bg-violet-50 text-violet-900',
        'with_maker' => 'border-amber-200 bg-amber-50 text-amber-950',
        'confirmed' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'totally_fixed' => 'border-teal-200 bg-teal-50 text-teal-950',
        'maker_done' => 'border-emerald-300 bg-emerald-100 text-emerald-950',
    ];
    $filterQuery = array_filter([
        'month' => $month !== '' ? $month : null,
        'year' => $year !== '' ? $year : null,
        'auditor_id' => $auditorId !== '' ? $auditorId : null,
        'reviewer_id' => $reviewerId !== '' ? $reviewerId : null,
        'q' => $q !== '' ? $q : null,
        'position' => $position !== '' ? $position : null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp

<div style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;" wire:loading.class="opacity-70">
    <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="mb-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                <a href="{{ route('audit-review.index') }}" class="hover:text-brand-600">Review Panel</a>
                <span>/</span>
                <a href="{{ route('audit-review.log') }}" class="hover:text-brand-600">Auditors log</a>
                <span>/</span>
                <span class="text-slate-600">{{ $mode === 'activity' ? 'Activity' : 'Pipeline' }}</span>
            </div>
            <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">
                {{ $mode === 'activity' ? 'Recent activity' : 'Pipeline by auditor' }}
            </h1>
            <p class="mt-0.5 text-[12px] text-slate-500">
                Watch only · {{ $mode === 'activity' ? 'latest review events' : 'where each report sits' }} · updates live
            </p>
        </div>
        <div class="flex flex-wrap gap-1.5">
            @if ($mode === 'pipeline')
                <a href="{{ route('audit-review.log.activity', $filterQuery) }}" class="inline-flex h-9 items-center rounded-md border border-violet-200 bg-violet-50 px-3 text-[12px] font-semibold text-violet-900 hover:bg-violet-100">Activity</a>
            @else
                <a href="{{ route('audit-review.log.pipeline', $filterQuery) }}" class="inline-flex h-9 items-center rounded-md border border-sky-200 bg-sky-50 px-3 text-[12px] font-semibold text-sky-900 hover:bg-sky-100">Pipeline</a>
            @endif
            <a href="{{ route('audit-review.assignments') }}" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Assign</a>
            <a href="{{ route('audit-review.log') }}" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Hub</a>
        </div>
    </div>

    <div class="mb-3 rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm">
        <div class="grid gap-2 sm:grid-cols-2 {{ $mode === 'pipeline' ? 'xl:grid-cols-6' : 'xl:grid-cols-5' }}">
            <label class="block text-[11px] font-semibold text-slate-500">
                Month
                <select wire:model.live="month" class="mt-1 h-9 w-full rounded-md border-slate-200 text-[12px]">
                    <option value="">All</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                    @endfor
                </select>
            </label>
            <label class="block text-[11px] font-semibold text-slate-500">
                Year
                <select wire:model.live="year" class="mt-1 h-9 w-full rounded-md border-slate-200 text-[12px]">
                    <option value="">All</option>
                    @for ($y = (int) $now->year - 2; $y <= (int) $now->year + 1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </label>
            <label class="block text-[11px] font-semibold text-slate-500">
                Auditor
                <select wire:model.live="auditorId" class="mt-1 h-9 w-full rounded-md border-slate-200 text-[12px]">
                    <option value="">All auditors</option>
                    @foreach ($log['auditor_options'] as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-[11px] font-semibold text-slate-500">
                Reviewer
                <select wire:model.live="reviewerId" class="mt-1 h-9 w-full rounded-md border-slate-200 text-[12px]">
                    <option value="">All reviewers</option>
                    @foreach ($log['reviewer_options'] as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                    @endforeach
                </select>
            </label>
            @if ($mode === 'pipeline')
                <label class="block text-[11px] font-semibold text-slate-500">
                    Position
                    <select wire:model.live="position" class="mt-1 h-9 w-full rounded-md border-slate-200 text-[12px]">
                        <option value="">All positions</option>
                        @foreach ($positionOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <label class="block text-[11px] font-semibold text-slate-500">
                Search
                <input type="search" wire:model.live.debounce.300ms="q" placeholder="Memo, branch…" class="mt-1 h-9 w-full rounded-md border-slate-200 text-[12px]">
            </label>
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-1.5">
            <button type="button" wire:click="clearFilters" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-[11px] font-medium text-slate-600 hover:bg-slate-50">Clear</button>
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400" wire:loading>
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-sky-500"></span>
                Updating…
            </span>
            <span class="ml-auto text-[11px] text-slate-400" wire:loading.remove>
                @if ($mode === 'pipeline')
                    {{ $log['filtered_total'] ?? 0 }} report(s)
                    @if ($position !== '' && ($summary['total'] ?? 0) !== ($log['filtered_total'] ?? 0))
                        <span class="text-slate-300">·</span> {{ $summary['total'] ?? 0 }} total
                    @endif
                @else
                    {{ count($log['events']) }} event(s)
                @endif
            </span>
        </div>
    </div>

    @if ($mode === 'pipeline')
        <div class="mb-3 flex flex-wrap gap-1.5">
            @foreach ($positionOptions as $key => $label)
                @php
                    $isActive = $position === $key;
                    $chipBase = 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-semibold transition '.$positionTone[$key];
                    $chipState = $isActive
                        ? ' outline outline-2 outline-offset-1 outline-navy-900/50 shadow-sm'
                        : ' hover:opacity-90';
                @endphp
                <button
                    type="button"
                    wire:click="setPosition('{{ $key }}')"
                    class="{{ $chipBase }}{{ $chipState }}"
                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                >
                    <span class="tabular-nums">{{ $summary[$key] ?? 0 }}</span>
                    <span>{{ \Illuminate\Support\Str::limit($label, 22) }}</span>
                </button>
            @endforeach
        </div>

        <div class="space-y-3" wire:key="pipeline-{{ $month }}-{{ $year }}-{{ $auditorId }}-{{ $reviewerId }}-{{ $position }}-{{ $q }}">
            @forelse ($log['auditors'] as $auditor)
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" wire:key="auditor-{{ $auditor['id'] }}">
                    <div class="flex flex-wrap items-start justify-between gap-2 border-b border-slate-100 bg-slate-50/70 px-3 py-2">
                        <div>
                            <h2 class="text-[13px] font-semibold text-navy-900">{{ $auditor['name'] }}</h2>
                            <p class="text-[11px] text-slate-500">{{ $auditor['email'] ?: '—' }} · {{ $auditor['counts']['total'] ?? count($auditor['reports']) }} report(s)</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-[12px]">
                            <thead class="border-b border-slate-100 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="px-3 py-2">Report</th>
                                    <th class="px-3 py-2">Period</th>
                                    <th class="px-3 py-2">Position</th>
                                    <th class="px-3 py-2">Round</th>
                                    <th class="px-3 py-2">Reviewer</th>
                                    <th class="px-3 py-2">Updated</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($auditor['reports'] as $row)
                                    <tr class="align-top hover:bg-slate-50/70" wire:key="report-{{ $row['id'] }}">
                                        <td class="px-3 py-2">
                                            <p class="font-semibold text-slate-800">{{ $row['name'] }}</p>
                                            <p class="text-[11px] text-slate-500">
                                                @if ($row['memo_no'] !== '')
                                                    Memo {{ $row['memo_no'] }} ·
                                                @endif
                                                #{{ $row['id'] }}
                                            </p>
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">{{ $row['period'] }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex rounded-md border px-2 py-0.5 text-[11px] font-semibold {{ $positionTone[$row['position']] ?? 'border-slate-200 bg-slate-50 text-slate-700' }}">
                                                {{ $row['position_label'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">{{ $row['round_label'] }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $row['reviewer'] }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $row['updated_at'] }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <a href="{{ $row['url'] }}" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">History</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-10 text-center text-[13px] text-slate-400">
                    No auditor reports match these filters.
                </div>
            @endforelse
        </div>
    @else
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" wire:key="activity-{{ $month }}-{{ $year }}-{{ $auditorId }}-{{ $reviewerId }}-{{ $q }}">
            <ul class="divide-y divide-slate-100">
                @forelse ($log['events'] as $event)
                    <li class="px-3 py-2.5" wire:key="event-{{ $event['id'] }}">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-[12px] font-semibold text-slate-800">
                                    {{ $event['label'] }}
                                    <span class="font-normal text-slate-400">·</span>
                                    {{ $event['round_label'] }}
                                </p>
                                <p class="mt-0.5 text-[11px] text-slate-500">
                                    {{ $event['report_name'] }}
                                    <span class="text-slate-300">·</span>
                                    Auditor {{ $event['auditor'] }}
                                    @if (! empty($event['position_label']))
                                        <span class="text-slate-300">·</span>
                                        {{ $event['position_label'] }}
                                    @endif
                                </p>
                                @if ($event['body'])
                                    <p class="mt-1 line-clamp-2 text-[11px] text-slate-400">{{ $event['body'] }}</p>
                                @endif
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-[11px] text-slate-500">{{ $event['at'] }}</p>
                                <p class="text-[10px] text-slate-400">{{ $event['actor'] }}</p>
                                @if ($event['url'])
                                    <a href="{{ $event['url'] }}" class="mt-1 inline-flex text-[11px] font-semibold text-sky-700 hover:underline">History</a>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-3 py-10 text-center text-[12px] text-slate-400">No review events yet.</li>
                @endforelse
            </ul>
        </section>
    @endif
</div>
