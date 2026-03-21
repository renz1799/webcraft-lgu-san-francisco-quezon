@php
  $columnCount = 10;
  $rows = collect($pageRows ?? []);
  $blankRowCutoff = (int) ($blankRowCutoff ?? $maxGridRows ?? 0);
  $shouldPadBlankRows = $rows->count() < $blankRowCutoff;
@endphp

<table class="rpcppe-items-table">
  <thead>
    <tr>
      <th rowspan="2" style="width:14%;">Article</th>
      <th rowspan="2" style="width:21%;">Description</th>
      <th rowspan="2" style="width:11%;">Property No.</th>
      <th rowspan="2" style="width:5%;">Unit</th>
      <th rowspan="2" style="width:7%;">Unit Value</th>
      <th rowspan="2" style="width:8%;">Qty. per Property Card</th>
      <th rowspan="2" style="width:8%;">Qty. per Physical Count</th>
      <th colspan="2" style="width:12%;">Shortage / Overage</th>
      <th rowspan="2" style="width:14%;">Remarks</th>
    </tr>
    <tr>
      <th style="width:6%;">Qty.</th>
      <th style="width:6%;">Value</th>
    </tr>
  </thead>
  <tbody>
    @if($rows->isEmpty())
      <tr class="rpcppe-items-row">
        <td colspan="{{ $columnCount }}" class="rpcppe-empty-note">No mock PPE balances found for the selected report window.</td>
      </tr>
    @else
      @foreach($rows as $row)
        <tr class="rpcppe-items-row">
          <td>{{ $row['article'] ?? '' }}</td>
          <td>{{ $row['description'] ?? '' }}</td>
          <td class="rpcppe-center">{{ $row['property_no'] ?? '' }}</td>
          <td class="rpcppe-center">{{ $row['unit'] ?? '' }}</td>
          <td class="rpcppe-right">{{ number_format((float) ($row['unit_value'] ?? 0), 2) }}</td>
          <td class="rpcppe-right">{{ number_format((int) ($row['balance_per_card_qty'] ?? 0)) }}</td>
          <td class="rpcppe-right">{{ isset($row['count_qty']) ? number_format((int) $row['count_qty']) : '' }}</td>
          <td class="rpcppe-right">{{ isset($row['shortage_overage_qty']) ? number_format((int) $row['shortage_overage_qty']) : '' }}</td>
          <td class="rpcppe-right">{{ isset($row['shortage_overage_value']) ? number_format((float) $row['shortage_overage_value'], 2) : '' }}</td>
          <td>{{ $row['remarks'] ?? '' }}</td>
        </tr>
      @endforeach
    @endif

    @if($shouldPadBlankRows)
      @for($i = $rows->count(); $i < $maxGridRows; $i++)
        <tr class="rpcppe-items-row rpcppe-items-row--blank">
          @for($j = 0; $j < $columnCount; $j++)
            <td>&nbsp;</td>
          @endfor
        </tr>
      @endfor
    @endif
  </tbody>
</table>
