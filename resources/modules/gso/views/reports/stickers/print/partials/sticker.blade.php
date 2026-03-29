<div class="sticker-card">
    @if ($showCutGuides)
        <div class="cut-guide"></div>
        <div class="cut-guide-mark h mark-top-left-h"></div>
        <div class="cut-guide-mark v mark-top-left-v"></div>
        <div class="cut-guide-mark h mark-top-right-h"></div>
        <div class="cut-guide-mark v mark-top-right-v"></div>
        <div class="cut-guide-mark h mark-bottom-left-h"></div>
        <div class="cut-guide-mark v mark-bottom-left-v"></div>
        <div class="cut-guide-mark h mark-bottom-right-h"></div>
        <div class="cut-guide-mark v mark-bottom-right-v"></div>
    @endif

    <div class="sticker-field" style="left: 12%; top: 8%; width: 84%; font-size: 4.7cqw; letter-spacing: 0.02em; line-height: 0.98; text-transform: uppercase; white-space: nowrap;">
        {{ $sticker['type_label'] }} NO. {{ $sticker['reference'] }}
    </div>

    <div class="qr-placeholder" aria-label="Asset QR code" style="left: 14.5%; top: 23.8%; width: 17.5%; aspect-ratio: 1 / 1;">
        <img src="{{ $sticker['qr_code_url'] }}" alt="QR code for {{ $sticker['reference'] }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="qr-fallback">QR LINK</div>
    </div>

    <div class="sticker-field" style="left: 35%; top: 24%; width: 60%; font-size: 2.3cqw; min-height: 9.5%;">{{ $sticker['description'] }}</div>
    <div class="sticker-field" style="left: 35%; top: 34%; width: 45.2%; font-size: 2.3cqw;">{{ $sticker['model_number'] }}</div>
    <div class="sticker-field" style="left: 35%; top: 44%; width: 45.2%; font-size: 2.3cqw;">{{ $sticker['serial_number'] }}</div>
    <div class="sticker-field" style="left: 35%; top: 54%; width: 45.2%; font-size: 2.3cqw;">{{ $sticker['acquisition_date'] }}</div>
    <div class="sticker-field" style="left: 37%; top: 64%; width: 45.2%; font-size: 2.3cqw;">{{ $sticker['acquisition_cost'] }}</div>
    <div class="sticker-field" style="left: 35%; top: 74%; width: 36%; font-size: 2.3cqw;">{{ $sticker['person_accountable'] }}</div>

    <div class="barcode" aria-label="Barcode placeholder" style="left: 35%; bottom: 7.2%; width: 30.5%; height: 9.4%;"></div>
    <div class="indicator-strip" aria-hidden="true" style="width: 30%; height: 5%; background: {{ $sticker['indicator_color'] }};"></div>
</div>
