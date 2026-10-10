{{-- Named anchor (TOC links + page lookup) and a PDF outline bookmark; place it inside the heading element so it lands on the heading's page. --}}
@if (($id ?? '') !== '')<a name="{{ $id }}"></a>@endif
@if (! ($forDoc ?? false) && trim((string) ($label ?? '')) !== '')<bookmark content="{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', (string) $label)), 90) }}" level="{{ (int) ($level ?? 0) }}" />@endif
