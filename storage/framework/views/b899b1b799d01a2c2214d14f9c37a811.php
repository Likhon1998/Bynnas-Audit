
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'path',
    'columns' => [],
    'hint' => null,
    'replace' => true,
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
    'path',
    'columns' => [],
    'hint' => null,
    'replace' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $hint = $hint ?? 'Excel থেকে টেবিল কপি করুন → এখানে Ctrl+V (বা Cmd+V) চাপুন';
    $colCount = is_array($columns) ? count($columns) : 0;
?>

<div
    <?php echo e($attributes->class('excel-paste-zone mb-2 rounded-md border border-dashed border-emerald-300 bg-emerald-50/60 px-3 py-2')); ?>

    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'excel-paste-'.e(md5((string) $path)).''; ?>wire:key="excel-paste-<?php echo e(md5((string) $path)); ?>"
    x-data="{
        path: <?php echo \Illuminate\Support\Js::from($path)->toHtml() ?>,
        columns: <?php echo \Illuminate\Support\Js::from($columns)->toHtml() ?>,
        replace: <?php echo \Illuminate\Support\Js::from((bool) $replace)->toHtml() ?>,
        status: '',
        onPaste(e) {
            const text = e.clipboardData?.getData('text/plain') || '';
            if (!text.trim()) return;
            e.preventDefault();
            this.status = 'পেস্ট হচ্ছে…';
            $wire.pasteTable(this.path, text, this.columns, this.replace)
                .then(() => { this.status = '✓ পেস্ট সম্পন্ন'; setTimeout(() => this.status = '', 2500); })
                .catch(() => { this.status = 'পেস্ট ব্যর্থ'; });
        }
    }"
    @paste="onPaste($event)"
    tabindex="0"
    role="textbox"
    aria-label="Excel paste zone"
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="min-w-0 flex-1">
            <p class="m-0 text-[11px] font-semibold text-emerald-800">Excel থেকে পেস্ট</p>
            <p class="m-0 text-[10px] leading-snug text-emerald-700/90"><?php echo e($hint); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($colCount > 0): ?> · <?php echo e($colCount); ?> কলাম<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> · পেস্ট করলে নিচের টেবিল <span class="font-semibold">পুরোপুরি বদলে যাবে</span></p>
        </div>
        <div class="flex flex-shrink-0 items-center gap-2">
            <span class="text-[10px] font-medium text-emerald-700" x-text="status" x-show="status" x-cloak></span>
            <button
                type="button"
                @click="if (confirm('এই টেবিলের সব সারি মুছে ফেলবেন? পরে আবার পেস্ট বা + row দিয়ে যোগ করতে পারবেন।')) { $wire.clearTable(path) }"
                class="rounded border border-rose-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-rose-700 hover:bg-rose-50"
            >
                টেবিল মুছুন
            </button>
        </div>
    </div>
    <textarea
        class="mt-1.5 h-14 w-full resize-y rounded border border-emerald-200 bg-white/90 px-2 py-1 text-[11px] text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
        placeholder="এখানে Ctrl+V করুন…"
        @paste="onPaste($event)"
    ></textarea>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\components\audit-excel-paste-zone.blade.php ENDPATH**/ ?>