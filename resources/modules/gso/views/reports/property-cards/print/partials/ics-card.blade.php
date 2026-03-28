@php
    $maxRows = (int) ($maxGridRows ?? 18);
    $rows = $entries ?? [];

    if (!is_array($rows) && !($rows instanceof \Illuminate\Support\Collection)) {
        $rows = [];
    }

    $fmtMoney = function ($value) {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 2);
    };

    $fmtDate = function ($value) {
        if (empty($value)) {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('F d, Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    };

    $rows = $rows instanceof \Illuminate\Support\Collection ? $rows->values()->all() : (array) $rows;
    $rows = array_slice($rows, 0, $maxRows);
@endphp

<div class="gso-property-cards-page gso-property-cards-page--ics">
    <div class="gso-property-cards-page__body">
        <div class="gso-property-cards-corner-label">Annex A.1</div>
        <div class="gso-property-cards-title gso-property-cards-title--ics">SEMI-EXPENDABLE PROPERTY CARD</div>

        <div class="gso-property-cards-meta-row">
            <div class="gso-property-cards-meta-left">
                <strong>Entity Name :</strong>
                <span class="gso-property-cards-underline"><strong>{{ $card['entity_name'] ?? $card['lgu'] ?? 'LGU SAN FRANCISCO' }}</strong></span>
            </div>

            <div class="gso-property-cards-meta-right">
                <strong>Fund Cluster :</strong>
                <span class="gso-property-cards-underline"><strong>{{ $card['fund_cluster'] ?? '-' }}</strong></span>
            </div>
        </div>

        <table class="gso-property-cards-table gso-property-cards-table--ics">
            <colgroup>
                <col style="width:13%;">
                <col style="width:10%;">
                <col style="width:3.5%;">
                <col style="width:7.5%;">
                <col style="width:8.5%;">
                <col style="width:4.5%;">
                <col style="width:7.5%;">
                <col style="width:3.5%;">
                <col style="width:12%;">
                <col style="width:12%;">
                <col style="width:5%;">
                <col style="width:6%;">
                <col style="width:7%;">
            </colgroup>

            <tr>
                <td class="gso-property-cards-left gso-property-cards-heading-label" colspan="2">Semi-expendable Property:</td>
                <td class="gso-property-cards-left" colspan="7">{{ $card['property_name'] ?? '-' }}</td>
                <td class="gso-property-cards-left gso-property-cards-heading-label" colspan="3">Semi-expendable<br>Property Number:</td>
                <td class="gso-property-cards-left" colspan="1">{{ $card['se_property_number'] ?? $card['reference'] ?? '-' }}</td>
            </tr>

            <tr>
                <td class="gso-property-cards-left gso-property-cards-heading-label" colspan="2">Description:</td>
                <td class="gso-property-cards-left gso-property-cards-wrap" colspan="11">{{ $card['description'] ?? '-' }}</td>
            </tr>

            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2">Reference</th>
                <th rowspan="2">Qty.</th>
                <th colspan="2">Receipt</th>
                <th rowspan="2">Receipt<br>Qty.</th>
                <th rowspan="2">Item No.</th>
                <th rowspan="2">Qty.</th>
                <th colspan="2">Issue/Transfer/Disposal</th>
                <th rowspan="2">Balance<br>Qty.</th>
                <th rowspan="2">Amount</th>
                <th rowspan="2">Remarks</th>
            </tr>

            <tr>
                <th>Unit Cost</th>
                <th>Total Cost</th>
                <th>Office</th>
                <th>Officer</th>
            </tr>

            @for($i = 0; $i < $maxRows; $i++)
                @php
                    $row = $rows[$i] ?? null;
                    $date = $row['event_date'] ?? null;
                    $reference = $row['reference'] ?? null;
                    $receiptQty = $row['qty_in'] ?? null;
                    $issueQty = $row['qty_out'] ?? null;
                    $qtyLeft = $row['qty_left'] ?? $receiptQty;
                    $unitCost = $row['receipt_unit_cost'] ?? null;
                    $totalCost = $row['receipt_total_cost'] ?? null;
                    $itemNo = $row['item_no'] ?? '';
                    $office = $row['office'] ?? '';
                    $officer = $row['officer'] ?? '';
                    $balance = $row['balance_qty'] ?? null;
                    $amount = $row['issue_amount'] ?? null;
                    $remarks = $row['notes'] ?? '';
                @endphp

                <tr class="gso-property-cards-grid-row gso-property-cards-grid-row--ics">
                    <td class="gso-property-cards-center gso-property-cards-nowrap">{{ $row ? $fmtDate($date) : '' }}</td>
                    <td class="gso-property-cards-center gso-property-cards-wrap">{{ $row ? e((string) $reference) : '' }}</td>
                    <td class="gso-property-cards-center">{{ $row && $qtyLeft !== null && $qtyLeft !== '' ? (int) $qtyLeft : '' }}</td>
                    <td class="gso-property-cards-center gso-property-cards-nowrap">{{ $row ? $fmtMoney($unitCost) : '' }}</td>
                    <td class="gso-property-cards-center gso-property-cards-nowrap">{{ $row ? $fmtMoney($totalCost) : '' }}</td>
                    <td class="gso-property-cards-center">{{ $row && $receiptQty !== null && $receiptQty !== '' ? (int) $receiptQty : '' }}</td>
                    <td class="gso-property-cards-center gso-property-cards-wrap">{{ $row ? e((string) $itemNo) : '' }}</td>
                    <td class="gso-property-cards-center">{{ $row && $issueQty !== null && $issueQty !== '' ? (int) $issueQty : '' }}</td>
                    <td class="gso-property-cards-center gso-property-cards-wrap">{{ $row ? e((string) $office) : '' }}</td>
                    <td class="gso-property-cards-center gso-property-cards-wrap">{{ $row ? e((string) $officer) : '' }}</td>
                    <td class="gso-property-cards-center">{{ $row && $balance !== null && $balance !== '' ? (int) $balance : '' }}</td>
                    <td class="gso-property-cards-center gso-property-cards-nowrap">{{ $row ? $fmtMoney($amount) : '' }}</td>
                    <td class="gso-property-cards-left gso-property-cards-wrap">{{ $row ? e((string) $remarks) : '' }}</td>
                </tr>
            @endfor
        </table>
    </div>
</div>
