<style>
  @page {
    size: A4 portrait;
    margin: 10mm;
  }

  .gso-air-print-panel-section {
    border: 1px solid #dbe3ee;
    border-radius: 16px;
    padding: 14px 15px;
    margin-bottom: 14px;
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(241, 245, 249, 0.96));
  }

  .gso-air-print-panel-section:last-child {
    margin-bottom: 0;
  }

  .gso-air-print-panel-title {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #9a3412;
    margin-bottom: 10px;
  }

  .gso-air-print-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .gso-air-print-summary-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .gso-air-print-summary-field span {
    font-size: 11px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .gso-air-print-summary-field strong {
    font-size: 14px;
    color: #0f172a;
    font-weight: 700;
  }

  .gso-air-print-action-group {
    display: grid;
    gap: 10px;
  }

  .gso-air-print-action-group a,
  .gso-air-print-action-group button {
    width: 100%;
    min-height: 42px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    background: #fff;
    text-decoration: none;
    cursor: pointer;
    box-sizing: border-box;
  }

  .gso-air-print-action-group .gso-air-print-action-primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
  }

  .gso-air-print-page {
    width: 210mm;
    min-height: 297mm;
    background: #fff;
    box-sizing: border-box;
    box-shadow: 0 24px 46px rgba(15, 23, 42, 0.14);
    padding: 11mm 10mm 10mm;
    color: #0f172a;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    page-break-after: always;
  }

  .gso-air-print-page:last-child {
    page-break-after: auto;
  }

  .gso-air-print-appendix {
    text-align: right;
    font-size: 10px;
    font-style: italic;
    margin-bottom: 4px;
  }

  .gso-air-print-title {
    text-align: center;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .gso-air-print-meta,
  .gso-air-print-items,
  .gso-air-print-signatures {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .gso-air-print-meta td,
  .gso-air-print-items th,
  .gso-air-print-items td,
  .gso-air-print-signatures th,
  .gso-air-print-signatures td {
    border: 1px solid #0f172a;
    padding: 4px 6px;
    vertical-align: middle;
    word-break: break-word;
  }

  .gso-air-print-meta td strong {
    font-weight: 700;
  }

  .gso-air-print-items {
    margin-top: -1px;
  }

  .gso-air-print-items th {
    text-align: center;
    font-weight: 700;
    height: 20px;
  }

  .gso-air-print-items td {
    height: 18px;
  }

  .gso-air-print-center {
    text-align: center;
  }

  .gso-air-print-message {
    text-align: center;
    font-weight: 700;
    letter-spacing: 0.04em;
  }

  .gso-air-print-empty-note {
    text-align: center;
    color: #64748b;
    font-style: italic;
  }

  .gso-air-print-signatures {
    margin-top: -1px;
  }

  .gso-air-print-signatures th {
    text-align: center;
    font-weight: 700;
    text-transform: uppercase;
    height: 20px;
  }

  .gso-air-print-checkbox {
    display: inline-block;
    width: 10px;
    height: 10px;
    border: 1px solid #0f172a;
    margin-right: 6px;
    text-align: center;
    line-height: 9px;
    font-size: 9px;
    font-weight: 700;
  }

  .gso-air-print-signature-name {
    margin-top: 18px;
    font-weight: 700;
    text-transform: uppercase;
    text-decoration: underline;
  }

  .gso-air-print-signature-role {
    font-size: 10px;
    margin-top: 4px;
  }

  .gso-air-print-page-number {
    margin-top: 6px;
    text-align: right;
    font-size: 10px;
    color: #334155;
  }

  @media print {
    .gso-air-print-page {
      width: auto;
      min-height: auto;
      margin: 0;
      box-shadow: none;
      padding: 0;
    }
  }
</style>
