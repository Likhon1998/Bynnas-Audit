<x-app-layout>
    @php
        $grouped = $laws->groupBy('category');
        $categoryOrder = ['increase', 'fund', 'percentage', 'ratio'];
        $orderedCategories = collect($categoryOrder)
            ->filter(fn ($key) => $grouped->has($key))
            ->merge($grouped->keys()->diff($categoryOrder))
            ->values();
        $firstCategory = $orderedCategories->first() ?? 'increase';
        $opLabels = [
            'add' => 'Add (+)',
            'subtract' => 'Subtract (−)',
            'divide' => 'Divide (÷)',
        ];
        $formatLabels = [
            'money' => 'Money',
            'int' => 'Whole number',
            'pct' => 'Percentage',
            'ratio' => 'Ratio',
        ];
        $glanceSections = $orderedCategories->map(function ($category) use ($grouped, $categoryLabels) {
            $rows = $grouped->get($category);

            return [
                'id' => $category,
                'label' => $categoryLabels[$category] ?? ucfirst($category),
                'count' => $rows->count(),
            ];
        })->values();
    @endphp

    <div
        class="flex h-full min-h-0 flex-col overflow-hidden px-3 py-3 lg:px-5"
        x-data="{
            section: @js($firstCategory),
            sections: @js($glanceSections),
            help: null,
        }"
    >
        {{-- Fixed top bar (does not scroll) --}}
        <div class="shrink-0 border-b border-slate-200/80 pb-3">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <div class="flex items-center gap-1.5 text-[13px] text-slate-500">
                        <a href="{{ route('kpis.index') }}" class="hover:text-brand-600">Annual Key Performance Indicator (KPI)</a>
                        <span>/</span>
                        <span class="text-slate-600">Laws</span>
                    </div>
                    <h1 class="mt-1 text-lg font-semibold tracking-tight text-navy-900">KPI laws</h1>
                    <p class="mt-0.5 text-[13px] text-slate-500">Each law is one sum, difference, or division. Press <span class="font-semibold">i</span> to see it solved with sample numbers.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('kpis.index') }}" class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Back to KPI</a>
                    <form
                        method="POST"
                        action="{{ route('kpis.laws.reset') }}"
                        data-bynnas-confirm="This replaces every KPI law with the standard formulas and discards your edits."
                        data-bynnas-confirm-title="Restore standard laws?"
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

        <form method="POST" action="{{ route('kpis.laws.update') }}" class="mt-4 flex min-h-0 flex-1 flex-col gap-2 overflow-hidden lg:flex-row">
            @csrf
            @method('PUT')

            {{-- Fixed At a glance (does not scroll) --}}
            <aside class="w-full shrink-0 lg:w-[200px]">
                <div class="rounded-xl border border-slate-100 bg-white shadow-card">
                    <div class="border-b border-slate-100 px-3 py-2.5">
                        <p class="text-[12px] font-semibold text-navy-900">At a glance</p>
                    </div>
                    <nav class="p-2" aria-label="KPI laws at a glance">
                        <template x-for="sec in sections" :key="sec.id">
                            <button
                                type="button"
                                @click="section = sec.id"
                                class="mb-1 flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-left transition"
                                :class="section === sec.id
                                    ? 'bg-brand-50 text-brand-800'
                                    : 'text-slate-700 hover:bg-slate-50'"
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

            {{-- Only this panel scrolls --}}
            <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                <div class="min-h-0 flex-1 space-y-3 overflow-y-auto pr-1">
                    @foreach ($orderedCategories as $category)
                        @php $rows = $grouped->get($category); @endphp
                        <div
                            class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card"
                            x-show="section === @js($category)"
                            x-cloak
                        >
                            <div class="border-b border-slate-100 px-3 py-2.5">
                                <p class="text-[13px] font-semibold text-navy-900">{{ $categoryLabels[$category] ?? ucfirst($category) }}</p>
                                <p class="mt-0.5 text-[13px] text-slate-500">{{ $rows->count() }} law{{ $rows->count() === 1 ? '' : 's' }}</p>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach ($rows as $law)
                                    @php $prefix = 'laws.'.$law->id; @endphp
                                    <div class="px-3 py-3">
                                        <input type="hidden" name="laws[{{ $law->id }}][id]" value="{{ $law->id }}">
                                        @php $example = $law->workedExample(); @endphp
                                        <div class="mb-2 flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="text-[14px] font-semibold text-navy-900">{{ $law->label }}</p>
                                                <p class="mt-0.5 text-[13px] text-slate-600">{{ $example['sentence'] }}</p>
                                                <p class="mt-1 text-[12px] font-medium text-slate-500">{{ $example['left_label'] }} {{ $example['symbol'] }} {{ $example['right_label'] }}</p>
                                            </div>
                                            <button
                                                type="button"
                                                @click="help = help === {{ $law->id }} ? null : {{ $law->id }}"
                                                class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-sky-200 bg-sky-50 text-[13px] font-bold text-sky-800 hover:bg-sky-100"
                                                :class="help === {{ $law->id }} ? 'ring-2 ring-sky-300' : ''"
                                                title="Show a worked example"
                                                aria-label="Show a worked example"
                                            >i</button>
                                        </div>
                                        <div
                                            x-show="help === {{ $law->id }}"
                                            x-cloak
                                            class="mb-3 rounded-lg border border-sky-100 bg-sky-50 px-3 py-2.5 text-[13px] text-sky-950"
                                        >
                                            <p class="font-semibold">Example</p>
                                            <p class="mt-1">{{ $example['left_label'] }} = <span class="font-semibold tabular-nums">{{ $example['left_value'] }}</span></p>
                                            <p>{{ $example['right_label'] }} = <span class="font-semibold tabular-nums">{{ $example['right_value'] }}</span></p>
                                            <p class="mt-1 border-t border-sky-100 pt-1 font-semibold">
                                                {{ $example['left_value'] }} {{ $example['symbol'] }} {{ $example['right_value'] }} = {{ $example['result'] }}
                                            </p>
                                            <p class="mt-1 text-[12px] text-sky-800">{{ $example['note'] }}</p>
                                        </div>
                                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-[11px] uppercase tracking-wide text-slate-400">{{ $law->key }}</p>
                                            <label class="inline-flex items-center gap-2 text-[12px] font-medium text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="laws[{{ $law->id }}][is_active]"
                                                    value="1"
                                                    @checked(old($prefix.'.is_active', $law->is_active))
                                                    class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                                >
                                                Active in calculations
                                            </label>
                                        </div>

                                        <div class="grid gap-2 lg:grid-cols-12">
                                            <div class="lg:col-span-4">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Display label</label>
                                                <input
                                                    type="text"
                                                    name="laws[{{ $law->id }}][label]"
                                                    value="{{ old($prefix.'.label', $law->label) }}"
                                                    class="block w-full rounded-lg border-slate-200 text-[13px]"
                                                >
                                            </div>
                                            <div class="lg:col-span-2">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Operation</label>
                                                <select name="laws[{{ $law->id }}][operation]" class="block w-full rounded-lg border-slate-200 text-[13px]">
                                                    @foreach ($opLabels as $value => $label)
                                                        <option value="{{ $value }}" @selected(old($prefix.'.operation', $law->operation) === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="lg:col-span-3">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Left field</label>
                                                <select name="laws[{{ $law->id }}][left_operand]" class="block w-full rounded-lg border-slate-200 text-[13px]">
                                                    @foreach ($operandOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected(old($prefix.'.left_operand', $law->left_operand) === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="lg:col-span-3">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Right field</label>
                                                <select name="laws[{{ $law->id }}][right_operand]" class="block w-full rounded-lg border-slate-200 text-[13px]">
                                                    @foreach ($operandOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected(old($prefix.'.right_operand', $law->right_operand) === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="lg:col-span-3">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Result format</label>
                                                <select name="laws[{{ $law->id }}][format]" class="block w-full rounded-lg border-slate-200 text-[13px]">
                                                    @foreach ($formatLabels as $value => $label)
                                                        <option value="{{ $value }}" @selected(old($prefix.'.format', $law->format) === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="lg:col-span-2">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Sort order</label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    max="9999"
                                                    name="laws[{{ $law->id }}][sort_order]"
                                                    value="{{ old($prefix.'.sort_order', $law->sort_order) }}"
                                                    class="block w-full rounded-lg border-slate-200 text-[13px]"
                                                >
                                            </div>
                                            <div class="lg:col-span-7">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Current formula</label>
                                                <p class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-[12px] font-medium text-navy-900">
                                                    {{ $law->formula_display }}
                                                </p>
                                            </div>
                                            <div class="lg:col-span-12">
                                                <label class="mb-1 block text-[13px] font-medium text-slate-600">Law note (optional)</label>
                                                <textarea
                                                    name="laws[{{ $law->id }}][description]"
                                                    rows="2"
                                                    class="block w-full rounded-lg border-slate-200 text-[13px]"
                                                    placeholder="Why this formula is used / when to change it"
                                                >{{ old($prefix.'.description', $law->description) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="shrink-0 border-t border-slate-100 bg-canvas pt-3">
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex h-10 items-center rounded-xl bg-brand-600 px-5 text-[13px] font-semibold text-white shadow-sm hover:bg-brand-500"
                        >
                            Save KPI laws
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
