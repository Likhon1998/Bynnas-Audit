
<?php
    use App\Support\CustomTableSchema;
    $blockIndex = (int) $blockIndex;
    $table = CustomTableSchema::normalize(is_array($table ?? []) ? $table : []);
    $columns = $table['columns'];
    $leaves = CustomTableSchema::leafColumns($columns);
    $leafLabelsJson = json_encode(array_map(static fn ($l) => (string) ($l['label'] ?? ''), $leaves), JSON_UNESCAPED_UNICODE);
?>

<div
    class="fixed inset-0 z-[10060] flex items-center justify-center bg-slate-900/55 p-3"
    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'custom-table-editor-'.e($blockIndex).''; ?>wire:key="custom-table-editor-<?php echo e($blockIndex); ?>"
    wire:click.self="closeCustomTableEditor"
    x-data="{
        selR: <?php echo e($customTableSelR !== null && $customTableSelR !== '' ? (int) $customTableSelR : 'null'); ?>,
        selC: <?php echo e($customTableSelC !== null && $customTableSelC !== '' ? (int) $customTableSelC : 'null'); ?>,
        mergeRows: <?php echo e(max(1, (int) ($customTableMergeRows ?? 2))); ?>,
        mergeCols: <?php echo e(max(1, (int) ($customTableMergeCols ?? 1))); ?>,
        sizeCols: <?php echo e((int) ($customTableSizeCols ?? count($columns))); ?>,
        sizeRows: <?php echo e((int) ($customTableSizeRows ?? count($table['rows']))); ?>,
        leafLabels: <?php echo e($leafLabelsJson ?: '[]'); ?>,
        selectCell(r, c, el) {
            this.selR = r;
            this.selC = c;
            const rs = Number(el?.dataset?.mergeRs || 1);
            const cs = Number(el?.dataset?.mergeCs || 1);
            if (rs > 1 || cs > 1) {
                this.mergeRows = rs;
                this.mergeCols = cs;
            } else if (this.mergeRows < 2 && this.mergeCols < 2) {
                this.mergeRows = 2;
                this.mergeCols = 1;
            }
        },
        leafName() {
            if (this.selC === null) return '';
            return this.leafLabels[this.selC] || '';
        },
        applyMerge() {
            if (this.selR === null || this.selC === null) return;
            $wire.applyCustomTableMerge(this.selR, this.selC, Number(this.mergeRows) || 1, Number(this.mergeCols) || 1);
        },
        clearMerge() {
            if (this.selR === null || this.selC === null) return;
            $wire.clearCustomTableMerge(this.selR, this.selC);
            this.mergeRows = 1;
            this.mergeCols = 1;
        },
        nudgeRows(delta) {
            if (this.selR === null || this.selC === null) return;
            $wire.adjustCustomTableMerge(this.selR, this.selC, delta, 0);
        },
        nudgeCols(delta) {
            if (this.selR === null || this.selC === null) return;
            $wire.adjustCustomTableMerge(this.selR, this.selC, 0, delta);
        },
        undoMerge() {
            $wire.undoCustomTableMerge();
        },
        applySize() {
            $wire.resizeCustomTable(Number(this.sizeCols) || 1, Number(this.sizeRows) || 1);
            this.selR = null;
            this.selC = null;
        }
    }"
    x-init="
        document.body.dataset.ctEditor = '1';
        $wire.$watch('customTableSizeCols', v => { if (v != null) sizeCols = v });
        $wire.$watch('customTableSizeRows', v => { if (v != null) sizeRows = v });
        return () => { delete document.body.dataset.ctEditor; };
    "
    @keydown.escape.window="$wire.closeCustomTableEditor()"
>
    <div
        class="flex max-h-[92vh] w-full max-w-[1180px] flex-col overflow-hidden rounded-lg bg-white shadow-2xl"
        @click.stop
    >
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-2.5">
            <div class="min-w-0">
                <p class="text-[13px] font-semibold text-slate-900">Customize Table</p>
                <p class="text-[11px] text-slate-500">বাম = কাঠামো · ডান = ক্লিক/টাইপ (তাত্ক্ষণিক, লোডার ছাড়া)</p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <?php echo $__env->make('livewire.partials.audit-custom-table-example-popover', [
                    'blockIndex' => $blockIndex,
                    'insideEditor' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <button
                    type="button"
                    wire:click="closeCustomTableEditor"
                    class="rounded border border-slate-300 bg-white px-2.5 py-1 text-[12px] font-semibold text-slate-700 hover:bg-slate-100"
                >বন্ধ</button>
            </div>
        </div>

        <div class="border-b border-violet-100 bg-violet-50 px-3 py-2">
            <div class="flex flex-wrap items-stretch gap-2 text-[10px] leading-snug">
                <div class="flex min-w-[140px] flex-1 items-start gap-1.5 rounded border border-violet-200 bg-white px-2 py-1.5">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-600 text-[10px] font-bold text-white">১</span>
                    <span><strong class="text-violet-900">বামে</strong> সারি/কলাম → <strong>প্রয়োগ</strong></span>
                </div>
                <div class="flex min-w-[140px] flex-1 items-start gap-1.5 rounded border border-amber-200 bg-white px-2 py-1.5">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white">২</span>
                    <span>কলাম নাম · গ্রুপ হলে <strong class="text-amber-800">+ সাব</strong></span>
                </div>
                <div
                    class="flex min-w-[140px] flex-1 items-start gap-1.5 rounded border border-rose-200 bg-white px-2 py-1.5"
                    :class="selR !== null ? 'ring-2 ring-rose-400' : ''"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-600 text-[10px] font-bold text-white">৩</span>
                    <span><strong class="text-rose-800">ডানে সেল ক্লিক</strong> → মার্জ</span>
                </div>
                <div class="flex min-w-[140px] flex-1 items-start gap-1.5 rounded border border-emerald-200 bg-white px-2 py-1.5">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-bold text-white">৪</span>
                    <span>প্রস্থ % · টাইপ · <strong>বন্ধ</strong></span>
                </div>
            </div>
        </div>

        <div class="grid min-h-0 flex-1 grid-cols-1 divide-y divide-slate-200 lg:grid-cols-2 lg:divide-x lg:divide-y-0">
            <div class="min-h-0 overflow-y-auto p-3">
                <div class="mb-2 flex items-center gap-2">
                    <span class="rounded bg-violet-600 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">বাম প্যানেল</span>
                    <span class="text-[10px] text-slate-500">কাঠামো সেট — ফলাফল ডানে</span>
                </div>

                <label class="mb-3 block">
                    <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">টেবিল শিরোনাম</span>
                    <input
                        type="text"
                        wire:model.blur="reportBlocks.<?php echo e($blockIndex); ?>.title"
                        class="w-full rounded border border-slate-200 px-2 py-1.5 text-[12px] font-bold"
                    >
                </label>

                <div class="mb-3 rounded border-2 border-violet-200 bg-violet-50/40 p-2.5">
                    <p class="mb-1 text-[10px] font-bold text-violet-900">ধাপ ১ — সারি / কলাম সংখ্যা</p>
                    <div class="flex flex-wrap items-end gap-2">
                        <label class="text-[11px]">
                            টপ কলাম
                            <input type="number" min="1" max="20" x-model.number="sizeCols" class="mt-0.5 w-16 rounded border border-violet-300 px-1.5 py-1 text-[12px]">
                        </label>
                        <label class="text-[11px]">
                            সারি
                            <input type="number" min="1" max="100" x-model.number="sizeRows" class="mt-0.5 w-16 rounded border border-violet-300 px-1.5 py-1 text-[12px]">
                        </label>
                        <button type="button" @click="applySize()" class="rounded bg-violet-700 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-violet-800">প্রয়োগ</button>
                    </div>
                </div>

                <div class="mb-3 flex flex-wrap gap-2">
                    <button type="button" wire:click="applyCustomTableTemplate(<?php echo e($blockIndex); ?>, 'expense')" class="rounded border border-amber-400 bg-amber-50 px-2 py-1 text-[10px] font-semibold text-amber-950 hover:bg-amber-100">নমুনা Expense টেমপ্লেট লোড</button>
                    <button type="button" wire:click="applyCustomTableTemplate(<?php echo e($blockIndex); ?>, 'blank')" class="rounded border border-slate-300 bg-white px-2 py-1 text-[10px] font-semibold text-slate-700 hover:bg-slate-50">খালি ৪×৫</button>
                    <button type="button" wire:click="addCustomTableColumn(<?php echo e($blockIndex); ?>)" class="rounded bg-slate-800 px-2 py-1 text-[10px] font-semibold text-white hover:bg-slate-900">+ টপ কলাম</button>
                    <button type="button" wire:click="addCustomTableRow(<?php echo e($blockIndex); ?>)" class="rounded bg-slate-800 px-2 py-1 text-[10px] font-semibold text-white hover:bg-slate-900">+ সারি</button>
                </div>

                <div class="mb-3 space-y-1 rounded border-2 border-amber-200 bg-amber-50/30 p-2">
                    <p class="mb-0.5 text-[10px] font-bold text-amber-950">ধাপ ২ — কলাম নাম ও সাব-কলাম</p>
                    <p class="mb-2 text-[10px] text-slate-600">নাম লিখে বাইরে ক্লিক করুন · গ্রুপের জন্য <span class="rounded border border-violet-300 bg-white px-1 font-semibold text-violet-800">+ সাব</span></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $colIndex => $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php echo $__env->make('livewire.partials.audit-custom-table-column-node', [
                            'blockIndex' => $blockIndex,
                            'column' => $col,
                            'depth' => 0,
                            'path' => [$colIndex],
                            'showWidth' => true,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <div
                    class="rounded border-2 border-dashed border-rose-300 bg-rose-50/50 p-2.5"
                    :class="selR !== null ? 'border-solid ring-2 ring-rose-300' : ''"
                >
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[10px] font-bold text-rose-900">ধাপ ৩ — সেল মার্জ (বদলানো যায়)</p>
                        <button
                            type="button"
                            @click="undoMerge()"
                            class="rounded border border-slate-300 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-700 hover:bg-slate-50"
                            title="শেষ মার্জ পরিবর্তন বাতিল"
                        >↩ আগের মার্জ</button>
                    </div>
                    <template x-if="selR !== null && selC !== null">
                        <div>
                            <p class="mb-2 rounded bg-white px-2 py-1 text-[11px] text-slate-800">
                                নির্বাচিত: সারি <strong x-text="selR + 1"></strong>, কলাম <strong x-text="selC + 1"></strong>
                                <span class="text-slate-500" x-text="leafName() ? '(' + leafName() + ')' : ''"></span>
                                · এখন: <strong x-text="mergeRows + '×' + mergeCols"></strong>
                            </p>

                            <p class="mb-1 text-[10px] font-semibold text-rose-900">দ্রুত ঠিক করুন (±১)</p>
                            <div class="mb-2 flex flex-wrap gap-1.5">
                                <button type="button" @click="nudgeRows(1)" class="rounded bg-rose-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-rose-700">সারি +১</button>
                                <button type="button" @click="nudgeRows(-1)" class="rounded border border-rose-400 bg-white px-2 py-1 text-[10px] font-bold text-rose-800 hover:bg-rose-50">সারি −১</button>
                                <button type="button" @click="nudgeCols(1)" class="rounded bg-rose-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-rose-700">কলাম +১</button>
                                <button type="button" @click="nudgeCols(-1)" class="rounded border border-rose-400 bg-white px-2 py-1 text-[10px] font-bold text-rose-800 hover:bg-rose-50">কলাম −১</button>
                            </div>

                            <p class="mb-1 text-[10px] font-semibold text-rose-900">অথবা সঠিক সংখ্যা দিন</p>
                            <div class="mb-2 flex flex-wrap items-end gap-2">
                                <label class="text-[11px] font-semibold text-rose-900">
                                    নিচে সারি
                                    <input type="number" min="1" max="100" x-model.number="mergeRows" class="mt-0.5 block w-20 rounded border-2 border-rose-400 px-1.5 py-1 text-[12px]">
                                </label>
                                <label class="text-[11px] font-semibold text-rose-900">
                                    পাশে কলাম
                                    <input type="number" min="1" max="20" x-model.number="mergeCols" class="mt-0.5 block w-20 rounded border-2 border-rose-400 px-1.5 py-1 text-[12px]">
                                </label>
                                <button type="button" @click="applyMerge()" class="rounded bg-rose-700 px-3 py-1.5 text-[11px] font-bold text-white hover:bg-rose-800">মার্জ প্রয়োগ</button>
                                <button type="button" @click="clearMerge()" class="rounded border border-slate-400 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-800 hover:bg-slate-50">মার্জ ভেঙে দিন</button>
                            </div>
                            <p class="text-[10px] text-rose-900/80">ভুল হলে <strong>সারি −১</strong> বা <strong>↩ আগের মার্জ</strong> চাপুন। ১×১ = মার্জ নেই।</p>
                        </div>
                    </template>
                    <template x-if="selR === null">
                        <div class="flex items-start gap-2 rounded border border-rose-200 bg-white px-2.5 py-2">
                            <span class="mt-0.5 text-lg leading-none text-rose-500" aria-hidden="true">→</span>
                            <div>
                                <p class="text-[11px] font-bold text-rose-900">ডান প্রিভিউতে একটি সেল ক্লিক করুন</p>
                                <p class="text-[10px] text-slate-600">মার্জ করা সেল ক্লিক করলে বর্তমান সাইজ দেখাবে — তারপর ±১ দিয়ে ঠিক করুন।</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex min-h-0 flex-col bg-emerald-50/40 p-3">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="rounded bg-emerald-700 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">ডান · লাইভ প্রিভিউ</span>
                    <span class="text-[10px] font-semibold text-emerald-900" x-text="selR !== null ? 'সেল নির্বাচিত — বামে মার্জ করুন' : 'এখানে সেল ক্লিক করুন'"></span>
                </div>

                <div
                    class="mb-2 rounded border border-dashed border-rose-400 bg-rose-50 px-2 py-1.5 text-center text-[11px] font-semibold text-rose-800"
                    x-show="selR === null"
                    x-cloak
                >↓ ঘরে ক্লিক করুন (তাত্ক্ষণিক) ↓</div>

                <div class="min-h-0 flex-1 overflow-auto rounded border-2 border-emerald-300 bg-white p-2 shadow-inner">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($table['title'] ?? '') !== ''): ?>
                        <p class="mb-2 text-[12px] font-bold text-slate-900"><?php echo e($table['title']); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php echo $__env->make('livewire.partials.audit-custom-table-render', [
                        'block' => $table,
                        'blockIndex' => $blockIndex,
                        'editable' => true,
                        'selectable' => false,
                        'alpineSelect' => true,
                        'compact' => false,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-custom-table-editor-modal.blade.php ENDPATH**/ ?>