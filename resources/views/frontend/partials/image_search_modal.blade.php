<!-- Visual Search Modal -->
<div class="modal fade" id="imageSearchModal" tabindex="-1" aria-labelledby="imageSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white border-0 px-4 py-3 align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-gold-subtle p-2 text-gold">
                        <i class="fa-solid fa-camera fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-serif fw-bold mb-0" id="imageSearchModalLabel">Visual Search by Image / Screenshot</h5>
                        <p class="text-muted small mb-0">Upload any outfit photo or screenshot to find matching products</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light">
                <!-- Dropzone Area -->
                <div id="visualDropzone" class="border-2 border-dashed rounded-4 p-4 text-center bg-white position-relative shadow-sm transition-all" style="cursor: pointer; border-color: #d4af37;">
                    <input type="file" id="visualFileInput" accept="image/*" class="d-none">
                    
                    <div id="dropzoneContent">
                        <div class="mb-3">
                            <span class="d-inline-flex p-3 rounded-circle bg-light text-gold shadow-sm">
                                <i class="fa-solid fa-cloud-arrow-up display-6"></i>
                            </span>
                        </div>
                        <h6 class="font-serif fw-bold mb-1">Drag & Drop Image / Screenshot Here</h6>
                        <p class="text-muted small mb-3">or <span class="text-gold fw-bold text-decoration-underline">Browse File</span> from your phone or computer</p>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <span class="badge bg-light text-muted border px-2 py-1 small"><i class="fa-solid fa-file-image me-1"></i> JPG, PNG, WEBP</span>
                            <span class="badge bg-light text-muted border px-2 py-1 small"><i class="fa-solid fa-mobile-screen me-1"></i> Screenshots Supported</span>
                        </div>
                    </div>

                    <!-- Image Preview & Scanning Overlay -->
                    <div id="visualPreviewArea" class="d-none position-relative text-center">
                        <div class="position-relative d-inline-block rounded-3 overflow-hidden shadow-sm" style="max-height: 220px;">
                            <img id="visualPreviewImg" src="" alt="Uploaded Screenshot" class="img-fluid rounded-3" style="max-height: 220px; object-fit: contain;">
                            <!-- AI Scanner Line Effect -->
                            <div id="scannerLine" class="position-absolute w-100 top-0 start-0 d-none" style="height: 4px; background: linear-gradient(90deg, transparent, #d4af37, #ffffff, #d4af37, transparent); box-shadow: 0 0 12px #d4af37; animation: scanAnimation 1.5s infinite ease-in-out;"></div>
                        </div>
                        <div class="mt-2">
                            <button type="button" id="btnChangeImage" class="btn btn-link btn-sm text-muted text-decoration-none">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Choose Different Image
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="visualSearchLoading" class="d-none text-center py-4">
                    <div class="spinner-border text-gold mb-2" role="status" style="width: 2.5rem; height: 2.5rem; color: #d4af37;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="font-serif fw-bold text-dark mb-1">Scanning Outfit Design & Colors...</h6>
                    <p class="text-muted small mb-0">Finding exact and matching products from Quara Waldrop catalog</p>
                </div>

                <!-- Results Container -->
                <div id="visualSearchResultsArea" class="d-none mt-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h6 class="font-serif fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-wand-magic-sparkles text-gold me-2"></i> Matched Products (<span id="matchCount">0</span>)
                        </h6>
                        <div id="detectedColorsContainer" class="d-flex gap-1 align-items-center"></div>
                    </div>

                    <!-- Products Grid -->
                    <div id="visualProductsGrid" class="row g-3">
                        <!-- Dynamic Matched Product Cards -->
                    </div>
                </div>

                <!-- Error Message Alert -->
                <div id="visualSearchError" class="alert alert-danger rounded-3 mt-3 d-none small"></div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes scanAnimation {
    0% { top: 0%; }
    50% { top: 95%; }
    100% { top: 0%; }
}
#visualDropzone:hover {
    background-color: #fffdf5 !important;
    border-color: #b89327 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('visualDropzone');
    const fileInput = document.getElementById('visualFileInput');
    const dropzoneContent = document.getElementById('dropzoneContent');
    const previewArea = document.getElementById('visualPreviewArea');
    const previewImg = document.getElementById('visualPreviewImg');
    const scannerLine = document.getElementById('scannerLine');
    const btnChange = document.getElementById('btnChangeImage');
    const loadingEl = document.getElementById('visualSearchLoading');
    const resultsArea = document.getElementById('visualSearchResultsArea');
    const productsGrid = document.getElementById('visualProductsGrid');
    const matchCountEl = document.getElementById('matchCount');
    const detectedColorsEl = document.getElementById('detectedColorsContainer');
    const errorEl = document.getElementById('visualSearchError');

    if (!dropzone || !fileInput) return;

    // Open file picker on dropzone click
    dropzone.addEventListener('click', function(e) {
        if (e.target !== btnChange && !btnChange.contains(e.target)) {
            fileInput.click();
        }
    });

    btnChange.addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.click();
    });

    // Drag & drop listeners
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('bg-warning-subtle');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('bg-warning-subtle');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleImageUpload(files[0]);
        }
    });

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleImageUpload(this.files[0]);
        }
    });

    function handleImageUpload(file) {
        if (!file.type.startsWith('image/')) {
            showError('Please upload a valid image or screenshot file.');
            return;
        }

        // Show image preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            dropzoneContent.classList.add('d-none');
            previewArea.classList.remove('d-none');
            scannerLine.classList.remove('d-none');
        };
        reader.readAsDataURL(file);

        // Hide old results & errors
        resultsArea.classList.add('d-none');
        errorEl.classList.add('d-none');
        loadingEl.classList.remove('d-none');

        // Submit via AJAX
        const formData = new FormData();
        formData.append('image', file);

        fetch("{{ route('visual.search') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            loadingEl.classList.add('d-none');
            scannerLine.classList.add('d-none');

            if (data.success) {
                renderResults(data);
            } else {
                showError(data.message || 'Failed to process image.');
            }
        })
        .catch(err => {
            loadingEl.classList.add('d-none');
            scannerLine.classList.add('d-none');
            showError('Server error while analyzing image. Please try again.');
        });
    }

    function renderResults(data) {
        productsGrid.innerHTML = '';
        matchCountEl.innerText = data.total_matches || 0;

        // Detected color tags
        detectedColorsEl.innerHTML = '';
        if (data.detected_colors && data.detected_colors.length > 0) {
            data.detected_colors.forEach(col => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-gold text-dark border rounded-pill px-2 py-1 small fw-normal';
                badge.innerText = col;
                detectedColorsEl.appendChild(badge);
            });
        }

        if (data.products && data.products.length > 0) {
            data.products.forEach(p => {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-3';
                col.innerHTML = `
                    <div class="card h-100 border rounded-3 overflow-hidden shadow-sm hover-shadow transition-all">
                        <div class="position-relative">
                            <img src="${p.image}" class="card-img-top" alt="${p.name}" style="height: 160px; object-fit: cover;">
                            <span class="position-absolute top-0 end-0 m-2 badge bg-success shadow-sm rounded-pill py-1 px-2 small">
                                <i class="fa-solid fa-sparkles me-1"></i> ${p.match_score}% Match
                            </span>
                        </div>
                        <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge bg-light text-muted border small mb-1">${p.category_name}</span>
                                <h6 class="font-serif fw-bold text-dark text-truncate mb-1" style="font-size: 0.85rem;" title="${p.name}">${p.name}</h6>
                                <div class="fw-bold text-gold small">₹${p.final_price}</div>
                            </div>
                            <a href="${p.url}" class="btn btn-dark btn-sm rounded-pill w-100 mt-2 py-1 small">
                                VIEW PRODUCT <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                `;
                productsGrid.appendChild(col);
            });
            resultsArea.classList.remove('d-none');
        } else {
            showError('No matching products found for this outfit image.');
        }
    }

    function showError(msg) {
        errorEl.innerText = msg;
        errorEl.classList.remove('d-none');
    }
});
</script>
