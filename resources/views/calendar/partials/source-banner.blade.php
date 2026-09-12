{{-- Shared notice: Working Calendar drives off days / free days --}}
@php
    $calendarLink = $calendarManageUrl ?? (Route::has('calendar.index') ? route('calendar.index') : null);
    $weekendText = ! empty($weekendLabels ?? null)
        ? implode(', ', $weekendLabels)
        : 'configured weekly offs';
@endphp
<div class="rounded-lg border border-sky-100 bg-sky-50/70 px-3 py-2 text-[11px] text-sky-950">
    <p class="font-semibold text-sky-900">Uses Working Calendar</p>
    <p class="mt-0.5 text-sky-800/90">
        Free / working days exclude {{ $weekendText }} and all active off days (national, government, internal).
        @if ($calendarLink)
            <a href="{{ $calendarLink }}" class="font-semibold text-sky-700 underline hover:text-sky-900">Manage calendar</a>
        @endif
    </p>
</div>
