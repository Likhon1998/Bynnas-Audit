{{-- Transfer / Fire action links + modal popups --}}
@props([
    'employee',
    'transferTargets' => [],
    'returnTo' => null,
])

@php
    $canAct = ! $employee->isFired();
    $targets = collect($transferTargets);
    $returnTo = $returnTo ?: url()->full();
@endphp

@if ($canAct)
    <div
        class="inline-flex flex-wrap items-center justify-end gap-x-2 gap-y-1"
        x-data="{ transferOpen: false, fireOpen: false }"
        @keydown.escape.window="transferOpen = false; fireOpen = false"
    >
        <button
            type="button"
            @click="transferOpen = true"
            class="text-[11px] font-semibold text-sky-700 hover:underline"
        >Transfer</button>
        <button
            type="button"
            @click="fireOpen = true"
            class="text-[11px] font-semibold text-rose-600 hover:underline"
        >Fire</button>

        {{-- Transfer popup --}}
        <div
            x-show="transferOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
            @click.self="transferOpen = false"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-4 shadow-xl" @click.stop>
                <div class="mb-3">
                    <p class="text-[14px] font-semibold text-navy-900">Transfer (স্থানান্তর)</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">
                        {{ $employee->name }} · {{ $employee->employee_code }}
                    </p>
                </div>
                <form method="POST" action="{{ route('shakha-employees.transfer', $employee) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Target shakha</label>
                        <select name="target_shakha_id" required class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                            <option value="">Select shakha…</option>
                            @foreach ($targets as $target)
                                <option value="{{ $target->id }}">
                                    {{ $target->name }}{{ $target->code ? ' ('.$target->code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Joined new shakha</label>
                        <input type="date" name="joined_shakha_at" value="{{ now()->toDateString() }}" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Note (optional)</label>
                        <input type="text" name="note" class="h-9 w-full rounded-lg border-slate-200 text-[12px]" placeholder="Reason / order no.">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="transferOpen = false" class="h-8 rounded-lg px-3 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-sky-700 px-3 text-[12px] font-medium text-white hover:bg-sky-800">Transfer</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Fire popup --}}
        <div
            x-show="fireOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
            @click.self="fireOpen = false"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-4 shadow-xl" @click.stop>
                <div class="mb-3">
                    <p class="text-[14px] font-semibold text-navy-900">Fire (চাকরিচ্যুত)</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">
                        {{ $employee->name }} · {{ $employee->employee_code }} — will leave audit name suggestions
                    </p>
                </div>
                <form method="POST" action="{{ route('shakha-employees.fire', $employee) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Note (optional)</label>
                        <input type="text" name="note" class="h-9 w-full rounded-lg border-slate-200 text-[12px]" placeholder="Reason">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="fireOpen = false" class="h-8 rounded-lg px-3 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-rose-700 px-3 text-[12px] font-medium text-white hover:bg-rose-800">Mark as fired</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
