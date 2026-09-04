
<?php
    $insertIndex = (int) ($insertIndex ?? 0);
    $end = (bool) ($end ?? false);
?>

<div
    class="group relative my-0.5 flex h-5 items-center justify-center"
    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'insert-menu-'.e($insertIndex).'-'.e($end ? 'end' : 'mid').''; ?>wire:key="insert-menu-<?php echo e($insertIndex); ?>-<?php echo e($end ? 'end' : 'mid'); ?>"
    x-data="{
        open: false,
        panelStyle: '',
        toggle() {
            this.open = ! this.open;
            if (this.open) {
                this.$nextTick(() => this.place());
            }
        },
        place() {
            const btn = this.$refs.trigger;
            if (! btn) return;
            const r = btn.getBoundingClientRect();
            const width = 224;
            let left = r.left + (r.width / 2) - (width / 2);
            left = Math.max(8, Math.min(left, window.innerWidth - width - 8));
            let top = r.bottom + 6;
            if (top + 520 > window.innerHeight) {
                top = Math.max(8, r.top - 526);
            }
            this.panelStyle = 'position:fixed;top:' + top + 'px;left:' + left + 'px;width:' + width + 'px;z-index:10050;background:#ffffff;opacity:1;';
        },
        close() { this.open = false; }
    }"
    @keydown.escape.window="close()"
>
    <div class="pointer-events-none absolute inset-x-8 top-1/2 h-px -translate-y-1/2 bg-slate-200 opacity-0 transition-opacity group-hover:opacity-100"></div>

    <button
        type="button"
        x-ref="trigger"
        @click.stop="toggle()"
        :class="open ? 'border-emerald-500 bg-emerald-500 text-white opacity-100' : ''"
        class="relative flex h-5 w-5 items-center justify-center rounded-full border border-slate-300 bg-white text-[14px] leading-none text-slate-500 shadow-sm opacity-55 transition hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 hover:opacity-100 group-hover:opacity-100 <?php echo e($end ? 'opacity-80' : ''); ?>"
        title="এখানে যোগ করুন"
        aria-label="এখানে যোগ করুন"
    >+</button>

    <template x-teleport="body">
        <div x-show="open" x-cloak style="display: none;">
            
            <div
                class="fixed inset-0"
                style="z-index:10040; background:rgba(15,23,42,0.18);"
                @click="close()"
            ></div>

            <div
                x-show="open"
                @click.stop
                :style="panelStyle"
                class="overflow-hidden rounded-lg border border-slate-300 py-1.5 shadow-2xl"
                style="background:#ffffff; opacity:1;"
            >
                <p class="px-3 pb-1 pt-0.5 text-[9px] font-semibold uppercase tracking-wide text-slate-500">যোগ করুন</p>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'finding')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#e0f2fe; color:#0c4a6e;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#0284c7;">+</span>
                    শিরোনাম
                </button>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'section')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#e0e7ff; color:#312e81;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#4f46e5;">+</span>
                    বিভাগ
                </button>

                <div class="my-1.5 border-t border-slate-200"></div>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'criteria')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#fef3c7; color:#78350f;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#d97706;">+</span>
                    প্রচলিত নিয়ম
                </button>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'observation')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#ccfbf1; color:#134e4a;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#0d9488;">+</span>
                    পর্যবেক্ষণ
                </button>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'stats')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#ffe4e6; color:#881337;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#e11d48;">+</span>
                    Report Rating Box
                </button>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'custom_table')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#ede9fe; color:#4c1d95;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#7c3aed;">+</span>
                    Custom Table
                </button>

                <div class="my-1.5 border-t border-slate-200"></div>
                <p class="px-3 pb-1 pt-0.5 text-[9px] font-semibold uppercase tracking-wide text-slate-500">ঝুঁকি · জবাব</p>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'followup_pack')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#ffedd5; color:#9a3412;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#ea580c;">+</span>
                    Risk + Jobab (সব)
                </button>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'risk')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#fef2f2; color:#7f1d1d;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#dc2626;">+</span>
                    ঝুঁকি/প্রভাব
                </button>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'root_cause')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#fff7ed; color:#9a3412;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#f97316;">+</span>
                    মূল কারণ
                </button>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'recommendation')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#ecfdf5; color:#065f46;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#059669;">+</span>
                    সুপারিশ
                </button>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'jobab_table')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#eff6ff; color:#1e3a8a;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#2563eb;">+</span>
                    জবাব টেবিল
                </button>

                <button
                    type="button"
                    @click="close(); $wire.insertBlockAt(<?php echo e($insertIndex); ?>, 'text_box')"
                    class="mx-1 mb-0.5 flex w-[calc(100%-0.5rem)] items-center gap-2 rounded-md px-2.5 py-2 text-left text-[11px] font-semibold"
                    style="background-color:#f8fafc; color:#334155;"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[11px] font-bold text-white" style="background-color:#64748b;">+</span>
                    আরও বক্স
                </button>
            </div>
        </div>
    </template>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-block-insert-menu.blade.php ENDPATH**/ ?>