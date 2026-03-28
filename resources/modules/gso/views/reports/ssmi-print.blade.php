<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SSMI - {{ $report['period_label'] ?? 'Report' }}</title>
  <x-print.workspace-styles />
  @include('gso::reports.partials.print-styles')
</head>
<body class="print-workspace-body">
@php
  $summary = $report['summary'] ?? [];
  $signatories = $report['signatories'] ?? [];
  $rowsPerPage = 18;
  $pages = collect($rows ?? [])->chunk($rowsPerPage)->values();
  if ($pages->isEmpty()) {
      $pages = collect([collect()]);
  }
@endphp

<x-print.workspace
  sidebar-width="clamp(340px, calc(297mm * 0.30), 390px)"
  preview-width="min(297mm, calc(100vw - clamp(340px, calc(297mm * 0.30), 390px) - 152px))"
  gap="42px"
>
  <x-slot:sidebar>
    <x-print.panel
      kicker="Reports"
      title="SSMI Preview"
      copy="Set the fund source, reporting period, and signatories here, then review the printable SSMI on the right."
    >
      <div class="gso-report-panel-section">
        <div style="font-size:13px; font-weight:700; color:#111827;">Report Window</div>
        <div style="font-size:20px; font-weight:700; margin-top:4px;">{{ $report['period_label'] ?? 'Current Period' }}</div>
        <div class="gso-report-summary-copy">{{ $report['fund_source'] ?? 'Fund Source' }}</div>

        <div class="gso-report-summary-grid">
          <div class="gso-report-summary-field">
            <span>Fund Cluster</span>
            <strong>{{ $report['fund_cluster'] ?? 'Unassigned' }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>RIS Covered</span>
            <strong>{{ number_format((int) ($summary['total_ris'] ?? 0)) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Issued Lines</span>
            <strong>{{ number_format((int) ($summary['total_lines'] ?? 0)) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Total Cost</span>
            <strong>{{ number_format((float) ($summary['total_cost'] ?? 0), 2) }}</strong>
          </div>
        </div>
      </div>

      <form method="GET" action="{{ route('gso.stocks.ssmi.print') }}" class="gso-report-filter-form">
        <input type="hidden" name="preview" value="1">

        <div class="gso-report-filter-group">
          <label for="ssmi-fund-source">Fund Source</label>
          <select id="ssmi-fund-source" name="fund_source_id" required>
            @foreach(($available_funds ?? []) as $fund)
              <option value="{{ $fund['id'] }}" @selected((string) ($fund['id'] ?? '') === (string) ($report['fund_source_id'] ?? ''))>
                {{ $fund['label'] }}
              </option>
            @endforeach
          </select>
        </div>

        <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px;">
          <div class="gso-report-filter-group">
            <label for="ssmi-date-from">Date From</label>
            <input id="ssmi-date-from" type="date" name="date_from" value="{{ $report['period_from'] ?? '' }}">
          </div>
          <div class="gso-report-filter-group">
            <label for="ssmi-date-to">Date To</label>
            <input id="ssmi-date-to" type="date" name="date_to" value="{{ $report['period_to'] ?? '' }}">
          </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px;">
          <div class="gso-report-filter-group">
            <label for="ssmi-prepared-by-name">Prepared By</label>
            <input id="ssmi-prepared-by-name" type="text" name="prepared_by_name" value="{{ $signatories['prepared_by_name'] ?? '' }}">
          </div>
          <div class="gso-report-filter-group">
            <label for="ssmi-prepared-by-designation">Prepared By Designation</label>
            <input id="ssmi-prepared-by-designation" type="text" name="prepared_by_designation" value="{{ $signatories['prepared_by_designation'] ?? '' }}">
          </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px;">
          <div class="gso-report-filter-group">
            <label for="ssmi-prepared-by-date">Prepared By Date</label>
            <input id="ssmi-prepared-by-date" type="date" name="prepared_by_date" value="{{ $signatories['prepared_by_date'] ?? '' }}">
          </div>
          <div class="gso-report-filter-group">
            <label for="ssmi-certified-by-date">Certified By Date</label>
            <input id="ssmi-certified-by-date" type="date" name="certified_by_date" value="{{ $signatories['certified_by_date'] ?? '' }}">
          </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px;">
          <div class="gso-report-filter-group">
            <label for="ssmi-certified-by-name">Certified By</label>
            <input id="ssmi-certified-by-name" type="text" name="certified_by_name" value="{{ $signatories['certified_by_name'] ?? '' }}">
          </div>
          <div class="gso-report-filter-group">
            <label for="ssmi-certified-by-designation">Certified By Designation</label>
            <input id="ssmi-certified-by-designation" type="text" name="certified_by_designation" value="{{ $signatories['certified_by_designation'] ?? '' }}">
          </div>
        </div>

        <button type="submit" class="gso-report-button gso-report-button--primary">Apply Changes</button>
      </form>

      <div class="gso-report-action-group">
        <button type="button" class="gso-report-button gso-report-button--primary" onclick="window.print()">Print</button>
        <a href="{{ route('gso.stocks.index') }}" class="gso-report-button">Back to Stocks</a>
        <a href="javascript:window.close();" class="gso-report-button">Close Preview</a>
      </div>
    </x-print.panel>
  </x-slot:sidebar>

  @foreach($pages as $pageIndex => $pageRows)
    @php
      $pageNo = $pageIndex + 1;
      $totalPages = $pages->count();
      $remainingRows = max(0, $rowsPerPage - $pageRows->count());
      $isLastPage = $pageNo === $totalPages;
    @endphp

    <div class="gso-report-page print-page">
      <div class="gso-report-content">
        <div class="gso-report-appendix">{{ $report['appendix_label'] ?? 'SSMI' }}</div>
        <div class="gso-report-title">Summary of Supplies and Materials Issued</div>

        <table class="gso-report-meta-table">
          <tr>
            <td style="width:50%;">
              <span class="gso-report-meta-label">Entity Name:</span>
              {{ $report['entity_name'] ?? 'Local Government Unit' }}
            </td>
            <td style="width:50%;">
              <span class="gso-report-meta-label">Fund Cluster:</span>
              {{ $report['fund_cluster'] ?? 'Unassigned' }}
            </td>
          </tr>
          <tr>
            <td>
              <span class="gso-report-meta-label">Fund Source:</span>
              {{ $report['fund_source'] ?? 'Fund Source' }}
            </td>
            <td>
              <span class="gso-report-meta-label">For the Period:</span>
              {{ $report['period_label'] ?? 'Current Period' }}
            </td>
          </tr>
          <tr>
            <td>
              <span class="gso-report-meta-label">RIS Covered:</span>
              {{ number_format((int) ($summary['total_ris'] ?? 0)) }}
            </td>
            <td>
              <span class="gso-report-meta-label">Issued Qty:</span>
              {{ number_format((int) ($summary['total_qty'] ?? 0)) }}
            </td>
          </tr>
          <tr>
            <td>
              <span class="gso-report-meta-label">Line Count:</span>
              {{ number_format((int) ($summary['total_lines'] ?? 0)) }}
            </td>
            <td>
              <span class="gso-report-meta-label">Total Cost:</span>
              {{ number_format((float) ($summary['total_cost'] ?? 0), 2) }}
            </td>
          </tr>
        </table>

        <table class="gso-report-items-table">
          <colgroup>
            <col style="width:8%;">
            <col style="width:12%;">
            <col style="width:16%;">
            <col style="width:10%;">
            <col style="width:25%;">
            <col style="width:7%;">
            <col style="width:7%;">
            <col style="width:7%;">
            <col style="width:8%;">
          </colgroup>
          <thead>
            <tr>
              <th>Date</th>
              <th>RIS No.</th>
              <th>Office</th>
              <th>Stock No.</th>
              <th>Description</th>
              <th>Unit</th>
              <th>Qty. Issued</th>
              <th>Unit Cost</th>
              <th>Total Cost</th>
            </tr>
          </thead>
          <tbody>
            @if($pageRows->isEmpty())
              <tr>
                <td colspan="9" class="gso-report-empty-note">No issued RIS lines found for the selected period.</td>
              </tr>
              @php $remainingRows = max(0, $remainingRows - 1); @endphp
            @else
              @foreach($pageRows as $row)
                <tr>
                  <td class="gso-report-center">{{ $row['issue_date'] ?? '' }}</td>
                  <td class="gso-report-center">{{ $row['ris_number'] ?? '' }}</td>
                  <td>{{ $row['office'] ?? '' }}</td>
                  <td>{{ $row['stock_no'] ?? '' }}</td>
                  <td>{{ $row['description'] ?? '' }}</td>
                  <td class="gso-report-center">{{ $row['unit'] ?? '' }}</td>
                  <td class="gso-report-right">{{ number_format((int) ($row['qty_issued'] ?? 0)) }}</td>
                  <td class="gso-report-right">{{ number_format((float) ($row['unit_cost'] ?? 0), 4) }}</td>
                  <td class="gso-report-right">{{ number_format((float) ($row['total_cost'] ?? 0), 2) }}</td>
                </tr>
              @endforeach
            @endif

            @for($i = 0; $i < $remainingRows; $i++)
              <tr>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
              </tr>
            @endfor
          </tbody>
        </table>

        @if($isLastPage)
          <table class="gso-report-summary-table">
            <tr>
              <th>Total RIS</th>
              <th>Issued Lines</th>
              <th>Total Qty. Issued</th>
              <th>Total Cost</th>
            </tr>
            <tr>
              <td class="gso-report-center">{{ number_format((int) ($summary['total_ris'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ number_format((int) ($summary['total_lines'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ number_format((int) ($summary['total_qty'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ number_format((float) ($summary['total_cost'] ?? 0), 2) }}</td>
            </tr>
          </table>

          <table class="gso-report-signature-table">
            <tr>
              <th>Prepared By</th>
              <th>Certified Correct By</th>
            </tr>
            <tr>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['prepared_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['prepared_by_designation'] ?? '' }}</div>
                <div class="gso-report-signature-role">
                  Date:
                  {{ !empty($signatories['prepared_by_date']) ? \Carbon\Carbon::parse($signatories['prepared_by_date'])->format('m/d/Y') : '' }}
                </div>
              </td>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['certified_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['certified_by_designation'] ?? '' }}</div>
                <div class="gso-report-signature-role">
                  Date:
                  {{ !empty($signatories['certified_by_date']) ? \Carbon\Carbon::parse($signatories['certified_by_date'])->format('m/d/Y') : '' }}
                </div>
              </td>
            </tr>
          </table>
        @endif

        <div class="gso-report-page-number">Page {{ $pageNo }} of {{ $totalPages }}</div>
      </div>
    </div>
  @endforeach
</x-print.workspace>

<script>
  window.addEventListener('load', function () {
    const isPreview = {{ !empty($isPreview) ? 'true' : 'false' }};
    if (!isPreview) {
      window.print();
    }
  });
</script>
</body>
</html>
