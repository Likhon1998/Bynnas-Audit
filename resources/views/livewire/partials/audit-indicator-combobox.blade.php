{{-- Searchable indicator picker for finding শিরোনাম / body --}}
{{-- create mode: Save new · after save locked + Edit --}}
{{-- pick mode (Rating Box): only choose already-saved report শিরোনাম · no Save/create --}}
@props([
    'index',
    'value' => '',
    'indicators' => [],
    'collection' => 'financialFindings',
    'sectionIndex' => null,
    'wireKey' => null,
    'locked' => false,
    'code' => null,
    'allowCreate' => true,
])

@php
    $isLocked = (bool) $locked;
    $canCreate = (bool) $allowCreate;
    $displayCode = filled($code) ? (string) $code : '';
@endphp

<div
    wire:key="{{ $wireKey ?? ('fin-ind-'.$index.($isLocked ? '-locked' : '-new')) }}"
    class="relative"
    x-data="{
        open: false,
        editing: false,
        locked: @js($isLocked),
        allowCreate: @js($canCreate),
        q: @js($value),
        savedTitle: @js($value),
        savedCode: @js($displayCode),
        highlight: 0,
        collection: @js($collection),
        sectionIndex: @js($sectionIndex),
        indicators: @js($indicators),
        get isEditing() {
            return !this.locked || this.editing;
        },
        get filtered() {
            const q = this.q.trim().toLowerCase();
            const limit = this.allowCreate ? 8 : 25;
            if (!q) return this.indicators.slice(0, limit);
            return this.indicators.filter((i) => {
                const hay = (i.code + ' ' + i.title + ' ' + (i.category || '')).toLowerCase();
                return hay.includes(q);
            }).slice(0, limit);
        },
        get exactMatch() {
            const q = this.q.trim().toLowerCase();
            if (!q) return null;
            return this.indicators.find((i) => i.title.trim().toLowerCase() === q) || null;
        },
        apply(id, title) {
            if (this.collection === 'reportBlocks') {
                $wire.applyBlockFindingIndicator({{ (int) $index }}, id, title);
            } else if (this.collection === 'statsBlocks') {
                $wire.applyStatsBlockIndicator({{ (int) $index }}, id, title);
            } else if (this.collection === 'reportSections' && this.sectionIndex !== null) {
                $wire.applySectionFindingIndicator(this.sectionIndex, {{ (int) $index }}, id, title);
            } else {
                $wire.applyFindingIndicator(this.collection, {{ (int) $index }}, id, title);
            }
            this.editing = false;
            this.open = false;
        },
        pick(item) {
            this.q = item.title;
            this.open = false;
            this.apply(item.id, item.title);
        },
        commitCustom() {
            if (! this.allowCreate) {
                if (this.exactMatch) this.pick(this.exactMatch);
                return;
            }
            const title = this.q.trim();
            if (!title) return;
            if (this.exactMatch) {
                this.pick(this.exactMatch);
                return;
            }
            this.open = false;
            this.apply(null, title);
        },
        startEdit() {
            this.editing = true;
            this.q = this.savedTitle;
            this.open = !this.allowCreate;
            this.$nextTick(() => this.$refs.input?.focus());
        },
        cancelEdit() {
            this.editing = false;
            this.q = this.savedTitle;
            this.open = false;
        },
        onKey(e) {
            if (!this.isEditing) return;
            const list = this.filtered;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.open = true;
                this.highlight = Math.min(this.highlight + 1, Math.max(list.length - 1, 0));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.highlight = Math.max(this.highlight - 1, 0);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (list[this.highlight]) this.pick(list[this.highlight]);
                else if (this.allowCreate) this.commitCustom();
                else if (this.exactMatch) this.pick(this.exactMatch);
            } else if (e.key === 'Escape') {
                if (this.locked && this.editing) this.cancelEdit();
                else this.open = false;
            }
        }
    }"
    @click.outside="open = false"
>
    {{-- Locked (saved) view --}}
    <div x-show="!isEditing" class="flex items-start gap-2 rounded border border-slate-200 bg-slate-50/80 px-2 py-2">
        <div class="min-w-0 flex-1">
            <p class="m-0 whitespace-pre-wrap text-[11px] font-medium leading-relaxed text-navy-900" x-text="savedTitle"></p>
            <p x-show="savedCode" class="mt-0.5 m-0 font-mono text-[10px] text-slate-500" x-text="savedCode"></p>
        </div>
        <button
            type="button"
            @click="startEdit()"
            class="shrink-0 rounded border border-slate-300 bg-white px-2 py-1 text-[10px] font-semibold text-slate-700 hover:bg-slate-50"
            title="পরিবর্তন"
        >Edit</button>
    </div>

    {{-- Editable (new or editing) view --}}
    <div x-show="isEditing" x-cloak>
        <div class="relative">
            <input
                type="search"
                x-ref="input"
                x-model="q"
                @focus="open = true; highlight = 0"
                @input="open = true; highlight = 0"
                @keydown="onKey($event)"
                :placeholder="allowCreate ? 'Indicator খুঁজুন বা নতুন শিরোনাম লিখুন…' : 'সংরক্ষিত শিরোনাম থেকে বেছে নিন…'"
                class="w-full rounded border border-slate-200 bg-sky-50/40 py-2 pl-2 text-[11px] leading-relaxed focus:border-[#2b579a] focus:ring-[#2b579a]"
                :class="allowCreate ? 'pr-[4.5rem]' : (locked && editing ? 'pr-10' : 'pr-2')"
                autocomplete="off"
            >
            <div class="absolute right-1.5 top-1/2 flex -translate-y-1/2 items-center gap-1">
                <button
                    type="button"
                    x-show="locked && editing"
                    @click="cancelEdit()"
                    class="rounded border border-slate-200 bg-white px-1.5 py-1 text-[10px] font-semibold text-slate-600 hover:bg-slate-50"
                    title="বাতিল"
                >✕</button>
                <button
                    type="button"
                    x-show="allowCreate"
                    @click="commitCustom()"
                    class="rounded bg-[#2b579a] px-2 py-1 text-[10px] font-semibold text-white hover:bg-[#204072]"
                    title="সংরক্ষণ / নতুন যোগ"
                >Save</button>
            </div>
        </div>

        <div
            x-show="open"
            x-cloak
            class="absolute z-30 mt-1 max-h-56 w-full overflow-y-auto rounded-md border border-slate-200 bg-white py-1 shadow-lg"
            style="max-height: 220px;"
        >
            <template x-for="(item, idx) in filtered" :key="item.id">
                <button
                    type="button"
                    @click="pick(item)"
                    @mouseenter="highlight = idx"
                    class="flex w-full flex-col items-start gap-0.5 px-2.5 py-1.5 text-left hover:bg-sky-50"
                    :class="highlight === idx ? 'bg-sky-50' : ''"
                >
                    <span class="text-[11px] font-semibold leading-tight text-navy-900" x-text="item.title"></span>
                    <span class="text-[10px] leading-tight text-slate-500">
                        <span class="font-mono" x-text="item.code"></span>
                        <span x-show="item.category"> · </span>
                        <span x-text="item.category || ''"></span>
                    </span>
                </button>
            </template>

            <button
                type="button"
                x-show="allowCreate && q.trim().length > 0 && !exactMatch"
                @click="commitCustom()"
                class="flex w-full items-start gap-2 border-t border-slate-100 px-2.5 py-2 text-left hover:bg-emerald-50"
            >
                <span class="text-[11px] font-semibold text-emerald-700">
                    + নতুন indicator যোগ করুন:
                    <span class="font-normal" x-text="'“' + q.trim() + '”'"></span>
                </span>
            </button>

            <p x-show="filtered.length === 0 && q.trim().length === 0" class="px-2.5 py-2 text-[11px] text-slate-400">
                <span x-show="allowCreate">Type to search indicators…</span>
                <span x-show="!allowCreate">Findings Matrix থেকে indicator খুঁজুন বা বেছে নিন</span>
            </p>
            <p x-show="!allowCreate && filtered.length === 0 && q.trim().length > 0" class="px-2.5 py-2 text-[11px] text-amber-700">
                মিল পাওয়া যায়নি — Findings Matrix-এর তালিকা থেকে বেছে নিন
            </p>
        </div>
    </div>

    <p class="mt-1 text-[10px] text-slate-400" x-show="isEditing && allowCreate">
        তালিকা থেকে বাছাই করুন · নতুন লিখলে indicator হিসেবে সেভ হবে
    </p>
    <p class="mt-1 text-[10px] text-slate-400" x-show="isEditing && !allowCreate">
        Findings Matrix-এর সব indicator থেকে বেছে নিন (নতুন তৈরি নয় · Save নেই)
    </p>
    <p class="mt-1 text-[10px] text-slate-400" x-show="!isEditing" x-cloak>
        সংরক্ষিত · পরিবর্তন করতে Edit চাপুন
    </p>
</div>
