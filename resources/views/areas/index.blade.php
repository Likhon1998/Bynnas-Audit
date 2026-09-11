<x-app-layout>
    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">All Areas</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Division → Area → Shakha · {{ $areas->count() }} area{{ $areas->count() === 1 ? '' : 's' }}
                </p>
            </div>
            <a href="{{ route('areas.create') }}" class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                <span class="text-[13px] leading-none">+</span>
                Add Area
            </a>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('areas.index') }}" class="mb-3 flex flex-wrap items-end gap-2 rounded-xl border border-slate-100 bg-white p-3 shadow-card">
            <div class="min-w-[180px]">
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Division</label>
                <select name="division" class="h-9 w-full rounded-lg border-slate-200 text-[12px]" onchange="this.form.submit()">
                    <option value="">All divisions</option>
                    @foreach ($divisions as $divisionOption)
                        <option value="{{ $divisionOption }}" @selected($selectedDivision === $divisionOption)>{{ $divisionOption }}</option>
                    @endforeach
                </select>
            </div>
            @if ($selectedDivision !== '')
                <a href="{{ route('areas.index') }}" class="mb-0.5 rounded-md px-2 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50">Clear</a>
            @endif
        </form>

        <div class="space-y-4">
            @forelse ($groupedAreas as $divisionName => $divisionAreas)
                <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-2.5">
                        <div>
                            <p class="text-[12px] font-semibold text-navy-900">{{ $divisionName ?: 'Unassigned division' }}</p>
                            <p class="text-[10px] text-slate-500">{{ $divisionAreas->count() }} area{{ $divisionAreas->count() === 1 ? '' : 's' }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="border-b border-slate-100">
                                <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    <th class="px-4 py-2.5">Area Name</th>
                                    <th class="px-4 py-2.5">Shakhas</th>
                                    <th class="px-4 py-2.5">Status</th>
                                    <th class="px-4 py-2.5">Added On</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($divisionAreas as $area)
                                    <tr class="text-[12px]">
                                        <td class="px-4 py-2.5 font-medium text-navy-900">{{ $area->name }}</td>
                                        <td class="px-4 py-2.5 text-slate-600">{{ $area->shakhas_count }}</td>
                                        <td class="px-4 py-2.5">
                                            @if ($area->isActive())
                                                <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Active</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-600">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-slate-500">{{ $area->created_at->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-100 bg-white px-4 py-10 text-center text-[12px] text-slate-400 shadow-card">
                    No areas yet.
                    <a href="{{ route('areas.create') }}" class="font-medium text-brand-600 hover:underline">Add the first one</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
