<x-app-layout>
    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4">
            <a href="{{ route('monthly-visits.index', ['fy' => $assignment->workItem->fy_label, 'month' => $assignment->workItem->month_index]) }}" class="text-[11px] font-medium text-brand-600 hover:underline">← Back</a>
            <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">Record execution</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">
                {{ $assignment->workItem->entity_label }} · {{ $assignment->visitorNames(', ') }}
                · Planned {{ $assignment->visitDateRangeLabel() }}
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="max-w-xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <form method="POST" action="{{ route('monthly-visits.execution.store', $assignment) }}" class="space-y-3 px-4 py-4">
                @csrf
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Status</label>
                    <select name="status" required class="block w-full rounded-lg border-slate-200 text-[13px]">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $assignment->execution?->status ?? 'planned') === $status)>
                                {{ str_replace('_', ' ', ucfirst($status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Actual start</label>
                        <input type="date" name="actual_start_date" value="{{ old('actual_start_date', $assignment->execution?->actual_start_date?->toDateString() ?? $assignment->start_date?->toDateString()) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Actual end</label>
                        <input type="date" name="actual_end_date" value="{{ old('actual_end_date', $assignment->execution?->actual_end_date?->toDateString() ?? $assignment->end_date?->toDateString()) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Actual visitor (if different)</label>
                    <select name="actual_employee_id" class="block w-full rounded-lg border-slate-200 text-[13px]">
                        <option value="">Same as planned ({{ $assignment->employee?->name }})</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('actual_employee_id', $assignment->execution?->actual_employee_id) == $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Remarks</label>
                    <textarea name="remarks" rows="2" class="block w-full rounded-lg border-slate-200 text-[13px]">{{ old('remarks', $assignment->execution?->remarks) }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Status change reason</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="submit" class="rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Save execution</button>
                </div>
            </form>

            @if ($assignment->statusLogs->isNotEmpty())
                <div class="border-t border-slate-100 px-4 py-3">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">History</p>
                    <ul class="space-y-1 text-[11px] text-slate-600">
                        @foreach ($assignment->statusLogs->sortByDesc('id') as $log)
                            <li>{{ $log->created_at?->format('d M Y H:i') }}: {{ $log->from_status ?? '—' }} → {{ $log->to_status }} @if($log->reason)— {{ $log->reason }}@endif</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
