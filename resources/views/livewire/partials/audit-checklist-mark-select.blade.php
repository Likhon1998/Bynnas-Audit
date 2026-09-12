{{-- Expects: $wireModel (string), $markValue (string|null) --}}
@php
    $mark = trim((string) ($markValue ?? ''));
    $markClass = match (true) {
        $mark === '✓' => 'bg-emerald-100 text-emerald-800 ring-emerald-300',
        in_array($mark, ['✗', 'x', 'X'], true) => 'bg-rose-100 text-rose-800 ring-rose-300',
        in_array($mark, ['N/A', 'n/a', 'NA'], true) => 'bg-amber-100 text-amber-900 ring-amber-300',
        default => 'bg-white text-slate-400 ring-slate-200',
    };
@endphp
<select
    wire:model.live="{{ $wireModel }}"
    class="checklist-mark-select h-8 w-full min-w-[2rem] cursor-pointer rounded px-0 text-center text-[14px] font-extrabold leading-none ring-1 focus:outline-none focus:ring-2 focus:ring-[#2b579a] {{ $markClass }}"
    title="✓ ok · ✗ fail · N/A"
>
    <option value=""></option>
    <option value="✓">✓</option>
    <option value="✗">✗</option>
    <option value="N/A">N/A</option>
</select>
