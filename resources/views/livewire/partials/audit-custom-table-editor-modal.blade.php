{{-- Customize popup: fully client-side (instant). Livewire gets a quiet draft sync + one save on close. --}}
@php
    use App\Support\CustomTableSchema;
    $blockIndex = (int) $blockIndex;
    $table = CustomTableSchema::normalize(is_array($table ?? []) ? $table : []);
    $editorConfig = [
        'blockIndex' => $blockIndex,
        'table' => $table,
        'templates' => [
            'expense' => CustomTableSchema::normalize(CustomTableSchema::expenseVatTaxTemplate()),
            'blank' => CustomTableSchema::normalize(CustomTableSchema::blank(4, 5)),
        ],
    ];
@endphp

<div
    class="fixed inset-0 z-[10060] flex items-center justify-center bg-slate-900/55 p-3"
    wire:key="custom-table-editor-{{ $blockIndex }}"
    wire:ignore
    x-data="customTableEditor(@js($editorConfig))"
    x-show="!closing"
    @click.self="close()"
    @keydown.escape.window="close()"
>
    <div class="flex max-h-[92vh] w-full max-w-[1180px] flex-col overflow-hidden rounded-lg bg-white shadow-2xl" @click.stop>
        <div class="flex items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-3 py-2">
            <div class="min-w-0">
                <p class="text-[13px] font-semibold text-slate-900">Customize Table</p>
                <p class="text-[13px] text-slate-500">বাম = কাঠামো · ডান = ক্লিক/টাইপ — সব তাত্ক্ষণিক, বন্ধ করলে সেভ</p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <span class="text-xs font-medium" :class="dirty ? 'text-amber-600' : 'text-emerald-600'" x-text="dirty ? 'সেভ হচ্ছে…' : 'সেভ হয়েছে'"></span>
                <button
                    type="button"
                    @click="undo()"
                    :disabled="history.length === 0"
                    class="rounded border border-slate-300 bg-white px-2.5 py-1 text-[12px] font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                    title="শেষ পরিবর্তন বাতিল"
                >↩ আগের ধাপ</button>
                <button
                    type="button"
                    @click="close()"
                    class="rounded bg-violet-700 px-3 py-1 text-[12px] font-semibold text-white hover:bg-violet-800"
                >সেভ ও বন্ধ</button>
            </div>
        </div>

        <div class="border-b border-violet-100 bg-violet-50 px-3 py-2">
            <div class="flex flex-wrap items-stretch gap-2 text-xs leading-snug">
                <div class="flex min-w-[140px] flex-1 items-start gap-1.5 rounded border border-violet-200 bg-white px-2 py-1.5">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-600 text-xs font-bold text-white">১</span>
                    <span><strong class="text-violet-900">বামে</strong> সারি/কলাম → <strong>প্রয়োগ</strong></span>
                </div>
                <div class="flex min-w-[140px] flex-1 items-start gap-1.5 rounded border border-amber-200 bg-white px-2 py-1.5">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-white">২</span>
                    <span>কলাম নাম · গ্রুপ হলে <strong class="text-amber-800">+ সাব</strong></span>
                </div>
                <div
                    class="flex min-w-[140px] flex-1 items-start gap-1.5 rounded border border-rose-200 bg-white px-2 py-1.5"
                    :class="selR !== null ? 'ring-2 ring-rose-400' : ''"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white">৩</span>
                    <span><strong class="text-rose-800">ডানে সেল ক্লিক</strong> → মার্জ</span>
                </div>
                <div class="flex min-w-[140px] flex-1 items-start gap-1.5 rounded border border-emerald-200 bg-white px-2 py-1.5">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">৪</span>
                    <span>প্রস্থ % · টাইপ · <strong>সেভ ও বন্ধ</strong></span>
                </div>
            </div>
        </div>

        <div class="grid min-h-0 flex-1 grid-cols-1 divide-y divide-slate-200 lg:grid-cols-2 lg:divide-x lg:divide-y-0">
            {{-- Left: structure --}}
            <div class="min-h-0 overflow-y-auto p-3">
                <div class="mb-2 flex items-center gap-2">
                    <span class="rounded bg-violet-600 px-1.5 py-0.5 text-xs font-bold uppercase tracking-wide text-white">বাম প্যানেল</span>
                    <span class="text-xs text-slate-500">কাঠামো সেট — ফলাফল ডানে</span>
                </div>

                <label class="mb-3 block">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">টেবিল শিরোনাম</span>
                    <input
                        type="text"
                        x-model="t.title"
                        @input="touched()"
                        class="w-full rounded border border-slate-200 px-2 py-1.5 text-[12px] font-bold"
                    >
                </label>

                <div class="mb-3 rounded border-2 border-violet-200 bg-violet-50/40 p-2.5">
                    <p class="mb-1 text-xs font-bold text-violet-900">ধাপ ১ — সারি / কলাম সংখ্যা</p>
                    <div class="flex flex-wrap items-end gap-2">
                        <label class="text-[13px]">
                            টপ কলাম
                            <input type="number" min="1" max="20" x-model.number="sizeCols" @keydown.enter.prevent="applySize()" class="mt-0.5 w-16 rounded border border-violet-300 px-1.5 py-1 text-[12px]">
                        </label>
                        <label class="text-[13px]">
                            সারি
                            <input type="number" min="1" max="100" x-model.number="sizeRows" @keydown.enter.prevent="applySize()" class="mt-0.5 w-16 rounded border border-violet-300 px-1.5 py-1 text-[12px]">
                        </label>
                        <button type="button" @click="applySize()" class="rounded bg-violet-700 px-2.5 py-1.5 text-[13px] font-semibold text-white hover:bg-violet-800">প্রয়োগ</button>
                    </div>
                </div>

                <div class="mb-3 flex flex-wrap gap-2">
                    <button type="button" @click="loadTemplate('expense')" class="rounded border border-amber-400 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-950 hover:bg-amber-100">নমুনা Expense টেমপ্লেট লোড</button>
                    <button type="button" @click="loadTemplate('blank')" class="rounded border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">খালি ৪×৫</button>
                    <button type="button" @click="addTopColumn()" class="rounded bg-slate-800 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-900">+ টপ কলাম</button>
                    <button type="button" @click="addRow()" class="rounded bg-slate-800 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-900">+ সারি</button>
                    <button type="button" @click="removeLastRow()" :disabled="t.rows.length <= 1" class="rounded border border-rose-200 bg-white px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50 disabled:opacity-40">শেষ সারি মুছুন</button>
                    <button type="button" @click="toggleTotalRow()" class="rounded border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50" x-text="lastRow && lastRow.is_total ? 'মোট সারি বন্ধ' : 'শেষ সারি = মোট'"></button>
                </div>

                <div class="mb-3 space-y-1 rounded border-2 border-amber-200 bg-amber-50/30 p-2">
                    <p class="mb-0.5 text-xs font-bold text-amber-950">ধাপ ২ — কলাম নাম ও সাব-কলাম</p>
                    <p class="mb-2 text-xs text-slate-600">নাম লিখুন — সাথে সাথে ডানে দেখাবে · গ্রুপের জন্য <span class="rounded border border-violet-300 bg-white px-1 font-semibold text-violet-800">+ সাব</span></p>
                    <template x-for="item in columnList" :key="item.node.id">
                        <div class="rounded border border-slate-100 bg-slate-50/80 px-2 py-1" :style="'margin-left:' + (item.depth * 14) + 'px'">
                            <div class="flex flex-wrap items-center gap-1">
                                <input
                                    type="text"
                                    x-model="item.node.label"
                                    @input="setLabel()"
                                    class="min-w-[120px] flex-1 rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[13px] font-semibold"
                                    placeholder="কলাম নাম"
                                >
                                <label x-show="item.isLeaf" class="flex items-center gap-0.5 text-xs text-slate-500" title="কলামের প্রস্থ %">
                                    <span>প্রস্থ</span>
                                    <input
                                        type="number"
                                        min="4"
                                        max="80"
                                        step="1"
                                        :value="item.node.width ?? ''"
                                        placeholder="auto"
                                        @change="setWidth(item.node, $event.target.value)"
                                        class="w-14 rounded border border-slate-200 bg-white px-1 py-0.5 text-[13px]"
                                    >
                                    <span>%</span>
                                </label>
                                <button
                                    type="button"
                                    @click="addSubColumn(item.node.id)"
                                    class="rounded border border-violet-300 bg-white px-1.5 py-0.5 text-xs font-semibold text-violet-700 hover:bg-violet-50"
                                    title="এই কলামের নিচে সাব-কলাম"
                                >+ সাব</button>
                                <button
                                    type="button"
                                    @click="removeColumn(item.node.id)"
                                    class="rounded border border-rose-200 px-1.5 py-0.5 text-xs text-rose-600 hover:bg-rose-50"
                                    title="কলাম মুছুন"
                                >×</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div
                    class="rounded border-2 border-dashed border-rose-300 bg-rose-50/50 p-2.5"
                    :class="selR !== null ? 'border-solid ring-2 ring-rose-300' : ''"
                >
                    <p class="mb-1 text-xs font-bold text-rose-900">ধাপ ৩ — সেল মার্জ (বদলানো যায়)</p>
                    <template x-if="selR !== null && selC !== null">
                        <div>
                            <p class="mb-2 rounded bg-white px-2 py-1 text-[13px] text-slate-800">
                                নির্বাচিত: সারি <strong x-text="bn(selR + 1)"></strong>, কলাম <strong x-text="bn(selC + 1)"></strong>
                                <span class="text-slate-500" x-text="leafName() ? '(' + leafName() + ')' : ''"></span>
                                · এখন: <strong x-text="bn(mergeRows) + '×' + bn(mergeCols)"></strong>
                            </p>

                            <p class="mb-1 text-xs font-semibold text-rose-900">দ্রুত ঠিক করুন (±১)</p>
                            <div class="mb-2 flex flex-wrap gap-1.5">
                                <button type="button" @click="nudge(1, 0)" class="rounded bg-rose-600 px-2 py-1 text-xs font-bold text-white hover:bg-rose-700">সারি +১</button>
                                <button type="button" @click="nudge(-1, 0)" class="rounded border border-rose-400 bg-white px-2 py-1 text-xs font-bold text-rose-800 hover:bg-rose-50">সারি −১</button>
                                <button type="button" @click="nudge(0, 1)" class="rounded bg-rose-600 px-2 py-1 text-xs font-bold text-white hover:bg-rose-700">কলাম +১</button>
                                <button type="button" @click="nudge(0, -1)" class="rounded border border-rose-400 bg-white px-2 py-1 text-xs font-bold text-rose-800 hover:bg-rose-50">কলাম −১</button>
                            </div>

                            <p class="mb-1 text-xs font-semibold text-rose-900">অথবা সঠিক সংখ্যা দিন</p>
                            <div class="mb-2 flex flex-wrap items-end gap-2">
                                <label class="text-[13px] font-semibold text-rose-900">
                                    নিচে সারি
                                    <input type="number" min="1" max="100" x-model.number="mergeRows" class="mt-0.5 block w-20 rounded border-2 border-rose-400 px-1.5 py-1 text-[12px]">
                                </label>
                                <label class="text-[13px] font-semibold text-rose-900">
                                    পাশে কলাম
                                    <input type="number" min="1" max="20" x-model.number="mergeCols" class="mt-0.5 block w-20 rounded border-2 border-rose-400 px-1.5 py-1 text-[12px]">
                                </label>
                                <button type="button" @click="applyMerge()" class="rounded bg-rose-700 px-3 py-1.5 text-[13px] font-bold text-white hover:bg-rose-800">মার্জ প্রয়োগ</button>
                                <button type="button" @click="clearMerge()" class="rounded border border-slate-400 bg-white px-2.5 py-1.5 text-[13px] font-semibold text-slate-800 hover:bg-slate-50">মার্জ ভেঙে দিন</button>
                            </div>
                            <p class="text-xs text-rose-900/80">ভুল হলে <strong>সারি −১</strong> বা উপরের <strong>↩ আগের ধাপ</strong> চাপুন। ১×১ = মার্জ নেই।</p>
                        </div>
                    </template>
                    <template x-if="selR === null">
                        <div class="flex items-start gap-2 rounded border border-rose-200 bg-white px-2.5 py-2">
                            <span class="mt-0.5 text-lg leading-none text-rose-500" aria-hidden="true">→</span>
                            <div>
                                <p class="text-[13px] font-bold text-rose-900">ডান প্রিভিউতে একটি সেল ক্লিক করুন</p>
                                <p class="text-xs text-slate-600">মার্জ করা সেল ক্লিক করলে বর্তমান সাইজ দেখাবে — তারপর ±১ দিয়ে ঠিক করুন।</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Right: live preview (client-rendered) --}}
            <div class="flex min-h-0 flex-col bg-emerald-50/40 p-3">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="rounded bg-emerald-700 px-1.5 py-0.5 text-xs font-bold uppercase tracking-wide text-white">ডান · লাইভ প্রিভিউ</span>
                    <span class="text-xs font-semibold text-emerald-900" x-text="selR !== null ? 'সেল নির্বাচিত — বামে মার্জ করুন' : 'এখানে সেল ক্লিক করুন'"></span>
                </div>

                <div class="min-h-0 flex-1 overflow-auto rounded border-2 border-emerald-300 bg-white p-2 shadow-inner">
                    <p x-show="t.title" class="mb-2 text-[12px] font-bold text-slate-900" x-text="t.title"></p>
                    <table class="a4-table mb-[2mm] w-full border-collapse text-[10.5px]" style="table-layout: fixed;">
                        <colgroup>
                            <template x-for="(w, wi) in widths" :key="'w' + wi">
                                <col :style="'width:' + w + '%'">
                            </template>
                        </colgroup>
                        <thead>
                            <template x-for="(hrow, hi) in header" :key="'h' + hi">
                                <tr>
                                    <template x-for="h in hrow" :key="h.id">
                                        <th
                                            class="border border-slate-700 bg-slate-200 px-1 py-1 text-center align-middle font-bold"
                                            :colspan="h.colspan"
                                            :rowspan="h.rowspan"
                                            x-text="h.text"
                                        ></th>
                                    </template>
                                </tr>
                            </template>
                        </thead>
                        <tbody>
                            <template x-for="prow in paint" :key="'r' + prow.r">
                                <tr :class="prow.total ? 'font-bold' : ''">
                                    <template x-for="cell in prow.cells" :key="'c' + prow.r + '-' + cell.c">
                                        <td
                                            class="cursor-pointer border border-slate-700 px-1 py-0.5"
                                            :class="[cell.valign, cell.align, isSelected(prow.r, cell.c) ? 'ring-2 ring-inset ring-violet-500 bg-violet-50' : '']"
                                            :rowspan="cell.rs"
                                            :colspan="cell.cs"
                                            @click="selectCell(prow.r, cell.c)"
                                        >
                                            <input
                                                type="text"
                                                x-model="t.rows[prow.r].cells[cell.c]"
                                                @input="cellInput()"
                                                @focus="selectCell(prow.r, cell.c)"
                                                @click.stop
                                                class="w-full border-0 bg-transparent p-0 text-[13px] focus:ring-0"
                                                :class="[cell.align, prow.total ? 'font-bold' : '']"
                                            >
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
