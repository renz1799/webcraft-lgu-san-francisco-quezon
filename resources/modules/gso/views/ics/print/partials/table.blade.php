@php
    $rows = collect($rows ?? [])->values();
    $gridRows = max((int) ($gridRows ?? 28), $rows->count());
    $isLastPage = (bool) ($isLastPage ?? false);
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $usedGridUnits = max($rows->count(), (int) ($usedGridUnits ?? $rows->count()));
    $remainingRows = max(0, $gridRows - $usedGridUnits);
    $itemColumns = array_merge([
        'qty' => '7%',
        'unit' => '7%',
        'unit_cost' => '11%',
        'total_cost' => '11%',
        'description' => '34%',
        'inventory_item_no' => '18%',
        'useful_life' => '12%',
    ], (array) ($paperProfile['item_column_widths'] ?? []));

    if ($isLastPage && $lastPageGridRows > 0) {
        $remainingRows = max(0, $lastPageGridRows - $usedGridUnits);
    }
@endphp

<table class="gso-ics-print-sheet gso-ics-print-stack-next">
    <colgroup>
        <col style="width: {{ $itemColumns['qty'] }};">
        <col style="width: {{ $itemColumns['unit'] }};">
        <col style="width: {{ $itemColumns['unit_cost'] }};">
        <col style="width: {{ $itemColumns['total_cost'] }};">
        <col style="width: {{ $itemColumns['description'] }};">
        <col style="width: {{ $itemColumns['inventory_item_no'] }};">
        <col style="width: {{ $itemColumns['useful_life'] }};">
    </colgroup>
    <thead class="gso-ics-print-items-head">
        <tr>
            <th rowspan="2" class="gso-ics-print-col--qty gso-ics-print-col-head--compact" style="width: {{ $itemColumns['qty'] }};">Qty</th>
            <th rowspan="2" class="gso-ics-print-col--unit gso-ics-print-col-head--compact" style="width: {{ $itemColumns['unit'] }};">Unit</th>
            <th colspan="2">Amount</th>
            <th rowspan="2" class="gso-ics-print-col--description" style="width: {{ $itemColumns['description'] }};">Description</th>
            <th rowspan="2" class="gso-ics-print-col--inventory-item-no" style="width: {{ $itemColumns['inventory_item_no'] }};">Inventory Item No.</th>
            <th rowspan="2" class="gso-ics-print-col--useful-life gso-ics-print-col-head--compact" style="width: {{ $itemColumns['useful_life'] }};">Est. Useful Life</th>
        </tr>
        <tr>
            <th class="gso-ics-print-col--unit-cost gso-ics-print-col-head--compact" style="width: {{ $itemColumns['unit_cost'] }};">Unit Cost</th>
            <th class="gso-ics-print-col--total-cost gso-ics-print-col-head--compact" style="width: {{ $itemColumns['total_cost'] }};">Total Cost</th>
        </tr>
    </thead>
    <tbody>
        @if ($rows->isEmpty())
            <tr>
                <td colspan="7" class="gso-ics-print-empty-note">No ICS line items are available for print yet.</td>
            </tr>
            @php $remainingRows = max(0, $remainingRows - 1); @endphp
        @else
            @foreach ($rows as $row)
                <tr class="gso-ics-print-items-row">
                    <td class="gso-ics-print-center gso-ics-print-col--qty gso-ics-print-col-cell--compact gso-ics-print-cell--numeric" style="width: {{ $itemColumns['qty'] }};">{{ (int) ($row['quantity'] ?? 0) }}</td>
                    <td class="gso-ics-print-center gso-ics-print-col--unit gso-ics-print-col-cell--compact" style="width: {{ $itemColumns['unit'] }};">{{ $row['unit'] ?: ' ' }}</td>
                    <td class="gso-ics-print-right gso-ics-print-col--unit-cost gso-ics-print-col-cell--compact gso-ics-print-cell--numeric" style="width: {{ $itemColumns['unit_cost'] }};">
                        {{ $row['unit_cost'] !== null ? number_format((float) $row['unit_cost'], 2) : ' ' }}
                    </td>
                    <td class="gso-ics-print-right gso-ics-print-col--total-cost gso-ics-print-col-cell--compact gso-ics-print-cell--numeric" style="width: {{ $itemColumns['total_cost'] }};">
                        {{ $row['total_cost'] !== null ? number_format((float) $row['total_cost'], 2) : ' ' }}
                    </td>
                    <td class="gso-ics-print-description gso-ics-print-col--description" style="width: {{ $itemColumns['description'] }};">{{ $row['description'] ?: ' ' }}</td>
                    <td class="gso-ics-print-center gso-ics-print-col--inventory-item-no" style="width: {{ $itemColumns['inventory_item_no'] }};">{{ $row['inventory_item_no'] ?: ' ' }}</td>
                    <td class="gso-ics-print-center gso-ics-print-col--useful-life gso-ics-print-col-cell--compact" style="width: {{ $itemColumns['useful_life'] }};">{{ $row['estimated_useful_life'] ?: ' ' }}</td>
                </tr>
            @endforeach
        @endif

        @for ($i = 0; $i < $remainingRows; $i++)
            <tr class="gso-ics-print-items-row">
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
