{{-- Employee row actions: one menu, plus transfer / fire dialogs. --}}
@props([
    'employee',
    'transferTargets' => [],
    'returnTo' => null,
    'canManage' => false,
    'reportCount' => 0,
    'showManage' => false,
    'showRemove' => false,
])

@php
    $canAct = ! $employee->isFired();
    $targets = collect($transferTargets);
    $returnTo = $returnTo ?: url()->full();
@endphp

<div
    class="relative inline-block text-left"
    x-data="{
        open: false,
        menuStyle: '',
        transferOpen: false,
        fireOpen: false,
        toggle() {
            this.open = ! this.open;
            if (this.open) this.$nextTick(() => this.place());
        },
        place() {
            const btn = this.$refs.trigger;
            if (! btn) return;
            const r = btn.getBoundingClientRect();
            const width = 184;
            const edge = 8;
            const menuHeight = 240;
            let left = r.right - width;
            if (left < edge) left = edge;
            const spaceBelow = window.innerHeight - r.bottom;
            const top = spaceBelow < menuHeight ? Math.max(edge, r.top - 8 - menuHeight) : r.bottom + 6;
            this.menuStyle = 'position:fixed;top:' + top + 'px;left:' + left + 'px;width:' + width + 'px;z-index:60;';
        },
    }"
    @keydown.escape.window="open = false; transferOpen = false; fireOpen = false"
    @click.outside="open = false"
>
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        class="inline-flex h-7 items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-semibold text-navy-900 shadow-sm hover:border-slate-300 hover:bg-slate-50"
        :class="open ? 'ring-2 ring-slate-200' : ''"
    >
        Actions
        <svg class="h-3 w-3 text-slate-500 transition" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
    </button>

    @php
        $item = 'flex w-full items-center gap-2 rounded-xl border px-2 py-1.5 text-left text-[12.5px] font-semibold shadow-[0_1px_2px_rgba(15,33,71,0.06)] transition-colors duration-150 hover:shadow-[0_4px_12px_rgba(15,33,71,0.10)]';
        $chip = 'inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm';
    @endphp
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        :style="menuStyle"
        class="origin-top-right space-y-1 rounded-2xl border border-slate-200/80 bg-white p-1.5 text-left shadow-[0_18px_40px_rgba(15,33,71,0.18)] ring-1 ring-black/5"
    >
        <a href="{{ route('shakha-employees.dossier', $employee) }}" class="{{ $item }} border-rose-100 bg-rose-50/70 text-rose-700 hover:bg-rose-100">
            <span class="{{ $chip }} text-rose-600">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>
            </span>
            <span class="flex-1">রিপোর্ট</span>
            @if ($reportCount > 0)
                <span class="rounded-full bg-rose-600 px-1.5 py-px text-[10.5px] font-bold text-white">{{ $reportCount }}</span>
            @endif
        </a>
        @if ($canManage)
            <a href="{{ route('shakha-employees.edit', $employee) }}" class="{{ $item }} border-indigo-100 bg-indigo-50/70 text-indigo-700 hover:bg-indigo-100">
                <span class="{{ $chip }} text-indigo-600">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                </span>
                Edit
            </a>
            @if ($canAct)
                <button type="button" @click="open = false; transferOpen = true" class="{{ $item }} border-sky-100 bg-sky-50/70 text-sky-800 hover:bg-sky-100">
                    <span class="{{ $chip }} text-sky-600">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3l4 4-4 4"/><path d="M21 7H9"/><path d="M7 21l-4-4 4-4"/><path d="M3 17h12"/></svg>
                    </span>
                    Transfer
                </button>
                <div class="mx-1 border-t border-slate-100"></div>
                <button type="button" @click="open = false; fireOpen = true" class="{{ $item }} border-rose-200 bg-white text-rose-700 hover:border-rose-600 hover:bg-rose-600 hover:text-white">
                    <span class="{{ $chip }} text-rose-600">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c.7-3.4 3.1-5.5 6-5.5s5.3 2.1 6 5.5"/><path d="M16 11h6"/></svg>
                    </span>
                    Fire
                </button>
            @endif
        @endif
        @if ($showRemove && $canManage)
            <form
                method="POST"
                action="{{ route('shakha-employees.destroy', $employee) }}"
                data-bynnas-confirm="Remove this employee from the roster?"
                data-bynnas-confirm-title="Remove employee?"
                data-bynnas-confirm-ok="Remove"
                data-bynnas-confirm-tone="rose"
            >
                @csrf
                @method('DELETE')
                <button type="submit" class="{{ $item }} border-rose-200 bg-white text-rose-700 hover:border-rose-600 hover:bg-rose-600 hover:text-white">
                    <span class="{{ $chip }} text-rose-600">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/></svg>
                    </span>
                    Remove
                </button>
            </form>
        @endif
    </div>

    @if ($canManage && $canAct)
        <div x-show="transferOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4" @click.self="transferOpen = false">
            <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-4 shadow-xl" @click.stop>
                <div class="mb-3">
                    <p class="text-[14px] font-semibold text-navy-900">Transfer (স্থানান্তর)</p>
                    <p class="mt-0.5 text-[13px] text-slate-500">{{ $employee->name }} · {{ $employee->employee_code }}</p>
                </div>
                <form method="POST" action="{{ route('shakha-employees.transfer', $employee) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Target shakha</label>
                        <select name="target_shakha_id" required class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                            <option value="">Select shakha…</option>
                            @foreach ($targets as $target)
                                <option value="{{ $target->id }}">{{ $target->name }}{{ $target->code ? ' ('.$target->code.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Joined new shakha</label>
                        <input type="date" name="joined_shakha_at" value="{{ bd_today() }}" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Note (optional)</label>
                        <input type="text" name="note" class="h-9 w-full rounded-lg border-slate-200 text-[12px]" placeholder="Reason / order no.">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="transferOpen = false" class="h-8 rounded-lg px-3 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-sky-700 px-3 text-[12px] font-medium text-white hover:bg-sky-800">Transfer</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="fireOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4" @click.self="fireOpen = false">
            <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-4 shadow-xl" @click.stop>
                <div class="mb-3">
                    <p class="text-[14px] font-semibold text-navy-900">Fire (চাকরিচ্যুত)</p>
                    <p class="mt-0.5 text-[13px] text-slate-500">{{ $employee->name }} · {{ $employee->employee_code }} — will leave audit name suggestions</p>
                </div>
                <form method="POST" action="{{ route('shakha-employees.fire', $employee) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Note (optional)</label>
                        <input type="text" name="note" class="h-9 w-full rounded-lg border-slate-200 text-[12px]" placeholder="Reason">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="fireOpen = false" class="h-8 rounded-lg px-3 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-rose-700 px-3 text-[12px] font-medium text-white hover:bg-rose-800">Mark as fired</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
