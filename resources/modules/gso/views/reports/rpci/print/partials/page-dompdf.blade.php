@php
    $document = $report['document'] ?? [];
    $summary = $document['summary'] ?? [];
    $signatories = $document['signatories'] ?? [];
    $pageRows = $page['rows'] ?? [];
    $isLastPage = $pageNo === $totalPages;
    $usedUnits = (int) ($page['used_units'] ?? 0);
    $gridRows = max(1, (int) ($paperProfile['grid_rows'] ?? 16));
    $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
    $targetGridRows = $isLastPage && $lastPageGridRows > 0 ? $lastPageGridRows : $gridRows;
    $fillerRows = max(0, $targetGridRows - $usedUnits);
    $assumptionDateLabel = !empty($signatories['date_of_assumption'])
        ? \Carbon\Carbon::parse($signatories['date_of_assumption'])->format('F j, Y')
        : '';
    $committeeNames = array_values(array_filter([
        $signatories['committee_chair_name'] ?? '',
        $signatories['committee_member_1_name'] ?? '',
        $signatories['committee_member_2_name'] ?? '',
    ], fn ($value) => trim((string) $value) !== ''));
@endphp

<div class="gso-rpci-print-page">
    @if (!empty($headerImage ?? null))
        <div class="gso-rpci-print-header">
            <img src="{{ $headerImage }}" alt="RPCI Header" class="gso-rpci-print-header-image">
        </div>
    @endif

    <div class="gso-rpci-print-page__body">
        <div class="gso-rpci-print-appendix">{{ $document['appendix_label'] ?? 'Annex 48' }}</div>
        <div class="gso-rpci-print-title">{{ $report['title'] ?? 'Report on the Physical Count of Inventories' }}</div>

        @if ($pageNo > 1)
            <div class="gso-rpci-print-flow-note">Continuation from Page {{ $pageNo - 1 }}</div>
        @endif

        <table class="gso-rpci-print-sheet">
            <colgroup>
                <col style="width: 14%;">
                <col style="width: 22%;">
                <col style="width: 10%;">
                <col style="width: 6%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 6%;">
                <col style="width: 8%;">
                <col style="width: 10%;">
            </colgroup>
            <tbody>
                <tr class="gso-rpci-print-meta-row">
                    <td colspan="6"><span class="gso-rpci-print-meta-label">Entity Name:</span> {{ $document['entity_name'] ?? 'Local Government Unit' }}</td>
                    <td colspan="4"><span class="gso-rpci-print-meta-label">Fund Cluster:</span> {{ $document['fund_cluster'] ?? 'Unassigned' }}</td>
                </tr>
                <tr class="gso-rpci-print-meta-row">
                    <td colspan="6"><span class="gso-rpci-print-meta-label">Fund Source:</span> {{ $document['fund_source'] ?? 'Fund Source' }}</td>
                    <td colspan="4"><span class="gso-rpci-print-meta-label">As of:</span> {{ $document['as_of_label'] ?? '' }}</td>
                </tr>
                <tr class="gso-rpci-print-meta-row">
                    <td colspan="10"><span class="gso-rpci-print-meta-label">Type of Inventory Item:</span> {{ strtoupper((string) ($document['inventory_type'] ?? 'OFFICE SUPPLIES')) }}</td>
                </tr>
                <tr class="gso-rpci-print-meta-row">
                    <td colspan="10">
                        <span class="gso-rpci-print-meta-label">Accountability Copy:</span>
                        For which {{ $signatories['accountable_officer_name'] ?? '' }},
                        {{ $signatories['accountable_officer_designation'] ?? '' }}
                        is accountable, having assumed such accountability on {{ $assumptionDateLabel }}.
                    </td>
                </tr>

                <tr class="gso-rpci-print-column-head">
                    <th rowspan="2">Article</th>
                    <th rowspan="2">Description</th>
                    <th rowspan="2">Stock No.</th>
                    <th rowspan="2">Unit</th>
                    <th rowspan="2">Unit Value</th>
                    <th rowspan="2">Balance per Card Qty.</th>
                    <th rowspan="2">On Hand per Count Qty.</th>
                    <th colspan="2">Shortage / Overage</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr class="gso-rpci-print-column-head">
                    <th>Qty.</th>
                    <th>Value</th>
                </tr>

                @if ($pageRows === [])
                    <tr class="gso-rpci-print-data-row">
                        <td colspan="10" class="gso-rpci-print-empty-note">No inventory balances found for the selected report window.</td>
                    </tr>
                    @php $fillerRows = max(0, $fillerRows - 1); @endphp
                @else
                    @foreach ($pageRows as $row)
                        <tr class="gso-rpci-print-data-row">
                            <td>{{ $row['article'] ?? '' }}</td>
                            <td>{{ $row['description'] ?? '' }}</td>
                            <td class="gso-rpci-print-center">{{ $row['stock_no'] ?? '' }}</td>
                            <td class="gso-rpci-print-center">{{ $row['unit'] ?? '' }}</td>
                            <td class="gso-rpci-print-right">{{ number_format((float) ($row['unit_value'] ?? 0), 2) }}</td>
                            <td class="gso-rpci-print-right">{{ number_format((int) ($row['balance_per_card_qty'] ?? 0)) }}</td>
                            <td class="gso-rpci-print-right">{{ isset($row['count_qty']) ? number_format((int) $row['count_qty']) : '' }}</td>
                            <td class="gso-rpci-print-right">{{ isset($row['shortage_overage_qty']) ? number_format((int) $row['shortage_overage_qty']) : '' }}</td>
                            <td class="gso-rpci-print-right">{{ isset($row['shortage_overage_value']) ? number_format((float) $row['shortage_overage_value'], 2) : '' }}</td>
                            <td>{{ $row['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @endif

                @for ($i = 0; $i < $fillerRows; $i++)
                    <tr class="gso-rpci-print-fill-row">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor

                @if ($isLastPage)
                    <tr class="gso-rpci-print-summary-head">
                        <th colspan="2">Items Listed</th>
                        <th colspan="3">Balance Qty.</th>
                        <th colspan="2">Count Qty.</th>
                        <th colspan="2">Shortage / Overage Qty.</th>
                        <th>Book Value</th>
                    </tr>
                    <tr class="gso-rpci-print-summary-values">
                        <td colspan="2" class="gso-rpci-print-center">{{ number_format((int) ($summary['total_items'] ?? 0)) }}</td>
                        <td colspan="3" class="gso-rpci-print-center">{{ number_format((int) ($summary['total_balance_qty'] ?? 0)) }}</td>
                        <td colspan="2" class="gso-rpci-print-center">{{ isset($summary['total_count_qty']) ? number_format((int) ($summary['total_count_qty'] ?? 0)) : '' }}</td>
                        <td colspan="2" class="gso-rpci-print-center">{{ isset($summary['total_shortage_overage_qty']) ? number_format((int) ($summary['total_shortage_overage_qty'] ?? 0)) : '' }}</td>
                        <td class="gso-rpci-print-right">{{ number_format((float) ($summary['total_book_value'] ?? 0), 2) }}</td>
                    </tr>
                    <tr class="gso-rpci-print-signatures-head">
                        <th colspan="4">Certified Correct By</th>
                        <th colspan="3">Approved By</th>
                        <th colspan="3">Verified By</th>
                    </tr>
                    <tr class="gso-rpci-print-signatures-row">
                        <td colspan="4">
                            <div class="gso-rpci-print-signature-cell">
                                <div class="gso-rpci-print-signature-stack">
                                    @forelse ($committeeNames as $committeeName)
                                        <div>
                                            <div class="gso-rpci-print-signature-line">{{ $committeeName }}</div>
                                            <div class="gso-rpci-print-signature-caption">Inventory Committee</div>
                                        </div>
                                    @empty
                                        <div>
                                            <div class="gso-rpci-print-signature-line">&nbsp;</div>
                                            <div class="gso-rpci-print-signature-caption">Inventory Committee</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="gso-rpci-print-signature-cell">
                                <div class="gso-rpci-print-signature-line">{{ $signatories['approved_by_name'] ?? '' }}</div>
                                <div class="gso-rpci-print-signature-caption">{{ $signatories['approved_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="gso-rpci-print-signature-cell">
                                <div class="gso-rpci-print-signature-line">{{ $signatories['verified_by_name'] ?? '' }}</div>
                                <div class="gso-rpci-print-signature-caption">{{ $signatories['verified_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="gso-rpci-print-footer">
        <div class="gso-rpci-print-footer-content">
            @if (! $isLastPage)
                <div class="gso-rpci-print-flow-note gso-rpci-print-flow-note--footer">Continued on Page {{ $pageNo + 1 }}</div>
            @endif

            <div class="gso-rpci-print-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
        </div>

        @if (!empty($footerImage ?? null))
            <div class="gso-rpci-print-footer-image-wrap">
                <img src="{{ $footerImage }}" alt="RPCI Footer" class="gso-rpci-print-footer-image">
            </div>
        @endif
    </div>
</div>
