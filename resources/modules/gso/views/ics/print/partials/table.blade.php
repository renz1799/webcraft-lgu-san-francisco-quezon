@php
    $rows = collect($rows ?? [])->values();
    $gridRows = max((int) ($gridRows ?? 28), $rows->count());
    $isLastPage = (bool) ($isLastPage ?? false);
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $usedGridUnits = max($rows->count(), (int) ($usedGridUnits ?? $rows->count()));
    $remainingRows = max(0, $gridRows - $usedGridUnits);

    if ($isLastPage && $lastPageGridRows > 0) {
        $remainingRows = max(0, $lastPageGridRows - $usedGridUnits);
    }
@endphp

<table class="gso-ics-print-sheet gso-ics-print-stack-next">
    <colgroup>
        <col style="width:7%;">
        <col style="width:7%;">
        <col style="width:11%;">
        <col style="width:11%;">
        <col style="width:34%;">
        <col style="width:18%;">
        <col style="width:12%;">
    </colgroup>
    <thead class="gso-ics-print-items-head">
        <tr>
            <th rowspan="2">Qty</th>
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
        @if ($rows->isEmpty())
            <tr>
                <td colspan="7" class="gso-ics-print-empty-note">No ICS line items are available for print yet.</td>
            </tr>
            @php $remainingRows = max(0, $remainingRows - 1); @endphp
        @else
            @foreach ($rows as $row)
                <tr class="gso-ics-print-items-row">
                    <td class="gso-ics-print-center">{{ (int) ($row['quantity'] ?? 0) }}</td>
                    <td class="gso-ics-print-center">{{ $row['unit'] ?: ' ' }}</td>
                    <td class="gso-ics-print-right">
                        {{ $row['unit_cost'] !== null ? number_format((float) $row['unit_cost'], 2) : ' ' }}
                    </td>
                    <td class="gso-ics-print-right">
                        {{ $row['total_cost'] !== null ? number_format((float) $row['total_cost'], 2) : ' ' }}
                    </td>
                    <td class="gso-ics-print-description">{{ $row['description'] ?: ' ' }}</td>
                    <td class="gso-ics-print-center">{{ $row['inventory_item_no'] ?: ' ' }}</td>
                    <td class="gso-ics-print-center">{{ $row['estimated_useful_life'] ?: ' ' }}</td>
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
