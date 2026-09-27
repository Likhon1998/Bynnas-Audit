@php
    $options = $ruleBookRules ?? collect();
@endphp
@if ($options->isNotEmpty())
    <label class="mb-1 block">
        <span class="mb-0.5 block text-[11px] font-medium text-slate-500">From rule book</span>
        <select
            class="h-8 w-full rounded border border-slate-200 bg-white text-[12px]"
            onchange="if (!this.value) return; const box = this.closest('[data-rule-pick]'); const ta = box ? box.querySelector('textarea') : null; if (ta) { ta.value = this.value; ta.dispatchEvent(new Event('input', { bubbles: true })); } this.value = '';"
        >
            <option value="">Choose a saved rule…</option>
            @foreach ($options as $rule)
                <option value="{{ $rule->criteriaText() }}">{{ $rule->serial }}. {{ $rule->title }}</option>
            @endforeach
        </select>
    </label>
@endif
