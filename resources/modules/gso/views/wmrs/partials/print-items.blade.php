@php
  $rowsPrinted = 0;
  $maxRows = $maxGridRows ?? 10;
  $rows = $pageItems ?? [];
@endphp

<table class="items-table stack-next">
  <colgroup>
    <col style="width: 8%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 28%;">
    <col style="width: 14%;">
    <col style="width: 13%;">
    <col style="width: 13%;">
  </colgroup>
  <thead class="items-head">
    <tr>
      <th rowspan="2">Item</th>
      <th rowspan="2">Quantity</th>
      <th rowspan="2">Unit</th>
      <th rowspan="2">Description</th>
      <th colspan="3">Record of Sales<br>Official Receipt</th>
    </tr>
    <tr>
      <th>No.</th>
      <th>Date</th>
      <th>Amount</th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $wmrItem)
      @php
        $inventoryItem = $wmrItem->inventoryItem;
        $lineNo = (int) ($wmrItem->line_no ?? 0) > 0 ? (int) $wmrItem->line_no : $loop->iteration;
        $quantity = (int) ($wmrItem->quantity ?? 1);
        $unit = trim((string) ($wmrItem->unit_snapshot ?? $inventoryItem?->unit ?? ''));
        $itemName = trim((string) ($wmrItem->item_name_snapshot ?? $inventoryItem?->item?->item_name ?? ''));
        $description = trim((string) ($wmrItem->description_snapshot ?? $inventoryItem?->description ?? ''));
        $receiptNo = trim((string) ($wmrItem->official_receipt_no ?? ''));
        $receiptDate = $wmrItem->official_receipt_date ? optional($wmrItem->official_receipt_date)->format('m/d/Y') : '';
        $amount = $wmrItem->official_receipt_amount;
      @endphp
      <tr class="items-row">
        <td class="center">{{ $lineNo }}</td>
        <td class="center">{{ $quantity }}</td>
        <td class="center">{!! $unit !== '' ? e($unit) : '&nbsp;' !!}</td>
        <td>
          @if($itemName !== '')
            <div class="bold">{{ $itemName }}</div>
          @endif
          @if($description !== '' && strcasecmp($description, $itemName) !== 0)
            <div class="small">{{ $description }}</div>
          @endif
          @if($itemName === '' && $description === '')
            &nbsp;
          @endif
        </td>
        <td class="center">{!! $receiptNo !== '' ? e($receiptNo) : '&nbsp;' !!}</td>
        <td class="center">{!! $receiptDate !== '' ? e($receiptDate) : '&nbsp;' !!}</td>
        <td class="right">{!! $amount !== null ? e(number_format((float) $amount, 2)) : '&nbsp;' !!}</td>
      </tr>
      @php $rowsPrinted++; @endphp
    @endforeach

    @for($i = $rowsPrinted; $i < $maxRows; $i++)
      <tr class="items-row">
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    @endfor

    @if($showTotal ?? false)
      <tr>
        <td colspan="6" class="right bold">TOTAL</td>
        <td class="right bold">{{ number_format((float) ($totalAmount ?? 0), 2) }}</td>
      </tr>
    @endif
  </tbody>
</table>