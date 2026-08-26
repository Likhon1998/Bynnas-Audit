{{-- Official letterhead block: logo slot + org name + rating badge --}}
@php
    $editable = $editable ?? false;
@endphp
<div class="flex items-start justify-between gap-4">
    <div class="flex min-w-0 items-start gap-3">
        @if ($editable)
            <div class="shrink-0">
                <label class="group relative flex h-[58px] w-[58px] cursor-pointer items-center justify-center overflow-hidden rounded border border-dashed border-slate-400 bg-slate-50 hover:border-[#2b579a] hover:bg-sky-50">
                    @if (! empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-contain p-0.5">
                    @else
                        <span class="px-1 text-center text-[9px] font-medium leading-tight text-slate-500">Add<br>Logo</span>
                    @endif
                    <input type="file" accept="image/*" wire:model="logoUpload" class="absolute inset-0 cursor-pointer opacity-0">
                </label>
                <div wire:loading wire:target="logoUpload" class="mt-0.5 text-[9px] text-slate-500">Uploading…</div>
                @error('logoUpload') <p class="mt-0.5 max-w-[70px] text-[9px] text-rose-600">{{ $message }}</p> @enderror
                @if (! empty($logoUrl))
                    <button type="button" wire:click="removeLogo" class="mt-0.5 text-[9px] text-rose-600 hover:underline">Remove</button>
                @endif
            </div>
        @else
            <div class="flex h-[58px] w-[58px] shrink-0 items-center justify-center overflow-hidden border border-slate-300 bg-white">
                @if (! empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-contain p-0.5">
                @else
                    <span class="px-1 text-center text-[9px] leading-tight text-slate-400">Logo</span>
                @endif
            </div>
        @endif

        <div class="leading-tight pt-0.5">
            <p class="text-[20px] font-extrabold tracking-tight text-black">DSK</p>
            <p class="text-[12px] font-semibold text-black">দুঃস্থ স্বাস্থ্য কেন্দ্র</p>
            <p class="text-[9px] font-semibold uppercase tracking-[0.04em] text-black">Dushtha Shasthya Kendra</p>
        </div>
    </div>

    <div class="w-[158px] shrink-0 text-center">
        <div class="rounded-[2px] bg-[#1d4ed8] px-2 py-1.5 text-[10px] font-semibold leading-snug text-white">
            Branch Internal<br>Control Rating
        </div>
        <div class="mx-auto h-0 w-0 border-l-[9px] border-r-[9px] border-t-[9px] border-l-transparent border-r-transparent border-t-[#1d4ed8]"></div>
        @if ($editable)
            <select
                wire:model.live="control_rating"
                class="mt-0.5 w-full rounded-[2px] border-2 border-orange-400 px-1 py-1.5 text-center text-[11px] font-bold text-white"
                style="background: {{ $ratingColor }};"
            >
                <option>Satisfactory</option>
                <option>Minor</option>
                <option>Medium</option>
                <option>Major</option>
                <option>Unsatisfactory</option>
            </select>
        @else
            <div
                class="mt-0.5 rounded-[2px] border-2 border-orange-400 px-2 py-2 text-[11px] font-bold text-white"
                style="background: {{ $ratingColor }};"
            >
                {{ $control_rating ?: '—' }}
            </div>
        @endif
    </div>
</div>
