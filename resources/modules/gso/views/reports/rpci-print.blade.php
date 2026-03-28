<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RPCI - {{ $report['as_of_label'] ?? 'Report' }}</title>
  <x-print.workspace-styles />
  @include('gso::reports.partials.print-styles', ['pageSize' => 'A4 landscape'])
</head>
<body class="print-workspace-body">
@php
  $summary = $report['summary'] ?? [];
  $signatories = $report['signatories'] ?? [];
  $committeeNames = array_values(array_filter([
      $signatories['committee_chair_name'] ?? '',
      $signatories['committee_member_1_name'] ?? '',
      $signatories['committee_member_2_name'] ?? '',
  ], fn ($value) => trim((string) $value) !== ''));
  $rowsPerPage = 16;
  $pages = collect($rows ?? [])->chunk($rowsPerPage)->values();
  if ($pages->isEmpty()) {
      $pages = collect([collect()]);
  }
  $assumptionDate = !empty($signatories['date_of_assumption'])
      ? \Carbon\Carbon::parse($signatories['date_of_assumption'])->format('F j, Y')
      : '';
@endphp

<x-print.workspace
  sidebar-width="clamp(330px, calc(297mm * 0.29), 380px)"
  preview-width="min(290mm, calc(100vw - clamp(330px, calc(297mm * 0.29), 380px) - 152px))"
  gap="48px"
>
  <x-slot:sidebar>
    <x-print.panel
      kicker="Reports"
      title="RPCI Preview"
      copy="Set the fund source, cutoff date, and signatories here, then review the printable RPCI on the right."
    >
      <div class="gso-report-panel-section">
        <div style="font-size:13px; font-weight:700; color:#111827;">Report Window</div>
        <div style="font-size:20px; font-weight:700; margin-top:4px;">{{ $report['as_of_label'] ?? 'Current Date' }}</div>
        <div class="gso-report-summary-copy">{{ $report['fund_source'] ?? 'Fund Source' }}</div>

        <div class="gso-report-summary-grid">
          <div class="gso-report-summary-field">
            <span>Inventory Type</span>
            <strong>{{ $report['inventory_type'] ?? 'Office Supplies' }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Items Listed</span>
            <strong>{{ number_format((int) ($summary['total_items'] ?? 0)) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Balance Qty</span>
            <strong>{{ number_format((int) ($summary['total_balance_qty'] ?? 0)) }}</strong>
          </div>
          <div class="gso-report-summary-field">
            <span>Book Value</span>
            <strong>{{ number_format((float) ($summary['total_book_value'] ?? 0), 2) }}</strong>
          </div>
        </div>
      </div>

      <form method="GET" action="{{ route('gso.stocks.rpci.print') }}" class="gso-report-filter-form">
        <input type="hidden" name="preview" value="1">
        <input type="hidden" name="prefill_count" value="0">

        <div class="gso-report-filter-group">
          <label for="rpci-fund-source">Fund Source</label>
          <select id="rpci-fund-source" name="fund_source_id" required>
            @foreach(($available_funds ?? []) as $fund)
              <option value="{{ $fund['id'] }}" @selected((string) ($fund['id'] ?? '') === (string) ($report['fund_source_id'] ?? ''))>
                {{ $fund['label'] }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-as-of">As of Date</label>
          <input id="rpci-as-of" type="date" name="as_of" value="{{ $report['as_of'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-inventory-type">Inventory Type</label>
          <input id="rpci-inventory-type" type="text" name="inventory_type" value="{{ $report['inventory_type'] ?? '' }}">
        </div>

        <label class="gso-report-toggle">
          <input type="checkbox" name="prefill_count" value="1" @checked(!empty($report['prefill_count']))>
          <span>Prefill the physical-count and shortage or overage columns from the book balance.</span>
        </label>

        <div class="gso-report-filter-group">
          <label for="rpci-accountable-name">Accountable Officer</label>
          <input id="rpci-accountable-name" type="text" name="accountable_officer_name" value="{{ $signatories['accountable_officer_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-accountable-designation">Accountable Designation</label>
          <input id="rpci-accountable-designation" type="text" name="accountable_officer_designation" value="{{ $signatories['accountable_officer_designation'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-assumption-date">Date of Assumption</label>
          <input id="rpci-assumption-date" type="date" name="date_of_assumption" value="{{ $signatories['date_of_assumption'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-chair">Inventory Committee Chair</label>
          <input id="rpci-chair" type="text" name="committee_chair_name" value="{{ $signatories['committee_chair_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-member-1">Committee Member 1</label>
          <input id="rpci-member-1" type="text" name="committee_member_1_name" value="{{ $signatories['committee_member_1_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-member-2">Committee Member 2</label>
          <input id="rpci-member-2" type="text" name="committee_member_2_name" value="{{ $signatories['committee_member_2_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-approved-name">Approved By</label>
          <input id="rpci-approved-name" type="text" name="approved_by_name" value="{{ $signatories['approved_by_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-approved-designation">Approved Designation</label>
          <input id="rpci-approved-designation" type="text" name="approved_by_designation" value="{{ $signatories['approved_by_designation'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-verified-name">Verified By</label>
          <input id="rpci-verified-name" type="text" name="verified_by_name" value="{{ $signatories['verified_by_name'] ?? '' }}">
        </div>

        <div class="gso-report-filter-group">
          <label for="rpci-verified-designation">Verified Designation</label>
          <input id="rpci-verified-designation" type="text" name="verified_by_designation" value="{{ $signatories['verified_by_designation'] ?? '' }}">
        </div>

        <button type="submit" class="gso-report-button gso-report-button--primary">Apply Changes</button>
      </form>

      <div class="gso-report-action-group">
        <button type="button" class="gso-report-button gso-report-button--primary" onclick="window.print()">Print</button>
        <a href="{{ route('gso.stocks.index') }}" class="gso-report-button">Back to Stocks</a>
        <a href="javascript:window.close();" class="gso-report-button">Close Preview</a>
      </div>

      @unless(!empty($rows ?? []))
        <p class="print-workspace-copy" style="margin-top:14px;">
          No on-hand consumable balances were found for this selection yet. You can still prepare the report shell and signatories.
        </p>
      @endunless
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
        <div class="gso-report-appendix">{{ $report['appendix_label'] ?? 'Annex 48' }}</div>
        <div class="gso-report-title">Report on the Physical Count of Inventories</div>

        <table class="gso-report-meta-table">
          <tr>
            <td style="width:50%;"><span class="gso-report-meta-label">Entity Name:</span> {{ $report['entity_name'] ?? 'Local Government Unit' }}</td>
            <td style="width:50%;"><span class="gso-report-meta-label">Fund Cluster:</span> {{ $report['fund_cluster'] ?? 'Unassigned' }}</td>
          </tr>
          <tr>
            <td><span class="gso-report-meta-label">Fund Source:</span> {{ $report['fund_source'] ?? 'Fund Source' }}</td>
            <td><span class="gso-report-meta-label">As of:</span> {{ $report['as_of_label'] ?? '' }}</td>
          </tr>
          <tr>
            <td colspan="2">
              <span class="gso-report-meta-label">Type of Inventory Item:</span>
              {{ strtoupper((string) ($report['inventory_type'] ?? 'OFFICE SUPPLIES')) }}
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <span class="gso-report-meta-label">Accountability Copy:</span>
              For which {{ $signatories['accountable_officer_name'] ?? '' }},
              {{ $signatories['accountable_officer_designation'] ?? '' }}
              is accountable, having assumed such accountability on {{ $assumptionDate }}.
            </td>
          </tr>
        </table>

        <table class="gso-report-items-table">
          <colgroup>
            <col style="width:14%;"><col style="width:22%;"><col style="width:10%;"><col style="width:6%;"><col style="width:8%;">
            <col style="width:8%;"><col style="width:8%;"><col style="width:6%;"><col style="width:8%;"><col style="width:10%;">
          </colgroup>
          <thead>
            <tr>
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
            <tr>
              <th>Qty.</th>
              <th>Value</th>
            </tr>
          </thead>
          <tbody>
            @if($pageRows->isEmpty())
              <tr><td colspan="10" class="gso-report-empty-note">No inventory balances found for the selected report window.</td></tr>
              @php $remainingRows = max(0, $remainingRows - 1); @endphp
            @else
              @foreach($pageRows as $row)
                <tr>
                  <td>{{ $row['article'] ?? '' }}</td>
                  <td>{{ $row['description'] ?? '' }}</td>
                  <td class="gso-report-center">{{ $row['stock_no'] ?? '' }}</td>
                  <td class="gso-report-center">{{ $row['unit'] ?? '' }}</td>
                  <td class="gso-report-right">{{ number_format((float) ($row['unit_value'] ?? 0), 2) }}</td>
                  <td class="gso-report-right">{{ number_format((int) ($row['balance_per_card_qty'] ?? 0)) }}</td>
                  <td class="gso-report-right">{{ isset($row['count_qty']) ? number_format((int) $row['count_qty']) : '' }}</td>
                  <td class="gso-report-right">{{ isset($row['shortage_overage_qty']) ? number_format((int) $row['shortage_overage_qty']) : '' }}</td>
                  <td class="gso-report-right">{{ isset($row['shortage_overage_value']) ? number_format((float) $row['shortage_overage_value'], 2) : '' }}</td>
                  <td>{{ $row['remarks'] ?? '' }}</td>
                </tr>
              @endforeach
            @endif

            @for($i = 0; $i < $remainingRows; $i++)
              <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            @endfor
          </tbody>
        </table>

        @if($isLastPage)
          <table class="gso-report-summary-table">
            <tr><th>Items Listed</th><th>Balance Qty.</th><th>Count Qty.</th><th>Shortage / Overage Qty.</th><th>Book Value</th></tr>
            <tr>
              <td class="gso-report-center">{{ number_format((int) ($summary['total_items'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ number_format((int) ($summary['total_balance_qty'] ?? 0)) }}</td>
              <td class="gso-report-center">{{ isset($summary['total_count_qty']) ? number_format((int) ($summary['total_count_qty'] ?? 0)) : '' }}</td>
              <td class="gso-report-center">{{ isset($summary['total_shortage_overage_qty']) ? number_format((int) ($summary['total_shortage_overage_qty'] ?? 0)) : '' }}</td>
              <td class="gso-report-center">{{ number_format((float) ($summary['total_book_value'] ?? 0), 2) }}</td>
            </tr>
          </table>

          <table class="gso-report-signature-table">
            <tr><th>Certified Correct By</th><th>Approved By</th><th>Verified By</th></tr>
            <tr>
              <td>
                <div class="gso-report-committee-lines">
                  @forelse($committeeNames as $committeeName)
                    <div class="gso-report-signature-name">{{ $committeeName }}</div>
                  @empty
                    <div class="gso-report-signature-name">&nbsp;</div>
                  @endforelse
                </div>
              </td>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['approved_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['approved_by_designation'] ?? '' }}</div>
              </td>
              <td>
                <div class="gso-report-signature-name">{{ $signatories['verified_by_name'] ?? '' }}</div>
                <div class="gso-report-signature-role">{{ $signatories['verified_by_designation'] ?? '' }}</div>
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
