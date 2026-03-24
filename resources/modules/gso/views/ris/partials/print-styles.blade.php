<style>
    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
    }

    body {
        padding: 10mm;
        background: #f5f5f5;
    }

    .page {
        background: #fff;
        padding: 4mm;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.12);
    }

    .print-page {
        page-break-after: always;
        break-after: page;
        margin-bottom: 8mm;
    }

    .print-page:last-of-type {
        page-break-after: auto;
        break-after: auto;
        margin-bottom: 0;
    }

    .title {
        text-align: center;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 0.4px;
        margin: 0 0 6px 0;
        text-transform: uppercase;
    }

    .appendix {
        text-align: right;
        font-style: italic;
        font-size: 10px;
        margin-bottom: 4px;
    }

    .no-print {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-bottom: 12px;
    }

    .btn {
        border: 1px solid #ccc;
        background: #fff;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        color: #111;
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .form-table td,
    .form-table th,
    .items-table td,
    .items-table th,
    .footer-table td,
    .footer-table th {
        border: 1px solid #000;
        padding: 2px 4px;
        vertical-align: middle;
        word-wrap: break-word;
    }

    .header-inner {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .header-inner td {
        border: 0;
        padding: 2px 4px;
        height: 18px;
        vertical-align: middle;
    }

    .header-inner .divider {
        border-right: 1px solid #000;
    }

    .items-head th {
        font-weight: 700;
        text-align: center;
        height: 20px;
    }

    .items-row td {
        height: 18px;
        vertical-align: middle;
    }

    .stack-next {
        margin-top: -1px;
    }

    .small {
        font-size: 10px;
    }

    .center {
        text-align: center;
    }

    .right {
        text-align: right;
    }

    .bold {
        font-weight: 700;
    }

    .upper {
        text-transform: uppercase;
    }

    @media print {
        body {
            padding: 0;
            background: #fff;
        }

        .page {
            box-shadow: none;
            padding: 0;
        }

        .no-print {
            display: none !important;
        }
    }
</style>
