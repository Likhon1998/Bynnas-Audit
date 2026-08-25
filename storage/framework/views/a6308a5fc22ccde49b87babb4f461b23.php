<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'active' => false,
    'manual' => false,
    'editable' => false,
    'category' => null,
    'schedulableType' => null,
    'schedulableId' => null,
    'monthIndex' => null,
    'tab' => null,
    'fy' => null,
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
    'active' => false,
    'manual' => false,
    'editable' => false,
    'category' => null,
    'schedulableType' => null,
    'schedulableId' => null,
    'monthIndex' => null,
    'tab' => null,
    'fy' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($editable): ?>
    <button
        type="button"
        x-data="{
            active: <?php echo e($active ? 'true' : 'false'); ?>,
            busy: false,
            url: <?php echo \Illuminate\Support\Js::from(route('annual-audit.toggle-month'))->toHtml() ?>,
            payload: {
                category: <?php echo \Illuminate\Support\Js::from($category)->toHtml() ?>,
                schedulable_type: <?php echo \Illuminate\Support\Js::from($schedulableType)->toHtml() ?>,
                schedulable_id: <?php echo \Illuminate\Support\Js::from($schedulableId)->toHtml() ?>,
                month_index: <?php echo \Illuminate\Support\Js::from($monthIndex)->toHtml() ?>,
                tab: <?php echo \Illuminate\Support\Js::from($tab)->toHtml() ?>,
                fy: <?php echo \Illuminate\Support\Js::from($fy)->toHtml() ?>,
            },
            async toggle() {
                if (this.busy) return;
                this.busy = true;
                const previous = this.active;
                this.active = !this.active;
                this.$dispatch('audit-tick', { delta: this.active ? 1 : -1, month_index: this.payload.month_index });
                try {
                    const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                    const response = await fetch(this.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify(this.payload),
                    });
                    if (! response.ok) throw new Error('toggle failed');
                    const data = await response.json();
                    if (Boolean(data.active) !== this.active) {
                        this.$dispatch('audit-tick', { delta: data.active ? 1 : -1, month_index: this.payload.month_index });
                        this.active = Boolean(data.active);
                    }
                } catch (e) {
                    this.active = previous;
                    this.$dispatch('audit-tick', { delta: previous ? 1 : -1, month_index: this.payload.month_index });
                } finally {
                    this.busy = false;
                }
            },
        }"
        @click="toggle()"
        :disabled="busy"
        :title="active ? 'Remove audit this month' : 'Schedule audit this month'"
        :class="active ? 'bg-emerald-50 hover:bg-emerald-100' : 'hover:bg-slate-50'"
        class="inline-flex h-7 w-7 items-center justify-center rounded-md transition disabled:opacity-50"
    >
        <svg x-show="active" x-cloak class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
        </svg>
        <span x-show="!active" class="h-1.5 w-1.5 rounded-full bg-slate-200"></span>
    </button>
<?php else: ?>
    <span class="inline-flex h-7 w-7 items-center justify-center">
        <?php if($active): ?>
            <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
            </svg>
        <?php else: ?>
            <span class="h-1.5 w-1.5 rounded-full bg-slate-200"></span>
        <?php endif; ?>
    </span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/components/audit-month-mark.blade.php ENDPATH**/ ?>