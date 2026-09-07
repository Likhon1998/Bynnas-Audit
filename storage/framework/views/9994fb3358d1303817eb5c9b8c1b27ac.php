
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'format' => 'dmy',
    'placeholder' => null,
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
    'format' => 'dmy',
    'placeholder' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $placeholder = $placeholder ?? ($format === 'iso' ? 'yyyy-mm-dd / dd/mm/yyyy' : 'dd/mm/yyyy');
    $inputClass = $attributes->get('class', 'inline-input');
?>

<div
    class="audit-date-field relative inline-flex w-full max-w-full min-w-[7rem] items-stretch gap-0.5"
    x-data="{
        format: <?php echo \Illuminate\Support\Js::from($format)->toHtml() ?>,
        toDisplay(iso) {
            if (!iso) return '';
            const p = String(iso).split('-');
            if (p.length !== 3) return iso;
            return this.format === 'iso' ? iso : (p[2] + '/' + p[1] + '/' + p[0]);
        },
        toIso(text) {
            text = String(text || '').trim();
            if (!text) return '';
            if (/^\d{4}-\d{2}-\d{2}$/.test(text)) return text;
            const m = text.match(/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/);
            if (!m) return '';
            return m[3] + '-' + m[2].padStart(2, '0') + '-' + m[1].padStart(2, '0');
        },
        applyIso(iso) {
            if (!iso) return;
            const input = this.$refs.text;
            if (!input) return;
            input.value = this.toDisplay(iso);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },
        onNativePick(e) {
            this.applyIso(e.target.value);
        }
    }"
>
    <input
        x-ref="text"
        type="text"
        placeholder="<?php echo e($placeholder); ?>"
        autocomplete="off"
        <?php echo e($attributes->except('class')->merge(['class' => trim($inputClass.' min-w-0 flex-1')])); ?>

    />

    <span class="audit-date-picker-hit relative inline-flex w-7 shrink-0 items-center justify-center self-stretch overflow-hidden rounded border border-slate-200 bg-white text-slate-600 hover:bg-sky-50 hover:text-[#2b579a]" title="তারিখ বাছাই করুন">
        <svg class="pointer-events-none h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        
        <input
            type="date"
            class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
            tabindex="-1"
            aria-label="Select date"
            @click.stop="$el.value = toIso($refs.text.value) || $el.value"
            @change="onNativePick($event)"
        />
    </span>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\components\audit-date-field.blade.php ENDPATH**/ ?>