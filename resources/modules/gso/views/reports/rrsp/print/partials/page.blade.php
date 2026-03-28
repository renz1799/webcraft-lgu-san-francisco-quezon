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

<div class="gso-rrsp-print-page">
    @if (!empty($headerImage ?? null))
        <div class="gso-rrsp-print-header">
            <img src="{{ $headerImage }}" alt="RRSP Header" class="gso-rrsp-print-header-image">
        </div>
    @endif

    <div class="gso-rrsp-print-page__body">
        <div class="gso-rrsp-print-appendix">{{ $document['appendix_label'] ?? 'RRSP' }}</div>
        <div class="gso-rrsp-print-title">{{ $report['title'] ?? 'Receipt of Returned Semi-Expendable Property' }}</div>

        @if ($pageNo > 1)
            <div class="gso-rrsp-print-flow-note">Continuation from Page {{ $pageNo - 1 }}</div>
        @endif

        <table class="gso-rrsp-print-sheet">
            <colgroup>
                <col style="width: 10%;">
                <col style="width: 12%;">
                <col style="width: 22%;">
                <col style="width: 6%;">
                <col style="width: 7%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 9%;">
                <col style="width: 9%;">
                <col style="width: 9%;">
            </colgroup>
            <tbody>
                <tr class="gso-rrsp-print-meta-row">
                    <td colspan="6"><span class="gso-rrsp-print-meta-label">Entity Name:</span> {{ $document['entity_name'] ?? 'Local Government Unit' }}</td>
                    <td colspan="4"><span class="gso-rrsp-print-meta-label">Fund Cluster:</span> {{ $document['fund_cluster'] ?? 'All / Multiple' }}</td>
                </tr>
                <tr class="gso-rrsp-print-meta-row">
                    <td colspan="6"><span class="gso-rrsp-print-meta-label">Fund Source:</span> {{ $document['fund_source'] ?? 'All Fund Sources' }}</td>
                    <td colspan="4"><span class="gso-rrsp-print-meta-label">Return Date:</span> {{ $document['return_date_label'] ?? '' }}</td>
                </tr>
                <tr class="gso-rrsp-print-meta-row">
                    <td colspan="6"><span class="gso-rrsp-print-meta-label">Office:</span> {{ $document['department'] ?? 'All Offices' }}</td>
                    <td colspan="4"><span class="gso-rrsp-print-meta-label">Accountable Officer:</span> {{ $document['accountable_officer'] ?? 'All Accountable Officers' }}</td>
                </tr>

                <tr class="gso-rrsp-print-column-head">
                    <th>SE Property No.</th>
                    <th>Property</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Qty. Returned</th>
                    <th>Unit Value</th>
                    <th>Total Value</th>
                    <th>Condition</th>
                    <th>Office</th>
                    <th>Officer / Remarks</th>
                </tr>

                @if ($pageRows === [])
                    <tr class="gso-rrsp-print-data-row">
                        <td colspan="10" class="gso-rrsp-print-empty-note">No eligible semi-expendable return lines found for the selected receipt scope.</td>
                    </tr>
                    @php $fillerRows = max(0, $fillerRows - 1); @endphp
                @else
                    @foreach ($pageRows as $row)
                        <tr class="gso-rrsp-print-data-row">
                            <td class="gso-rrsp-print-center">{{ $row['property_no'] ?? '' }}</td>
                            <td>{{ $row['article'] ?? '' }}</td>
                            <td>{{ $row['description'] ?? '' }}</td>
                            <td class="gso-rrsp-print-center">{{ $row['unit'] ?? '' }}</td>
                            <td class="gso-rrsp-print-right">{{ number_format((int) ($row['qty_returned'] ?? 0)) }}</td>
                            <td class="gso-rrsp-print-right">{{ number_format((float) ($row['unit_value'] ?? 0), 2) }}</td>
                            <td class="gso-rrsp-print-right">{{ number_format((float) ($row['total_value'] ?? 0), 2) }}</td>
                            <td>{{ $row['condition'] ?? '' }}</td>
                            <td>{{ $row['office'] ?? '' }}</td>
                            <td>
                                {{ $row['accountable_officer'] ?? '' }}
                                @if (!empty($row['remarks']))
                                    <div class="gso-rrsp-print-subtext">{{ $row['remarks'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif

                @for ($i = 0; $i < $fillerRows; $i++)
                    <tr class="gso-rrsp-print-fill-row">
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor

                @if ($isLastPage)
                    <tr class="gso-rrsp-print-summary-head">
                        <th colspan="3">Items Listed</th>
                        <th colspan="3">Qty. Returned</th>
                        <th colspan="4">Total Value</th>
                    </tr>
                    <tr class="gso-rrsp-print-summary-values">
                        <td colspan="3">{{ number_format((int) ($summary['items_listed'] ?? 0)) }}</td>
                        <td colspan="3">{{ number_format((int) ($summary['total_qty_returned'] ?? 0)) }}</td>
                        <td colspan="4" class="gso-rrsp-print-right">{{ number_format((float) ($summary['total_value'] ?? 0), 2) }}</td>
                    </tr>
                    <tr class="gso-rrsp-print-signatures-head">
                        <th colspan="3">Returned By</th>
                        <th colspan="3">Received By</th>
                        <th colspan="4">Noted By</th>
                    </tr>
                    <tr class="gso-rrsp-print-signatures-row">
                        <td colspan="3">
                            <div class="gso-rrsp-print-signature-cell">
                                <div class="gso-rrsp-print-signature-line">{{ $signatories['returned_by_name'] ?? '' }}</div>
                                <div class="gso-rrsp-print-signature-caption">{{ $signatories['returned_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="3">
                            <div class="gso-rrsp-print-signature-cell">
                                <div class="gso-rrsp-print-signature-line">{{ $signatories['received_by_name'] ?? '' }}</div>
                                <div class="gso-rrsp-print-signature-caption">{{ $signatories['received_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                        <td colspan="4">
                            <div class="gso-rrsp-print-signature-cell">
                                <div class="gso-rrsp-print-signature-line">{{ $signatories['noted_by_name'] ?? '' }}</div>
                                <div class="gso-rrsp-print-signature-caption">{{ $signatories['noted_by_designation'] ?? '' }}</div>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="gso-rrsp-print-footer">
        <div class="gso-rrsp-print-footer-content">
            @if (! $isLastPage)
                <div class="gso-rrsp-print-flow-note gso-rrsp-print-flow-note--footer">Continued on Page {{ $pageNo + 1 }}</div>
            @endif

            <div class="gso-rrsp-print-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
        </div>

        @if (!empty($footerImage ?? null))
            <div class="gso-rrsp-print-footer-image-wrap">
                <img src="{{ $footerImage }}" alt="RRSP Footer" class="gso-rrsp-print-footer-image">
            </div>
        @endif
    </div>
</div>
