@php
  $rowsPrinted = 0;
  $maxRows = $maxGridRows ?? 10;
  $rows = $pageItems ?? [];
@endphp

<table class="items-table stack-next">
  <colgroup>
    <col style="width: 7%;">
    <col style="width: 13%;">
    <col style="width: 16%;">
    <col style="width: 34%;">
    <col style="width: 12%;">
    <col style="width: 10%;">
    <col style="width: 8%;">
  </colgroup>
  <thead class="items-head">
    <tr>
      <th>Qty</th>
      <th>Date Acquired</th>
      <th>Inventory No.</th>
      <th>Description</th>
      <th>Amount</th>
      <th>Useful Life</th>
      <th>Condition</th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $itrItem)
      @php
        $inventoryItem = $itrItem->inventoryItem;
        $dateAcquired = $itrItem->date_acquired_snapshot
          ? optional($itrItem->date_acquired_snapshot)->format('m/d/Y')
          : ($inventoryItem?->acquisition_date ? optional($inventoryItem->acquisition_date)->format('m/d/Y') : '');
        $inventoryNo = (string) ($itrItem->inventory_item_no_snapshot ?? $inventoryItem?->property_number ?? $inventoryItem?->inventory_item_no ?? '');
        $itemName = trim((string) ($itrItem->item_name_snapshot ?? $inventoryItem?->item?->item_name ?? ''));
        $description = trim((string) ($itrItem->description_snapshot ?? $inventoryItem?->description ?? ''));
        $condition = (string) ($itrItem->condition_snapshot ?? $inventoryItem?->condition ?? '');
        $amount = $itrItem->amount_snapshot;
        $quantity = (int) ($itrItem->quantity ?? 1);
        $usefulLife = trim((string) ($itrItem->estimated_useful_life_snapshot ?? $inventoryItem?->service_life ?? ''));
      @endphp
      <tr class="items-row">
        <td class="center">{{ $quantity }}</td>
        <td class="center">{!! $dateAcquired !== '' ? e($dateAcquired) : '&nbsp;' !!}</td>
        <td class="center">{!! $inventoryNo !== '' ? e($inventoryNo) : '&nbsp;' !!}</td>
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
        <td class="right">{!! $amount !== null ? e(number_format((float) $amount, 2)) : '&nbsp;' !!}</td>
        <td class="center">{!! $usefulLife !== '' ? e($usefulLife) : '&nbsp;' !!}</td>
        <td class="center">{!! $condition !== '' ? e($condition) : '&nbsp;' !!}</td>
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
  </tbody>
</table>


