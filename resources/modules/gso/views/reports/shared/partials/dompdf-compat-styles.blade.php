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

        [class$="-print-page__body"],
        [class$="-print-body"] {
            display: block !important;
            overflow: hidden !important;
            width: auto !important;
            padding: 3mm 8mm 28mm !important;
        }

        [class$="-print-appendix"] {
            padding-right: 1mm !important;
            font-size: 9px !important;
        }

        [class$="-print-title"] {
            font-size: 14px !important;
            margin-bottom: 3px !important;
        }

        [class$="-print-flow-note"],
        [class$="-print-page-number"] {
            font-size: 9px !important;
        }

        [class$="-print-sheet"] {
            display: table !important;
            width: 100% !important;
        }

        [class$="-print-footer"] {
            position: absolute !important;
            left: 0;
            right: 0;
            bottom: 0;
        }

        [class$="-print-footer-content"] {
            display: block !important;
            min-height: 0 !important;
            padding: 2px 8mm 3px !important;
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
            width: 88% !important;
        }

        [class$="-print-signature-caption"],
        [class$="-print-signature-meta"] {
            font-size: 9px !important;
        }
    </style>
@endif
