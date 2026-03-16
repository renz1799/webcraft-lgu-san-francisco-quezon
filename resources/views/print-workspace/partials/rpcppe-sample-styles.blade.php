<style>
  @page {
    size: A4 landscape;
    margin: 0;
  }

  .rpcppe-sample-page {
    width: 297mm;
    height: 210mm;
    min-height: 210mm;
    background: #fff;
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
    box-shadow: 0 20px 48px rgba(15, 23, 42, 0.12);
    display: flex;
    flex-direction: column;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    background-image:
      url('{{ asset('headers/a4_landscape_header_dark_3508x300.png') }}'),
      url('{{ asset('headers/a4_landscape_footer_dark_3508x250.png') }}');
    background-repeat: no-repeat, no-repeat;
    background-position: top center, bottom center;
    background-size: 100% auto, 100% auto;
  }

  .rpcppe-sample-page::before,
  .rpcppe-sample-page::after {
    content: none;
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
    break-inside: avoid;
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

  .rpcppe-panel-section {
    padding: 14px;
    margin-bottom: 16px;
    border: 1px solid #d8e3f0;
    border-radius: 18px;
    background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
  }

  .rpcppe-summary-label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
  }

  .rpcppe-summary-value {
    margin-top: 3px;
    font-size: 18px;
    font-weight: 800;
    color: #1f2937;
  }

  .rpcppe-summary-copy {
    margin-top: 6px;
    font-size: 13px;
    color: #64748b;
  }

  .rpcppe-summary-grid {
    margin-top: 14px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .rpcppe-summary-field {
    border: 1px solid #cbdaf0;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.85);
    padding: 10px 12px;
  }

  .rpcppe-summary-field strong {
    display: block;
    margin-top: 3px;
    font-size: 14px;
    color: #1f2937;
  }

  .rpcppe-filter-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .rpcppe-filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .rpcppe-filter-group label {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
  }

  .rpcppe-filter-group input,
  .rpcppe-filter-group select {
    width: 100%;
    border: 1px solid #c9d7ea;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 14px;
    color: #1f2937;
    background: #fff;
    box-sizing: border-box;
  }

  .rpcppe-toggle {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 10px;
    align-items: start;
    border: 1px solid #c9d7ea;
    border-radius: 14px;
    padding: 12px 14px;
    font-size: 13px;
    color: #475569;
  }

  .rpcppe-toggle input {
    margin-top: 2px;
  }

  .rpcppe-primary-btn {
    background: linear-gradient(135deg, #0f4c81 0%, #1d6fb8 100%);
    color: #fff !important;
    border-color: #0f4c81 !important;
  }

  .rpcppe-primary-btn:hover {
    background: linear-gradient(135deg, #0b3a62 0%, #175a95 100%);
  }

  .rpcppe-action-group {
    margin-top: 18px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .rpcppe-action-group .btn,
  .rpcppe-filter-form .btn {
    border: 1px solid #d3deeb;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    color: #1f2937;
    background: #fff;
    cursor: pointer;
  }

  @media print {
    .rpcppe-sample-page {
      width: 297mm;
      height: 205mm;
      min-height: 205mm;
      box-shadow: none;
      margin: 0;
      overflow: visible;
      background-image: linear-gradient(to top, #111827 0 7mm, transparent 7mm);
      background-repeat: no-repeat;
      background-size: 100% 100%;
    }

    .rpcppe-sample-content {
      padding: 31mm 14mm 25mm;
    }

    .rpcppe-sample-page::before,
    .rpcppe-sample-page::after {
      content: '';
      position: absolute;
      left: -6mm;
      width: calc(100% + 12mm);
      background-repeat: no-repeat;
      background-size: 100% 100%;
      pointer-events: none;
      z-index: 0;
      display: block;
    }

    .rpcppe-sample-page::before {
      top: -6mm;
      height: 32mm;
      background-image: url('{{ asset('headers/a4_landscape_header_dark_3508x300.png') }}');
    }

    .rpcppe-sample-page::after {
      bottom: -6mm;
      height: 26mm;
      background-image: url('{{ asset('headers/a4_landscape_footer_dark_3508x250.png') }}');
    }

    .rpcppe-sample-content-frame,
    .rpcppe-sample-content,
    .rpcppe-sample-page-number {
      position: relative;
      z-index: 1;
    }

    .rpcppe-sample-page:not(:last-child) {
      break-after: page;
      page-break-after: always;
    }
  }
</style>
