{{-- Expects: $alreadyAdded (bool), $wireWithAi, $wireWithoutAi, $aiReady (bool) --}}
@php
    $alreadyAdded = (bool) ($alreadyAdded ?? false);
    $aiReady = (bool) ($aiReady ?? false);
@endphp
@once
    <style>
        .ai-add-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            height: 2.1rem;
            padding: 0 0.95rem 0 0.75rem;
            border-radius: 9999px;
            border: 0;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.01em;
            background: linear-gradient(120deg, #6d28d9 0%, #9333ea 45%, #db2777 100%);
            background-size: 180% 100%;
            background-position: 0% 50%;
            box-shadow: 0 6px 16px -4px rgba(124, 58, 237, 0.55), 0 2px 4px rgba(15, 23, 42, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.25);
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-position 0.5s ease;
        }
        .ai-add-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg, transparent 30%, rgba(255, 255, 255, 0.35) 50%, transparent 70%);
            transform: translateX(-120%);
            transition: transform 0.7s ease;
            pointer-events: none;
        }
        .ai-add-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            background-position: 100% 50%;
            box-shadow: 0 12px 24px -6px rgba(147, 51, 234, 0.6), 0 3px 6px rgba(15, 23, 42, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }
        .ai-add-btn:hover:not(:disabled)::after { transform: translateX(120%); }
        .ai-add-btn:active:not(:disabled) { transform: translateY(0); }
        .ai-add-btn:focus-visible { outline: 2px solid #a855f7; outline-offset: 2px; }
        .ai-add-btn:disabled { cursor: not-allowed; opacity: 0.5; box-shadow: none; filter: grayscale(0.35); }
        .ai-add-btn svg { width: 0.95rem; height: 0.95rem; flex-shrink: 0; }
        .ai-add-btn .ai-add-spin { animation: ai-add-spin 0.9s linear infinite; }
        @keyframes ai-add-spin { to { transform: rotate(360deg); } }

        .ai-add-plain {
            display: inline-flex;
            align-items: center;
            height: 2.1rem;
            padding: 0 0.85rem;
            border-radius: 9999px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        .ai-add-plain:hover:not(:disabled) { background: #f8fafc; border-color: #94a3b8; }
        .ai-add-plain:disabled { opacity: 0.6; }
    </style>
@endonce
<div class="mt-2 flex flex-wrap items-center gap-2">
    @if ($alreadyAdded)
        <span class="inline-flex h-7 items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 text-xs font-semibold text-emerald-800">রিপোর্টে যোগ হয়েছে</span>
    @else
        <button
            type="button"
            wire:click="{{ $wireWithAi }}"
            wire:loading.attr="disabled"
            wire:target="{{ $wireWithoutAi }},{{ $wireWithAi }}"
            @disabled(! $aiReady)
            class="ai-add-btn"
            title="{{ $aiReady ? 'ঝুঁকি ও সুপারিশ AI লিখবে' : 'OPENAI_API_KEY প্রয়োজন' }}"
        >
            <svg wire:loading.remove wire:target="{{ $wireWithAi }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2.5l1.9 5.1a2 2 0 001.2 1.2l5.1 1.9-5.1 1.9a2 2 0 00-1.2 1.2L12 18.9l-1.9-5.1a2 2 0 00-1.2-1.2L3.8 10.7l5.1-1.9a2 2 0 001.2-1.2L12 2.5z"/>
                <path d="M19 15.5l.8 2.1 2.1.8-2.1.8-.8 2.1-.8-2.1-2.1-.8 2.1-.8.8-2.1z" opacity=".8"/>
            </svg>
            <svg wire:loading wire:target="{{ $wireWithAi }}" class="ai-add-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" d="M12 3a9 9 0 109 9"/>
            </svg>
            <span wire:loading.remove wire:target="{{ $wireWithAi }}">রিপোর্টে যোগ (AI সহ)</span>
            <span wire:loading wire:target="{{ $wireWithAi }}">AI লিখছে…</span>
        </button>
        <button
            type="button"
            wire:click="{{ $wireWithoutAi }}"
            wire:loading.attr="disabled"
            wire:target="{{ $wireWithoutAi }},{{ $wireWithAi }}"
            class="ai-add-plain"
        >
            <span wire:loading.remove wire:target="{{ $wireWithoutAi }}">রিপোর্টে যোগ (AI ছাড়া)</span>
            <span wire:loading wire:target="{{ $wireWithoutAi }}">যোগ হচ্ছে…</span>
        </button>
    @endif
</div>
<p class="mt-1 text-xs leading-snug text-slate-500">পর্যবেক্ষণ = সারসংক্ষেপ। AI সহ হলে ঝুঁকি ও সুপারিশও তৈরি হবে। বিভাগ/শিরোনাম/প্রচলিত নিয়ম/ম্যাট্রিক্স/জবাব আপনি পূরণ করবেন।</p>
