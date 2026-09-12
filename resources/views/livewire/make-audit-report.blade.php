<div
    class="audit-wizard @if($step === 'wizard') flex min-h-0 flex-1 flex-col overflow-hidden @endif @if($reviewReadOnly) is-review-readonly @endif"
    style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;"
    x-data="{}"
    x-on:audit-goto-place.window="window.__auditGotoPlace && window.__auditGotoPlace($event)"
>
    <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

    @if ($step === 'select')
        <div
            class="px-3 py-3 lg:px-5"
            x-data="{
                q: '',
                open: false,
                highlight: 0,
                selectedId: @js($selectedEntityKey ?: ''),
                selectedLabel: @js($selectedShakhaLabel ?: ''),
                branches: @js($branchOptions),
                get filtered() {
                    const q = this.q.trim().toLowerCase();
                    if (!q) return this.branches;
                    return this.branches.filter((b) => {
                        const hay = (b.name + ' ' + b.code + ' ' + b.area + ' ' + b.division + ' ' + b.focal + ' ' + (b.kind_label || '')).toLowerCase();
                        return hay.includes(q);
                    });
                },
                pick(b) {
                    this.selectedId = String(b.id);
                    this.selectedLabel = b.name + (b.code && b.kind !== 'location' ? ' (' + b.code + ')' : '') + (b.area ? ' — ' + b.area : '');
                    this.q = '';
                    this.open = false;
                    this.highlight = 0;
                    $wire.selectReportEntity(String(b.id));
                },
                clear() {
                    this.q = '';
                    this.selectedId = '';
                    this.selectedLabel = '';
                    this.open = false;
                    this.highlight = 0;
                    $wire.clearShakha();
                },
                onKey(e) {
                    const list = this.filtered;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.open = true;
                        this.highlight = Math.min(this.highlight + 1, Math.max(list.length - 1, 0));
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.highlight = Math.max(this.highlight - 1, 0);
                    } else if (e.key === 'Enter' && list[this.highlight]) {
                        e.preventDefault();
                        this.pick(list[this.highlight]);
                    } else if (e.key === 'Escape') {
                        this.open = false;
                        this.q = '';
                    }
                }
            }"
        >
            @include('livewire.partials.audit-reports-dashboard')
        </div>
    @else
        {{-- Fixed toolbar — does not scroll away --}}
        <div class="z-30 shrink-0 border-b border-slate-200 bg-white px-3 py-2 lg:px-4">
            {{-- Quiet background persist — loader intentionally skipped in app.js --}}
            <span wire:poll.5s="autoSaveDraft" class="hidden" aria-hidden="true"></span>
            <span wire:poll.30s="refreshUndoWindow" class="hidden" aria-hidden="true"></span>
            @php
                $undoCount = count($undoStack);
                $undoSeconds = $this->undoSecondsRemaining();
                $undoTitle = $undoCount
                    ? 'Undo: '.($undoStack[array_key_last($undoStack)]['label'] ?? '').' · বাকি '.$this->formatUndoRemaining($undoSeconds)
                    : 'Undo ১০ মিনিট পর্যন্ত কাজ করে (Save এর পরেও)';
            @endphp
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    wire:click="backToSelect"
                    class="inline-flex h-8 shrink-0 items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-[13px] font-semibold leading-tight text-navy-900">অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</h1>
                    <p class="truncate text-[10px] leading-tight text-slate-500">
                        {{ $shakha_display_name }} · {{ $area_display_name }} · {{ $monthLabel }} {{ $report_year }}
                        @if ($autoSaveHint !== '')
                            <span class="text-emerald-700"> · {{ $autoSaveHint }}</span>
                        @endif
                    </p>
                </div>

                @if ($reviewNeedsFix)
                    <div class="order-last flex w-full flex-wrap items-center gap-2 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-[11px] text-rose-950 sm:order-none sm:max-w-xl sm:w-auto">
                        <span class="min-w-0 flex-1">
                            <span class="font-semibold">Fix &amp; resubmit</span> — edit the report on the left; reviewer comments stay on the right.
                        </span>
                        <button
                            type="button"
                            wire:click="toggleReviewComments"
                            class="inline-flex h-7 shrink-0 items-center rounded-md border border-rose-300 bg-white px-2.5 text-[11px] font-semibold text-rose-800 hover:bg-rose-100"
                        >
                            {{ $reviewCommentsOpen ? 'Hide comments' : 'Show comments' }}
                            @if (count($reviewFixComments) > 0)
                                <span class="ml-1 rounded-full bg-rose-600 px-1.5 text-[9px] font-bold text-white">{{ count($reviewFixComments) }}</span>
                            @endif
                        </button>
                    </div>
                @elseif ($reviewReadOnly)
                    <div class="order-last w-full rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-[11px] font-medium text-amber-900 sm:order-none sm:w-auto">
                        Read-only — waiting for reviewer, or already confirmed.
                    </div>
                @endif

                <div class="flex shrink-0 flex-wrap items-center gap-1.5">
                    @if ($checklistUrl !== '')
                        <a
                            href="{{ $checklistUrl }}"
                            class="inline-flex h-8 items-center rounded-md border border-teal-200 bg-teal-50 px-2.5 text-[11px] font-medium text-teal-800 hover:bg-teal-100"
                            title="Optional — checklist findings can seed into this report"
                        >
                            Checklist
                            @if ($checklistRequired > 0)
                                <span class="ml-1 tabular-nums text-teal-700">{{ $checklistDone }}/{{ $checklistRequired }}</span>
                            @endif
                        </a>
                    @endif
                    <button
                        type="button"
                        wire:click="openReportSearch"
                        class="inline-flex h-8 items-center gap-1 rounded-md border border-violet-200 bg-violet-50 px-2.5 text-[12px] font-semibold text-violet-800 hover:bg-violet-100"
                        title="Search any name or word across the whole report"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
                        Search
                    </button>

                    <button
                        type="button"
                        wire:click="undoLastChange"
                        wire:loading.attr="disabled"
                        wire:target="undoLastChange"
                        @disabled($undoCount === 0)
                        title="{{ $undoTitle }}"
                        class="inline-flex h-8 items-center gap-1 rounded-md border px-2.5 text-[12px] font-medium leading-none
                            {{ $undoCount > 0
                                ? 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100'
                                : 'border-slate-200 text-slate-400' }}
                            disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a4 4 0 014 4v2M3 10l4-4M3 10l4 4"/></svg>
                        <span class="leading-none">Undo</span>
                        @if ($undoCount > 0)
                            <span class="rounded bg-white/80 px-1 text-[10px] font-semibold tabular-nums text-emerald-700">{{ $undoCount }}</span>
                            <span class="hidden text-[10px] tabular-nums text-emerald-600/80 sm:inline">{{ $this->formatUndoRemaining($undoSeconds) }}</span>
                        @endif
                    </button>

                    <button
                        type="button"
                        wire:click="autoSaveDraft"
                        class="inline-flex h-8 items-center rounded-md border border-slate-200 px-2.5 text-[12px] text-slate-600 hover:bg-slate-50"
                    >Save</button>

                    <button
                        type="button"
                        wire:click="openPreview"
                        class="inline-flex h-8 items-center rounded-md border border-[#2b579a] bg-white px-2.5 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50"
                    >Preview</button>

                    <details class="relative">
                        <summary
                            class="inline-flex h-8 cursor-pointer list-none items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50 [&::-webkit-details-marker]:hidden"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                            Download as
                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </summary>
                        <div class="absolute right-0 z-40 mt-1 w-36 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                            <button
                                type="button"
                                wire:click="downloadPdf"
                                wire:loading.attr="disabled"
                                wire:target="downloadPdf"
                                onclick="this.closest('details')?.removeAttribute('open')"
                                class="flex w-full items-center px-3 py-1.5 text-left text-[12px] font-semibold text-emerald-700 hover:bg-emerald-50 disabled:opacity-60"
                            >PDF</button>
                            <button
                                type="button"
                                wire:click="downloadDoc"
                                wire:loading.attr="disabled"
                                wire:target="downloadDoc"
                                onclick="this.closest('details')?.removeAttribute('open')"
                                class="flex w-full items-center px-3 py-1.5 text-left text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50 disabled:opacity-60"
                            >Doc</button>
                        </div>
                    </details>

                    <button
                        type="button"
                        wire:click="saveCurrentTab"
                        class="inline-flex h-8 items-center rounded-md bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]"
                    >সংরক্ষণ</button>
                </div>
            </div>
        </div>

        @if ($reportSearchOpen)
            <div class="fixed inset-0 z-50 flex items-start justify-center bg-slate-900/40 px-3 py-10 sm:px-6" wire:key="report-search-modal">
                <div class="flex max-h-[85vh] w-full max-w-xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl" @keydown.escape.window="$wire.closeReportSearch()">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3">
                        <div>
                            <p class="text-[14px] font-semibold text-navy-900">Report search</p>
                            <p class="text-[11px] text-slate-500">Count how many times a name or word appears anywhere in this report.</p>
                        </div>
                        <button type="button" wire:click="closeReportSearch" class="rounded-md px-2 py-1 text-[12px] text-slate-500 hover:bg-slate-50">✕</button>
                    </div>
                    <div class="space-y-2 border-b border-slate-100 px-4 py-3">
                        <div class="flex gap-2">
                            <input
                                type="search"
                                wire:model.live.debounce.250ms="reportSearchQ"
                                placeholder="নাম বা শব্দ লিখুন… (e.g. রফিক, VAT, সমিতি)"
                                class="h-9 flex-1 rounded-md border-slate-200 text-[13px] focus:border-violet-400 focus:ring-violet-400"
                                autofocus
                            >
                            <button type="button" wire:click="runReportSearch" class="inline-flex h-9 items-center rounded-md bg-violet-700 px-3 text-[12px] font-semibold text-white hover:bg-violet-800">Search</button>
                        </div>
                        <label class="inline-flex items-center gap-1.5 text-[11px] text-slate-600">
                            <input type="checkbox" wire:model.live="reportSearchWholeWord" class="rounded border-slate-300 text-violet-700 focus:ring-violet-500">
                            Whole word only
                        </label>
                        @if (trim($reportSearchQ) !== '')
                            <p class="text-[12px] text-slate-700">
                                <span class="font-bold text-violet-800">{{ $reportSearchTotal }}</span> occurrence{{ $reportSearchTotal === 1 ? '' : 's' }}
                                in <span class="font-semibold">{{ $reportSearchLocations }}</span> place{{ $reportSearchLocations === 1 ? '' : 's' }}
                                for “{{ $reportSearchQ }}”
                            </p>
                        @endif
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto px-2 py-2">
                        @if (trim($reportSearchQ) === '')
                            <p class="px-2 py-6 text-center text-[12px] text-slate-400">Type a name or word to scan the full report.</p>
                        @elseif ($reportSearchHits === [])
                            <p class="px-2 py-6 text-center text-[12px] text-slate-500">No matches found.</p>
                        @else
                            <ul class="space-y-1">
                                @foreach ($reportSearchHits as $hit)
                                    <li>
                                        <button
                                            type="button"
                                            wire:click="goToSearchHitByIndex({{ $loop->index }})"
                                            wire:key="search-hit-{{ $loop->index }}-{{ md5(($hit['tab'] ?? '').'|'.($hit['anchor'] ?? '').'|'.($hit['label'] ?? '')) }}"
                                            class="flex w-full items-start gap-2 rounded-lg px-2.5 py-2 text-left hover:bg-violet-50 focus:bg-violet-50 focus:outline-none focus:ring-2 focus:ring-violet-400"
                                        >
                                            <span class="mt-0.5 inline-flex min-w-[2rem] justify-center rounded-md bg-violet-100 px-1.5 py-0.5 text-[11px] font-bold tabular-nums text-violet-900">{{ $hit['count'] }}×</span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-[12px] font-semibold text-slate-800">{{ $hit['label'] }}</span>
                                                <span class="mt-0.5 block text-[11px] leading-snug text-slate-500">{{ $hit['snippet'] }}</span>
                                            </span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div
            class="flex min-h-0 flex-1 overflow-hidden {{ $reviewNeedsFix && $reviewCommentsOpen ? 'flex-col xl:flex-row' : '' }}"
            x-data="{
                open: true,
                activeTab: @entangle('activeTab'),
                activeAnchor: @entangle('outlineActiveAnchor'),
                init() {
                    try {
                        const saved = localStorage.getItem('auditOutlineOpen');
                        if (saved === '0') this.open = false;
                        if (saved === '1') this.open = true;
                        this.$watch('open', (v) => localStorage.setItem('auditOutlineOpen', v ? '1' : '0'));
                    } catch (e) {}
                },
                isOutlineActive(tab, anchor, kind) {
                    if (this.activeTab !== tab) return false;
                    if (tab !== 'page4') return true;
                    const current = this.activeAnchor || 'audit-page4';
                    if (current === '' || current === 'audit-page4') {
                        return kind === 'fixed';
                    }
                    return anchor !== '' && anchor === current;
                },
                selectOutline(tab, anchor) {
                    this.activeTab = tab;
                    this.activeAnchor = anchor || (
                        tab === 'cover' ? 'audit-cover' :
                        tab === 'page2' ? 'audit-page2' :
                        tab === 'page3' ? 'audit-page3' : 'audit-page4'
                    );
                    $wire.goToOutlineItem(tab, anchor || '');
                }
            }"
        >
            {{-- Left: outline — collapses fully to a thin side rail --}}
            <aside
                class="z-[5] hidden h-full shrink-0 overflow-hidden border-r border-slate-200 bg-white transition-[width] duration-200 ease-out lg:flex lg:flex-col"
                :class="open ? 'w-[200px]' : 'w-8'"
                :title="open ? '' : 'শিরোনাম খুলুন'"
            >
                {{-- Expanded header --}}
                <div
                    x-show="open"
                    x-cloak
                    class="flex shrink-0 items-center justify-between gap-1 border-b border-slate-100 px-2 py-2"
                >
                    <div class="min-w-0 flex-1 px-1">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">শিরোনাম</p>
                        <p class="truncate text-[9px] text-slate-500">ক্লিক = স্ক্রল</p>
                    </div>
                    <button
                        type="button"
                        @click="open = false"
                        class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                        aria-label="সাইডবার বন্ধ"
                        title="সাইডে ভাঁজ করুন"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                </div>

                {{-- Collapsed: full-height thin rail on the side --}}
                <button
                    type="button"
                    x-show="! open"
                    x-cloak
                    @click="open = true"
                    class="flex h-full w-full flex-col items-center gap-3 bg-slate-50 py-3 text-slate-500 hover:bg-sky-50 hover:text-[#2b579a]"
                    aria-label="সাইডবার খুলুন"
                    title="শিরোনাম খুলুন"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="select-none text-[10px] font-semibold tracking-wide" style="writing-mode: vertical-rl; text-orientation: mixed;">শিরোনাম</span>
                </button>

                <nav
                    class="min-h-0 flex-1 space-y-0.5 overflow-y-auto px-1.5 py-1.5"
                    x-show="open"
                    x-cloak
                >
                    @foreach ($outlineNav ?? [] as $item)
                        @php
                            $itemTab = (string) ($item['tab'] ?? '');
                            $itemAnchor = (string) ($item['anchor'] ?? '');
                            $kind = $item['kind'] ?? '';
                            $depth = (int) ($item['depth'] ?? 0);
                        @endphp
                        <button
                            type="button"
                            @click="selectOutline(@js($itemTab), @js($itemAnchor))"
                            data-outline-nav="{{ $itemAnchor }}"
                            class="block w-full rounded-md px-2 py-1 text-left text-[11px] leading-snug transition
                                {{ $depth > 0 ? 'pl-3.5' : '' }}
                                {{ $kind === 'section' ? 'font-semibold' : '' }}"
                            :class="isOutlineActive(@js($itemTab), @js($itemAnchor), @js($kind))
                                ? 'bg-[#2b579a] text-white'
                                : 'text-slate-700 hover:bg-slate-100'"
                            title="{{ $item['label'] }}"
                        >
                            <span class="line-clamp-2">{{ $item['label'] }}</span>
                        </button>
                    @endforeach
                </nav>
            </aside>

            {{-- Main editor — this pane scrolls; toolbar + outline stay put --}}
            <div class="min-h-0 min-w-0 flex-1 overflow-y-auto">
                {{-- Mobile outline --}}
                <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-3 py-2 lg:hidden">
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">শিরোনাম</label>
                    <select
                        class="w-full rounded-md border border-slate-200 bg-white px-2 py-1.5 text-[12px] text-slate-800"
                        @change="
                            const v = $event.target.value;
                            if (!v) return;
                            const i = v.indexOf('|');
                            const tab = i >= 0 ? v.slice(0, i) : v;
                            const anchor = i >= 0 ? v.slice(i + 1) : '';
                            selectOutline(tab, anchor);
                        "
                    >
                        <option value="">যে শিরোনামে যেতে চান…</option>
                        @foreach ($outlineNav ?? [] as $item)
                            <option value="{{ $item['tab'] }}|{{ $item['anchor'] }}">{{ $item['label'] }}</option>
                        @endforeach
                    </select>
                </div>
        @if (session('status'))
            <div class="bg-emerald-50 px-4 py-2 text-[12px] text-emerald-800 lg:px-6">{{ session('status') }}</div>
        @endif

        @if ($activeTab === 'cover')
        <div id="audit-cover" class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-[12px] font-semibold text-slate-800">১. Cover Page — ইনপুট ফর্ম</p>
                <span class="text-[11px] text-slate-500">নীল ঘরগুলো পূরণ করুন · Preview দিয়ে ডাউনলোড দেখুন</span>
            </div>

            <div class="cover-form mx-auto rounded-sm bg-white shadow-lg">
                <div class="cover-inner text-[12.5px] leading-relaxed text-slate-900">
                    @include('livewire.partials.audit-cover-letterhead', [
                        'editable' => ! $reviewReadOnly,
                        'logoUrl' => $logoUrl,
                        'ratingColor' => $ratingColor,
                        'control_rating' => $control_rating,
                    ])

                    <div class="mt-4 space-y-2 {{ $reviewReadOnly ? 'pointer-events-none opacity-70' : '' }}">
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">সূত্র নাম্বার:</span>
                            <input type="text" wire:model.live.debounce.400ms="memo_no" class="inline-input min-w-[220px] flex-1" @disabled($reviewReadOnly)>
                        </p>
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">তারিখ:</span>
                            <x-audit-date-field wire:model.live.debounce.400ms="report_date" format="iso" class="inline-input" />
                        </p>
                    </div>

                    <div class="mt-5 leading-relaxed">
                        <p>বরাবর,</p>
                        <p>যুগ্ম পরিচালক (নিরীক্ষা)</p>
                        <p>দুঃস্থ স্বাস্থ্য কেন্দ্র (ডিএসকে)</p>
                        <p>প্রধান কার্যালয়, ঢাকা।</p>
                    </div>

                    <h2 class="mt-5 text-center text-[15px] font-bold underline decoration-1 underline-offset-4">অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</h2>

                    <div class="mt-4 space-y-2">
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">শাখার নাম ও নাম্বার:</span>
                            <input type="text" wire:model.live.debounce.400ms="shakha_display_name" class="inline-input min-w-[200px] flex-1">
                        </p>
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">অঞ্চলের নাম:</span>
                            <input type="text" wire:model.live.debounce.400ms="area_display_name" class="inline-input min-w-[200px] flex-1">
                        </p>
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">নিরীক্ষাকাল:</span>
                            <input type="text" wire:model.live.debounce.400ms="audit_period_label" class="inline-input min-w-[200px] flex-1">
                        </p>
                    </div>

                    <div class="mt-5 leading-[1.85]">
                        <p class="font-semibold">প্রিয় মহোদয়,</p>
                        <p class="mt-2 text-justify">
                            গত
                            <x-audit-date-field wire:model.live.debounce.400ms="audit_start_date" format="iso" class="inline-input mx-1" />
                            হতে
                            <x-audit-date-field wire:model.live.debounce.400ms="audit_end_date" format="iso" class="inline-input mx-1" />
                            পর্যন্ত মোট
                            <input type="number" min="0" wire:model.live.debounce.400ms="working_days" class="inline-input mx-1 w-16 text-center">
                            কর্ম দিবস
                            <input type="text" wire:model.live.debounce.400ms="shakha_display_name" class="inline-input mx-1 min-w-[140px]">
                            শাখা হতে
                            <input type="text" wire:model.live.debounce.400ms="period_scope" class="inline-input mx-1 min-w-[140px]">
                            সময়ের উপর অভ্যন্তরীণ নিরীক্ষা সম্পন্ন করা হয়। শাখার খসড়া প্রতিবেদন
                            <x-audit-date-field wire:model.live.debounce.400ms="draft_sent_date" format="iso" class="inline-input mx-1" />
                            ইং তারিখে প্রেরণ করা হয় এবং
                            <x-audit-date-field wire:model.live.debounce.400ms="comments_received_date" format="iso" class="inline-input mx-1" />
                            তারিখে মতামত পাওয়া যায়। এতদসংক্রান্ত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন আপনার সদয় অবগতির জন্য পেশ করা হলো।
                        </p>
                    </div>

                    <div class="mt-6">
                        <p>আপনার বিশ্বস্ত,</p>
                        <p class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">নাম:</span>
                            <input type="text" wire:model.live.debounce.400ms="auditor_name" class="inline-input min-w-[180px] flex-1">
                        </p>
                        @error('auditor_name') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        <p class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">পদবী:</span>
                            <input type="text" wire:model.live.debounce.400ms="auditor_designation" class="inline-input min-w-[180px] flex-1">
                        </p>
                    </div>

                    <div class="mt-6 text-[11.5px] leading-relaxed">
                        <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold">অনুলিপি:</p>
                            <button
                                type="button"
                                wire:click="addCopyRecipient"
                                class="rounded border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-[#2b579a] hover:bg-sky-50"
                            >+ যোগ করুন</button>
                        </div>
                        <ol class="ml-0 list-none space-y-1">
                            @foreach ($copyRecipients as $idx => $recipient)
                                <li class="flex items-center gap-1.5">
                                    <span class="w-4 shrink-0 text-right text-[11px] text-slate-400">{{ $idx + 1 }}.</span>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.400ms="copyRecipients.{{ $idx }}"
                                        class="inline-input min-w-0 flex-1 bg-sky-50/50"
                                        placeholder="পদবী / প্রাপক"
                                    >
                                    <button
                                        type="button"
                                        wire:click="removeCopyRecipient({{ $idx }})"
                                        class="shrink-0 text-[10px] font-semibold text-rose-600 hover:underline"
                                        @disabled(count($copyRecipients) <= 1)
                                    >মুছুন</button>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="mt-8 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
                        <p class="text-[11px] text-slate-500">পৃষ্ঠা ১ / Cover Page</p>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                            <button type="button" wire:click="saveCover" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ ও পরবর্তী →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @elseif ($activeTab === 'page2')
            <div id="audit-page2">
                @include('livewire.partials.audit-page2-form')
            </div>
        @elseif ($activeTab === 'page3')
            <div id="audit-page3">
                @include('livewire.partials.audit-page3-form')
            </div>
        @elseif ($activeTab === 'page4')
            <div id="audit-page4">
                @include('livewire.partials.audit-page4-form')
            </div>
        @else
            <div class="px-4 py-16 text-center text-[13px] text-slate-500">
                এই পৃষ্ঠা এখনো যোগ করা হয়নি। Cover Page শেষ করে পরের ছবি পাঠালে ট্যাব যোগ করা হবে।
            </div>
        @endif
            </div>{{-- end main editor --}}

            @if ($reviewNeedsFix && $reviewCommentsOpen)
                <aside class="z-[5] flex max-h-[42vh] w-full shrink-0 flex-col border-t border-rose-200 bg-rose-50/40 xl:max-h-none xl:w-[300px] xl:border-t-0 xl:border-l">
                    @php
                        $missingSnapshots = collect($reviewFixComments)->contains(fn ($c) => empty($c['snapshot_url']));
                    @endphp
                    @if ($missingSnapshots)
                        <div wire:poll.4s="refreshReviewFixComments" class="hidden" aria-hidden="true"></div>
                    @endif
                    <div class="flex shrink-0 items-start justify-between gap-2 border-b border-rose-100 bg-rose-50 px-3 py-2.5">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-700">What to change</p>
                            <p class="mt-0.5 text-[11px] text-rose-900/80">{{ count($reviewFixComments) }} mark(s) · edit report beside this list</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <a
                                href="{{ route('audit-review.show', $reportId) }}"
                                class="text-[10px] font-semibold text-[#2b579a] hover:underline"
                                target="_blank"
                            >Full marked view</a>
                            <button type="button" wire:click="refreshReviewFixComments" class="text-[10px] font-semibold text-rose-700 hover:underline">Refresh photos</button>
                        </div>
                    </div>
                    @if ($missingSnapshots)
                        <div class="border-b border-amber-100 bg-amber-50 px-3 py-2 text-[11px] text-amber-950">
                            Place photos are missing for older marks.
                            Open <a href="{{ route('audit-review.show', $reportId) }}" target="_blank" class="font-semibold underline">Full marked view</a> once (wait ~2s), then click <span class="font-semibold">Refresh photos</span>.
                        </div>
                    @endif
                    <div class="min-h-0 flex-1 space-y-2 overflow-y-auto p-2.5">
                        @forelse ($reviewFixComments as $i => $c)
                            <div class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 shadow-sm">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full text-[10px] font-bold text-white"
                                        style="background: {{ match ($c['color'] ?? 'yellow') {
                                            'rose' => '#e11d48',
                                            'sky' => '#0284c7',
                                            'lime' => '#65a30d',
                                            'orange' => '#ea580c',
                                            default => '#ca8a04',
                                        } }}"
                                    >{{ $i + 1 }}</span>
                                    <span class="text-[10px] text-slate-400">{{ ($c['type'] ?? '') === 'area' ? 'Area' : 'Text' }}</span>
                                </div>

                                @if (! empty($c['snapshot_url']))
                                    <div class="mb-1.5 overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                                        <img
                                            src="{{ $c['snapshot_url'] }}"
                                            alt="Marked place in report"
                                            class="max-h-40 w-full object-contain object-top"
                                            loading="lazy"
                                        >
                                    </div>
                                @elseif (($c['type'] ?? '') === 'area' && isset($c['rect_w'], $c['rect_h']))
                                    <a
                                        href="{{ route('audit-review.show', $reportId) }}#ann-{{ (int) $c['id'] }}"
                                        target="_blank"
                                        class="relative mb-1.5 block h-24 overflow-hidden rounded-md border border-dashed border-rose-300 bg-[#ececec] hover:border-rose-500"
                                    >
                                        <div class="absolute inset-2 rounded-sm bg-white shadow-sm ring-1 ring-slate-200/80"></div>
                                        <div
                                            class="absolute rounded-sm border-2 border-rose-500 bg-rose-400/20"
                                            style="left: {{ max(4, (float) $c['rect_x']) }}%; top: {{ max(8, (float) $c['rect_y'] * 0.7) }}%; width: {{ max(8, (float) $c['rect_w']) }}%; height: {{ max(10, (float) $c['rect_h'] * 0.55) }}%;"
                                        ></div>
                                        <p class="absolute bottom-1 left-2 right-2 text-[9px] font-semibold text-rose-700">Open marked view to create photo</p>
                                    </a>
                                @elseif (! empty($c['quote']))
                                    <p class="mb-1.5 rounded-md border border-amber-200 bg-amber-50 px-2 py-2 text-[11px] italic leading-snug text-slate-800">“{{ \Illuminate\Support\Str::limit($c['quote'], 180) }}”</p>
                                @endif

                                @if (! empty($c['body']))
                                    <p class="mt-1 text-[12px] font-medium leading-snug text-slate-900">{{ $c['body'] }}</p>
                                @endif
                                <p class="mt-2 text-[10px] text-slate-400">{{ $c['author'] ?? 'Reviewer' }}@if (! empty($c['created'])) · {{ $c['created'] }}@endif</p>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-rose-200 bg-white px-3 py-8 text-center text-[12px] text-slate-500">
                                No marks on the document. Follow the reviewer note above (if any), then resubmit.
                            </div>
                        @endforelse
                    </div>
                    <div class="shrink-0 border-t border-rose-100 bg-white px-3 py-2.5">
                        <form method="POST" action="{{ route('audit-review.submit', $reportId) }}" class="space-y-2">
                            @csrf
                            <input type="hidden" name="destination" value="assigned">
                            <label class="block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Resubmit note (optional)</label>
                            <textarea name="note" rows="2" class="w-full rounded-lg border-slate-200 text-[12px]" placeholder="Optional note to reviewer"></textarea>
                            <label class="inline-flex items-center gap-1.5 text-[11px] text-slate-600">
                                <input type="checkbox" name="cc_superadmin" value="1" class="rounded border-slate-300">
                                CC Superadmin
                            </label>
                            <button
                                type="submit"
                                class="inline-flex h-8 w-full items-center justify-center rounded-md bg-rose-700 text-[11px] font-semibold text-white hover:bg-rose-800"
                            >Resubmit for review</button>
                        </form>
                    </div>
                </aside>
            @endif
        </div>{{-- end outline + editor flex --}}

        @if ($showPreview)
            @include('livewire.partials.audit-document-preview-styles')

            <div
                x-data="{ open: true }"
                x-show="open"
                x-cloak
                x-transition.opacity.duration.75ms
                class="fixed inset-0 z-50 flex flex-col bg-slate-900/60"
                @click.self="open = false; $wire.closePreview()"
                @keydown.escape.window="if (open) { open = false; $wire.closePreview() }"
            >
                <div class="mx-auto w-full max-w-[236mm] shrink-0 px-3 pt-4">
                    <div class="flex items-center justify-between rounded-lg bg-white px-4 py-2.5 shadow-lg ring-1 ring-black/5">
                        <div>
                            <p class="text-[13px] font-semibold text-navy-900">Preview</p>
                            <p class="text-[11px] text-slate-500">A4 · Cover আলাদা · বাকি অংশ একসাথে বসে (ফাঁকা পৃষ্ঠা নয়)</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <details class="relative">
                                <summary class="inline-flex h-8 cursor-pointer list-none items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                                    Download as
                                    <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </summary>
                                <div class="absolute right-0 z-40 mt-1 w-36 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                    <button type="button" wire:click="downloadPdf" wire:loading.attr="disabled" wire:target="downloadPdf" onclick="this.closest('details')?.removeAttribute('open')" class="flex w-full px-3 py-1.5 text-left text-[12px] font-semibold text-emerald-700 hover:bg-emerald-50 disabled:opacity-60">PDF</button>
                                    <button type="button" wire:click="downloadDoc" wire:loading.attr="disabled" wire:target="downloadDoc" onclick="this.closest('details')?.removeAttribute('open')" class="flex w-full px-3 py-1.5 text-left text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50 disabled:opacity-60">Doc</button>
                                </div>
                            </details>
                            <button
                                type="button"
                                @click="open = false; $wire.closePreview()"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-800"
                                title="Close"
                                aria-label="Close preview"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-3 py-4" @click.self="open = false; $wire.closePreview()">
                    <div class="mx-auto w-full max-w-[236mm]">
                    <div class="audit-doc-preview rounded-sm bg-[#8d8d8d] px-4 py-6">
                        @include('livewire.partials.audit-document-preview-pages', [
                            'documentSheets' => $documentSheets,
                            'logoUrl' => $logoUrl,
                            'ratingColor' => $ratingColor,
                            'control_rating' => $control_rating,
                            'memo_no' => $memo_no,
                            'report_date' => $report_date,
                            'shakha_display_name' => $shakha_display_name,
                            'area_display_name' => $area_display_name,
                            'audit_period_label' => $audit_period_label,
                            'audit_start_date' => $audit_start_date,
                            'audit_end_date' => $audit_end_date,
                            'working_days' => $working_days,
                            'period_scope' => $period_scope,
                            'draft_sent_date' => $draft_sent_date,
                            'comments_received_date' => $comments_received_date,
                            'auditor_name' => $auditor_name,
                            'auditor_designation' => $auditor_designation,
                            'glance_as_of' => $glance_as_of,
                            'branch_opening_date' => $branch_opening_date,
                            'staff_info_as_of' => $staff_info_as_of,
                            'glanceRows' => $glanceRows,
                            'staffColumns' => $staffColumns,
                            'staffRows' => $staffRows,
                            'sign_auditor_name' => $sign_auditor_name,
                            'sign_auditor_designation' => $sign_auditor_designation,
                            'sign_auditor_date' => $sign_auditor_date,
                            'sign_bm_name' => $sign_bm_name,
                            'sign_bm_date' => $sign_bm_date,
                            'sign_abm_name' => $sign_abm_name,
                            'sign_abm_date' => $sign_abm_date,
                            'financial_section_title' => $financial_section_title,
                            'financialFindings' => $financialFindings,
                            'reportSections' => $reportSections ?? [],
                            'reportBlocks' => $reportBlocks ?? [],
                            'financial_criteria' => $financial_criteria,
                            'vatObservationRows' => $vatObservationRows,
                            'taxObservationRows' => $taxObservationRows,
                            'tableHeaders' => $tableHeaders ?? [],
                            'page19_compliance_title' => $page19_compliance_title,
                            'page19_compliance_period' => $page19_compliance_period,
                            'page19_compliance_followup_date' => $page19_compliance_followup_date,
                            'page19ComplianceRows' => $page19ComplianceRows,
                        ])
                    </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

<style>
    .field-label {
        display: block;
        margin-bottom: 4px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
    }
    .field-input {
        width: 100%;
        height: 36px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 0 10px;
        font-size: 13px;
        font-family: inherit;
        color: #334155;
        line-height: 36px;
    }
    .inline-input {
        display: inline-block;
        height: 28px;
        border: 1px solid #93c5fd;
        border-radius: 4px;
        background: #eff6ff;
        padding: 0 8px;
        font-size: 12px;
        color: #0f172a;
        line-height: 26px;
        vertical-align: middle;
    }
    .inline-input:focus {
        outline: none;
        border-color: #2b579a;
        box-shadow: 0 0 0 1px #2b579a;
        background: #fff;
    }
    .audit-date-field {
        vertical-align: middle;
    }
    .audit-date-field > input[type="text"] {
        min-width: 0;
        width: 100%;
    }
    table .audit-date-field {
        min-width: 6.5rem;
    }
    table .audit-date-picker-hit {
        width: 1.5rem;
        min-width: 1.5rem;
    }
    /* Bangla UI + digits: Hind Siliguri (clear ১). Do not prefer Noto — its ১ looks poor on web. */
    .audit-tab-pill,
    .audit-tab-label {
        font-family: 'Hind Siliguri', 'Nirmala UI', Inter, system-ui, sans-serif;
    }
    .audit-tab-label .bn-num,
    .bn-num.bn-tab {
        font-family: 'Hind Siliguri', 'Nirmala UI', sans-serif;
        font-weight: 700;
        letter-spacing: 0.02em;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }
    .audit-tab-index {
        font-family: Inter, system-ui, sans-serif;
        font-variant-numeric: tabular-nums;
    }
    .audit-wizard input,
    .audit-wizard textarea,
    .audit-wizard select,
    .audit-wizard button {
        font-family: inherit;
    }
    .audit-wizard.is-review-readonly input:not([type="hidden"]),
    .audit-wizard.is-review-readonly textarea,
    .audit-wizard.is-review-readonly select {
        pointer-events: none;
        background-color: #f8fafc;
        cursor: not-allowed;
    }
    .finding-serial-cell,
    .finding-serial-input,
    .finding-heading,
    .finding-heading .bn-num,
    .bn-num.bn-serial {
        font-family: 'Hind Siliguri', 'Nirmala UI', Arial, sans-serif !important;
        font-weight: 700;
        letter-spacing: 0.02em;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }
    .finding-serial-input {
        font-size: inherit;
        line-height: 1.35;
        color: inherit;
    }
    /* Editor sheet — same A4 margins as submitted document */
    .cover-form {
        width: 210mm;
        max-width: 100%;
    }
    .cover-inner {
        padding: 15mm 20mm 15mm;
        color: #111;
        font-size: 12pt;
        line-height: 1.45;
        min-height: 297mm;
        box-sizing: border-box;
    }
    .dotted {
        border-bottom: 1px dotted #111;
        padding: 0 2px 1px;
        font-weight: 600;
    }
    /* Form editor tables (pages 2–4 input views) */
    .a4-table {
        width: 100%;
        border-collapse: collapse;
    }
    .a4-table th,
    .a4-table td {
        border: 1px solid #111;
        padding: 1.6mm 1.8mm;
        vertical-align: middle;
    }
    .a4-table th {
        font-weight: 600;
        background: #d9d9d9;
    }
    .a4-table-compact th,
    .a4-table-compact td {
        padding: 1.2mm 1.4mm;
    }
    .it-checklist-table .it-tick {
        font-family: "Segoe UI Symbol", "DejaVu Sans", "Nirmala UI", sans-serif;
        font-size: 14px;
        font-weight: 700;
        color: #111;
        line-height: 1;
    }
    .it-checklist-table th {
        background: #e2e8f0;
    }
    .external-audit-table th {
        background: #f0e4d4;
    }
</style>
<script>
    window.__auditGotoPlace = function (eventOrDetail) {
        const raw = (eventOrDetail && eventOrDetail.detail !== undefined) ? eventOrDetail.detail : (eventOrDetail || {});
        const d = (raw && (raw.anchor !== undefined || raw.tab !== undefined || raw.query !== undefined))
            ? raw
            : (Array.isArray(raw) ? (raw[0] || {}) : raw);
        const id = d.anchor || '';
        const q = String(d.query || '').trim();

        const run = () => {
            const el = id ? document.getElementById(id) : null;
            if (!el) return false;
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            el.classList.add('ring-2', 'ring-violet-500', 'ring-offset-2');
            setTimeout(() => el.classList.remove('ring-2', 'ring-violet-500', 'ring-offset-2'), 1800);
            if (!q) return true;
            const scope = el.closest('.border-b, .mx-auto, #audit-page4, #audit-page3, #audit-page2, #audit-cover') || el.parentElement || document;
            const needle = q.toLowerCase();
            scope.querySelectorAll('input, textarea').forEach((field) => {
                const val = String(field.value || '');
                if (!val.toLowerCase().includes(needle)) return;
                field.classList.add('ring-2', 'ring-violet-400', 'bg-violet-50');
                try { field.focus({ preventScroll: true }); } catch (e) { try { field.focus(); } catch (e2) {} }
                setTimeout(() => field.classList.remove('ring-2', 'ring-violet-400', 'bg-violet-50'), 2400);
            });
            return true;
        };

        // Tab content may still be morphing — retry a few times.
        setTimeout(() => { if (!run()) setTimeout(run, 200); }, 50);
        setTimeout(run, 300);
        setTimeout(run, 600);
    };

    if (!window.__auditGotoPlaceBound) {
        window.__auditGotoPlaceBound = true;
        window.addEventListener('audit-goto-place', window.__auditGotoPlace);
        document.addEventListener('livewire:init', () => {
            if (window.Livewire && typeof window.Livewire.on === 'function') {
                window.Livewire.on('audit-goto-place', (payload) => {
                    window.__auditGotoPlace({ detail: payload });
                });
            }
        });
    }
</script>
</div>

