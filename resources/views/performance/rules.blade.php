@php
    $pts = fn ($n) => $performance->formatPoints((float) $n);
    $btn = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3 text-[12px] font-semibold text-white transition hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-50';
    $sample = ['confirmed' => 4, 'first_time' => 3, 'perfect' => 2, 'completed' => 5, 'sent' => 6, 'started' => 5, 'maker_done' => 2, 'returned' => 2, 'visit_done' => 2, 'visit_delayed' => 0, 'email' => 25, 'checklist' => 3, 'active_day' => 22];
    $initial = $rules->mapWithKeys(fn ($r) => [$r->id => [
        'key' => $r->key,
        'points' => (float) $r->points,
        'cap' => $r->monthly_cap,
        'active' => (bool) $r->is_active,
        'sample' => $r->isManual() ? 1 : ($sample[$r->key] ?? 1),
    ]]);
    $cols = 'md:grid-cols-[minmax(0,1fr)_96px_96px_56px_78px_32px]';
    $input = 'h-8 w-full rounded-lg border-slate-200 px-2.5 text-[13px] focus:border-amber-400 focus:ring-amber-400';
@endphp
<x-app-layout>
    <div
        class="space-y-3 px-3 py-3 lg:px-5"
        x-data="{
            rules: @js((object) $initial->all()),
            subtotal(id) {
                const r = this.rules[id];
                if (! r.active) return 0;
                let n = r.sample;
                const cap = parseInt(r.cap);
                if (cap > 0) n = Math.min(n, cap);
                return Math.round(n * (parseFloat(r.points) || 0) * 100) / 100;
            },
            get total() { return Object.keys(this.rules).reduce((sum, id) => sum + this.subtotal(id), 0); },
            fmt(n) { n = Math.round(n * 100) / 100; return (n > 0 ? '+' : n < 0 ? '−' : '') + Math.abs(n); },
        }"
    >
        <x-performance-header
            title="Scoring rules"
            subtitle="Change, add or remove rules. Every leaderboard and scorecard updates straight away."
            :back="route('performance.index')"
            back-label="Back to leaderboard"
        >
            <a href="{{ route('performance.marks') }}" class="{{ $btn }} bg-pink-600 shadow-[0_6px_14px_rgba(219,39,119,0.3)] hover:bg-pink-700">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                Special marks
            </a>
            <form
                method="POST"
                action="{{ route('performance.rules.reset') }}"
                data-bynnas-confirm="Built-in rules go back to their original points, caps and names. Removed built-in rules come back. Rules you added stay as they are."
                data-bynnas-confirm-title="Reset built-in rules?"
                data-bynnas-confirm-ok="Reset to defaults"
                data-bynnas-confirm-tone="rose"
            >
                @csrf
                <button type="submit" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-600 transition hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-600">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10a6 6 0 1 0 1.8-4.3M4 4v3h3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Reset to defaults
                </button>
            </form>
            <button type="submit" form="rules-form" class="{{ $btn }} bg-emerald-600 shadow-[0_6px_14px_rgba(5,150,105,0.3)] hover:bg-emerald-700" @disabled($rules->isEmpty())>
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m4.5 10.5 3.5 3.5 7.5-8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Save rules
            </button>
        </x-performance-header>

        @if (session('status'))
            <div data-flash class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-[12px] font-medium text-emerald-800">
                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>{{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-[12px] font-medium text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="grid items-start gap-3 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0 space-y-3">
                <form id="rules-form" method="POST" action="{{ route('performance.rules.save') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @csrf
                    <div class="hidden gap-3 border-b border-slate-100 bg-slate-50/80 px-3 py-2 text-[10.5px] font-semibold uppercase tracking-wide text-slate-400 md:grid {{ $cols }}">
                        <span>Rule</span>
                        <span>Points each</span>
                        <span>Max / month</span>
                        <span class="text-center">On</span>
                        <span class="text-right">Example</span>
                        <span></span>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @forelse ($rules as $rule)
                            @php $used = (int) ($markCounts[$rule->id] ?? 0); @endphp
                            <li class="grid grid-cols-2 items-center gap-x-3 gap-y-2 px-3 py-2 {{ $cols }}" :class="rules[{{ $rule->id }}].active ? '' : 'bg-slate-50/70 opacity-60'">
                                <div class="col-span-2 flex min-w-0 items-start gap-2 md:col-span-1">
                                    <span class="mt-2.5 h-3 w-3 shrink-0 rounded {{ $rule->color() }}"></span>
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <input
                                                type="text"
                                                name="rules[{{ $rule->id }}][label]"
                                                value="{{ old('rules.'.$rule->id.'.label', $rule->label) }}"
                                                maxlength="80"
                                                required
                                                class="h-7 min-w-0 flex-1 rounded-md border-transparent bg-transparent px-1.5 text-[13px] font-semibold text-navy-900 hover:border-slate-200 focus:border-amber-400 focus:bg-white focus:ring-amber-400"
                                                aria-label="Rule name"
                                            >
                                            @if ($rule->isManual())
                                                <span class="shrink-0 rounded-full bg-pink-50 px-2 py-0.5 text-[10.5px] font-semibold text-pink-700 ring-1 ring-pink-200" title="Admin gives this from Special marks">Given by admin{{ $used ? ' · '.$used : '' }}</span>
                                            @else
                                                <span class="shrink-0 rounded-full bg-sky-50 px-2 py-0.5 text-[10.5px] font-semibold text-sky-700 ring-1 ring-sky-200" title="Counted from the activity log">Automatic</span>
                                            @endif
                                        </div>
                                        <input
                                            type="text"
                                            name="rules[{{ $rule->id }}][description]"
                                            value="{{ old('rules.'.$rule->id.'.description', $rule->description) }}"
                                            maxlength="255"
                                            placeholder="Add a short explanation"
                                            class="h-6 w-full rounded-md border-transparent bg-transparent px-1.5 text-[11.5px] text-slate-500 placeholder:text-slate-300 hover:border-slate-200 focus:border-amber-400 focus:bg-white focus:ring-amber-400"
                                            aria-label="Rule explanation"
                                        >
                                    </div>
                                </div>
                                <label class="block">
                                    <span class="mb-0.5 block text-[10.5px] font-semibold uppercase text-slate-400 md:hidden">Points each</span>
                                    <input
                                        type="number"
                                        step="0.5"
                                        min="-1000"
                                        max="1000"
                                        name="rules[{{ $rule->id }}][points]"
                                        value="{{ (float) $rule->points }}"
                                        x-model="rules[{{ $rule->id }}].points"
                                        class="{{ $input }} font-semibold"
                                        :class="parseFloat(rules[{{ $rule->id }}].points) < 0 ? 'text-rose-600' : 'text-emerald-700'"
                                        required
                                    >
                                </label>
                                <label class="block">
                                    <span class="mb-0.5 block text-[10.5px] font-semibold uppercase text-slate-400 md:hidden">Max per month</span>
                                    <input
                                        type="number"
                                        min="1"
                                        step="1"
                                        name="rules[{{ $rule->id }}][monthly_cap]"
                                        value="{{ $rule->monthly_cap }}"
                                        x-model="rules[{{ $rule->id }}].cap"
                                        placeholder="No limit"
                                        class="{{ $input }}"
                                    >
                                </label>
                                <div class="flex items-center md:justify-center">
                                    <input type="hidden" name="rules[{{ $rule->id }}][is_active]" value="0">
                                    <label class="relative inline-flex cursor-pointer items-center" title="Count this rule">
                                        <input type="checkbox" name="rules[{{ $rule->id }}][is_active]" value="1" class="peer sr-only" @checked($rule->is_active) x-model="rules[{{ $rule->id }}].active">
                                        <span class="h-5 w-9 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500"></span>
                                        <span class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-4"></span>
                                    </label>
                                </div>
                                <p class="text-right text-[12px] text-slate-500">
                                    <span class="text-[11px]" x-text="rules[{{ $rule->id }}].sample + ' ×'"></span>
                                    <b class="font-semibold" :class="subtotal({{ $rule->id }}) < 0 ? 'text-rose-600' : 'text-navy-900'" x-text="fmt(subtotal({{ $rule->id }}))"></b>
                                </p>
                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        form="delete-rule-{{ $rule->id }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                                        title="Remove this rule"
                                        aria-label="Remove {{ $rule->label }}"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h12M8 6V4h4v2m-6 0 .7 10h6.6L14 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </li>
                        @empty
                            <li class="px-3 py-8 text-center text-[12.5px] text-slate-500">No rules yet. Add one below, or reset to the defaults.</li>
                        @endforelse
                    </ul>
                    <div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                        <p class="text-[12px] text-slate-500">Example month total, using the counts in the Example column</p>
                        <p class="text-[16px] font-bold text-navy-900"><span x-text="fmt(total)"></span> <span class="text-[11px] font-semibold text-slate-400">points</span></p>
                    </div>
                </form>

                @foreach ($rules as $rule)
                    @php $used = (int) ($markCounts[$rule->id] ?? 0); @endphp
                    <form
                        id="delete-rule-{{ $rule->id }}"
                        method="POST"
                        action="{{ route('performance.rules.destroy', $rule) }}"
                        class="hidden"
                        data-bynnas-confirm="{{ $rule->isManual()
                            ? ($used ? 'The '.$used.' special '.($used === 1 ? 'mark' : 'marks').' already given with it stay on people\'s scores, worth '.$pts($rule->points).' each. ' : '').'You will not be able to give it again.'
                            : 'This activity stops counting in every leaderboard, including past months. You can bring it back with Add a rule or Reset to defaults.' }}"
                        data-bynnas-confirm-title="Remove “{{ $rule->label }}”?"
                        data-bynnas-confirm-ok="Remove rule"
                        data-bynnas-confirm-tone="rose"
                    >
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach

                <form
                    method="POST"
                    action="{{ route('performance.rules.store') }}"
                    class="rounded-2xl border border-slate-200 border-t-4 border-t-amber-400 bg-white px-3 py-2.5 shadow-sm"
                    x-data="{
                        counts: @js(old('counts', 'manual')),
                        catalog: @js((object) $freeActivities),
                        label: @js(old('label', '')),
                        description: @js(old('description', '')),
                        pick() {
                            const item = this.catalog[this.counts];
                            if (item) { this.label = item.label; this.description = item.description; }
                        },
                    }"
                >
                    @csrf
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <p class="text-[13px] font-semibold text-navy-900">Add a rule</p>
                        <p class="text-[11.5px] text-slate-500">
                            <span x-show="counts === 'manual'">You give it to people yourself, from Special marks. Example: “Client appreciation”, “Late submission”.</span>
                            <span x-show="counts !== 'manual'" x-cloak>Counted automatically from the activity log.</span>
                        </p>
                    </div>
                    <div class="mt-2 grid gap-2 sm:grid-cols-[minmax(0,240px)_minmax(0,1fr)]">
                        <label class="block">
                            <span class="mb-0.5 block text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">What counts</span>
                            <select name="counts" x-model="counts" @change="pick()" class="{{ $input }} py-0">
                                <option value="manual">Special mark given by admin</option>
                                @if (count($freeActivities))
                                    <optgroup label="Automatic activity">
                                        @foreach ($freeActivities as $key => $activity)
                                            <option value="{{ $key }}">{{ $activity['label'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-0.5 block text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Rule name</span>
                            <input type="text" name="label" x-model="label" maxlength="80" required placeholder="e.g. Client appreciation" class="{{ $input }}">
                        </label>
                    </div>
                    <div class="mt-2 grid gap-2 sm:grid-cols-[minmax(0,1fr)_90px_110px_auto] sm:items-end">
                        <label class="block">
                            <span class="mb-0.5 block text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Explanation (optional)</span>
                            <input type="text" name="description" x-model="description" maxlength="255" placeholder="When is it given? e.g. Branch or client praised the audit." class="{{ $input }}">
                        </label>
                        <label class="block">
                            <span class="mb-0.5 block text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Points</span>
                            <input type="number" name="points" value="{{ old('points', 5) }}" step="0.5" min="-1000" max="1000" required class="{{ $input }} font-semibold">
                        </label>
                        <label class="block">
                            <span class="mb-0.5 block text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Max / month</span>
                            <input type="number" name="monthly_cap" value="{{ old('monthly_cap') }}" min="1" step="1" placeholder="No limit" class="{{ $input }}">
                        </label>
                        <button type="submit" class="{{ $btn }} bg-amber-500 shadow-[0_6px_14px_rgba(245,158,11,0.3)] hover:bg-amber-600">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10 4v12M4 10h12" stroke-linecap="round"/></svg>
                            Add rule
                        </button>
                    </div>
                    <p class="mt-1.5 text-[11px] text-slate-400">Use negative points for a penalty.</p>
                </form>
            </div>

            <aside class="space-y-3 xl:sticky xl:top-[80px]">
                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <p class="text-[13px] font-semibold text-navy-900">Two kinds of rules</p>
                    <div class="mt-1.5 space-y-2 text-[12px] leading-relaxed text-slate-600">
                        <p><span class="rounded-full bg-sky-50 px-2 py-0.5 text-[10.5px] font-semibold text-sky-700 ring-1 ring-sky-200">Automatic</span> The app counts these from the activity log: reports, reviews, visits, emails and more.</p>
                        <p><span class="rounded-full bg-pink-50 px-2 py-0.5 text-[10.5px] font-semibold text-pink-700 ring-1 ring-pink-200">Given by admin</span> You give these to a person from <a href="{{ route('performance.marks') }}" class="font-semibold text-pink-700 hover:underline">Special marks</a>, e.g. for client praise, extra duty or a penalty.</p>
                    </div>
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <p class="text-[13px] font-semibold text-navy-900">How scoring works</p>
                    <ul class="mt-1.5 space-y-1.5 text-[12px] leading-relaxed text-slate-600">
                        <li class="flex gap-2"><span class="mt-[7px] h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span><span><b class="text-slate-800">Positive points</b> reward good work. <b class="text-slate-800">Negative points</b> are penalties.</span></li>
                        <li class="flex gap-2"><span class="mt-[7px] h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span><span><b class="text-slate-800">Max per month</b> stops one easy activity, like emails, from outweighing real audit work.</span></li>
                        <li class="flex gap-2"><span class="mt-[7px] h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400"></span><span><b class="text-slate-800">Switch off</b> a rule to pause it; <b class="text-slate-800">remove</b> it to delete it.</span></li>
                        <li class="flex gap-2"><span class="mt-[7px] h-1.5 w-1.5 shrink-0 rounded-full bg-sky-500"></span><span>Scores are worked out again each time, so changing a rule also changes past months.</span></li>
                    </ul>
                </section>
                <section class="rounded-2xl border border-amber-200 bg-amber-50/70 px-3 py-2.5 text-[12px] leading-relaxed text-amber-900">
                    <p class="font-semibold">Awards</p>
                    <p class="mt-1"><b>Best employee</b>: highest score. <b>Most progressive</b>: biggest gain over the previous period. <b>Quality champion</b>: most first-time and 100% confirmations. <b>Field champion</b>: most branch visits finished.</p>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
