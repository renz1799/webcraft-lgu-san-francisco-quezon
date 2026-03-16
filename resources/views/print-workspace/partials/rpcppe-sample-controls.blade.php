@php
  $summary = $report['summary'] ?? [];
  $selectedFundSourceId = (string) ($report['fund_source_id'] ?? '');
  $selectedDepartmentId = (string) ($report['department_id'] ?? '');
  $selectedOfficerId = (string) ($report['accountable_officer_id'] ?? '');
  $recordCount = (int) ($report['record_count'] ?? 20);
  $signatories = $report['signatories'] ?? [];
  $hasRows = !empty($rows ?? []);
@endphp

<x-print.panel
  kicker="Reports"
  title="RPCPPE Preview"
  copy="Use this Core sample as the print-workspace template. The controls update the mock report on the right without touching any database records."
>
  <div class="rpcppe-panel-section">
    <div class="rpcppe-summary-label">Report Window</div>
    <div class="rpcppe-summary-value">{{ $report['as_of_label'] ?? 'Current Date' }}</div>
    <div class="rpcppe-summary-copy">{{ $report['fund_source'] ?? 'All Fund Sources' }}</div>

    <div class="rpcppe-summary-grid">
      <div class="rpcppe-summary-field">
        <span class="rpcppe-summary-label">Fund Cluster</span>
        <strong>{{ $report['fund_cluster'] ?? 'All / Multiple' }}</strong>
      </div>
      <div class="rpcppe-summary-field">
        <span class="rpcppe-summary-label">Offices Covered</span>
        <strong>{{ number_format((int) ($summary['offices_covered'] ?? 0)) }}</strong>
      </div>
      <div class="rpcppe-summary-field">
        <span class="rpcppe-summary-label">PPE Items</span>
        <strong>{{ number_format((int) ($summary['total_items'] ?? 0)) }}</strong>
      </div>
      <div class="rpcppe-summary-field">
        <span class="rpcppe-summary-label">Book Value</span>
        <strong>{{ number_format((float) ($summary['total_book_value'] ?? 0), 2) }}</strong>
      </div>
    </div>
  </div>

  <form method="GET" action="{{ route('reports.samples.rpcppe') }}" class="rpcppe-filter-form">
    <div class="rpcppe-action-group rpcppe-action-group--top">
      <button type="submit" class="btn rpcppe-primary-btn">Apply Changes</button>
      <button
        type="submit"
        class="btn rpcppe-primary-btn"
        formaction="{{ route('reports.samples.rpcppe.pdf') }}"
        formtarget="_blank"
      >
        Download PDF
      </button>
      <button type="button" class="btn" onclick="window.print()">Print</button>
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-record-count">Mock Record Count</label>
      <input
        id="sample-rpcppe-record-count"
        type="number"
        name="record_count"
        min="1"
        max="60"
        value="{{ $recordCount }}"
      >
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-fund-source">Fund Source</label>
      <select id="sample-rpcppe-fund-source" name="fund_source_id">
        <option value="">All Fund Sources</option>
        @foreach(($available_funds ?? []) as $fund)
          <option value="{{ $fund['id'] }}" @selected((string) ($fund['id'] ?? '') === $selectedFundSourceId)>
            {{ $fund['label'] }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-office">Office</label>
      <select id="sample-rpcppe-office" name="department_id">
        <option value="">All Offices</option>
        @foreach(($available_departments ?? []) as $department)
          <option value="{{ $department['id'] }}" @selected((string) ($department['id'] ?? '') === $selectedDepartmentId)>
            {{ $department['label'] }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-officer">Accountable Officer</label>
      <select id="sample-rpcppe-officer" name="accountable_officer_id">
        <option value="">All Accountable Officers</option>
        @foreach(($available_accountable_officers ?? []) as $officer)
          <option value="{{ $officer['id'] }}" @selected((string) ($officer['id'] ?? '') === $selectedOfficerId)>
            {{ $officer['label'] }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-as-of">As of Date</label>
      <input id="sample-rpcppe-as-of" type="date" name="as_of" value="{{ $report['as_of'] ?? '' }}">
    </div>

    <label class="rpcppe-toggle">
      <input type="checkbox" name="prefill_count" value="1" @checked(!empty($report['prefill_count']))>
      <span>Prefill the physical-count and shortage/overage columns from the book quantity.</span>
    </label>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-accountable-name">Accountable Officer Signatory</label>
      <input id="sample-rpcppe-accountable-name" type="text" name="accountable_officer_name" value="{{ $signatories['accountable_officer_name'] ?? '' }}">
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-accountable-designation">Accountable Designation</label>
      <input id="sample-rpcppe-accountable-designation" type="text" name="accountable_officer_designation" value="{{ $signatories['accountable_officer_designation'] ?? '' }}">
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-chair">Inventory Committee Chair</label>
      <input id="sample-rpcppe-chair" type="text" name="committee_chair_name" value="{{ $signatories['committee_chair_name'] ?? '' }}">
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-member-1">Committee Member 1</label>
      <input id="sample-rpcppe-member-1" type="text" name="committee_member_1_name" value="{{ $signatories['committee_member_1_name'] ?? '' }}">
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-member-2">Committee Member 2</label>
      <input id="sample-rpcppe-member-2" type="text" name="committee_member_2_name" value="{{ $signatories['committee_member_2_name'] ?? '' }}">
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-approved-name">Approved By</label>
      <input id="sample-rpcppe-approved-name" type="text" name="approved_by_name" value="{{ $signatories['approved_by_name'] ?? '' }}">
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-approved-designation">Approved Designation</label>
      <input id="sample-rpcppe-approved-designation" type="text" name="approved_by_designation" value="{{ $signatories['approved_by_designation'] ?? '' }}">
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-verified-name">Verified By</label>
      <input id="sample-rpcppe-verified-name" type="text" name="verified_by_name" value="{{ $signatories['verified_by_name'] ?? '' }}">
    </div>

    <div class="rpcppe-filter-group">
      <label for="sample-rpcppe-verified-designation">Verified Designation</label>
      <input id="sample-rpcppe-verified-designation" type="text" name="verified_by_designation" value="{{ $signatories['verified_by_designation'] ?? '' }}">
    </div>
  </form>

  <div class="rpcppe-action-group">
    <a href="{{ route('reports.samples.rpcppe') }}" class="btn">Reset Sample</a>
    <a href="{{ route('profile.index') }}" class="btn">Back to App</a>
  </div>

  @unless($hasRows)
    <p class="print-workspace-copy" style="margin-top:14px;">
      No mock PPE rows matched the current selection. The template still renders the report shell so you can evaluate spacing and signatories.
    </p>
  @endunless
</x-print.panel>
