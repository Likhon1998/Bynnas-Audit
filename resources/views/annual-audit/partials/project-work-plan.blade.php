@php
    $mode = $mode ?? 'monitoring'; // audit | monitoring
    $isAudit = $mode === 'audit';
    $tabKey = $isAudit ? 'project_audit' : 'project_monitoring';
    $title = $isAudit ? 'Project Audit Work Plan' : 'Project Monitoring Work Plan';
    $flagLabel = $isAudit ? 'Also include in Project Monitoring' : 'Also include in Project Audit';
    $otherFlag = $isAudit ? 'has_project_monitoring' : 'has_project_audit';
    $category = $isAudit
        ? \App\Models\AuditPolicy::CATEGORY_PROJECT_AUDIT
        : \App\Models\AuditPolicy::CATEGORY_PROJECT_MONITORING;

    $fy = $plan->fy_label;
    $fyParts = explode('-', $fy);
    $startYear = substr($fyParts[0] ?? '2026', -2);
    $endYear = substr($fyParts[1] ?? '2027', -2);
@endphp

<div x-data="{ showAddProject: false, openLocationFor: null }">
    <div class="border-b border-slate-200 bg-slate-50/80 px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-[13px] font-semibold text-navy-900">{{ $title }}</p>
                <p class="text-[11px] text-slate-500">
                    July {{ $fyParts[0] ?? '' }} to June {{ $fyParts[1] ?? '' }}
                    · same projects master as
                    <a href="{{ route('projects.index') }}" class="font-medium text-brand-600 hover:underline">Projects</a>
                    @if ($isAudit)
                        · matches Excel <span class="font-medium text-slate-600">Project Audit</span> sheet
                    @else
                        · matches Excel <span class="font-medium text-slate-600">Project Monitoring</span> sheet
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('annual-audit.export', ['mode' => $isAudit ? 'audit' : 'monitoring', 'fy' => $plan->fy_label]) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[12px] font-medium text-emerald-800 hover:bg-emerald-100"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Excel
                </a>
                <button
                    type="button"
                    @click="showAddProject = !showAddProject"
                    class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800"
                >
                    <span class="text-[13px] leading-none">+</span>
                    Add Project
                </button>
            </div>
        </div>

        <div x-show="showAddProject" x-cloak class="mt-3 rounded-xl border border-slate-200 bg-white p-4">
            <p class="mb-3 text-[12px] font-medium text-navy-900">New {{ $isAudit ? 'audit' : 'monitoring' }} project</p>
            <form method="POST" action="{{ route('annual-audit.projects.store') }}" x-data="{ locations: [{ name: '', division: '' }] }">
                @csrf
                <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                <input type="hidden" name="return_tab" value="{{ $tabKey }}">
                <input type="hidden" name="status" value="active">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-400">Name of the Project</label>
                        <input type="text" name="name" required placeholder="e.g. DSK-WASH Water Aid Project" class="block w-full rounded-lg border-slate-200 text-[12px]" value="{{ old('name') }}">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-medium uppercase tracking-wide text-slate-400">Donor</label>
                        <input type="text" name="donor" placeholder="e.g. Water Aid" class="block w-full rounded-lg border-slate-200 text-[12px]" value="{{ old('donor') }}">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-[12px] text-slate-600">
                            <input type="checkbox" name="{{ $otherFlag }}" value="1" class="rounded border-slate-300 text-brand-600">
                            {{ $flagLabel }}
                        </label>
                    </div>
                </div>

                <div class="mt-3 border-t border-slate-100 pt-3">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-[11px] font-medium text-slate-600">Locations of the Project</p>
                        <button type="button" @click="locations.push({ name: '', division: '' })" class="text-[11px] font-medium text-brand-600 hover:underline">+ Location</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(loc, index) in locations" :key="index">
                            <div class="grid gap-2 sm:grid-cols-12">
                                <input type="text" :name="'locations['+index+'][name]'" x-model="loc.name" required placeholder="Location e.g. Dhaka" class="sm:col-span-7 rounded-lg border-slate-200 text-[12px]">
                                <select :name="'locations['+index+'][division]'" x-model="loc.division" class="sm:col-span-4 rounded-lg border-slate-200 text-[12px]">
                                    <option value="">Division</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division }}">{{ $division }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="if (locations.length > 1) locations.splice(index, 1)" class="sm:col-span-1 text-[11px] text-rose-500">×</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-3 flex justify-end gap-2">
                    <button type="button" @click="showAddProject = false" class="rounded-lg px-3 py-1.5 text-[12px] text-slate-500 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Save Project</button>
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-emerald-50/60 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                    <th class="border border-slate-200 px-2 py-2 text-center w-10">#</th>
                    <th class="border border-slate-200 px-3 py-2 min-w-[220px]">Name of the Projects / Donor</th>
                    <th class="border border-slate-200 px-3 py-2 min-w-[180px]">Location of the Projects</th>
                    @foreach ($months as $month)
                        @php
                            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
                            $monthName = match ($month['month']) {
                                7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
                                default => $month['label'],
                            };
                        @endphp
                        <th class="border border-slate-200 px-1 py-2 text-center text-[9px] leading-tight min-w-[52px]">
                            {{ $monthName }}'{{ $shortYear }}
                        </th>
                    @endforeach
                    <th class="border border-slate-200 px-2 py-2 text-center w-12">Total</th>
                    <th class="border border-slate-200 px-2 py-2 w-28">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projectGroups as $group)
                    @if ($group['rows']->isEmpty())
                        <tr class="text-[12px]">
                            <td class="border border-slate-200 px-2 py-2 text-center text-slate-500">{{ $group['sl'] }}</td>
                            <td class="border border-slate-200 px-3 py-2 align-top">
                                <p class="font-medium text-navy-900">{{ $group['project'] }}</p>
                                @if ($group['donor'])
                                    <p class="mt-0.5 text-[11px] text-slate-500">{{ $group['donor'] }}</p>
                                @endif
                            </td>
                            <td colspan="{{ count($months) + 1 }}" class="border border-slate-200 px-3 py-3 text-slate-400">
                                No locations yet.
                                <button type="button" @click="openLocationFor = openLocationFor === {{ $group['project_id'] }} ? null : {{ $group['project_id'] }}" class="ml-1 font-medium text-brand-600 hover:underline">Add location</button>
                            </td>
                            <td class="border border-slate-200 px-2 py-2 text-center">
                                <form method="POST" action="{{ route('annual-audit.projects.destroy', $group['project_id']) }}" onsubmit="return confirm('Delete this project and all its locations?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return_tab" value="{{ $tabKey }}">
                                    <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                                    <button type="submit" class="text-[10px] font-medium text-rose-500 hover:underline">Delete project</button>
                                </form>
                            </td>
                        </tr>
                        <tr x-show="openLocationFor === {{ $group['project_id'] }}" x-cloak>
                            <td colspan="{{ count($months) + 5 }}" class="border border-slate-200 bg-slate-50 px-3 py-3">
                                <form method="POST" action="{{ route('annual-audit.projects.locations.store', $group['project_id']) }}" class="flex flex-wrap items-end gap-2">
                                    @csrf
                                    <input type="hidden" name="return_tab" value="{{ $tabKey }}">
                                    <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                                    <div>
                                        <label class="mb-1 block text-[10px] font-medium text-slate-400">Location</label>
                                        <input type="text" name="name" required placeholder="e.g. Savar (Unit Office)" class="rounded-lg border-slate-200 text-[12px]">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[10px] font-medium text-slate-400">Division</label>
                                        <select name="division" class="rounded-lg border-slate-200 text-[12px]">
                                            <option value="">Optional</option>
                                            @foreach ($divisions as $division)
                                                <option value="{{ $division }}">{{ $division }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white">Add</button>
                                    <button type="button" @click="openLocationFor = null" class="rounded-lg px-3 py-1.5 text-[12px] text-slate-500 hover:bg-white">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    @else
                        @foreach ($group['rows'] as $index => $row)
                            <tr
                                class="text-[12px]"
                                x-data="{ total: {{ (int) $row['total'] }} }"
                                @audit-tick="total += $event.detail.delta"
                            >
                                @if ($index === 0)
                                    <td rowspan="{{ $group['rows']->count() }}" class="border border-slate-200 px-2 py-2 text-center align-middle font-medium text-slate-600">{{ $group['sl'] }}</td>
                                    <td rowspan="{{ $group['rows']->count() }}" class="border border-slate-200 px-3 py-2 align-top">
                                        <p class="font-medium leading-snug text-navy-900">{{ $group['project'] }}</p>
                                        @if ($group['donor'])
                                            <p class="mt-1 text-[11px] text-slate-500">{{ $group['donor'] }}</p>
                                        @endif
                                    </td>
                                @endif
                                <td class="border border-slate-200 px-3 py-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-slate-700">{{ $row['location'] }}</span>
                                        <form method="POST" action="{{ route('annual-audit.projects.locations.destroy', [$group['project_id'], $row['id']]) }}" onsubmit="return confirm('Remove location {{ $row['location'] }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="return_tab" value="{{ $tabKey }}">
                                    <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                                            <button type="submit" class="shrink-0 text-[10px] font-medium text-rose-500 hover:underline" title="Remove location">Remove</button>
                                        </form>
                                    </div>
                                </td>
                                @foreach ($row['months'] as $monthIndex => $active)
                                    <td class="border border-slate-200 px-0 py-0 text-center {{ $active ? 'bg-emerald-100' : 'bg-white' }}">
                                        <x-audit-month-mark
                                            :active="(bool) $active"
                                            :manual="(bool) ($row['manual'][$monthIndex] ?? false)"
                                            :editable="$canEditSchedule"
                                            :category="$category"
                                            :schedulable-type="$row['schedulable_type']"
                                            :schedulable-id="$row['id']"
                                            :month-index="$monthIndex"
                                            :tab="$tabKey"
                                            :fy="$plan->fy_label"
                                        />
                                    </td>
                                @endforeach
                                <td class="border border-slate-200 px-2 py-1.5 text-center font-semibold text-navy-900" x-text="total">{{ $row['total'] }}</td>
                                @if ($index === 0)
                                    <td rowspan="{{ $group['rows']->count() }}" class="border border-slate-200 px-2 py-2 align-middle">
                                        <div class="flex flex-col items-center gap-1">
                                            <button type="button" @click="openLocationFor = openLocationFor === {{ $group['project_id'] }} ? null : {{ $group['project_id'] }}" class="text-[10px] font-medium text-brand-600 hover:underline">+ Loc</button>
                                            <form method="POST" action="{{ route('annual-audit.projects.destroy', $group['project_id']) }}" onsubmit="return confirm('Delete project {{ $group['project'] }} and all locations?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="return_tab" value="{{ $tabKey }}">
                                    <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                                                <button type="submit" class="text-[10px] font-medium text-rose-500 hover:underline">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        <tr x-show="openLocationFor === {{ $group['project_id'] }}" x-cloak>
                            <td colspan="{{ count($months) + 5 }}" class="border border-slate-200 bg-slate-50 px-3 py-3">
                                <form method="POST" action="{{ route('annual-audit.projects.locations.store', $group['project_id']) }}" class="flex flex-wrap items-end gap-2">
                                    @csrf
                                    <input type="hidden" name="return_tab" value="{{ $tabKey }}">
                                    <input type="hidden" name="fy" value="{{ $plan->fy_label }}">
                                    <div>
                                        <label class="mb-1 block text-[10px] font-medium text-slate-400">Location</label>
                                        <input type="text" name="name" required placeholder="e.g. Khulna (Unit Office)" class="rounded-lg border-slate-200 text-[12px]">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[10px] font-medium text-slate-400">Division</label>
                                        <select name="division" class="rounded-lg border-slate-200 text-[12px]">
                                            <option value="">Optional</option>
                                            @foreach ($divisions as $division)
                                                <option value="{{ $division }}">{{ $division }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white">Add location</button>
                                    <button type="button" @click="openLocationFor = null" class="rounded-lg px-3 py-1.5 text-[12px] text-slate-500 hover:bg-white">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ count($months) + 5 }}" class="border border-slate-200 px-4 py-10 text-center text-[12px] text-slate-400">
                            No {{ $isAudit ? 'audit' : 'monitoring' }} projects yet. Click <span class="font-medium text-navy-800">Add Project</span> to match your Excel work plan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="border-t border-slate-100 px-4 py-2 text-[11px] text-slate-500">
        Green cells = planned {{ $isAudit ? 'audit' : 'monitoring' }} visit (like Excel). Click to add/remove. Use <span class="font-medium text-slate-700">Remove</span> / <span class="font-medium text-slate-700">Delete</span> to undo mistakes.
    </p>
</div>
