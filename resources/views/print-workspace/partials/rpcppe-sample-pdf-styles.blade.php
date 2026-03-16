@php
  $headerUrl = $assetUrls['header'] ?? '';
  $footerUrl = $assetUrls['footer'] ?? '';
@endphp
<style>
  @page {
    size: A4 landscape;
    margin: 0;
  }

  html,
  body {
    margin: 0;
    padding: 0;
    background: #fff;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    color: #000;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .rpcppe-pdf-document {
    width: 297mm;
    margin: 0;
  }

  .rpcppe-sample-page {
    width: 297mm;
    height: 210mm;
    min-height: 210mm;
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    background: #fff;
    background-image:
      url('{{ $headerUrl }}'),
      url('{{ $footerUrl }}');
    background-repeat: no-repeat, no-repeat;
    background-position: top left, bottom left;
    background-size: 297mm auto, 297mm auto;
    page-break-after: always;
  }

  .rpcppe-sample-page:last-child {
    page-break-after: auto;
  }

  .rpcppe-sample-content-frame {
    flex: 1 1 auto;
    display: flex;
    min-height: 0;
  }

  .rpcppe-sample-content {
    flex: 1 1 auto;
    padding: 31mm 14mm 25mm;
    position: relative;
    box-sizing: border-box;
  }

  .rpcppe-sample-appendix {
    position: absolute;
    top: 0;
    right: 14mm;
    font-size: 11px;
    font-style: italic;
    color: #111827;
  }

  .rpcppe-sample-title {
    margin-bottom: 8px;
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.02em;
  }

  .rpcppe-sample-page-number {
    position: absolute;
    right: 10mm;
    bottom: 23mm;
    font-size: 10px;
  }

  .rpcppe-meta-table,
  .rpcppe-items-table,
  .rpcppe-signature-table,
  .rpcppe-summary-table {
    width: 100%;
    border-collapse: collapse;
    color: #000;
  }

  .rpcppe-meta-table {
    margin-bottom: 4px;
  }

  .rpcppe-meta-table td,
  .rpcppe-items-table td,
  .rpcppe-items-table th,
  .rpcppe-signature-table td,
  .rpcppe-signature-table th,
  .rpcppe-summary-table td,
  .rpcppe-summary-table th {
    border: 1px solid #111;
    padding: 2px 4px;
    font-size: 9px;
    vertical-align: top;
  }

  .rpcppe-meta-label {
    font-weight: 700;
  }

  .rpcppe-meta-value {
    display: inline-block;
    border-bottom: 1px solid #111;
    min-height: 12px;
    padding: 0 2px;
  }

  .rpcppe-meta-value.medium {
    min-width: 160px;
  }

  .rpcppe-meta-value.long {
    min-width: 220px;
  }

  .rpcppe-accountability-copy {
    min-height: 14px;
  }

  .rpcppe-items-table {
    table-layout: fixed;
  }

  .rpcppe-items-table th {
    text-align: center;
    font-weight: 700;
  }

  .rpcppe-items-row td {
    height: 14px;
    line-height: 1.15;
  }

  .rpcppe-items-row--blank td {
    color: transparent;
  }

  .rpcppe-center {
    text-align: center;
  }

  .rpcppe-right {
    text-align: right;
  }

  .rpcppe-empty-note {
    text-align: center;
    font-style: italic;
    color: #475569;
  }

  .stack-next {
    margin-top: 4px;
  }

  .rpcppe-signature-title,
  .rpcppe-summary-table th {
    text-transform: uppercase;
    text-align: center;
  }

  .rpcppe-signature-block td {
    height: 40px;
    text-align: center;
    vertical-align: middle;
  }

  .rpcppe-committee-lines {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .rpcppe-signature-name {
    font-weight: 700;
    text-transform: uppercase;
    min-height: 10px;
  }

  .rpcppe-signature-role {
    font-size: 8px;
    margin-top: 2px;
  }
</style>
