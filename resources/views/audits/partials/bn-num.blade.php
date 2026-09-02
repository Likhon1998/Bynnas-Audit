@php
    use App\Support\BanglaNumerals;
    $variant = $variant ?? 'default';
    $value = $value ?? '';
@endphp
{!! BanglaNumerals::markup($value, $variant) !!}
