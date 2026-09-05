
<?php
    $insertIndex = (int) ($insertIndex ?? 0);
    $end = (bool) ($end ?? false);
?>

<div
    class="group relative my-0.5 flex h-5 items-center justify-center"
    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'insert-menu-'.e($insertIndex).'-'.e($end ? 'end' : 'mid').''; ?>wire:key="insert-menu-<?php echo e($insertIndex); ?>-<?php echo e($end ? 'end' : 'mid'); ?>"
    x-data="{
        open: false,
        style: '',
        toggle() {
            if (this.open) { this.open = false; return; }
            this.open = true;
            this.$nextTick(() => this.place());
        },
        place() {
            const btn = this.$refs.btn;
            const menu = this.$refs.menu;
            if (!btn || !menu) return;
            const r = btn.getBoundingClientRect();
            const mw = menu.offsetWidth || 220;
            const mh = menu.offsetHeight || 280;
            const gap = 6;
            const pad = 8;
            const spaceBelow = window.innerHeight - r.bottom - pad;
            const spaceAbove = r.top - pad;
            const openUp = spaceBelow < Math.min(mh, 300) && spaceAbove > spaceBelow;
            let top = openUp
                ? Math.max(pad, r.top - mh - gap)
                : Math.min(r.bottom + gap, window.innerHeight - Math.min(mh, window.innerHeight - pad * 2) - pad);
            let left = r.left + r.width / 2 - mw / 2;
            left = Math.max(pad, Math.min(left, window.innerWidth - mw - pad));
            const maxH = openUp
                ? Math.max(140, r.top - pad - gap)
                : Math.max(140, window.innerHeight - top - pad);
            this.style = `position:fixed;top:${top}px;left:${left}px;max-height:${maxH}px;width:${mw}px;z-index:90;`;
        },
        pick(type) {
            this.open = false;
            $wire.insertBlockAt(<?php echo e($insertIndex); ?>, type);
        }
    }"
    @keydown.escape.window="open = false"
    @scroll.window="open && place()"
    @resize.window="open && place()"
>
    <div class="pointer-events-none absolute inset-x-8 top-1/2 h-px -translate-y-1/2 bg-slate-200 opacity-0 transition-opacity group-hover:opacity-100"></div>

    <button
        type="button"
        x-ref="btn"
        @click.stop="toggle()"
        :class="open ? 'border-emerald-500 bg-emerald-500 text-white opacity-100' : ''"
        class="relative z-20 flex h-5 w-5 items-center justify-center rounded-full border border-slate-300 bg-white text-[14px] leading-none text-slate-500 shadow-sm opacity-55 transition hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 hover:opacity-100 group-hover:opacity-100 <?php echo e($end ? 'opacity-80' : ''); ?>"
        title="এখানে যোগ করুন"
        aria-label="এখানে যোগ করুন"
        :aria-expanded="open"
    >+</button>

    <div
        x-ref="menu"
        x-show="open"
        x-cloak
        @click.outside="open = false"
        :style="style"
        class="overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-xl"
        style="width:13.5rem;background:#ffffff;"
    >
        <button type="button" @click="pick('finding_format_pack')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1.5 text-left text-[11px] font-semibold" style="background:#ecfdf5;color:#065f46;">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded text-[9px] font-bold text-white" style="background:#059669;">★</span>
            <span class="min-w-0 leading-tight">
                Finding format pack
                <span class="mt-0.5 block text-[9px] font-medium opacity-80">বিভাগ → শিরোনাম → নিয়ম → পর্যবেক্ষণ → Rating + Risk</span>
            </span>
        </button>

        <div class="mx-2 my-0.5 border-t border-slate-100"></div>

        <button type="button" @click="pick('finding')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-semibold" style="background:#e0f2fe;color:#0c4a6e;">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded text-[10px] font-bold text-white" style="background:#0284c7;">+</span>
            শিরোনাম
        </button>
        <button type="button" @click="pick('section')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-semibold" style="background:#e0e7ff;color:#312e81;">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded text-[10px] font-bold text-white" style="background:#4f46e5;">§</span>
            বিভাগ
        </button>
        <button type="button" @click="pick('criteria')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </span>
            প্রচলিত নিয়ম
        </button>
        <button type="button" @click="pick('observation')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            পর্যবেক্ষণ বক্স
        </button>
        <button type="button" @click="pick('stats')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </span>
            Report Rating Box
        </button>
        <button type="button" @click="pick('custom_table')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-semibold" style="background:#f3e8ff;color:#6b21a8;">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-violet-600">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
            </span>
            টেবিল (Customize)
        </button>
        <button type="button" @click="pick('followup_pack')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </span>
            Risk + Jobab pack
        </button>

        <div class="mx-2 my-0.5 border-t border-slate-100"></div>

        <button type="button" @click="pick('risk')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            </span>
            ঝুঁকি / প্রভাব
        </button>
        <button type="button" @click="pick('root_cause')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            মূল কারণ
        </button>
        <button type="button" @click="pick('recommendation')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </span>
            সুপারিশ
        </button>
        <button type="button" @click="pick('jobab_table')" class="mx-1 mb-px flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </span>
            জবাব টেবিল
        </button>
        <button type="button" @click="pick('text_box')" class="mx-1 flex w-[calc(100%-0.5rem)] items-center gap-1.5 rounded px-2 py-1 text-left text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            আরও বক্স
        </button>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-block-insert-menu.blade.php ENDPATH**/ ?>