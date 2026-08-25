<x-app-layout>
    @php
        $openEmployeeModal = $errors->hasAny(['name', 'email', 'position_id']);
        $openPositionModal = $errors->hasAny(['title', 'serial']);
        $officerCount = $positions->sum(fn ($position) => $position->employees->count());
    @endphp

    <div
        class="flex h-full min-h-0 flex-col px-4 py-3 lg:px-5"
        x-data="{ zoom: 1, addOpen: {{ $openEmployeeModal ? 'true' : 'false' }}, positionOpen: {{ $openPositionModal ? 'true' : 'false' }}, showOpen: false }"
        @keydown.escape.window="showOpen = false; addOpen = false; positionOpen = false"
    >
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2.5">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">Audit Organogram</h1>
                    <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500">{{ $positions->count() }} ranks</span>
                    <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500">{{ $officerCount }} officers</span>
                </div>
                <p class="mt-0.5 text-[11px] text-slate-500">Director Audit through Audit Officer — multiple people per rank</p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" @click="showOpen = true" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Preview
                </button>
                <button type="button" @click="positionOpen = true" class="inline-flex items-center gap-1 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1.5 text-[12px] font-medium text-brand-700 hover:bg-brand-100">
                    <span class="text-[13px] leading-none">+</span>
                    Position
                </button>
                <button
                    type="button"
                    @click="addOpen = true"
                    @disabled($positions->isEmpty())
                    class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span class="text-[13px] leading-none">+</span>
                    Employee
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-2 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-2 rounded-lg bg-red-50 px-3 py-2 text-[12px] text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="relative min-h-0 flex-1 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            <div class="dot-grid absolute inset-0 overflow-auto">
                <div class="flex min-h-full min-w-max origin-top justify-center px-4 py-5" :style="`transform: scale(${zoom});`">
                    <div class="flex flex-col items-center">
                        @if ($positions->isEmpty())
                            <div class="my-auto flex max-w-sm flex-col items-center rounded-xl border border-dashed border-slate-200 bg-white/90 px-5 py-6 text-center shadow-sm">
                                <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </div>
                                <p class="text-[13px] font-medium text-navy-900">No positions yet</p>
                                <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Create the first rank, then add officers under each level.</p>
                                <button type="button" @click="positionOpen = true" class="mt-3 inline-flex items-center gap-1 rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                                    <span class="text-[13px] leading-none">+</span>
                                    Add Position
                                </button>
                            </div>
                        @else
                            @foreach ($positions as $index => $position)
                                @if ($index > 0)
                                    <div class="h-5 w-px bg-slate-200"></div>
                                @endif

                                <div class="mb-2 inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-white" style="background-color: {{ $position->color }}">
                                    <span>{{ $position->serial }}. {{ $position->title }}</span>
                                    <span class="rounded bg-white/20 px-1 py-px text-[9px] font-medium normal-case tracking-normal">{{ $position->employees->count() }}</span>
                                </div>

                                @if ($position->employees->isEmpty())
                                    <button
                                        type="button"
                                        @click="addOpen = true"
                                        class="rounded-lg border border-dashed border-slate-200 bg-white px-3 py-2 text-[11px] text-slate-400 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600"
                                    >
                                        + Add officer to this rank
                                    </button>
                                @else
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @foreach ($position->employees as $employee)
                                            <div class="group relative">
                                                <x-org-node :name="$employee->name" :title="$position->title" :accent="$position->color" />
                                                <form method="POST" action="{{ route('organogram.employees.destroy', $employee) }}" class="absolute -right-1 -top-1 hidden group-hover:block" onsubmit="return confirm('Remove this officer from the organogram?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex h-4 w-4 items-center justify-center rounded-full bg-slate-700 text-[9px] leading-none text-white hover:bg-red-600" title="Remove">×</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="absolute bottom-3 right-3 flex overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <button type="button" class="px-2.5 py-1.5 text-[13px] font-medium text-slate-500 hover:bg-slate-50" @click="zoom = Math.min(+(zoom + 0.1).toFixed(1), 1.6)" title="Zoom in">+</button>
                <button type="button" class="min-w-[3rem] border-l border-slate-100 px-2 py-1.5 text-[11px] font-medium text-slate-400 hover:bg-slate-50" @click="zoom = 1" title="Reset zoom" x-text="Math.round(zoom * 100) + '%'"></button>
                <button type="button" class="border-l border-slate-100 px-2.5 py-1.5 text-[13px] font-medium text-slate-500 hover:bg-slate-50" @click="zoom = Math.max(+(zoom - 0.1).toFixed(1), 0.6)" title="Zoom out">−</button>
            </div>
        </div>

        {{-- Full organogram glance modal --}}
        <div
            x-show="showOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 px-4 backdrop-blur-[2px]"
            @click.self="showOpen = false"
        >
            <div
                class="flex w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl"
                x-transition
                @click.stop
            >
                <div class="flex items-start gap-3 border-b border-slate-100 px-4 py-3.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-[14px] font-semibold tracking-tight text-navy-900">Organogram preview</h2>
                        <p class="mt-0.5 text-[11px] text-slate-500">
                            {{ $positions->count() }} ranks · {{ $officerCount }} officers — full hierarchy at a glance
                        </p>
                    </div>
                    <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-50 hover:text-slate-600" @click="showOpen = false" aria-label="Close">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-4 py-3.5">
                    @if ($positions->isEmpty())
                        <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-[12px] text-slate-400">
                            No positions to preview yet.
                        </p>
                    @else
                        <div class="space-y-1.5">
                            @foreach ($positions as $position)
                                <div class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/70 px-2.5 py-2">
                                    <div class="flex min-w-[148px] max-w-[168px] shrink-0 items-center gap-1.5 pt-0.5">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md text-[10px] font-semibold text-white" style="background-color: {{ $position->color }}">
                                            {{ $position->serial }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-[11px] font-semibold leading-tight text-navy-900">{{ $position->title }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $position->employees->count() }} {{ $position->employees->count() === 1 ? 'officer' : 'officers' }}</p>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        @if ($position->employees->isEmpty())
                                            <p class="rounded-md border border-dashed border-slate-200 bg-white px-2 py-1 text-[10px] text-slate-400">No officers assigned</p>
                                        @else
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($position->employees as $employee)
                                                    @php
                                                        $initials = collect(preg_split('/\s+/', trim($employee->name)))
                                                            ->filter()
                                                            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                                            ->take(2)
                                                            ->implode('');
                                                    @endphp
                                                    <span class="inline-flex max-w-full items-center gap-1 rounded-md border border-slate-100 bg-white px-1.5 py-1 shadow-sm">
                                                        <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-[8px] font-semibold text-white" style="background-color: {{ $position->color }}">
                                                            {{ $initials }}
                                                        </span>
                                                        <span class="truncate text-[10px] font-medium text-slate-700">{{ $employee->name }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Add position modal --}}
        <div
            x-show="positionOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 px-4 backdrop-blur-[2px]"
            @click.self="positionOpen = false"
        >
            <div
                class="w-full max-w-sm overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl"
                x-transition
                @click.stop
            >
                <div class="flex items-start gap-3 border-b border-slate-100 px-4 py-3.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-[14px] font-semibold tracking-tight text-navy-900">Add position</h2>
                        <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">Create a new rank in the audit hierarchy.</p>
                    </div>
                    <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-50 hover:text-slate-600" @click="positionOpen = false" aria-label="Close">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('organogram.positions.store') }}" class="px-4 py-4">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label for="title" class="mb-1 block text-[11px] font-medium text-slate-600">Position title</label>
                            <x-text-input id="title" name="title" class="block w-full rounded-lg text-[13px]" type="text" :value="old('title')" required placeholder="e.g. Senior Officer Audit" autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-1" />
                        </div>
                        <div>
                            <label for="serial" class="mb-1 block text-[11px] font-medium text-slate-600">Serial order</label>
                            <x-text-input id="serial" name="serial" class="block w-full rounded-lg text-[13px]" type="number" min="1" max="255" :value="old('serial', $nextSerial)" placeholder="{{ $nextSerial }}" />
                            <p class="mt-1 text-[10px] leading-relaxed text-slate-400">Lower numbers appear higher in the chart. Leave blank to append at the end.</p>
                            <x-input-error :messages="$errors->get('serial')" class="mt-1" />
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3.5">
                        <button type="button" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-slate-50" @click="positionOpen = false">Cancel</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white shadow-sm hover:bg-navy-800">
                            Save position
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Add employee modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4" @click.self="addOpen = false">
            <div class="w-full max-w-md rounded-xl bg-white p-4 shadow-xl sm:p-5">
                <h2 class="text-[14px] font-semibold text-navy-900">Add employee</h2>
                <p class="mt-0.5 text-[11px] text-slate-500">Assign an officer to a rank in the organogram.</p>

                <form method="POST" action="{{ route('organogram.employees.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="name" class="block text-[11px] font-medium text-slate-600">Name</label>
                        <x-text-input id="name" name="name" class="mt-1 block w-full text-[13px]" type="text" :value="old('name')" required />
                    </div>
                    <div>
                        <label for="email" class="block text-[11px] font-medium text-slate-600">Email (optional)</label>
                        <x-text-input id="email" name="email" class="mt-1 block w-full text-[13px]" type="email" :value="old('email')" />
                    </div>
                    <div>
                        <label for="position_id" class="block text-[11px] font-medium text-slate-600">Position</label>
                        <select id="position_id" name="position_id" required class="mt-1 block w-full rounded-lg border-slate-200 text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500" @disabled($positions->isEmpty())>
                            @if ($positions->isEmpty())
                                <option value="" disabled selected>Add a position first</option>
                            @else
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>
                                        {{ $position->serial }}. {{ $position->title }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="flex justify-end gap-1.5 pt-1">
                        <button type="button" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-slate-50" @click="addOpen = false">Cancel</button>
                        @if ($positions->isEmpty())
                            <button type="submit" disabled class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white opacity-50">Save officer</button>
                        @else
                            <button type="submit" class="rounded-lg bg-navy-900 px-3 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Save officer</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
