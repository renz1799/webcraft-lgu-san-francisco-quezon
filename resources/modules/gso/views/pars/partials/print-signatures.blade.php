@php
  $receivedName = $par->person_accountable ?? '';
  $receivedPos  = $par->received_by_position ?? '';
  $receivedDate = $par->received_by_date ? optional($par->received_by_date)->format('m/d/Y') : '';

  $issuedName = $par->issued_by_name ?? '';
  $issuedPos  = $par->issued_by_position ?? '';
  $issuedOff  = $par->issued_by_office ?? '';
  $issuedDate = $par->issued_by_date ? optional($par->issued_by_date)->format('m/d/Y') : '';
@endphp

<table class="sign-table stack-next">
  <colgroup>
    <col style="width: 50%;">
    <col style="width: 50%;">
  </colgroup>
  <tbody>
    <tr>
      {{-- RECEIVED BY --}}
      <td style="height: 60mm; vertical-align: top;">
        <div class="small" style="padding: 2mm 2mm 0 2mm;">
          <span class="bold">Received by:</span>
        </div>

        <div style="padding: 0 6mm;">
          <div class="sig-line"></div>
          <div class="sig-label">Signature over Printed Name of End User</div>

          <div class="sig-name">{{ $receivedName ?: '&nbsp;' }}</div>
          <div class="sig-label">Printed Name</div>

          <div class="sig-name">{{ $receivedPos ?: '&nbsp;' }}</div>
          <div class="sig-label">Position/Office</div>

          <div class="sig-name">{{ $receivedDate ?: '&nbsp;' }}</div>
          <div class="sig-label">Date</div>
        </div>
      </td>

      {{-- ISSUED BY --}}
      <td style="height: 60mm; vertical-align: top;">
        <div class="small" style="padding: 2mm 2mm 0 2mm;">
          <span class="bold">Issued by:</span>
        </div>

        <div style="padding: 0 6mm;">
          <div class="sig-line"></div>
          <div class="sig-label">Signature over Printed Name of Supply and/or Property Custodian</div>

          <div class="sig-name">{{ $issuedName ?: '&nbsp;' }}</div>
          <div class="sig-label">Printed Name</div>

          <div class="sig-name">
            @php
              $issuedPosOffice = trim(($issuedPos ?: '') . ($issuedOff ? (' / ' . $issuedOff) : ''));
            @endphp
            {{ $issuedPosOffice ?: '&nbsp;' }}
          </div>
          <div class="sig-label">Position/Office</div>

          <div class="sig-name">{{ $issuedDate ?: '&nbsp;' }}</div>
          <div class="sig-label">Date</div>
        </div>
      </td>
    </tr>
  </tbody>
</table>