<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RRSP - {{ $report['return_date_label'] ?? 'Receipt' }}</title>
  <x-print.workspace-styles />
  @include('gso::reports.partials.print-styles')
</head>
<body class="print-workspace-body">
@php
  $summary = $report['summary'] ?? [];
  $signatories = $report['signatories'] ?? [];
  $rowsPerPage = 16;
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
      title="RRSP Preview"
      copy="Set the return date, office scope, and signatories here, then review the printable RRSP on the right."
    >
      <div class="gso-report-panel-section">
        <div style="font-size:13px; font-weight:700; color:#111827;">Receipt Date</div>
        <div style="font-size:20px; font-weight:700; margin-top:4px;">{{ $report['return_date_label'] ?? 'Current Date' }}</div>
        <div class="gso-report-summary-copy">{{ $report['fund_source'] ?? 'All Fund Sources' }}</div>

        <div class="gso-report-summary-grid">
          <div class="gso-report-summary-field">
            <span>Items Listed</span>
            <strong>{{ number_format((int) ($summary['items_listed'] ?? 0)) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Qty. Returned</span>
            <strong>{{ number_format((int) ($summary['total_qty_returned'] ?? 0)) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Total Value</span>
            <strong>{{ number_format((float) ($summary['total_value'] ?? 0), 2) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Accountable Officer</span>
            <strong>{{ $report['accountable_officer'] ?? 'All Accountable Officers' }}</strong>
          </div>
        </div>
      </div>

      <form method="GET" action="{{ route('gso.reports.rrsp.print') }}" class="gso-report-filter-form">
        <input type="hidden" name="preview" value="1">

        <div class="gso-report-filter-group">
          <label for="rrsp-fund-source">Fund Source</label>
          <select id="rrsp-fund-source" name="fund_source_id">
            <option value="">All Fund Sources</option>
            @foreach(($available_funds ?? []) as $fund)
              <option value="{{ $fund['id'] }}" @selected((string) ($fund['id'] ?? '') === (string) ($report['fund_source_id'] ?? ''))>
                {{ $fund['label'] }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="gso-report-filter-group">
          <label for="rrsp-office">Office</label>
          <select id="rrsp-office" name="department_id">
            <option value="">All Offices</option>
            @foreach(($available_departments ?? []) as $department)
              <option value="{{ $department['id'] }}" @selected((string) ($department['id'] ?? '') === (string) ($report['department_id'] ?? ''))>
                {{ $department['label'] }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="gso-report-filter-group">
          <label for="rrsp-officer">Accountable Officer</label>
          <select id="rrsp-officer" name="accountable_officer_id">
            <option value="">All Accountable Officers</option>
            @foreach(($available_accountable_officers ?? []) as $officer)
              <option value="{{ $officer['id'] }}" @selected((string) ($officer['id'] ?? '') === (string) ($report['accountable_officer_id'] ?? ''))>
                {{ $officer['label'] }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="gso-report-filter-group">
          <label for="rrsp-return-date">Return Date</label>
          <input id="rrsp-return-date" type="date" name="return_date" value="{{ $report['return_date'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rrsp-returned-name">Returned By</label>
          <input id="rrsp-returned-name" type="text" name="returned_by_name" value="{{ $signatories['returned_by_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rrsp-returned-designation">Returned Designation</label>
          <input id="rrsp-returned-designation" type="text" name="returned_by_designation" value="{{ $signatories['returned_by_designation'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rrsp-received-name">Received By</label>
          <input id="rrsp-received-name" type="text" name="received_by_name" value="{{ $signatories['received_by_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rrsp-received-designation">Received Designation</label>
          <input id="rrsp-received-designation" type="text" name="received_by_designation" value="{{ $signatories['received_by_designation'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rrsp-noted-name">Noted By</label>
          <input id="rrsp-noted-name" type="text" name="noted_by_name" value="{{ $signatories['noted_by_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rrsp-noted-designation">Noted Designation</label>
          <input id="rrsp-noted-designation" type="text" name="noted_by_designation" value="{{ $signatories['noted_by_designation'] ?? '' }}">
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
        <div class="gso-report-appendix">{{ $report['appendix_label'] ?? 'RRSP' }}</div>
        <div class="gso-report-title">Receipt of Returned Semi-Expendable Property</div>

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
              <span class="gso-report-meta-label">Return Date:</span>
              {{ $report['return_date_label'] ?? '' }}
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
            <col style="width:10%;">
            <col style="width:12%;">
            <col style="width:18%;">
            <col style="width:6%;">
            <col style="width:6%;">
            <col style="width:8%;">
            <col style="width:8%;">
            <col style="width:10%;">
            <col style="width:10%;">
            <col style="width:12%;">
          </colgroup>
          <thead>
            <tr>
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
          </thead>
          <tbody>
            @if($pageRows->isEmpty())
              <tr>
                <td colspan="10" class="gso-report-empty-note">No eligible semi-expendable items found for the selected receipt scope.</td>
              </tr>
              @php $remainingRows = max(0, $remainingRows - 1); @endphp
            @else
              @foreach($pageRows as $row)
                <tr>
                  <td class="gso-report-center">{{ $row['property_no'] ?? '' }}</td>
                  <td>{{ $row['article'] ?? '' }}</td>
                  <td>{{ $row['description'] ?? '' }}</td>
                  <td class="gso-report-center">{{ $row['unit'] ?? '' }}</td>
                  <td class="gso-report-right">{{ number_format((int) ($row['qty_returned'] ?? 0)) }}</td>
                  <td class="gso-report-right">{{ number_format((float) ($row['unit_value'] ?? 0), 2) }}</td>
                  <td class="gso-report-right">{{ number_format((float) ($row['total_value'] ?? 0), 2) }}</td>
                  <td>{{ $row['condition'] ?? '' }}</td>
                  <td>{{ $row['office'] ?? '' }}</td>
                  <td>
                    {{ $row['accountable_officer'] ?? '' }}
                    @if(!empty($row['remarks']))
                      <div>{{ $row['remarks'] }}</div>
                    @endif
                  </td>
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
              <th>Items Listed</th>
              <th>Qty. Returned</th>
              <th>Total Value</th>
            </tr>
            <tr>
              <td class="gso-report-center">{{ number_format((int) ($summary['items_listed'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ number_format((int) ($summary['total_qty_returned'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ number_format((float) ($summary['total_value'] ?? 0), 2) }}</td>
            </tr>
          </table>

          <table class="gso-report-signature-table">
            <tr>
              <th>Returned By</th>
              <th>Received By</th>
              <th>Noted By</th>
            </tr>
            <tr>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['returned_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['returned_by_designation'] ?? '' }}</div>
              </td>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['received_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['received_by_designation'] ?? '' }}</div>
              </td>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['noted_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['noted_by_designation'] ?? '' }}</div>
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
