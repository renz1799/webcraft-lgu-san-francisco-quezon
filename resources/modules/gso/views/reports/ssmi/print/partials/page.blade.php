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

<div class="gso-ssmi-print-page">
    @if (!empty($headerImage ?? null))
        <div class="gso-ssmi-print-header">
            <img src="{{ $headerImage }}" alt="SSMI Header" class="gso-ssmi-print-header-image">
        </div>
    @endif

    <div class="gso-ssmi-print-page__body">
        <div class="gso-ssmi-print-appendix">{{ $document['appendix_label'] ?? 'SSMI' }}</div>
        <div class="gso-ssmi-print-title">{{ $report['title'] ?? 'Summary of Supplies and Materials Issued' }}</div>

        @if ($pageNo > 1)
            <div class="gso-ssmi-print-flow-note">Continuation from Page {{ $pageNo - 1 }}</div>
        @endif

        <table class="gso-ssmi-print-sheet">
            <colgroup>
                <col style="width: 9%;">
                <col style="width: 12%;">
                <col style="width: 17%;">
                <col style="width: 11%;">
                <col style="width: 26%;">
                <col style="width: 6%;">
                <col style="width: 6%;">
                <col style="width: 6%;">
                <col style="width: 7%;">
            </colgroup>
            <tbody>
                <tr class="gso-ssmi-print-meta-row">
                    <td colspan="5"><span class="gso-ssmi-print-meta-label">Entity Name:</span> {{ $document['entity_name'] ?? 'Local Government Unit' }}</td>
                    <td colspan="4"><span class="gso-ssmi-print-meta-label">Fund Cluster:</span> {{ $document['fund_cluster'] ?? 'Unassigned' }}</td>
                </tr>
                <tr class="gso-ssmi-print-meta-row">
                    <td colspan="5"><span class="gso-ssmi-print-meta-label">Fund Source:</span> {{ $document['fund_source'] ?? 'Fund Source' }}</td>
                    <td colspan="4"><span class="gso-ssmi-print-meta-label">Period:</span> {{ $document['period_label'] ?? '' }}</td>
                </tr>
                <tr class="gso-ssmi-print-meta-row">
                    <td colspan="3"><span class="gso-ssmi-print-meta-label">RIS Covered:</span> {{ number_format((int) ($summary['total_ris'] ?? 0)) }}</td>
                    <td colspan="3"><span class="gso-ssmi-print-meta-label">Issued Qty:</span> {{ number_format((int) ($summary['total_qty'] ?? 0)) }}</td>
                    <td colspan="3"><span class="gso-ssmi-print-meta-label">Line Count:</span> {{ number_format((int) ($summary['total_lines'] ?? 0)) }}</td>
                </tr>

                <tr class="gso-ssmi-print-column-head">
                    <th>Date</th>
                    <th>RIS No.</th>
                    <th>Office</th>
                    <th>Stock No.</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Qty.</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                </tr>

                @if ($pageRows === [])
                    <tr class="gso-ssmi-print-data-row">
                        <td colspan="9" class="gso-ssmi-print-empty-note">No issued RIS lines found for the selected period.</td>
                    </tr>
                    @php $fillerRows = max(0, $fillerRows - 1); @endphp
                @else
                    @foreach ($pageRows as $row)
                        <tr class="gso-ssmi-print-data-row">
                            <td class="gso-ssmi-print-center">{{ $row['issue_date'] ?? '' }}</td>
                            <td class="gso-ssmi-print-center">{{ $row['ris_number'] ?? '' }}</td>
                            <td>{{ $row['office'] ?? '' }}</td>
                            <td>{{ $row['stock_no'] ?? '' }}</td>
                            <td>{{ $row['description'] ?? '' }}</td>
                            <td class="gso-ssmi-print-center">{{ $row['unit'] ?? '' }}</td>
                            <td class="gso-ssmi-print-right">{{ number_format((int) ($row['qty_issued'] ?? 0)) }}</td>
                            <td class="gso-ssmi-print-right">{{ number_format((float) ($row['unit_cost'] ?? 0), 4) }}</td>
                            <td class="gso-ssmi-print-right">{{ number_format((float) ($row['total_cost'] ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                @endif

                @for ($i = 0; $i < $fillerRows; $i++)
                    <tr class="gso-ssmi-print-fill-row">
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor

                @if ($isLastPage)
                    <tr class="gso-ssmi-print-summary-head">
                        <th colspan="2">Total RIS</th>
                        <th colspan="2">Issued Lines</th>
                        <th colspan="2">Total Qty. Issued</th>
                        <th colspan="3">Total Cost</th>
                    </tr>
                    <tr class="gso-ssmi-print-summary-values">
                        <td colspan="2">{{ number_format((int) ($summary['total_ris'] ?? 0)) }}</td>
                        <td colspan="2">{{ number_format((int) ($summary['total_lines'] ?? 0)) }}</td>
                        <td colspan="2">{{ number_format((int) ($summary['total_qty'] ?? 0)) }}</td>
                        <td colspan="3" class="gso-ssmi-print-right">{{ number_format((float) ($summary['total_cost'] ?? 0), 2) }}</td>
                    </tr>
                    <tr class="gso-ssmi-print-signatures-head">
                        <th colspan="4">Prepared By</th>
                        <th colspan="5">Certified Correct By</th>
                    </tr>
                    <tr class="gso-ssmi-print-signatures-row">
                        <td colspan="4">
                            <div class="gso-ssmi-print-signature-cell">
                                <div class="gso-ssmi-print-signature-line">{{ $signatories['prepared_by_name'] ?? '' }}</div>
                                <div class="gso-ssmi-print-signature-caption">{{ $signatories['prepared_by_designation'] ?? '' }}</div>
                                <div class="gso-ssmi-print-signature-meta">Date: {{ !empty($signatories['prepared_by_date']) ? \Carbon\Carbon::parse($signatories['prepared_by_date'])->format('m/d/Y') : '' }}</div>
                            </div>
                        </td>
                        <td colspan="5">
                            <div class="gso-ssmi-print-signature-cell">
                                <div class="gso-ssmi-print-signature-line">{{ $signatories['certified_by_name'] ?? '' }}</div>
                                <div class="gso-ssmi-print-signature-caption">{{ $signatories['certified_by_designation'] ?? '' }}</div>
                                <div class="gso-ssmi-print-signature-meta">Date: {{ !empty($signatories['certified_by_date']) ? \Carbon\Carbon::parse($signatories['certified_by_date'])->format('m/d/Y') : '' }}</div>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="gso-ssmi-print-footer">
        <div class="gso-ssmi-print-footer-content">
            @if (! $isLastPage)
                <div class="gso-ssmi-print-flow-note gso-ssmi-print-flow-note--footer">Continued on Page {{ $pageNo + 1 }}</div>
            @endif

            <div class="gso-ssmi-print-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
        </div>

        @if (!empty($footerImage ?? null))
            <div class="gso-ssmi-print-footer-image-wrap">
                <img src="{{ $footerImage }}" alt="SSMI Footer" class="gso-ssmi-print-footer-image">
            </div>
        @endif
    </div>
</div>
