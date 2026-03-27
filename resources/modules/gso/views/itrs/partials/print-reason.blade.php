@php
  $reason = trim((string) ($print['reason_for_transfer'] ?? ''));
  $reasonLines = preg_split('/\r\n|\r|\n/', $reason) ?: [];
  $reasonLines = array_values(array_filter(array_map(fn ($line) => trim((string) $line), $reasonLines), fn ($line) => $line !== ''));
@endphp

<table class="form-table stack-next">
  <tbody>
    <tr>
      <td>
        <div class="bold" style="margin-bottom: 2mm;">Reason for Transfer:</div>
        <div class="reason-lines">
          @for($i = 0; $i < 4; $i++)
            <div class="line">{{ $reasonLines[$i] ?? '' }}</div>
          @endfor
        </div>
      </td>
    </tr>
  </tbody>
</table>


