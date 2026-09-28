<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="bynnas-timezone" content="{{ bd_zone() }}">
        <meta name="bynnas-today" content="{{ bd_today() }}">

        <title>{{ $title ?? config('app.name', 'Bynnas Audit') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/bynnas-logo.png') }}?v=3">
        <script>
            try {
                document.documentElement.classList.remove('dark');
                localStorage.removeItem('bynnasTheme');
            } catch (e) {}
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @stack('styles')
    </head>
        <body
            class="bynnas-dense font-sans text-sm font-normal leading-normal antialiased text-slate-700"
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: false,
                init() {
                    try {
                        this.sidebarCollapsed = localStorage.getItem('bynnasSidebarCollapsedV2') === '1';
                    } catch (e) {
                        this.sidebarCollapsed = false;
                    }
                },
                toggleSidebarCollapsed() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    try {
                        localStorage.setItem('bynnasSidebarCollapsedV2', this.sidebarCollapsed ? '1' : '0');
                    } catch (e) {}
                    this.$nextTick(() => {
                        window.dispatchEvent(new Event('resize'));
                    });
                },
            }"
            @keydown.window.escape="sidebarOpen = false"
        >
        <x-app-loader />
        <div class="flex h-screen overflow-hidden bg-canvas">
            @include('layouts.sidebar')

            <div class="relative flex min-w-0 flex-1 flex-col">
                <button
                    type="button"
                    class="absolute left-3 top-3 z-20 rounded-lg border border-slate-200 bg-white p-1.5 text-slate-500 shadow-sm hover:bg-slate-50 lg:hidden"
                    @click="sidebarOpen = true"
                    aria-label="Open menu"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <main class="flex min-h-0 flex-1 flex-col overflow-hidden pt-10 lg:pt-0">
                    <div class="flex h-full min-h-0 flex-1 flex-col overflow-y-auto">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <x-nice-confirm />

        @stack('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                if (! window.Alpine || window.__bynnasRuleSearch) {
                    return;
                }
                window.__bynnasRuleSearch = true;
                window.Alpine.data('ruleSearch', () => ({
                    q: '',
                    ruleMenuOpen: false,
                    rules: [],
                    init() {
                        try {
                            this.rules = JSON.parse(this.$el.dataset.rules || '[]');
                        } catch (error) {
                            this.rules = [];
                        }
                        window.addEventListener('bynnas-rule-added', (event) => {
                            const row = event.detail || {};
                            if (! row.value || this.rules.some((item) => item.value === row.value)) {
                                return;
                            }
                            this.rules.push(row);
                        });
                    },
                    get filtered() {
                        const query = this.q.trim().toLowerCase();
                        const rows = this.rules.filter((rule) => query === '' || (rule.group + ' ' + rule.article + ' ' + rule.statement).toLowerCase().includes(query));
                        const groups = [];
                        rows.forEach((rule) => {
                            let group = groups.find((item) => item.name === rule.group);
                            if (! group) {
                                group = { name: rule.group, rows: [] };
                                groups.push(group);
                            }
                            group.rows.push(rule);
                        });
                        return groups;
                    },
                    toggle() {
                        this.ruleMenuOpen = ! this.ruleMenuOpen;
                        if (this.ruleMenuOpen) {
                            this.$nextTick(() => this.$refs.find && this.$refs.find.focus());
                        }
                    },
                    choose(rule) {
                        const box = this.$root.closest('[data-rule-pick]');
                        const area = box ? box.querySelector('textarea') : null;
                        if (area) {
                            area.value = rule.value;
                            area.dispatchEvent(new Event('input', { bubbles: true }));
                            area.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        this.q = '';
                        this.ruleMenuOpen = false;
                    },
                    chooseFirst() {
                        const group = this.filtered[0];
                        if (group && group.rows[0]) {
                            this.choose(group.rows[0]);
                        }
                    },
                }));
            });

            window.bynnasSaveRule = function (button) {
                const dialog = document.getElementById('bynnas-rule-dialog');
                if (! dialog) {
                    return;
                }
                const box = button.closest('[data-rule-pick]');
                const area = box ? box.querySelector('textarea') : null;
                dialog._ruleBox = box;
                const form = dialog.querySelector('form');
                form.reset();
                form.querySelector('[name="statement"]').value = (area?.value || '').trim();
                const list = dialog.querySelector('#bynnas-rule-policies');
                list.innerHTML = '';
                let policies = [];
                try {
                    policies = JSON.parse(button.dataset.policies || '[]');
                } catch (error) {
                    policies = [];
                }
                policies.forEach((name) => {
                    const option = document.createElement('option');
                    option.value = name;
                    list.appendChild(option);
                });
                dialog.querySelector('[data-rule-error]').classList.add('hidden');
                dialog.showModal();
                form.querySelector('[name="source_name"]').focus();
            };

            document.addEventListener('submit', async (event) => {
                const form = event.target;
                if (! form.matches('#bynnas-rule-dialog form')) {
                    return;
                }
                event.preventDefault();
                const dialog = form.closest('dialog');
                const error = dialog.querySelector('[data-rule-error]');
                const submit = form.querySelector('[type="submit"]');
                const payload = {
                    source_name: form.source_name.value.trim(),
                    article: form.article.value.trim(),
                    statement: form.statement.value.replace(/\s+/g, ' ').trim(),
                };
                if (payload.statement === '') {
                    error.textContent = 'নিয়মটি লিখুন।';
                    error.classList.remove('hidden');
                    return;
                }
                submit.disabled = true;
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });
                    if (! response.ok) {
                        error.textContent = 'নিয়ম যোগ করা যায়নি। আবার চেষ্টা করুন।';
                        error.classList.remove('hidden');
                        return;
                    }
                    const saved = await response.json();
                    window.dispatchEvent(new CustomEvent('bynnas-rule-added', {
                        detail: {
                            group: saved.group,
                            article: saved.article || '',
                            statement: saved.statement || payload.statement,
                            value: saved.value,
                        },
                    }));
                    const box = dialog._ruleBox;
                    const area = box ? box.querySelector('textarea') : null;
                    if (area) {
                        area.value = saved.value;
                        area.dispatchEvent(new Event('input', { bubbles: true }));
                        area.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    const note = box ? box.querySelector('[data-rule-status]') : null;
                    if (note) {
                        note.textContent = saved.added ? 'নিয়ম বইয়ে যোগ হয়েছে।' : 'এই নিয়ম আগেই নিয়ম বইয়ে আছে।';
                        note.classList.remove('hidden');
                    }
                    dialog.close();
                } catch (err) {
                    error.textContent = 'নিয়ম যোগ করা যায়নি। আবার চেষ্টা করুন।';
                    error.classList.remove('hidden');
                } finally {
                    submit.disabled = false;
                }
            });
        </script>

        @auth
            <dialog id="bynnas-rule-dialog" class="w-full max-w-lg rounded-xl border border-slate-200 p-0 shadow-xl backdrop:bg-slate-900/40">
                <form method="POST" action="{{ route('rule-book.quick') }}" class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-[15px] font-semibold text-navy-900">নতুন নিয়ম যোগ করুন</h2>
                            <p class="mt-0.5 text-[12px] text-slate-500">নিয়মটি নিয়ম বইয়ে জমা হবে এবং এই প্রচলিত নিয়মে বসবে।</p>
                        </div>
                        <button type="button" class="inline-flex h-7 shrink-0 items-center rounded-lg bg-rose-600 px-3 text-[12px] font-semibold text-white shadow-[0_6px_14px_rgba(225,29,72,0.35)] transition hover:-translate-y-0.5 hover:bg-rose-700" onclick="this.closest('dialog').close()">বন্ধ</button>
                    </div>
                    <label class="mt-3 block">
                        <span class="text-[11px] font-semibold text-slate-600">Policy name</span>
                        <input type="text" name="source_name" list="bynnas-rule-policies" placeholder="যেমন: Current Loan Adjustment Policy" autocomplete="off" class="mt-1 block w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-[13px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                        <datalist id="bynnas-rule-policies"></datalist>
                    </label>
                    <div class="mt-2 flex items-start gap-2">
                        <label class="w-16 shrink-0">
                            <span class="text-[11px] font-semibold text-slate-600">No.</span>
                            <input type="text" name="article" placeholder="১" class="mt-1 block w-full rounded-lg border border-slate-200 px-2 py-1.5 text-center text-[13px] text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900">
                        </label>
                        <label class="min-w-0 flex-1">
                            <span class="text-[11px] font-semibold text-slate-600">Rule</span>
                            <textarea name="statement" rows="4" placeholder="নিয়মটি লিখুন" class="mt-1 block w-full rounded-lg border border-slate-200 px-2.5 py-2 text-[13px] leading-snug text-navy-900 placeholder:text-slate-400 focus:border-navy-900 focus:outline-none focus:ring-1 focus:ring-navy-900"></textarea>
                        </label>
                    </div>
                    <p data-rule-error class="mt-2 hidden text-[12px] font-medium text-rose-700"></p>
                    <div class="mt-3 flex justify-end gap-2">
                        <button type="button" class="inline-flex h-8 items-center rounded-lg bg-rose-600 px-3 text-[12px] font-semibold text-white shadow-[0_6px_14px_rgba(225,29,72,0.35)] transition hover:-translate-y-0.5 hover:bg-rose-700" onclick="this.closest('dialog').close()">বাতিল</button>
                        <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-emerald-600 px-3 text-[12px] font-semibold text-white shadow-[0_6px_14px_rgba(5,150,105,0.35)] transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:opacity-60">নিয়ম যোগ করুন</button>
                    </div>
                </form>
            </dialog>
        @endauth
        @livewireScripts
        @include('partials.flash-autohide')
    </body>
</html>
