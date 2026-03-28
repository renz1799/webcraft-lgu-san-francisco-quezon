<style>
    html,
    body {
        margin: 0;
        padding: 0;
        background: #ffffff;
        color: #000;
        font-family: "Times New Roman", Times, serif;
        font-size: 12px;
        line-height: 1.15;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    .gso-property-cards-page {
        width: {{ $paperProfile['width'] ?? '297mm' }};
        min-height: {{ $paperProfile['height'] ?? '210mm' }};
        background: #ffffff;
        color: #000;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .gso-property-cards-page__body {
        padding: 12mm 10mm;
        flex: 1 1 auto;
    }

    .gso-property-cards-corner-label {
        position: absolute;
        top: 12mm;
        right: 10mm;
        font-style: italic;
        font-size: 12px;
    }

    .gso-property-cards-title {
        text-align: center;
        font-weight: 700;
        letter-spacing: 0.2px;
        margin-top: 2mm;
        margin-bottom: 5mm;
    }

    .gso-property-cards-title--pc {
        font-size: 16px;
    }

    .gso-property-cards-title--ics {
        font-size: 14px;
    }

    .gso-property-cards-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 3mm;
    }

    .gso-property-cards-meta-left,
    .gso-property-cards-meta-right {
        font-size: 12px;
    }

    .gso-property-cards-underline {
        display: inline-block;
        border-bottom: 1px solid #000;
        padding: 0 2px;
        min-width: 60mm;
    }

    .gso-property-cards-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        page-break-inside: avoid;
    }

    .gso-property-cards-table th,
    .gso-property-cards-table td {
        border: 1px solid #000;
        padding: 2px 3px;
        vertical-align: middle;
        line-height: 1.1;
        word-break: normal;
        overflow-wrap: break-word;
    }

    .gso-property-cards-table th {
        text-align: center;
        font-weight: 700;
    }

    .gso-property-cards-left {
        text-align: left;
    }

    .gso-property-cards-center {
        text-align: center;
    }

    .gso-property-cards-right {
        text-align: right;
    }

    .gso-property-cards-nowrap {
        white-space: nowrap;
    }

    .gso-property-cards-wrap {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .gso-property-cards-heading-label {
        font-weight: 700;
        white-space: nowrap;
    }

    .gso-property-cards-inline-meta {
        display: grid;
        grid-template-columns: 48mm minmax(0, 1fr);
        align-items: baseline;
        gap: 4px;
    }

    .gso-property-cards-inline-meta__label {
        font-weight: 700;
        white-space: nowrap;
    }

    .gso-property-cards-inline-meta__value {
        min-width: 0;
    }

    .gso-property-cards-grid-row--pc td {
        height: 4mm;
    }

    .gso-property-cards-grid-row--ics td {
        height: 6mm;
    }

    .gso-property-cards-table--ics {
        font-size: 9.5px;
    }

    .gso-property-cards-table--ics th {
        line-height: 1.05;
        padding-top: 1px;
        padding-bottom: 1px;
        font-size: 9px;
    }

    .gso-property-cards-table--ics td {
        padding: 1px 2px;
    }

    .gso-property-cards-table--ics .gso-property-cards-heading-label {
        white-space: normal;
        word-break: normal;
        overflow-wrap: normal;
    }

    .gso-property-cards-table--ics .gso-property-cards-nowrap {
        white-space: normal;
        word-break: normal;
        overflow-wrap: normal;
    }

    .gso-property-cards-empty {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        justify-content: center;
        padding: 18mm;
        text-align: center;
        font-family: Arial, Helvetica, sans-serif;
    }

    .gso-property-cards-empty__eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 10px;
    }

    .gso-property-cards-empty__title {
        margin: 0 0 10px;
        font-size: 24px;
        line-height: 1.2;
    }

    .gso-property-cards-empty__copy {
        margin: 0 auto 18px;
        max-width: 620px;
        color: #64748b;
        font-size: 14px;
    }

    .gso-property-cards-empty__scope {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 16px;
        max-width: 760px;
        margin: 0 auto;
        text-align: left;
        font-size: 13px;
    }
</style>
