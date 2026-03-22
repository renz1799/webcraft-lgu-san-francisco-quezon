@php
  $maxRows = (int) ($maxGridRows ?? 18);
  $entries = $entries ?? [];

  if (!is_array($entries) && !($entries instanceof \Illuminate\Support\Collection)) {
      $entries = [];
  }

  $fmtMoney = function ($value) {
      if ($value === null || $value === '') {
          return '';
      }

      return number_format((float) $value, 2);
  };

  $fmtDate = function ($value) {
      if (empty($value)) {
          return '';
      }

      try {
          return \Illuminate\Support\Carbon::parse($value)->format('F d, Y');
      } catch (\Throwable) {
          return (string) $value;
      }
  };

  $rows = $entries instanceof \Illuminate\Support\Collection ? $entries->values()->all() : (array) $entries;
  $rows = array_slice($rows, 0, $maxRows);
@endphp

<div class="page">
  <div class="print-controls no-print">
    <button type="button" onclick="window.print()">Print</button>
  </div>

  <div style="position:relative;">
    <div class="annex">Annex A.1</div>
    <div class="title">SEMI-EXPENDABLE PROPERTY CARD</div>
  </div>

  <div class="meta-row">
    <div class="meta-left">
      <strong>Entity Name :</strong>
      <span class="underline"><strong>{{ $card['entity_name'] ?? $card['lgu'] ?? 'LGU SAN FRANCISCO' }}</strong></span>
    </div>

    <div class="meta-right">
      <strong>Fund Cluster :</strong>
      <span class="underline"><strong>{{ $card['fund_cluster'] ?? '-' }}</strong></span>
    </div>
  </div>

  <table class="spc-table">
    <colgroup>
      <col style="width:40mm;">
      <col style="width:28mm;">
      <col style="width:10mm;">
      <col style="width:22mm;">
      <col style="width:26mm;">
      <col style="width:12mm;">
      <col style="width:22mm;">
      <col style="width:10mm;">
      <col style="width:34mm;">
      <col style="width:52mm;">
      <col style="width:12mm;">
      <col style="width:28mm;">
      <col style="width:40mm;">
    </colgroup>

    <tr>
      <td class="h-label left">Semi-expendable Property :</td>
      <td class="h-value left" colspan="9">{{ $card['property_name'] ?? '-' }}</td>
      <td class="h-label left" colspan="2">Semi-expendable <br> Property Number:</td>
      <td class="h-value left">{{ $card['se_property_number'] ?? $card['reference'] ?? '-' }}</td>
    </tr>

    <tr>
      <td class="h-label left">Description :</td>
      <td class="h-value left wrap" colspan="9">{{ $card['description'] ?? '-' }}</td>
      <td class="h-label left" colspan="2"></td>
      <td class="h-value left"></td>
    </tr>

    <tr>
      <th rowspan="2">Date</th>
      <th rowspan="2">Reference</th>
      <th rowspan="2">Qty.</th>
      <th colspan="2">Receipt</th>
      <th rowspan="2">Receipt<br>Qty.</th>
      <th rowspan="2">Item No.</th>
      <th rowspan="2">Qty.</th>
      <th colspan="2">Issue/Transfer/Disposal</th>
      <th rowspan="2">Balance<br>Qty.</th>
      <th rowspan="2">Amount</th>
      <th rowspan="2">Remarks</th>
    </tr>

    <tr>
      <th>Unit Cost</th>
      <th>Total Cost</th>
      <th>Office</th>
      <th>Officer</th>
    </tr>

    @for($i = 0; $i < $maxRows; $i++)
      @php
        $row = $rows[$i] ?? null;
        $date = $row['event_date'] ?? null;
        $reference = $row['reference'] ?? null;
        $receiptQty = $row['qty_in'] ?? null;
        $issueQty = $row['qty_out'] ?? null;
        $qtyLeft = $row['qty_left'] ?? $receiptQty;
        $unitCost = $row['receipt_unit_cost'] ?? null;
        $totalCost = $row['receipt_total_cost'] ?? null;
        $itemNo = $row['item_no'] ?? '';
        $office = $row['office'] ?? '';
        $officer = $row['officer'] ?? '';
        $balance = $row['balance_qty'] ?? null;
        $amount = $row['issue_amount'] ?? null;
        $remarks = $row['notes'] ?? '';
      @endphp

      <tr class="grid-row">
        <td class="center nowrap">{{ $row ? $fmtDate($date) : '' }}</td>
        <td class="center wrap">{{ $row ? e((string) $reference) : '' }}</td>
        <td class="center">{{ $row && $qtyLeft !== null && $qtyLeft !== '' ? (int) $qtyLeft : '' }}</td>
        <td class="center nowrap">{{ $row ? $fmtMoney($unitCost) : '' }}</td>
        <td class="center nowrap">{{ $row ? $fmtMoney($totalCost) : '' }}</td>
        <td class="center">{{ $row && $receiptQty !== null && $receiptQty !== '' ? (int) $receiptQty : '' }}</td>
        <td class="center wrap">{{ $row ? e((string) $itemNo) : '' }}</td>
        <td class="center">{{ $row && $issueQty !== null && $issueQty !== '' ? (int) $issueQty : '' }}</td>
        <td class="center wrap">{{ $row ? e((string) $office) : '' }}</td>
        <td class="center wrap">{{ $row ? e((string) $officer) : '' }}</td>
        <td class="center">{{ $row && $balance !== null && $balance !== '' ? (int) $balance : '' }}</td>
        <td class="center nowrap">{{ $row ? $fmtMoney($amount) : '' }}</td>
        <td class="left wrap">{{ $row ? e((string) $remarks) : '' }}</td>
      </tr>
    @endfor
  </table>
</div>
