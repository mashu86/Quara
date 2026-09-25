<style>
    #productPreviewModal .preview-header-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
    }
    @media (max-width: 576px) {
        #productPreviewModal .preview-header-btn {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            padding: 0 !important;
            font-size: 0.65rem !important;
            border-radius: 50% !important;
            margin-right: 6px !important;
        }
        #productPreviewModal .preview-header-btn i {
            margin: 0 !important;
            font-size: 0.68rem !important;
        }
    }
</style>
<!-- Product Image Preview Modal -->
<div class="modal fade" id="productPreviewModal" tabindex="-1" aria-labelledby="productPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ ($showDetails ?? false) ? 'modal-dialog-scrollable' : '' }}">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                <h5 class="modal-title font-serif fw-bold small text-truncate me-2" id="productPreviewModalLabel" style="min-width: 0;">Product Preview</h5>
                <div class="d-flex align-items-center flex-shrink-0" style="gap: 12px !important;">
                    <a id="productPreviewModalDownloadBtn" href="#" download="" class="btn btn-warning btn-sm rounded-pill px-2.5 px-sm-3 py-1 fw-bold text-dark shadow-sm preview-header-btn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Download Original Image" aria-label="Download Original Image">
                        <i class="fa-solid fa-download me-0 me-sm-1"></i><span class="d-none d-sm-inline"> Download</span>
                    </a>
                    <button id="productPreviewModalInstaBtn" type="button" onclick="downloadInstagramImage()" class="btn btn-sm rounded-pill px-2.5 px-sm-3 py-1 fw-bold text-white shadow-sm preview-header-btn" style="background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); border: none;" title="Download Instagram Image (With Price & Size Overlay)" aria-label="Download Instagram Image">
                        <i class="fa-brands fa-instagram me-0 me-sm-1"></i><span class="d-none d-sm-inline"> Instagram</span>
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            @if($showDetails ?? false)
            <div class="modal-body p-0">
            @endif
            <div class="{{ ($showDetails ?? false) ? '' : 'modal-body' }} p-0 text-center bg-black d-flex align-items-center justify-content-center position-relative overflow-hidden" style="min-height: 280px; max-height: 75vh;">
                <img id="productPreviewModalImg" src="" alt="Product Image" class="img-fluid" style="max-height: 72vh; object-fit: contain;">
                
                <!-- Live Instagram Overlay Box Preview -->
                <div id="productPreviewInstaBox" class="position-absolute p-2.5 rounded-3 shadow text-start d-none" style="z-index: 10; font-size: 0.75rem; line-height: 1.35; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; pointer-events: none; opacity: 0.94; border: 1px solid rgba(255,255,255,0.25);">
                </div>
            </div>
            @if($showDetails ?? false)
                <div id="productPreviewDetails" class="p-3 text-start"></div>
            </div>
            @endif
        </div>
    </div>
</div>
