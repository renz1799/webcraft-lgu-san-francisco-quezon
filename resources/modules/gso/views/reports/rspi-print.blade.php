<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RSPI - {{ $report['period_label'] ?? 'Report' }}</title>
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
      title="RSPI Preview"
      copy="Set the report period, office scope, and signatories here, then review the printable RSPI on the right."
    >
      <div class="gso-report-panel-section">
        <div style="font-size:13px; font-weight:700; color:#111827;">Report Window</div>
        <div style="font-size:20px; font-weight:700; margin-top:4px;">{{ $report['period_label'] ?? 'Current Period' }}</div>
        <div class="gso-report-summary-copy">{{ $report['fund_source'] ?? 'All Fund Sources' }}</div>

        <div class="gso-report-summary-grid">
          <div class="gso-report-summary-field">
            <span>ICS Covered</span>
            <strong>{{ number_format((int) ($summary['ics_covered'] ?? 0)) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Issued Lines</span>
            <strong>{{ number_format((int) ($summary['lines_count'] ?? 0)) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Total Qty. Issued</span>
            <strong>{{ number_format((int) ($summary['total_qty_issued'] ?? 0)) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Total Cost</span>
            <strong>{{ number_format((float) ($summary['total_cost'] ?? 0), 2) }}</strong>
          </div>
        </div>
      </div>

      <form method="GET" action="{{ route('gso.reports.rspi.print') }}" class="gso-report-filter-form">
        <input type="hidden" name="preview" value="1">

        <div class="gso-report-filter-group">
          <label for="rspi-fund-source">Fund Source</label>
          <select id="rspi-fund-source" name="fund_source_id">
            <option value="">All Fund Sources</option>
            @foreach(($available_funds ?? []) as $fund)
              <option value="{{ $fund['id'] }}" @selected((string) ($fund['id'] ?? '') === (string) ($report['fund_source_id'] ?? ''))>
                {{ $fund['label'] }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="gso-report-filter-group">
          <label for="rspi-office">Office</label>
          <select id="rspi-office" name="department_id">
            <option value="">All Offices</option>
            @foreach(($available_departments ?? []) as $department)
              <option value="{{ $department['id'] }}" @selected((string) ($department['id'] ?? '') === (string) ($report['department_id'] ?? ''))>
                {{ $department['label'] }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="gso-report-filter-group">
          <label for="rspi-officer">Accountable Officer</label>
          <select id="rspi-officer" name="accountable_officer_id">
            <option value="">All Accountable Officers</option>
            @foreach(($available_accountable_officers ?? []) as $officer)
              <option value="{{ $officer['id'] }}" @selected((string) ($officer['id'] ?? '') === (string) ($report['accountable_officer_id'] ?? ''))>
                {{ $officer['label'] }}
              </option>
            @endforeach
          </select>
        </div>

        <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px;">
          <div class="gso-report-filter-group">
            <label for="rspi-date-from">Date From</label>
            <input id="rspi-date-from" type="date" name="date_from" value="{{ $report['date_from'] ?? '' }}">
          </div>
          <div class="gso-report-filter-group">
            <label for="rspi-date-to">Date To</label>
            <input id="rspi-date-to" type="date" name="date_to" value="{{ $report['date_to'] ?? '' }}">
          </div>
        </div>

        <div class="gso-report-filter-group">
          <label for="rspi-prepared-name">Prepared By</label>
          <input id="rspi-prepared-name" type="text" name="prepared_by_name" value="{{ $signatories['prepared_by_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rspi-prepared-designation">Prepared Designation</label>
          <input id="rspi-prepared-designation" type="text" name="prepared_by_designation" value="{{ $signatories['prepared_by_designation'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rspi-reviewed-name">Reviewed By</label>
          <input id="rspi-reviewed-name" type="text" name="reviewed_by_name" value="{{ $signatories['reviewed_by_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rspi-reviewed-designation">Reviewed Designation</label>
          <input id="rspi-reviewed-designation" type="text" name="reviewed_by_designation" value="{{ $signatories['reviewed_by_designation'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rspi-approved-name">Approved By</label>
          <input id="rspi-approved-name" type="text" name="approved_by_name" value="{{ $signatories['approved_by_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rspi-approved-designation">Approved Designation</label>
          <input id="rspi-approved-designation" type="text" name="approved_by_designation" value="{{ $signatories['approved_by_designation'] ?? '' }}">
        </div>

        <button type="submit" class="gso-report-button gso-report-button--primary">Apply Changes</button>
      </form>

      <div class="gso-report-action-group">
        <button type="button" class="gso-report-button gso-report-button--primary" onclick="window.print()">Print</button>
        <a href="{{ route('gso.inventory-items.index') }}" class="gso-report-button">Back to Inventory Items</a>
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
        <div class="gso-report-appendix">{{ $report['appendix_label'] ?? 'RSPI' }}</div>
        <div class="gso-report-title">Report of Semi-Expendable Property Issued</div>

        <table class="gso-report-meta-table">
          <tr>
            <td style="width:50%;">
              <span class="gso-report-meta-label">Entity Name:</span>
              {{ $report['entity_name'] ?? 'Local Government Unit' }}
            </td>
            <td style="width:50%;">
              <span class="gso-report-meta-label">Fund Cluster:</span>
              {{ $report['fund_cluster'] ?? 'All / Multiple' }}
            </td>
          </tr>
          <tr>
            <td>
              <span class="gso-report-meta-label">Fund Source:</span>
              {{ $report['fund_source'] ?? 'All Fund Sources' }}
            </td>
            <td>
              <span class="gso-report-meta-label">Period:</span>
              {{ $report['period_label'] ?? '' }}
            </td>
          </tr>
          <tr>
            <td>
              <span class="gso-report-meta-label">Office:</span>
              {{ $report['department'] ?? 'All Offices' }}
            </td>
            <td>
              <span class="gso-report-meta-label">Accountable Officer:</span>
              {{ $report['accountable_officer'] ?? 'All Accountable Officers' }}
            </td>
          </tr>
        </table>

        <table class="gso-report-items-table">
          <colgroup>
            <col style="width:8%;">
            <col style="width:12%;">
            <col style="width:10%;">
            <col style="width:12%;">
            <col style="width:19%;">
            <col style="width:6%;">
            <col style="width:8%;">
            <col style="width:10%;">
            <col style="width:10%;">
            <col style="width:5%;">
          </colgroup>
          <thead>
            <tr>
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
          </thead>
          <tbody>
            @if($pageRows->isEmpty())
              <tr>
                <td colspan="10" class="gso-report-empty-note">No semi-expendable issuance lines found for the selected report period.</td>
              </tr>
              @php $remainingRows = max(0, $remainingRows - 1); @endphp
            @else
              @foreach($pageRows as $row)
                <tr>
                  <td class="gso-report-center">{{ !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('m/d/Y') : '' }}</td>
                  <td>{{ $row['reference'] ?? '' }}</td>
                  <td class="gso-report-center">{{ $row['property_no'] ?? '' }}</td>
                  <td>{{ $row['article'] ?? '' }}</td>
                  <td>{{ $row['description'] ?? '' }}</td>
                  <td class="gso-report-right">{{ number_format((int) ($row['qty_issued'] ?? 0)) }}</td>
                  <td class="gso-report-right">{{ number_format((float) ($row['unit_cost'] ?? 0), 2) }}</td>
                  <td>{{ $row['office'] ?? '' }}</td>
                  <td>{{ $row['accountable_officer'] ?? '' }}</td>
                  <td class="gso-report-right">{{ number_format((float) ($row['total_cost'] ?? 0), 2) }}</td>
                </tr>
              @endforeach
            @endif

            @for($i = 0; $i < $remainingRows; $i++)
              <tr>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
              </tr>
            @endfor
          </tbody>
        </table>

        @if($isLastPage)
          <table class="gso-report-summary-table">
            <tr>
              <th>ICS Covered</th>
              <th>Issued Lines</th>
              <th>Total Qty. Issued</th>
              <th>Total Cost</th>
            </tr>
            <tr>
              <td class="gso-report-center">{{ number_format((int) ($summary['ics_covered'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ number_format((int) ($summary['lines_count'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ number_format((int) ($summary['total_qty_issued'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ number_format((float) ($summary['total_cost'] ?? 0), 2) }}</td>
            </tr>
          </table>

          <table class="gso-report-signature-table">
            <tr>
              <th>Prepared By</th>
              <th>Reviewed By</th>
              <th>Approved By</th>
            </tr>
            <tr>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['prepared_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['prepared_by_designation'] ?? '' }}</div>
              </td>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['reviewed_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['reviewed_by_designation'] ?? '' }}</div>
              </td>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['approved_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['approved_by_designation'] ?? '' }}</div>
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
