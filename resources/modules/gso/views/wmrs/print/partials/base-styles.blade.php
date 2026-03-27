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

    .gso-wmr-print-page {
        width: {{ $paperProfile['width'] ?? '210mm' }};
        height: {{ $paperProfile['height'] ?? '297mm' }};
        background: #ffffff;
        color: #000;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    .gso-wmr-print-header,
    .gso-wmr-print-footer {
        flex: 0 0 auto;
        width: 100%;
    }

    .gso-wmr-print-header-image,
    .gso-wmr-print-footer-image {
        display: block;
        width: 100%;
        height: auto;
    }

    .gso-wmr-print-header-bar {
        padding: 4mm 10mm 0;
    }

    .gso-wmr-print-appendix {
        text-align: right;
        font-size: 10px;
        font-style: italic;
        margin-bottom: 4px;
    }

    .gso-wmr-print-title {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        margin: 0;
    }

    .gso-wmr-print-flow-note {
        margin-top: 4px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .gso-wmr-print-flow-note-footer {
        text-align: right;
    }

    .gso-wmr-print-body {
        flex: 1 1 auto;
        padding: 4mm 10mm 4mm;
        overflow: hidden;
    }

    .gso-wmr-print-sheet,
    .gso-wmr-print-sign-table,
    .gso-wmr-print-certificate-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .gso-wmr-print-sheet th,
    .gso-wmr-print-sheet td,
    .gso-wmr-print-sign-table td,
    .gso-wmr-print-certificate-table td {
        border: 1px solid #000;
        padding: 2px 4px;
        vertical-align: middle;
        word-break: break-word;
    }

    .gso-wmr-print-meta-row td {
        min-height: 18px;
    }

    .gso-wmr-print-meta-label {
        font-weight: 700;
        white-space: normal;
        font-size: 10px;
        line-height: 1.15;
    }

    .gso-wmr-print-meta-value {
        text-transform: uppercase;
    }

    .gso-wmr-print-items-head th {
        font-weight: 700;
        text-align: center;
        height: 20px;
    }

    .gso-wmr-print-items-row td {
        min-height: 18px;
        vertical-align: top;
        padding-top: 3px;
        padding-bottom: 3px;
    }

    .gso-wmr-print-stack-next {
        margin-top: -1px;
    }

    .gso-wmr-print-center {
        text-align: center;
    }

    .gso-wmr-print-right {
        text-align: right;
    }

    .gso-wmr-print-empty-note {
        text-align: center;
        font-style: italic;
        padding: 6px 10px !important;
    }

    .gso-wmr-print-description-title {
        display: block;
        font-weight: 700;
    }

    .gso-wmr-print-description-detail {
        display: block;
        font-size: 10px;
        white-space: pre-line;
    }

    .gso-wmr-print-total-row td {
        font-weight: 700;
    }

    .gso-wmr-print-sign-title-row td {
        font-weight: 700;
        text-align: center;
        padding: 3mm 2mm;
    }

    .gso-wmr-print-sign-field-cell {
        padding: 2mm 2mm 1.5mm !important;
    }

    .gso-wmr-print-sign-field {
        border-bottom: 1px solid #000;
        min-height: 18px;
        line-height: 18px;
        text-align: center;
        padding: 0 4px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .gso-wmr-print-sign-caption {
        padding: 2mm 4px !important;
        text-align: center;
        font-size: 10px;
        line-height: 1.2;
    }

    .gso-wmr-print-sign-designation {
        padding: 2px 4px 3px !important;
        text-align: center;
        font-size: 10px;
        min-height: 14px;
    }

    .gso-wmr-print-certificate-body {
        padding: 3mm 4mm !important;
    }

    .gso-wmr-print-certificate-copy {
        margin: 0 0 3mm;
        font-size: 10px;
    }

    .gso-wmr-print-certificate-methods {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .gso-wmr-print-certificate-methods td {
        border: none;
        padding: 1mm 0;
        vertical-align: bottom;
        font-size: 10px;
    }

    .gso-wmr-print-certificate-fill {
        border-bottom: 1px solid #000;
        text-align: center;
        padding: 0 2mm !important;
    }

    .gso-wmr-print-certificate-transfer {
        display: inline-block;
        min-width: 62%;
        border-bottom: 1px solid #000;
        padding: 0 2mm;
    }

    .gso-wmr-print-page-number {
        margin-top: 4px;
        text-align: right;
        font-size: 10px;
        color: #000;
    }

    .gso-wmr-print-footer {
        position: relative;
    }

    .gso-wmr-print-footer-content {
        padding: 0 10mm 6mm;
    }

    .gso-wmr-print-footer-image-wrap {
        line-height: 0;
        margin-top: 4px;
        width: 100%;
    }
</style>
