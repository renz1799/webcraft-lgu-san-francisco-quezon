@php
  $pageSize = $pageSize ?? 'A4 landscape';
@endphp
<style>
  @page {
    size: {{ $pageSize }};
    margin: 0;
  }

  html,
  body {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #0f172a;
  }

  .print-workspace-body {
    color: #0f172a;
  }

  .gso-report-page {
    width: 297mm;
    min-height: 210mm;
    background: #fff;
    box-sizing: border-box;
    page-break-after: always;
    box-shadow: 0 16px 38px rgba(15, 23, 42, 0.12);
  }

  .gso-report-page:last-child {
    page-break-after: auto;
  }

  .gso-report-content {
    padding: 12mm 11mm 12mm;
  }

  .gso-report-appendix {
    text-align: right;
    font-style: italic;
    font-size: 10px;
    margin-bottom: 4px;
  }

  .gso-report-title {
    text-align: center;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 15px;
    line-height: 1.25;
    letter-spacing: 0.03em;
    margin-bottom: 8px;
  }

  .gso-report-meta-table,
  .gso-report-items-table,
  .gso-report-summary-table,
  .gso-report-signature-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .gso-report-meta-table {
    margin-bottom: 8px;
  }

  .gso-report-meta-table td,
  .gso-report-items-table td,
  .gso-report-items-table th,
  .gso-report-summary-table td,
  .gso-report-summary-table th,
  .gso-report-signature-table td,
  .gso-report-signature-table th {
    border: 1px solid #0f172a;
    padding: 4px 5px;
    vertical-align: middle;
    word-break: break-word;
  }

  .gso-report-meta-label {
    font-weight: 700;
  }

  .gso-report-items-table th,
  .gso-report-summary-table th,
  .gso-report-signature-table th {
    text-align: center;
    font-weight: 700;
  }

  .gso-report-items-table thead {
    display: table-header-group;
  }

  .gso-report-items-table tfoot {
    display: table-footer-group;
  }

  .gso-report-items-table tr,
  .gso-report-summary-table tr,
  .gso-report-signature-table tr {
    page-break-inside: avoid;
  }

  .gso-report-items-table tbody td {
    height: 19px;
  }

  .gso-report-empty-note {
    text-align: center;
    color: #64748b;
    font-style: italic;
  }

  .gso-report-center {
    text-align: center;
  }

  .gso-report-right {
    text-align: right;
  }

  .gso-report-summary-table,
  .gso-report-signature-table {
    margin-top: -1px;
  }

  .gso-report-signature-name {
    margin-top: 18px;
    text-align: center;
    font-weight: 700;
    text-transform: uppercase;
    text-decoration: underline;
  }

  .gso-report-signature-role {
    margin-top: 3px;
    text-align: center;
    font-size: 10px;
  }

  .gso-report-committee-lines {
    min-height: 56px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 10px;
  }

  .gso-report-page-number {
    text-align: right;
    font-size: 10px;
    margin-top: 6px;
  }

  .gso-report-panel-section {
    margin-bottom: 18px;
  }

  .gso-report-summary-copy {
    margin-top: 6px;
    color: #475569;
    font-size: 13px;
    line-height: 1.45;
  }

  .gso-report-summary-grid {
    margin-top: 12px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .gso-report-summary-field {
    border: 1px solid #d9e2ee;
    border-radius: 12px;
    padding: 11px 12px;
    background: #f8fafc;
  }

  .gso-report-summary-field span {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 4px;
  }

  .gso-report-summary-field strong {
    display: block;
    font-size: 15px;
    color: #111827;
  }

  .gso-report-filter-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .gso-report-filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .gso-report-filter-group label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
  }

  .gso-report-filter-group input,
  .gso-report-filter-group select {
    width: 100%;
    min-height: 40px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 9px 11px;
    font-size: 13px;
    color: #0f172a;
    box-sizing: border-box;
    background: #fff;
  }

  .gso-report-toggle {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid #d9e2ee;
    border-radius: 12px;
    background: #f8fafc;
    font-size: 12px;
    line-height: 1.45;
    color: #334155;
  }

  .gso-report-toggle input {
    margin-top: 1px;
  }

  .gso-report-action-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
  }

  .gso-report-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 14px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    color: #0f172a;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
  }

  .gso-report-button:hover {
    text-decoration: none;
    background: #f8fafc;
  }

  .gso-report-button--primary {
    background: #0f766e;
    border-color: #0f766e;
    color: #fff;
  }

  .gso-report-button--primary:hover {
    background: #115e59;
    border-color: #115e59;
    color: #fff;
  }

  @media screen {
    .gso-report-page {
      overflow: hidden;
    }
  }

  @media print {
    .gso-report-page {
      width: auto;
      min-height: auto;
      box-shadow: none;
    }
  }
</style>
