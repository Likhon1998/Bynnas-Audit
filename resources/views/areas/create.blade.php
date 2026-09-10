<x-app-layout>
    <div class="px-4 py-6 lg:px-6">
        <div class="mx-auto mb-5 flex max-w-lg flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                <a href="{{ route('areas.index') }}" class="hover:text-brand-600">All Areas</a>
                <span>/</span>
                <span class="text-slate-600">Add Area</span>
            </div>
            <a href="{{ route('areas.index') }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">
                Back to list
            </a>
        </div>

        @if ($errors->any())
            <div class="mx-auto mb-4 max-w-lg rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mx-auto w-full max-w-lg">
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 bg-gradient-to-b from-slate-50/90 to-white px-6 py-5 text-center">
                    <span class="mx-auto mb-2.5 flex h-10 w-10 items-center justify-center rounded-xl bg-navy-900 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </span>
                    <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Add Area</h1>
                    <p class="mt-1 text-[12px] text-slate-500">
                        Division → Area · create an area under a Bangladesh division
                    </p>
                </div>

                <form method="POST" action="{{ route('areas.store') }}" class="px-6 py-6 sm:px-8">
                    @csrf

                    <div class="mx-auto w-full max-w-sm space-y-5">
                        <div>
                            <label for="division" class="mb-1.5 block text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                Division <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="division"
                                name="division"
                                required
                                class="block h-10 w-full rounded-lg border-slate-200 text-center text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            >
                                <option value="" disabled @selected(! old('division'))>Select division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division }}" @selected(old('division') === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('division')" class="mt-1 text-center" />
                        </div>

                        <div>
                            <label for="name" class="mb-1.5 block text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                Area name <span class="text-rose-500">*</span>
                            </label>
                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="block h-10 w-full rounded-lg text-center text-[13px]"
                                :value="old('name')"
                                required
                                placeholder="e.g. Gazipur"
                                autofocus
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-1 text-center" />
                        </div>

                        <div>
                            <label for="status" class="mb-1.5 block text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                Status <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2 rounded-xl border border-slate-200 bg-slate-50 p-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="active" class="peer sr-only" @checked(old('status', 'active') === 'active')>
                                    <span class="flex h-9 items-center justify-center rounded-lg text-[12px] font-semibold text-slate-500 transition peer-checked:bg-white peer-checked:text-emerald-700 peer-checked:shadow-sm">
                                        Active
                                    </span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="inactive" class="peer sr-only" @checked(old('status') === 'inactive')>
                                    <span class="flex h-9 items-center justify-center rounded-lg text-[12px] font-semibold text-slate-500 transition peer-checked:bg-white peer-checked:text-slate-700 peer-checked:shadow-sm">
                                        Inactive
                                    </span>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('status')" class="mt-1 text-center" />
                        </div>
                    </div>

                    <div class="mx-auto mt-7 flex w-full max-w-sm items-center justify-center gap-2 border-t border-slate-100 pt-5">
                        <a href="{{ route('areas.index') }}" class="inline-flex h-9 items-center rounded-lg px-4 text-[12px] font-medium text-slate-500 hover:bg-slate-50">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex h-9 items-center rounded-lg bg-navy-900 px-5 text-[12px] font-semibold text-white hover:bg-navy-800">
                            Save Area
                        </button>
                    </div>
                </form>
            </div>

            <p class="mt-4 text-center text-[11px] text-slate-400">
                After saving, you can add shakhas under this area.
            </p>
        </div>
    </div>
</x-app-layout>
