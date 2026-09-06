{{-- Audit reports dashboard — dense single workspace --}}
@php
    $allReports = $ongoingReports->concat($completedReports)->values();
@endphp

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-slate-50/70 px-3 py-2 sm:px-4">
        <div class="min-w-0">
            <h1 class="text-[14px] font-semibold tracking-tight text-navy-900">Audit Reports</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">
                Max {{ $maxConcurrentDrafts }} drafts · Auto-save · Continue from where you left off
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
            <span class="inline-flex items-center gap-1 rounded-md border border-sky-200 bg-sky-50 px-2 py-1 font-semibold text-sky-800">
                Ongoing <span class="tabular-nums">{{ $ongoingCount }}</span>
            </span>
            <span class="inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 font-semibold text-amber-800">
                Slots <span class="tabular-nums">{{ $pendingSlots }}</span>
            </span>
            <span class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 font-semibold text-emerald-800">
                Done <span class="tabular-nums">{{ $completedCount }}</span>
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="border-b border-emerald-100 bg-emerald-50 px-3 py-1.5 text-[11px] text-emerald-800 sm:px-4">{{ session('status') }}</div>
    @endif

    @unless ($canStartNewReport)
        <div class="border-b border-amber-200 bg-amber-50 px-3 py-1.5 text-[11px] text-amber-900 sm:px-4">
            Draft limit reached ({{ $maxConcurrentDrafts }}). Continue or delete a draft to start another.
        </div>
    @endunless

    {{-- Start new --}}
    <div class="border-b border-slate-100 px-3 py-2.5 sm:px-4 {{ $canStartNewReport ? '' : 'pointer-events-none opacity-55' }}">
        <div class="mb-1.5 flex items-baseline justify-between gap-2">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Start new</p>
            <p class="text-[10px] text-slate-400">Branch · month · year</p>
        </div>
        <div class="grid gap-2 lg:grid-cols-[minmax(0,1fr)_118px_88px_auto] lg:items-end">
            <div class="relative min-w-0" @mousedown.outside="open = false">
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Branch</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 z-[1] h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                    <input
                        type="search"
                        x-model="q"
                        @focus="open = true; highlight = 0"
                        @click="open = true"
                        @input="open = true; highlight = 0"
                        @keydown="onKey($event)"
                        :placeholder="selectedLabel ? '' : 'Search or click to browse…'"
                        class="block w-full rounded-md border-slate-200 py-1.5 pl-8 pr-14 text-[12px] leading-5 shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]"
                        :class="selectedId && !q ? 'text-transparent caret-slate-700' : ''"
                        autocomplete="off"
                    >
                    {{-- Selected branch shown inside the bar (keeps Month/Year/Start aligned) --}}
                    <span
                        x-show="selectedId && !q"
                        x-cloak
                        class="pointer-events-none absolute inset-y-0 left-8 right-14 flex items-center truncate text-[12px] font-medium leading-5 text-emerald-800"
                        x-text="selectedLabel"
                    ></span>
                    <button
                        type="button"
                        x-show="q || selectedId"
                        x-cloak
                        @click="clear()"
                        class="absolute right-2.5 top-1/2 z-[1] -translate-y-1/2 text-[10px] font-medium text-slate-400 hover:text-slate-600"
                    >Clear</button>

                    <div
                        x-show="open"
                        x-cloak
                        class="absolute left-0 right-0 top-full z-20 mt-1 overflow-y-auto overscroll-contain rounded-md border border-slate-200 bg-white shadow-lg"
                        style="max-height: 220px;"
                    >
                        <template x-for="(b, idx) in filtered" :key="b.id">
                            <button
                                type="button"
                                @click="pick(b)"
                                @mouseenter="highlight = idx"
                                class="flex h-9 w-full shrink-0 items-center gap-2 px-2.5 text-left hover:bg-sky-50"
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
                                <span
                                    class="shrink-0 rounded px-1.5 py-0.5 text-[9px] font-semibold"
                                    :class="b.active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                    x-text="b.active ? 'Active' : 'Inactive'"
                                ></span>
                            </button>
                        </template>
                        <p x-show="filtered.length === 0" class="px-2.5 py-2 text-[11px] text-slate-500">No branch matched</p>
                    </div>
                </div>
                @error('shakha_id')
                    <p class="absolute left-0 top-full z-10 mt-0.5 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Month</label>
                <select wire:model="report_month" class="block w-full rounded-md border-slate-200 py-1.5 text-[12px] leading-5" @disabled(! $canStartNewReport)>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Year</label>
                <select wire:model="report_year" class="block w-full rounded-md border-slate-200 py-1.5 text-[12px] leading-5" @disabled(! $canStartNewReport)>
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
                    class="inline-flex h-[34px] w-full items-center justify-center rounded-md bg-[#2b579a] px-3.5 text-[12px] font-semibold text-white hover:bg-[#204072] disabled:cursor-not-allowed disabled:opacity-50 lg:w-auto"
                >
                    <span wire:loading.remove wire:target="startReport">Start</span>
                    <span wire:loading wire:target="startReport">…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-2 border-b border-slate-100 bg-slate-50/40 px-3 py-2 sm:px-4">
        <div class="min-w-[10rem] flex-1 basis-[12rem]">
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Find</label>
            <input
                type="search"
                wire:model.live.debounce.300ms="listFilterQ"
                placeholder="Branch, code, memo…"
                class="block w-full rounded-md border-slate-200 py-1.5 text-[12px] leading-5 shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]"
            >
        </div>
        <div class="w-[7.5rem]">
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Month</label>
            <select wire:model.live="listFilterMonth" class="block w-full rounded-md border-slate-200 py-1.5 text-[12px] leading-5">
                <option value="0">All</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
                @endfor
            </select>
        </div>
        <div class="w-[5.5rem]">
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Year</label>
            <select wire:model.live="listFilterYear" class="block w-full rounded-md border-slate-200 py-1.5 text-[12px] leading-5">
                <option value="0">All</option>
                @for ($y = now()->year + 1; $y >= now()->year - 6; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Status</label>
            <div class="inline-flex h-[34px] overflow-hidden rounded-md border border-slate-200 bg-white">
                @foreach ([
                    'all' => 'All',
                    'draft' => 'Ongoing',
                    'completed' => 'Done',
                ] as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('listFilterStatus', '{{ $value }}')"
                        class="px-2.5 text-[11px] font-semibold transition
                            {{ ($listFilterStatus ?? 'all') === $value
                                ? 'bg-navy-900 text-white'
                                : 'text-slate-600 hover:bg-slate-50' }}"
                    >{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="flex items-end gap-1.5 pb-px">
            <button type="button" wire:click="showCurrentMonthReports" class="inline-flex h-[34px] items-center rounded-md border border-sky-200 bg-sky-50 px-2.5 text-[11px] font-semibold text-sky-800 hover:bg-sky-100">This month</button>
            <button type="button" wire:click="clearReportListFilters" class="inline-flex h-[34px] items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-medium text-slate-600 hover:bg-slate-50">Clear</button>
        </div>
    </div>

    {{-- Unified list --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-left">
            <thead class="border-b border-slate-100 bg-white">
                <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                    <th class="px-3 py-2 sm:px-4">Branch</th>
                    <th class="px-2 py-2">Period</th>
                    <th class="px-2 py-2">Status</th>
                    <th class="hidden px-2 py-2 md:table-cell">Progress</th>
                    <th class="hidden px-2 py-2 lg:table-cell">Saved</th>
                    <th class="px-3 py-2 text-right sm:px-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($allReports as $report)
                    @php
                        $isDraft = $report->isDraft();
                    @endphp
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-3 py-2 align-middle sm:px-4">
                            <p class="truncate text-[12px] font-semibold text-navy-900">
                                {{ $report->shakha_display_name ?: ($report->shakha?->name ?? 'Branch') }}
                            </p>
                            @if ($report->memo_no)
                                <p class="truncate text-[10px] text-slate-400">{{ $report->memo_no }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-2 py-2 align-middle text-[12px] text-slate-600">
                            {{ $report->periodLabel() }}
                        </td>
                        <td class="px-2 py-2 align-middle">
                            @if ($isDraft)
                                <span class="inline-flex rounded bg-sky-50 px-1.5 py-0.5 text-[10px] font-semibold text-sky-800">Ongoing</span>
                            @else
                                <span class="inline-flex rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-800">Done</span>
                            @endif
                        </td>
                        <td class="hidden px-2 py-2 align-middle md:table-cell">
                            @if ($isDraft)
                                <div class="flex min-w-[7rem] items-center gap-1.5">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-[#2b579a]" style="width: {{ min(100, (int) $report->progress_pct) }}%"></div>
                                    </div>
                                    <span class="w-8 text-right text-[10px] font-medium tabular-nums text-slate-500">{{ $report->progress_pct }}%</span>
                                </div>
                            @else
                                <span class="text-[11px] text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="hidden whitespace-nowrap px-2 py-2 align-middle text-[11px] text-slate-500 lg:table-cell">
                            @if ($isDraft && $report->last_saved_at)
                                {{ $report->last_saved_at->timezone('Asia/Dhaka')->format('d M, h:i A') }}
                            @elseif (! $isDraft && $report->completed_at)
                                {{ $report->completed_at->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-3 py-2 align-middle text-right sm:px-4">
                            <div class="inline-flex flex-wrap items-center justify-end gap-1">
                                @if ($isDraft)
                                    <button
                                        type="button"
                                        wire:click="resumeReport({{ $report->id }})"
                                        class="inline-flex h-7 items-center rounded-md bg-[#2b579a] px-2.5 text-[11px] font-semibold text-white hover:bg-[#204072]"
                                    >Continue</button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="resumeReport({{ $report->id }})"
                                        class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50"
                                    >Open</button>
                                @endif
                                <a
                                    href="{{ route('audits.checklist', $report) }}"
                                    class="inline-flex h-7 items-center rounded-md border border-slate-200 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                                >Checklist</a>
                                <a
                                    href="{{ route('audit-findings.entry', ['report' => $report->id]) }}"
                                    class="inline-flex h-7 items-center rounded-md border border-slate-200 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                                >Findings</a>
                                @if ($isDraft)
                                    <button
                                        type="button"
                                        wire:click="deleteDraft({{ $report->id }})"
                                        wire:confirm="Delete this draft?"
                                        class="inline-flex h-7 items-center rounded-md px-2 text-[11px] font-medium text-rose-600 hover:bg-rose-50"
                                    >Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center">
                            <p class="text-[13px] font-semibold text-slate-700">No reports match</p>
                            <p class="mt-1 text-[11px] text-slate-500">Change filters or clear them to see everything</p>
                            <button type="button" wire:click="clearReportListFilters" class="mt-3 inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Clear filters</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($allReports->isNotEmpty())
        <div class="border-t border-slate-100 bg-slate-50/50 px-3 py-1.5 text-[10px] text-slate-500 sm:px-4">
            Showing {{ $allReports->count() }}
            · {{ $ongoingReports->count() }} ongoing
            · {{ $completedReports->count() }} done
        </div>
    @endif
</div>
