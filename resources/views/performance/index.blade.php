@php
    $rows = $board['rows'];
    $awards = $board['awards'];
    $team = $board['team'];
    $rules = $board['rules'];
    $pts = fn ($n) => $performance->formatPoints((float) $n);
    $num = fn ($n) => rtrim(rtrim(number_format((float) $n, 1, '.', ''), '0'), '.');
    $initials = fn ($name) => collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('') ?: '?';
    $avatarTones = ['bg-sky-100 text-sky-700', 'bg-violet-100 text-violet-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-800', 'bg-rose-100 text-rose-700', 'bg-teal-100 text-teal-700'];
    $maxPlus = max(1, ...array_map(fn ($r) => $r['plus'], $rows ?: [['plus' => 0]]));
    $cardYear = $period['type'] === 'range' ? $period['to']->year : $period['year'];
    $btn = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3 text-[12px] font-semibold text-white transition hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-50';
    $count = fn (array $row, string $key) => (int) ($row['lines'][$key]['count'] ?? 0);
    $awardCards = [
        'employee' => [
            'title' => 'Best employee',
            'tone' => 'from-amber-400 via-amber-500 to-orange-500',
            'shadow' => 'shadow-[0_12px_26px_rgba(234,88,12,0.28)]',
            'text' => fn ($a) => $pts($a['value']).' pts · highest score',
            'empty' => 'Nobody has points yet.',
        ],
        'progressive' => [
            'title' => 'Most progressive',
            'tone' => 'from-emerald-500 to-teal-600',
            'shadow' => 'shadow-[0_12px_26px_rgba(13,148,136,0.28)]',
            'text' => fn ($a) => $pts($a['value']).' pts vs '.$period['prev_label'].($a['row']['delta_pct'] !== null ? ' ('.($a['row']['delta_pct'] > 0 ? '+' : '').$a['row']['delta_pct'].'%)' : ''),
            'empty' => 'Nobody improved on '.$period['prev_label'].'.',
        ],
        'quality' => [
            'title' => 'Quality champion',
            'tone' => 'from-violet-500 to-fuchsia-600',
            'shadow' => 'shadow-[0_12px_26px_rgba(147,51,234,0.28)]',
            'text' => fn ($a) => $a['value'].' first-time or 100% '.($a['value'] === 1 ? 'confirmation' : 'confirmations'),
            'empty' => 'No first-time or 100% confirmations.',
        ],
        'field' => [
            'title' => 'Field champion',
            'tone' => 'from-sky-500 to-blue-700',
            'shadow' => 'shadow-[0_12px_26px_rgba(29,78,216,0.28)]',
            'text' => fn ($a) => $a['value'].' branch '.($a['value'] === 1 ? 'visit' : 'visits').' finished',
            'empty' => 'No branch visits finished.',
        ],
    ];
    $years = range((int) now()->year + 1, (int) now()->year - 5);
@endphp
<x-app-layout>
    <div class="space-y-3 px-3 py-3 lg:px-5" x-data="{ q: '' }">
        <x-performance-header
            :title="'Performance & awards · '.$period['label']"
            :subtitle="'vs '.$period['prev_label'].' · '.$team['scored'].' of '.$team['people'].' people scored'"
        >
            <form
                method="GET"
                action="{{ route('performance.index') }}"
                class="flex flex-wrap items-center gap-1.5"
                x-data="{ period: @js($period['type']) }"
                x-ref="periodForm"
            >
                <input type="hidden" name="period" :value="period">
                <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-[12px] font-semibold">
                    @foreach (['month' => 'Month', 'year' => 'Year', 'range' => 'Custom'] as $key => $label)
                        <button
                            type="button"
                            class="rounded-md px-2.5 py-1 transition"
                            :class="period === '{{ $key }}' ? 'bg-white text-amber-700 shadow-sm ring-1 ring-amber-200' : 'text-slate-500 hover:text-slate-800'"
                            @click="period = '{{ $key }}'; if (period !== 'range') $nextTick(() => $refs.periodForm.submit())"
                        >{{ $label }}</button>
                    @endforeach
                </div>
                <select name="month" x-show="period === 'month'" @change="$refs.periodForm.submit()" class="h-8 rounded-lg border-slate-200 py-0 pl-2.5 pr-8 text-[12px] focus:border-amber-400 focus:ring-amber-400" aria-label="Month">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($period['month'] === $m)>{{ \Carbon\Carbon::create(2000, $m, 1)->format('F') }}</option>
                    @endfor
                </select>
                <select name="year" x-show="period !== 'range'" @change="$refs.periodForm.submit()" class="h-8 rounded-lg border-slate-200 py-0 pl-2.5 pr-8 text-[12px] focus:border-amber-400 focus:ring-amber-400" aria-label="Year">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @selected($period['year'] === $y)>{{ $y }}</option>
                    @endforeach
                </select>
                <template x-if="period === 'range'">
                    <div class="flex items-center gap-1.5">
                        <input type="date" name="from" value="{{ $period['from']->toDateString() }}" class="h-8 rounded-lg border-slate-200 px-2 text-[12px] focus:border-amber-400 focus:ring-amber-400" aria-label="From">
                        <span class="text-[11px] text-slate-400">to</span>
                        <input type="date" name="to" value="{{ $period['to']->toDateString() }}" class="h-8 rounded-lg border-slate-200 px-2 text-[12px] focus:border-amber-400 focus:ring-amber-400" aria-label="To">
                        <button type="submit" class="{{ $btn }} bg-amber-500 shadow-[0_6px_14px_rgba(245,158,11,0.3)] hover:bg-amber-600">Show</button>
                    </div>
                </template>
            </form>
            <div class="flex shrink-0 items-center gap-1.5">
                <a href="{{ route('performance.export', $period['query']) }}" class="{{ $btn }} bg-emerald-600 shadow-[0_6px_14px_rgba(5,150,105,0.3)] hover:bg-emerald-700">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 3v10m0 0-4-4m4 4 4-4M4 16h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Download CSV
                </a>
                <a href="{{ route('performance.marks') }}" class="{{ $btn }} bg-pink-600 shadow-[0_6px_14px_rgba(219,39,119,0.3)] hover:bg-pink-700">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                    Special marks
                </a>
                <a href="{{ route('performance.rules') }}" class="{{ $btn }} bg-violet-600 shadow-[0_6px_14px_rgba(124,58,237,0.3)] hover:bg-violet-700">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 5h8M4 10h12M4 15h6" stroke-linecap="round"/><circle cx="15" cy="5" r="1.8"/><circle cx="13" cy="15" r="1.8"/></svg>
                    Scoring rules
                </a>
            </div>
        </x-performance-header>

        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($awardCards as $key => $card)
                @php $award = $awards[$key]; @endphp
                @if ($award)
                    <a
                        href="{{ route('performance.show', ['user' => $award['row']['id'], 'year' => $cardYear]) }}"
                        class="group relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $card['tone'] }} {{ $card['shadow'] }} px-3.5 py-3 text-white transition hover:-translate-y-0.5"
                    >
                        <svg class="pointer-events-none absolute -bottom-3 -right-2 h-20 w-20 text-white/15 transition group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor"><path d="M7 3h10v2h3a1 1 0 011 1v2a5 5 0 01-4.6 5A5 5 0 0113 15.9V18h3v3H8v-3h3v-2.1A5 5 0 017.6 13 5 5 0 013 8V6a1 1 0 011-1h3V3zm0 4H5v1a3 3 0 002 2.8V7zm10 0v3.8A3 3 0 0019 8V7h-2z"/></svg>
                        <p class="relative text-[10.5px] font-bold uppercase tracking-[0.12em] text-white/80">{{ $card['title'] }}</p>
                        <div class="relative mt-2 flex items-center gap-2.5">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/20 text-[13px] font-bold ring-2 ring-white/40">{{ $initials($award['row']['name']) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-[15px] font-bold leading-tight">{{ $award['row']['name'] }}</p>
                                <p class="truncate text-[11.5px] text-white/85">{{ ($card['text'])($award) }}</p>
                            </div>
                        </div>
                    </a>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-3.5 py-3">
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.12em] text-slate-400">{{ $card['title'] }}</p>
                        <p class="mt-3 text-[12.5px] font-medium text-slate-500">{{ $card['empty'] }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="grid items-start gap-3 xl:grid-cols-[minmax(0,1fr)_320px]">
            <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                    <div class="mr-auto">
                        <p class="text-[13px] font-semibold text-navy-900">Leaderboard <span class="font-normal text-slate-400">{{ count($rows) }}</span></p>
                        <p class="text-[11px] text-slate-500">Click a person for the full-year scorecard.</p>
                    </div>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="9" r="5.5"/><path d="m13.5 13.5 3 3" stroke-linecap="round"/></svg>
                        <input type="search" x-model="q" placeholder="Find person" class="h-8 w-44 rounded-lg border-slate-200 pl-8 text-[12px] focus:border-amber-400 focus:ring-amber-400">
                    </div>
                </div>

                <div class="hidden grid-cols-[44px_minmax(0,1.4fr)_90px_minmax(0,1.6fr)_120px_16px] gap-3 border-b border-slate-100 px-3 py-1.5 text-[10.5px] font-semibold uppercase tracking-wide text-slate-400 lg:grid">
                    <span>Rank</span>
                    <span>Person</span>
                    <span class="text-right">Score</span>
                    <span>Where points came from</span>
                    <span>vs {{ $period['prev_label'] }}</span>
                    <span></span>
                </div>

                <ul class="divide-y divide-slate-100">
                    @forelse ($rows as $i => $row)
                        @php
                            $medal = match (true) {
                                $row['score'] > 0 && $row['rank'] === 1 => 'bg-gradient-to-br from-amber-300 to-amber-500 text-white shadow-[0_4px_10px_rgba(245,158,11,0.45)]',
                                $row['score'] > 0 && $row['rank'] === 2 => 'bg-gradient-to-br from-slate-200 to-slate-400 text-white shadow-[0_4px_10px_rgba(100,116,139,0.35)]',
                                $row['score'] > 0 && $row['rank'] === 3 => 'bg-gradient-to-br from-orange-300 to-orange-600 text-white shadow-[0_4px_10px_rgba(234,88,12,0.35)]',
                                default => 'bg-slate-100 text-slate-500',
                            };
                            $moved = $row['prev_rank'] !== null ? $row['prev_rank'] - $row['rank'] : null;
                        @endphp
                        <li x-show="!q || @js(mb_strtolower($row['name'].' '.$row['email'])).includes(q.toLowerCase())">
                            <a
                                href="{{ route('performance.show', ['user' => $row['id'], 'year' => $cardYear]) }}"
                                class="group grid grid-cols-[44px_minmax(0,1fr)_auto] items-center gap-3 px-3 py-2 transition hover:bg-amber-50/40 lg:grid-cols-[44px_minmax(0,1.4fr)_90px_minmax(0,1.6fr)_120px_16px]"
                            >
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-[12.5px] font-bold {{ $medal }}">{{ $row['score'] == 0 ? '–' : $row['rank'] }}</span>

                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $avatarTones[$row['id'] % count($avatarTones)] }}">{{ $initials($row['name']) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-[13px] font-semibold text-navy-900 group-hover:text-amber-700">{{ $row['name'] }}</p>
                                        <p class="truncate text-[11px] text-slate-500">{{ $row['role'] ?: $row['email'] }}</p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p @class(['text-[18px] font-bold leading-none', 'text-navy-900' => $row['score'] >= 0, 'text-rose-600' => $row['score'] < 0])>{{ $num($row['score']) }}</p>
                                    <p class="mt-0.5 text-[10.5px] text-slate-400">points</p>
                                </div>

                                <div class="col-span-3 min-w-0 lg:col-span-1">
                                    <div class="flex h-2.5 overflow-hidden rounded-full bg-slate-100" style="width: {{ max(4, round($row['plus'] / $maxPlus * 100)) }}%">
                                        @foreach ($rules as $rule)
                                            @php $line = $row['lines'][$rule->key] ?? null; @endphp
                                            @if ($line && $line['subtotal'] > 0)
                                                <span class="{{ $rule->color() }} h-full" style="width: {{ $line['subtotal'] / max(0.01, $row['plus']) * 100 }}%" title="{{ $rule->label }}: {{ $line['counted'] }} × {{ $pts($rule->points) }} = {{ $pts($line['subtotal']) }}"></span>
                                            @endif
                                        @endforeach
                                        @if (($row['lines']['special']['plus'] ?? 0) > 0)
                                            <span class="{{ \App\Models\PerformanceRule::SPECIAL_COLOR }} h-full" style="width: {{ $row['lines']['special']['plus'] / max(0.01, $row['plus']) * 100 }}%" title="Special marks: {{ $pts($row['lines']['special']['plus']) }}"></span>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-x-2.5 gap-y-0.5 text-[11px] text-slate-500">
                                        <span><b class="font-semibold text-emerald-700">{{ $count($row, 'confirmed') }}</b> confirmed</span>
                                        <span><b class="font-semibold text-sky-700">{{ $count($row, 'completed') }}</b> written</span>
                                        <span><b class="font-semibold text-orange-600">{{ $count($row, 'visit_done') }}</b> visits</span>
                                        @if ($count($row, 'returned') > 0)
                                            <span><b class="font-semibold text-rose-600">{{ $count($row, 'returned') }}</b> sent back</span>
                                        @endif
                                        @if (($row['lines']['special']['count'] ?? 0) > 0)
                                            <span class="text-pink-600">★ {{ $pts($row['lines']['special']['subtotal']) }} special</span>
                                        @endif
                                        @php
                                            $special = $row['lines']['special'] ?? ['subtotal' => 0, 'plus' => 0];
                                            $penalty = round($row['minus'] - ($special['subtotal'] - $special['plus']), 2);
                                        @endphp
                                        @if ($penalty < 0)
                                            <span class="text-rose-600">{{ $pts($penalty) }} penalty</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-span-3 flex items-center gap-2 text-[11.5px] lg:col-span-1 lg:block">
                                    @if ($row['delta'] > 0)
                                        <p class="font-semibold text-emerald-600">▲ {{ $pts($row['delta']) }}{{ $row['delta_pct'] !== null ? ' · '.$row['delta_pct'].'%' : '' }}</p>
                                    @elseif ($row['delta'] < 0)
                                        <p class="font-semibold text-rose-600">▼ {{ $pts($row['delta']) }}{{ $row['delta_pct'] !== null ? ' · '.$row['delta_pct'].'%' : '' }}</p>
                                    @else
                                        <p class="font-semibold text-slate-400">No change</p>
                                    @endif
                                    <p class="text-[11px] text-slate-400">
                                        @if ($row['score'] == 0 && $row['prev_score'] == 0)
                                            No points yet
                                        @elseif ($row['score'] == 0)
                                            Was #{{ $row['prev_rank'] }}
                                        @elseif ($moved === null)
                                            New this period
                                        @elseif ($moved > 0)
                                            Up {{ $moved }} from #{{ $row['prev_rank'] }}
                                        @elseif ($moved < 0)
                                            Down {{ abs($moved) }} from #{{ $row['prev_rank'] }}
                                        @else
                                            Held #{{ $row['rank'] }}
                                        @endif
                                    </p>
                                </div>

                                <svg class="hidden h-4 w-4 text-slate-300 transition-colors group-hover:text-amber-500 lg:block" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        </li>
                    @empty
                        <li class="px-3 py-8 text-center text-[12.5px] text-slate-500">No auditors to rank yet.</li>
                    @endforelse
                </ul>
            </section>

            <aside class="space-y-3 xl:sticky xl:top-[80px]">
                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <p class="text-[13px] font-semibold text-navy-900">Team · {{ $period['label'] }}</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-slate-50 px-2.5 py-2">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Total points</p>
                            <p class="mt-0.5 text-[18px] font-bold leading-none text-navy-900">{{ $num($team['total']) }}</p>
                            @php $teamDelta = round($team['total'] - $team['prev_total'], 2); @endphp
                            <p @class(['mt-0.5 text-[11px] font-semibold', 'text-emerald-600' => $teamDelta > 0, 'text-rose-600' => $teamDelta < 0, 'text-slate-400' => $teamDelta == 0])>
                                {{ $teamDelta == 0 ? 'Same as before' : ($teamDelta > 0 ? '▲ ' : '▼ ').$pts($teamDelta) }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-2.5 py-2">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Average</p>
                            <p class="mt-0.5 text-[18px] font-bold leading-none text-navy-900">{{ $num($team['average']) }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">per person scored</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-2.5 py-2">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Improved</p>
                            <p class="mt-0.5 text-[18px] font-bold leading-none text-emerald-600">{{ $team['improved'] }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">did better than before</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-2.5 py-2">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Scored</p>
                            <p class="mt-0.5 text-[18px] font-bold leading-none text-navy-900">{{ $team['scored'] }}<span class="text-[12px] font-semibold text-slate-400"> / {{ $team['people'] }}</span></p>
                            <p class="mt-0.5 text-[11px] text-slate-500">have points</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[13px] font-semibold text-navy-900">How points are earned</p>
                        <a href="{{ route('performance.rules') }}" class="text-[11.5px] font-semibold text-violet-600 hover:text-violet-800">Edit</a>
                    </div>
                    <ul class="mt-1.5 space-y-1">
                        @forelse ($rules as $rule)
                            <li class="flex items-center gap-2 text-[12px]">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-sm {{ $rule->color() }}"></span>
                                <span class="min-w-0 flex-1 truncate text-slate-700" title="{{ $rule->description }}">{{ $rule->label }}</span>
                                @if ($rule->isManual())
                                    <span class="text-[10.5px] font-semibold text-pink-600">by admin</span>
                                @endif
                                @if ($rule->monthly_cap)
                                    <span class="text-[10.5px] text-slate-400">max {{ $rule->monthly_cap }}/mo</span>
                                @endif
                                <span @class(['w-10 text-right font-bold', 'text-emerald-600' => $rule->points > 0, 'text-rose-600' => $rule->points < 0, 'text-slate-400' => $rule->points == 0])>{{ $pts($rule->points) }}</span>
                            </li>
                        @empty
                            <li class="text-[12px] text-slate-500">No active rules. Turn some on in Scoring rules.</li>
                        @endforelse
                        <li class="flex items-center gap-2 text-[12px]">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-sm {{ \App\Models\PerformanceRule::SPECIAL_COLOR }}"></span>
                            <a href="{{ route('performance.marks') }}" class="min-w-0 flex-1 truncate text-slate-700 hover:text-pink-700">Special marks from admin</a>
                            <span class="w-10 text-right font-bold text-pink-600">±</span>
                        </li>
                    </ul>
                    <p class="mt-2 border-t border-slate-100 pt-2 text-[11px] leading-relaxed text-slate-500">
                        Best employee has the highest score. Most progressive gained the most points compared with {{ $period['prev_label'] }}. Ties go to more confirmed reports.
                    </p>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
