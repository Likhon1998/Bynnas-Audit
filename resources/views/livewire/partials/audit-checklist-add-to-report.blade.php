{{-- Expects: $alreadyAdded (bool), $wireWithAi, $wireWithoutAi, $aiReady (bool) --}}
@php
    $alreadyAdded = (bool) ($alreadyAdded ?? false);
    $aiReady = (bool) ($aiReady ?? false);
@endphp
<div class="mt-2 flex flex-wrap items-center gap-1.5">
    @if ($alreadyAdded)
        <span class="inline-flex h-7 items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 text-[10px] font-semibold text-emerald-800">রিপোর্টে যোগ হয়েছে</span>
    @else
        <button
            type="button"
            wire:click="{{ $wireWithoutAi }}"
            wire:loading.attr="disabled"
            wire:target="{{ $wireWithoutAi }},{{ $wireWithAi }}"
            class="inline-flex h-7 items-center rounded-md border border-slate-300 bg-white px-2 text-[10px] font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="{{ $wireWithoutAi }}">রিপোর্টে যোগ (AI ছাড়া)</span>
            <span wire:loading wire:target="{{ $wireWithoutAi }}">যোগ হচ্ছে…</span>
        </button>
        <button
            type="button"
            wire:click="{{ $wireWithAi }}"
            wire:loading.attr="disabled"
            wire:target="{{ $wireWithoutAi }},{{ $wireWithAi }}"
            @disabled(! $aiReady)
            class="inline-flex h-7 items-center rounded-md border border-violet-300 bg-violet-50 px-2 text-[10px] font-semibold text-violet-800 hover:bg-violet-100 disabled:cursor-not-allowed disabled:opacity-50"
            title="{{ $aiReady ? 'ঝুঁকি ও সুপারিশ AI লিখবে' : 'OPENAI_API_KEY প্রয়োজন' }}"
        >
            <span wire:loading.remove wire:target="{{ $wireWithAi }}">রিপোর্টে যোগ (AI সহ)</span>
            <span wire:loading wire:target="{{ $wireWithAi }}">AI লিখছে…</span>
        </button>
    @endif
</div>
<p class="mt-1 text-[10px] leading-snug text-slate-500">পর্যবেক্ষণ = সারসংক্ষেপ। AI সহ হলে ঝুঁকি ও সুপারিশও তৈরি হবে। বিভাগ/শিরোনাম/প্রচলিত নিয়ম/ম্যাট্রিক্স/জবাব আপনি পূরণ করবেন।</p>
