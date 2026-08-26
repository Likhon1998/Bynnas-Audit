<div class="audit-wizard" style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;">
    <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

    @if ($step === 'select')
        <div class="border-b border-slate-200 bg-white px-4 py-4 lg:px-6">
            <div class="mb-3">
                <h1 class="text-[16px] font-semibold text-navy-900">Audit Report</h1>
                <p class="text-[11px] text-slate-500">Select branch · month · year — then Start</p>
            </div>

            <div class="grid gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-card sm:grid-cols-[1fr_130px_100px_auto]">
                {{-- Find branch (Alpine typeahead) --}}
                <div
                    class="relative"
                    x-data="{
                        q: @js($selectedShakhaLabel ?: ''),
                        open: false,
                        highlight: 0,
                        selectedId: @js($shakha_id ? (string) $shakha_id : ''),
                        branches: @js($branchOptions),
                        get filtered() {
                            const q = this.q.trim().toLowerCase();
                            return this.branches.filter((b) => {
                                if (!q) return true;
                                const hay = (b.name + ' ' + b.code + ' ' + b.area + ' ' + b.division + ' ' + b.focal + ' ' + b.opening).toLowerCase();
                                return hay.includes(q);
                            });
                        },
                        pick(b) {
                            this.q = b.name;
                            this.selectedId = String(b.id);
                            this.open = false;
                            this.highlight = 0;
                            $wire.set('shakha_id', Number(b.id));
                        },
                        clear() {
                            this.q = '';
                            this.selectedId = '';
                            this.open = false;
                            this.highlight = 0;
                            $wire.set('shakha_id', null);
                        },
                        onKey(e) {
                            const list = this.filtered;
                            if (!this.open && (e.key === 'ArrowDown' || e.key === 'Enter')) {
                                this.open = true;
                                return;
                            }
                            if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                this.highlight = Math.min(this.highlight + 1, Math.max(Math.min(list.length, 40) - 1, 0));
                            } else if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                this.highlight = Math.max(this.highlight - 1, 0);
                            } else if (e.key === 'Enter' && list[this.highlight]) {
                                e.preventDefault();
                                this.pick(list[this.highlight]);
                            } else if (e.key === 'Escape') {
                                this.open = false;
                            }
                        }
                    }"
                    @click.outside="open = false"
                >
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Find branch</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                        <input
                            type="search"
                            x-model="q"
                            @focus="open = true; highlight = 0"
                            @input="open = true; highlight = 0; if (selectedId) { selectedId = ''; $wire.set('shakha_id', null); }"
                            @keydown="onKey($event)"
                            placeholder="Type branch, area, code, focal person…"
                            class="h-9 w-full rounded-lg border-slate-200 py-0 pl-8 pr-8 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            autocomplete="off"
                        >
                        <button
                            type="button"
                            x-show="q"
                            x-cloak
                            @click="clear()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] font-medium text-slate-400 hover:text-slate-600"
                        >Clear</button>
                    </div>

                    <div
                        x-show="open && filtered.length"
                        x-cloak
                        class="absolute z-30 mt-1 max-h-72 w-full overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        <template x-for="(b, idx) in filtered.slice(0, 40)" :key="b.id">
                            <button
                                type="button"
                                @click="pick(b)"
                                @mouseenter="highlight = idx"
                                class="flex w-full items-start gap-2 px-3 py-2 text-left hover:bg-sky-50"
                                :class="highlight === idx || selectedId === b.id ? 'bg-sky-50' : ''"
                            >
                                <span class="mt-0.5 w-6 shrink-0 text-[11px] tabular-nums text-slate-400" x-text="b.serial"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[12px] font-semibold text-navy-900" x-text="b.name"></span>
                                    <span class="mt-0.5 block truncate text-[10px] text-slate-500">
                                        <span x-text="b.area || 'No area'"></span>
                                        <span x-show="b.code"> · <span x-text="b.code"></span></span>
                                        <span x-show="b.opening"> · Opened <span x-text="b.opening"></span></span>
                                    </span>
                                </span>
                                <span
                                    class="mt-0.5 shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-semibold"
                                    :class="b.active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                    x-text="b.active ? 'Active' : 'Inactive'"
                                ></span>
                            </button>
                        </template>
                        <p class="border-t border-slate-100 px-3 py-1.5 text-[10px] text-slate-400" x-show="filtered.length > 40">
                            Showing first 40 of <span x-text="filtered.length"></span> — keep typing to narrow
                        </p>
                    </div>
                    <p x-show="open && q && !filtered.length" x-cloak class="absolute z-30 mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-[12px] text-slate-500 shadow-lg">
                        No branch matches “<span x-text="q"></span>”
                    </p>

                    @if ($shakha_id)
                        <p class="mt-1 text-[11px] text-emerald-700">Selected: {{ $selectedShakhaLabel }}</p>
                    @endif
                    @error('shakha_id')
                        <p class="mt-1 text-[12px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Month</label>
                    <select wire:model="report_month" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Year</label>
                    <select wire:model="report_year" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                        @for ($y = now()->year + 1; $y >= now()->year - 6; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Pure Livewire Start (outside Alpine) --}}
                <div class="flex items-end">
                    <button
                        type="button"
                        wire:click="startReport"
                        wire:loading.attr="disabled"
                        wire:target="startReport"
                        class="inline-flex h-9 w-full items-center justify-center rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072] disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="startReport">Start</span>
                        <span wire:loading wire:target="startReport">Starting…</span>
                    </button>
                </div>
            </div>

            {{-- Fallback native select if typeahead fails --}}
            <div class="mt-3">
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Or select branch</label>
                <select wire:model="shakha_id" class="h-9 w-full max-w-xl rounded-lg border-slate-200 py-0 text-[12px]">
                    <option value="">শাখা নির্বাচন করুন…</option>
                    @foreach ($branchOptions as $b)
                        <option value="{{ $b['id'] }}">{{ $b['name'] }}@if($b['code']) ({{ $b['code'] }})@endif@if($b['area']) — {{ $b['area'] }}@endif</option>
                    @endforeach
                </select>
            </div>
        </div>
    @else
        <div class="border-b border-slate-200 bg-white px-4 py-3 lg:px-6">
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-[15px] font-semibold text-navy-900">অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</h1>
                    <p class="truncate text-[11px] text-slate-500">{{ $shakha_display_name }} · {{ $area_display_name }} · {{ $monthLabel }} {{ $report_year }}</p>
                </div>
                <button type="button" wire:click="backToSelect" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">নতুন করে শুরু</button>
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] bg-white px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button
                    type="button"
                    wire:click="downloadPdf"
                    wire:loading.attr="disabled"
                    wire:target="downloadPdf"
                    class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-emerald-600 bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    <span wire:loading.remove wire:target="downloadPdf">PDF Download</span>
                    <span wire:loading wire:target="downloadPdf">Downloading…</span>
                </button>
                <button type="button" wire:click="saveCover" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ</button>
            </div>

            {{-- Page sequence --}}
            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                <span class="mr-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">পৃষ্ঠা ক্রম</span>
                @foreach ($tabs as $tab)
                    <button
                        type="button"
                        @if ($tab['ready']) wire:click="$set('activeTab', '{{ $tab['id'] }}')" @endif
                        @disabled(! $tab['ready'])
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-medium transition
                            {{ $activeTab === $tab['id'] ? 'bg-[#2b579a] text-white' : ($tab['ready'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'cursor-not-allowed bg-slate-50 text-slate-400') }}"
                    >
                        <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold
                            {{ $activeTab === $tab['id'] ? 'bg-white/20 text-white' : 'bg-white text-slate-500' }}">{{ $tab['num'] }}</span>
                        {{ $tab['label'] }}
                    </button>
                    @if (! $loop->last)
                        <span class="text-slate-300">→</span>
                    @endif
                @endforeach
            </div>
        </div>

        @if (session('status'))
            <div class="bg-emerald-50 px-4 py-2 text-[12px] text-emerald-800 lg:px-6">{{ session('status') }}</div>
        @endif

        @if ($activeTab === 'cover')
        <div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-[12px] font-semibold text-slate-800">১. Cover Page — ইনপুট ফর্ম</p>
                <span class="text-[11px] text-slate-500">নীল ঘরগুলো পূরণ করুন · Preview দিয়ে ডাউনলোড দেখুন</span>
            </div>

            <div class="cover-form mx-auto rounded-sm bg-white shadow-lg">
                <div class="cover-inner text-[12.5px] leading-relaxed text-slate-900">
                    @include('livewire.partials.audit-cover-letterhead', [
                        'editable' => true,
                        'logoUrl' => $logoUrl,
                        'ratingColor' => $ratingColor,
                        'control_rating' => $control_rating,
                    ])

                    <div class="mt-4 space-y-2">
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">সূত্র নাম্বার:</span>
                            <input type="text" wire:model.live="memo_no" class="inline-input min-w-[220px] flex-1">
                        </p>
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">তারিখ:</span>
                            <input type="date" wire:model.live="report_date" class="inline-input">
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
                            <input type="text" wire:model.live="shakha_display_name" class="inline-input min-w-[200px] flex-1">
                        </p>
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">অঞ্চলের নাম:</span>
                            <input type="text" wire:model.live="area_display_name" class="inline-input min-w-[200px] flex-1">
                        </p>
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">নিরীক্ষাকাল:</span>
                            <input type="text" wire:model.live="audit_period_label" class="inline-input min-w-[200px] flex-1">
                        </p>
                    </div>

                    <div class="mt-5 leading-[1.85]">
                        <p class="font-semibold">প্রিয় মহোদয়,</p>
                        <p class="mt-2 text-justify">
                            গত
                            <input type="date" wire:model.live="audit_start_date" class="inline-input mx-1">
                            হতে
                            <input type="date" wire:model.live="audit_end_date" class="inline-input mx-1">
                            পর্যন্ত মোট
                            <input type="number" min="0" wire:model.live="working_days" class="inline-input mx-1 w-16 text-center">
                            কর্ম দিবস
                            <input type="text" wire:model.live="shakha_display_name" class="inline-input mx-1 min-w-[140px]">
                            শাখা হতে
                            <input type="text" wire:model.live="period_scope" class="inline-input mx-1 min-w-[140px]">
                            সময়ের উপর অভ্যন্তরীণ নিরীক্ষা সম্পন্ন করা হয়। শাখার খসড়া প্রতিবেদন
                            <input type="date" wire:model.live="draft_sent_date" class="inline-input mx-1">
                            ইং তারিখে প্রেরণ করা হয় এবং
                            <input type="date" wire:model.live="comments_received_date" class="inline-input mx-1">
                            তারিখে মতামত পাওয়া যায়। এতদসংক্রান্ত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন আপনার সদয় অবগতির জন্য পেশ করা হলো।
                        </p>
                    </div>

                    <div class="mt-6">
                        <p>আপনার বিশ্বস্ত,</p>
                        <p class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">নাম:</span>
                            <input type="text" wire:model.live="auditor_name" class="inline-input min-w-[180px] flex-1">
                        </p>
                        @error('auditor_name') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        <p class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">পদবী:</span>
                            <input type="text" wire:model.live="auditor_designation" class="inline-input min-w-[180px] flex-1">
                        </p>
                    </div>

                    <div class="mt-6 text-[11.5px] leading-relaxed">
                        <p class="font-semibold">অনুলিপি:</p>
                        <ol class="ml-4 list-decimal space-y-0.5">
                            <li>নির্বাহী পরিচালক</li>
                            <li>উপ-নির্বাহী পরিচালক</li>
                            <li>পরিচালক ঋণ</li>
                            <li>উপ-প্রধান ঋণ</li>
                            <li>যুগ্ম পরিচালক প্রশাসন ও মানব সম্পদ</li>
                            <li>ফোকাল পার্সন</li>
                            <li>অঞ্চলিক ব্যবস্থাপক</li>
                            <li>শাখা ব্যবস্থাপক</li>
                            <li>অফিস কপি</li>
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
            @include('livewire.partials.audit-page2-form')
        @elseif ($activeTab === 'page3')
            @include('livewire.partials.audit-page3-form')
        @else
            <div class="px-4 py-16 text-center text-[13px] text-slate-500">
                এই পৃষ্ঠা এখনো যোগ করা হয়নি। Cover Page শেষ করে পরের ছবি পাঠালে ট্যাব যোগ করা হবে।
            </div>
        @endif

        @if ($showPreview)
            <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-900/55 px-3 py-6" wire:click.self="closePreview">
                <div class="w-full max-w-[230mm]">
                    <div class="mb-3 flex items-center justify-between rounded-lg bg-white px-4 py-2.5 shadow">
                        <div>
                            <p class="text-[13px] font-semibold text-navy-900">Preview</p>
                            <p class="text-[11px] text-slate-500">A4 · পৃষ্ঠা ১–৩ · Cover + এক নজরে/সূচিপত্র + স্বাক্ষর</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="downloadPdf"
                                wire:loading.attr="disabled"
                                wire:target="downloadPdf"
                                class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                                <span wire:loading.remove wire:target="downloadPdf">PDF Download</span>
                                <span wire:loading wire:target="downloadPdf">Downloading…</span>
                            </button>
                            <button type="button" wire:click="closePreview" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">বন্ধ</button>
                        </div>
                    </div>

                    <div class="a4-preview-stage bg-[#a6a6a6] px-3 py-5 sm:px-5">
                        <div class="mb-2 flex items-center justify-between px-1">
                            <p class="text-[11px] font-medium text-slate-800">পৃষ্ঠা ১ — Cover (A4)</p>
                        </div>

                        <div class="a4-sheet">
                            <div class="a4-inner official-preview">
                                <div class="a4-body">
                                    @include('livewire.partials.audit-cover-letterhead', [
                                        'editable' => false,
                                        'logoUrl' => $logoUrl,
                                        'ratingColor' => $ratingColor,
                                        'control_rating' => $control_rating,
                                    ])

                                    <div class="mt-[4mm] space-y-[1.5mm] text-[12px] text-black leading-[1.55]">
                                        <p><span class="font-semibold">সূত্র নাম্বার:</span> <span class="dotted">{{ $memo_no ?: '………………………………' }}</span></p>
                                        <p><span class="font-semibold">তারিখ:</span> <span class="dotted">{{ $report_date ? \Carbon\Carbon::parse($report_date)->format('d/m/Y') : '……………………' }}</span></p>
                                    </div>

                                    <div class="mt-[5mm] text-[12px] leading-[1.55] text-black">
                                        <p>বরাবর,</p>
                                        <p>যুগ্ম পরিচালক (নিরীক্ষা)</p>
                                        <p>দুঃস্থ স্বাস্থ্য কেন্দ্র (ডিএসকে)</p>
                                        <p>প্রধান কার্যালয়, ঢাকা।</p>
                                    </div>

                                    <h2 class="mt-[5mm] text-center text-[14.5px] font-bold text-black underline decoration-1 underline-offset-4">অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</h2>

                                    <div class="mt-[4mm] space-y-[2mm] text-[12px] leading-[1.55] text-black">
                                        <p><span class="font-semibold">শাখার নাম ও নাম্বার:</span> <span class="dotted">{{ $shakha_display_name ?: '………………………………' }}</span></p>
                                        <p><span class="font-semibold">অঞ্চলের নাম:</span> <span class="dotted">{{ $area_display_name ?: '………………………………' }}</span></p>
                                        <p><span class="font-semibold">নিরীক্ষাকাল:</span> <span class="dotted">{{ $audit_period_label ?: '………………………………' }}</span></p>
                                    </div>

                                    <div class="mt-[4.5mm] text-[12px] leading-[1.7] text-black">
                                        <p class="font-semibold">প্রিয় মহোদয়,</p>
                                        <p class="mt-[2mm] text-justify">
                                            গত
                                            <span class="underline-field">{{ $audit_start_date ? \Carbon\Carbon::parse($audit_start_date)->format('d/m/Y') : '…………' }}</span>
                                            হতে
                                            <span class="underline-field">{{ $audit_end_date ? \Carbon\Carbon::parse($audit_end_date)->format('d/m/Y') : '…………' }}</span>
                                            পর্যন্ত মোট
                                            <span class="underline-field">{{ $working_days !== null && $working_days !== '' ? $working_days : '……' }}</span>
                                            কর্ম দিবস
                                            <span class="underline-field">{{ $shakha_display_name ?: '………………' }}</span>
                                            শাখা হতে
                                            <span class="underline-field">{{ $period_scope ?: '………………' }}</span>
                                            সময়ের উপর অভ্যন্তরীণ নিরীক্ষা সম্পন্ন করা হয়। শাখার খসড়া প্রতিবেদন
                                            <span class="underline-field">{{ $draft_sent_date ? \Carbon\Carbon::parse($draft_sent_date)->format('d/m/Y') : '…………' }}</span>
                                            ইং তারিখে প্রেরণ করা হয় এবং
                                            <span class="underline-field">{{ $comments_received_date ? \Carbon\Carbon::parse($comments_received_date)->format('d/m/Y') : '…………' }}</span>
                                            তারিখে মতামত পাওয়া যায়। এতদসংক্রান্ত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন আপনার সদয় অবগতির জন্য পেশ করা হলো।
                                        </p>
                                    </div>

                                    <div class="mt-[6mm] text-[12px] leading-[1.55] text-black">
                                        <p>আপনার বিশ্বস্ত,</p>
                                        <p class="mt-[5mm]"><span class="font-semibold">নাম:</span> <span class="dotted">{{ $auditor_name ?: '……………………' }}</span></p>
                                        <p class="mt-[1.5mm]"><span class="font-semibold">পদবী:</span> <span class="dotted">{{ $auditor_designation ?: '……………………' }}</span></p>
                                    </div>

                                    <div class="mt-[6mm] text-[11.5px] leading-[1.55] text-black">
                                        <p class="font-semibold">অনুলিপি:</p>
                                        <ol class="ml-5 list-decimal space-y-[0.8mm]">
                                            <li>নির্বাহী পরিচালক</li>
                                            <li>উপ-নির্বাহী পরিচালক</li>
                                            <li>পরিচালক ঋণ</li>
                                            <li>উপ-প্রধান ঋণ</li>
                                            <li>যুগ্ম পরিচালক প্রশাসন ও মানব সম্পদ</li>
                                            <li>ফোকাল পার্সন</li>
                                            <li>অঞ্চলিক ব্যবস্থাপক</li>
                                            <li>শাখা ব্যবস্থাপক</li>
                                            <li>অফিস কপি</li>
                                        </ol>
                                    </div>
                                </div>
                                <p class="a4-page-num">1</p>
                            </div>
                        </div>

                        <div class="mb-2 mt-8 flex items-center justify-between px-1">
                            <p class="text-[11px] font-medium text-slate-800">পৃষ্ঠা ২ — এক নজরে তথ্য + সূচিপত্র (শুরু) (A4)</p>
                        </div>

                        @include('livewire.partials.audit-page2-preview', [
                            'logoUrl' => $logoUrl,
                            'shakha_display_name' => $shakha_display_name,
                            'glance_as_of' => $glance_as_of,
                            'branch_opening_date' => $branch_opening_date,
                            'staff_info_as_of' => $staff_info_as_of,
                            'glanceRows' => $glanceRows,
                            'staffColumns' => $staffColumns,
                            'staffRows' => $staffRows,
                            'tocPage2Rows' => $tocPage2Rows,
                        ])

                        <div class="mb-2 mt-8 flex items-center justify-between px-1">
                            <p class="text-[11px] font-medium text-slate-800">পৃষ্ঠা ৩ — সূচিপত্র (ধারাবাহিকতা) + স্বাক্ষর (A4)</p>
                        </div>

                        @include('livewire.partials.audit-page3-preview', [
                            'tocPage3Rows' => $tocPage3Rows,
                            'sign_auditor_name' => $sign_auditor_name,
                            'sign_auditor_designation' => $sign_auditor_designation,
                            'sign_auditor_date' => $sign_auditor_date,
                            'sign_bm_name' => $sign_bm_name,
                            'sign_bm_date' => $sign_bm_date,
                            'sign_abm_name' => $sign_abm_name,
                            'sign_abm_date' => $sign_abm_date,
                        ])
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
    /* Editor sheet (approximate A4 look while editing) */
    .cover-form {
        width: 210mm;
        max-width: 100%;
    }
    .cover-inner {
        padding: 14mm 16mm 12mm;
        color: #111;
        font-size: 12.5px;
        min-height: 297mm;
        box-sizing: border-box;
    }
    /* True A4 preview pages */
    .a4-sheet {
        width: 210mm;
        height: 297mm;
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
        background: #fff;
        box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.35);
        box-sizing: border-box;
        overflow: hidden;
    }
    .a4-inner {
        height: 100%;
        padding: 12mm 14mm 10mm;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        color: #111;
        font-size: 11.5px;
        line-height: 1.5;
    }
    .a4-body {
        flex: 1 1 auto;
        min-height: 0;
    }
    .a4-page-num {
        flex: 0 0 auto;
        margin-top: auto;
        padding-top: 4mm;
        text-align: right;
        font-size: 11px;
        color: #111;
        line-height: 1;
    }
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
    .official-preview {
        font-family: 'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;
    }
    .underline-field {
        display: inline;
        border-bottom: 1px dotted #111;
        padding: 0 4px;
        font-weight: 600;
    }
    .dotted {
        border-bottom: 1px dotted #111;
        padding: 0 2px 1px;
        font-weight: 600;
    }
    @media print {
        @page { size: A4 portrait; margin: 0; }
        .a4-sheet {
            box-shadow: none;
            page-break-after: always;
            break-after: page;
        }
    }
</style>
</div>

