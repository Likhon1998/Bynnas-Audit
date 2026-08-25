<x-app-layout>
    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4">
            <a href="{{ route('monthly-visits.index', ['fy' => $assignment->workItem->fy_label, 'month' => $assignment->workItem->month_index]) }}" class="text-[11px] font-medium text-brand-600 hover:underline">← Back</a>
            <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">Reschedule visit</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">
                {{ $assignment->workItem?->entity_label }} · Working days auto-calculated (Fri/Sat &amp; holidays excluded unless special request).
            </p>
            <p class="mt-1 text-[12px] text-slate-600">
                Current: {{ $assignment->visitDateRangeLabel() }}
                @if ($assignment->original_start_date)
                    · First planned: {{ $assignment->original_start_date->format('d M') }}–{{ $assignment->original_end_date?->format('d M Y') }}
                @endif
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700">{{ $errors->first() }}</div>
        @endif

        <div class="max-w-xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <form method="POST" action="{{ route('monthly-visits.reschedule.store', $assignment) }}" class="space-y-3 px-4 py-4">
                @csrf
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Visitor(s) — one or more</label>
                    <div class="max-h-40 space-y-1 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50/50 p-2">
                        @php $selected = collect(old('employee_ids', $assignment->visitorList()->pluck('id')->all()))->map(fn ($id) => (int) $id); @endphp
                        @foreach ($employees as $employee)
                            <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-[12px] hover:bg-white">
                                <input
                                    type="checkbox"
                                    name="employee_ids[]"
                                    value="{{ $employee->id }}"
                                    class="rounded border-slate-300 text-emerald-600"
                                    @checked($selected->contains((int) $employee->id))
                                >
                                <span class="font-medium text-navy-900">{{ $employee->name }}</span>
                                @if ($employee->position)
                                    <span class="text-slate-400">— {{ $employee->position->title }}</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">New start date</label>
                        <input type="date" name="start_date" required value="{{ old('start_date', $assignment->start_date?->toDateString()) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">New end date</label>
                        <input type="date" name="end_date" required value="{{ old('end_date', $assignment->end_date?->toDateString()) }}" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                </div>
                <label class="flex items-start gap-2 rounded-lg border border-amber-100 bg-amber-50/60 px-3 py-2 text-[12px] text-amber-950">
                    <input type="checkbox" name="count_off_days" value="1" class="mt-0.5 rounded border-amber-300 text-amber-600" @checked(old('count_off_days', $assignment->count_off_days))>
                    <span>Special request — count Fri/Sat &amp; holidays as working days</span>
                </label>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Reschedule reason (required)</label>
                    <textarea name="reschedule_reason" required rows="2" class="block w-full rounded-lg border-slate-200 text-[13px]">{{ old('reschedule_reason') }}</textarea>
                </div>
                <p class="text-[11px] text-slate-500">Same person cannot be scheduled at two places on overlapping dates — conflicts are blocked.</p>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="submit" class="rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Save reschedule</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
