<x-app-layout>
    <div
        class="flex h-full min-h-0 flex-col px-3 py-2 lg:px-5"
        x-data="ruleBook(@js($catalog), @js($openComposer), @js($groups->keys()->first() ?: 'all'))"
    >
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-2">
            <div class="min-w-0">
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">Rule book</h1>
                <p class="text-[11px] text-slate-500">প্রচলিত নিয়ম used in audit findings. <span x-text="matchLabel"></span></p>
            </div>
            <label class="relative ml-auto min-w-[12rem] flex-1 sm:max-w-xs">
                <span class="sr-only">Search rules</span>
                <input x-model="q" type="search" placeholder="Search rules" class="h-8 w-full rounded-lg border border-slate-200 bg-white pl-8 pr-2 text-[12px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                <svg class="pointer-events-none absolute left-2.5 top-2 h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.42 9.8l3.14 3.14a.75.75 0 1 0 1.06-1.06l-3.14-3.14A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
            </label>
            @if ($groups->count() > 1)
                <select x-model="source" class="h-8 max-w-[14rem] rounded-lg border border-slate-200 bg-white px-2 text-[12px] text-navy-900 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900 lg:hidden">
                    <option value="all">All policies</option>
                    @foreach ($groups as $name => $items)
                        <option value="{{ $name }}">{{ $name }} ({{ $items->count() }})</option>
                    @endforeach
                </select>
            @endif
            <button type="button" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800" @click="$refs.composer.showModal()">Add rules</button>
        </div>

        @if (session('status'))
            <div class="mt-2 rounded-lg bg-emerald-50 px-3 py-1.5 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-2 rounded-lg bg-rose-50 px-3 py-1.5 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="mt-2 flex min-h-0 flex-1 flex-col gap-3 lg:flex-row">
            @if ($ruleCount === 0)
                <div class="flex flex-1 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center">
                    <div>
                        <p class="text-[13px] font-semibold text-navy-900">No rules yet</p>
                        <p class="mt-1 text-[12px] text-slate-500">Add a policy, then write its rules.</p>
                    </div>
                </div>
            @else
                <aside class="hidden w-60 shrink-0 flex-col overflow-y-auto lg:flex">
                    <p class="px-1 pb-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Policies</p>
                    <div class="space-y-1.5">
                        <button type="button" @click="source = 'all'" :class="source === 'all' ? 'border-navy-900 bg-navy-900 text-white shadow-[0_6px_14px_rgba(15,23,42,0.25)]' : 'border-slate-200 bg-white text-navy-900 hover:bg-slate-50'" class="flex w-full items-center justify-between gap-2 rounded-lg border px-2.5 py-2 text-left transition">
                            <span class="min-w-0 truncate text-[12px] font-semibold">All policies</span>
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-indigo-700 px-1.5 text-[11px] font-semibold text-white" x-text="catalog.length"></span>
                        </button>
                        @foreach ($groups as $name => $items)
                            <button type="button" @click="source = @js($name)" :class="source === @js($name) ? 'border-navy-900 bg-navy-900 text-white shadow-[0_6px_14px_rgba(15,23,42,0.25)]' : 'border-slate-200 bg-white text-navy-900 hover:bg-slate-50'" class="flex w-full items-center justify-between gap-2 rounded-lg border px-2.5 py-2 text-left transition">
                                <span class="min-w-0">
                                    <span class="block truncate text-[12px] font-semibold">{{ $name }}</span>
                                    <span class="block text-[10px] font-medium uppercase tracking-wide" :class="source === @js($name) ? 'text-indigo-100' : 'text-slate-400'">Policy</span>
                                </span>
                                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md px-1.5 text-[11px] font-semibold text-white" :class="source === @js($name) ? 'bg-white/20' : 'bg-indigo-700'" x-text="policyCount(@js($name))"></span>
                            </button>
                        @endforeach
                    </div>
                </aside>
                <div class="min-h-0 min-w-0 flex-1 overflow-y-auto">
                    <div class="space-y-3 pb-4">
                        @foreach ($groups as $name => $items)
                            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white" x-show="groupVisible(@js($name))" x-cloak>
                                <header class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-3 py-2">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-700">Policy</p>
                                        <h2 class="truncate text-[13px] font-semibold text-navy-900">{{ $name }}</h2>
                                    </div>
                                    <span class="shrink-0 text-[11px] font-medium text-slate-500" x-text="policyCount(@js($name)) + ' rules'"></span>
                                </header>
                                <ol>
                                    @foreach ($items as $rule)
                                        <li class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-2.5 border-b border-slate-100 px-3 py-2 last:border-b-0" x-show="shown({{ $rule->id }})" x-cloak>
                                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-lg bg-indigo-700 px-1.5 text-[12px] font-semibold tabular-nums text-white shadow-[0_6px_14px_rgba(55,48,163,0.35)]">{{ $rule->article !== '' ? $rule->article : $rule->serial }}</span>
                                            <div class="min-w-0">
                                                <p class="text-[13px] leading-snug text-navy-900">{{ $rule->statement }}</p>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <button
                                                    type="button"
                                                    title="Edit"
                                                    aria-label="Edit"
                                                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-sky-700 text-white shadow-[0_6px_14px_rgba(3,105,161,0.35)] transition hover:-translate-y-0.5 hover:bg-sky-800 hover:shadow-[0_10px_18px_rgba(3,105,161,0.4)]"
                                                    @click="openEdit(@js([
                                                        'id' => $rule->id,
                                                        'statement' => $rule->statement,
                                                        'article' => $rule->article,
                                                        'source_name' => $rule->source_name,
                                                    ]))"
                                                >
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.63.63l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg>
                                                </button>
                                                <form method="POST" action="{{ route('rule-book.destroy', $rule) }}" data-bynnas-confirm="Remove this rule from the book?" data-bynnas-confirm-title="Remove rule?" data-bynnas-confirm-ok="Remove" data-bynnas-confirm-tone="rose">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Remove" aria-label="Remove" class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-rose-700 text-white shadow-[0_6px_14px_rgba(190,18,60,0.35)] transition hover:-translate-y-0.5 hover:bg-rose-800 hover:shadow-[0_10px_18px_rgba(190,18,60,0.4)]">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </section>
                        @endforeach
                    </div>
                    <p class="px-1 py-8 text-center text-[12px] text-slate-500" x-show="matchCount === 0" x-cloak>No rules match that search.</p>
                </div>
            @endif
        </div>

        <dialog x-ref="composer" class="w-full max-w-2xl rounded-xl border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
            <form method="POST" action="{{ route('rule-book.store') }}" class="p-4" x-data="ruleWriter(@js($formRows))">
                @csrf
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-[15px] font-semibold text-navy-900">Add rules</h2>
                        <p class="mt-0.5 text-[12px] text-slate-500">Name the policy once. Every rule you write here belongs to it.</p>
                    </div>
                    <button type="button" class="text-[12px] font-medium text-slate-500 hover:text-navy-900" onclick="this.closest('dialog').close()">Close</button>
                </div>
                <label class="mt-3 block">
                    <span class="text-[11px] font-semibold text-slate-600">Policy name</span>
                    <input type="text" name="source_name" value="{{ old('source_name') }}" placeholder="Example: Current Loan Adjustment Policy" class="mt-1 block w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-[13px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                </label>
                <div class="mt-3 max-h-[50vh] space-y-2 overflow-y-auto pr-1">
                    <template x-for="(row, index) in rows" :key="row.key">
                        <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-2.5">
                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                <span class="text-[11px] font-semibold text-slate-500" x-text="'Rule ' + (index + 1)"></span>
                                <button type="button" class="text-[11px] font-semibold text-rose-700 hover:underline" x-show="rows.length > 1" @click="remove(index)">Remove</button>
                            </div>
                            <div class="flex items-start gap-2">
                                <input :name="'rules[' + index + '][article]'" x-model="row.article" placeholder="No." class="w-14 shrink-0 rounded-lg border border-slate-200 bg-white px-2 py-2 text-center text-[12px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                                <textarea :name="'rules[' + index + '][statement]'" x-model="row.statement" rows="2" placeholder="Write the rule here" class="block w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-[13px] leading-snug text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900"></textarea>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <button type="button" class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-3 text-[12px] font-semibold text-navy-900 hover:bg-slate-50" @click="add()">Add another rule</button>
                    <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800">Save rules</button>
                </div>
            </form>
        </dialog>

        <dialog x-ref="editor" class="w-full max-w-lg rounded-xl border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
            <form method="POST" :action="edit.id ? '{{ url('/rule-book') }}/' + edit.id : '#'" class="p-4">
                @csrf
                @method('PUT')
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-[15px] font-semibold text-navy-900">Edit rule</h2>
                    <button type="button" class="text-[12px] font-medium text-slate-500 hover:text-navy-900" @click="$refs.editor.close()">Close</button>
                </div>
                <textarea name="statement" x-model="edit.statement" rows="5" required class="mt-3 block w-full rounded-lg border border-slate-200 px-2.5 py-2 text-[13px] leading-snug text-navy-900 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900"></textarea>
                <div class="mt-2 grid gap-2 sm:grid-cols-[5rem_minmax(0,1fr)]">
                    <input type="text" name="article" x-model="edit.article" placeholder="No." class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-center text-[12px] focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                    <input type="text" name="source_name" x-model="edit.source_name" placeholder="Policy name" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-[12px] focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800">Save</button>
                </div>
            </form>
        </dialog>
    </div>

    <script>
        function ruleBook(catalog, openComposer, initialSource) {
            return {
                q: '',
                source: initialSource || 'all',
                catalog: Array.isArray(catalog) ? catalog : [],
                edit: { id: '', statement: '', article: '', source_name: '' },
                init() {
                    if (openComposer) {
                        this.$nextTick(() => this.$refs.composer.showModal());
                    }
                },
                get matchCount() {
                    return this.catalog.filter((row) => this.shown(row.id)).length;
                },
                get matchLabel() {
                    const total = this.catalog.length;
                    const policies = new Set(this.catalog.map((row) => row.source)).size;
                    if (!this.q.trim() && this.source === 'all') {
                        return total + (total === 1 ? ' rule' : ' rules') + ' in ' + policies + (policies === 1 ? ' policy' : ' policies');
                    }
                    return this.matchCount + ' of ' + total;
                },
                policyCount(name) {
                    const query = this.q.trim().toLowerCase();
                    return this.catalog.filter((row) => row.source === name && (query === '' || row.haystack.includes(query))).length;
                },
                shown(id) {
                    const row = this.catalog.find((item) => item.id === id);
                    if (!row) {
                        return false;
                    }
                    if (this.source !== 'all' && row.source !== this.source) {
                        return false;
                    }
                    const query = this.q.trim().toLowerCase();
                    return query === '' || row.haystack.includes(query);
                },
                groupVisible(name) {
                    return this.catalog.some((row) => row.source === name && this.shown(row.id));
                },
                openEdit(rule) {
                    this.edit = {
                        id: rule.id,
                        statement: rule.statement || '',
                        article: rule.article || '',
                        source_name: rule.source_name || '',
                    };
                    this.$refs.editor.showModal();
                },
            };
        }

        function ruleWriter(initial) {
            const blank = () => ({ statement: '', article: '', key: Date.now() + Math.random() });
            const rows = (Array.isArray(initial) && initial.length ? initial : [blank()]).map((row) => ({
                statement: row.statement || '',
                article: row.article || '',
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
