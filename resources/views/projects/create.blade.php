<x-app-layout>
    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4">
            <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">Add Project</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">Creates master data used by Annual Audit project tabs</p>
        </div>

        <div class="max-w-2xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <div class="border-b border-slate-100 px-4 py-3.5">
                <p class="text-[13px] font-medium text-navy-900">Project details</p>
                <p class="mt-0.5 text-[11px] text-slate-500">Flags control which report tabs this project appears in</p>
            </div>

            <form method="POST" action="{{ route('projects.store') }}" class="px-4 py-4" x-data="{ locations: [{ name: '', division: '', status: 'active' }] }">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label for="name" class="mb-1 block text-[11px] font-medium text-slate-600">Project name</label>
                        <x-text-input id="name" name="name" type="text" class="block w-full rounded-lg text-[13px]" :value="old('name')" required placeholder="e.g. Livelihood Resilience" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label for="donor" class="mb-1 block text-[11px] font-medium text-slate-600">Donor (optional)</label>
                        <x-text-input id="donor" name="donor" type="text" class="block w-full rounded-lg text-[13px]" :value="old('donor')" placeholder="e.g. PKSF / Donor A" />
                        <x-input-error :messages="$errors->get('donor')" class="mt-1" />
                    </div>

                    <div>
                        <label for="status" class="mb-1 block text-[11px] font-medium text-slate-600">Status</label>
                        <select id="status" name="status" required class="block w-full rounded-lg border-slate-200 text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-[12px] text-slate-700">
                            <input type="checkbox" name="is_pksf" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" @checked(old('is_pksf'))>
                            PKSF project
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-[12px] text-slate-700">
                            <input type="checkbox" name="is_maternity" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" @checked(old('is_maternity'))>
                            Maternity project
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-[12px] text-slate-700">
                            <input type="checkbox" name="has_project_audit" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" @checked(old('has_project_audit', true))>
                            Include in Project Audit
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-[12px] text-slate-700">
                            <input type="checkbox" name="has_project_monitoring" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" @checked(old('has_project_monitoring', true))>
                            Include in Project Monitoring
                        </label>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <div class="mb-2 flex items-center justify-between">
                            <div>
                                <p class="text-[12px] font-medium text-navy-900">Locations</p>
                                <p class="text-[11px] text-slate-500">Schedules are generated per location</p>
                            </div>
                            <button type="button" @click="locations.push({ name: '', division: '', status: 'active' })" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-medium text-slate-600 hover:bg-slate-50">
                                + Location
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(loc, index) in locations" :key="index">
                                <div class="grid gap-2 rounded-lg border border-slate-100 p-2.5 sm:grid-cols-12">
                                    <div class="sm:col-span-5">
                                        <input type="text" :name="'locations['+index+'][name]'" x-model="loc.name" placeholder="Location name" class="block w-full rounded-lg border-slate-200 text-[12px]" required>
                                    </div>
                                    <div class="sm:col-span-4">
                                        <select :name="'locations['+index+'][division]'" x-model="loc.division" class="block w-full rounded-lg border-slate-200 text-[12px]">
                                            <option value="">Division (optional)</option>
                                            @foreach ($divisions as $division)
                                                <option value="{{ $division }}">{{ $division }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2 sm:col-span-3">
                                        <select :name="'locations['+index+'][status]'" x-model="loc.status" class="block w-full rounded-lg border-slate-200 text-[12px]">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <button type="button" @click="if (locations.length > 1) locations.splice(index, 1)" class="shrink-0 text-[11px] text-rose-500 hover:underline">Remove</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <x-input-error :messages="$errors->get('locations')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3.5">
                    <a href="{{ route('projects.index') }}" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                        Save Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
