<style>
    html, body { margin: 0; padding: 0; background: #ffffff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.2; }
    *, *::before, *::after { box-sizing: border-box; }
    .gso-rrsp-print-page { width: {{ $paperProfile['width'] ?? '297mm' }}; height: {{ $paperProfile['height'] ?? '210mm' }}; background: #ffffff; color: #000; display: flex; flex-direction: column; overflow: hidden; position: relative; }
    .gso-rrsp-print-header, .gso-rrsp-print-footer { flex: 0 0 auto; width: 100%; }
    .gso-rrsp-print-header-image, .gso-rrsp-print-footer-image { display: block; width: 100%; height: auto; }
    .gso-rrsp-print-page__body { flex: 1 1 auto; width: 100%; padding: 4mm 10mm 6mm; display: flex; flex-direction: column; overflow: hidden; }
    .gso-rrsp-print-appendix { text-align: right; font-size: 10px; font-style: italic; margin-bottom: 4px; }
    .gso-rrsp-print-title { margin: 0 0 4px; text-align: center; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
    .gso-rrsp-print-flow-note { margin-bottom: 4px; text-align: center; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .gso-rrsp-print-flow-note--footer { margin: 0; text-align: left; }
    .gso-rrsp-print-sheet { width: 100%; border-collapse: collapse; table-layout: fixed; flex: 0 0 auto; }
    .gso-rrsp-print-sheet th, .gso-rrsp-print-sheet td { border: 1px solid #000; padding: 2px 4px; vertical-align: top; word-break: break-word; }
    .gso-rrsp-print-sheet tr { page-break-inside: avoid; }
    .gso-rrsp-print-sheet th { text-align: center; font-weight: 700; vertical-align: middle; }
    .gso-rrsp-print-meta-row td { height: 16px; vertical-align: middle; }
    .gso-rrsp-print-meta-label { font-weight: 700; white-space: nowrap; }
    .gso-rrsp-print-column-head th { height: 18px; padding-top: 1px; padding-bottom: 1px; }
    .gso-rrsp-print-data-row td, .gso-rrsp-print-fill-row td, .gso-rrsp-print-summary-head th, .gso-rrsp-print-summary-values td { height: 16px; vertical-align: middle; }
    .gso-rrsp-print-summary-head th, .gso-rrsp-print-summary-values td { text-align: center; }
    .gso-rrsp-print-signatures-head th { height: 16px; padding-top: 1px; padding-bottom: 1px; }
    .gso-rrsp-print-signatures-row td { padding: 0; vertical-align: top; }
    .gso-rrsp-print-empty-note { text-align: center; color: #64748b; font-style: italic; }
    .gso-rrsp-print-center { text-align: center; }
    .gso-rrsp-print-right { text-align: right; }
    .gso-rrsp-print-subtext { margin-top: 2px; font-size: 10px; }
    .gso-rrsp-print-signature-cell { min-height: 21mm; padding: 6px 6px 8px; }
    .gso-rrsp-print-signature-line { border-bottom: 1px solid #000; min-height: 18px; line-height: 18px; text-align: center; margin: 10px auto 2px; width: 92%; padding: 0 6px; font-weight: 700; text-transform: uppercase; }
    .gso-rrsp-print-signature-caption { text-align: center; font-size: 10px; }
    .gso-rrsp-print-footer-content { padding: 4px 10mm 6mm; display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 14px; }
    .gso-rrsp-print-page-number { margin-left: auto; text-align: right; font-size: 10px; }
    .gso-rrsp-print-footer-image-wrap { line-height: 0; width: 100%; }
</style>
