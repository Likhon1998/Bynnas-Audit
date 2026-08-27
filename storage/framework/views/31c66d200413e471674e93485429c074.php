
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'index',
    'value' => '',
    'indicators' => [],
    'wireKey' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'index',
    'value' => '',
    'indicators' => [],
    'wireKey' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = ''.e($wireKey ?? ('fin-ind-'.$index)).''; ?>wire:key="<?php echo e($wireKey ?? ('fin-ind-'.$index)); ?>"
    class="relative"
    x-data="{
        open: false,
        q: <?php echo \Illuminate\Support\Js::from($value)->toHtml() ?>,
        highlight: 0,
        indicators: <?php echo \Illuminate\Support\Js::from($indicators)->toHtml() ?>,
        get filtered() {
            const q = this.q.trim().toLowerCase();
            if (!q) return this.indicators.slice(0, 8);
            return this.indicators.filter((i) => {
                const hay = (i.code + ' ' + i.title + ' ' + (i.category || '')).toLowerCase();
                return hay.includes(q);
            }).slice(0, 8);
        },
        get exactMatch() {
            const q = this.q.trim().toLowerCase();
            if (!q) return null;
            return this.indicators.find((i) => i.title.trim().toLowerCase() === q) || null;
        },
        pick(item) {
            this.q = item.title;
            this.open = false;
            $wire.applyFinancialIndicator(<?php echo e((int) $index); ?>, item.id, item.title);
        },
        commitCustom() {
            const title = this.q.trim();
            if (!title) return;
            if (this.exactMatch) {
                this.pick(this.exactMatch);
                return;
            }
            this.open = false;
            $wire.applyFinancialIndicator(<?php echo e((int) $index); ?>, null, title);
        },
        onKey(e) {
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
                else this.commitCustom();
            } else if (e.key === 'Escape') {
                this.open = false;
            }
        }
    }"
    @click.outside="open = false"
>
    <div class="relative">
        <input
            type="search"
            x-model="q"
            @focus="open = true; highlight = 0"
            @input="open = true; highlight = 0"
            @keydown="onKey($event)"
            placeholder="Indicator খুঁজুন বা নতুন শিরোনাম লিখুন…"
            class="w-full rounded border border-slate-200 bg-sky-50/40 py-2 pl-2 pr-16 text-[11px] leading-relaxed focus:border-[#2b579a] focus:ring-[#2b579a]"
            autocomplete="off"
        >
        <button
            type="button"
            @click="commitCustom()"
            class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded bg-[#2b579a] px-2 py-1 text-[10px] font-semibold text-white hover:bg-[#204072]"
            title="সংরক্ষণ / নতুন যোগ"
        >Save</button>
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
            x-show="q.trim().length > 0 && !exactMatch"
            @click="commitCustom()"
            class="flex w-full items-start gap-2 border-t border-slate-100 px-2.5 py-2 text-left hover:bg-emerald-50"
        >
            <span class="text-[11px] font-semibold text-emerald-700">
                + নতুন indicator যোগ করুন:
                <span class="font-normal" x-text="'“' + q.trim() + '”'"></span>
            </span>
        </button>

        <p x-show="filtered.length === 0 && q.trim().length === 0" class="px-2.5 py-2 text-[11px] text-slate-400">
            Type to search indicators…
        </p>
    </div>

    <p class="mt-1 text-[10px] text-slate-400">তালিকা থেকে বাছাই করুন · নতুন লিখলে indicator হিসেবে সেভ হবে</p>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-indicator-combobox.blade.php ENDPATH**/ ?>