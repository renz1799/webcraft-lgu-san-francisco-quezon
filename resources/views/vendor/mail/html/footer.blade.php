@php
    $footerCopy = trim((string) $slot);
    $footerYear = date('Y');
@endphp
<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
@if ($footerCopy !== '')
{{ Illuminate\Mail\Markdown::parse($footerCopy) }}
@else
<p>&copy; {{ $footerYear }} Webcraft Web Development Services. All rights reserved.</p>
@endif
</td>
</tr>
</table>
</td>
</tr>
