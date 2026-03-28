@php
    $document = $report['document'] ?? [];
    $summary = $document['summary'] ?? [];
    $signatories = $document['signatories'] ?? [];
    $pageRows = $page['rows'] ?? [];
    $isLastPage = $pageNo === $totalPages;
    $usedUnits = (int) ($page['used_units'] ?? 0);
    $gridRows = max(1, (int) ($paperProfile['grid_rows'] ?? 13));
    $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
    $targetGridRows = $isLastPage && $lastPageGridRows > 0 ? $lastPageGridRows : $gridRows;
    $fillerRows = max(0, $targetGridRows - $usedUnits);
    $committeeNames = array_values(array_filter([
        $signatories['committee_chair_name'] ?? '',
        $signatories['committee_member_1_name'] ?? '',
        $signatories['committee_member_2_name'] ?? '',
    ], fn ($value) => trim((string) $value) !== ''));
@endphp

<div class="gso-rpcppe-print-page">
    @if (!empty($headerImage ?? null))
        <div class="gso-rpcppe-print-header">
            <img src="{{ $headerImage }}" alt="RPCPPE Header" class="gso-rpcppe-print-header-image">
        </div>
    @endif

    <div class="gso-rpcppe-print-page__body">
        <div class="gso-rpcppe-print-appendix">{{ $document['appendix_label'] ?? 'RPCPPE' }}</div>
        <div class="gso-rpcppe-print-title">{{ $report['title'] ?? 'Report on the Physical Count of Property, Plant and Equipment' }}</div>

        @if ($pageNo > 1)
            <div class="gso-rpcppe-print-flow-note">Continuation from Page {{ $pageNo - 1 }}</div>
        @endif

        <table class="gso-rpcppe-print-sheet">
            <colgroup>
                <col style="width: 12%;">
                <col style="width: 20%;">
                <col style="width: 12%;">
                <col style="width: 6%;">
                <col style="width: 8%;">
                <col style="width: 9%;">
                <col style="width: 9%;">
                <col style="width: 7%;">
                <col style="width: 8%;">
                <col style="width: 9%;">
            </colgroup>
            <tbody>
                <tr class="gso-rpcppe-print-meta-row">
                    <td colspan="6"><span class="gso-rpcppe-print-meta-label">Entity Name:</span> {{ $document['entity_name'] ?? 'Local Government Unit' }}</td>
                    <td colspan="4"><span class="gso-rpcppe-print-meta-label">Fund Cluster:</span> {{ $document['fund_cluster'] ?? 'All / Multiple' }}</td>
                </tr>
                <tr class="gso-rpcppe-print-meta-row">
                    <td colspan="6"><span class="gso-rpcppe-print-meta-label">Fund Source:</span> {{ $document['fund_source'] ?? 'All Fund Sources' }}</td>
                    <td colspan="4"><span class="gso-rpcppe-print-meta-label">As of:</span> {{ $document['as_of_label'] ?? '' }}</td>
                </tr>
                <tr class="gso-rpcppe-print-meta-row">
                    <td colspan="6"><span class="gso-rpcppe-print-meta-label">Office:</span> {{ $document['department'] ?? 'All Offices' }}</td>
                    <td colspan="4"><span class="gso-rpcppe-print-meta-label">Accountable Officer:</span> {{ $document['accountable_officer'] ?? 'All Accountable Officers' }}</td>
                </tr>
                <tr class="gso-rpcppe-print-meta-row">
                    <td colspan="10">
                        <span class="gso-rpcppe-print-meta-label">Accountability Copy:</span>
                        {{ $signatories['accountable_officer_name'] ?? '' }}
                        @if (!empty($signatories['accountable_officer_designation']))
                            ({{ $signatories['accountable_officer_designation'] }})
                        @endif
                    </td>
                </tr>

                <tr class="gso-rpcppe-print-column-head">
                    <th rowspan="2">Article</th>
                    <th rowspan="2">Description</th>
                    <th rowspan="2">Property No.</th>
                    <th rowspan="2">Unit</th>
                    <th rowspan="2">Unit Value</th>
                    <th rowspan="2">Qty. per Property Card</th>
                    <th rowspan="2">Qty. per Physical Count</th>
                    <th colspan="2">Shortage / Overage</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr class="gso-rpcppe-print-column-head">
                    <th>Qty.</th>
                    <th>Value</th>
                </tr>

                @if ($pageRows === [])
                    <tr class="gso-rpcppe-print-data-row">
                        <td colspan="10" class="gso-rpcppe-print-empty-note">No PPE balances found for the selected report window.</td>
                    </tr>
                    @php $fillerRows = max(0, $fillerRows - 1); @endphp
                @else
                    @foreach ($pageRows as $row)
                        <tr class="gso-rpcppe-print-data-row">
                            <td>{{ $row['article'] ?? '' }}</td>
                            <td>{{ $row['description'] ?? '' }}</td>
                            <td class="gso-rpcppe-print-center">{{ $row['property_no'] ?? '' }}</td>
                            <td class="gso-rpcppe-print-center">{{ $row['unit'] ?? '' }}</td>
                            <td class="gso-rpcppe-print-right">{{ number_format((float) ($row['unit_value'] ?? 0), 2) }}</td>
                            <td class="gso-rpcppe-print-right">{{ number_format((int) ($row['balance_per_card_qty'] ?? 0)) }}</td>
                            <td class="gso-rpcppe-print-right">{{ isset($row['count_qty']) ? number_format((int) $row['count_qty']) : '' }}</td>
                            <td class="gso-rpcppe-print-right">{{ isset($row['shortage_overage_qty']) ? number_format((int) $row['shortage_overage_qty']) : '' }}</td>
                            <td class="gso-rpcppe-print-right">{{ isset($row['shortage_overage_value']) ? number_format((float) $row['shortage_overage_value'], 2) : '' }}</td>
                            <td>{{ $row['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @endif

                @for ($i = 0; $i < $fillerRows; $i++)
                    <tr class="gso-rpcppe-print-fill-row">
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor

                @if ($isLastPage)
                    <tr class="gso-rpcppe-print-summary-head">
                        <th colspan="2">Items Listed</th>
                        <th colspan="3">Qty. per Card</th>
                        <th colspan="2">Qty. per Count</th>
                        <th colspan="2">Shortage / Overage Qty.</th>
                        <th>Book Value</th>
                    </tr>
                    <tr class="gso-rpcppe-print-summary-values">
                        <td colspan="2">{{ number_format((int) ($summary['total_items'] ?? 0)) }}</td>
                        <td colspan="3">{{ number_format((int) ($summary['total_balance_qty'] ?? 0)) }}</td>
                        <td colspan="2">{{ isset($summary['total_count_qty']) ? number_format((int) ($summary['total_count_qty'] ?? 0)) : '' }}</td>
                        <td colspan="2">{{ isset($summary['total_shortage_overage_qty']) ? number_format((int) ($summary['total_shortage_overage_qty'] ?? 0)) : '' }}</td>
                        <td class="gso-rpcppe-print-right">{{ number_format((float) ($summary['total_book_value'] ?? 0), 2) }}</td>
                    </tr>
                    <tr class="gso-rpcppe-print-signatures-head">
                        <th colspan="2">Accountable Officer</th>
                        <th colspan="3">Inventory Committee</th>
                        <th colspan="2">Approved By</th>
                        <th colspan="3">Verified By</th>
                    </tr>
                    <tr class="gso-rpcppe-print-signatures-row">
                        <td colspan="2">
                            <div class="gso-rpcppe-print-signature-cell">
                                <div class="gso-rpcppe-print-signature-line">{{ $signatories['accountable_officer_name'] ?? '' }}</div>
                                <div class="gso-rpcppe-print-signature-caption">{{ $signatories['accountable_officer_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="gso-rpcppe-print-signature-cell">
                                <div class="gso-rpcppe-print-signature-stack">
                                    @forelse ($committeeNames as $committeeName)
                                        <div>
                                            <div class="gso-rpcppe-print-signature-line">{{ $committeeName }}</div>
                                            <div class="gso-rpcppe-print-signature-caption">Inventory Committee</div>
                                        </div>
                                    @empty
                                        <div>
                                            <div class="gso-rpcppe-print-signature-line">&nbsp;</div>
                                            <div class="gso-rpcppe-print-signature-caption">Inventory Committee</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="gso-rpcppe-print-signature-cell">
                                <div class="gso-rpcppe-print-signature-line">{{ $signatories['approved_by_name'] ?? '' }}</div>
                                <div class="gso-rpcppe-print-signature-caption">{{ $signatories['approved_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="gso-rpcppe-print-signature-cell">
                                <div class="gso-rpcppe-print-signature-line">{{ $signatories['verified_by_name'] ?? '' }}</div>
                                <div class="gso-rpcppe-print-signature-caption">{{ $signatories['verified_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="gso-rpcppe-print-footer">
        <div class="gso-rpcppe-print-footer-content">
            @if (! $isLastPage)
                <div class="gso-rpcppe-print-flow-note gso-rpcppe-print-flow-note--footer">Continued on Page {{ $pageNo + 1 }}</div>
            @endif

            <div class="gso-rpcppe-print-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
        </div>

        @if (!empty($footerImage ?? null))
            <div class="gso-rpcppe-print-footer-image-wrap">
                <img src="{{ $footerImage }}" alt="RPCPPE Footer" class="gso-rpcppe-print-footer-image">
            </div>
        @endif
    </div>
</div>
