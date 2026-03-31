@php
    $rows = collect($rows ?? [])->values();
    $gridRows = max((int) ($gridRows ?? 26), $rows->count());
    $isLastPage = (bool) ($isLastPage ?? false);
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $usedGridUnits = max($rows->count(), (int) ($usedGridUnits ?? $rows->count()));
    $remainingRows = max(0, $gridRows - $usedGridUnits);
    $itemColumns = array_merge([
        'qty' => '7%',
        'unit' => '8%',
        'description' => '35%',
        'property_number' => '20%',
        'date_acquired' => '15%',
        'amount' => '15%',
    ], (array) ($paperProfile['item_column_widths'] ?? []));

    if ($isLastPage && $lastPageGridRows > 0) {
        $remainingRows = max(0, $lastPageGridRows - $usedGridUnits);
    }
@endphp

<table class="gso-par-print-sheet gso-par-print-stack-next">
    <colgroup>
        <col style="width: {{ $itemColumns['qty'] }};">
        <col style="width: {{ $itemColumns['unit'] }};">
        <col style="width: {{ $itemColumns['description'] }};">
        <col style="width: {{ $itemColumns['property_number'] }};">
        <col style="width: {{ $itemColumns['date_acquired'] }};">
        <col style="width: {{ $itemColumns['amount'] }};">
    </colgroup>
    <thead class="gso-par-print-items-head">
        <tr>
            <th class="gso-par-print-col--qty gso-par-print-col-head--compact" style="width: {{ $itemColumns['qty'] }};">Qty</th>
            <th class="gso-par-print-col--unit gso-par-print-col-head--compact" style="width: {{ $itemColumns['unit'] }};">Unit</th>
            <th class="gso-par-print-col--description" style="width: {{ $itemColumns['description'] }};">Description</th>
            <th class="gso-par-print-col--property-number" style="width: {{ $itemColumns['property_number'] }};">Property Number</th>
            <th class="gso-par-print-col--date-acquired gso-par-print-col-head--compact" style="width: {{ $itemColumns['date_acquired'] }};">Date Acquired</th>
            <th class="gso-par-print-col--amount gso-par-print-col-head--compact" style="width: {{ $itemColumns['amount'] }};">Amount</th>
        </tr>
    </thead>
    <tbody>
        @if ($rows->isEmpty())
            <tr>
                <td colspan="6" class="gso-par-print-empty-note">No PAR line items are available for print yet.</td>
            </tr>
            @php $remainingRows = max(0, $remainingRows - 1); @endphp
        @else
            @foreach ($rows as $row)
                <tr class="gso-par-print-items-row">
                    <td class="gso-par-print-center gso-par-print-col--qty gso-par-print-col-cell--compact gso-par-print-cell--numeric" style="width: {{ $itemColumns['qty'] }};">{{ (int) ($row['quantity'] ?? 0) }}</td>
                    <td class="gso-par-print-center gso-par-print-col--unit gso-par-print-col-cell--compact" style="width: {{ $itemColumns['unit'] }};">{{ $row['unit'] ?: ' ' }}</td>
                    <td class="gso-par-print-col--description" style="width: {{ $itemColumns['description'] }};">{{ $row['description'] ?: ' ' }}</td>
                    <td class="gso-par-print-center gso-par-print-col--property-number" style="width: {{ $itemColumns['property_number'] }};">{{ $row['property_number'] ?: ' ' }}</td>
                    <td class="gso-par-print-center gso-par-print-col--date-acquired gso-par-print-col-cell--compact" style="width: {{ $itemColumns['date_acquired'] }};">{{ $row['date_acquired_label'] ?: ' ' }}</td>
                    <td class="gso-par-print-right gso-par-print-col--amount gso-par-print-col-cell--compact gso-par-print-cell--numeric" style="width: {{ $itemColumns['amount'] }};">
                        {{ $row['amount'] !== null ? number_format((float) $row['amount'], 2) : ' ' }}
                    </td>
                </tr>
            @endforeach
        @endif

        @for ($i = 0; $i < $remainingRows; $i++)
            <tr class="gso-par-print-items-row">
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
