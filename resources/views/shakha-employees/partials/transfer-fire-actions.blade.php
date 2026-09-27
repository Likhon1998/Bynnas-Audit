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
            const width = 176;
            const edge = 8;
            let left = r.right - width;
            if (left < edge) left = edge;
            const spaceBelow = window.innerHeight - r.bottom;
            const top = spaceBelow < 220 ? Math.max(edge, r.top - 8 - 220) : r.bottom + 6;
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

    <div
        x-show="open"
        x-cloak
        :style="menuStyle"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl"
    >
        <a href="{{ route('shakha-employees.dossier', $employee) }}" class="flex items-center justify-between px-3 py-1.5 text-[12px] font-semibold text-rose-700 hover:bg-rose-50">
            রিপোর্ট
            @if ($reportCount > 0)
                <span class="rounded-full bg-rose-100 px-1.5 text-[10px] text-rose-800">{{ $reportCount }}</span>
            @endif
        </a>
        @if ($canManage)
            <a href="{{ route('shakha-employees.edit', $employee) }}" class="block px-3 py-1.5 text-[12px] font-semibold text-indigo-700 hover:bg-indigo-50">Edit</a>
            @if ($canAct)
                <button type="button" @click="open = false; transferOpen = true" class="block w-full px-3 py-1.5 text-left text-[12px] font-semibold text-sky-800 hover:bg-sky-50">Transfer</button>
                <button type="button" @click="open = false; fireOpen = true" class="block w-full px-3 py-1.5 text-left text-[12px] font-semibold text-rose-700 hover:bg-rose-50">Fire</button>
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
                <button type="submit" class="block w-full px-3 py-1.5 text-left text-[12px] font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
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
