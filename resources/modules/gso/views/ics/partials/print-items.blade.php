@php
  $rowsPrinted = 0;
  $maxRows = $maxGridRows ?? 28;
  $rows = $pageItems ?? [];
@endphp

<table class="items-table stack-next">
  <colgroup>
    <col style="width: 13.2%;">
    <col style="width: 8%;">
    <col style="width: 9%;">
    <col style="width: 13%;">
    <col style="width: 30%;">
    <col style="width: 17.3%;">
    <col style="width: 12%;">
  </colgroup>

  <thead class="items-head">
    <tr>
      <th rowspan="2">Quantity</th>
      <th rowspan="2">Unit</th>
      <th colspan="2">Amount</th>
      <th rowspan="2">Description</th>
      <th rowspan="2">Inventory Item No.</th>
      <th rowspan="2">Estimated Useful Life</th>
    </tr>
    <tr>
      <th>Unit Cost</th>
      <th>Total Cost</th>
    </tr>
  </thead>

  <tbody>
    @foreach(($rows ?? []) as $item)
      @php
        $rowsPrinted++;
        $description = trim(implode("\n", array_filter([
            (string) ($item->item_name_snapshot ?? ''),
            (string) ($item->description_snapshot ?? ''),
        ])));
      @endphp
      <tr class="items-row">
        <td class="center">{{ (int) ($item->quantity ?? 0) }}</td>
        <td class="center">{{ $item->unit_snapshot ?: ' ' }}</td>
        <td class="right">{{ is_null($item->unit_cost_snapshot) ? ' ' : number_format((float) $item->unit_cost_snapshot, 2) }}</td>
        <td class="right">{{ is_null($item->total_cost_snapshot) ? ' ' : number_format((float) $item->total_cost_snapshot, 2) }}</td>
        <td style="white-space: pre-wrap;">{{ $description !== '' ? $description : ' ' }}</td>
        <td class="center">{{ $item->inventory_item_no_snapshot ?: ' ' }}</td>
        <td class="center">{{ $item->estimated_useful_life_snapshot ?: ' ' }}</td>
      </tr>
    @endforeach

    @for($i = $rowsPrinted; $i < $maxRows; $i++)
      <tr class="items-row">
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
      </tr>
    @endfor
  </tbody>
</table>