@php
  $maxRows = (int) ($maxGridRows ?? 18);
  $entries = $entries ?? [];

  if (!is_array($entries) && !($entries instanceof \Illuminate\Support\Collection)) {
      $entries = [];
  }

  $running = null;
  $startBalance = $card['starting_balance_qty'] ?? null;
  if ($startBalance !== null) {
      $running = (int) $startBalance;
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
          return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
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
    <div class="appendix">Appendix 54</div>
    <div class="title">PROPERTY CARD</div>
  </div>

  <div class="meta-row">
    <div class="meta-left">
      <strong>LGU :</strong>
      <span class="underline"><strong>{{ $card['lgu'] ?? 'San Francisco, Quezon' }}</strong></span>
    </div>

    <div class="meta-right">
      <strong>Fund :</strong>
      <span class="underline"><strong>{{ $card['fund'] ?? '-' }}</strong></span>
    </div>
  </div>

  <table class="pc-table">
    <colgroup>
      <col style="width:45mm;">
      <col style="width:34mm;">
      <col style="width:14mm;">
      <col style="width:14mm;">
      <col style="width:18mm;">
      <col style="width:55mm;">
      <col style="width:14mm;">
      <col style="width:28mm;">
      <col style="width:auto;">
    </colgroup>

    <tr>
      <td class="h-label left">Property, Plant and Equipment :</td>
      <td class="h-value left" colspan="8">{{ $card['property_name'] ?? '-' }}</td>
    </tr>
    <tr>
      <td class="h-label left">Description :</td>
      <td class="h-value left wrap" colspan="8">{{ $card['description'] ?? '-' }}</td>
    </tr>

    <tr>
      <th class="col-date" rowspan="2">Date</th>
      <th class="col-ref" rowspan="2">Reference /<br>PAR No.</th>
      <th class="col-rqty">Receipt</th>
      <th class="col-i-group" colspan="3">Issue/Transfer/Disposal</th>
      <th class="col-bqty">Balance</th>
      <th class="col-amount" rowspan="2">Amount</th>
      <th class="col-remarks" rowspan="2">Remarks</th>
    </tr>
    <tr>
      <th class="col-rqty">Qty.</th>
      <th class="col-iqty">Qty.</th>
      <th class="col-office" colspan="2">Office/Officer</th>
      <th class="col-bqty">Qty.</th>
    </tr>

    @for($i = 0; $i < $maxRows; $i++)
      @php
        $row = $rows[$i] ?? null;
        $date = $row['event_date'] ?? null;
        $reference = $row['reference'] ?? null;
        $qtyIn = $row['qty_in'] ?? null;
        $qtyOut = $row['qty_out'] ?? null;
        $office = $row['office'] ?? '';
        $officer = $row['officer'] ?? '';
        $amount = $row['amount_snapshot'] ?? null;
        $remarks = $row['notes'] ?? '';
        $balance = $row['balance_qty'] ?? null;

        if ($row !== null) {
            $inN = ($qtyIn === null || $qtyIn === '') ? 0 : (int) $qtyIn;
            $outN = ($qtyOut === null || $qtyOut === '') ? 0 : (int) $qtyOut;

            if ($balance === null && $running !== null) {
                $running = $running + $inN - $outN;
                $balance = $running;
            } elseif ($balance !== null) {
                $running = (int) $balance;
            }
        }
      @endphp

      <tr>
        <td class="center nowrap">{{ $row ? $fmtDate($date) : '' }}</td>
        <td class="center wrap">{{ $row ? e((string) $reference) : '' }}</td>
        <td class="center">{{ $row && ($qtyIn !== null && $qtyIn !== '') ? (int) $qtyIn : '' }}</td>
        <td class="center">{{ $row && ($qtyOut !== null && $qtyOut !== '') ? (int) $qtyOut : '' }}</td>
        <td class="center">{{ $row ? e((string) $office) : '' }}</td>
        <td class="left wrap">{{ $row ? e((string) $officer) : '' }}</td>
        <td class="center">{{ $row && ($balance !== null && $balance !== '') ? (int) $balance : '' }}</td>
        <td class="center nowrap">{{ $row ? $fmtMoney($amount) : '' }}</td>
        <td class="left wrap">{{ $row ? e((string) $remarks) : '' }}</td>
      </tr>
    @endfor
  </table>
</div>
