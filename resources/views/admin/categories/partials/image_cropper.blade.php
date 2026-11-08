{{-- Crop the category background image in the browser before upload. --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
    #categoryCropModal .crop-stage { max-height: 65vh; background: #111; }
    #categoryCropModal .crop-stage img { display: block; max-width: 100%; }
</style>

<div class="modal fade" id="categoryCropModal" tabindex="-1" aria-labelledby="categoryCropModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold" id="categoryCropModalLabel"><i class="fa-solid fa-crop-simple me-1"></i> Crop Category Image</h6>
                <button type="button" class="btn-close" data-crop-cancel aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <div class="crop-stage rounded-3 overflow-hidden"><img id="categoryCropImage" alt="Image to crop"></div>
                <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                    <span class="small fw-bold text-muted me-1">Ratio:</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Aspect ratio">
                        <button type="button" class="btn btn-outline-dark active" data-crop-ratio="1.6">Card (16:10)</button>
                        <button type="button" class="btn btn-outline-dark" data-crop-ratio="1">Square</button>
                        <button type="button" class="btn btn-outline-dark" data-crop-ratio="0.8">Portrait (4:5)</button>
                        <button type="button" class="btn btn-outline-dark" data-crop-ratio="NaN">Free</button>
                    </div>
                    <div class="btn-group btn-group-sm ms-auto" role="group" aria-label="Adjust">
                        <button type="button" class="btn btn-outline-secondary" data-crop-action="rotate" title="Rotate"><i class="fa-solid fa-rotate-right"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-crop-action="zoomIn" title="Zoom in"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-crop-action="zoomOut" title="Zoom out"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-crop-action="reset" title="Reset"><i class="fa-solid fa-arrows-rotate"></i></button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-light rounded-3" data-crop-cancel>Cancel</button>
                <button type="button" class="btn btn-outline-dark rounded-3" data-crop-original>Use Original</button>
                <button type="button" class="btn btn-dark rounded-3 fw-bold" id="categoryCropApply"><i class="fa-solid fa-check me-1"></i> Crop &amp; Use</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
(function () {
    const modalEl = document.getElementById('categoryCropModal');
    const imgEl = document.getElementById('categoryCropImage');
    if (!modalEl || !imgEl || typeof Cropper === 'undefined') return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    let cropper = null, activeInput = null, sourceFile = null, objectUrl = null, ratio = 1.6, settled = false;

    function setFile(input, file) {
        const dt = new DataTransfer();
        if (file) dt.items.add(file);
        input.files = dt.files;
        input.dataset.cropDone = '1';
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function finish(file) {
        settled = true;
        if (activeInput) setFile(activeInput, file);
        modal.hide();
    }

    // Capture phase on document runs before the input's own change listeners.
    document.addEventListener('change', function (e) {
        const input = e.target;
        if (!(input instanceof HTMLInputElement) || input.name !== 'background_image') return;
        if (input.dataset.cropDone === '1') { delete input.dataset.cropDone; return; }

        const file = input.files && input.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        e.stopImmediatePropagation();
        activeInput = input; sourceFile = file; settled = false;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        imgEl.src = objectUrl;
        modal.show();
    }, true);

    modalEl.addEventListener('shown.bs.modal', function () {
        if (cropper) cropper.destroy();
        cropper = new Cropper(imgEl, { aspectRatio: ratio, viewMode: 1, autoCropArea: 1, responsive: true, background: false });
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        if (!settled && activeInput) { setFile(activeInput, null); }
        if (cropper) { cropper.destroy(); cropper = null; }
        if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
        activeInput = null; sourceFile = null;
    });

    modalEl.querySelectorAll('[data-crop-ratio]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            modalEl.querySelectorAll('[data-crop-ratio]').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            ratio = parseFloat(btn.dataset.cropRatio);
            if (cropper) cropper.setAspectRatio(ratio);
        });
    });

    modalEl.querySelectorAll('[data-crop-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!cropper) return;
            const a = btn.dataset.cropAction;
            if (a === 'rotate') cropper.rotate(90);
            else if (a === 'zoomIn') cropper.zoom(0.1);
            else if (a === 'zoomOut') cropper.zoom(-0.1);
            else cropper.reset();
        });
    });

    modalEl.querySelectorAll('[data-crop-cancel]').forEach(function (b) { b.addEventListener('click', function () { modal.hide(); }); });
    modalEl.querySelector('[data-crop-original]').addEventListener('click', function () { finish(sourceFile); });

    document.getElementById('categoryCropApply').addEventListener('click', function () {
        if (!cropper || !sourceFile) return;
        const btn = this;
        btn.disabled = true;
        const canvas = cropper.getCroppedCanvas({ maxWidth: 2000, maxHeight: 2000, imageSmoothingQuality: 'high', fillColor: '#fff' });
        const type = sourceFile.type === 'image/png' ? 'image/png' : 'image/jpeg';
        canvas.toBlob(function (blob) {
            btn.disabled = false;
            if (!blob) { alert('Could not crop this image. Please try another image.'); return; }
            const base = sourceFile.name.replace(/\.[^.]+$/, '') || 'category';
            finish(new File([blob], base + (type === 'image/png' ? '.png' : '.jpg'), { type: type }));
        }, type, 0.92);
    });
})();
</script>
