<div class="gso-ris-print-footer">
    <div class="gso-ris-print-footer-content">
        <div class="gso-ris-print-page-number">
            Page {{ $pageIndex + 1 }} of {{ $totalPages }}
        </div>
    </div>

    @if (!empty($footerImage))
        <div class="gso-ris-print-footer-image-wrap">
            <img src="{{ $footerImage }}" alt="RIS Footer" class="gso-ris-print-footer-image">
        </div>
    @endif
</div>
