<x-app-layout>
    @php
        $isEdit = $employee !== null;
        $formEmployee = $employee;
        $currentPhotoUrl = $formEmployee?->photoUrl();
    @endphp

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="mb-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="{{ route('shakha-employees.index') }}" class="hover:text-brand-600">Shakha Employees</a>
                    <span>/</span>
                    <span class="text-slate-600">{{ $shakha->name }}</span>
                </div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">
                    {{ $isEdit ? 'Edit employee' : 'Shakha staff' }}
                </h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    {{ $shakha->area?->name ?: '—' }} · {{ $shakha->code ?: 'No code' }} · {{ $employees->count() }} on roster
                </p>
            </div>
            <a href="{{ route('shakha-employees.index', ['area_id' => $shakha->area_id, 'shakha_id' => $shakha->id]) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">
                Back to list
            </a>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-3.5 py-2.5">
                    <p class="text-[12px] font-semibold text-navy-900">Roster</p>
                    <p class="text-[10px] text-slate-500">Photo · employee ID · name · designation</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-slate-100 bg-slate-50/80">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-3.5 py-2.5">Photo</th>
                                <th class="px-3.5 py-2.5">ID</th>
                                <th class="px-3.5 py-2.5">Name</th>
                                <th class="px-3.5 py-2.5">Designation</th>
                                <th class="px-3.5 py-2.5">Joined shakha</th>
                                <th class="px-3.5 py-2.5">Status</th>
                                <th class="px-3.5 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($employees as $row)
                                <tr class="text-[12px] {{ $isEdit && $formEmployee->id === $row->id ? 'bg-sky-50/60' : '' }}">
                                    <td class="px-3.5 py-2.5">
                                        @include('shakha-employees.partials.photo', ['employee' => $row, 'size' => 'sm'])
                                    </td>
                                    <td class="px-3.5 py-2.5 font-semibold text-navy-900">{{ $row->employee_code }}</td>
                                    <td class="px-3.5 py-2.5">
                                        <p class="font-medium text-slate-800">{{ $row->name }}</p>
                                        @if ($row->phone || $row->email)
                                            <p class="text-[10px] text-slate-400">
                                                {{ collect([$row->phone, $row->email])->filter()->implode(' · ') }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2.5 text-slate-600">{{ $row->designation }}</td>
                                    <td class="px-3.5 py-2.5 text-slate-500">
                                        {{ $row->joined_shakha_at?->format('d M Y') ?: '—' }}
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        @if ($row->isActive())
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Active</span>
                                        @elseif ($row->isFired())
                                            <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-700">Fired</span>
                                        @elseif ($row->isTransferred())
                                            <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-800">Transferred</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">Inactive</span>
                                        @endif
                                    </td>
                                    @if ($canManage)
                                        <td class="px-3.5 py-2.5 text-right whitespace-nowrap">
                                            @php $count = (int) (($reportCounts[$row->id] ?? 0)); @endphp
                                            <a href="{{ route('shakha-employees.dossier', $row) }}" class="mr-2 text-[11px] font-semibold text-rose-700 hover:underline">
                                                রিপোর্ট{{ $count > 0 ? ' ('.$count.')' : '' }}
                                            </a>
                                            <a href="{{ route('shakha-employees.edit', $row) }}" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Edit</a>
                                            <span class="ml-2 inline-block align-middle">
                                                @include('shakha-employees.partials.transfer-fire-actions', [
                                                    'employee' => $row,
                                                    'transferTargets' => $transferTargets ?? collect(),
                                                    'returnTo' => request()->fullUrl(),
                                                ])
                                            </span>
                                            <form method="POST" action="{{ route('shakha-employees.destroy', $row) }}" class="ml-2 inline" onsubmit="return confirm('Remove this employee from the roster?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[11px] font-semibold text-rose-600 hover:underline">Remove</button>
                                            </form>
                                        </td>
                                    @else
                                        <td class="px-3.5 py-2.5 text-right whitespace-nowrap">
                                            @php $count = (int) (($reportCounts[$row->id] ?? 0)); @endphp
                                            <a href="{{ route('shakha-employees.dossier', $row) }}" class="text-[11px] font-semibold text-rose-700 hover:underline">
                                                রিপোর্ট{{ $count > 0 ? ' ('.$count.')' : '' }}
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3.5 py-10 text-center text-[12px] text-slate-400">
                                        No employees on this shakha yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($canManage)
                <div class="space-y-4">
                <div
                    class="rounded-xl border border-slate-100 bg-white shadow-card"
                    x-data="{
                        preview: @js($currentPhotoUrl),
                        onFile(event) {
                            const file = event.target.files?.[0];
                            if (!file) return;
                            this.preview = URL.createObjectURL(file);
                        }
                    }"
                >
                    <div class="border-b border-slate-100 px-3.5 py-2.5">
                        <p class="text-[12px] font-semibold text-navy-900">{{ $isEdit ? 'Edit employee' : 'Add employee' }}</p>
                        <p class="text-[10px] text-slate-500">Required: photo preferred · ID, name, designation</p>
                    </div>
                    <form
                        method="POST"
                        action="{{ $isEdit ? route('shakha-employees.update', $formEmployee) : route('shakha-employees.store', $shakha) }}"
                        enctype="multipart/form-data"
                        class="space-y-3 px-3.5 py-3.5"
                    >
                        @csrf
                        @if ($isEdit)
                            @method('PUT')
                        @endif

                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/70 p-3">
                            <label class="mb-2 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Employee photo</label>
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <template x-if="preview">
                                        <img :src="preview" alt="Preview" class="h-16 w-16 rounded-full object-cover ring-1 ring-slate-200">
                                    </template>
                                    <template x-if="!preview">
                                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-white text-[14px] font-semibold text-slate-400 ring-1 ring-slate-200">
                                            {{ $isEdit ? mb_strtoupper(mb_substr((string) $formEmployee->name, 0, 1)) : '?' }}
                                        </span>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <input
                                        type="file"
                                        name="photo"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="block w-full text-[11px] text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-navy-900 file:px-2.5 file:py-1.5 file:text-[11px] file:font-semibold file:text-white hover:file:bg-navy-800"
                                        @change="onFile($event)"
                                    >
                                    <p class="mt-1 text-[10px] text-slate-400">JPG, PNG or WebP · max 2 MB</p>
                                    @if ($isEdit && $currentPhotoUrl)
                                        <label class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] text-rose-600">
                                            <input type="checkbox" name="remove_photo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                            Remove current photo
                                        </label>
                                    @endif
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                        </div>

                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Employee ID</label>
                            <input type="text" name="employee_code" value="{{ old('employee_code', $formEmployee?->employee_code) }}" required class="h-8 w-full rounded-lg border-slate-200 text-[12px]" placeholder="e.g. EMP-001">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Employee name</label>
                            <input type="text" name="name" value="{{ old('name', $formEmployee?->name) }}" required class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Designation</label>
                            <input type="text" name="designation" value="{{ old('designation', $formEmployee?->designation) }}" required class="h-8 w-full rounded-lg border-slate-200 text-[12px]" placeholder="e.g. Branch Manager">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $formEmployee?->phone) }}" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Status</label>
                                <select name="status" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                                    <option value="active" @selected(old('status', $formEmployee?->status ?? 'active') === 'active')>Active (কর্মরত)</option>
                                    <option value="transferred" @selected(old('status', $formEmployee?->status ?? 'active') === 'transferred')>Transferred (স্থানান্তরিত)</option>
                                    <option value="fired" @selected(old('status', $formEmployee?->status ?? 'active') === 'fired')>Fired (চাকরিচ্যুত)</option>
                                    <option value="inactive" @selected(old('status', $formEmployee?->status ?? 'active') === 'inactive')>Inactive (নিষ্ক্রিয়)</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Email</label>
                            <input type="email" name="email" value="{{ old('email', $formEmployee?->email) }}" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Joined org</label>
                                <input type="date" name="joined_organization_at" value="{{ old('joined_organization_at', $formEmployee?->joined_organization_at?->format('Y-m-d')) }}" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Joined shakha</label>
                                <input type="date" name="joined_shakha_at" value="{{ old('joined_shakha_at', $formEmployee?->joined_shakha_at?->format('Y-m-d')) }}" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Sort order</label>
                            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $formEmployee?->sort_order ?? 0) }}" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Notes</label>
                            <textarea name="notes" rows="2" class="w-full rounded-lg border-slate-200 text-[12px]">{{ old('notes', $formEmployee?->notes) }}</textarea>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-medium text-white hover:bg-navy-800">
                                {{ $isEdit ? 'Save changes' : 'Add employee' }}
                            </button>
                            @if ($isEdit)
                                <a href="{{ route('shakha-employees.manage', $shakha) }}" class="text-[11px] font-semibold text-slate-500 hover:underline">Cancel</a>
                            @endif
                        </div>
                    </form>
                </div>
                </div>
            @else
                <div class="rounded-xl border border-slate-100 bg-slate-50 px-3.5 py-4 text-[12px] text-slate-500">
                    You can view this roster. Ask someone with shakha manage access to add or edit employees.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
