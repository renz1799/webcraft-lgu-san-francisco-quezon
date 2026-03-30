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

    .gso-ics-print-page {
        width: {{ $paperProfile['width'] ?? '210mm' }};
        height: {{ $paperProfile['height'] ?? '297mm' }};
        background: #ffffff;
        color: #000;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    .gso-ics-print-header,
    .gso-ics-print-footer {
        flex: 0 0 auto;
        width: 100%;
    }

    .gso-ics-print-header-image,
    .gso-ics-print-footer-image {
        display: block;
        width: 100%;
        height: auto;
    }

    .gso-ics-print-header-bar {
        padding: 4mm 10mm 0;
    }

    .gso-ics-print-appendix {
        text-align: right;
        font-size: 10px;
        font-style: italic;
        margin-bottom: 4px;
    }

    .gso-ics-print-title {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        margin: 0;
    }

    .gso-ics-print-flow-note {
        margin-top: 4px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .gso-ics-print-flow-note-footer {
        text-align: right;
    }

    .gso-ics-print-body {
        flex: 1 1 auto;
        padding: 4mm 10mm 4mm;
        overflow: hidden;
    }

    .gso-ics-print-sheet,
    .gso-ics-print-signatures {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .gso-ics-print-sheet th,
    .gso-ics-print-sheet td,
    .gso-ics-print-signatures td {
        border: 1px solid #000;
        padding: 2px 4px;
        vertical-align: middle;
        word-break: break-word;
    }

    .gso-ics-print-meta-row td {
        height: 18px;
    }

    .gso-ics-print-meta-label {
        font-weight: 700;
        white-space: nowrap;
    }

    .gso-ics-print-meta-value {
        text-transform: uppercase;
    }

    .gso-ics-print-items-head th {
        font-weight: 700;
        text-align: center;
    }

    .gso-ics-print-items-row td {
        height: 18px;
        vertical-align: middle;
    }

    .gso-ics-print-stack-next {
        margin-top: -1px;
    }

    .gso-ics-print-center {
        text-align: center;
    }

    .gso-ics-print-right {
        text-align: right;
    }

    .gso-ics-print-empty-note {
        text-align: center;
        font-style: italic;
        padding: 6px 10px !important;
    }

    .gso-ics-print-description {
        white-space: pre-line;
    }

    .gso-ics-print-signatures td {
        padding: 0;
        vertical-align: top;
    }

    .gso-ics-print-signature-cell {
        padding: 8px 10px 10px;
        min-height: 52mm;
    }

    .gso-ics-print-signature-title {
        font-size: 10px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .gso-ics-print-signature-line {
        border-bottom: 1px solid #000;
        min-height: 18px;
        line-height: 18px;
        text-align: center;
        margin: 8px auto 2px;
        width: 88%;
        padding: 0 6px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .gso-ics-print-signature-caption {
        text-align: center;
        font-size: 10px;
        margin-bottom: 8px;
    }

    .gso-ics-print-page-number {
        margin-top: 4px;
        text-align: right;
        font-size: 10px;
        color: #000;
    }

    .gso-ics-print-footer {
        position: relative;
    }

    .gso-ics-print-footer-content {
        padding: 0 10mm 6mm;
    }

    .gso-ics-print-footer-image-wrap {
        line-height: 0;
        margin-top: 4px;
        width: 100%;
    }
</style>

@include('gso::reports.shared.partials.dompdf-compat-styles')
