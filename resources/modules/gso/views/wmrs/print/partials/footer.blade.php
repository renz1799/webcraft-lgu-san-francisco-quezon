<div class="gso-wmr-print-footer">
    <div class="gso-wmr-print-footer-content">
        @if (!empty($continuedToPage))
            <div class="gso-wmr-print-flow-note gso-wmr-print-flow-note-footer">
                Continued on Page {{ $continuedToPage }}
            </div>
        @endif
        <div class="gso-wmr-print-page-number">
            Page {{ $pageIndex + 1 }} of {{ $totalPages }}
        </div>
    </div>

    @if (!empty($footerImage))
        <div class="gso-wmr-print-footer-image-wrap">
            <img src="{{ $footerImage }}" alt="WMR Footer" class="gso-wmr-print-footer-image">
        </div>
    @endif
</div>
