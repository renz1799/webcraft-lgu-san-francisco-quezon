<div class="gso-ptr-print-footer">
    <div class="gso-ptr-print-footer-content">
        @if (!empty($continuedToPage))
            <div class="gso-ptr-print-flow-note gso-ptr-print-flow-note-footer">
                Continued on Page {{ $continuedToPage }}
            </div>
        @endif
        <div class="gso-ptr-print-page-number">
            Page {{ $pageIndex + 1 }} of {{ $totalPages }}
        </div>
    </div>

    @if (!empty($footerImage))
        <div class="gso-ptr-print-footer-image-wrap">
            <img src="{{ $footerImage }}" alt="PTR Footer" class="gso-ptr-print-footer-image">
        </div>
    @endif
</div>
