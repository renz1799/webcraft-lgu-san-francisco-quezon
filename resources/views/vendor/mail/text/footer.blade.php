@php
    $footerCopy = trim((string) $slot);
@endphp

{{ $footerCopy !== '' ? $footerCopy : '(c) '.date('Y').' Webcraft Web Development Services. All rights reserved.' }}
