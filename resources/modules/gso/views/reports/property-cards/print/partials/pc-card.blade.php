@php
    $maxRows = (int) ($maxGridRows ?? 18);
    $rows = $entries ?? [];

    if (!is_array($rows) && !($rows instanceof \Illuminate\Support\Collection)) {
        $rows = [];
    }

    $running = null;
    $startBalance = $card['starting_balance_qty'] ?? null;
    if ($startBalance !== null) {
        $running = (int) $startBalance;
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
            return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return (string) $value;
        }
    };

    $rows = $rows instanceof \Illuminate\Support\Collection ? $rows->values()->all() : (array) $rows;
    $rows = array_slice($rows, 0, $maxRows);
@endphp

<div class="gso-property-cards-page gso-property-cards-page--pc">
    <div class="gso-property-cards-page__body">
        <div class="gso-property-cards-corner-label">Appendix 54</div>
        <div class="gso-property-cards-title gso-property-cards-title--pc">PROPERTY CARD</div>

        <div class="gso-property-cards-meta-row">
            <div class="gso-property-cards-meta-left">
                <strong>LGU :</strong>
                <span class="gso-property-cards-underline"><strong>{{ $card['lgu'] ?? 'San Francisco, Quezon' }}</strong></span>
            </div>

            <div class="gso-property-cards-meta-right">
                <strong>Fund :</strong>
                <span class="gso-property-cards-underline"><strong>{{ $card['fund'] ?? '-' }}</strong></span>
            </div>
        </div>

        <table class="gso-property-cards-table gso-property-cards-table--pc">
            <colgroup>
                <col style="width:45mm;">
                <col style="width:34mm;">
                <col style="width:14mm;">
                <col style="width:14mm;">
                <col style="width:18mm;">
                <col style="width:55mm;">
                <col style="width:14mm;">
                <col style="width:28mm;">
                <col style="width:auto;">
            </colgroup>

            <tr>
                <td class="gso-property-cards-left gso-property-cards-heading-label">Property, Plant and Equipment :</td>
                <td class="gso-property-cards-left" colspan="8">{{ $card['property_name'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="gso-property-cards-left gso-property-cards-heading-label">Description :</td>
                <td class="gso-property-cards-left gso-property-cards-wrap" colspan="8">{{ $card['description'] ?? '-' }}</td>
            </tr>

            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2">Reference /<br>PAR No.</th>
                <th>Receipt</th>
                <th colspan="3">Issue/Transfer/Disposal</th>
                <th>Balance</th>
                <th rowspan="2">Amount</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>Qty.</th>
                <th>Qty.</th>
                <th colspan="2">Office/Officer</th>
                <th>Qty.</th>
            </tr>

            @for($i = 0; $i < $maxRows; $i++)
                @php
                    $row = $rows[$i] ?? null;
                    $date = $row['event_date'] ?? null;
                    $reference = $row['reference'] ?? null;
                    $qtyIn = $row['qty_in'] ?? null;
                    $qtyOut = $row['qty_out'] ?? null;
                    $office = $row['office'] ?? '';
                    $officer = $row['officer'] ?? '';
                    $amount = $row['amount_snapshot'] ?? null;
                    $remarks = $row['notes'] ?? '';
                    $balance = $row['balance_qty'] ?? null;

                    if ($row !== null) {
                        $inN = ($qtyIn === null || $qtyIn === '') ? 0 : (int) $qtyIn;
                        $outN = ($qtyOut === null || $qtyOut === '') ? 0 : (int) $qtyOut;

                        if ($balance === null && $running !== null) {
                            $running = $running + $inN - $outN;
                            $balance = $running;
                        } elseif ($balance !== null) {
                            $running = (int) $balance;
                        }
                    }
                @endphp

                <tr class="gso-property-cards-grid-row gso-property-cards-grid-row--pc">
                    <td class="gso-property-cards-center gso-property-cards-nowrap">{{ $row ? $fmtDate($date) : '' }}</td>
                    <td class="gso-property-cards-center gso-property-cards-wrap">{{ $row ? e((string) $reference) : '' }}</td>
                    <td class="gso-property-cards-center">{{ $row && ($qtyIn !== null && $qtyIn !== '') ? (int) $qtyIn : '' }}</td>
                    <td class="gso-property-cards-center">{{ $row && ($qtyOut !== null && $qtyOut !== '') ? (int) $qtyOut : '' }}</td>
                    <td class="gso-property-cards-center">{{ $row ? e((string) $office) : '' }}</td>
                    <td class="gso-property-cards-left gso-property-cards-wrap">{{ $row ? e((string) $officer) : '' }}</td>
                    <td class="gso-property-cards-center">{{ $row && ($balance !== null && $balance !== '') ? (int) $balance : '' }}</td>
                    <td class="gso-property-cards-center gso-property-cards-nowrap">{{ $row ? $fmtMoney($amount) : '' }}</td>
                    <td class="gso-property-cards-left gso-property-cards-wrap">{{ $row ? e((string) $remarks) : '' }}</td>
                </tr>
            @endfor
        </table>
    </div>
</div>
