{{-- Editor-only: optional অভিযুক্ত কর্মী picker (hidden until auditor opens it; never in preview/PDF). --}}
@props([
    'blockIndex',
    'people' => [],
    'staffOptions' => [],
    'opened' => false,
])

@php
    $people = array_values((array) $people);
    $staffOptions = array_values((array) $staffOptions);
    $filledPeople = array_values(array_filter($people, function ($person) {
        if (! is_array($person)) {
            return false;
        }

        return trim((string) ($person['name'] ?? '')) !== '' || trim((string) ($person['code'] ?? '')) !== '';
    }));
    $hasAccused = $filledPeople !== [];
    $panelOpen = (bool) $opened;
    $displayPeople = $people !== [] ? $people : [['id' => null, 'code' => '', 'name' => '']];
    $formatPersonLabel = static function (array $p): string {
        $name = trim((string) ($p['name'] ?? ''));
        $code = trim((string) ($p['code'] ?? ''));
        if ($name !== '' && $code !== '') {
            return $code.' — '.$name;
        }

        return $name !== '' ? $name : $code;
    };
    $summary = collect($filledPeople)
        ->map(fn ($p) => $formatPersonLabel($p))
        ->filter()
        ->implode(', ');
@endphp

<div class="mt-1.5 print:hidden" wire:key="obs-people-{{ $blockIndex }}-{{ $panelOpen ? 'open' : 'closed' }}-{{ count($displayPeople) }}">
    @if (! $panelOpen)
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="openObservationPeople({{ (int) $blockIndex }})"
                class="inline-flex h-7 items-center gap-1.5 rounded-md border border-violet-200 bg-white px-2.5 text-[11px] font-semibold text-violet-800 shadow-sm hover:bg-violet-50"
            >
                <span class="text-[12px] leading-none">+</span>
                অভিযুক্ত আছে?
            </button>
            @if ($hasAccused)
                <span class="max-w-[18rem] truncate text-[10px] text-violet-700/90" title="{{ $summary }}">
                    {{ $summary }}
                </span>
                <button
                    type="button"
                    wire:click="openObservationPeople({{ (int) $blockIndex }})"
                    class="text-[10px] font-semibold text-violet-700 hover:underline"
                >সম্পাদনা</button>
            @endif
        </div>
    @else
        <div class="rounded-lg border border-violet-100 bg-violet-50/50 px-2.5 py-2">
            <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-[11px] font-semibold text-violet-950">অভিযুক্ত কর্মী (একাধিক)</p>
                    <p class="text-[10px] text-violet-800/80">ID + নাম · এই শাখার staff · Preview/PDF-এ নয়</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        wire:click="addObservationPerson({{ (int) $blockIndex }})"
                        class="h-7 rounded border border-violet-200 bg-white px-2 text-[11px] font-semibold text-violet-800 hover:bg-violet-50"
                    >+ নাম</button>
                    <button
                        type="button"
                        wire:click="hideObservationPeople({{ (int) $blockIndex }})"
                        class="h-7 rounded px-2 text-[11px] font-medium text-slate-500 hover:bg-white hover:text-slate-700"
                    >বন্ধ</button>
                </div>
            </div>

            <div class="space-y-1.5">
                @foreach ($displayPeople as $pIndex => $person)
                    @php
                        $personCode = trim((string) ($person['code'] ?? ''));
                        $personName = trim((string) ($person['name'] ?? ''));
                        $personLabel = $formatPersonLabel($person);
                    @endphp
                    <div
                        class="relative flex items-start gap-1.5"
                        wire:key="obs-person-{{ $blockIndex }}-{{ $pIndex }}-{{ $personCode }}-{{ $personName }}"
                        x-data="{
                            open: false,
                            q: @js($personLabel),
                            highlight: 0,
                            staff: @js($staffOptions),
                            labelFor(emp) {
                                const code = (emp.code || '').trim();
                                const name = (emp.name || '').trim();
                                if (code && name) return code + ' — ' + name;
                                return name || code;
                            },
                            get filtered() {
                                const needle = this.q.trim().toLowerCase();
                                if (!needle) return this.staff.slice(0, 8);
                                return this.staff.filter((e) => {
                                    const hay = ((e.code || '') + ' ' + (e.name || '') + ' ' + (e.designation || '')).toLowerCase();
                                    return hay.includes(needle);
                                }).slice(0, 8);
                            },
                            pick(emp) {
                                this.q = this.labelFor(emp);
                                this.open = false;
                                $wire.applyObservationPerson({{ (int) $blockIndex }}, {{ (int) $pIndex }}, emp.id, emp.code || '', emp.name || '');
                            },
                            commitTyped() {
                                const raw = this.q.trim();
                                this.open = false;
                                if (!raw) {
                                    $wire.applyObservationPerson({{ (int) $blockIndex }}, {{ (int) $pIndex }}, null, '', '');
                                    return;
                                }
                                const exact = this.staff.find((e) =>
                                    this.labelFor(e).toLowerCase() === raw.toLowerCase()
                                    || (e.name || '').trim().toLowerCase() === raw.toLowerCase()
                                    || (e.code || '').trim().toLowerCase() === raw.toLowerCase()
                                );
                                if (exact) {
                                    this.pick(exact);
                                    return;
                                }
                                // Allow free type as name only
                                $wire.applyObservationPerson({{ (int) $blockIndex }}, {{ (int) $pIndex }}, null, '', raw);
                            }
                        }"
                        @click.outside="open = false"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="mb-0.5 flex items-center gap-1.5">
                                <span class="text-[9px] font-semibold uppercase tracking-wide text-violet-700/70">ID + নাম</span>
                                @if ($personCode !== '')
                                    <span class="rounded bg-violet-100 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-violet-900">{{ $personCode }}</span>
                                @endif
                            </div>
                            <input
                                type="search"
                                x-model="q"
                                @focus="open = true; highlight = 0"
                                @input="open = true; highlight = 0"
                                @keydown.arrow-down.prevent="open = true; highlight = Math.min(highlight + 1, Math.max(filtered.length - 1, 0))"
                                @keydown.arrow-up.prevent="highlight = Math.max(highlight - 1, 0)"
                                @keydown.enter.prevent="filtered[highlight] ? pick(filtered[highlight]) : commitTyped()"
                                @keydown.escape="open = false"
                                @blur="commitTyped()"
                                placeholder="Employee ID / নাম খুঁজুন…"
                                class="h-8 w-full rounded border border-violet-200 bg-white px-2 text-[11px] focus:border-violet-400 focus:ring-violet-400"
                                autocomplete="off"
                            >
                            <div
                                x-show="open"
                                x-cloak
                                class="absolute left-0 right-8 z-30 mt-1 max-h-44 overflow-y-auto rounded-md border border-violet-200 bg-white py-1 shadow-lg"
                            >
                                <template x-for="(emp, idx) in filtered" :key="emp.id">
                                    <button
                                        type="button"
                                        @mousedown.prevent="pick(emp)"
                                        @mouseenter="highlight = idx"
                                        class="flex w-full flex-col items-start gap-0.5 px-2.5 py-1.5 text-left hover:bg-violet-50"
                                        :class="highlight === idx ? 'bg-violet-50' : ''"
                                    >
                                        <span class="text-[11px] font-semibold text-navy-900">
                                            <span class="font-mono text-violet-800" x-text="emp.code"></span>
                                            <span x-show="emp.code && emp.name"> — </span>
                                            <span x-text="emp.name"></span>
                                        </span>
                                        <span class="text-[10px] text-slate-500" x-text="emp.designation || ''"></span>
                                    </button>
                                </template>
                                <p x-show="filtered.length === 0" class="px-2.5 py-2 text-[11px] text-slate-400">
                                    <span x-show="staff.length === 0">এই শাখায় staff নেই — নাম টাইপ করুন</span>
                                    <span x-show="staff.length > 0">মিল নেই — Enter চাপলে যা লিখেছেন সেভ হবে</span>
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="removeObservationPerson({{ (int) $blockIndex }}, {{ (int) $pIndex }})"
                            class="mt-5 shrink-0 text-[11px] text-rose-600 hover:underline"
                            @disabled(count($displayPeople) <= 1)
                        >×</button>
                    </div>
                @endforeach
            </div>

            @if ($hasAccused)
                <div class="mt-2 flex justify-end">
                    <button
                        type="button"
                        wire:click="clearObservationPeople({{ (int) $blockIndex }})"
                        class="text-[10px] font-semibold text-rose-600 hover:underline"
                    >অভিযুক্ত সরান</button>
                </div>
            @endif
        </div>
    @endif
</div>
