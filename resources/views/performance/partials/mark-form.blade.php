@php
    $input = 'h-8 w-full rounded-lg border-slate-200 px-2.5 text-[13px] focus:border-pink-400 focus:ring-pink-400';
    $label = 'mb-0.5 block text-[10.5px] font-semibold uppercase tracking-wide text-slate-400';
    $fixedPerson = $fixedPerson ?? null;
@endphp
<form
    method="POST"
    action="{{ route('performance.marks.store') }}"
    class="rounded-2xl border border-pink-200 border-t-4 border-t-pink-500 bg-white px-3 py-2.5 shadow-sm"
    x-data="{
        rule: @js((string) old('performance_rule_id', '')),
        points: @js(old('points', '5')),
        rules: @js((object) $rulePoints->all()),
        get picked() { return this.rules[this.rule] ?? null; },
        fmt(n) { n = parseFloat(n) || 0; return (n > 0 ? '+' : n < 0 ? '−' : '') + Math.abs(n); },
    }"
>
    @csrf
    <p class="flex items-center gap-1.5 text-[13px] font-semibold text-navy-900">
        <svg class="h-4 w-4 text-pink-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
        Give a special mark
    </p>
    <p class="text-[11.5px] text-slate-500">Reward extra effort or record a penalty.</p>

    <div class="mt-2 space-y-2">
        @if ($fixedPerson)
            <input type="hidden" name="user_id" value="{{ $fixedPerson->id }}">
        @else
            <label class="block">
                <span class="{{ $label }}">Person</span>
                <select name="user_id" required class="{{ $input }} py-0">
                    <option value="">Choose a person</option>
                    @foreach ($people as $p)
                        <option value="{{ $p->id }}" @selected((int) $selectedUser === (int) $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <label class="block">
            <span class="{{ $label }}">Mark</span>
            <select name="performance_rule_id" x-model="rule" class="{{ $input }} py-0">
                <option value="">Custom points and reason</option>
                @if ($manualRules->isNotEmpty())
                    <optgroup label="Your rules">
                        @foreach ($manualRules as $r)
                            <option value="{{ $r->id }}">{{ $r->label }} ({{ $performance->formatPoints($r->points) }})</option>
                        @endforeach
                    </optgroup>
                @endif
                @if ($autoRules->isNotEmpty())
                    <optgroup label="Automatic rules (add one extra)">
                        @foreach ($autoRules as $r)
                            <option value="{{ $r->id }}">{{ $r->label }} ({{ $performance->formatPoints($r->points) }})</option>
                        @endforeach
                    </optgroup>
                @endif
            </select>
        </label>

        <template x-if="picked">
            <div class="rounded-lg bg-violet-50 px-2.5 py-1.5 text-[12px] text-violet-800 ring-1 ring-violet-200">
                Worth <b x-text="fmt(picked.points)"></b> points, following the rule.
                <span x-show="picked.cap">Max <span x-text="picked.cap"></span> a month.</span>
                Changing the rule later changes this mark too.
            </div>
        </template>

        <div x-show="! picked" class="space-y-2">
            <label class="block">
                <span class="{{ $label }}">Reason</span>
                <input type="text" name="label" value="{{ old('label') }}" maxlength="120" :required="! picked" placeholder="e.g. Excellent client feedback" class="{{ $input }}">
            </label>
            <div>
                <span class="{{ $label }}">Points</span>
                <div class="flex items-center gap-1.5">
                    <input type="number" name="points" x-model="points" step="0.5" min="-1000" max="1000" :required="! picked" class="{{ str_replace('w-full', 'w-20 shrink-0', $input) }} font-semibold" :class="parseFloat(points) < 0 ? 'text-rose-600' : 'text-emerald-700'">
                    <div class="flex flex-wrap gap-1">
                        @foreach ([5, 10, 20, -5, -10] as $quick)
                            <button
                                type="button"
                                @click="points = '{{ $quick }}'"
                                class="rounded-md px-1.5 py-1 text-[11px] font-bold ring-1 transition {{ $quick > 0 ? 'text-emerald-700 ring-emerald-200 hover:bg-emerald-50' : 'text-rose-600 ring-rose-200 hover:bg-rose-50' }}"
                                :class="points == '{{ $quick }}' ? '{{ $quick > 0 ? 'bg-emerald-100' : 'bg-rose-100' }}' : ''"
                            >{{ $quick > 0 ? '+'.$quick : '−'.abs($quick) }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <label class="block">
            <span class="{{ $label }}">Counts on</span>
            <input type="date" name="awarded_on" value="{{ old('awarded_on', now()->toDateString()) }}" max="{{ now()->addDay()->toDateString() }}" required class="{{ $input }}">
        </label>

        <label class="block">
            <span class="{{ $label }}">Note (optional)</span>
            <textarea name="note" rows="2" maxlength="1000" placeholder="What did they do?" class="w-full rounded-lg border-slate-200 px-2.5 py-1.5 text-[13px] focus:border-pink-400 focus:ring-pink-400">{{ old('note') }}</textarea>
        </label>

        <button type="submit" class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-lg bg-pink-600 text-[12.5px] font-semibold text-white shadow-[0_6px_14px_rgba(219,39,119,0.3)] transition hover:-translate-y-0.5 hover:bg-pink-700">
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
            Give mark
        </button>
    </div>
</form>
