<x-app-layout>
    @php
        $grouped = $laws->groupBy('group_key');
        $groupOrder = ['ratios', 'flags', 'category'];
        $orderedGroups = collect($groupOrder)
            ->filter(fn ($key) => $grouped->has($key))
            ->merge($grouped->keys()->diff($groupOrder))
            ->values();
        $firstGroup = $orderedGroups->first() ?? 'ratios';
        $glanceSections = $orderedGroups->map(function ($group) use ($grouped, $groupLabels) {
            $rows = $grouped->get($group);

            return [
                'id' => $group,
                'label' => $groupLabels[$group] ?? ucfirst($group),
                'count' => $rows->count(),
            ];
        })->values();
    @endphp

    <div
        class="flex h-full min-h-0 flex-col overflow-hidden px-4 py-4 lg:px-6"
        x-data="{
            section: @js($firstGroup),
            sections: @js($glanceSections),
        }"
    >
        <div class="shrink-0 border-b border-slate-200/80 pb-3">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-1.5 text-[13px] text-slate-400">
                        <a href="{{ route('shakhas.index') }}" class="hover:text-brand-600">All Shakha</a>
                        <span>/</span>
                        <span class="text-slate-600">Risk laws</span>
                    </div>
                    <h1 class="mt-1 text-lg font-semibold tracking-tight text-navy-900">Risk analysis laws</h1>
                    <p class="mt-0.5 text-[13px] text-slate-500">
                        Scoring matrix used when analysing a shakha. Change thresholds anytime — they are not fixed in code.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('shakhas.index') }}" class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Back to Shakha</a>
                    <form
                        method="POST"
                        action="{{ route('shakhas.risk.laws.reset') }}"
                        data-bynnas-confirm="This replaces every risk law with the standard scoring matrix and discards your edits."
                        data-bynnas-confirm-title="Restore standard risk laws?"
                        data-bynnas-confirm-ok="Restore defaults"
                        data-bynnas-confirm-tone="amber"
                    >
                        @csrf
                        <button type="submit" class="inline-flex h-8 items-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-[12px] font-semibold text-amber-800 hover:bg-amber-100">
                            Restore defaults
                        </button>
                    </form>
                </div>
            </div>

            @if (session('status'))
                <div class="mt-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mt-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
            @endif
        </div>

        <form method="POST" action="{{ route('shakhas.risk.laws.update') }}" class="mt-4 flex min-h-0 flex-1 flex-col gap-4 overflow-hidden lg:flex-row">
            @csrf
            @method('PUT')

            <aside class="w-full shrink-0 lg:w-[200px]">
                <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="text-[12px] font-semibold text-navy-900">At a glance</p>
                    </div>
                    <nav class="p-2" aria-label="Risk laws at a glance">
                        <template x-for="sec in sections" :key="sec.id">
                            <button
                                type="button"
                                @click="section = sec.id"
                                class="mb-1 flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-left transition"
                                :class="section === sec.id ? 'bg-brand-50 text-brand-800' : 'text-slate-700 hover:bg-slate-50'"
                            >
                                <span class="truncate text-[12px] font-semibold" x-text="sec.label"></span>
                                <span
                                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-xs font-bold"
                                    :class="section === sec.id ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'"
                                    x-text="sec.count"
                                ></span>
                            </button>
                        </template>
                    </nav>
                </div>
            </aside>

            <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto pr-1">
                    @foreach ($orderedGroups as $group)
                        @php $rows = $grouped->get($group); @endphp
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card"
                            x-show="section === @js($group)"
                            x-cloak
                        >
                            <div class="border-b border-slate-100 px-5 py-3.5">
                                <p class="text-[13px] font-semibold text-navy-900">{{ $groupLabels[$group] ?? ucfirst($group) }}</p>
                                <p class="mt-0.5 text-[13px] text-slate-500">{{ $rows->count() }} law{{ $rows->count() === 1 ? '' : 's' }}</p>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach ($rows as $law)
                                    @php
                                        $prefix = 'laws.'.$law->id;
                                        $bands = old($prefix.'.bands', $law->bands);
                                    @endphp
                                    <div class="px-5 py-4">
                                        <input type="hidden" name="laws[{{ $law->id }}][id]" value="{{ $law->id }}">

                                        <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                                            <div>
                                                <p class="text-[13px] font-semibold uppercase tracking-wide text-slate-500">{{ $law->key }}</p>
                                                @unless ($law->affects_live_score)
                                                    <p class="mt-0.5 text-[13px] text-amber-700">Export / category only — not added into the live total</p>
                                                @endunless
                                            </div>
                                            <label class="inline-flex items-center gap-2 text-[12px] font-medium text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="laws[{{ $law->id }}][is_active]"
                                                    value="1"
                                                    @checked(old($prefix.'.is_active', $law->is_active))
                                                    class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                                >
                                                Active
                                            </label>
                                        </div>

                                        <div class="grid gap-3 lg:grid-cols-12">
                                            <div class="lg:col-span-6">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Display label</label>
                                                <input
                                                    type="text"
                                                    name="laws[{{ $law->id }}][label]"
                                                    value="{{ old($prefix.'.label', $law->label) }}"
                                                    class="block w-full rounded-lg border-slate-200 text-[13px]"
                                                >
                                            </div>
                                            <div class="lg:col-span-2">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Sort</label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    max="9999"
                                                    name="laws[{{ $law->id }}][sort_order]"
                                                    value="{{ old($prefix.'.sort_order', $law->sort_order) }}"
                                                    class="block w-full rounded-lg border-slate-200 text-[13px]"
                                                >
                                            </div>
                                            <div class="lg:col-span-12">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Law note</label>
                                                <textarea
                                                    name="laws[{{ $law->id }}][description]"
                                                    rows="2"
                                                    class="block w-full rounded-lg border-slate-200 text-[13px]"
                                                    placeholder="How this factor is used in analysis"
                                                >{{ old($prefix.'.description', $law->description) }}</textarea>
                                            </div>
                                        </div>

                                        <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/80 p-3">
                                            <p class="mb-2 text-[13px] font-semibold uppercase tracking-wide text-slate-500">Scoring bands</p>

                                            @if ($law->unit === 'boolean')
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <div>
                                                        <label class="mb-1 block text-[13px] font-medium text-slate-600">When true</label>
                                                        <input type="text" name="laws[{{ $law->id }}][true_label]" value="{{ old($prefix.'.true_label', $bands['true_label'] ?? '') }}" class="mb-2 block w-full rounded-lg border-slate-200 text-[13px]">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[13px] text-slate-500">Points</span>
                                                            <input type="number" min="0" max="100" name="laws[{{ $law->id }}][true_points]" value="{{ old($prefix.'.true_points', $bands['true_points'] ?? 0) }}" class="w-24 rounded-lg border-slate-200 text-[13px]">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="mb-1 block text-[13px] font-medium text-slate-600">When false</label>
                                                        <input type="text" name="laws[{{ $law->id }}][false_label]" value="{{ old($prefix.'.false_label', $bands['false_label'] ?? '') }}" class="mb-2 block w-full rounded-lg border-slate-200 text-[13px]">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[13px] text-slate-500">Points</span>
                                                            <input type="number" min="0" max="100" name="laws[{{ $law->id }}][false_points]" value="{{ old($prefix.'.false_points', $bands['false_points'] ?? 0) }}" class="w-24 rounded-lg border-slate-200 text-[13px]">
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="space-y-2">
                                                    @foreach ($bands as $i => $band)
                                                        <div class="grid gap-2 sm:grid-cols-12 sm:items-center">
                                                            <div class="sm:col-span-3">
                                                                <select name="laws[{{ $law->id }}][bands][{{ $i }}][op]" class="block w-full rounded-lg border-slate-200 text-[12px]">
                                                                    @if ($law->direction === 'higher_better')
                                                                        <option value="gte" @selected(($band['op'] ?? '') === 'gte')>≥ (gte)</option>
                                                                    @else
                                                                        <option value="lte" @selected(($band['op'] ?? '') === 'lte')>≤ (lte)</option>
                                                                    @endif
                                                                    <option value="default" @selected(($band['op'] ?? '') === 'default')>Else / default</option>
                                                                </select>
                                                            </div>
                                                            <div class="sm:col-span-3">
                                                                <input
                                                                    type="number"
                                                                    step="any"
                                                                    name="laws[{{ $law->id }}][bands][{{ $i }}][value]"
                                                                    value="{{ $band['value'] ?? '' }}"
                                                                    placeholder="{{ $law->unit === 'percent' ? 'e.g. 95' : 'Threshold' }}"
                                                                    class="block w-full rounded-lg border-slate-200 text-[12px]"
                                                                >
                                                            </div>
                                                            @if ($law->unit === 'category')
                                                                <div class="sm:col-span-6">
                                                                    <input
                                                                        type="text"
                                                                        name="laws[{{ $law->id }}][bands][{{ $i }}][label]"
                                                                        value="{{ $band['label'] ?? '' }}"
                                                                        placeholder="Category label"
                                                                        class="block w-full rounded-lg border-slate-200 text-[12px]"
                                                                    >
                                                                </div>
                                                            @else
                                                                <div class="sm:col-span-3">
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="text-[13px] text-slate-500">Pts</span>
                                                                        <input
                                                                            type="number"
                                                                            min="0"
                                                                            max="100"
                                                                            name="laws[{{ $law->id }}][bands][{{ $i }}][points]"
                                                                            value="{{ $band['points'] ?? 0 }}"
                                                                            class="block w-full rounded-lg border-slate-200 text-[12px]"
                                                                        >
                                                                    </div>
                                                                </div>
                                                                <div class="sm:col-span-3 text-[13px] text-slate-400">
                                                                    {{ $law->unit === 'percent' ? '% of ratio' : 'raw ratio' }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <ul class="mt-3 space-y-0.5 text-[13px] text-slate-500">
                                                @foreach ($law->bandSummaries() as $line)
                                                    <li>{{ $line }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="shrink-0 border-t border-slate-100 bg-canvas pt-3">
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-brand-600 px-5 text-[13px] font-semibold text-white shadow-sm hover:bg-brand-500">
                            Save risk laws
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
