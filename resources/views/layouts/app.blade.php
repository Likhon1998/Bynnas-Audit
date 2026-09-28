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
                    open: false,
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
                        this.open = ! this.open;
                        if (this.open) {
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
                        this.open = false;
                    },
                    chooseFirst() {
                        const group = this.filtered[0];
                        if (group && group.rows[0]) {
                            this.choose(group.rows[0]);
                        }
                    },
                }));
            });

            window.bynnasSaveRule = async function (button) {
                const box = button.closest('[data-rule-pick]');
                const note = box ? box.querySelector('[data-rule-status]') : null;
                const area = box ? box.querySelector('textarea') : null;
                const statement = (area?.value || '').replace(/\s+/g, ' ').trim();
                const say = (text) => {
                    if (note) {
                        note.textContent = text;
                        note.classList.remove('hidden');
                    }
                };
                if (statement === '') {
                    say('আগে নিয়মটি লিখুন।');
                    return;
                }
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                button.disabled = true;
                try {
                    const response = await fetch(button.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ statement }),
                    });
                    if (! response.ok) {
                        say('যোগ করা যায়নি।');
                        return;
                    }
                    const saved = await response.json();
                    window.dispatchEvent(new CustomEvent('bynnas-rule-added', {
                        detail: {
                            group: saved.group,
                            article: saved.article || '',
                            statement: saved.statement || statement,
                            value: saved.value,
                        },
                    }));
                    say(saved.added ? 'নিয়ম বইয়ে যোগ হয়েছে।' : 'এই নিয়ম আগেই আছে।');
                } catch (error) {
                    say('যোগ করা যায়নি।');
                } finally {
                    button.disabled = false;
                }
            };
        </script>
        @livewireScripts
    </body>
</html>
