{{-- Audit reports dashboard: familiar layout, tighter spacing --}}
<div class="mb-3 grid gap-2 sm:grid-cols-3">
    <div class="rounded-lg border border-sky-100 bg-sky-50/80 px-3 py-2">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700">Ongoing</p>
        <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-sky-900">{{ $ongoingCount }}</p>
        <p class="mt-0.5 text-[10px] text-sky-700/80">চলমান খসড়া</p>
    </div>
    <div class="rounded-lg border border-amber-100 bg-amber-50/80 px-3 py-2">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700">Pending slots</p>
        <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-amber-900">{{ $pendingSlots }}</p>
        <p class="mt-0.5 text-[10px] text-amber-700/80">আরও নতুন ({{ $maxConcurrentDrafts }} পর্যন্ত)</p>
    </div>
    <div class="rounded-lg border border-emerald-100 bg-emerald-50/80 px-3 py-2">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Completed</p>
        <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-emerald-900">{{ $completedCount }}</p>
        <p class="mt-0.5 text-[10px] text-emerald-700/80">সম্পন্ন সংরক্ষিত</p>
    </div>
</div>

@unless ($canStartNewReport)
    <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-[11px] text-amber-900">
        চলমান রিপোর্ট {{ $maxConcurrentDrafts }}টিতে পূর্ণ। নতুন শুরু করতে Continue করে শেষ করুন বা Delete করুন।
    </div>
@endunless

{{-- Start new card --}}
<div class="mb-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm {{ $canStartNewReport ? '' : 'pointer-events-none opacity-60' }}">
    <div class="mb-2">
        <p class="text-[13px] font-semibold text-navy-900">নতুন রিপোর্ট শুরু করুন</p>
        <p class="text-[11px] text-slate-500">ক্লিক করলে সব শাখা · একসাথে ৫টি · বাকি স্ক্রল</p>
    </div>

    <div class="grid gap-2 lg:grid-cols-[minmax(0,1.4fr)_120px_88px_auto] lg:items-end">
        <div class="relative min-w-0" @mousedown.outside="open = false">
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Search branch</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                <input
                    type="search"
                    x-model="q"
                    @focus="open = true; highlight = 0"
                    @click="open = true"
                    @input="open = true; highlight = 0"
                    @keydown="onKey($event)"
                    placeholder="Click to browse, or type to filter…"
                    class="h-9 w-full rounded-lg border-slate-200 py-0 pl-8 pr-14 text-[12px] shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]"
                    autocomplete="off"
                >
                <button
                    type="button"
                    x-show="q || selectedId"
                    x-cloak
                    @click="clear()"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-medium text-slate-400 hover:text-slate-600"
                >Clear</button>
            </div>

            <div
                x-show="open"
                x-cloak
                class="absolute z-20 mt-1 w-full overflow-y-auto overscroll-contain rounded-lg border border-slate-200 bg-white shadow-lg"
                style="max-height: 200px;"
            >
                <template x-for="(b, idx) in filtered" :key="b.id">
                    <button
                        type="button"
                        @click="pick(b)"
                        @mouseenter="highlight = idx"
                        class="flex h-10 w-full shrink-0 items-center gap-2 px-2.5 text-left hover:bg-sky-50"
                        :class="highlight === idx ? 'bg-sky-50' : ''"
                    >
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[12px] font-semibold leading-tight text-navy-900" x-text="b.name"></span>
                            <span class="block truncate text-[10px] leading-tight text-slate-500">
                                <span x-text="b.code || '—'"></span>
                                <span x-show="b.area"> · </span>
                                <span x-text="b.area || ''"></span>
                            </span>
                        </span>
                        <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-semibold"
                            :class="b.active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                            x-text="b.active ? 'Active' : 'Inactive'"
                        ></span>
                    </button>
                </template>
                <p x-show="filtered.length === 0" class="px-2.5 py-2 text-[11px] text-slate-500">কোনো শাখা মেলেনি</p>
            </div>

            <p x-show="selectedLabel" x-cloak class="mt-1 truncate text-[11px] font-medium text-emerald-700">
                Selected: <span x-text="selectedLabel"></span>
            </p>
            @error('shakha_id')
                <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Month</label>
            <select wire:model="report_month" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]" @disabled(! $canStartNewReport)>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Year</label>
            <select wire:model="report_year" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]" @disabled(! $canStartNewReport)>
                @for ($y = now()->year + 1; $y >= now()->year - 6; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="flex items-end">
            <button
                type="button"
                wire:click="startReport"
                wire:loading.attr="disabled"
                wire:target="startReport"
                @disabled(! $canStartNewReport)
                class="inline-flex h-9 w-full items-center justify-center rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072] disabled:cursor-not-allowed disabled:opacity-50 lg:w-auto"
            >
                <span wire:loading.remove wire:target="startReport">Start new</span>
                <span wire:loading wire:target="startReport">Starting…</span>
            </button>
        </div>
    </div>
</div>

{{-- Find saved reports by month / year / branch --}}
<div class="mb-3 overflow-hidden rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
    <div class="mb-2 flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="text-[13px] font-semibold text-navy-900">রিপোর্ট খুঁজুন (মাস অনুযায়ী)</p>
            <p class="text-[11px] text-slate-500">প্রতিটি রিপোর্ট মাস + বছর দিয়ে সংরক্ষিত · এখান থেকে খুঁজে Continue / Open করুন</p>
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
            <button type="button" wire:click="showCurrentMonthReports" class="h-7 rounded-md border border-sky-200 bg-sky-50 px-2.5 text-[11px] font-medium text-sky-800 hover:bg-sky-100">এই মাস</button>
            <button type="button" wire:click="clearReportListFilters" class="h-7 rounded-md border border-slate-200 px-2.5 text-[11px] font-medium text-slate-600 hover:bg-slate-50">সব দেখুন</button>
        </div>
    </div>
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.2fr)_130px_100px_140px]">
        <div>
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Branch / memo</label>
            <input
                type="search"
                wire:model.live.debounce.300ms="listFilterQ"
                placeholder="শাখার নাম, কোড বা সূত্র…"
                class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px] shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]"
            >
        </div>
        <div>
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Month</label>
            <select wire:model.live="listFilterMonth" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                <option value="0">All months</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Year</label>
            <select wire:model.live="listFilterYear" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                <option value="0">All years</option>
                @for ($y = now()->year + 1; $y >= now()->year - 6; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Status</label>
            <select wire:model.live="listFilterStatus" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]">
                <option value="all">All</option>
                <option value="draft">Ongoing only</option>
                <option value="completed">Completed only</option>
            </select>
        </div>
    </div>
    @php
        $filterLabel = [];
        if ((int) $listFilterMonth >= 1 && (int) $listFilterMonth <= 12) {
            $filterLabel[] = date('F', mktime(0, 0, 0, (int) $listFilterMonth, 1));
        }
        if ((int) $listFilterYear >= 2000) {
            $filterLabel[] = (string) $listFilterYear;
        }
        if (($listFilterStatus ?? 'all') === 'draft') {
            $filterLabel[] = 'Ongoing';
        } elseif (($listFilterStatus ?? 'all') === 'completed') {
            $filterLabel[] = 'Completed';
        }
        if (trim((string) ($listFilterQ ?? '')) !== '') {
            $filterLabel[] = '“'.trim($listFilterQ).'”';
        }
    @endphp
    <p class="mt-2 text-[11px] text-slate-500">
        Showing
        <span class="font-semibold text-slate-700">{{ $ongoingReports->count() }}</span> ongoing
        ·
        <span class="font-semibold text-slate-700">{{ $completedReports->count() }}</span> completed
        @if ($filterLabel !== [])
            <span class="text-slate-400">· Filter:</span>
            <span class="font-medium text-[#2b579a]">{{ implode(' · ', $filterLabel) }}</span>
        @endif
    </p>
</div>

@if ($ongoingReports->isNotEmpty())
    <div class="mb-3 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-3 py-2">
            <p class="text-[13px] font-semibold text-navy-900">চলমান রিপোর্ট</p>
            <p class="text-[10px] text-slate-500">Continue · Auto-save চালু · মাস অনুযায়ী ফিল্টার করা</p>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach ($ongoingReports as $report)
                <div class="flex flex-wrap items-center gap-2 px-3 py-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[12px] font-semibold text-navy-900">
                            {{ $report->shakha_display_name ?: ($report->shakha?->name ?? 'Branch') }}
                        </p>
                        <p class="mt-0.5 truncate text-[10px] text-slate-500">
                            {{ $report->periodLabel() }}
                            · {{ $report->statusBadge() }}
                            @if ($report->last_saved_at)
                                · Saved {{ $report->last_saved_at->timezone('Asia/Dhaka')->format('d M, h:i A') }}
                            @endif
                        </p>
                        <div class="mt-1.5 flex items-center gap-2">
                            <div class="h-1 max-w-[180px] flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-[#2b579a]" style="width: {{ min(100, (int) $report->progress_pct) }}%"></div>
                            </div>
                            <span class="text-[10px] font-medium tabular-nums text-slate-500">{{ $report->progress_pct }}%</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            wire:click="resumeReport({{ $report->id }})"
                            class="h-7 rounded-md bg-[#2b579a] px-2.5 text-[11px] font-semibold text-white hover:bg-[#204072]"
                        >Continue</button>
                        <a
                            href="{{ route('audits.checklist', $report) }}"
                            class="inline-flex h-7 items-center rounded-md border border-indigo-200 bg-indigo-50 px-2.5 text-[11px] font-medium text-indigo-800 hover:bg-indigo-100"
                        >Check List</a>
                        <a
                            href="{{ route('audit-findings.entry', ['report' => $report->id]) }}"
                            class="inline-flex h-7 items-center rounded-md border border-slate-200 px-2.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                        >Findings</a>
                        <button
                            type="button"
                            wire:click="deleteDraft({{ $report->id }})"
                            wire:confirm="এই খসড়া মুছে ফেলবেন?"
                            class="h-7 rounded-md border border-rose-200 px-2.5 text-[11px] font-medium text-rose-600 hover:bg-rose-50"
                        >Delete</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if ($completedReports->isNotEmpty())
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-3 py-2">
            <p class="text-[13px] font-semibold text-navy-900">সম্পন্ন রিপোর্ট</p>
            <p class="text-[10px] text-slate-500">মাস / বছর দিয়ে খুঁজে Open করুন</p>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach ($completedReports as $report)
                <div class="flex flex-wrap items-center gap-2 px-3 py-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[12px] font-semibold text-navy-900">
                            {{ $report->shakha_display_name ?: ($report->shakha?->name ?? 'Branch') }}
                        </p>
                        <p class="mt-0.5 truncate text-[10px] text-slate-500">
                            {{ $report->periodLabel() }}
                            @if ($report->completed_at)
                                · {{ $report->completed_at->format('d M Y') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <a
                            href="{{ route('audits.checklist', $report) }}"
                            class="inline-flex h-7 items-center rounded-md border border-indigo-200 bg-indigo-50 px-2.5 text-[11px] font-medium text-indigo-800 hover:bg-indigo-100"
                        >Check List</a>
                        <a
                            href="{{ route('audit-findings.entry', ['report' => $report->id]) }}"
                            class="inline-flex h-7 items-center rounded-md border border-emerald-200 bg-emerald-50 px-2.5 text-[11px] font-medium text-emerald-800 hover:bg-emerald-100"
                        >Findings</a>
                        <button
                            type="button"
                            wire:click="resumeReport({{ $report->id }})"
                            class="h-7 rounded-md border border-slate-200 px-2.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                        >Open</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if ($ongoingReports->isEmpty() && $completedReports->isEmpty())
    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
        <p class="text-[13px] font-semibold text-slate-700">এই ফিল্টারে কোনো রিপোর্ট নেই</p>
        <p class="mt-1 text-[11px] text-slate-500">অন্য মাস/বছর বেছে নিন, অথবা “সব দেখুন” চাপুন</p>
        <button type="button" wire:click="clearReportListFilters" class="mt-3 inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-700 hover:bg-slate-50">সব দেখুন</button>
    </div>
@endif
