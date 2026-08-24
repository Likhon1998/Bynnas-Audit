<x-app-layout>
    @php
        $openEmployeeModal = $errors->hasAny(['name', 'email', 'position_id']);
        $openPositionModal = $errors->hasAny(['title', 'serial', 'color']);
    @endphp

    <div
        class="flex h-full flex-col px-4 py-6 lg:px-8"
        x-data="{ zoom: 1, addOpen: {{ $openEmployeeModal ? 'true' : 'false' }}, positionOpen: {{ $openPositionModal ? 'true' : 'false' }}, showOpen: false }"
        @keydown.escape.window="showOpen = false; addOpen = false; positionOpen = false"
    >
        <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a3 3 0 00-3-3H7a3 3 0 00-3 3v2m16-11a3 3 0 11-6 0 3 3 0 016 0zM9 9a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Audit Organogram</h1>
                    <p class="text-sm text-slate-400">Director Audit through Audit Officer — multiple people per rank</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="showOpen = true" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Show
                </button>
                <button type="button" @click="positionOpen = true" class="inline-flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-4 py-2.5 text-sm font-semibold text-brand-700 hover:bg-brand-100">
                    <span class="text-lg leading-none">+</span>
                    Add Position
                </button>
                <button type="button" @click="addOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600">
                    <span class="text-lg leading-none">+</span>
                    Add Employee
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="relative min-h-[560px] flex-1 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <div class="dot-grid h-full overflow-auto px-6 py-10">
                <div class="flex min-w-max origin-top justify-center pb-16 pt-2" :style="`transform: scale(${zoom});`">
                    <div class="flex flex-col items-center">
                        @if ($positions->isEmpty())
                            <p class="rounded-xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-400">
                                No positions yet. Click <strong>Add Position</strong> to create the first rank.
                            </p>
                        @else
                            @foreach ($positions as $index => $position)
                                @if ($index > 0)
                                    <div class="h-8 w-px bg-slate-200"></div>
                                @endif

                                <div class="mb-3 rounded-lg px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-white" style="background-color: {{ $position->color }}">
                                    {{ $position->serial }}. {{ $position->title }}
                                </div>

                                @if ($position->employees->isEmpty())
                                    <p class="rounded-xl border border-dashed border-slate-200 bg-white px-4 py-3 text-sm text-slate-400">No officers assigned yet</p>
                                @else
                                    <div class="flex flex-wrap justify-center gap-3">
                                        @foreach ($position->employees as $employee)
                                            <div class="group relative">
                                                <x-org-node :name="$employee->name" :title="$position->title" :accent="$position->color" />
                                                <form method="POST" action="{{ route('organogram.employees.destroy', $employee) }}" class="absolute -right-1 -top-1 hidden group-hover:block" onsubmit="return confirm('Remove this officer from the organogram?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-700 text-[10px] text-white hover:bg-red-600" title="Remove">×</button>
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

            <div class="absolute bottom-5 right-5 flex flex-col overflow-hidden rounded-full border border-slate-200 bg-white shadow-card">
                <button type="button" class="px-3 py-2 text-lg text-slate-500 hover:bg-slate-50" @click="zoom = Math.min(zoom + 0.1, 1.6)">+</button>
                <button type="button" class="border-t border-slate-100 px-3 py-2 text-lg text-slate-500 hover:bg-slate-50" @click="zoom = Math.max(zoom - 0.1, 0.6)">−</button>
            </div>
        </div>

        {{-- Full organogram glance modal --}}
        <div
            x-show="showOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4 py-6"
            @click.self="showOpen = false"
        >
            <div class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" @click.stop>
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Full Audit Organogram</h2>
                        <p class="mt-0.5 text-sm text-slate-400">Complete hierarchy at a glance</p>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-700" @click="showOpen = false" aria-label="Close">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="dot-grid flex-1 overflow-auto px-6 py-8">
                    <div class="mx-auto flex max-w-4xl flex-col items-center">
                        @foreach ($positions as $index => $position)
                            @if ($index > 0)
                                <div class="h-5 w-px bg-slate-200"></div>
                            @endif

                            <div class="mb-2 rounded-md px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-white" style="background-color: {{ $position->color }}">
                                {{ $position->serial }}. {{ $position->title }}
                                <span class="ml-1 opacity-80">({{ $position->employees->count() }})</span>
                            </div>

                            @if ($position->employees->isEmpty())
                                <p class="mb-1 rounded-lg border border-dashed border-slate-200 bg-white/80 px-3 py-1.5 text-xs text-slate-400">No officers</p>
                            @else
                                <div class="mb-1 flex flex-wrap justify-center gap-2">
                                    @foreach ($position->employees as $employee)
                                        @php
                                            $initials = collect(preg_split('/\s+/', trim($employee->name)))
                                                ->filter()
                                                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                                ->take(2)
                                                ->implode('');
                                        @endphp
                                        <div class="flex items-center gap-2 rounded-lg border border-slate-100 bg-white px-2.5 py-1.5 shadow-sm">
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-semibold text-white" style="background-color: {{ $position->color }}">
                                                {{ $initials }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-semibold text-slate-800">{{ $employee->name }}</p>
                                                <p class="truncate text-[10px] text-slate-400">{{ $position->title }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 px-6 py-3">
                    <button type="button" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" @click="showOpen = false">
                        Close
                    </button>
                </div>
            </div>
        </div>

        {{-- Add position modal --}}
        <div x-show="positionOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4" @click.self="positionOpen = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-900">Add position</h2>
                <p class="mt-1 text-sm text-slate-400">Create a new rank in the audit organogram hierarchy.</p>

                <form method="POST" action="{{ route('organogram.positions.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="title" value="Position title" />
                        <x-text-input id="title" name="title" class="mt-1 block w-full" type="text" :value="old('title')" required placeholder="e.g. Senior Officer Audit" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="serial" value="Serial (order)" />
                        <x-text-input id="serial" name="serial" class="mt-1 block w-full" type="number" min="1" max="255" :value="old('serial', $nextSerial)" placeholder="{{ $nextSerial }}" />
                        <p class="mt-1 text-xs text-slate-400">Lower numbers appear higher in the chart. Leave as suggested to append at the end.</p>
                        <x-input-error :messages="$errors->get('serial')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="color" value="Color" />
                        <input id="color" name="color" type="color" value="{{ old('color', '#4C6FFF') }}" class="mt-1 h-10 w-full cursor-pointer rounded-xl border border-slate-200 bg-white p-1" />
                        <x-input-error :messages="$errors->get('color')" class="mt-2" />
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-50" @click="positionOpen = false">Cancel</button>
                        <x-primary-button>Save position</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Add employee modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4" @click.self="addOpen = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-900">Add employee</h2>
                <p class="mt-1 text-sm text-slate-400">Assign an officer to any rank in the audit organogram.</p>

                <form method="POST" action="{{ route('organogram.employees.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" type="text" :value="old('name')" required />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email (optional)" />
                        <x-text-input id="email" name="email" class="mt-1 block w-full" type="email" :value="old('email')" />
                    </div>
                    <div>
                        <x-input-label for="position_id" value="Position" />
                        <select id="position_id" name="position_id" required class="mt-1 block w-full rounded-xl border-slate-200 text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500" @disabled($positions->isEmpty())>
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
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-50" @click="addOpen = false">Cancel</button>
                        @if ($positions->isEmpty())
                            <x-primary-button disabled>Save officer</x-primary-button>
                        @else
                            <x-primary-button>Save officer</x-primary-button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
