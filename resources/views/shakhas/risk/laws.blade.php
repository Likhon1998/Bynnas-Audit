<x-app-layout>
    @php
        $grouped = $laws->groupBy('group_key');
        $tones = [
            'ratios' => [
                'label' => $groupLabels['ratios'] ?? 'Ratio scoring',
                'bar' => 'bg-indigo-500',
                'head' => 'bg-white text-navy-900',
                'mark' => 'text-indigo-700',
            ],
            'flags' => [
                'label' => $groupLabels['flags'] ?? 'Yes / No factors',
                'bar' => 'bg-amber-500',
                'head' => 'bg-white text-navy-900',
                'mark' => 'text-amber-700',
            ],
            'category' => [
                'label' => $groupLabels['category'] ?? 'Final category',
                'bar' => 'bg-rose-500',
                'head' => 'bg-white text-navy-900',
                'mark' => 'text-rose-700',
            ],
        ];
        $riskPaint = function (?string $label): string {
            $text = mb_strtolower((string) $label);
            return match (true) {
                str_contains($text, 'significant') => 'text-rose-700',
                str_contains($text, 'high') => 'text-orange-700',
                str_contains($text, 'medium') => 'text-amber-700',
                str_contains($text, 'low') => 'text-emerald-700',
                default => 'text-slate-700',
            };
        };
        $groupOrder = ['ratios', 'flags', 'category'];
        $orderedGroups = collect($groupOrder)
            ->filter(fn ($key) => $grouped->has($key))
            ->merge($grouped->keys()->diff($groupOrder))
            ->values();
    @endphp

    <form method="POST" action="{{ route('shakhas.risk.laws.update') }}" class="flex h-full min-h-0 flex-col bg-slate-100/80">
        @csrf
        @method('PUT')

        <div class="shrink-0 border-b border-slate-200 bg-white px-3 py-2 lg:px-5">
            <div class="flex flex-wrap items-center gap-2">
                <div class="min-w-0">
                    <a href="{{ route('shakhas.index') }}" class="text-[11px] font-medium text-slate-500 hover:text-brand-700">All Shakha</a>
                    <h1 class="text-[15px] font-semibold leading-tight text-navy-900">Risk analysis laws</h1>
                </div>
                <div class="ml-auto flex items-center gap-1.5">
                    <button type="submit" form="reset-risk-laws" class="inline-flex h-8 items-center rounded-lg border border-amber-300 bg-amber-50 px-2.5 text-[12px] font-semibold text-amber-900 hover:bg-amber-100">Restore defaults</button>
                    <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-semibold text-white shadow-sm hover:bg-navy-800">Save laws</button>
                </div>
            </div>
            @if (session('status'))
                <div data-flash class="mt-2 rounded-lg bg-emerald-50 px-2 py-1 text-[12px] text-emerald-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mt-2 rounded-lg bg-rose-50 px-2 py-1 text-[12px] text-rose-700">{{ $errors->first() }}</div>
            @endif
        </div>

        <div class="min-h-0 flex-1 space-y-3 overflow-y-auto px-3 py-3 lg:px-5">
            @foreach ($orderedGroups as $group)
                @php
                    $rows = $grouped->get($group);
                    $tone = $tones[$group] ?? $tones['ratios'];
                @endphp
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_8px_24px_rgba(15,23,42,0.06)]">
                    <div class="h-1 {{ $tone['bar'] }}"></div>
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-3 py-2 {{ $tone['head'] }}">
                        <h2 class="text-[13px] font-semibold"><span class="mr-1.5 inline-block h-2 w-2 rounded-full {{ $tone['bar'] }}"></span>{{ $tone['label'] }}</h2>
                        <span class="rounded-full bg-white/80 px-2 py-0.5 text-[11px] font-semibold">{{ $rows->count() }}</span>
                    </div>

                    <div class="grid gap-2 p-2 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($rows as $law)
                            @php
                                $prefix = 'laws.'.$law->id;
                                $bands = old($prefix.'.bands', $law->bands);
                            @endphp
                            <article class="rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm {{ $group === 'category' ? 'sm:col-span-2 xl:col-span-3' : '' }}">
                                <input type="hidden" name="laws[{{ $law->id }}][id]" value="{{ $law->id }}">
                                <input type="hidden" name="laws[{{ $law->id }}][sort_order]" value="{{ old($prefix.'.sort_order', $law->sort_order) }}">
                                <input type="hidden" name="laws[{{ $law->id }}][description]" value="{{ old($prefix.'.description', $law->description) }}">

                                <div class="mb-2 flex items-center gap-2">
                                    <input
                                        type="text"
                                        name="laws[{{ $law->id }}][label]"
                                        value="{{ old($prefix.'.label', $law->label) }}"
                                        class="h-8 min-w-0 flex-1 rounded-md border-slate-200 bg-slate-50 text-[13px] font-semibold text-navy-900"
                                    >
                                    <label class="inline-flex shrink-0 items-center gap-1 text-[11px] font-semibold text-slate-600">
                                        <input type="checkbox" name="laws[{{ $law->id }}][is_active]" value="1" @checked(old($prefix.'.is_active', $law->is_active)) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        On
                                    </label>
                                </div>

                                @if ($law->unit === 'boolean')
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <div class="rounded-lg border border-emerald-100 bg-white px-2 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Yes</p>
                                            <input type="text" name="laws[{{ $law->id }}][true_label]" value="{{ old($prefix.'.true_label', $bands['true_label'] ?? '') }}" class="mt-1 h-7 w-full rounded-md border-slate-200 text-[12px] text-navy-900">
                                            <label class="mt-1 flex items-center justify-between text-[11px] text-slate-600">
                                                Points
                                                <input type="number" min="0" max="100" name="laws[{{ $law->id }}][true_points]" value="{{ old($prefix.'.true_points', $bands['true_points'] ?? 0) }}" class="h-7 w-14 rounded-md border-slate-200 text-center text-[13px] font-semibold text-emerald-800">
                                            </label>
                                        </div>
                                        <div class="rounded-lg border border-rose-100 bg-white px-2 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-700">No</p>
                                            <input type="text" name="laws[{{ $law->id }}][false_label]" value="{{ old($prefix.'.false_label', $bands['false_label'] ?? '') }}" class="mt-1 h-7 w-full rounded-md border-slate-200 text-[12px] text-navy-900">
                                            <label class="mt-1 flex items-center justify-between text-[11px] text-slate-600">
                                                Points
                                                <input type="number" min="0" max="100" name="laws[{{ $law->id }}][false_points]" value="{{ old($prefix.'.false_points', $bands['false_points'] ?? 0) }}" class="h-7 w-14 rounded-md border-slate-200 text-center text-[13px] font-semibold text-rose-800">
                                            </label>
                                        </div>
                                    </div>
                                @else
                                    <div class="space-y-1">
                                        @foreach ($bands as $i => $band)
                                            @php $paint = $law->unit === 'category' ? $riskPaint($band['label'] ?? '') : 'text-navy-900'; @endphp
                                            <div class="flex items-center gap-1.5 rounded-lg border border-slate-100 bg-white px-1.5 py-1">
                                                <select name="laws[{{ $law->id }}][bands][{{ $i }}][op]" class="h-7 w-[6.5rem] rounded-md border-slate-200 bg-white text-[11px]">
                                                    @if ($law->direction === 'higher_better')
                                                        <option value="gte" @selected(($band['op'] ?? '') === 'gte')>at least</option>
                                                    @else
                                                        <option value="lte" @selected(($band['op'] ?? '') === 'lte')>at most</option>
                                                    @endif
                                                    <option value="default" @selected(($band['op'] ?? '') === 'default')>else</option>
                                                </select>
                                                <input type="number" step="any" name="laws[{{ $law->id }}][bands][{{ $i }}][value]" value="{{ $band['value'] ?? '' }}" class="h-7 w-16 rounded-md border-slate-200 bg-white text-center text-[12px] font-semibold" placeholder="—">
                                                @if ($law->unit === 'category')
                                                    <input type="text" name="laws[{{ $law->id }}][bands][{{ $i }}][label]" value="{{ $band['label'] ?? '' }}" class="h-7 min-w-0 flex-1 rounded-md border-slate-200 bg-white px-2 text-[12px] font-semibold {{ $paint }}">
                                                @else
                                                    <span class="ml-auto text-[10px] font-semibold uppercase text-slate-400">pts</span>
                                                    <input type="number" min="0" max="100" name="laws[{{ $law->id }}][bands][{{ $i }}][points]" value="{{ $band['points'] ?? 0 }}" class="h-7 w-12 rounded-md border-slate-200 bg-white text-center text-[13px] font-semibold {{ $tone['mark'] }}">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </form>

    <form
        id="reset-risk-laws"
        method="POST"
        action="{{ route('shakhas.risk.laws.reset') }}"
        class="hidden"
        data-bynnas-confirm="This replaces every risk law with the standard scoring matrix and discards your edits."
        data-bynnas-confirm-title="Restore standard risk laws?"
        data-bynnas-confirm-ok="Restore defaults"
        data-bynnas-confirm-tone="amber"
    >
        @csrf
    </form>
</x-app-layout>
