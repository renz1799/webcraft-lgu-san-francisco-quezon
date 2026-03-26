<div class="gso-air-print-footer">
    <div class="gso-air-print-footer-content">
        @if (!empty($continuedToPage))
            <div class="gso-air-print-flow-note gso-air-print-flow-note-footer">
                Continued on Page {{ $continuedToPage }}
            </div>
        @endif
        <div class="gso-air-print-page-number">Page {{ $pageIndex + 1 }} of {{ $totalPages }}</div>
    </div>

    @if (!empty($footerImage ?? null))
        <div class="gso-air-print-footer-image-wrap">
            <img src="{{ $footerImage }}" alt="AIR footer" class="gso-air-print-footer-image">
        </div>
    @endif
</div>
