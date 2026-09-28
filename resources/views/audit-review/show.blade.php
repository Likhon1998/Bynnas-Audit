<x-app-layout>
    @php
        $annotationColors = [
            'yellow' => '#fef08a',
            'rose' => '#fecdd3',
            'sky' => '#bae6fd',
            'lime' => '#bef264',
            'orange' => '#fed7aa',
        ];
    @endphp
    @php
        $btn = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3 text-[12px] font-semibold text-white transition hover:-translate-y-0.5 disabled:opacity-60';
        $tones = [
            'navy' => 'bg-[#2b579a] hover:bg-[#204072] shadow-[0_6px_14px_rgba(43,87,154,0.35)]',
            'emerald' => 'bg-emerald-600 hover:bg-emerald-700 shadow-[0_6px_14px_rgba(5,150,105,0.35)]',
            'teal' => 'bg-teal-600 hover:bg-teal-700 shadow-[0_6px_14px_rgba(13,148,136,0.35)]',
            'sky' => 'bg-sky-600 hover:bg-sky-700 shadow-[0_6px_14px_rgba(2,132,199,0.35)]',
        ];
        $isResubmit = ! empty($reviewContext['is_resubmit']);
        $timeline = $reviewContext['timeline'] ?? [];
        $roundLabel = $reviewContext['round_label'] ?? $report->currentReviewRoundLabel();
        $statusChip = match (true) {
            $report->isReviewed() && $report->review_perfect => 'bg-teal-50 text-teal-700 ring-teal-200',
            $report->isReviewed() => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            $report->isChangesRequested() => 'bg-rose-50 text-rose-700 ring-rose-200',
            $report->isInReview() && $report->isReviewReady() => 'bg-sky-50 text-sky-700 ring-sky-200',
            default => 'bg-amber-50 text-amber-800 ring-amber-200',
        };
        $sideStart = $isResubmit && $canAct && ! $reviewReady ? 'changes' : 'marks';
    @endphp
    <div class="space-y-3 px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <header class="flex flex-wrap items-center gap-x-3 gap-y-2 rounded-xl border border-slate-200 border-l-4 border-l-[#2b579a] bg-white px-3 py-2 shadow-sm">
            <a
                href="{{ route('audit-review.index') }}"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:-translate-y-0.5 hover:border-[#2b579a] hover:text-[#2b579a]"
                title="Back to Review Panel"
                aria-label="Back to Review Panel"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.5 15 7.5 10l5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-[14px] font-semibold text-navy-900" title="{{ $report->entityDisplayName() }} · {{ $report->periodLabel() }}">
                    {{ $report->entityDisplayName() }}
                    <span class="font-medium text-slate-400">·</span>
                    <span class="font-medium text-slate-600">{{ $report->periodLabel() }}</span>
                </h1>
                <p class="mt-0.5 truncate text-[11.5px] text-slate-500">
                    <span class="text-slate-400">#{{ $report->id }}</span>
                    <span class="mx-1 text-slate-300">|</span>Maker <span class="font-medium text-slate-700">{{ $report->user?->name ?: '—' }}</span>
                    <span class="mx-1 text-slate-300">|</span>Reviewer <span class="font-medium text-slate-700">{{ $report->reviewer?->name ?: '—' }}</span>
                    @if ($report->isReviewed())
                        <span class="mx-1 text-slate-300">|</span>Locked <span class="font-medium text-slate-700">{{ bd_date($report->reviewed_at) }}</span>
                    @endif
                    @if ($report->review_cc_superadmin)
                        <span class="mx-1 text-slate-300">|</span><span class="font-medium text-rose-600">Flagged for Super Admin</span>
                    @endif
                </p>
            </div>
            <div class="flex shrink-0 flex-col items-end gap-1 sm:flex-row sm:items-center sm:gap-1.5">
                <span class="rounded-md px-1.5 py-0.5 text-[11px] font-semibold ring-1 {{ $statusChip }}">{{ $report->statusLabel() }}</span>
                <span class="rounded-md px-1.5 py-0.5 text-[11px] font-semibold ring-1 {{ $isResubmit ? 'bg-amber-50 text-amber-800 ring-amber-200' : 'bg-violet-50 text-violet-700 ring-violet-200' }}">{{ $roundLabel }}</span>
            </div>
            @if ($canDownloadReviewPack)
                <a href="{{ $downloadReviewUrl }}" class="{{ $btn }} {{ $tones['sky'] }} shrink-0">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 3v10m0 0 4-4m-4 4-4-4M4 16h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Download PDF + comments
                </a>
            @endif
        </header>

        @if (session('status'))
            <div data-flash class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-[12px] font-medium text-emerald-800">
                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>{{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-[12px] font-medium text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div
            class="grid items-start gap-3 lg:grid-cols-[minmax(0,1fr)_280px] xl:grid-cols-[minmax(0,1fr)_320px]"
            x-data="reviewAnnotator({
                canAnnotate: @js((bool) $canAnnotate),
                storeUrl: @js($storeAnnotationUrl),
                updateUrlTemplate: @js(route('audit-review.annotations.update', ['report' => $report->id, 'annotation' => '__ID__'])),
                destroyUrlTemplate: @js(route('audit-review.annotations.destroy', ['report' => $report->id, 'annotation' => '__ID__'])),
                snapshotUrlTemplate: @js(route('audit-review.annotations.snapshot', ['report' => $report->id, 'annotation' => '__ID__'])),
                csrf: @js(csrf_token()),
                initial: @js($annotations),
                colors: @js($annotationColors),
            })"
            x-init="init()"
        >
            <div class="xl:col-span-full" x-show="uiError" x-cloak>
                <div class="mb-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[12px] text-rose-800" x-text="uiError"></div>
            </div>
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/80 px-3 py-2">
                    <div>
                        <p class="text-[13px] font-semibold text-navy-900">Full report</p>
                        @if ($canAnnotate)
                            <p class="text-[13px] text-slate-500">
                                <span x-show="tool === 'text'">Select text to highlight, or switch to Draw area</span>
                                <span x-show="tool === 'area'" x-cloak>Drag on the report to box the issue area, then add a note</span>
                            </p>
                        @else
                            <p class="text-[13px] text-slate-500">Complete audit document</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($canAnnotate)
                            <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5">
                                <button
                                    type="button"
                                    class="rounded-md px-2.5 py-1 text-[12px] font-semibold transition"
                                    :class="tool === 'text' ? 'bg-[#1b3a70] text-white shadow-[0_6px_14px_rgba(27,58,112,0.3)]' : 'text-slate-600 hover:bg-slate-50'"
                                    @click="setTool('text')"
                                >Select text</button>
                                <button
                                    type="button"
                                    class="rounded-md px-2.5 py-1 text-[12px] font-semibold transition"
                                    :class="tool === 'area' ? 'bg-[#1b3a70] text-white shadow-[0_6px_14px_rgba(27,58,112,0.3)]' : 'text-slate-600 hover:bg-slate-50'"
                                    @click="setTool('area')"
                                >Draw area</button>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="relative max-h-[min(82vh,980px)] overflow-y-auto bg-[#8d8d8d] p-3 sm:p-4" x-ref="scroller" @scroll.passive="onScroll()">
                    @if ($previewError)
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-[12px] text-rose-800">
                            {{ $previewError }}
                            <a href="{{ $documentUrl }}" target="_blank" class="ml-1 font-semibold underline">Open PDF instead</a>
                        </div>
                    @elseif ($preview === [])
                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-3 text-center text-[12px] text-slate-500">
                            No report content available yet.
                        </div>
                    @else
                        @include('livewire.partials.audit-document-preview-styles')
                        <style>
                            mark.review-mark {
                                border-radius: 2px;
                                padding: 0 1px;
                                cursor: pointer;
                                box-decoration-break: clone;
                                -webkit-box-decoration-break: clone;
                            }
                            mark.review-mark.is-active {
                                outline: 2px solid #2b579a;
                                outline-offset: 1px;
                            }
                            .review-area-box {
                                position: absolute;
                                border: 2.5px solid;
                                background: transparent;
                                border-radius: 4px;
                                box-sizing: border-box;
                                cursor: pointer;
                                pointer-events: auto;
                            }
                            .review-area-box.is-active {
                                box-shadow: 0 0 0 2px #2b579a;
                            }
                            .review-area-draft {
                                position: absolute;
                                border: 2px dashed #2b579a;
                                background: transparent;
                                border-radius: 4px;
                                pointer-events: none;
                                box-sizing: border-box;
                            }
                        </style>
                        <div class="relative mx-auto w-full max-w-[236mm]" x-ref="stage">
                            <div
                                class="audit-doc-preview"
                                x-ref="doc"
                                @mouseup="onTextMouseUp($event)"
                                @click="onMarkClick($event)"
                            >
                                @include('livewire.partials.audit-document-preview-pages', $preview)
                            </div>

                            <div class="pointer-events-none absolute inset-0 z-10" x-ref="areas">
                                <template x-for="ann in areaAnnotations" :key="'area-'+ann.id">
                                    <div
                                        class="review-area-box"
                                        :class="activeId === ann.id ? 'is-active' : ''"
                                        :data-ann-id="ann.id"
                                        :style="areaStyle(ann)"
                                        :title="ann.body || 'Marked area'"
                                        @click.stop="focusAnnotation(ann.id)"
                                    >
                                        <span
                                            class="absolute -top-2 -right-2 flex h-5 min-w-5 items-center justify-center rounded-full px-1 text-xs font-bold text-white shadow"
                                            :style="`background:${ann.border || '#ca8a04'}`"
                                            x-text="annNumber(ann.id)"
                                        ></span>
                                    </div>
                                </template>
                                <div
                                    class="review-area-draft"
                                    x-show="draft.visible"
                                    x-cloak
                                    :style="`left:${draft.x}%;top:${draft.y}%;width:${draft.w}%;height:${draft.h}%;`"
                                ></div>
                            </div>

                            <div
                                x-show="canAnnotate && tool === 'area'"
                                x-cloak
                                class="absolute inset-0 z-20 cursor-crosshair"
                                style="touch-action: none;"
                                @mousedown.prevent="startDraw($event)"
                                @mousemove.prevent="moveDraw($event)"
                                @mouseup.prevent="endDraw($event)"
                                @mouseleave="cancelDrawIfNeeded($event)"
                            ></div>
                        </div>
                    @endif

                    <div
                        x-show="composer.open"
                        x-cloak
                        x-transition
                        class="fixed z-50 w-[min(320px,calc(100vw-1.5rem))] rounded-xl border border-slate-200 bg-white p-3 shadow-xl"
                        :style="`left:${composer.x}px;top:${composer.y}px`"
                        @mousedown.stop
                    >
                        <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500" x-text="composer.type === 'area' ? 'Area note' : 'Text mark'"></p>
                        <p class="mb-2 line-clamp-2 text-[13px] text-slate-500" x-text="composer.quote"></p>
                        <div class="mb-2 flex items-center gap-1.5">
                            <template x-for="(bg, key) in colors" :key="key">
                                <button
                                    type="button"
                                    class="h-6 w-6 rounded-full border border-slate-300"
                                    :class="composer.color === key ? 'ring-2 ring-navy-900 ring-offset-1' : ''"
                                    :style="`background:${bg}`"
                                    @click="composer.color = key"
                                    :title="key"
                                ></button>
                            </template>
                        </div>
                        <textarea
                            x-model="composer.body"
                            rows="3"
                            class="mb-2 w-full rounded-lg border-slate-200 text-[12px]"
                            :placeholder="composer.type === 'area' ? 'What is wrong in this area?' : 'Comment on this text (optional)'"
                            @keydown.ctrl.enter.prevent="saveComposer()"
                        ></textarea>
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button" class="h-8 rounded-md px-2.5 text-[13px] font-medium text-slate-600 hover:bg-slate-50" @click="hideComposer()">Cancel</button>
                            <button
                                type="button"
                                class="inline-flex h-8 items-center rounded-md bg-navy-900 px-3 text-[13px] font-semibold text-white hover:bg-slate-800 disabled:opacity-50"
                                :disabled="composer.saving || (composer.type === 'area' && !String(composer.body || '').trim())"
                                @click="saveComposer()"
                            >
                                <span x-text="composer.saving ? 'Saving…' : 'Save note'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="space-y-3 lg:sticky lg:top-3 lg:self-start" x-data="{ side: @js($sideStart) }">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex gap-1 border-b border-slate-100 bg-slate-50/70 p-1">
                        <button type="button" class="inline-flex h-7 flex-1 items-center justify-center gap-1 rounded-lg text-[12px] font-semibold transition" :class="side === 'marks' ? 'bg-[#1b3a70] text-white shadow-[0_6px_14px_rgba(27,58,112,0.3)]' : 'text-slate-600 hover:bg-white'" @click="side = 'marks'">
                            Marks
                            <span class="rounded-full px-1.5 text-[10px] font-bold" :class="side === 'marks' ? 'bg-white/20' : 'bg-slate-200 text-slate-700'" x-text="annotations.length"></span>
                        </button>
                        @if ($isResubmit)
                            <button type="button" class="inline-flex h-7 flex-1 items-center justify-center gap-1 rounded-lg text-[12px] font-semibold transition" :class="side === 'changes' ? 'bg-[#1b3a70] text-white shadow-[0_6px_14px_rgba(27,58,112,0.3)]' : 'text-slate-600 hover:bg-white'" @click="side = 'changes'">
                                Changes
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            </button>
                        @endif
                        @if ($timeline)
                            <button type="button" class="inline-flex h-7 flex-1 items-center justify-center gap-1 rounded-lg text-[12px] font-semibold transition" :class="side === 'timeline' ? 'bg-[#1b3a70] text-white shadow-[0_6px_14px_rgba(27,58,112,0.3)]' : 'text-slate-600 hover:bg-white'" @click="side = 'timeline'">
                                Timeline
                                <span class="rounded-full px-1.5 text-[10px] font-bold" :class="side === 'timeline' ? 'bg-white/20' : 'bg-slate-200 text-slate-700'">{{ count($timeline) }}</span>
                            </button>
                        @endif
                    </div>

                    <div x-show="side === 'marks'">
                        <div class="border-b border-slate-100 px-3 py-2">
                            <p class="text-[12px] font-semibold text-navy-900">Marks and comments</p>
                            <p class="text-[11px] text-slate-500">
                                @if (! $isResubmit && $canAnnotate)
                                    1st review · select text or draw an area on the report, then finish below.
                                @else
                                    Click a mark to jump to it on the report.
                                @endif
                            </p>
                        </div>
                    <ul class="max-h-[min(70vh,820px)] divide-y divide-slate-100 overflow-y-auto">
                        <template x-for="ann in annotations" :key="ann.id">
                            <li class="px-3 py-2.5 hover:bg-slate-50">
                                <button type="button" class="w-full text-left" @click="focusAnnotation(ann.id)" x-show="editingId !== ann.id">
                                    <div class="mb-1 flex items-center gap-1.5">
                                        <span class="inline-block h-2.5 w-2.5 rounded-full border border-slate-300" :style="`background:${ann.bg}`"></span>
                                        <span class="rounded bg-slate-100 px-1 text-xs font-semibold uppercase text-slate-500" x-text="ann.type === 'area' ? 'Area' : 'Text'"></span>
                                        <span class="text-xs font-semibold text-slate-600" x-text="ann.author"></span>
                                        <span class="text-xs text-slate-500" x-text="ann.created_at"></span>
                                    </div>
                                    <p class="line-clamp-2 text-[13px] text-slate-500" x-text="ann.type === 'area' ? ('Area #' + annNumber(ann.id)) : ann.quote"></p>
                                    <template x-if="ann.snapshot_url">
                                        <img :src="ann.snapshot_url" alt="" class="mt-1.5 max-h-28 w-full rounded border border-slate-200 object-contain object-top bg-slate-50">
                                    </template>
                                    <p class="mt-1 whitespace-pre-wrap text-[12px] text-slate-800" x-show="ann.body" x-text="ann.body"></p>
                                </button>

                                <div x-show="editingId === ann.id" x-cloak class="space-y-2" @click.stop>
                                    <p class="text-[13px] text-slate-500" x-text="ann.type === 'area' ? ('Area #' + annNumber(ann.id)) : ann.quote"></p>
                                    <div class="flex items-center gap-1.5">
                                        <template x-for="(bg, key) in colors" :key="key">
                                            <button
                                                type="button"
                                                class="h-5 w-5 rounded-full border border-slate-300"
                                                :class="(editDrafts[ann.id]?.color || ann.color) === key ? 'ring-2 ring-navy-900 ring-offset-1' : ''"
                                                :style="`background:${bg}`"
                                                @click="setEditColor(ann.id, key)"
                                            ></button>
                                        </template>
                                    </div>
                                    <textarea
                                        rows="3"
                                        class="w-full rounded-lg border-slate-200 text-[12px]"
                                        :value="editDrafts[ann.id]?.body ?? ann.body ?? ''"
                                        @input="onEditInput(ann.id, $event.target.value)"
                                        placeholder="Edit note"
                                    ></textarea>
                                </div>

                                <div class="mt-1 flex items-center gap-2" x-show="canAnnotate">
                                    <button
                                        type="button"
                                        class="text-xs font-semibold text-[#2b579a] hover:underline"
                                        @click="editingId === ann.id ? stopEditing(ann.id) : startEditing(ann)"
                                        x-text="editingId === ann.id ? 'Done' : 'Edit'"
                                    ></button>
                                    <button
                                        type="button"
                                        class="text-xs font-semibold text-rose-600 hover:underline"
                                        @click="removeAnnotation(ann.id)"
                                    >Remove</button>
                                </div>
                            </li>
                        </template>
                        <li x-show="!annotations.length" class="px-3 py-3 text-center text-[13px] text-slate-500">
                            No marks yet. Select text or draw an area on the report.
                        </li>
                    </ul>
                    </div>

                    @if ($isResubmit)
                        <div x-show="side === 'changes'" x-cloak class="max-h-[min(70vh,820px)] space-y-2.5 overflow-y-auto p-3">
                            <div>
                                <p class="text-[12px] font-semibold text-navy-900">{{ $roundLabel }} — compare asks vs fixes</p>
                                <p class="text-[11px] text-slate-500">What you asked for last round, and what the maker changed.</p>
                            </div>
                            @if (! empty($reviewContext['maker_note']))
                                <div class="rounded-xl border border-amber-200 bg-amber-50/70 px-3 py-2">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700">Maker resubmit note</p>
                                    <p class="mt-0.5 text-[12px] font-medium text-slate-900">{{ $reviewContext['maker_note'] }}</p>
                                </div>
                            @endif
                            @if (! empty($reviewContext['change_summary']))
                                <div class="rounded-xl border border-sky-200 bg-sky-50/60 px-3 py-2">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700">What changed in the report</p>
                                    <ul class="mt-1 space-y-0.5 text-[12px] text-slate-800">
                                        @foreach ($reviewContext['change_summary'] as $line)
                                            <li class="flex gap-1.5"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-sky-500"></span>{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="rounded-xl border border-rose-200 bg-rose-50/50 px-3 py-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-700">Earlier asks (previous round)</p>
                                @forelse ($reviewContext['prior_asks'] as $ask)
                                    <div class="mt-1.5 border-t border-rose-100 pt-1.5 first:mt-1 first:border-0 first:pt-0">
                                        <p class="text-[12px] font-medium text-slate-900">{{ $ask['body'] ?: ($ask['quote'] ?: 'Mark') }}</p>
                                        <p class="text-[11px] text-slate-500">Round {{ $ask['review_round'] ?? '?' }} · {{ $ask['author'] ?? 'Reviewer' }}@if (! empty($ask['addressed'])) · <span class="font-semibold text-emerald-700">Maker marked done</span>@endif</p>
                                    </div>
                                @empty
                                    <p class="mt-1 text-[12px] text-slate-500">No earlier marks stored for a prior round.</p>
                                @endforelse
                            </div>
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 px-3 py-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Maker marked done</p>
                                @forelse ($reviewContext['addressed_asks'] as $ask)
                                    <p class="mt-1 text-[12px] font-medium text-slate-900 line-through decoration-emerald-600/50">{{ $ask['body'] ?: ($ask['quote'] ?: 'Mark') }}</p>
                                @empty
                                    <p class="mt-1 text-[12px] text-slate-500">The maker did not tick any marks as done.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    @if ($timeline)
                        <div x-show="side === 'timeline'" x-cloak class="max-h-[min(70vh,820px)] overflow-y-auto p-3">
                            <ol class="relative space-y-3 border-l border-slate-200 pl-4">
                                @foreach ($timeline as $ev)
                                    <li class="relative">
                                        <span class="absolute -left-[21px] top-1 h-2.5 w-2.5 rounded-full ring-2 ring-white {{ ($ev['round'] ?? 1) >= 2 ? 'bg-amber-500' : 'bg-violet-500' }}"></span>
                                        <p class="text-[12px] font-semibold text-navy-900">{{ $ev['label'] }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $ev['round_label'] }} · {{ $ev['actor'] }} · {{ $ev['at'] }}</p>
                                        @if (! empty($ev['body']))
                                            <p class="mt-0.5 whitespace-pre-line text-[12px] text-slate-600">{{ $ev['body'] }}</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                </div>

                @if ($report->isReviewed())
                    <div class="rounded-2xl border px-3.5 py-3 shadow-sm {{ $report->review_perfect ? 'border-teal-200 bg-teal-50' : 'border-emerald-200 bg-emerald-50' }}">
                        <p class="text-[12px] font-semibold {{ $report->review_perfect ? 'text-teal-900' : 'text-emerald-900' }}">
                            {{ $report->review_perfect ? 'Totally fixed · 100% perfect' : 'Confirmed' }}
                        </p>
                        <p class="mt-0.5 text-[11px] {{ $report->review_perfect ? 'text-teal-800' : 'text-emerald-800' }}">Reviewed and locked on {{ bd_date($report->reviewed_at) }}.</p>
                    </div>
                @endif

                @if ($canAct)
                    <div class="space-y-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Finish review</p>
                        @if (! $reviewReady)
                            <form
                                method="POST"
                                action="{{ route('audit-review.done', $report) }}"
                                x-ref="doneForm"
                                @submit.prevent="askReviewDone()"
                            >
                                @csrf
                                <button type="submit" class="{{ $btn }} {{ $tones['emerald'] }} w-full">Review done</button>
                            </form>
                            <form
                                method="POST"
                                action="{{ route('audit-review.totally-fixed', $report) }}"
                                data-bynnas-confirm="This marks the report as Totally fixed · 100% perfect and locks it. No send-back to the maker."
                                data-bynnas-confirm-title="Grant as totally fixed?"
                                data-bynnas-confirm-ok="Totally fixed"
                                data-bynnas-confirm-tone="emerald"
                            >
                                @csrf
                                <button type="submit" class="{{ $btn }} {{ $tones['teal'] }} w-full">Totally fixed · 100%</button>
                            </form>
                            <p class="text-[11px] leading-snug text-slate-500">Needs fixes? Choose <span class="font-semibold text-slate-700">Review done</span>, then send it to the maker. Already perfect? Choose <span class="font-semibold text-slate-700">Totally fixed</span>.</p>
                        @else
                            <p class="rounded-lg bg-sky-50 px-2.5 py-2 text-[11px] leading-snug text-sky-900">Review is ready. Send it to the maker or confirm it from the Reviewed tab.</p>
                            <a href="{{ route('audit-review.index', ['tab' => 'reviewed']) }}" class="{{ $btn }} {{ $tones['navy'] }} w-full">Open Reviewed tab</a>
                            <form
                                method="POST"
                                action="{{ route('audit-review.totally-fixed', $report) }}"
                                data-bynnas-confirm="This marks the report as Totally fixed · 100% perfect and locks it."
                                data-bynnas-confirm-title="Grant as totally fixed?"
                                data-bynnas-confirm-ok="Totally fixed"
                                data-bynnas-confirm-tone="emerald"
                            >
                                @csrf
                                <button type="submit" class="{{ $btn }} {{ $tones['teal'] }} w-full">Totally fixed · 100%</button>
                            </form>
                        @endif
                    </div>
                @endif
            </aside>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        function reviewAnnotator(cfg) {
            return {
                canAnnotate: !!cfg.canAnnotate,
                storeUrl: cfg.storeUrl,
                updateUrlTemplate: cfg.updateUrlTemplate,
                destroyUrlTemplate: cfg.destroyUrlTemplate,
                snapshotUrlTemplate: cfg.snapshotUrlTemplate,
                csrf: cfg.csrf,
                colors: cfg.colors || {},
                annotations: Array.isArray(cfg.initial) ? cfg.initial : [],
                tool: 'area',
                activeId: null,
                editingId: null,
                uiError: '',
                csrfToken() {
                    return window.bynnasCsrf?.token?.()
                        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        || this.csrf
                        || '';
                },
                async csrfHeaders() {
                    if (window.bynnasCsrf?.refresh) {
                        await window.bynnasCsrf.refresh({ force: false });
                    }
                    return {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    };
                },
                editDrafts: {},
                dirtyIds: {},
                savingIds: {},
                autoSaveTimer: null,
                selectionClip: null,
                composer: {
                    open: false, x: 0, y: 0, type: 'text', quote: '', prefix: '', suffix: '',
                    body: '', color: 'rose', saving: false,
                    rect_x: null, rect_y: null, rect_w: null, rect_h: null,
                },
                draft: { visible: false, x: 0, y: 0, w: 0, h: 0 },
                drawing: false,
                drawOrigin: null,

                get areaAnnotations() {
                    return this.annotations.filter(a => a.type === 'area');
                },

                init() {
                    this.$nextTick(async () => {
                        this.fitDocument();
                        this.paintAll();
                        await this.backfillMissingSnapshots();
                    });
                    window.addEventListener('resize', () => {
                        this.fitDocument();
                        this.paintAll();
                    });
                    if (window.ResizeObserver && this.$refs.scroller) {
                        new ResizeObserver(() => this.fitDocument()).observe(this.$refs.scroller);
                    }
                    if (this.canAnnotate) {
                        this.autoSaveTimer = setInterval(() => this.flushDirtyEdits(), 5000);
                    }
                },

                destroy() {
                    if (this.autoSaveTimer) clearInterval(this.autoSaveTimer);
                },

                fitDocument() {
                    const stage = this.$refs.stage;
                    const scroller = this.$refs.scroller;
                    if (!stage || !scroller) return;
                    const page = stage.querySelector('.page');
                    const natural = Math.max(page ? page.offsetWidth : 0, 794) + 8;
                    const styles = window.getComputedStyle(scroller);
                    const avail = scroller.clientWidth - parseFloat(styles.paddingLeft) - parseFloat(styles.paddingRight);
                    const zoom = Math.max(0.45, Math.min(1, avail / natural));
                    stage.style.width = natural + 'px';
                    stage.style.maxWidth = 'none';
                    stage.style.zoom = zoom < 0.999 ? String(zoom) : '';
                },

                startEditing(ann) {
                    this.editingId = ann.id;
                    this.editDrafts[ann.id] = {
                        body: ann.body || '',
                        color: ann.color || 'yellow',
                    };
                    this.focusAnnotation(ann.id);
                },

                async stopEditing(id) {
                    if (this.dirtyIds[id]) {
                        await this.saveEdit(id);
                    }
                    if (this.editingId === id) this.editingId = null;
                },

                onEditInput(id, value) {
                    if (!this.editDrafts[id]) this.editDrafts[id] = { body: '', color: 'yellow' };
                    this.editDrafts[id].body = value;
                    this.dirtyIds[id] = true;
                },

                setEditColor(id, color) {
                    if (!this.editDrafts[id]) {
                        const ann = this.annotations.find(a => a.id === id);
                        this.editDrafts[id] = { body: ann?.body || '', color };
                    } else {
                        this.editDrafts[id].color = color;
                    }
                    this.dirtyIds[id] = true;
                },

                async flushDirtyEdits() {
                    const ids = Object.keys(this.dirtyIds).filter(id => this.dirtyIds[id]);
                    if (!ids.length) return;
                    for (const id of ids) {
                        await this.saveEdit(Number(id));
                    }
                },

                async saveEdit(id) {
                    if (!this.canAnnotate || this.savingIds[id] || !this.dirtyIds[id]) return;
                    const draft = this.editDrafts[id];
                    const ann = this.annotations.find(a => a.id === id);
                    if (!draft || !ann) return;
                    if (ann.type === 'area' && !String(draft.body || '').trim()) {
                        return;
                    }

                    this.savingIds[id] = true;
                    try {
                        const url = this.updateUrlTemplate.replace('__ID__', String(id));
                        const res = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                ...(await this.csrfHeaders()),
                            },
                            body: JSON.stringify({
                                body: draft.body,
                                color: draft.color,
                            }),
                        });
                        if (!res.ok) throw new Error('Save failed');
                        const data = await res.json();
                        if (data.annotation) {
                            const idx = this.annotations.findIndex(a => a.id === id);
                            if (idx >= 0) this.annotations[idx] = data.annotation;
                            this.editDrafts[id] = {
                                body: data.annotation.body || '',
                                color: data.annotation.color,
                            };
                            this.paintAll();
                        }
                        this.dirtyIds[id] = false;
                    } catch (err) {
                        // Silent autosave — retry on next interval
                    } finally {
                        this.savingIds[id] = false;
                    }
                },

                async openConfirm({ title, message, okLabel = 'OK', tone = 'emerald', action = null }) {
                    if (typeof window.bynnasConfirm !== 'function') {
                        this.uiError = 'Confirm dialog unavailable. Refresh the page and try again.';
                        return;
                    }
                    const ok = await window.bynnasConfirm({ title, message, okLabel, tone });
                    if (ok && typeof action === 'function') {
                        await action();
                    }
                },

                flashError(message) {
                    this.uiError = message || 'Something went wrong.';
                    window.setTimeout(() => {
                        if (this.uiError === message) this.uiError = '';
                    }, 5000);
                },

                async askReviewDone() {
                    await this.flushDirtyEdits();
                    await this.openConfirm({
                        title: 'Review done?',
                        message: 'You can still edit marks after this. On the Reviewed tab: Send to maker (fixes) or Confirm (final).',
                        okLabel: 'Yes, done',
                        tone: 'emerald',
                        action: () => this.$refs.doneForm?.submit(),
                    });
                },

                async askSendToMaker() {
                    await this.flushDirtyEdits();
                    await this.openConfirm({
                        title: 'Send to maker?',
                        message: 'The maker will see this reviewed report with your marks and comments in Review Panel. No email is sent. The review will lock.',
                        okLabel: 'Send now',
                        tone: 'sky',
                        action: () => this.$refs.sendForm?.submit(),
                    });
                },

                setTool(tool) {
                    this.tool = tool;
                    this.hideComposer();
                    window.getSelection()?.removeAllRanges();
                },

                hideComposer() {
                    this.composer.open = false;
                    this.draft.visible = false;
                    this.drawing = false;
                    this.drawOrigin = null;
                },

                onScroll() {
                    if (this.composer.open) this.positionComposerNearDraft();
                },

                annNumber(id) {
                    const areas = this.annotations.filter(a => a.type === 'area');
                    const idx = areas.findIndex(a => a.id === id);
                    return idx >= 0 ? idx + 1 : '';
                },

                areaStyle(ann) {
                    return [
                        `left:${ann.rect_x}%`,
                        `top:${ann.rect_y}%`,
                        `width:${ann.rect_w}%`,
                        `height:${ann.rect_h}%`,
                        `background:transparent`,
                        `border-color:${ann.border || '#ca8a04'}`,
                    ].join(';');
                },

                stagePoint(e) {
                    const stage = this.$refs.stage;
                    if (!stage) return null;
                    const rect = stage.getBoundingClientRect();
                    if (rect.width < 1 || rect.height < 1) return null;
                    const x = ((e.clientX - rect.left) / rect.width) * 100;
                    const y = ((e.clientY - rect.top) / rect.height) * 100;
                    return {
                        x: Math.max(0, Math.min(100, x)),
                        y: Math.max(0, Math.min(100, y)),
                    };
                },

                startDraw(e) {
                    if (!this.canAnnotate || this.tool !== 'area') return;
                    const p = this.stagePoint(e);
                    if (!p) return;
                    this.drawing = true;
                    this.drawOrigin = p;
                    this.draft = { visible: true, x: p.x, y: p.y, w: 0, h: 0 };
                    this.composer.open = false;
                },

                moveDraw(e) {
                    if (!this.drawing || !this.drawOrigin) return;
                    const p = this.stagePoint(e);
                    if (!p) return;
                    const x1 = Math.min(this.drawOrigin.x, p.x);
                    const y1 = Math.min(this.drawOrigin.y, p.y);
                    const x2 = Math.max(this.drawOrigin.x, p.x);
                    const y2 = Math.max(this.drawOrigin.y, p.y);
                    this.draft = { visible: true, x: x1, y: y1, w: x2 - x1, h: y2 - y1 };
                },

                endDraw(e) {
                    if (!this.drawing) return;
                    this.moveDraw(e);
                    this.drawing = false;
                    if (this.draft.w < 1.2 || this.draft.h < 0.8) {
                        this.draft.visible = false;
                        this.drawOrigin = null;
                        return;
                    }
                    this.openAreaComposer();
                },

                cancelDrawIfNeeded() {
                    if (this.drawing && !this.composer.open) {
                        this.drawing = false;
                    }
                },

                openAreaComposer() {
                    this.composer.type = 'area';
                    this.composer.quote = 'Marked area';
                    this.composer.prefix = '';
                    this.composer.suffix = '';
                    this.composer.body = '';
                    this.composer.color = 'rose';
                    this.composer.saving = false;
                    this.composer.rect_x = Number(this.draft.x.toFixed(4));
                    this.composer.rect_y = Number(this.draft.y.toFixed(4));
                    this.composer.rect_w = Number(this.draft.w.toFixed(4));
                    this.composer.rect_h = Number(this.draft.h.toFixed(4));
                    this.positionComposerNearDraft();
                    this.composer.open = true;
                },

                positionComposerNearDraft() {
                    const stage = this.$refs.stage;
                    if (!stage) return;
                    const rect = stage.getBoundingClientRect();
                    const width = 320;
                    const boxLeft = rect.left + (rect.width * (this.draft.x / 100));
                    const boxTop = rect.top + (rect.height * (this.draft.y / 100));
                    const boxBottom = rect.top + (rect.height * ((this.draft.y + this.draft.h) / 100));
                    let x = boxLeft + (rect.width * (this.draft.w / 100) / 2) - (width / 2);
                    x = Math.max(12, Math.min(x, window.innerWidth - width - 12));
                    let y = boxBottom + 10;
                    if (y + 240 > window.innerHeight) y = Math.max(12, boxTop - 240);
                    this.composer.x = x;
                    this.composer.y = y;
                },

                onTextMouseUp(e) {
                    if (!this.canAnnotate || this.tool !== 'text') return;
                    const sel = window.getSelection();
                    if (!sel || sel.isCollapsed || !sel.rangeCount) return;
                    const range = sel.getRangeAt(0);
                    const root = this.$refs.doc;
                    if (!root || !root.contains(range.commonAncestorContainer)) return;
                    const quote = String(sel.toString() || '').replace(/\s+/g, ' ').trim();
                    if (quote.length < 2) return;

                    const full = this.plainText(root);
                    let idx = full.indexOf(quote);
                    if (idx < 0) idx = full.indexOf(String(sel.toString() || '').trim());
                    const prefix = idx >= 0 ? full.slice(Math.max(0, idx - 40), idx) : '';
                    const suffix = idx >= 0 ? full.slice(idx + quote.length, idx + quote.length + 40) : '';

                    this.composer.type = 'text';
                    this.composer.quote = quote;
                    this.composer.prefix = prefix;
                    this.composer.suffix = suffix;
                    this.composer.body = '';
                    this.composer.color = 'yellow';
                    this.composer.saving = false;
                    this.composer.rect_x = null;
                    this.composer.rect_y = null;
                    this.composer.rect_w = null;
                    this.composer.rect_h = null;
                    this.draft.visible = false;

                    const rect = range.getBoundingClientRect();
                    const stageEl = this.$refs.stage;
                    const stage = stageEl?.getBoundingClientRect();
                    if (stage && stage.width > 0 && stage.height > 0) {
                        const padX = 18;
                        const padY = 14;
                        let left = rect.left - stage.left - padX;
                        let top = rect.top - stage.top - padY;
                        let width = rect.width + padX * 2;
                        let height = rect.height + padY * 2;
                        left = Math.max(0, left);
                        top = Math.max(0, top);
                        width = Math.min(width, stage.width - left);
                        height = Math.min(height, stage.height - top);
                        this.selectionClip = {
                            x: (left / stage.width) * 100,
                            y: (top / stage.height) * 100,
                            w: Math.max(0.5, (width / stage.width) * 100),
                            h: Math.max(0.5, (height / stage.height) * 100),
                        };
                    } else {
                        this.selectionClip = null;
                    }

                    const width = 320;
                    let x = rect.left + (rect.width / 2) - (width / 2);
                    x = Math.max(12, Math.min(x, window.innerWidth - width - 12));
                    let y = rect.bottom + 10;
                    if (y + 220 > window.innerHeight) y = Math.max(12, rect.top - 230);
                    this.composer.x = x;
                    this.composer.y = y;
                    this.composer.open = true;
                },

                onMarkClick(e) {
                    const mark = e.target.closest('mark.review-mark');
                    if (!mark) return;
                    const id = Number(mark.getAttribute('data-ann-id') || 0);
                    if (id) this.focusAnnotation(id);
                },

                async waitHtml2Canvas(tries = 50) {
                    for (let i = 0; i < tries; i++) {
                        if (typeof window.html2canvas === 'function') return true;
                        await new Promise((r) => setTimeout(r, 120));
                    }
                    return false;
                },

                clipFromStagePercent(xPct, yPct, wPct, hPct, padX = 1.2, padY = 1) {
                    return {
                        x: Math.max(0, xPct - padX),
                        y: Math.max(0, yPct - padY),
                        w: Math.min(100, wPct + padX * 2),
                        h: Math.min(100, hPct + padY * 2),
                    };
                },

                clipFromElement(el) {
                    const stageEl = this.$refs.stage;
                    if (!el || !stageEl) return null;
                    const stage = stageEl.getBoundingClientRect();
                    const rect = el.getBoundingClientRect();
                    if (stage.width < 1 || stage.height < 1) return null;
                    const padX = 16;
                    const padY = 12;
                    let left = rect.left - stage.left - padX;
                    let top = rect.top - stage.top - padY;
                    let width = rect.width + padX * 2;
                    let height = rect.height + padY * 2;
                    left = Math.max(0, left);
                    top = Math.max(0, top);
                    width = Math.min(width, stage.width - left);
                    height = Math.min(height, stage.height - top);
                    if (width < 4 || height < 4) return null;
                    return {
                        x: (left / stage.width) * 100,
                        y: (top / stage.height) * 100,
                        w: (width / stage.width) * 100,
                        h: (height / stage.height) * 100,
                    };
                },

                async renderStageCrop(clip) {
                    if (!clip || typeof window.html2canvas !== 'function') return null;
                    const stage = this.$refs.stage;
                    if (!stage) return null;
                    const zoom = stage.style.zoom;
                    stage.style.zoom = '';
                    try {
                        const full = await window.html2canvas(stage, {
                            scale: 1.25,
                            useCORS: true,
                            logging: false,
                            backgroundColor: '#ffffff',
                            ignoreElements: (el) => {
                                if (!(el instanceof HTMLElement)) return false;
                                if (el.classList.contains('cursor-crosshair')) return true;
                                if (el.classList.contains('review-area-draft')) return true;
                                try {
                                    if (window.getComputedStyle(el).position === 'fixed') return true;
                                } catch (e) {}
                                return false;
                            },
                        });
                        let sx = (clip.x / 100) * full.width;
                        let sy = (clip.y / 100) * full.height;
                        let sw = (clip.w / 100) * full.width;
                        let sh = (clip.h / 100) * full.height;
                        sx = Math.max(0, sx);
                        sy = Math.max(0, sy);
                        sw = Math.min(sw, full.width - sx);
                        sh = Math.min(sh, full.height - sy);
                        if (sw < 8 || sh < 8) return null;

                        const maxW = 560;
                        const scale = sw > maxW ? maxW / sw : 1;
                        const out = document.createElement('canvas');
                        out.width = Math.max(1, Math.round(sw * scale));
                        out.height = Math.max(1, Math.round(sh * scale));
                        const ctx = out.getContext('2d');
                        if (!ctx) return null;
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, out.width, out.height);
                        ctx.drawImage(full, sx, sy, sw, sh, 0, 0, out.width, out.height);
                        ctx.strokeStyle = '#e11d48';
                        ctx.lineWidth = 3;
                        ctx.strokeRect(1.5, 1.5, out.width - 3, out.height - 3);
                        return out.toDataURL('image/jpeg', 0.78);
                    } catch (e) {
                        return null;
                    } finally {
                        stage.style.zoom = zoom;
                    }
                },

                async captureExistingAnnotation(ann) {
                    let clip = null;
                    if (ann.type === 'area' && ann.rect_w != null) {
                        clip = this.clipFromStagePercent(
                            Number(ann.rect_x),
                            Number(ann.rect_y),
                            Number(ann.rect_w),
                            Number(ann.rect_h),
                            2,
                            1.5
                        );
                    } else {
                        const mark = this.$refs.doc?.querySelector(`mark.review-mark[data-ann-id="${ann.id}"]`);
                        clip = this.clipFromElement(mark);
                    }
                    return await this.renderStageCrop(clip);
                },

                async backfillMissingSnapshots() {
                    if (!this.snapshotUrlTemplate || !this.$refs.stage) return;
                    const missing = this.annotations.filter((a) => !a.snapshot_url);
                    if (!missing.length) return;
                    if (! await this.waitHtml2Canvas()) return;
                    await new Promise((r) => setTimeout(r, 500));
                    this.paintAll();
                    await this.$nextTick();

                    for (const ann of missing) {
                        const dataUrl = await this.captureExistingAnnotation(ann);
                        if (!dataUrl) continue;
                        const url = this.snapshotUrlTemplate.replace('__ID__', String(ann.id));
                        try {
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    ...(await this.csrfHeaders()),
                                },
                                body: JSON.stringify({ snapshot: dataUrl }),
                            });
                            if (!res.ok) continue;
                            const data = await res.json();
                            if (data.annotation) {
                                const idx = this.annotations.findIndex((a) => a.id === ann.id);
                                if (idx >= 0) this.annotations[idx] = data.annotation;
                            }
                        } catch (e) {}
                    }
                },

                async captureMarkSnapshot() {
                    let clip = null;
                    if (this.composer.type === 'area') {
                        clip = this.clipFromStagePercent(
                            Number(this.composer.rect_x),
                            Number(this.composer.rect_y),
                            Number(this.composer.rect_w),
                            Number(this.composer.rect_h),
                            1.5,
                            1.2
                        );
                    } else if (this.selectionClip) {
                        clip = this.selectionClip;
                    }
                    return await this.renderStageCrop(clip);
                },

                async saveComposer() {
                    if (!this.canAnnotate || this.composer.saving) return;
                    if (this.composer.type === 'area' && !String(this.composer.body || '').trim()) {
                        this.flashError('Please add a note for this area.');
                        return;
                    }
                    this.composer.saving = true;
                    try {
                        const snapshot = await this.captureMarkSnapshot();
                        const payload = {
                            type: this.composer.type,
                            quote: this.composer.quote,
                            prefix: this.composer.prefix,
                            suffix: this.composer.suffix,
                            body: this.composer.body,
                            color: this.composer.color,
                        };
                        if (snapshot) payload.snapshot = snapshot;
                        if (this.composer.type === 'area') {
                            payload.rect_x = this.composer.rect_x;
                            payload.rect_y = this.composer.rect_y;
                            payload.rect_w = this.composer.rect_w;
                            payload.rect_h = this.composer.rect_h;
                        }
                        const res = await fetch(this.storeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                ...(await this.csrfHeaders()),
                            },
                            body: JSON.stringify(payload),
                        });
                        if (!res.ok) throw new Error('Save failed');
                        const data = await res.json();
                        if (data.annotation) {
                            this.annotations.push(data.annotation);
                            this.paintAll();
                        }
                        window.getSelection()?.removeAllRanges();
                        this.selectionClip = null;
                        this.hideComposer();
                    } catch (err) {
                        this.flashError('Could not save mark. Please try again.');
                    } finally {
                        this.composer.saving = false;
                    }
                },

                async removeAnnotation(id) {
                    if (!this.canAnnotate) return;
                    this.openConfirm({
                        title: 'Remove mark?',
                        message: 'This mark and its comment will be deleted.',
                        okLabel: 'Remove',
                        tone: 'rose',
                        action: () => this.deleteAnnotation(id),
                    });
                },

                async deleteAnnotation(id) {
                    if (this.dirtyIds[id]) {
                        await this.saveEdit(id);
                    }
                    const url = this.destroyUrlTemplate.replace('__ID__', String(id));
                    try {
                        const res = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                ...(await this.csrfHeaders()),
                            },
                        });
                        if (!res.ok) throw new Error('Delete failed');
                        this.annotations = this.annotations.filter(a => a.id !== id);
                        delete this.editDrafts[id];
                        delete this.dirtyIds[id];
                        if (this.editingId === id) this.editingId = null;
                        this.activeId = null;
                        this.paintAll();
                    } catch (err) {
                        this.flashError('Could not remove mark.');
                    }
                },

                focusAnnotation(id) {
                    this.activeId = id;
                    const root = this.$refs.stage || this.$refs.doc;
                    root?.querySelectorAll('.is-active').forEach(el => el.classList.remove('is-active'));
                    const mark = this.$refs.doc?.querySelector(`mark.review-mark[data-ann-id="${id}"]`);
                    const box = this.$refs.areas?.querySelector(`.review-area-box[data-ann-id="${id}"]`);
                    const target = mark || box;
                    if (target) {
                        target.classList.add('is-active');
                        target.scrollIntoView({ behavior: 'auto', block: 'center' });
                    }
                },

                plainText(root) {
                    return String(root.innerText || root.textContent || '').replace(/\s+/g, ' ');
                },

                paintAll() {
                    const root = this.$refs.doc;
                    if (!root) return;
                    this.unwrapMarks(root);
                    const textAnns = this.annotations
                        .filter(a => (a.type || 'text') !== 'area')
                        .sort((a, b) => String(b.quote || '').length - String(a.quote || '').length);
                    textAnns.forEach(ann => this.paintOne(root, ann));
                },

                unwrapMarks(root) {
                    root.querySelectorAll('mark.review-mark').forEach(mark => {
                        const parent = mark.parentNode;
                        while (mark.firstChild) parent.insertBefore(mark.firstChild, mark);
                        parent.removeChild(mark);
                        parent.normalize();
                    });
                },

                paintOne(root, ann) {
                    const quote = String(ann.quote || '').replace(/\s+/g, ' ').trim();
                    if (!quote) return;
                    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
                        acceptNode(node) {
                            if (!node.nodeValue || !node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
                            if (node.parentElement?.closest('mark.review-mark')) return NodeFilter.FILTER_REJECT;
                            return NodeFilter.FILTER_ACCEPT;
                        }
                    });

                    let node;
                    const nodes = [];
                    while ((node = walker.nextNode())) nodes.push(node);

                    let joined = '';
                    const map = [];
                    nodes.forEach(n => {
                        const text = n.nodeValue;
                        for (let i = 0; i < text.length; i++) {
                            const ch = text[i];
                            if (/\s/.test(ch)) {
                                if (joined.length === 0 || joined[joined.length - 1] === ' ') continue;
                                map.push({ node: n, offset: i });
                                joined += ' ';
                            } else {
                                map.push({ node: n, offset: i });
                                joined += ch;
                            }
                        }
                    });

                    let start = -1;
                    const prefix = String(ann.prefix || '').replace(/\s+/g, ' ');
                    const suffix = String(ann.suffix || '').replace(/\s+/g, ' ');
                    if (prefix || suffix) {
                        const needle = prefix + quote + suffix;
                        const at = joined.indexOf(needle);
                        if (at >= 0) start = at + prefix.length;
                    }
                    if (start < 0) start = joined.indexOf(quote);
                    if (start < 0) return;
                    const end = start + quote.length;
                    if (end > map.length) return;

                    const startPos = map[start];
                    const endPos = map[end - 1];
                    if (!startPos || !endPos) return;

                    try {
                        const range = document.createRange();
                        range.setStart(startPos.node, startPos.offset);
                        range.setEnd(endPos.node, endPos.offset + 1);
                        const mark = document.createElement('mark');
                        mark.className = 'review-mark';
                        mark.setAttribute('data-ann-id', String(ann.id));
                        mark.style.background = ann.bg || '#fef08a';
                        if (ann.body) mark.title = ann.body;
                        range.surroundContents(mark);
                    } catch (err) {
                        try {
                            const range = document.createRange();
                            range.setStart(startPos.node, startPos.offset);
                            range.setEnd(endPos.node, endPos.offset + 1);
                            const mark = document.createElement('mark');
                            mark.className = 'review-mark';
                            mark.setAttribute('data-ann-id', String(ann.id));
                            mark.style.background = ann.bg || '#fef08a';
                            if (ann.body) mark.title = ann.body;
                            const frag = range.extractContents();
                            mark.appendChild(frag);
                            range.insertNode(mark);
                        } catch (e2) {}
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
