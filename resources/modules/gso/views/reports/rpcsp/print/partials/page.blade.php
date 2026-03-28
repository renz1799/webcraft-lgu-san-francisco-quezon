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

<div class="gso-rpcsp-print-page">
    @if (!empty($headerImage ?? null))
        <div class="gso-rpcsp-print-header">
            <img src="{{ $headerImage }}" alt="RPCSP Header" class="gso-rpcsp-print-header-image">
        </div>
    @endif

    <div class="gso-rpcsp-print-page__body">
        <div class="gso-rpcsp-print-appendix">{{ $document['appendix_label'] ?? 'RPCSP' }}</div>
        <div class="gso-rpcsp-print-title">{{ $report['title'] ?? 'Report on the Physical Count of Semi-Expendable Property' }}</div>

        @if ($pageNo > 1)
            <div class="gso-rpcsp-print-flow-note">Continuation from Page {{ $pageNo - 1 }}</div>
        @endif

        <table class="gso-rpcsp-print-sheet">
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
                <tr class="gso-rpcsp-print-meta-row">
                    <td colspan="6"><span class="gso-rpcsp-print-meta-label">Entity Name:</span> {{ $document['entity_name'] ?? 'Local Government Unit' }}</td>
                    <td colspan="4"><span class="gso-rpcsp-print-meta-label">Fund Cluster:</span> {{ $document['fund_cluster'] ?? 'All / Multiple' }}</td>
                </tr>
                <tr class="gso-rpcsp-print-meta-row">
                    <td colspan="6"><span class="gso-rpcsp-print-meta-label">Fund Source:</span> {{ $document['fund_source'] ?? 'All Fund Sources' }}</td>
                    <td colspan="4"><span class="gso-rpcsp-print-meta-label">As of:</span> {{ $document['as_of_label'] ?? '' }}</td>
                </tr>
                <tr class="gso-rpcsp-print-meta-row">
                    <td colspan="6"><span class="gso-rpcsp-print-meta-label">Office:</span> {{ $document['department'] ?? 'All Offices' }}</td>
                    <td colspan="4"><span class="gso-rpcsp-print-meta-label">Accountable Officer:</span> {{ $document['accountable_officer'] ?? 'All Accountable Officers' }}</td>
                </tr>
                <tr class="gso-rpcsp-print-meta-row">
                    <td colspan="10">
                        <span class="gso-rpcsp-print-meta-label">Accountability Copy:</span>
                        {{ $signatories['accountable_officer_name'] ?? '' }}
                        @if (!empty($signatories['accountable_officer_designation']))
                            ({{ $signatories['accountable_officer_designation'] }})
                        @endif
                    </td>
                </tr>

                <tr class="gso-rpcsp-print-column-head">
                    <th rowspan="2">Semi-Expendable Property</th>
                    <th rowspan="2">Description</th>
                    <th rowspan="2">SE Property No.</th>
                    <th rowspan="2">Unit</th>
                    <th rowspan="2">Unit Value</th>
                    <th rowspan="2">Qty. per Card</th>
                    <th rowspan="2">Qty. per Physical Count</th>
                    <th colspan="2">Shortage / Overage</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr class="gso-rpcsp-print-column-head">
                    <th>Qty.</th>
                    <th>Value</th>
                </tr>

                @if ($pageRows === [])
                    <tr class="gso-rpcsp-print-data-row">
                        <td colspan="10" class="gso-rpcsp-print-empty-note">No semi-expendable balances found for the selected report window.</td>
                    </tr>
                    @php $fillerRows = max(0, $fillerRows - 1); @endphp
                @else
                    @foreach ($pageRows as $row)
                        <tr class="gso-rpcsp-print-data-row">
                            <td>{{ $row['article'] ?? '' }}</td>
                            <td>{{ $row['description'] ?? '' }}</td>
                            <td class="gso-rpcsp-print-center">{{ $row['property_no'] ?? '' }}</td>
                            <td class="gso-rpcsp-print-center">{{ $row['unit'] ?? '' }}</td>
                            <td class="gso-rpcsp-print-right">{{ number_format((float) ($row['unit_value'] ?? 0), 2) }}</td>
                            <td class="gso-rpcsp-print-right">{{ number_format((int) ($row['balance_per_card_qty'] ?? 0)) }}</td>
                            <td class="gso-rpcsp-print-right">{{ isset($row['count_qty']) ? number_format((int) $row['count_qty']) : '' }}</td>
                            <td class="gso-rpcsp-print-right">{{ isset($row['shortage_overage_qty']) ? number_format((int) $row['shortage_overage_qty']) : '' }}</td>
                            <td class="gso-rpcsp-print-right">{{ isset($row['shortage_overage_value']) ? number_format((float) $row['shortage_overage_value'], 2) : '' }}</td>
                            <td>{{ $row['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @endif

                @for ($i = 0; $i < $fillerRows; $i++)
                    <tr class="gso-rpcsp-print-fill-row">
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor

                @if ($isLastPage)
                    <tr class="gso-rpcsp-print-summary-head">
                        <th colspan="2">Items Listed</th>
                        <th colspan="3">Qty. per Card</th>
                        <th colspan="2">Qty. per Count</th>
                        <th colspan="2">Shortage / Overage Qty.</th>
                        <th>Book Value</th>
                    </tr>
                    <tr class="gso-rpcsp-print-summary-values">
                        <td colspan="2">{{ number_format((int) ($summary['total_items'] ?? 0)) }}</td>
                        <td colspan="3">{{ number_format((int) ($summary['total_balance_qty'] ?? 0)) }}</td>
                        <td colspan="2">{{ isset($summary['total_count_qty']) ? number_format((int) ($summary['total_count_qty'] ?? 0)) : '' }}</td>
                        <td colspan="2">{{ isset($summary['total_shortage_overage_qty']) ? number_format((int) ($summary['total_shortage_overage_qty'] ?? 0)) : '' }}</td>
                        <td class="gso-rpcsp-print-right">{{ number_format((float) ($summary['total_book_value'] ?? 0), 2) }}</td>
                    </tr>
                    <tr class="gso-rpcsp-print-signatures-head">
                        <th colspan="2">Accountable Officer</th>
                        <th colspan="3">Inventory Committee</th>
                        <th colspan="2">Approved By</th>
                        <th colspan="3">Verified By</th>
                    </tr>
                    <tr class="gso-rpcsp-print-signatures-row">
                        <td colspan="2">
                            <div class="gso-rpcsp-print-signature-cell">
                                <div class="gso-rpcsp-print-signature-line">{{ $signatories['accountable_officer_name'] ?? '' }}</div>
                                <div class="gso-rpcsp-print-signature-caption">{{ $signatories['accountable_officer_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="gso-rpcsp-print-signature-cell">
                                <div class="gso-rpcsp-print-signature-stack">
                                    @forelse ($committeeNames as $committeeName)
                                        <div>
                                            <div class="gso-rpcsp-print-signature-line">{{ $committeeName }}</div>
                                            <div class="gso-rpcsp-print-signature-caption">Inventory Committee</div>
                                        </div>
                                    @empty
                                        <div>
                                            <div class="gso-rpcsp-print-signature-line">&nbsp;</div>
                                            <div class="gso-rpcsp-print-signature-caption">Inventory Committee</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="gso-rpcsp-print-signature-cell">
                                <div class="gso-rpcsp-print-signature-line">{{ $signatories['approved_by_name'] ?? '' }}</div>
                                <div class="gso-rpcsp-print-signature-caption">{{ $signatories['approved_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="gso-rpcsp-print-signature-cell">
                                <div class="gso-rpcsp-print-signature-line">{{ $signatories['verified_by_name'] ?? '' }}</div>
                                <div class="gso-rpcsp-print-signature-caption">{{ $signatories['verified_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="gso-rpcsp-print-footer">
        <div class="gso-rpcsp-print-footer-content">
            @if (! $isLastPage)
                <div class="gso-rpcsp-print-flow-note gso-rpcsp-print-flow-note--footer">Continued on Page {{ $pageNo + 1 }}</div>
            @endif

            <div class="gso-rpcsp-print-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
        </div>

        @if (!empty($footerImage ?? null))
            <div class="gso-rpcsp-print-footer-image-wrap">
                <img src="{{ $footerImage }}" alt="RPCSP Footer" class="gso-rpcsp-print-footer-image">
            </div>
        @endif
    </div>
</div>
