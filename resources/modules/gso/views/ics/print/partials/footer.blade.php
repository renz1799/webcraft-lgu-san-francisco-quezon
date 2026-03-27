<div class="gso-ics-print-footer">
    <div class="gso-ics-print-footer-content">
        @if (!empty($continuedToPage))
            <div class="gso-ics-print-flow-note gso-ics-print-flow-note-footer">
                Continued on Page {{ $continuedToPage }}
            </div>
        @endif
        <div class="gso-ics-print-page-number">
            Page {{ $pageIndex + 1 }} of {{ $totalPages }}
        </div>
    </div>

    @if (!empty($footerImage))
        <div class="gso-ics-print-footer-image-wrap">
            <img src="{{ $footerImage }}" alt="ICS Footer" class="gso-ics-print-footer-image">
        </div>
    @endif
</div>
