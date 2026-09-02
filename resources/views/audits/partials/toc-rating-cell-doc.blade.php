@php
    $isSection = ($isSection ?? false);
    $rating = $rating ?? '';
@endphp
@if (! $isSection && $rating !== '')
    @include('audits.partials.rating-box-doc', ['rating' => $rating])
@endif
