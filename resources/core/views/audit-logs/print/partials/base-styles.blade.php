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

    .audit-print-page {
        width: {{ $paperProfile['width'] ?? '210mm' }};
        height: {{ $paperProfile['height'] ?? '297mm' }};
        background: #fff;
        color: #000;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
        font-family: Arial, Helvetica, sans-serif;
        line-height: 1.2;
    }

    .audit-print-header,
    .audit-print-footer {
        width: 100%;
        line-height: 0;
        flex: 0 0 auto;
        position: relative;
    }

    .audit-print-header-image,
    .audit-print-footer-image {
        display: block;
        width: 100%;
        height: auto;
    }

    .audit-print-body {
        flex: 1 1 auto;
        padding: 8mm 12mm 8mm;
        overflow: hidden;
    }

    .audit-print-title {
        font-size: 14px;
        font-weight: 700;
        margin: 0 0 10px;
        line-height: 1.2;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .audit-print-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .audit-print-page-number {
        position: absolute;
        bottom: 22mm;
        right: 10mm;
        font-size: 10px;
        font-weight: 700;
        color: #000;
        background: rgba(255, 255, 255, 0.85);
        padding: 2px 6px;
        border-radius: 4px;
        line-height: 1.2;
    }

    .audit-print-meta {
        margin-bottom: 10px;
    }

    .audit-print-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 4px 12px;
        font-size: 10px;
        margin-bottom: 12px;
        line-height: 1.2;
    }

    .audit-print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        table-layout: fixed;
        line-height: 1.2;
    }

    .audit-print-table th,
    .audit-print-table td {
        border: 1px solid #000;
        padding: 5px 6px;
        text-align: left;
        vertical-align: top;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .audit-print-table th {
        background: #f3f4f6;
        font-weight: 700;
    }

    .audit-print-empty {
        text-align: center;
        padding: 12px;
    }

    .audit-print-table th:nth-child(1),
    .audit-print-table td:nth-child(1) {
        width: 16%;
    }

    .audit-print-table th:nth-child(2),
    .audit-print-table td:nth-child(2) {
        width: 12%;
    }

    .audit-print-table th:nth-child(3),
    .audit-print-table td:nth-child(3) {
        width: 14%;
    }

    .audit-print-table th:nth-child(4),
    .audit-print-table td:nth-child(4) {
        width: 30%;
    }

    .audit-print-table th:nth-child(5),
    .audit-print-table td:nth-child(5) {
        width: 14%;
    }

    .audit-print-table th:nth-child(6),
    .audit-print-table td:nth-child(6) {
        width: 14%;
    }
</style>