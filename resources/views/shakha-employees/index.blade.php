<x-app-layout>
    @php
        $selectedShakha = $filters['shakha_id']
            ? $shakhas->firstWhere('id', (int) $filters['shakha_id'])
            : null;

        $areaOptions = $areas->map(fn ($area) => [
            'id' => (string) $area->id,
            'name' => $area->name,
            'shakhas' => $area->shakhas->map(fn ($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
            ])->values(),
        ])->values();
    @endphp

    <div
        class="px-4 py-5 lg:px-6"
        x-data="{
            addOpen: false,
            areaId: @js((string) ($filters['area_id'] ?? '')),
            shakhaId: @js((string) ($filters['shakha_id'] ?? '')),
            areas: @js($areaOptions),
            get shakhasForArea() {
                const area = this.areas.find((a) => a.id === String(this.areaId));
                return area ? area.shakhas : [];
            },
            onAreaChange() {
                if (!this.shakhasForArea.some((s) => s.id === String(this.shakhaId))) {
                    this.shakhaId = '';
                }
            },
            manageUrl() {
                if (!this.shakhaId) return '';
                return `{{ url('/shakhas') }}/${this.shakhaId}/employees`;
            },
            goAdd() {
                if (!this.shakhaId) return;
                window.location.href = this.manageUrl();
            }
        }"
    >
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">Shakha Employees</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Add and manage branch staff under Area → Shakha
                </p>
            </div>
            @if ($canManage)
                @if ($selectedShakha)
                    <a
                        href="{{ route('shakha-employees.manage', $selectedShakha) }}"
                        class="inline-flex h-9 items-center gap-1 rounded-lg bg-navy-900 px-3.5 text-[12px] font-semibold text-white hover:bg-navy-800"
                    >
                        <span class="text-[14px] leading-none">+</span>
                        Add employee to {{ $selectedShakha->name }}
                    </a>
                @else
                    <button
                        type="button"
                        @click="addOpen = true"
                        class="inline-flex h-9 items-center gap-1 rounded-lg bg-navy-900 px-3.5 text-[12px] font-semibold text-white hover:bg-navy-800"
                    >
                        <span class="text-[14px] leading-none">+</span>
                        Add employee
                    </button>
                @endif
            @endif
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        {{-- Add picker --}}
        <div
            x-show="addOpen"
            x-cloak
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 p-4"
            @keydown.escape.window="addOpen = false"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-4 shadow-xl" @click.outside="addOpen = false">
                <div class="mb-3">
                    <p class="text-[14px] font-semibold text-navy-900">Add employee</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Choose the area and shakha first, then enter employee details.</p>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Area</label>
                        <select x-model="areaId" @change="onAreaChange()" class="h-9 w-full rounded-lg border-slate-200 text-[12px]">
                            <option value="">Select area…</option>
                            <template x-for="area in areas" :key="area.id">
                                <option :value="area.id" x-text="area.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Shakha</label>
                        <select x-model="shakhaId" class="h-9 w-full rounded-lg border-slate-200 text-[12px]" :disabled="!areaId">
                            <option value="">Select shakha…</option>
                            <template x-for="shakha in shakhasForArea" :key="shakha.id">
                                <option :value="shakha.id" x-text="shakha.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-2">
                    <button type="button" @click="addOpen = false" class="h-8 rounded-lg px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button
                        type="button"
                        @click="goAdd()"
                        :disabled="!shakhaId"
                        class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800 disabled:cursor-not-allowed disabled:opacity-40"
                    >Continue</button>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('shakha-employees.index') }}" class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-3 py-2">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-white text-slate-500 shadow-sm ring-1 ring-slate-200">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 01.8 1.6L14 13.67V19a1 1 0 01-.55.9l-4 2A1 1 0 018 21v-7.33L3.2 4.6A1 1 0 013 4z"/></svg>
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold text-navy-900">Filter employees</p>
                        <p class="text-[9px] text-slate-400">{{ number_format($employees->count()) }} employee(s) shown</p>
                    </div>
                </div>
                @if ($filters['area_id'] || $filters['shakha_id'] || $filters['status'] !== 'all' || $filters['q'] !== '')
                    <a href="{{ route('shakha-employees.index') }}" class="rounded-md px-2 py-1 text-[10px] font-semibold text-rose-600 hover:bg-rose-50">Clear filters</a>
                @endif
            </div>
            <div class="grid grid-cols-1 gap-3 p-3 sm:grid-cols-2 xl:grid-cols-[minmax(150px,0.9fr)_minmax(220px,1.25fr)_minmax(130px,0.7fr)_minmax(280px,1.6fr)] xl:items-end">
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Area</label>
                    <select name="area_id" class="h-9 w-full rounded-lg border-slate-200 bg-white text-[12px] shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]" onchange="this.form.shakha_id.value=''; this.form.submit()">
                        <option value="">All areas</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected((int) $filters['area_id'] === $area->id)>{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Shakha</label>
                    <select name="shakha_id" class="h-9 w-full rounded-lg border-slate-200 bg-white text-[12px] shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]" onchange="this.form.submit()">
                        <option value="">All shakhas</option>
                        @foreach ($shakhas as $shakha)
                            <option value="{{ $shakha->id }}" @selected((int) $filters['shakha_id'] === $shakha->id)>{{ $shakha->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</label>
                    <select name="status" class="h-9 w-full rounded-lg border-slate-200 bg-white text-[12px] shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]" onchange="this.form.submit()">
                        <option value="all" @selected($filters['status'] === 'all')>All</option>
                        <option value="active" @selected($filters['status'] === 'active')>Active</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Search roster</label>
                    <div class="flex gap-2">
                        <div class="relative min-w-0 flex-1">
                            <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                            <input
                                type="search"
                                name="q"
                                value="{{ $filters['q'] }}"
                                placeholder="Employee ID, name or designation"
                                class="h-9 w-full rounded-lg border-slate-200 bg-white pl-8 text-[12px] shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]"
                            >
                        </div>
                        <button type="submit" class="inline-flex h-9 shrink-0 items-center rounded-lg bg-navy-900 px-4 text-[12px] font-semibold text-white shadow-sm hover:bg-navy-800">Search</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="mb-4 grid gap-3 lg:grid-cols-[280px_minmax(0,1fr)]">
            <div
                class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card"
                x-data="{
                    branchQ: '',
                    branches: @js($byShakha->map(fn ($b) => [
                        'id' => $b->id,
                        'name' => $b->name,
                        'area' => (string) ($b->area?->name ?: ''),
                        'count' => (int) $b->employees_count,
                        'url' => route('shakha-employees.manage', $b),
                        'selected' => (int) $filters['shakha_id'] === $b->id,
                    ])->values()),
                    get filteredBranches() {
                        const q = this.branchQ.trim().toLowerCase();
                        if (!q) return this.branches;
                        return this.branches.filter((b) =>
                            (b.name + ' ' + b.area).toLowerCase().includes(q)
                        );
                    }
                }"
            >
                <div class="border-b border-slate-100 px-3.5 py-2.5">
                    <p class="text-[12px] font-semibold text-navy-900">Shakhas</p>
                    <p class="mt-0.5 text-[10px] text-slate-500">Search, then open a branch to manage staff</p>
                    <div class="relative mt-2">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                        <input
                            type="search"
                            x-model="branchQ"
                            placeholder="Search shakha or area…"
                            class="h-8 w-full rounded-lg border-slate-200 pl-8 text-[12px] placeholder:text-slate-400"
                        >
                    </div>
                </div>
                <div class="max-h-[28rem] divide-y divide-slate-100 overflow-y-auto">
                    <template x-for="branch in filteredBranches" :key="branch.id">
                        <a
                            :href="branch.url"
                            class="flex items-start justify-between gap-2 px-3.5 py-2.5 hover:bg-slate-50"
                            :class="branch.selected ? 'bg-sky-50' : ''"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-[12px] font-medium text-navy-900" x-text="branch.name"></p>
                                <p class="truncate text-[10px] text-slate-500" x-text="branch.area || '—'"></p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600" x-text="branch.count"></span>
                                @if ($canManage)
                                    <span class="text-[10px] font-semibold text-[#2b579a]">+ Add</span>
                                @endif
                            </div>
                        </a>
                    </template>
                    <p x-show="filteredBranches.length === 0" class="px-3.5 py-8 text-center text-[12px] text-slate-400">
                        No shakhas match your search.
                    </p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-2.5">
                    <div>
                        <p class="text-[12px] font-semibold text-navy-900">Employees</p>
                        <p class="text-[10px] text-slate-500">{{ $employees->count() }} matching</p>
                    </div>
                    @if ($canManage && $selectedShakha)
                        <a href="{{ route('shakha-employees.manage', $selectedShakha) }}" class="text-[11px] font-semibold text-[#2b579a] hover:underline">
                            Open {{ $selectedShakha->name }} form →
                        </a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-slate-100 bg-slate-50/80">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-3.5 py-2.5">Photo</th>
                                <th class="px-3.5 py-2.5">Employee ID</th>
                                <th class="px-3.5 py-2.5">Name</th>
                                <th class="px-3.5 py-2.5">Designation</th>
                                <th class="px-3.5 py-2.5">Area / Shakha</th>
                                <th class="px-3.5 py-2.5">Status</th>
                                <th class="px-3.5 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($employees as $employee)
                                <tr class="text-[12px]">
                                    <td class="px-3.5 py-2.5">
                                        @include('shakha-employees.partials.photo', ['employee' => $employee, 'size' => 'sm'])
                                    </td>
                                    <td class="px-3.5 py-2.5 font-semibold text-navy-900">{{ $employee->employee_code }}</td>
                                    <td class="px-3.5 py-2.5 text-slate-700">{{ $employee->name }}</td>
                                    <td class="px-3.5 py-2.5 text-slate-600">{{ $employee->designation }}</td>
                                    <td class="px-3.5 py-2.5 text-slate-600">
                                        <span class="block">{{ $employee->shakha?->name }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $employee->shakha?->area?->name }}</span>
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        @if ($employee->isActive())
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Active</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-600">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2.5 text-right">
                                        <a href="{{ route('shakha-employees.manage', $employee->shakha) }}" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Manage</a>
                                        @if ($canManage)
                                            <a href="{{ route('shakha-employees.edit', $employee) }}" class="ml-2 text-[11px] font-semibold text-slate-600 hover:underline">Edit</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3.5 py-10 text-center text-[12px] text-slate-400">
                                        <p>No employees yet for these filters.</p>
                                        @if ($canManage)
                                            <button type="button" @click="addOpen = true" class="mt-2 font-semibold text-[#2b579a] hover:underline">
                                                + Add the first employee
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
