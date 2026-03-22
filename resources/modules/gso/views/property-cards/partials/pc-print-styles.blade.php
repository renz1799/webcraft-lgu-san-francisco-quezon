<style>
  @page { size: A4 landscape; margin: 12mm 10mm; }

  html, body {
    font-family: "Times New Roman", Times, serif;
    font-size: 12px;
    color: #000;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .page {
    width: 100%;
    min-height: calc(210mm - 24mm);
  }

  .print-controls {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
  }

  @media print {
    .page {
      min-height: auto !important;
      height: auto !important;
    }

    html, body {
      height: auto !important;
      margin: 0 !important;
      padding: 0 !important;
      overflow: visible !important;
    }

    table { page-break-inside: auto; }
    tr, td, th { page-break-inside: avoid; }
    .no-print { display: none !important; }
  }

  .appendix {
    position: absolute;
    top: 0;
    right: 0;
    font-style: italic;
    font-size: 12px;
  }

  .title {
    text-align: center;
    font-weight: 700;
    font-size: 16px;
    letter-spacing: 0.4px;
    margin-top: 2mm;
    margin-bottom: 6mm;
  }

  .meta-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 3mm;
  }

  .meta-left, .meta-right {
    font-size: 12px;
  }

  .meta-right .underline,
  .meta-left .underline {
    display: inline-block;
    border-bottom: 1px solid #000;
    padding: 0 2px;
    min-width: 60mm;
  }

  .pc-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .pc-table th,
  .pc-table td {
    border: 1px solid #000;
    padding: 2px 3px;
    vertical-align: middle;
    line-height: 1.1;
  }

  .pc-table th {
    text-align: center;
    font-weight: 700;
  }

  .pc-table .left { text-align: left; }
  .pc-table .center { text-align: center; }
  .pc-table .right { text-align: right; }

  .pc-table .h-label {
    font-weight: 700;
    white-space: nowrap;
  }

  .pc-table .h-value {
    font-weight: 400;
  }

  .nowrap { white-space: nowrap; }
  .wrap { white-space: normal; word-break: break-word; }

  .pc-table tbody tr,
  .pc-table tbody td {
    height: 4mm;
  }
</style>
