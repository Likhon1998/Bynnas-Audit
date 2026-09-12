<x-app-layout>
    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="mb-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="{{ route('audit-review.index') }}" class="hover:text-brand-600">Review Panel</a>
                    <span>/</span>
                    <span class="text-slate-600">Assignments</span>
                </div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Reviewer assignments</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Fixed map: Auditor X → Reviewer Y</p>
            </div>
            <a href="{{ route('audit-review.index') }}" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Back</a>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('audit-review.assignments.save') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-3 py-2.5">Auditor</th>
                            <th class="px-3 py-2.5">Email</th>
                            <th class="px-3 py-2.5">Reviewer</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($auditors as $i => $auditor)
                            @php $current = $map->get($auditor->id); @endphp
                            <tr>
                                <td class="px-3 py-2.5 font-medium text-slate-800">
                                    {{ $auditor->name }}
                                    <input type="hidden" name="assignments[{{ $i }}][auditor_user_id]" value="{{ $auditor->id }}">
                                </td>
                                <td class="px-3 py-2.5 text-slate-500">{{ $auditor->email }}</td>
                                <td class="px-3 py-2.5">
                                    <select name="assignments[{{ $i }}][reviewer_user_id]" class="h-9 w-full max-w-sm rounded-md border-slate-200 text-[12px]">
                                        <option value="">— No reviewer —</option>
                                        @foreach ($reviewers as $reviewer)
                                            @if ((int) $reviewer->id === (int) $auditor->id)
                                                @continue
                                            @endif
                                            <option value="{{ $reviewer->id }}" @selected((int) ($current?->reviewer_user_id) === (int) $reviewer->id)>
                                                {{ $reviewer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-10 text-center text-slate-400">No auditors with audits.create permission.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($auditors->isNotEmpty())
                <div class="border-t border-slate-100 px-3 py-3">
                    <button type="submit" class="inline-flex h-9 items-center rounded-md bg-navy-900 px-4 text-[12px] font-semibold text-white hover:bg-slate-800">
                        Save assignments
                    </button>
                </div>
            @endif
        </form>
    </div>
</x-app-layout>
