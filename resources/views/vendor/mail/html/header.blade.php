@props(['url'])

@php
    $brandName = trim((string) $slot);
    $brandName = $brandName !== '' ? $brandName : trim((string) (config('mail.from.name') ?: config('app.name') ?: 'Webcraft LGU Platform'));
@endphp

<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{{ $brandName }}
</a>
</td>
</tr>
