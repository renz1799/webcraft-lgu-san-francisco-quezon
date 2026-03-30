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

    .gso-air-print-page {
        width: {{ $paperProfile['width'] ?? '210mm' }};
        height: {{ $paperProfile['height'] ?? '297mm' }};
        background: #ffffff;
        color: #000;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    .gso-air-print-header,
    .gso-air-print-footer {
        flex: 0 0 auto;
        width: 100%;
    }

    .gso-air-print-header-image,
    .gso-air-print-footer-image {
        display: block;
        width: 100%;
        height: auto;
    }

    .gso-air-print-header-bar {
        padding: 4mm 10mm 0;
    }

    .gso-air-print-appendix {
        text-align: right;
        font-size: 10px;
        font-style: italic;
        margin-bottom: 4px;
    }

    .gso-air-print-title {
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
    }

    .gso-air-print-flow-note {
        margin-top: 4px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    .gso-air-print-body {
        flex: 1 1 auto;
        padding: 4mm 10mm 6mm;
        overflow: hidden;
    }

    .gso-air-print-sheet {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .gso-air-print-sheet th,
    .gso-air-print-sheet td {
        border: 1px solid #000;
        padding: 4px 6px;
        vertical-align: middle;
        word-break: break-word;
    }

    .gso-air-print-info-row td strong {
        font-weight: 700;
    }

    .gso-air-print-meta-label {
        font-family: Arial, Helvetica, sans-serif;
        font-weight: 700;
        white-space: nowrap;
    }

    .gso-air-print-meta-value {
        font-weight: 600;
    }

    .gso-air-print-meta-inline {
        border-left: 0 !important;
        white-space: nowrap;
    }

    .gso-air-print-meta-inline strong {
        display: inline-block;
        min-width: 56px;
        font-weight: 700;
    }

    .gso-air-print-column-head th {
        text-align: center;
        font-weight: 700;
        height: 20px;
        font-family: Arial, Helvetica, sans-serif;
    }

    .gso-air-print-sheet td {
        height: 18px;
    }

    .gso-air-print-center {
        text-align: center;
    }

    .gso-air-print-message {
        text-align: center;
        font-weight: 700;
        letter-spacing: .4px;
    }

    .gso-air-print-empty-note {
        text-align: center;
        color: #64748b;
        font-style: italic;
    }

    .gso-air-print-acceptance-wrap {
        padding: 0 !important;
    }

    .gso-air-print-signatures {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .gso-air-print-signatures th,
    .gso-air-print-signatures td {
        border: 0;
        padding: 4px 6px;
        word-break: break-word;
    }

    .gso-air-print-acceptance-head th {
        text-align: center;
        font-weight: 700;
        font-style: italic;
        text-transform: uppercase;
        height: 20px;
        font-family: Arial, Helvetica, sans-serif;
        border-bottom: 1px solid #000;
    }

    .gso-air-print-signatures th:first-child,
    .gso-air-print-signatures td:first-child {
        border-right: 1px solid #000;
    }

    .gso-air-print-acceptance-body td {
        vertical-align: top;
        border-bottom: 1px solid #000;
    }

    .gso-air-print-acceptance-cell {
        min-height: 48px;
    }

    .gso-air-print-date-field {
        display: flex;
        align-items: flex-end;
        gap: 6px;
        margin-bottom: 10px;
    }

    .gso-air-print-date-label {
        font-weight: 700;
        white-space: nowrap;
    }

    .gso-air-print-date-line {
        display: inline-block;
        min-width: 160px;
        min-height: 14px;
        border-bottom: 1px solid #000;
        padding: 0 2px 1px;
    }

    .gso-air-print-choice-row {
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .gso-air-print-choice-row + .gso-air-print-choice-row {
        margin-top: 8px;
    }

    .gso-air-print-checkbox {
        flex: 0 0 auto;
        display: inline-block;
        width: 10px;
        height: 10px;
        border: 1px solid #000;
        text-align: center;
        line-height: 9px;
        font-size: 9px;
        font-weight: 700;
        margin-top: 1px;
    }

    .gso-air-print-signoff-row td {
        text-align: center;
        height: 64px;
        vertical-align: bottom;
    }

    .gso-air-print-signoff-cell {
        padding-top: 14px !important;
    }

    .gso-air-print-signature-name {
        margin: 18px auto 0;
        width: 90%;
        min-height: 18px;
        line-height: 18px;
        padding: 0 6px 2px;
        border-bottom: 1px solid #000;
        font-weight: 700;
        text-transform: uppercase;
        font-family: Arial, Helvetica, sans-serif;
    }

    .gso-air-print-signature-role {
        font-size: 10px;
        margin-top: 4px;
    }

    .gso-air-print-footer {
        position: relative;
    }

    .gso-air-print-footer-content {
        padding: 0 10mm 6mm;
    }

    .gso-air-print-page-number {
        margin-top: 4px;
        text-align: right;
        font-size: 10px;
        color: #000;
    }

    .gso-air-print-flow-note-footer {
        text-align: right;
        margin-top: 0;
        margin-bottom: 2px;
    }

    .gso-air-print-footer-image-wrap {
        line-height: 0;
        margin-top: 4px;
        width: 100%;
    }
</style>

@include('gso::reports.shared.partials.dompdf-compat-styles')
