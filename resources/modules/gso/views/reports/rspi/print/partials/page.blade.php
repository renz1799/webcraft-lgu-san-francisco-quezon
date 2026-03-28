@php
    $document = $report['document'] ?? [];
    $summary = $document['summary'] ?? [];
    $signatories = $document['signatories'] ?? [];
    $pageRows = $page['rows'] ?? [];
    $isLastPage = $pageNo === $totalPages;
    $usedUnits = (int) ($page['used_units'] ?? 0);
    $gridRows = max(1, (int) ($paperProfile['grid_rows'] ?? 15));
    $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
    $targetGridRows = $isLastPage && $lastPageGridRows > 0 ? $lastPageGridRows : $gridRows;
    $fillerRows = max(0, $targetGridRows - $usedUnits);
@endphp

<div class="gso-rspi-print-page">
    @if (!empty($headerImage ?? null))
        <div class="gso-rspi-print-header">
            <img src="{{ $headerImage }}" alt="RSPI Header" class="gso-rspi-print-header-image">
        </div>
    @endif

    <div class="gso-rspi-print-page__body">
        <div class="gso-rspi-print-appendix">{{ $document['appendix_label'] ?? 'RSPI' }}</div>
        <div class="gso-rspi-print-title">{{ $report['title'] ?? 'Report of Semi-Expendable Property Issued' }}</div>

        @if ($pageNo > 1)
            <div class="gso-rspi-print-flow-note">Continuation from Page {{ $pageNo - 1 }}</div>
        @endif

        <table class="gso-rspi-print-sheet">
            <colgroup>
                <col style="width: 8%;">
                <col style="width: 12%;">
                <col style="width: 10%;">
                <col style="width: 12%;">
                <col style="width: 20%;">
                <col style="width: 6%;">
                <col style="width: 8%;">
                <col style="width: 10%;">
                <col style="width: 9%;">
                <col style="width: 5%;">
            </colgroup>
            <tbody>
                <tr class="gso-rspi-print-meta-row">
                    <td colspan="6"><span class="gso-rspi-print-meta-label">Entity Name:</span> {{ $document['entity_name'] ?? 'Local Government Unit' }}</td>
                    <td colspan="4"><span class="gso-rspi-print-meta-label">Fund Cluster:</span> {{ $document['fund_cluster'] ?? 'All / Multiple' }}</td>
                </tr>
                <tr class="gso-rspi-print-meta-row">
                    <td colspan="6"><span class="gso-rspi-print-meta-label">Fund Source:</span> {{ $document['fund_source'] ?? 'All Fund Sources' }}</td>
                    <td colspan="4"><span class="gso-rspi-print-meta-label">Period:</span> {{ $document['period_label'] ?? '' }}</td>
                </tr>
                <tr class="gso-rspi-print-meta-row">
                    <td colspan="6"><span class="gso-rspi-print-meta-label">Office:</span> {{ $document['department'] ?? 'All Offices' }}</td>
                    <td colspan="4"><span class="gso-rspi-print-meta-label">Accountable Officer:</span> {{ $document['accountable_officer'] ?? 'All Accountable Officers' }}</td>
                </tr>

                <tr class="gso-rspi-print-column-head">
                    <th>Date</th>
                    <th>Reference</th>
                    <th>SE Property No.</th>
                    <th>Property</th>
                    <th>Description</th>
                    <th>Qty.</th>
                    <th>Unit Cost</th>
                    <th>Office</th>
                    <th>Officer</th>
                    <th>Total Cost</th>
                </tr>

                @if ($pageRows === [])
                    <tr class="gso-rspi-print-data-row">
                        <td colspan="10" class="gso-rspi-print-empty-note">No semi-expendable issuance lines found for the selected report period.</td>
                    </tr>
                    @php $fillerRows = max(0, $fillerRows - 1); @endphp
                @else
                    @foreach ($pageRows as $row)
                        <tr class="gso-rspi-print-data-row">
                            <td class="gso-rspi-print-center">{{ !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('m/d/Y') : '' }}</td>
                            <td>{{ $row['reference'] ?? '' }}</td>
                            <td class="gso-rspi-print-center">{{ $row['property_no'] ?? '' }}</td>
                            <td>{{ $row['article'] ?? '' }}</td>
                            <td>{{ $row['description'] ?? '' }}</td>
                            <td class="gso-rspi-print-right">{{ number_format((int) ($row['qty_issued'] ?? 0)) }}</td>
                            <td class="gso-rspi-print-right">{{ number_format((float) ($row['unit_cost'] ?? 0), 2) }}</td>
                            <td>{{ $row['office'] ?? '' }}</td>
                            <td>{{ $row['accountable_officer'] ?? '' }}</td>
                            <td class="gso-rspi-print-right">{{ number_format((float) ($row['total_cost'] ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                @endif

                @for ($i = 0; $i < $fillerRows; $i++)
                    <tr class="gso-rspi-print-fill-row">
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor

                @if ($isLastPage)
                    <tr class="gso-rspi-print-summary-head">
                        <th colspan="2">ICS Covered</th>
                        <th colspan="3">Issued Lines</th>
                        <th colspan="2">Total Qty. Issued</th>
                        <th colspan="3">Total Cost</th>
                    </tr>
                    <tr class="gso-rspi-print-summary-values">
                        <td colspan="2">{{ number_format((int) ($summary['ics_covered'] ?? 0)) }}</td>
                        <td colspan="3">{{ number_format((int) ($summary['lines_count'] ?? 0)) }}</td>
                        <td colspan="2">{{ number_format((int) ($summary['total_qty_issued'] ?? 0)) }}</td>
                        <td colspan="3" class="gso-rspi-print-right">{{ number_format((float) ($summary['total_cost'] ?? 0), 2) }}</td>
                    </tr>
                    <tr class="gso-rspi-print-signatures-head">
                        <th colspan="3">Prepared By</th>
                        <th colspan="3">Reviewed By</th>
                        <th colspan="4">Approved By</th>
                    </tr>
                    <tr class="gso-rspi-print-signatures-row">
                        <td colspan="3">
                            <div class="gso-rspi-print-signature-cell">
                                <div class="gso-rspi-print-signature-line">{{ $signatories['prepared_by_name'] ?? '' }}</div>
                                <div class="gso-rspi-print-signature-caption">{{ $signatories['prepared_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="gso-rspi-print-signature-cell">
                                <div class="gso-rspi-print-signature-line">{{ $signatories['reviewed_by_name'] ?? '' }}</div>
                                <div class="gso-rspi-print-signature-caption">{{ $signatories['reviewed_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="4">
                            <div class="gso-rspi-print-signature-cell">
                                <div class="gso-rspi-print-signature-line">{{ $signatories['approved_by_name'] ?? '' }}</div>
                                <div class="gso-rspi-print-signature-caption">{{ $signatories['approved_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="gso-rspi-print-footer">
        <div class="gso-rspi-print-footer-content">
            @if (! $isLastPage)
                <div class="gso-rspi-print-flow-note gso-rspi-print-flow-note--footer">Continued on Page {{ $pageNo + 1 }}</div>
            @endif

            <div class="gso-rspi-print-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
        </div>

        @if (!empty($footerImage ?? null))
            <div class="gso-rspi-print-footer-image-wrap">
                <img src="{{ $footerImage }}" alt="RSPI Footer" class="gso-rspi-print-footer-image">
            </div>
        @endif
    </div>
</div>
