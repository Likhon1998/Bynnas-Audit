<x-app-layout>
    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4">
            <a href="{{ route('monthly-visits.index', ['fy' => $item->fy_label, 'month' => $item->month_index]) }}" class="text-[11px] font-medium text-brand-600 hover:underline">← Back to monthly worklist</a>
            <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">Assign visitor</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">
                {{ $item->entity_label }} · {{ $item->activityType?->name }} · {{ $fy->months()[$item->month_index]['label'] }} {{ $fy->months()[$item->month_index]['year'] }}
            </p>
        </div>

        @if (session('conflict_warning'))
            <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] text-amber-900">
                <p class="font-semibold">{{ session('conflict_warning') }}</p>
                @foreach (session('conflicts', []) as $c)
                    <p class="mt-1 text-amber-800">• {{ $c->employee?->name }}: {{ $c->start_date?->format('d M') }}–{{ $c->end_date?->format('d M') }} ({{ $c->workItem?->entity_label }})</p>
                @endforeach
                <p class="mt-2 text-[11px]">Check “Override conflict” below to save anyway.</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="max-w-xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <form method="POST" action="{{ route('monthly-visits.assign.store', $item) }}" class="space-y-3 px-4 py-4">
                @csrf
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Assigned person (Organogram)</label>
                    <select name="employee_id" required class="block w-full rounded-lg border-slate-200 text-[13px]">
                        <option value="">Select staff</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                {{ $employee->name }}{{ $employee->position ? ' — '.$employee->position->title : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Start date</label>
                        <input type="date" name="start_date" required value="{{ old('start_date', $defaultStart) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">End date</label>
                        <input type="date" name="end_date" required value="{{ old('end_date', $defaultEnd) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Start time (optional)</label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">End time (optional)</label>
                        <input type="time" name="end_time" value="{{ old('end_time') }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Duration mode</label>
                        <select name="duration_mode" class="block w-full rounded-lg border-slate-200 text-[13px]">
                            <option value="calendar" @selected(old('duration_mode', 'calendar') === 'calendar')>Calendar days</option>
                            <option value="working" @selected(old('duration_mode') === 'working')>Working days</option>
                            <option value="manual" @selected(old('duration_mode') === 'manual')>Manual days</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Manual duration (if manual)</label>
                        <input type="number" min="1" max="60" name="duration_days" value="{{ old('duration_days', 5) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Last audit / monitoring upto</label>
                    <input type="date" name="last_audit_upto" value="{{ old('last_audit_upto', $lastUpto) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    <p class="mt-1 text-[10px] text-slate-400">Pre-filled from history when available; override if needed.</p>
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Purpose</label>
                    <input type="text" name="purpose" value="{{ old('purpose', $item->activityType?->name) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Remarks</label>
                    <textarea name="remarks" rows="2" class="block w-full rounded-lg border-slate-200 text-[13px]">{{ old('remarks') }}</textarea>
                </div>

                @if (session('conflict_warning'))
                    <label class="flex items-center gap-2 text-[12px] text-amber-800">
                        <input type="checkbox" name="override_conflict" value="1" class="rounded border-amber-300 text-amber-600">
                        Override conflict and assign anyway
                    </label>
                @endif

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="{{ route('monthly-visits.index', ['fy' => $item->fy_label, 'month' => $item->month_index]) }}" class="rounded-lg px-3 py-1.5 text-[12px] text-slate-500 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Save assignment</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
