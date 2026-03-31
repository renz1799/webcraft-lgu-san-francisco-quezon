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

    .gso-ris-print-page {
        width: {{ $paperProfile['width'] ?? '210mm' }};
        height: {{ $paperProfile['height'] ?? '297mm' }};
        background: #ffffff;
        color: #000;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    .gso-ris-print-header,
    .gso-ris-print-footer {
        flex: 0 0 auto;
        width: 100%;
    }

    .gso-ris-print-header-image,
    .gso-ris-print-footer-image {
        display: block;
        width: 100%;
        height: auto;
    }

    .gso-ris-print-header-bar {
        padding: 4mm 10mm 0;
    }

    .gso-ris-print-appendix {
        text-align: right;
        font-size: 10px;
        font-style: italic;
        margin-bottom: 4px;
    }

    .gso-ris-print-title {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
    }

    .gso-ris-print-flow-note {
        margin-top: 4px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .gso-ris-print-flow-note-footer {
        text-align: right;
    }

    .gso-ris-print-body {
        flex: 1 1 auto;
        padding: 4mm 10mm 6mm;
        overflow: hidden;
    }

    .gso-ris-print-sheet,
    .gso-ris-print-header-inner,
    .gso-ris-print-signatures {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .gso-ris-print-sheet th,
    .gso-ris-print-sheet td,
    .gso-ris-print-signatures td {
        border: 1px solid #000;
        padding: 2px 4px;
        vertical-align: middle;
        word-break: break-word;
    }

    .gso-ris-print-meta-row td {
        height: 18px;
    }

    .gso-ris-print-meta-label {
        font-weight: 700;
        white-space: nowrap;
        padding-right: 6px !important;
    }

    .gso-ris-print-meta-value {
        font-weight: 600;
        padding-left: 5px !important;
    }

    .gso-ris-print-meta-label--right {
        text-align: left;
    }

    .gso-ris-print-meta-value--right {
        white-space: nowrap;
    }

    .gso-ris-print-meta-date {
        display: inline-block;
        margin-left: 10px;
        font-size: 10px;
        font-weight: 500;
        white-space: nowrap;
    }

    .gso-ris-print-items-head th {
        font-weight: 700;
        text-align: center;
        height: 20px;
    }

    .gso-ris-print-items-row td {
        height: 18px;
        vertical-align: middle;
    }

    .gso-ris-print-stack-next {
        margin-top: -1px;
    }

    .gso-ris-print-small {
        font-size: 10px;
    }

    .gso-ris-print-col-head--compact,
    .gso-ris-print-col-cell--compact {
        padding-left: 3px !important;
        padding-right: 3px !important;
        white-space: nowrap;
    }

    .gso-ris-print-col-head--compact {
        font-size: 10px;
    }

    .gso-ris-print-cell--numeric {
        font-variant-numeric: tabular-nums;
    }

    .gso-ris-print-center {
        text-align: center;
    }

    .gso-ris-print-right {
        text-align: right;
    }

    .gso-ris-print-bold {
        font-weight: 700;
    }

    .gso-ris-print-upper {
        text-transform: uppercase;
    }

    .gso-ris-print-signatures td {
        padding: 0;
        vertical-align: top;
    }

    .gso-ris-print-signature-cell {
        padding: 8px 8px 10px;
        min-height: 40mm;
    }

    .gso-ris-print-signature-title {
        margin-bottom: 12px;
        text-align: center;
        font-weight: 700;
    }

    .gso-ris-print-signature-line {
        border-bottom: 1px solid #000;
        min-height: 18px;
        line-height: 18px;
        text-align: center;
        margin: 10px auto 2px;
        width: 90%;
        padding: 0 6px;
        font-weight: 700;
    }

    .gso-ris-print-signature-caption {
        text-align: center;
        font-size: 10px;
        margin-bottom: 8px;
    }

    .gso-ris-print-page-number {
        margin-top: 4px;
        text-align: right;
        font-size: 10px;
        color: #000;
    }

    .gso-ris-print-footer {
        position: relative;
    }

    .gso-ris-print-footer-content {
        padding: 0 10mm 6mm;
    }

    .gso-ris-print-footer-image-wrap {
        line-height: 0;
        margin-top: 4px;
        width: 100%;
    }
</style>

@include('gso::reports.shared.partials.dompdf-compat-styles')
