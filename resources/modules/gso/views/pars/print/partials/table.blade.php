@php
    $rows = collect($rows ?? [])->values();
    $gridRows = max((int) ($gridRows ?? 26), $rows->count());
    $isLastPage = (bool) ($isLastPage ?? false);
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $usedGridUnits = max($rows->count(), (int) ($usedGridUnits ?? $rows->count()));
    $remainingRows = max(0, $gridRows - $usedGridUnits);

    if ($isLastPage && $lastPageGridRows > 0) {
        $remainingRows = max(0, $lastPageGridRows - $usedGridUnits);
    }
@endphp

<table class="gso-par-print-sheet gso-par-print-stack-next">
    <colgroup>
        <col style="width:7%;">
        <col style="width:8%;">
        <col style="width:35%;">
        <col style="width:20%;">
        <col style="width:15%;">
        <col style="width:15%;">
    </colgroup>
    <thead class="gso-par-print-items-head">
        <tr>
            <th>Qty</th>
            <th>Unit</th>
            <th>Description</th>
            <th>Property Number</th>
            <th>Date Acquired</th>
            <th>Amount</th>
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
                    <td class="gso-par-print-center">{{ (int) ($row['quantity'] ?? 0) }}</td>
                    <td class="gso-par-print-center">{{ $row['unit'] ?: ' ' }}</td>
                    <td>{{ $row['description'] ?: ' ' }}</td>
                    <td class="gso-par-print-center">{{ $row['property_number'] ?: ' ' }}</td>
                    <td class="gso-par-print-center">{{ $row['date_acquired_label'] ?: ' ' }}</td>
                    <td class="gso-par-print-right">
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
