@php
  $signatories = $report['signatories'] ?? [];
  $totalItems = (int) ($report['summary']['total_items'] ?? 0);
  $totalBookValue = (float) ($report['summary']['total_book_value'] ?? 0);
  $committeeNames = array_values(array_filter([
    $signatories['committee_chair_name'] ?? null,
    $signatories['committee_member_1_name'] ?? null,
    $signatories['committee_member_2_name'] ?? null,
  ]));
@endphp

<table class="rpcppe-summary-table stack-next">
  <tr>
    <th>Items Listed</th>
    <th>Qty. per Card</th>
    <th>Qty. per Count</th>
    <th>Shortage / Overage Qty.</th>
    <th>Book Value</th>
  </tr>
  <tr>
    <td>{{ number_format($totalItems) }}</td>
    <td>{{ number_format($totalItems) }}</td>
    <td>{{ number_format($totalItems) }}</td>
    <td>0</td>
    <td>{{ number_format($totalBookValue, 2) }}</td>
  </tr>
</table>

<table class="rpcppe-signature-table stack-next">
  <tr>
    <th class="rpcppe-signature-title">Accountable Officer</th>
    <th class="rpcppe-signature-title">Inventory Committee</th>
    <th class="rpcppe-signature-title">Approved By</th>
    <th class="rpcppe-signature-title">Verified By</th>
  </tr>
  <tr class="rpcppe-signature-block">
    <td>
      <div class="rpcppe-signature-name">{{ $signatories['accountable_officer_name'] ?? '' }}</div>
      <div class="rpcppe-signature-role">{{ $signatories['accountable_officer_designation'] ?? '' }}</div>
    </td>
    <td>
      <div class="rpcppe-committee-lines">
        @foreach($committeeNames as $committeeName)
          <div class="rpcppe-signature-name">{{ $committeeName }}</div>
        @endforeach
        @if(empty($committeeNames))
          <div class="rpcppe-signature-name">-</div>
        @endif
      </div>
    </td>
    <td>
      <div class="rpcppe-signature-name">{{ $signatories['approved_by_name'] ?? '' }}</div>
      <div class="rpcppe-signature-role">{{ $signatories['approved_by_designation'] ?? '' }}</div>
    </td>
    <td>
      <div class="rpcppe-signature-name">{{ $signatories['verified_by_name'] ?? '' }}</div>
      <div class="rpcppe-signature-role">{{ $signatories['verified_by_designation'] ?? '' }}</div>
    </td>
  </tr>
</table>
