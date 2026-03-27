@php
  $fmt = fn($d) => !empty($d) ? \Illuminate\Support\Carbon::parse($d)->format('m/d/Y') : '';
  $receivedFromPositionOffice = trim(implode(' / ', array_filter([
      (string) ($ics->received_from_position ?? ''),
      (string) ($ics->received_from_office ?? ''),
  ])));
  $receivedByPositionOffice = trim(implode(' / ', array_filter([
      (string) ($ics->received_by_position ?? ''),
      (string) ($ics->received_by_office ?? ''),
  ])));
  $lineStyle = 'border-bottom:1px solid #000; min-height:20px; line-height:20px; text-align:center; margin:10px auto 2px; width:88%; padding:0 6px; font-weight:700;';
  $captionStyle = 'text-align:center; font-size:10px; margin-bottom:8px;';
@endphp

<table class="form-table stack-next" style="margin-top: 0;">
  <colgroup>
    <col style="width: 50%;">
    <col style="width: 50%;">
  </colgroup>
  <tr>
    <td style="padding: 0; vertical-align: top;">
      <div style="padding: 8px 10px 10px; min-height: 52mm;">
        <div style="margin-bottom: 12px;">Received from:</div>
        <div style="{{ $lineStyle }}">{{ $ics->received_from_name ?? '' }}</div>
        <div style="{{ $captionStyle }}">Signature Over Printed Name</div>

        <div style="{{ $lineStyle }}">{{ $receivedFromPositionOffice }}</div>
        <div style="{{ $captionStyle }}">Position/Office</div>

        <div style="{{ $lineStyle }}">{{ $fmt($ics->received_from_date) }}</div>
        <div style="{{ $captionStyle }}">Date</div>
      </div>
    </td>
    <td style="padding: 0; vertical-align: top;">
      <div style="padding: 8px 10px 10px; min-height: 52mm;">
        <div style="margin-bottom: 12px;">Received by:</div>
        <div style="{{ $lineStyle }}">{{ $ics->received_by_name ?? '' }}</div>
        <div style="{{ $captionStyle }}">Signature Over Printed Name</div>

        <div style="{{ $lineStyle }}">{{ $receivedByPositionOffice }}</div>
        <div style="{{ $captionStyle }}">Position/Office</div>

        <div style="{{ $lineStyle }}">{{ $fmt($ics->received_by_date) }}</div>
        <div style="{{ $captionStyle }}">Date</div>
      </div>
    </td>
  </tr>
</table>