<style>
    @php
        $stickerBackgroundUrl = $stickerBackgroundUrl ?? ($sticker['template_url'] ?? asset('print/sticker.jpg'));
    @endphp

    @page {
        size: A4 portrait;
        margin: 10mm;
    }

    .sticker-sheet-page {
        width: 210mm;
        min-height: 297mm;
        margin: 0;
        padding: 0;
        background: #fff;
        overflow: hidden;
        page-break-after: always;
    }

    .sticker-sheet-page:last-child {
        page-break-after: auto;
    }

    .sticker-sheet-content {
        min-height: 297mm;
        padding: 10mm;
        box-sizing: border-box;
        position: relative;
    }

    .sticker-sheet-grid {
        display: block;
    }

    .sticker-sheet-row {
        display: block;
        margin-bottom: 8mm;
        white-space: nowrap;
        font-size: 0;
    }

    .sticker-sheet-row:last-child {
        margin-bottom: 0;
    }

    .sticker-sheet-page-number {
        position: absolute;
        right: 10mm;
        bottom: 8mm;
        font-size: 10px;
        color: #334155;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .sticker-card {
        position: relative;
        display: inline-block;
        vertical-align: top;
        width: 92mm;
        height: 48.25mm;
        margin-right: 6mm;
        background-image: url('{{ $stickerBackgroundUrl }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        overflow: visible;
    }

    .sticker-sheet-row .sticker-card:last-child {
        margin-right: 0;
    }

    .sticker-card--ghost {
        visibility: hidden;
    }

    .sticker-field {
        position: absolute;
        color: #000;
        font-family: "Courier New", Courier, monospace;
        font-weight: 700;
        line-height: 1.02;
        word-break: break-word;
        z-index: 2;
    }

    .cut-guide {
        position: absolute;
        inset: 0;
        border: 1px dashed rgba(55, 65, 81, 0.7);
        box-sizing: border-box;
        pointer-events: none;
        z-index: 4;
    }

    .cut-guide-mark {
        position: absolute;
        background: rgba(55, 65, 81, 0.9);
        pointer-events: none;
        z-index: 4;
    }

    .cut-guide-mark.h {
        width: 4.8%;
        height: 1px;
    }

    .cut-guide-mark.v {
        width: 1px;
        height: 7%;
    }

    .mark-top-left-h { top: 0; left: -2.4%; }
    .mark-top-left-v { top: -3.5%; left: 0; }
    .mark-top-right-h { top: 0; right: -2.4%; }
    .mark-top-right-v { top: -3.5%; right: 0; }
    .mark-bottom-left-h { bottom: 0; left: -2.4%; }
    .mark-bottom-left-v { bottom: -3.5%; left: 0; }
    .mark-bottom-right-h { bottom: 0; right: -2.4%; }
    .mark-bottom-right-v { bottom: -3.5%; right: 0; }

    .qr-placeholder {
        position: absolute;
        background: #fff;
        border: 2px solid #000;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        z-index: 2;
    }

    .qr-placeholder img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        background: #fff;
    }

    .qr-fallback {
        position: absolute;
        inset: 0;
        background: #fff;
        color: #111827;
        font-size: 2mm;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        text-align: center;
        line-height: 16.1mm;
    }

    .barcode {
        position: absolute;
        background:
            repeating-linear-gradient(
                to right,
                #000 0 2px,
                transparent 2px 4px,
                #000 4px 5px,
                transparent 5px 7px,
                #000 7px 10px,
                transparent 10px 12px,
                #000 12px 13px,
                transparent 13px 16px
            );
        z-index: 2;
    }

    .indicator-strip {
        position: absolute;
        right: 0;
        bottom: 0;
        z-index: 2;
    }

    .sticker-empty-state {
        min-height: calc(297mm - 20mm);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 18mm;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: #0f172a;
    }

    .sticker-empty-state__eyebrow {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #9a3412;
        margin-bottom: 10px;
    }

    .sticker-empty-state__title {
        margin: 0 0 10px;
        font-size: 26px;
        line-height: 1.15;
    }

    .sticker-empty-state__copy {
        margin: 0;
        max-width: 380px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    @media print {
        .sticker-sheet-page {
            width: auto;
            min-height: auto;
            box-shadow: none;
            overflow: visible;
            margin: 0;
        }

        .sticker-sheet-content {
            min-height: auto;
            padding: 0;
        }

        .sticker-sheet-page-number {
            right: 0;
            bottom: 0;
        }
    }
</style>
