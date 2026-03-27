<div class="gso-par-print-footer">
    <div class="gso-par-print-footer-content">
        @if (!empty($continuedToPage))
            <div class="gso-par-print-flow-note gso-par-print-flow-note-footer">
                Continued on Page {{ $continuedToPage }}
            </div>
        @endif
        <div class="gso-par-print-page-number">
            Page {{ $pageIndex + 1 }} of {{ $totalPages }}
        </div>
    </div>

    @if (!empty($footerImage))
        <div class="gso-par-print-footer-image-wrap">
            <img src="{{ $footerImage }}" alt="PAR Footer" class="gso-par-print-footer-image">
        </div>
    @endif
</div>
