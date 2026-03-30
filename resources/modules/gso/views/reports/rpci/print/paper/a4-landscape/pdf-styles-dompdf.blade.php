@include('gso::reports.rpci.print.paper.a4-landscape.pdf-styles', [
    'paperProfile' => $paperProfile,
])

<style>
    .gso-rpci-print-page {
        position: relative !important;
    }

    .gso-rpci-print-page__body {
        width: auto !important;
        padding: 3mm 9mm 28mm !important;
    }

    .gso-rpci-print-appendix {
        padding-right: 1mm;
        font-size: 9px !important;
    }

    .gso-rpci-print-title {
        font-size: 14px !important;
        margin-bottom: 3px !important;
    }

    .gso-rpci-print-flow-note,
    .gso-rpci-print-page-number,
    .gso-rpci-print-signature-caption {
        font-size: 9px !important;
    }

    .gso-rpci-print-sheet th,
    .gso-rpci-print-sheet td {
        padding: 1px 3px !important;
        font-size: 10px !important;
    }

    .gso-rpci-print-meta-row td,
    .gso-rpci-print-data-row td,
    .gso-rpci-print-fill-row td,
    .gso-rpci-print-summary-head th,
    .gso-rpci-print-summary-values td,
    .gso-rpci-print-column-head th,
    .gso-rpci-print-signatures-head th {
        height: 13px !important;
    }

    .gso-rpci-print-signature-line {
        width: 88% !important;
    }

    .gso-rpci-print-footer {
        position: absolute !important;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .gso-rpci-print-footer-content {
        padding: 2px 9mm 3px !important;
    }
</style>
