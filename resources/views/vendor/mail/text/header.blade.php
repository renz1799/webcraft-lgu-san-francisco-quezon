@php
    $brandName = trim((string) $slot);
    $brandName = $brandName !== '' ? $brandName : trim((string) (config('mail.from.name') ?: config('app.name') ?: 'Webcraft LGU Platform'));
@endphp

{{ $brandName }}: {{ $url }}
