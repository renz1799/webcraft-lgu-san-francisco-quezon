<style>
  /* ===== PAGE SETUP ===== */
  @page {
    size: A4 portrait;
    margin: 0;
  }

  html, body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000;
    margin: 0;
    padding: 0;
  }

  .page { position: relative; }

  /* Put the form content above the header image */
  .page-content{
    position: relative;
    z-index: 2;
  }

  /* Header image layer (NOT in flow) */
  .print-header{
    position: fixed;
    left: 0;
    right: 0;
    top: calc(-1 * var(--page-top));
    height: var(--print-header-h);
    z-index: 1;
    pointer-events: none;
  }

  .print-header img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  /* Footer image layer (full bleed) */
  .print-footer{
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: var(--print-footer-h);
    z-index: 10;
    pointer-events: none;
    transform: translateY(0);
  }

  .print-footer img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .print-page{ page-break-after: always; }
  .print-page:last-child{ page-break-after: auto; }

  /* Content should be inside margins (but header stays edge-to-edge) */
  .content-wrap{
    position: relative;
    z-index: 2;

    padding-top: calc(var(--print-header-h) + 2mm);
    padding-left: var(--content-pad-x);
    padding-right: var(--content-pad-x);
    padding-bottom: calc(var(--print-footer-h) + var(--content-pad-bottom));
  }

  @media screen {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page { background: #fff; padding: 10mm; box-shadow: 0 0 10px rgba(0,0,0,.15); }
    .no-print { display: flex; gap: 8px; justify-content: flex-end; margin-bottom: 10px; }
    .btn {
      border: 1px solid #ccc;
      background: #f8f8f8;
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      color: #111;
      font-size: 12px;
    }
  }

  @media print {
    .no-print { display: none !important; }
    .page { padding: 0; box-shadow: none; }
  }

  /* ===== FORM LOOK ===== */
  :root{
    --line: 1px solid #000;
    --row-h: 18px;
    --head-h: 20px;
    --pad-y: 2px;
    --pad-x: 4px;

    /* content offsets */
    --content-pad-x: 10mm;
    --content-pad-bottom: -8mm;
    --print-header-h: 24mm;
    --print-footer-h: 18mm;

    --page-top: 0mm; /* leave as-is unless you’re offsetting */
  }

  .title {
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: .4px;
    margin: 0 0 6px 0;
    text-transform: uppercase;
  }

  .appendix {
    text-align: right;
    font-style: italic;
    font-size: 10px;
    margin-top: -2px;
    margin-bottom: 2px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .form-table td, .form-table th,
  .items-table td, .items-table th,
  .sign-table td, .sign-table th {
    border: var(--line);
    padding: var(--pad-y) var(--pad-x);
    vertical-align: middle;
    word-wrap: break-word;
  }

  .small { font-size: 10px; }
  .center { text-align: center; }
  .right { text-align: right; }
  .bold { font-weight: 700; }
  .upper { text-transform: uppercase; }

  /* ===== Items grid ===== */
  .items-head th {
    font-weight: 700;
    text-align: center;
    height: var(--head-h);
  }

  .items-row td {
    height: var(--row-h);
    vertical-align: middle;
  }

  thead { display: table-header-group; }
  tfoot { display: table-footer-group; }
  tr { page-break-inside: avoid; }

  /* signature area */
  .sig-label {
    font-size: 10px;
    text-align: center;
    margin-top: 2px;
  }
  .sig-line {
    height: 18px;
  }
  .stack-next {
    margin-top: -1px;
  }
  .sig-name {
  margin-top: 10px;
  font-weight: 700;
  text-align: center;
  text-transform: uppercase;
  border-bottom: 1px solid #000;  /* stronger than text-decoration */
  padding-bottom: 2px;
  min-height: 16px;
}
</style>