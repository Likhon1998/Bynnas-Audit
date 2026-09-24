<x-app-layout>
    <div class="px-3 py-3 lg:px-5">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-navy-900">Projects</h1>
                <p class="mt-0.5 text-[13px] text-slate-500">Master list — flags decide which Annual Audit tabs get schedules</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @canany(['annual_audit.view', 'annual_audit.manage'])
                    <a href="{{ route('annual-audit.index', ['tab' => 'project_audit']) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                        Open Annual Audit
                    </a>
                @endcanany
                @can('projects.manage')
                    <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                        <span class="text-[13px] leading-none">+</span>
                        Add Project
                    </a>
                @endcan
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">Project</th>
                            <th class="px-3 py-2">Donor</th>
                            <th class="px-3 py-2">Flags</th>
                            <th class="px-3 py-2">Locations</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($projects as $project)
                            <tr class="text-[12px]">
                                <td class="px-3 py-2 font-medium text-navy-900">{{ $project->name }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $project->donor ?: '—' }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-1">
                                        @if ($project->is_pksf)
                                            <span class="rounded bg-sky-50 px-1.5 py-0.5 text-xs font-medium text-sky-700">PKSF</span>
                                        @endif
                                        @if ($project->is_maternity)
                                            <span class="rounded bg-violet-50 px-1.5 py-0.5 text-xs font-medium text-violet-700">Maternity</span>
                                        @endif
                                        @if ($project->has_project_audit)
                                            <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-xs font-medium text-emerald-700">Audit</span>
                                        @endif
                                        @if ($project->has_project_monitoring)
                                            <span class="rounded bg-amber-50 px-1.5 py-0.5 text-xs font-medium text-amber-700">Monitoring</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-slate-600">{{ $project->locations_count }}</td>
                                <td class="px-3 py-2">
                                    @if ($project->isActive())
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-600">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('projects.show', $project) }}" class="font-medium text-brand-600 hover:underline">{{ auth()->user()->can('projects.manage') ? 'Manage' : 'View' }}</a>
                                    @canany(['annual_audit.view', 'annual_audit.manage'])
                                        <span class="mx-1 text-slate-300">·</span>
                                        <a href="{{ route('annual-audit.index', ['tab' => $project->preferredPlanTab(), 'project' => $project->id]) }}" class="font-medium text-slate-500 hover:underline">Plan</a>
                                    @endcanany
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-[13px] text-slate-500">
                                    No projects yet.
                                    @can('projects.manage')
                                        <a href="{{ route('projects.create') }}" class="font-medium text-brand-600 hover:underline">Add the first one</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
