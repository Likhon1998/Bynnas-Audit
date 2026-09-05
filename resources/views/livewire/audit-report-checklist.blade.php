<div class="px-4 py-5 lg:px-6" style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;">
    @php $latestFile = $files->first(); @endphp

    @if ($viewMode === 'home')
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2">
                    <a
                        href="{{ route('audits.index') }}"
                        class="inline-flex h-7 items-center gap-1 rounded-md border border-slate-200 bg-white px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                    >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        Back to reports
                    </a>
                </div>
                <h1 class="text-[16px] font-semibold text-navy-900">Check List</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    {{ $report->shakha_display_name ?: ($report->shakha?->name ?? 'Branch') }}
                    · {{ $report->periodLabel() }}
                    · Choose headings · fill · save · edit
                </p>
            </div>

            @if ($files->isEmpty())
                <label
                    class="inline-flex h-8 cursor-pointer items-center rounded-md bg-[#2b579a] px-3 text-[12px] font-semibold text-white hover:bg-[#204072]"
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="upload"
                >
                    <span wire:loading.remove wire:target="upload">Check List File</span>
                    <span wire:loading wire:target="upload">Uploading…</span>
                    <input type="file" wire:model="upload" accept=".pdf,.doc,.docx" class="sr-only">
                </label>
            @else
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button
                        type="button"
                        @click="open = !open"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md bg-[#2b579a] px-3 text-[12px] font-semibold text-white hover:bg-[#204072]"
                    >
                        <span wire:loading.remove wire:target="upload">Check List File</span>
                        <span wire:loading wire:target="upload">Uploading…</span>
                        <svg class="h-3.5 w-3.5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div
                        x-show="open"
                        x-cloak
                        @click.outside="open = false"
                        class="absolute right-0 z-20 mt-1 w-40 overflow-hidden rounded-md border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        <button type="button" wire:click="downloadFile({{ $latestFile->id }})" @click="open = false" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-medium text-slate-700 hover:bg-slate-50">Download</button>
                        <label class="flex w-full cursor-pointer items-center px-3 py-2 text-left text-[12px] font-medium text-slate-700 hover:bg-slate-50" @click="open = false">
                            Add new
                            <input type="file" wire:model="upload" accept=".pdf,.doc,.docx" class="sr-only">
                        </label>
                    </div>
                </div>
            @endif
        </div>

        @error('upload')
            <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $message }}</div>
        @enderror

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif

        {{-- Choose headings --}}
        <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3 py-2.5">
                <div>
                    <p class="text-[13px] font-semibold text-navy-900">Headings for this report</p>
                    <p class="text-[10px] text-slate-500">এই শাখার রিপোর্টে যে চেকলিস্টগুলোতে কাজ করবেন — বেছে নিন (এক বা একাধিক)</p>
                </div>
                @if (! $choosingHeadings)
                    <button
                        type="button"
                        wire:click="openHeadingPicker"
                        class="inline-flex h-8 items-center rounded-md bg-[#2b579a] px-3 text-[12px] font-semibold text-white hover:bg-[#204072]"
                    >{{ $selectedFormats->isEmpty() ? 'Choose headings' : 'Change headings' }}</button>
                @endif
            </div>

            @if ($choosingHeadings)
                <div class="border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            type="search"
                            wire:model.live.debounce.250ms="search"
                            placeholder="Search heading…"
                            class="h-8 min-w-[200px] flex-1 rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]"
                        >
                        <button type="button" wire:click="saveHeadingSelection" class="inline-flex h-8 items-center rounded-md bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700">Save selection</button>
                        <button type="button" wire:click="closeHeadingPicker" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                    </div>
                    <p class="mt-1.5 text-[10px] text-slate-500">Tick the headings you work on for this shakha report</p>
                </div>
                <div class="max-h-72 divide-y divide-slate-100 overflow-y-auto">
                    @forelse ($pickerFormats as $format)
                        @php $checked = in_array((int) $format->id, array_map('intval', $pickedFormatIds), true); @endphp
                        <label class="flex cursor-pointer items-start gap-2.5 px-3 py-2.5 hover:bg-slate-50">
                            <input
                                type="checkbox"
                                class="mt-0.5 rounded border-slate-300 text-[#2b579a] focus:ring-[#2b579a]"
                                @checked($checked)
                                wire:click="togglePickFormat({{ $format->id }})"
                            >
                            <span class="min-w-0 flex-1">
                                <span class="mr-1.5 inline-flex h-5 items-center rounded bg-slate-100 px-1.5 text-[9px] font-bold text-slate-600">F{{ $format->format_number }}</span>
                                <span class="text-[12px] font-semibold text-navy-900">{{ $format->heading }}</span>
                            </span>
                        </label>
                    @empty
                        <div class="px-4 py-8 text-center text-[12px] text-slate-400">No heading matched</div>
                    @endforelse
                </div>
            @elseif ($selectedFormats->isEmpty())
                <div class="px-4 py-10 text-center">
                    <p class="text-[13px] font-medium text-slate-600">No headings selected yet</p>
                    <p class="mt-1 text-[12px] text-slate-400">Click <span class="font-semibold">Choose headings</span> — pick one or many</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($selectedFormats as $format)
                        @php
                            $sub = $submissionsByFormat->get($format->id);
                            $status = $sub?->status;
                        @endphp
                        <div class="flex flex-wrap items-center gap-2 px-3 py-2.5">
                            <span class="inline-flex h-7 items-center rounded-md bg-slate-100 px-2 text-[10px] font-bold tabular-nums text-slate-600">
                                Format {{ $format->format_number }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-semibold text-navy-900">{{ $format->heading }}</p>
                                <p class="text-[10px] text-slate-500">
                                    @if ($status === 'evidence')
                                        <span class="font-semibold text-emerald-700">Evidence saved</span>
                                        @if ($sub?->saved_at)
                                            · {{ $sub->saved_at->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}
                                        @endif
                                    @elseif ($status === 'draft')
                                        <span class="font-semibold text-amber-700">Draft</span>
                                        @if ($sub?->saved_at)
                                            · {{ $sub->saved_at->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}
                                        @endif
                                    @else
                                        Not filled yet
                                    @endif
                                </p>
                            </div>
                            <button
                                type="button"
                                wire:click="workOnFormat({{ $format->id }})"
                                class="inline-flex h-8 items-center rounded-md {{ $sub ? 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' : 'bg-[#2b579a] text-white hover:bg-[#204072]' }} px-3 text-[12px] font-semibold"
                            >{{ $sub ? 'Edit' : 'Fill' }}</button>
                            <button
                                type="button"
                                wire:click="removeHeading({{ $format->id }})"
                                wire:confirm="Remove this heading from the report? (Saved evidence stays until you delete it separately.)"
                                class="inline-flex h-8 items-center rounded-md border border-rose-200 px-2 text-[11px] font-medium text-rose-600 hover:bg-rose-50"
                            >Remove</button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <button type="button" wire:click="backHome" class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </button>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold text-slate-500">Format {{ $formatModel?->format_number }} · Fill / Edit</p>
                <p class="truncate text-[13px] font-semibold text-navy-900">{{ $formatModel?->heading }}</p>
            </div>
            <button type="button" wire:click="saveDraft" class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Save draft</button>
            <button type="button" wire:click="saveEvidence" class="inline-flex h-8 items-center rounded-md bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700">Save as evidence</button>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800">{{ session('status') }}</div>
        @endif

        @include('livewire.partials.audit-checklist-format-editor', [
            'formatModel' => $formatModel,
            'definition' => $definition,
            'payload' => $payload,
        ])
    @endif
</div>
