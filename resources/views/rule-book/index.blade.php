<x-app-layout>
    <div class="px-3 py-3 lg:px-5">
        <div class="mb-3">
            <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">Rule book</h1>
            <p class="text-[12px] text-slate-500">Write each rule yourself. Saved rules are used as প্রচলিত নিয়ম in audit findings.</p>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('rule-book.store') }}" class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm" x-data="ruleWriter(@js($formRows))">
            @csrf
            <div class="flex flex-wrap items-end justify-between gap-2">
                <label class="min-w-[16rem] flex-1">
                    <span class="text-[11px] font-semibold text-slate-600">Document name</span>
                    <input type="text" name="source_name" value="{{ old('source_name') }}" placeholder="Example: Current Loan Adjustment Policy" class="mt-1 block w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-[13px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                </label>
                <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800">Save rules</button>
            </div>

            <div class="mt-3 space-y-2">
                <template x-for="(row, index) in rows" :key="row.key">
                    <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-2.5">
                        <div class="mb-1.5 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-semibold text-slate-500" x-text="'Rule ' + (index + 1)"></span>
                            <button type="button" class="text-[11px] font-semibold text-rose-700 hover:underline" x-show="rows.length > 1" @click="remove(index)">Remove</button>
                        </div>
                        <textarea :name="'rules[' + index + '][statement]'" x-model="row.statement" rows="3" placeholder="Write the rule here" class="block w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-[13px] leading-snug text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900"></textarea>
                        <div class="mt-2 grid gap-2 sm:grid-cols-4">
                            <input :name="'rules[' + index + '][article]'" x-model="row.article" placeholder="অনুচ্ছেদ" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                            <input :name="'rules[' + index + '][where]'" x-model="row.where" placeholder="কোথায়" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                            <input :name="'rules[' + index + '][when]'" x-model="row.when" placeholder="কখন" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                            <input :name="'rules[' + index + '][who]'" x-model="row.who" placeholder="কে" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" class="mt-2 inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-semibold text-navy-900 hover:bg-slate-50" @click="add()">Add another rule</button>
        </form>

        <div class="mt-4">
            @if ($rules->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-8 text-center">
                    <p class="text-[13px] font-semibold text-navy-900">No saved rules yet</p>
                    <p class="mt-1 text-[12px] text-slate-500">Write a rule above and press Save rules.</p>
                </div>
            @else
                <ol class="space-y-2">
                    @foreach ($rules as $rule)
                        <li class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-700">{{ $rule->serial }}.</p>
                                    <p class="mt-1 text-[13px] leading-snug text-navy-900">{{ $rule->statement }}</p>
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        @if ($rule->article) অনুচ্ছেদ {{ $rule->article }} · @endif
                                        কোথায়: {{ $rule->reference_where ?: '—' }}
                                        · কখন: {{ $rule->reference_when ?: '—' }}
                                        · কে: {{ $rule->reference_who ?: '—' }}
                                        @if ($rule->source_name) · {{ $rule->source_name }} @endif
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('rule-book.destroy', $rule) }}" data-bynnas-confirm="Remove this rule from the book?" data-bynnas-confirm-title="Remove rule?" data-bynnas-confirm-ok="Remove" data-bynnas-confirm-tone="rose">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[11px] font-semibold text-rose-700 hover:underline">Remove</button>
                                </form>
                            </div>
                            <details class="mt-2">
                                <summary class="cursor-pointer text-[11px] font-semibold text-slate-600">Edit</summary>
                                <form method="POST" action="{{ route('rule-book.update', $rule) }}" class="mt-2 space-y-2">
                                    @csrf
                                    @method('PUT')
                                    <textarea name="statement" rows="3" required class="block w-full rounded-lg border border-slate-200 px-2.5 py-2 text-[13px] leading-snug text-navy-900 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">{{ $rule->statement }}</textarea>
                                    <div class="grid gap-2 sm:grid-cols-4">
                                        <input type="text" name="article" value="{{ $rule->article }}" placeholder="অনুচ্ছেদ" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-[12px] focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                                        <input type="text" name="where" value="{{ $rule->reference_where }}" placeholder="কোথায়" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-[12px] focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                                        <input type="text" name="when" value="{{ $rule->reference_when }}" placeholder="কখন" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-[12px] focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                                        <input type="text" name="who" value="{{ $rule->reference_who }}" placeholder="কে" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-[12px] focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                                    </div>
                                    <input type="text" name="source_name" value="{{ $rule->source_name }}" placeholder="Document name" class="block w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-[12px] focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                                    <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800">Save</button>
                                </form>
                            </details>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    <script>
        function ruleWriter(initial) {
            const blank = () => ({ statement: '', article: '', where: '', when: '', who: '', key: Date.now() + Math.random() });
            const rows = (Array.isArray(initial) && initial.length ? initial : [blank()]).map((row) => ({
                statement: row.statement || '',
                article: row.article || '',
                where: row.where || '',
                when: row.when || '',
                who: row.who || '',
                key: Date.now() + Math.random(),
            }));

            return {
                rows,
                add() { this.rows.push(blank()); },
                remove(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },
            };
        }
    </script>
</x-app-layout>
