<x-app-layout>
    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4">
            <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">Add Area</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">Create an area under a Bangladesh division</p>
        </div>

        <div class="max-w-lg overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <div class="border-b border-slate-100 px-4 py-3.5">
                <p class="text-[13px] font-medium text-navy-900">Area details</p>
                <p class="mt-0.5 text-[11px] text-slate-500">Name, division, and status</p>
            </div>

            <form method="POST" action="{{ route('areas.store') }}" class="px-4 py-4">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label for="name" class="mb-1 block text-[11px] font-medium text-slate-600">Area name</label>
                        <x-text-input id="name" name="name" type="text" class="block w-full rounded-lg text-[13px]" :value="old('name')" required placeholder="e.g. Dhaka North" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label for="division" class="mb-1 block text-[11px] font-medium text-slate-600">Division</label>
                        <select id="division" name="division" required class="block w-full rounded-lg border-slate-200 text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="" disabled @selected(! old('division'))>Select division</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division }}" @selected(old('division') === $division)>{{ $division }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('division')" class="mt-1" />
                    </div>

                    <div>
                        <label for="status" class="mb-1 block text-[11px] font-medium text-slate-600">Status</label>
                        <select id="status" name="status" required class="block w-full rounded-lg border-slate-200 text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3.5">
                    <a href="{{ route('areas.index') }}" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                        Save Area
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
