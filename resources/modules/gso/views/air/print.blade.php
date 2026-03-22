<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AIR Print - {{ $air['label'] ?? 'Acceptance and Inspection Report' }}</title>
  <x-print.workspace-styles />
  @include('gso::air.partials.print-styles')
</head>
<body class="print-workspace-body">
@php
  $isPreview = (bool) ($isPreview ?? request()->boolean('preview'));
  $summary = $print['summary'] ?? [];
  $pages = collect($pages ?? [])->values();
  if ($pages->isEmpty()) {
      $pages = collect([collect()]);
  }
  $maxGridRows = (int) ($maxGridRows ?? 24);
  $canOpenInspection = in_array((string) ($air['status'] ?? ''), ['submitted', 'in_progress', 'inspected'], true);
@endphp

<x-print.workspace
  sidebar-width="clamp(330px, calc(210mm * 0.42), 390px)"
  preview-width="210mm"
  gap="36px"
>
  <x-slot:sidebar>
    <x-print.panel
      kicker="AIR"
      title="Acceptance and Inspection Report"
      copy="Review the saved AIR document on the right, then print when the register and inspection details look correct."
    >
      <div class="gso-air-print-panel-section">
        <div class="gso-air-print-panel-title">Document Summary</div>
        <div class="gso-air-print-summary-grid">
          <div class="gso-air-print-summary-field">
            <span>Workflow Status</span>
            <strong>{{ $air['status_text'] ?? 'Unknown' }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Continuation</span>
            <strong>{{ $air['continuation_label'] ?? 'Root AIR' }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Supplier</span>
            <strong>{{ ($print['supplier'] ?? '') ?: 'Not Yet Set' }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Requesting Office</span>
            <strong>{{ ($print['office_department'] ?? '') ?: 'Not Yet Set' }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Fund Source</span>
            <strong>{{ ($print['fund_source'] ?? '') ?: 'Not Yet Set' }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Pages</span>
            <strong>{{ number_format((int) ($summary['page_count'] ?? 0)) }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Item Lines</span>
            <strong>{{ number_format((int) ($summary['line_items'] ?? 0)) }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Printed Rows</span>
            <strong>{{ number_format((int) ($summary['printed_rows'] ?? 0)) }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Unit Rows</span>
            <strong>{{ number_format((int) ($summary['unit_rows'] ?? 0)) }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Total Quantity</span>
            <strong>{{ number_format((int) ($summary['quantity_total'] ?? 0)) }}</strong>
          </div>
        </div>
      </div>

      <div class="gso-air-print-panel-section">
        <div class="gso-air-print-panel-title">Signatures</div>
        <div class="gso-air-print-summary-grid">
          <div class="gso-air-print-summary-field">
            <span>Accepted By</span>
            <strong>{{ ($print['accepted_by_name'] ?? '') ?: 'Pending' }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Inspected By</span>
            <strong>{{ ($print['inspected_by_name'] ?? '') ?: 'Pending' }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Date Received</span>
            <strong>{{ ($print['date_received_label'] ?? '') ?: 'Pending' }}</strong>
          </div>
          <div class="gso-air-print-summary-field">
            <span>Date Inspected</span>
            <strong>{{ ($print['date_inspected_label'] ?? '') ?: 'Pending' }}</strong>
          </div>
        </div>
      </div>

      <div class="gso-air-print-action-group">
        <button type="button" class="gso-air-print-action-primary" onclick="window.print()">Print</button>
        <a href="{{ route('gso.air.edit', ['air' => $air['id'] ?? '']) }}">Open AIR</a>
        @if($canOpenInspection)
          <a href="{{ route('gso.air.inspect', ['air' => $air['id'] ?? '']) }}">Inspection Workspace</a>
        @endif
        <a href="{{ route('gso.air.index') }}">Back to AIR Register</a>
        <a href="javascript:window.close();">Close Preview</a>
      </div>
    </x-print.panel>
  </x-slot:sidebar>

  @foreach($pages as $pageIndex => $pageRows)
    @php
      $pageRows = collect($pageRows);
      $pageNo = $pageIndex + 1;
      $totalPageCount = $pages->count();
      $remainingRows = max(0, $maxGridRows - $pageRows->count());
      $isLastPage = $pageNo === $totalPageCount;
      $receivedCompleteness = strtolower((string) ($print['received_completeness'] ?? ''));
    @endphp

    <div class="gso-air-print-page print-page">
      <div class="gso-air-print-appendix">{{ $print['appendix_label'] ?? 'Appendix 30' }}</div>
      <div class="gso-air-print-title">{{ $print['title'] ?? 'Acceptance and Inspection Report' }}</div>

      <table class="gso-air-print-meta">
        <colgroup>
          <col style="width:50%;">
          <col style="width:50%;">
        </colgroup>
        <tr>
          <td><strong>LGU:</strong> {{ $print['entity_name'] ?? 'Local Government Unit' }}</td>
          <td><strong>Fund:</strong> {{ ($print['fund_source'] ?? '') ?: '-' }}</td>
        </tr>
        <tr>
          <td><strong>Supplier:</strong> {{ ($print['supplier'] ?? '') ?: '-' }}</td>
          <td><strong>AIR No.:</strong> {{ ($print['air_no'] ?? '') ?: '-' }}</td>
        </tr>
        <tr>
          <td><strong>PO No. / Date:</strong> {{ ($print['po_number'] ?? '') ?: '-' }}@if(!empty($print['po_date_label'] ?? null)) / {{ $print['po_date_label'] }}@endif</td>
          <td><strong>Date:</strong> {{ ($print['air_date_label'] ?? '') ?: '-' }}</td>
        </tr>
        <tr>
          <td rowspan="2"><strong>Req. Office / Dept.:</strong> {{ ($print['office_department'] ?? '') ?: '-' }}</td>
          <td><strong>Invoice No.:</strong> {{ ($print['invoice_no'] ?? '') ?: '-' }}</td>
        </tr>
        <tr>
          <td><strong>Invoice Date:</strong> {{ ($print['invoice_date_label'] ?? '') ?: '-' }}</td>
        </tr>
      </table>

      <table class="gso-air-print-items">
        <colgroup>
          <col style="width:18%;">
          <col style="width:52%;">
          <col style="width:15%;">
          <col style="width:15%;">
        </colgroup>
        <thead>
          <tr>
            <th>Property No.</th>
            <th>Description</th>
            <th>Unit</th>
            <th>Quantity</th>
          </tr>
        </thead>
        <tbody>
          @if($pageRows->isEmpty())
            <tr>
              <td colspan="4" class="gso-air-print-empty-note">No AIR line items are available for print yet.</td>
            </tr>
            @php $remainingRows = max(0, $remainingRows - 1); @endphp
          @else
            @foreach($pageRows as $row)
              @if(!empty($row['__msg']))
                <tr>
                  <td>&nbsp;</td>
                  <td class="gso-air-print-message">{{ $row['description'] ?? '' }}</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
              @else
                <tr>
                  <td class="gso-air-print-center">{{ $row['property_no'] ?: ' ' }}</td>
                  <td>{{ $row['description'] ?: ' ' }}</td>
                  <td class="gso-air-print-center">{{ $row['unit'] ?: ' ' }}</td>
                  <td class="gso-air-print-center">{{ $row['quantity'] !== '' ? $row['quantity'] : ' ' }}</td>
                </tr>
              @endif
            @endforeach
          @endif

          @for($i = 0; $i < $remainingRows; $i++)
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          @endfor
        </tbody>
      </table>

      @if($isLastPage)
        <table class="gso-air-print-signatures">
          <colgroup>
            <col style="width:50%;">
            <col style="width:50%;">
          </colgroup>
          <tr>
            <th>Acceptance</th>
            <th>Inspection</th>
          </tr>
          <tr>
            <td style="height: 78px; vertical-align: top;">
              <div><strong>Date Received:</strong> {{ ($print['date_received_label'] ?? '') ?: '____________________________' }}</div>
              <div style="margin-top: 12px;">
                <span class="gso-air-print-checkbox">{{ $receivedCompleteness === 'complete' ? 'X' : '' }}</span>
                Complete
              </div>
              <div style="margin-top: 8px;">
                <span class="gso-air-print-checkbox">{{ $receivedCompleteness === 'partial' ? 'X' : '' }}</span>
                Partial
                @if(!empty($print['received_notes'] ?? null))
                  ({{ $print['received_notes'] }})
                @else
                  (please specify)
                @endif
              </div>
            </td>
            <td style="height: 78px; vertical-align: top;">
              <div><strong>Date Inspected:</strong> {{ ($print['date_inspected_label'] ?? '') ?: '____________________________' }}</div>
              <div style="margin-top: 18px;">
                <span class="gso-air-print-checkbox">{{ !empty($print['inspection_verified'] ?? null) ? 'X' : '' }}</span>
                Inspected, verified and found in order as to quantity and specifications.
              </div>
            </td>
          </tr>
          <tr>
            <td style="height: 64px; text-align: center;">
              @if(!empty($print['accepted_by_name'] ?? null))
                <div class="gso-air-print-signature-name">{{ $print['accepted_by_name'] }}</div>
              @endif
              <div class="gso-air-print-signature-role">{{ $print['accepted_by_designation'] ?? '' }}</div>
            </td>
            <td style="height: 64px; text-align: center;">
              @if(!empty($print['inspected_by_name'] ?? null))
                <div class="gso-air-print-signature-name">{{ $print['inspected_by_name'] }}</div>
              @endif
              <div class="gso-air-print-signature-role">{{ $print['inspected_by_designation'] ?? '' }}</div>
            </td>
          </tr>
        </table>
      @endif

      <div class="gso-air-print-page-number">Page {{ $pageNo }} of {{ $totalPageCount }}</div>
    </div>
  @endforeach
</x-print.workspace>

<script>
  window.addEventListener('load', function () {
    const isPreview = {{ $isPreview ? 'true' : 'false' }};
    if (!isPreview) {
      window.print();
    }
  });
</script>
</body>
</html>
