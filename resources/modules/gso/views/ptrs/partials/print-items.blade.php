@php
  $rowsPrinted = 0;
  $maxRows = $maxGridRows ?? 12;
  $rows = $pageItems ?? [];
@endphp

<table class="items-table stack-next">
  <colgroup>
    <col style="width: 15%;">
    <col style="width: 18%;">
    <col style="width: 39%;">
    <col style="width: 13%;">
    <col style="width: 15%;">
  </colgroup>
  <thead class="items-head">
    <tr>
      <th>Date Acquired</th>
      <th>Property No.</th>
      <th>Description</th>
      <th>Amount</th>
      <th>Condition of PPE</th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $ptrItem)
      @php
        $inventoryItem = $ptrItem->inventoryItem;
        $dateAcquired = $ptrItem->date_acquired_snapshot
          ? optional($ptrItem->date_acquired_snapshot)->format('m/d/Y')
          : ($inventoryItem?->acquisition_date ? optional($inventoryItem->acquisition_date)->format('m/d/Y') : '');
        $propertyNo = (string) ($ptrItem->property_number_snapshot ?? $inventoryItem?->property_number ?? '');
        $itemName = trim((string) ($ptrItem->item_name_snapshot ?? $inventoryItem?->item?->item_name ?? ''));
        $description = trim((string) ($ptrItem->description_snapshot ?? $inventoryItem?->description ?? ''));
        $condition = (string) ($ptrItem->condition_snapshot ?? $inventoryItem?->condition ?? '');
        $amount = $ptrItem->amount_snapshot;
      @endphp
      <tr class="items-row">
        <td class="center">{!! $dateAcquired !== '' ? e($dateAcquired) : '&nbsp;' !!}</td>
        <td class="center">{!! $propertyNo !== '' ? e($propertyNo) : '&nbsp;' !!}</td>
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
      </tr>
    @endfor
  </tbody>
</table>
