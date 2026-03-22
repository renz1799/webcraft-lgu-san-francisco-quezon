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
    .no-print { display: none !important; }
  }

  .annex {
    position: absolute;
    top: 0;
    right: 0;
    font-style: italic;
    font-size: 12px;
  }

  .title {
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    margin-top: 2mm;
    margin-bottom: 4mm;
    letter-spacing: 0.2px;
  }

  .meta-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 3mm;
  }

  .meta-left, .meta-right { font-size: 12px; }

  .underline {
    display: inline-block;
    border-bottom: 1px solid #000;
    padding: 0 2px;
    min-width: 60mm;
  }

  .spc-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .spc-table th,
  .spc-table td {
    border: 1px solid #000;
    padding: 2px 3px;
    vertical-align: middle;
    line-height: 1.1;
  }

  .spc-table th {
    text-align: center;
    font-weight: 700;
  }

  .left { text-align: left; }
  .center { text-align: center; }
  .right { text-align: right; }

  .nowrap { white-space: nowrap; }
  .wrap { white-space: normal; word-break: break-word; }

  .h-label {
    font-weight: 700;
    white-space: nowrap;
  }

  .h-value {
    font-weight: 400;
  }

  .grid-row td {
    height: 6mm;
  }

  table { page-break-inside: avoid; }
  tr { page-break-inside: avoid; page-break-after: auto; }
</style>
