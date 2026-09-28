@php
    $pts = fn ($n) => $performance->formatPoints((float) $n);
    $btn = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3 text-[12px] font-semibold text-white transition hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-50';
    $input = 'h-8 w-full rounded-lg border-slate-200 px-2.5 text-[13px] focus:border-pink-400 focus:ring-pink-400';
    $initials = fn ($name) => collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('') ?: '?';
    $avatarTones = ['bg-sky-100 text-sky-700', 'bg-violet-100 text-violet-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-800', 'bg-rose-100 text-rose-700', 'bg-teal-100 text-teal-700'];
    $worth = fn ($mark) => $mark->rule ? (float) $mark->rule->points : (float) $mark->points;
    $counting = fn ($mark) => ! $mark->rule || $mark->rule->is_active;
    $given = $marks->filter($counting)->map($worth);
    $manualRules = $rules->filter->isManual();
    $autoRules = $rules->reject->isManual();
    $rulePoints = $rules->mapWithKeys(fn ($r) => [$r->id => ['points' => (float) $r->points, 'label' => $r->label, 'cap' => $r->monthly_cap]]);
    $thisYear = (int) now()->year;
@endphp
<x-app-layout>
    <div class="space-y-3 px-3 py-3 lg:px-5">
        <x-performance-header
            :title="'Special marks · '.$year"
            subtitle="Extra points or penalties from the admin. They count on the date you choose."
            :back="route('performance.index')"
            back-label="Back to leaderboard"
        >
            <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white text-[12px] font-semibold">
                <a href="{{ route('performance.marks', array_filter(['year' => $year - 1, 'user' => $userId])) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-l-lg text-slate-500 hover:bg-slate-50 hover:text-pink-600" aria-label="Previous year">‹</a>
                <span class="px-2 text-navy-900">{{ $year }}</span>
                @if ($year < $thisYear)
                    <a href="{{ route('performance.marks', array_filter(['year' => $year + 1, 'user' => $userId])) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-r-lg text-slate-500 hover:bg-slate-50 hover:text-pink-600" aria-label="Next year">›</a>
                @else
                    <span class="inline-flex h-8 w-8 items-center justify-center text-slate-300">›</span>
                @endif
            </div>
            <a href="{{ route('performance.rules') }}" class="{{ $btn }} bg-violet-600 shadow-[0_6px_14px_rgba(124,58,237,0.3)] hover:bg-violet-700">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 5h8M4 10h12M4 15h6" stroke-linecap="round"/><circle cx="15" cy="5" r="1.8"/><circle cx="13" cy="15" r="1.8"/></svg>
                Scoring rules
            </a>
        </x-performance-header>

        @if (session('status'))
            <div data-flash class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-[12px] font-medium text-emerald-800">
                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>{{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-[12px] font-medium text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="grid items-start gap-3 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="min-w-0 space-y-3">
                <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                    @foreach ([
                        ['Marks given', $marks->count(), 'in '.$year, 'text-navy-900'],
                        ['Bonus points', $pts($given->filter(fn ($p) => $p > 0)->sum()), 'rewards', 'text-emerald-600'],
                        ['Penalty points', $pts($given->filter(fn ($p) => $p < 0)->sum()), 'deductions', 'text-rose-600'],
                        ['People', $marks->pluck('user_id')->unique()->count(), 'received a mark', 'text-navy-900'],
                    ] as [$label, $value, $hint, $tone])
                        <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                            <p class="mt-1 text-[20px] font-bold leading-none {{ $tone }}">{{ $value ?: '0' }}</p>
                            <p class="mt-1 text-[11px] text-slate-500">{{ $hint }}</p>
                        </div>
                    @endforeach
                </div>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <form method="GET" action="{{ route('performance.marks') }}" class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <p class="mr-auto text-[13px] font-semibold text-navy-900">Marks <span class="font-normal text-slate-400">{{ $marks->count() }}</span></p>
                        <select name="user" onchange="this.form.submit()" class="h-8 rounded-lg border-slate-200 py-0 pl-2.5 pr-8 text-[12px] focus:border-pink-400 focus:ring-pink-400" aria-label="Filter by person">
                            <option value="">Everyone</option>
                            @foreach ($people as $p)
                                <option value="{{ $p->id }}" @selected($userId === (int) $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </form>

                    <ul class="divide-y divide-slate-100">
                        @forelse ($marks as $mark)
                            @php $value = $worth($mark); $counts = $counting($mark); @endphp
                            <li @class(['flex items-start gap-3 px-3 py-2.5', 'opacity-60' => ! $counts])>
                                <div class="w-12 shrink-0 rounded-lg bg-slate-50 py-1 text-center ring-1 ring-slate-200">
                                    <p class="text-[15px] font-bold leading-none text-navy-900">{{ $mark->awarded_on->format('d') }}</p>
                                    <p class="mt-0.5 text-[10px] font-semibold uppercase text-slate-500">{{ $mark->awarded_on->format('M') }}</p>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <a href="{{ route('performance.show', ['user' => $mark->user_id, 'year' => $year]) }}" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-navy-900 hover:text-pink-700">
                                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-bold {{ $avatarTones[$mark->user_id % count($avatarTones)] }}">{{ $initials($mark->user?->name) }}</span>
                                            {{ $mark->user?->name ?? 'Removed user' }}
                                        </a>
                                        <span class="text-slate-300">·</span>
                                        <span class="text-[13px] text-slate-700">{{ $mark->label }}</span>
                                        @if ($mark->rule)
                                            <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[10.5px] font-semibold text-violet-700 ring-1 ring-violet-200">Rule</span>
                                            @unless ($counts)
                                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10.5px] font-semibold text-slate-600">Rule switched off · not counting</span>
                                            @endunless
                                        @else
                                            <span class="rounded-full bg-pink-50 px-2 py-0.5 text-[10.5px] font-semibold text-pink-700 ring-1 ring-pink-200">Custom</span>
                                        @endif
                                    </div>
                                    @if ($mark->note)
                                        <p class="mt-0.5 text-[12px] leading-relaxed text-slate-600">{{ $mark->note }}</p>
                                    @endif
                                    <p class="mt-0.5 text-[11px] text-slate-400">Given by {{ $mark->awardedByUser?->name ?? 'admin' }} · {{ $mark->created_at?->format('d M Y, h:i A') }}</p>
                                </div>
                                <span @class([
                                    'shrink-0 rounded-lg px-2.5 py-1 text-[14px] font-bold',
                                    'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' => $value > 0,
                                    'bg-rose-50 text-rose-700 ring-1 ring-rose-200' => $value < 0,
                                    'bg-slate-100 text-slate-500' => $value == 0,
                                ])>{{ $pts($value) ?: '0' }}</span>
                                <form
                                    method="POST"
                                    action="{{ route('performance.marks.destroy', $mark) }}"
                                    data-bynnas-confirm="{{ $pts($value) }} points for “{{ $mark->label }}” will be taken off {{ $mark->user?->name }}'s score."
                                    data-bynnas-confirm-title="Remove this special mark?"
                                    data-bynnas-confirm-ok="Remove mark"
                                    data-bynnas-confirm-tone="rose"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Remove mark" aria-label="Remove mark">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h12M8 6V4h4v2m-6 0 .7 10h6.6L14 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="px-3 py-10 text-center">
                                <p class="text-[13px] font-semibold text-slate-600">No special marks in {{ $year }} yet</p>
                                <p class="mt-0.5 text-[12px] text-slate-500">Use the form to reward extra effort or record a penalty.</p>
                            </li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <aside class="xl:sticky xl:top-[80px]">
                @include('performance.partials.mark-form', [
                    'people' => $people,
                    'selectedUser' => old('user_id', $userId),
                    'manualRules' => $manualRules,
                    'autoRules' => $autoRules,
                    'rulePoints' => $rulePoints,
                ])
            </aside>
        </div>
    </div>
</x-app-layout>
