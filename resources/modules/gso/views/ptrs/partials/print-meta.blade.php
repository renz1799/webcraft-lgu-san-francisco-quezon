@php
  $entityName = $print['entity_name'] ?? '';
  $fundClusterCode = $print['fund_cluster_code'] ?? '';
  $ptrNo = $print['ptr_no'] ?? '';
  $transferDate = $print['transfer_date'] ?? '';
  $fromSummary = $print['from_summary'] ?? '';
  $toSummary = $print['to_summary'] ?? '';
  $transferType = strtolower((string) ($print['transfer_type'] ?? ''));
  $transferTypeOther = $print['transfer_type_other'] ?? '';
@endphp

<table class="form-table meta-table">
  <colgroup>
    <col style="width: 28%;">
    <col style="width: 44%;">
    <col style="width: 12%;">
    <col style="width: 16%;">
  </colgroup>
  <tbody>
    <tr>
      <td class="bold">Entity Name :</td>
      <td>{{ $entityName }}</td>
      <td class="bold">Fund Cluster :</td>
      <td>{{ $fundClusterCode }}</td>
    </tr>
    <tr>
      <td class="bold">From Accountable Officer/Agency/Fund Cluster :</td>
      <td>{{ $fromSummary }}</td>
      <td class="bold">PTR No. :</td>
      <td>{{ $ptrNo }}</td>
    </tr>
    <tr>
      <td class="bold">To Accountable Officer/Agency/Fund Cluster :</td>
      <td>{{ $toSummary }}</td>
      <td class="bold">Date :</td>
      <td>{{ $transferDate }}</td>
    </tr>
    <tr>
      <td class="bold" style="vertical-align: top;">Transfer Type:<br><span class="small">(check only one)</span></td>
      <td colspan="3">
        <div>
          <span class="type-option">[{{ $transferType === 'donation' ? 'x' : ' ' }}] Donation</span>
          <span class="type-option">[{{ $transferType === 'relocate' ? 'x' : ' ' }}] Relocate</span>
        </div>
        <div>
          <span class="type-option">[{{ $transferType === 'reassignment' ? 'x' : ' ' }}] Reassignment</span>
          <span class="type-option">[{{ $transferType === 'others' ? 'x' : ' ' }}] Others (Specify){{ $transferType === 'others' && $transferTypeOther !== '' ? ': ' . $transferTypeOther : '' }}</span>
        </div>
      </td>
    </tr>
  </tbody>
</table>
