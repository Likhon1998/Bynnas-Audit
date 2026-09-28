<svg class="{{ $class ?? 'h-3.5 w-3.5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($type)
        @case('started')
            <path d="M14 3.5H7.5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V8Z"/><path d="M14 3.5V8h4.5M12 11.5v5M9.5 14h5"/>
            @break
        @case('completed')
            <path d="M14 3.5H7.5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V8Z"/><path d="M14 3.5V8h4.5M9 14.2l2 2 4-4"/>
            @break
        @case('pdf_stored')
            <path d="M4 7.5h16v3H4zM5.5 10.5v8a1.5 1.5 0 0 0 1.5 1.5h10a1.5 1.5 0 0 0 1.5-1.5v-8M10 14h4"/>
            @break
        @case('submitted')
        @case('resubmitted')
            <path d="m21 3-9.5 9.5M21 3l-6.5 18-3-8.5L3 9.5Z"/>
            @break
        @case('returned')
            <path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>
            @break
        @case('approved')
            <path d="M12 3.5 5 6.2v5.3c0 4.2 2.9 7.6 7 9 4.1-1.4 7-4.8 7-9V6.2Z"/><path d="m8.8 12 2.2 2.2 4.2-4.4"/>
            @break
        @case('maker_done')
            <circle cx="12" cy="12" r="8.5"/><path d="m8.5 12.2 2.4 2.4 4.6-4.8"/>
            @break
        @case('visit_started')
            <path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z"/><circle cx="12" cy="10" r="2.3"/>
            @break
        @case('visit_done')
            <path d="M5 21V4M5 4.5h11l-2 3.5 2 3.5H5"/>
            @break
        @case('visit_delayed')
            <circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>
            @break
        @case('email')
            <rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4 7 8 6 8-6"/>
            @break
        @case('checklist')
            <rect x="5.5" y="4.5" width="13" height="16" rx="2"/><path d="M9 4.5V3.5h6v1M9 11l1.5 1.5L13.5 9.5M9 16.5h6"/>
            @break
        @default
            <path d="M20 11.5a7.5 7.5 0 0 1-11 6.6L4 19.5l1.4-4.6A7.5 7.5 0 1 1 20 11.5Z"/>
    @endswitch
</svg>
