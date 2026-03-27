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

    .gso-ptr-print-page {
        width: {{ $paperProfile['width'] ?? '210mm' }};
        height: {{ $paperProfile['height'] ?? '297mm' }};
        background: #ffffff;
        color: #000;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    .gso-ptr-print-header,
    .gso-ptr-print-footer {
        flex: 0 0 auto;
        width: 100%;
    }

    .gso-ptr-print-header-image,
    .gso-ptr-print-footer-image {
        display: block;
        width: 100%;
        height: auto;
    }

    .gso-ptr-print-header-bar {
        padding: 4mm 10mm 0;
    }

    .gso-ptr-print-appendix {
        text-align: right;
        font-size: 10px;
        font-style: italic;
        margin-bottom: 4px;
    }

    .gso-ptr-print-title {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        margin: 0;
    }

    .gso-ptr-print-flow-note {
        margin-top: 4px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .gso-ptr-print-flow-note-footer {
        text-align: right;
    }

    .gso-ptr-print-body {
        flex: 1 1 auto;
        padding: 4mm 10mm 4mm;
        overflow: hidden;
    }

    .gso-ptr-print-sheet,
    .gso-ptr-print-reason,
    .gso-ptr-print-signatures {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .gso-ptr-print-sheet th,
    .gso-ptr-print-sheet td,
    .gso-ptr-print-reason td,
    .gso-ptr-print-signatures td {
        border: 1px solid #000;
        padding: 2px 4px;
        vertical-align: middle;
        word-break: break-word;
    }

    .gso-ptr-print-meta-row td {
        min-height: 18px;
    }

    .gso-ptr-print-meta-label {
        font-weight: 700;
        white-space: normal;
        font-size: 10px;
        line-height: 1.15;
    }

    .gso-ptr-print-meta-value {
        text-transform: uppercase;
    }

    .gso-ptr-print-items-head th {
        font-weight: 700;
        text-align: center;
        height: 20px;
    }

    .gso-ptr-print-items-row td {
        min-height: 18px;
        vertical-align: top;
        padding-top: 3px;
        padding-bottom: 3px;
    }

    .gso-ptr-print-stack-next {
        margin-top: -1px;
    }

    .gso-ptr-print-center {
        text-align: center;
    }

    .gso-ptr-print-right {
        text-align: right;
    }

    .gso-ptr-print-empty-note {
        text-align: center;
        font-style: italic;
        padding: 6px 10px !important;
    }

    .gso-ptr-print-description-title {
        font-weight: 700;
    }

    .gso-ptr-print-description-detail {
        font-size: 10px;
        white-space: pre-line;
    }

    .gso-ptr-print-transfer-options {
        padding: 2px 0;
    }

    .gso-ptr-print-type-option {
        display: inline-block;
        min-width: 32%;
        margin: 1mm 0;
        white-space: nowrap;
    }

    .gso-ptr-print-reason-body {
        padding: 2mm 3mm 3mm;
    }

    .gso-ptr-print-reason-lines {
        margin-top: 2mm;
    }

    .gso-ptr-print-reason-line {
        border-bottom: 1px solid #000;
        height: 6mm;
        line-height: 6mm;
        overflow: hidden;
    }

    .gso-ptr-print-signatures td {
        padding: 0;
        vertical-align: middle;
    }

    .gso-ptr-print-signature-label-cell {
        font-weight: 700;
        white-space: nowrap;
        padding: 2.5mm 2mm !important;
    }

    .gso-ptr-print-signature-data-cell {
        padding: 2mm 2mm !important;
    }

    .gso-ptr-print-signature-field {
        border-bottom: 1px solid #000;
        min-height: 18px;
        line-height: 18px;
        text-align: center;
        padding: 0 4px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .gso-ptr-print-page-number {
        margin-top: 4px;
        text-align: right;
        font-size: 10px;
        color: #000;
    }

    .gso-ptr-print-footer {
        position: relative;
    }

    .gso-ptr-print-footer-content {
        padding: 0 10mm 6mm;
    }

    .gso-ptr-print-footer-image-wrap {
        line-height: 0;
        margin-top: 4px;
        width: 100%;
    }
</style>
