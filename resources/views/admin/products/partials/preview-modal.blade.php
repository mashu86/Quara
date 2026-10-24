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

                {{-- Photo arrows: shown when the product has several images; downloads use the photo on screen. --}}
                <button type="button" id="productPreviewImagePrev" class="btn btn-dark bg-opacity-75 rounded-circle shadow position-absolute top-50 start-0 translate-middle-y ms-2 p-0 d-none" style="z-index: 11; width: 36px; height: 36px;" onclick="stepPreviewImage(-1)" aria-label="Previous photo" title="Previous photo">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" id="productPreviewImageNext" class="btn btn-dark bg-opacity-75 rounded-circle shadow position-absolute top-50 end-0 translate-middle-y me-2 p-0 d-none" style="z-index: 11; width: 36px; height: 36px;" onclick="stepPreviewImage(1)" aria-label="Next photo" title="Next photo">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
                <span id="productPreviewImageCounter" class="badge bg-dark bg-opacity-75 position-absolute bottom-0 start-50 translate-middle-x mb-2 d-none" style="z-index: 11;"></span>
                
                <!-- Live Instagram Overlay Box Preview; the arrows step through sizes when there are several -->
                <div id="productPreviewInstaWrap" class="position-absolute d-flex align-items-center gap-1" style="z-index: 10;">
                <button type="button" id="productPreviewSizePrev" class="btn btn-light btn-sm rounded-circle shadow p-0 d-none" style="width: 24px; height: 24px; line-height: 1;" onclick="stepPreviewSize(-1)" aria-label="Previous size" title="Previous size">
                    <i class="fa-solid fa-chevron-left" style="font-size: 0.65rem;" aria-hidden="true"></i>
                </button>
                <div id="productPreviewInstaBox" class="rounded-3 shadow text-start d-none" style="padding: 8px 11px; font-size: 0.58rem; line-height: 1.35; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; pointer-events: none; opacity: 0.94; border: 1px solid rgba(255,255,255,0.25);">
                </div>
                <button type="button" id="productPreviewSizeNext" class="btn btn-light btn-sm rounded-circle shadow p-0 d-none" style="width: 24px; height: 24px; line-height: 1;" onclick="stepPreviewSize(1)" aria-label="Next size" title="Next size">
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.65rem;" aria-hidden="true"></i>
                </button>
                </div>
            </div>
            @if($showDetails ?? false)
                <div id="productPreviewDetails" class="p-3 text-start"></div>
            </div>
            @endif
        </div>
    </div>
</div>
