@php
    $document = $report['document'] ?? [];
    $summary = $document['summary'] ?? [];
    $signatories = $document['signatories'] ?? [];
    $pageRows = $page['rows'] ?? [];
    $isLastPage = $pageNo === $totalPages;
    $usedUnits = (int) ($page['used_units'] ?? 0);
    $gridRows = max(1, (int) ($paperProfile['grid_rows'] ?? 14));
    $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
    $targetGridRows = $isLastPage && $lastPageGridRows > 0 ? $lastPageGridRows : $gridRows;
    $fillerRows = max(0, $targetGridRows - $usedUnits);
@endphp

<div class="gso-regspi-print-page">
    @if (!empty($headerImage ?? null))
        <div class="gso-regspi-print-header">
            <img src="{{ $headerImage }}" alt="RegSPI Header" class="gso-regspi-print-header-image">
        </div>
    @endif

    <div class="gso-regspi-print-page__body">
        <div class="gso-regspi-print-appendix">{{ $document['appendix_label'] ?? 'RegSPI' }}</div>
        <div class="gso-regspi-print-title">{{ $report['title'] ?? 'Register of Semi-Expendable Property Issued' }}</div>

        @if ($pageNo > 1)
            <div class="gso-regspi-print-flow-note">Continuation from Page {{ $pageNo - 1 }}</div>
        @endif

        <table class="gso-regspi-print-sheet">
            <colgroup>
                <col style="width: 8%;">
                <col style="width: 12%;">
                <col style="width: 12%;">
                <col style="width: 12%;">
                <col style="width: 20%;">
                <col style="width: 5%;">
                <col style="width: 8%;">
                <col style="width: 10%;">
                <col style="width: 8%;">
                <col style="width: 5%;">
            </colgroup>
            <tbody>
                <tr class="gso-regspi-print-meta-row">
                    <td colspan="6"><span class="gso-regspi-print-meta-label">Entity Name:</span> {{ $document['entity_name'] ?? 'Local Government Unit' }}</td>
                    <td colspan="4"><span class="gso-regspi-print-meta-label">Fund Cluster:</span> {{ $document['fund_cluster'] ?? 'All / Multiple' }}</td>
                </tr>
                <tr class="gso-regspi-print-meta-row">
                    <td colspan="6"><span class="gso-regspi-print-meta-label">Fund Source:</span> {{ $document['fund_source'] ?? 'All Fund Sources' }}</td>
                    <td colspan="4"><span class="gso-regspi-print-meta-label">As of:</span> {{ $document['as_of_label'] ?? '' }}</td>
                </tr>
                <tr class="gso-regspi-print-meta-row">
                    <td colspan="6"><span class="gso-regspi-print-meta-label">Office:</span> {{ $document['department'] ?? 'All Offices' }}</td>
                    <td colspan="4"><span class="gso-regspi-print-meta-label">Accountable Officer:</span> {{ $document['accountable_officer'] ?? 'All Accountable Officers' }}</td>
                </tr>

                <tr class="gso-regspi-print-column-head">
                    <th>Date</th>
                    <th>Reference</th>
                    <th>SE Property No.</th>
                    <th>Property</th>
                    <th>Description</th>
                    <th>Qty.</th>
                    <th>Unit Value</th>
                    <th>Office</th>
                    <th>Officer</th>
                    <th>Remarks</th>
                </tr>

                @if ($pageRows === [])
                    <tr class="gso-regspi-print-data-row">
                        <td colspan="10" class="gso-regspi-print-empty-note">No issued semi-expendable items found for the selected register scope.</td>
                    </tr>
                    @php $fillerRows = max(0, $fillerRows - 1); @endphp
                @else
                    @foreach ($pageRows as $row)
                        <tr class="gso-regspi-print-data-row">
                            <td class="gso-regspi-print-center">{{ !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('m/d/Y') : '' }}</td>
                            <td>{{ $row['reference'] ?? '' }}</td>
                            <td class="gso-regspi-print-center">{{ $row['property_no'] ?? '' }}</td>
                            <td>{{ $row['article'] ?? '' }}</td>
                            <td>{{ $row['description'] ?? '' }}</td>
                            <td class="gso-regspi-print-right">{{ number_format((int) ($row['qty'] ?? 0)) }}</td>
                            <td class="gso-regspi-print-right">{{ number_format((float) ($row['unit_value'] ?? 0), 2) }}</td>
                            <td>{{ $row['office'] ?? '' }}</td>
                            <td>{{ $row['accountable_officer'] ?? '' }}</td>
                            <td>{{ $row['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @endif

                @for ($i = 0; $i < $fillerRows; $i++)
                    <tr class="gso-regspi-print-fill-row">
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor

                @if ($isLastPage)
                    <tr class="gso-regspi-print-summary-head">
                        <th colspan="2">Items Registered</th>
                        <th colspan="2">Total Qty.</th>
                        <th colspan="2">Offices Covered</th>
                        <th colspan="2">Officers Covered</th>
                        <th colspan="2">Total Value</th>
                    </tr>
                    <tr class="gso-regspi-print-summary-values">
                        <td colspan="2">{{ number_format((int) ($summary['total_items'] ?? 0)) }}</td>
                        <td colspan="2">{{ number_format((int) ($summary['total_qty'] ?? 0)) }}</td>
                        <td colspan="2">{{ number_format((int) ($summary['offices_covered'] ?? 0)) }}</td>
                        <td colspan="2">{{ number_format((int) ($summary['accountable_officers_covered'] ?? 0)) }}</td>
                        <td colspan="2" class="gso-regspi-print-right">{{ number_format((float) ($summary['total_value'] ?? 0), 2) }}</td>
                    </tr>
                    <tr class="gso-regspi-print-signatures-head">
                        <th colspan="3">Prepared By</th>
                        <th colspan="3">Reviewed By</th>
                        <th colspan="4">Approved By</th>
                    </tr>
                    <tr class="gso-regspi-print-signatures-row">
                        <td colspan="3">
                            <div class="gso-regspi-print-signature-cell">
                                <div class="gso-regspi-print-signature-line">{{ $signatories['prepared_by_name'] ?? '' }}</div>
                                <div class="gso-regspi-print-signature-caption">{{ $signatories['prepared_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="gso-regspi-print-signature-cell">
                                <div class="gso-regspi-print-signature-line">{{ $signatories['reviewed_by_name'] ?? '' }}</div>
                                <div class="gso-regspi-print-signature-caption">{{ $signatories['reviewed_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="4">
                            <div class="gso-regspi-print-signature-cell">
                                <div class="gso-regspi-print-signature-line">{{ $signatories['approved_by_name'] ?? '' }}</div>
                                <div class="gso-regspi-print-signature-caption">{{ $signatories['approved_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="gso-regspi-print-footer">
        <div class="gso-regspi-print-footer-content">
            @if (! $isLastPage)
                <div class="gso-regspi-print-flow-note gso-regspi-print-flow-note--footer">Continued on Page {{ $pageNo + 1 }}</div>
            @endif

            <div class="gso-regspi-print-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
        </div>

        @if (!empty($footerImage ?? null))
            <div class="gso-regspi-print-footer-image-wrap">
                <img src="{{ $footerImage }}" alt="RegSPI Footer" class="gso-regspi-print-footer-image">
            </div>
        @endif
    </div>
</div>
