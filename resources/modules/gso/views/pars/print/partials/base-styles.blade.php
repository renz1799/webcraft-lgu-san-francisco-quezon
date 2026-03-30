<style>
    html,
    body {
        margin: 0;
        padding: 0;
        background: #ffffff;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        line-height: 1.2;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    .gso-par-print-page {
        width: {{ $paperProfile['width'] ?? '210mm' }};
        height: {{ $paperProfile['height'] ?? '297mm' }};
        background: #ffffff;
        color: #000;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    .gso-par-print-header,
    .gso-par-print-footer {
        flex: 0 0 auto;
        width: 100%;
    }

    .gso-par-print-header-image,
    .gso-par-print-footer-image {
        display: block;
        width: 100%;
        height: auto;
    }

    .gso-par-print-header-bar {
        padding: 4mm 10mm 0;
    }

    .gso-par-print-appendix {
        text-align: right;
        font-size: 10px;
        font-style: italic;
        margin-bottom: 4px;
    }

    .gso-par-print-title {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        margin: 0;
    }

    .gso-par-print-flow-note {
        margin-top: 4px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .gso-par-print-flow-note-footer {
        text-align: right;
    }

    .gso-par-print-body {
        flex: 1 1 auto;
        padding: 4mm 10mm 4mm;
        overflow: hidden;
    }

    .gso-par-print-sheet,
    .gso-par-print-signatures {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .gso-par-print-sheet th,
    .gso-par-print-sheet td,
    .gso-par-print-signatures td {
        border: 1px solid #000;
        padding: 2px 4px;
        vertical-align: middle;
        word-break: break-word;
    }

    .gso-par-print-meta-row td {
        height: 18px;
    }

    .gso-par-print-meta-label {
        font-weight: 700;
        white-space: nowrap;
    }

    .gso-par-print-meta-value {
        text-transform: uppercase;
    }

    .gso-par-print-items-head th {
        font-weight: 700;
        text-align: center;
        height: 20px;
    }

    .gso-par-print-items-row td {
        height: 18px;
        vertical-align: middle;
    }

    .gso-par-print-stack-next {
        margin-top: -1px;
    }

    .gso-par-print-center {
        text-align: center;
    }

    .gso-par-print-right {
        text-align: right;
    }

    .gso-par-print-empty-note {
        text-align: center;
        font-style: italic;
        padding: 6px 10px !important;
    }

    .gso-par-print-signatures td {
        padding: 0;
        vertical-align: top;
    }

    .gso-par-print-signature-cell {
        padding: 8px 10px 10px;
        min-height: 56mm;
    }

    .gso-par-print-signature-title {
        font-size: 10px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .gso-par-print-signature-line {
        border-bottom: 1px solid #000;
        min-height: 18px;
        line-height: 18px;
        text-align: center;
        margin: 8px auto 2px;
        width: 90%;
        padding: 0 6px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .gso-par-print-signature-caption {
        text-align: center;
        font-size: 10px;
        margin-bottom: 8px;
    }

    .gso-par-print-page-number {
        margin-top: 4px;
        text-align: right;
        font-size: 10px;
        color: #000;
    }

    .gso-par-print-footer {
        position: relative;
    }

    .gso-par-print-footer-content {
        padding: 0 10mm 6mm;
    }

    .gso-par-print-footer-image-wrap {
        line-height: 0;
        margin-top: 4px;
        width: 100%;
    }
</style>

@include('gso::reports.shared.partials.dompdf-compat-styles')
