@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-slate-200 text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500']) }}>
