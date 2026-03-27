@php
  $rowsPrinted = 0;
  $maxRows = $maxGridRows ?? 26;
  $rows = $pageItems ?? [];
@endphp

<table class="items-table stack-next">
  <colgroup>
    <col style="width: 10%;">  {{-- Quantity --}}
    <col style="width: 10%;">  {{-- Unit --}}
    <col style="width: 34%;">  {{-- Description --}}
    <col style="width: 16%;">  {{-- Property Number --}}
    <col style="width: 15%;">  {{-- Date Acquired --}}
    <col style="width: 15%;">  {{-- Amount --}}
  </colgroup>

  <thead class="items-head">
    <tr>
      <th>Quantity</th>
      <th>Unit</th>
      <th>Description</th>
      <th>Property<br>Number</th>
      <th>Date<br>Acquired</th>
      <th>Amount</th>
    </tr>
  </thead>

  <tbody>
    @foreach($rows as $pi)
      @php
        $inv = $pi->inventoryItem;

        $qty = (int) ($pi->quantity ?? 1);

        $unit = $pi->unit_snapshot
          ?? $inv?->unit
          ?? '—';

        $desc = $pi->item_name_snapshot
          ?? $inv?->item?->item_name
          ?? $inv?->item_name
          ?? '—';

        $propNo = $pi->property_number_snapshot
          ?? $inv?->property_number
          ?? '—';

        $dateAcq = $inv?->acquisition_date
          ? optional($inv->acquisition_date)->format('m/d/Y')
          : '—';

        $amount = $pi->amount_snapshot;
      @endphp

      <tr class="items-row">
        <td class="center">{{ $qty }}</td>
        <td class="center">{{ $unit }}</td>
        <td>{{ $desc }}</td>
        <td class="center">{{ $propNo }}</td>
        <td class="center">{{ $dateAcq }}</td>
        <td class="right">{{ is_null($amount) ? '—' : number_format((float) $amount, 2) }}</td>
      </tr>

      @php $rowsPrinted++; @endphp
    @endforeach

    {{-- Fill remaining blank rows to keep table height fixed --}}
    @for($i = $rowsPrinted; $i < $maxRows; $i++)
      <tr class="items-row">
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