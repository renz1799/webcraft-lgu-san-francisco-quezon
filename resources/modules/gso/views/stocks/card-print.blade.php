<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Stock Card - {{ $card['item_name'] ?? 'Item' }}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 12mm;
    }

    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      color: #0f172a;
      background: #f8fafc;
    }

    .toolbar {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      justify-content: space-between;
      padding: 18px 22px;
      border-bottom: 1px solid #dbe2ea;
      background: #fff;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .toolbar-form {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
      width: min(520px, 100%);
    }

    .toolbar-form label {
      display: flex;
      flex-direction: column;
      gap: 6px;
      font-size: 12px;
      font-weight: 700;
      color: #334155;
    }

    .toolbar-form select,
    .toolbar-form input,
    .toolbar-actions a,
    .toolbar-actions button {
      min-height: 40px;
      border: 1px solid #cbd5e1;
      border-radius: 10px;
      padding: 8px 11px;
      font-size: 13px;
      background: #fff;
      color: #0f172a;
      box-sizing: border-box;
      text-decoration: none;
      cursor: pointer;
    }

    .toolbar-actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    .toolbar-actions .primary {
      background: #2563eb;
      border-color: #2563eb;
      color: #fff;
    }

    .page {
      width: 186mm;
      min-height: 263mm;
      margin: 18px auto;
      background: #fff;
      box-shadow: 0 16px 38px rgba(15, 23, 42, 0.12);
      padding: 12mm 10mm;
      box-sizing: border-box;
    }

    .appendix {
      text-align: right;
      font-size: 10px;
      font-style: italic;
      margin-bottom: 4px;
    }

    .title {
      text-align: center;
      font-size: 16px;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      margin-bottom: 10px;
    }

    .meta {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
      font-size: 11px;
    }

    .meta td,
    .entries td,
    .entries th {
      border: 1px solid #0f172a;
      padding: 5px 6px;
      vertical-align: top;
    }

    .meta strong {
      font-weight: 700;
    }

    .entries {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: 11px;
    }

    .entries th {
      text-align: center;
      font-weight: 700;
    }

    .entries td {
      height: 18px;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }

    .empty-note {
      text-align: center;
      color: #64748b;
      font-style: italic;
    }

    @media print {
      body {
        background: #fff;
      }

      .toolbar {
        display: none !important;
      }

      .page {
        width: auto;
        min-height: auto;
        margin: 0;
        box-shadow: none;
        padding: 0;
      }
    }
  </style>
</head>
<body>
@php
  $isPreview = (bool) ($isPreview ?? request()->boolean('preview'));
@endphp

<div class="toolbar">
  <form method="GET" action="{{ request()->url() }}" class="toolbar-form">
    <input type="hidden" name="preview" value="1">

    <label>
      Fund Source
      <select name="fund_source_id">
        <option value="">Auto Select</option>
        @foreach(($available_funds ?? []) as $fund)
          <option value="{{ $fund['id'] }}" @selected((string) ($card['fund_source_id'] ?? '') === (string) ($fund['id'] ?? ''))>
            {{ $fund['label'] }} ({{ $fund['on_hand'] }})
          </option>
        @endforeach
      </select>
    </label>

    <label>
      As of Date
      <input type="date" name="as_of" value="{{ $card['as_of'] ?? '' }}">
    </label>

    <div class="toolbar-actions" style="grid-column: 1 / -1;">
      <button type="submit">Apply</button>
      <button type="button" class="primary" onclick="window.print()">Print</button>
      <a href="{{ route('gso.stocks.ledger', ['item' => request()->route('item')]) }}">View Ledger</a>
      <a href="{{ route('gso.stocks.index') }}">Back to Stocks</a>
    </div>
  </form>
</div>

<div class="page">
  <div class="appendix">Appendix 58</div>
  <div class="title">Stock Card</div>

  <table class="meta">
    <tr>
      <td style="width:50%;"><strong>Entity Name:</strong> {{ $card['entity_name'] ?? 'Local Government Unit' }}</td>
      <td style="width:50%;"><strong>Fund Cluster:</strong> {{ $card['fund_cluster'] ?: 'Not Set' }}</td>
    </tr>
    <tr>
      <td><strong>Fund Source:</strong> {{ $card['fund_source'] ?: 'Unassigned Stock Row' }}</td>
      <td><strong>As of:</strong> {{ $card['as_of'] ?: now()->toDateString() }}</td>
    </tr>
    <tr>
      <td><strong>Item:</strong> {{ $card['item_name'] ?: 'Item' }}</td>
      <td><strong>Current On Hand:</strong> {{ number_format((int) ($card['current_on_hand'] ?? 0)) }}</td>
    </tr>
    <tr>
      <td><strong>Stock No:</strong> {{ $card['stock_no'] ?: '-' }}</td>
      <td><strong>Unit:</strong> {{ $card['unit'] ?: '-' }}</td>
    </tr>
    <tr>
      <td colspan="2"><strong>Description:</strong> {{ $card['description'] ?: '-' }}</td>
    </tr>
  </table>

  <table class="entries">
    <colgroup>
      <col style="width:14%;">
      <col style="width:28%;">
      <col style="width:12%;">
      <col style="width:12%;">
      <col style="width:12%;">
      <col style="width:22%;">
    </colgroup>
    <thead>
      <tr>
        <th>Date</th>
        <th>Reference</th>
        <th>Receipt</th>
        <th>Issue</th>
        <th>Balance</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $row)
        <tr>
          <td class="text-center">{{ $row['date'] ?: '-' }}</td>
          <td>{{ $row['reference'] ?: '-' }}</td>
          <td class="text-right">{{ $row['receipt_qty'] !== null ? number_format((int) $row['receipt_qty']) : '' }}</td>
          <td class="text-right">{{ $row['issue_qty'] !== null ? number_format((int) $row['issue_qty']) : '' }}</td>
          <td class="text-right">{{ number_format((int) ($row['balance_qty'] ?? 0)) }}</td>
          <td>{{ $row['remarks'] ?: '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="empty-note">No stock movements were found for the selected scope.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<script>
  window.addEventListener("load", function () {
    const isPreview = {{ $isPreview ? 'true' : 'false' }};
    if (!isPreview) {
      window.print();
    }
  });
</script>
</body>
</html>
