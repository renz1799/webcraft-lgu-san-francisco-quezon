@php
  $approvedDate = $ptr->approved_by_date ? optional($ptr->approved_by_date)->format('m/d/Y') : '';
  $releasedDate = $ptr->released_by_date ? optional($ptr->released_by_date)->format('m/d/Y') : '';
  $receivedDate = $ptr->received_by_date ? optional($ptr->received_by_date)->format('m/d/Y') : '';
@endphp

<table class="sign-table stack-next">
  <colgroup>
    <col style="width: 14%;">
    <col style="width: 28.666%;">
    <col style="width: 28.666%;">
    <col style="width: 28.666%;">
  </colgroup>
  <tbody>
    <tr>
      <td class="sig-cell">&nbsp;</td>
      <td class="sig-cell center bold">Approved by:</td>
      <td class="sig-cell center bold">Released/Issued by:</td>
      <td class="sig-cell center bold">Received by:</td>
    </tr>
    <tr>
      <td class="sig-cell sig-rowlabel">Signature :</td>
      <td class="sig-cell"><div class="sig-value">&nbsp;</div></td>
      <td class="sig-cell"><div class="sig-value">&nbsp;</div></td>
      <td class="sig-cell"><div class="sig-value">&nbsp;</div></td>
    </tr>
    <tr>
      <td class="sig-cell sig-rowlabel">Printed Name :</td>
      <td class="sig-cell"><div class="sig-value">{{ $ptr->approved_by_name ?: '&nbsp;' }}</div></td>
      <td class="sig-cell"><div class="sig-value">{{ $ptr->released_by_name ?: '&nbsp;' }}</div></td>
      <td class="sig-cell"><div class="sig-value">{{ $ptr->received_by_name ?: '&nbsp;' }}</div></td>
    </tr>
    <tr>
      <td class="sig-cell sig-rowlabel">Designation :</td>
      <td class="sig-cell"><div class="sig-value">{{ $ptr->approved_by_designation ?: '&nbsp;' }}</div></td>
      <td class="sig-cell"><div class="sig-value">{{ $ptr->released_by_designation ?: '&nbsp;' }}</div></td>
      <td class="sig-cell"><div class="sig-value">{{ $ptr->received_by_designation ?: '&nbsp;' }}</div></td>
    </tr>
    <tr>
      <td class="sig-cell sig-rowlabel">Date :</td>
      <td class="sig-cell"><div class="sig-value">{{ $approvedDate ?: '&nbsp;' }}</div></td>
      <td class="sig-cell"><div class="sig-value">{{ $releasedDate ?: '&nbsp;' }}</div></td>
      <td class="sig-cell"><div class="sig-value">{{ $receivedDate ?: '&nbsp;' }}</div></td>
    </tr>
  </tbody>
</table>
