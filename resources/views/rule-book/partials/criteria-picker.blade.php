@php
    $ruleCatalog = ($ruleBookRules ?? collect())->map(fn ($rule) => [
        'group' => trim((string) $rule->source_name) !== '' ? $rule->source_name : 'No document',
        'article' => $rule->article !== '' ? (string) $rule->article : (string) $rule->serial,
        'statement' => (string) $rule->statement,
        'value' => $rule->criteriaText(),
    ])->values();
@endphp
<div class="mb-1">
    <div class="flex items-center gap-1">
        <div
            class="relative min-w-0 flex-1"
            x-data="ruleSearch"
            data-rules='@json($ruleCatalog)'
            @click.outside="open = false"
            @keydown.escape.window="open = false"
        >
            <button
                type="button"
                class="flex h-8 w-full items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 text-left text-[12px] text-slate-500 hover:border-navy-900"
                @click.stop="toggle()"
            >
                <span class="min-w-0 flex-1 truncate">প্রচলিত নিয়ম বাছাই করুন…</span>
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
            </button>
            <div
                x-show="open"
                x-cloak
                class="absolute left-0 right-0 top-full z-[80] mt-1 flex max-h-72 flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_12px_32px_rgba(15,23,42,0.16)]"
            >
                <div class="border-b border-slate-100 p-2">
                    <input
                        x-ref="find"
                        x-model="q"
                        type="search"
                        placeholder="খুঁজুন…"
                        autocomplete="off"
                        class="h-8 w-full rounded-md border border-slate-200 px-2.5 text-[12px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900"
                        @keydown.enter.prevent="chooseFirst()"
                        @click.stop
                    >
                </div>
                <div class="min-h-0 flex-1 overflow-auto py-1">
                    <template x-if="filtered.length === 0">
                        <p class="px-3 py-6 text-center text-[12px] text-slate-500">কোনো নিয়ম পাওয়া যায়নি</p>
                    </template>
                    <template x-for="group in filtered" :key="group.name">
                        <div>
                            <p class="sticky top-0 border-b border-slate-100 bg-slate-50 px-3 py-1.5 text-[11px] font-semibold text-navy-900" x-text="group.name"></p>
                            <template x-for="(rule, index) in group.rows" :key="group.name + '-' + index">
                                <button type="button" class="flex w-full items-start gap-2 px-2.5 py-1.5 text-left hover:bg-slate-50" @click.stop="choose(rule)">
                                    <span class="mt-0.5 inline-flex h-6 min-w-6 shrink-0 items-center justify-center rounded-md bg-indigo-700 px-1 text-[11px] font-semibold text-white" x-text="rule.article"></span>
                                    <span class="min-w-0 text-[12px] leading-snug text-navy-900" x-text="rule.statement"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <button
            type="button"
            title="Add to rule book"
            aria-label="Add to rule book"
            data-url="{{ route('rule-book.quick') }}"
            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-700 text-[16px] font-semibold leading-none text-white shadow-[0_6px_14px_rgba(55,48,163,0.35)] transition hover:-translate-y-0.5 hover:bg-indigo-800"
            onclick="window.bynnasSaveRule(this)"
        >+</button>
    </div>
    <p data-rule-status class="mt-1 hidden text-[11px] font-medium text-emerald-700"></p>
</div>
