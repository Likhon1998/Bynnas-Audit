<x-app-layout>
    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-2.5">
            <div>
                <a href="{{ route('projects.index') }}" class="text-[11px] font-medium text-brand-600 hover:underline">← All projects</a>
                <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">{{ $project->name }}</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    {{ $project->donor ?: 'No donor' }}
                    <span class="mx-1 text-slate-300">·</span>
                    <span class="capitalize">{{ $project->status }}</span>
                </p>
            </div>
            <a href="{{ route('annual-audit.index', ['tab' => 'project_audit']) }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                Open Annual Audit
            </a>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap gap-1.5">
            @if ($project->is_pksf)
                <span class="rounded bg-sky-50 px-2 py-0.5 text-[11px] font-medium text-sky-700">PKSF</span>
            @endif
            @if ($project->is_maternity)
                <span class="rounded bg-violet-50 px-2 py-0.5 text-[11px] font-medium text-violet-700">Maternity</span>
            @endif
            @if ($project->has_project_audit)
                <span class="rounded bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">Project Audit</span>
            @endif
            @if ($project->has_project_monitoring)
                <span class="rounded bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">Project Monitoring</span>
            @endif
        </div>

        <div class="grid gap-4 lg:grid-cols-5">
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card lg:col-span-3">
                <div class="border-b border-slate-100 px-4 py-3">
                    <p class="text-[13px] font-medium text-navy-900">Locations</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Each active location can be scheduled in Annual Audit</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-slate-100 bg-slate-50/80">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-4 py-2.5">Name</th>
                                <th class="px-4 py-2.5">Division</th>
                                <th class="px-4 py-2.5">Status</th>
                                <th class="px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($project->locations as $location)
                                <tr class="text-[12px]">
                                    <td class="px-4 py-2.5 font-medium text-navy-900">{{ $location->name }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $location->division ?: '—' }}</td>
                                    <td class="px-4 py-2.5 capitalize text-slate-600">{{ $location->status }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <form method="POST" action="{{ route('projects.locations.destroy', [$project, $location]) }}" onsubmit="return confirm('Remove this location?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[11px] font-medium text-rose-500 hover:underline">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-[12px] text-slate-400">No locations yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card lg:col-span-2">
                <div class="border-b border-slate-100 px-4 py-3">
                    <p class="text-[13px] font-medium text-navy-900">Add location</p>
                </div>
                <form method="POST" action="{{ route('projects.locations.store', $project) }}" class="space-y-3 px-4 py-4">
                    @csrf
                    <div>
                        <label for="name" class="mb-1 block text-[11px] font-medium text-slate-600">Location name</label>
                        <x-text-input id="name" name="name" type="text" class="block w-full rounded-lg text-[13px]" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <label for="division" class="mb-1 block text-[11px] font-medium text-slate-600">Division</label>
                        <select id="division" name="division" class="block w-full rounded-lg border-slate-200 text-[13px]">
                            <option value="">Optional</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division }}" @selected(old('division') === $division)>{{ $division }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="mb-1 block text-[11px] font-medium text-slate-600">Status</label>
                        <select id="status" name="status" required class="block w-full rounded-lg border-slate-200 text-[13px]">
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                        Add location
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
