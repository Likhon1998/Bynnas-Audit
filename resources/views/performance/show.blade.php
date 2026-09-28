@php
    $pts = fn ($n) => $performance->formatPoints((float) $n);
    $num = fn ($n) => rtrim(rtrim(number_format((float) $n, 1, '.', ''), '0'), '.');
    $initials = collect(preg_split('/\s+/u', trim((string) $person->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('') ?: '?';
    $year = $card['year'];
    $delta = round($card['total'] - $card['prev_total'], 2);
    $role = $person->roles->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::headline($n))->join(', ');
    $lines = collect($card['breakdown']['lines']);
    $maxLine = max(1, ...$lines->map(fn ($l) => abs($l['subtotal']))->values()->all() ?: [1]);
    $btn = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3 text-[12px] font-semibold text-white transition hover:-translate-y-0.5';
    $thisYear = (int) now()->year;
    $summary = [];
    if ($card['rank']) {
        $summary[] = 'Ranked #'.$card['rank'].' of '.$card['people'].' in '.$year.' with '.$num($card['total']).' points.';
    } else {
        $summary[] = 'No points recorded in '.$year.' yet.';
    }
    if ($card['times_best'] > 0) {
        $summary[] = 'Best employee in '.$card['times_best'].' '.($card['times_best'] === 1 ? 'month' : 'months').'.';
    }
    if ($card['top_three'] > 0) {
        $summary[] = 'In the top 3 for '.$card['top_three'].' '.($card['top_three'] === 1 ? 'month' : 'months').'.';
    }
    if ($card['best_month']) {
        $summary[] = 'Strongest month: '.$card['best_month']['name'].' ('.$num($card['best_month']['score']).' points).';
    }
    if ($card['prev_total'] != 0 || $card['total'] != 0) {
        $summary[] = $delta > 0
            ? 'Up '.$num($delta).' points on '.($year - 1).'.'
            : ($delta < 0 ? 'Down '.$num(abs($delta)).' points on '.($year - 1).'.' : 'Same score as '.($year - 1).'.');
    }
    $confirmed = (int) ($lines['confirmed']['count'] ?? 0);
    $firstTime = (int) ($lines['first_time']['count'] ?? 0);
    if ($confirmed > 0) {
        $summary[] = round($firstTime / $confirmed * 100).'% of confirmed reports passed without a send-back.';
    }
    $markValues = $card['marks']->map(fn ($m) => $m->rule ? (float) $m->rule->points : (float) $m->points);
    $bonusMarks = $markValues->filter(fn ($v) => $v > 0);
    $penaltyMarks = $markValues->filter(fn ($v) => $v < 0);
    if ($bonusMarks->isNotEmpty()) {
        $summary[] = 'Received '.$bonusMarks->count().' special '.($bonusMarks->count() === 1 ? 'mark' : 'marks').' of appreciation ('.$pts($bonusMarks->sum()).' points).';
    }
    if ($penaltyMarks->isNotEmpty()) {
        $summary[] = $penaltyMarks->count().' '.($penaltyMarks->count() === 1 ? 'penalty' : 'penalties').' from the admin ('.$pts($penaltyMarks->sum()).' points).';
    }
@endphp
<x-app-layout>
    <div class="space-y-3 px-3 py-3 lg:px-5">
        <x-performance-header
            :title="'Scorecard · '.$person->name.' · '.$year"
            subtitle="Month-by-month score and rank, for appreciation and promotion reviews"
            :back="route('performance.index', ['period' => 'year', 'year' => $year])"
            back-label="Back to leaderboard"
        >
            <div class="flex shrink-0 items-center gap-1.5">
                <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white text-[12px] font-semibold">
                    <a href="{{ route('performance.show', ['user' => $person->id, 'year' => $year - 1]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-l-lg text-slate-500 hover:bg-slate-50 hover:text-amber-600" aria-label="Previous year">‹</a>
                    <span class="px-2 text-navy-900">{{ $year }}</span>
                    @if ($year < $thisYear)
                        <a href="{{ route('performance.show', ['user' => $person->id, 'year' => $year + 1]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-r-lg text-slate-500 hover:bg-slate-50 hover:text-amber-600" aria-label="Next year">›</a>
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center text-slate-300">›</span>
                    @endif
                </div>
                <a href="{{ route('audit-review.log.auditor', $person) }}" class="{{ $btn }} bg-[#2b579a] shadow-[0_6px_14px_rgba(43,87,154,0.3)] hover:bg-[#1f4480]">Activity log</a>
                <button type="button" onclick="window.print()" class="{{ $btn }} bg-emerald-600 shadow-[0_6px_14px_rgba(5,150,105,0.3)] hover:bg-emerald-700">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 8V3h10v5M5 14H3V9h14v5h-2M6 12h8v5H6z" stroke-linejoin="round"/></svg>
                    Print
                </button>
            </div>
        </x-performance-header>

        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0f2147] via-[#1b3a70] to-[#2b579a] px-4 py-3.5 text-white shadow-[0_14px_30px_rgba(15,33,71,0.25)]">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-300 to-orange-500 text-[15px] font-bold text-white ring-2 ring-white/30">{{ $initials }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-[17px] font-bold leading-tight">{{ $person->name }}</p>
                        <p class="truncate text-[12px] text-sky-100/80">{{ $role ?: 'Auditor' }} · {{ $person->email }}</p>
                    </div>
                </div>
                <div class="ml-auto grid flex-1 grid-cols-3 gap-2 sm:grid-cols-6 lg:max-w-[760px]">
                    @foreach ([
                        ['Year score', $num($card['total']), 'points'],
                        ['Year rank', $card['rank'] ? '#'.$card['rank'] : '—', 'of '.$card['people']],
                        ['Best employee', $card['times_best'], $card['times_best'] === 1 ? 'month' : 'months'],
                        ['Top 3', $card['top_three'], $card['top_three'] === 1 ? 'month' : 'months'],
                        ['Best month', $card['best_month']['label'] ?? '—', $card['best_month'] ? $num($card['best_month']['score']).' pts' : 'no points'],
                        ['vs '.($year - 1), $delta == 0 ? '0' : $pts($delta), $num($card['prev_total']).' last year'],
                    ] as [$label, $value, $hint])
                        <div class="rounded-xl bg-[#0f2147]/60 px-2.5 py-2 ring-1 ring-white/10">
                            <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-sky-100/70">{{ $label }}</p>
                            <p class="mt-0.5 truncate text-[18px] font-bold leading-none">{{ $value }}</p>
                            <p class="mt-0.5 truncate text-[10.5px] text-sky-100/70">{{ $hint }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="grid items-start gap-3 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="min-w-0 space-y-3">
                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <div>
                            <p class="text-[13px] font-semibold text-navy-900">Score by month</p>
                            <p class="text-[11.5px] text-slate-500">Rank among all auditors that month. Gold months = best employee. Click a month to open its leaderboard.</p>
                        </div>
                        <p class="text-[11.5px] text-slate-500">Average <b class="text-navy-900">{{ $num($card['average']) }}</b> per active month</p>
                    </div>
                    <div class="mt-3 grid h-48 grid-cols-12 items-end gap-1.5">
                        @foreach ($card['months'] as $m => $month)
                            @php
                                $height = $month['score'] != 0 ? max(6, round(abs($month['score']) / $card['max'] * 100)) : 0;
                                $bar = match (true) {
                                    $month['score'] < 0 => 'bg-rose-400',
                                    $month['best'] => 'bg-gradient-to-t from-amber-500 to-amber-300 shadow-[0_6px_14px_rgba(245,158,11,0.35)]',
                                    $month['rank'] !== null && $month['rank'] <= 3 => 'bg-gradient-to-t from-[#1b3a70] to-[#2b579a]',
                                    default => 'bg-gradient-to-t from-sky-400 to-sky-300',
                                };
                            @endphp
                            <a
                                href="{{ route('performance.index', ['period' => 'month', 'year' => $year, 'month' => $m]) }}"
                                @class(['group flex h-full flex-col items-center justify-end gap-1', 'opacity-40 pointer-events-none' => $month['future']])
                                title="{{ $month['name'] }} {{ $year }}: {{ $num($month['score']) }} points{{ $month['rank'] ? ' · rank #'.$month['rank'] : '' }}"
                            >
                                @if ($month['rank'])
                                    <span @class([
                                        'rounded-full px-1.5 text-[10.5px] font-bold',
                                        'bg-amber-100 text-amber-700' => $month['best'],
                                        'bg-slate-100 text-slate-600' => ! $month['best'],
                                    ])>#{{ $month['rank'] }}</span>
                                @endif
                                <span class="text-[11px] font-semibold text-slate-600">{{ $month['score'] != 0 ? $num($month['score']) : '' }}</span>
                                <span class="w-full max-w-[40px] rounded-t-md transition group-hover:opacity-80 {{ $bar }}" style="height: {{ $height }}%"></span>
                                <span class="w-full border-t border-slate-200 pt-1 text-center text-[11px] font-medium text-slate-500">{{ $month['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                        <p class="text-[13px] font-semibold text-navy-900">Where the {{ $year }} points came from</p>
                        <p class="text-[11px] text-slate-500">Monthly caps are applied month by month, so "Counted" can be lower than "Times".</p>
                    </div>
                    <table class="w-full text-[12px]">
                        <thead>
                            <tr class="border-b border-slate-100 text-left text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-3 py-1.5">Rule</th>
                                <th class="px-2 py-1.5 text-right">Each</th>
                                <th class="px-2 py-1.5 text-right">Times</th>
                                <th class="px-2 py-1.5 text-right">Counted</th>
                                <th class="hidden w-[28%] px-2 py-1.5 md:table-cell"></th>
                                <th class="px-3 py-1.5 text-right">Points</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($card['rules'] as $rule)
                                @php $line = $lines[$rule->key] ?? ['count' => 0, 'counted' => 0, 'subtotal' => 0]; @endphp
                                <tr @class(['text-slate-400' => $line['count'] === 0])>
                                    <td class="px-3 py-1.5">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 shrink-0 rounded-sm {{ $rule->color() }}"></span>
                                            <span @class(['font-medium', 'text-slate-700' => $line['count'] > 0])>{{ $rule->label }}</span>
                                        </span>
                                    </td>
                                    <td class="px-2 py-1.5 text-right">{{ $pts($rule->points) }}</td>
                                    <td class="px-2 py-1.5 text-right">{{ $line['count'] }}</td>
                                    <td class="px-2 py-1.5 text-right">{{ $line['counted'] }}</td>
                                    <td class="hidden px-2 py-1.5 md:table-cell">
                                        @if ($line['subtotal'] != 0)
                                            <span class="block h-2 rounded-full {{ $line['subtotal'] < 0 ? 'bg-rose-400' : $rule->color() }}" style="width: {{ max(3, round(abs($line['subtotal']) / $maxLine * 100)) }}%"></span>
                                        @endif
                                    </td>
                                    <td @class(['px-3 py-1.5 text-right font-bold', 'text-emerald-600' => $line['subtotal'] > 0, 'text-rose-600' => $line['subtotal'] < 0])>{{ $line['subtotal'] != 0 ? $pts($line['subtotal']) : '0' }}</td>
                                </tr>
                            @endforeach
                            @php $special = $lines['special'] ?? ['count' => 0, 'subtotal' => 0]; @endphp
                            <tr @class(['text-slate-400' => $special['count'] === 0])>
                                <td class="px-3 py-1.5">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-sm {{ \App\Models\PerformanceRule::SPECIAL_COLOR }}"></span>
                                        <span @class(['font-medium', 'text-slate-700' => $special['count'] > 0])>Special marks · custom points</span>
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 text-right">varies</td>
                                <td class="px-2 py-1.5 text-right">{{ $special['count'] }}</td>
                                <td class="px-2 py-1.5 text-right">{{ $special['count'] }}</td>
                                <td class="hidden px-2 py-1.5 md:table-cell">
                                    @if ($special['subtotal'] != 0)
                                        <span class="block h-2 rounded-full {{ $special['subtotal'] < 0 ? 'bg-rose-400' : \App\Models\PerformanceRule::SPECIAL_COLOR }}" style="width: {{ max(3, round(abs($special['subtotal']) / $maxLine * 100)) }}%"></span>
                                    @endif
                                </td>
                                <td @class(['px-3 py-1.5 text-right font-bold', 'text-emerald-600' => $special['subtotal'] > 0, 'text-rose-600' => $special['subtotal'] < 0])>{{ $special['subtotal'] != 0 ? $pts($special['subtotal']) : '0' }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-200 bg-slate-50/80 text-[12.5px]">
                                <td class="px-3 py-2 font-semibold text-navy-900" colspan="4">Total for {{ $year }}</td>
                                <td class="hidden px-2 py-2 text-[11px] text-slate-500 md:table-cell">
                                    <span class="text-emerald-600">{{ $pts($card['breakdown']['plus']) }}</span>
                                    @if ($card['breakdown']['minus'] < 0)
                                        · <span class="text-rose-600">{{ $pts($card['breakdown']['minus']) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right text-[15px] font-bold text-navy-900">{{ $num($card['total']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </section>
            </div>

            <aside class="space-y-3 xl:sticky xl:top-[80px]">
                <section class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white px-3 py-2.5 shadow-sm">
                    <p class="flex items-center gap-1.5 text-[13px] font-semibold text-amber-900">
                        <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor"><path d="M7 3h10v2h3a1 1 0 011 1v2a5 5 0 01-4.6 5A5 5 0 0113 15.9V18h3v3H8v-3h3v-2.1A5 5 0 017.6 13 5 5 0 013 8V6a1 1 0 011-1h3V3zm0 4H5v1a3 3 0 002 2.8V7zm10 0v3.8A3 3 0 0019 8V7h-2z"/></svg>
                        Promotion summary
                    </p>
                    <ul class="mt-1.5 space-y-1 text-[12px] leading-relaxed text-slate-700">
                        @foreach ($summary as $sentence)
                            <li class="flex gap-1.5"><span class="mt-[7px] h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></span>{{ $sentence }}</li>
                        @endforeach
                    </ul>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm" x-data="{ giving: {{ $errors->any() ? 'true' : 'false' }} }">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[13px] font-semibold text-navy-900">Special marks <span class="font-normal text-slate-400">{{ $card['marks']->count() }}</span></p>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('performance.marks', ['user' => $person->id, 'year' => $year]) }}" class="text-[11.5px] font-semibold text-slate-500 hover:text-pink-700">All</a>
                            <button type="button" @click="giving = ! giving" class="inline-flex h-7 items-center gap-1 rounded-lg bg-pink-600 px-2.5 text-[11.5px] font-semibold text-white shadow-[0_4px_10px_rgba(219,39,119,0.3)] transition hover:-translate-y-0.5 hover:bg-pink-700">
                                <span x-text="giving ? 'Close' : '+ Give'"></span>
                            </button>
                        </div>
                    </div>
                    @if (session('status'))
                        <p data-flash class="mt-1.5 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-[11.5px] font-medium text-emerald-800 ring-1 ring-emerald-200">{{ session('status') }}</p>
                    @endif
                    @if ($errors->any())
                        <p class="mt-1.5 rounded-lg bg-rose-50 px-2.5 py-1.5 text-[11.5px] font-medium text-rose-700 ring-1 ring-rose-200">{{ $errors->first() }}</p>
                    @endif
                    <div x-show="giving" x-cloak class="mt-2">
                        @include('performance.partials.mark-form', [
                            'fixedPerson' => $person,
                            'manualRules' => $markRules->filter->isManual(),
                            'autoRules' => $markRules->reject->isManual(),
                            'rulePoints' => $markRules->mapWithKeys(fn ($r) => [$r->id => ['points' => (float) $r->points, 'label' => $r->label, 'cap' => $r->monthly_cap]]),
                        ])
                    </div>
                    <ul class="mt-1.5 divide-y divide-slate-100">
                        @forelse ($card['marks']->take(8) as $mark)
                            @php $value = $mark->rule ? (float) $mark->rule->points : (float) $mark->points; @endphp
                            <li class="flex items-start gap-2 py-1.5 text-[12px]">
                                <span class="w-12 shrink-0 text-[11px] font-semibold text-slate-500">{{ $mark->awarded_on->format('d M') }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-slate-700" title="{{ $mark->note }}">{{ $mark->label }}</p>
                                    @if ($mark->note)
                                        <p class="truncate text-[11px] text-slate-400">{{ $mark->note }}</p>
                                    @endif
                                </div>
                                <span @class(['shrink-0 font-bold', 'text-emerald-600' => $value > 0, 'text-rose-600' => $value < 0])>{{ $pts($value) }}</span>
                            </li>
                        @empty
                            <li class="py-2 text-[12px] text-slate-500">No special marks in {{ $year }}.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <p class="text-[13px] font-semibold text-navy-900">Month by month</p>
                    <ul class="mt-1.5 divide-y divide-slate-100">
                        @foreach ($card['months'] as $m => $month)
                            @continue($month['future'])
                            <li class="flex items-center gap-2 py-1 text-[12px]">
                                <span class="w-20 text-slate-600">{{ $month['name'] }}</span>
                                <span @class(['flex-1 font-semibold', 'text-navy-900' => $month['score'] > 0, 'text-rose-600' => $month['score'] < 0, 'text-slate-400' => $month['score'] == 0])>{{ $num($month['score']) }} pts</span>
                                @if ($month['best'])
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10.5px] font-bold text-amber-700 ring-1 ring-amber-200">Best employee</span>
                                @elseif ($month['rank'])
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10.5px] font-semibold text-slate-600">#{{ $month['rank'] }}</span>
                                @else
                                    <span class="text-[11px] text-slate-400">—</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
