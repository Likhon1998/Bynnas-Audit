<x-app-layout>
    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">All Shakha</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">Manage branches across areas and divisions</p>
            </div>
            <a href="{{ route('shakhas.create') }}" class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                <span class="text-[13px] leading-none">+</span>
                Add Shakha
            </a>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-4 py-2.5">Shakha Name</th>
                            <th class="px-4 py-2.5">Area</th>
                            <th class="px-4 py-2.5">Division</th>
                            <th class="px-4 py-2.5">Code</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5">Added On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($shakhas as $shakha)
                            <tr class="text-[12px]">
                                <td class="px-4 py-2.5 font-medium text-navy-900">{{ $shakha->name }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $shakha->area->name }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $shakha->area->division }}</td>
                                <td class="px-4 py-2.5 text-slate-500">{{ $shakha->code ?: '—' }}</td>
                                <td class="px-4 py-2.5">
                                    @if ($shakha->isActive())
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Active</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-600">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-slate-500">{{ $shakha->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-[12px] text-slate-400">
                                    No shakhas yet.
                                    <a href="{{ route('shakhas.create') }}" class="font-medium text-brand-600 hover:underline">Add the first one</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
