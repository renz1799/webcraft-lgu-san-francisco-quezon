@if (($pdfEngine ?? null) === 'dompdf')
    <style>
        [class$="-print-page"] {
            display: block !important;
            overflow: hidden !important;
            position: relative !important;
        }

        [class$="-print-header"],
        [class$="-print-footer"],
        [class$="-print-footer-image-wrap"] {
            display: block !important;
            width: 100% !important;
        }

        [class$="-print-page__body"] {
            display: block !important;
            overflow: hidden !important;
            padding: 3mm 8mm 4mm !important;
        }

        [class$="-print-sheet"] {
            display: table !important;
            width: 100% !important;
        }

        [class$="-print-footer-content"] {
            display: block !important;
            min-height: 0 !important;
            padding: 2px 8mm 4px !important;
        }

        [class$="-print-flow-note--footer"] {
            margin: 0 !important;
            float: left;
        }

        [class$="-print-page-number"] {
            margin: 0 !important;
            float: right;
            text-align: right !important;
        }

        [class$="-print-footer-content"]::after {
            content: "";
            display: block;
            clear: both;
        }

        tr[class$="-print-meta-row"] td {
            height: 14px !important;
        }

        tr[class$="-print-column-head"] th,
        tr[class$="-print-signatures-head"] th {
            height: 16px !important;
        }

        tr[class$="-print-data-row"] td,
        tr[class$="-print-fill-row"] td,
        tr[class$="-print-summary-head"] th,
        tr[class$="-print-summary-values"] td {
            height: 14px !important;
        }

        [class$="-print-signature-cell"] {
            min-height: 18mm !important;
            padding: 4px 6px 6px !important;
        }

        [class$="-print-signature-stack"] {
            display: block !important;
            min-height: auto !important;
        }

        [class$="-print-signature-stack"] > * + * {
            margin-top: 4px;
        }

        [class$="-print-signature-line"] {
            min-height: 16px !important;
            line-height: 16px !important;
            margin: 6px auto 2px !important;
        }

        [class$="-print-signature-caption"],
        [class$="-print-signature-meta"] {
            font-size: 9px !important;
        }
    </style>
@endif
