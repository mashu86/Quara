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
                    <h6 class="font-serif fw-bold text-dark mb-1">Isolating Dress Design & Colors...</h6>
                    <p class="text-muted small mb-0">Ignoring the person and background to find visually similar dresses</p>
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
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Failed to process image.');
            return data;
        })
        .then(async data => {
            if (data.success) {
                await renderResults(data, file);
            } else {
                showError(data.message || 'Failed to process image.');
            }
        })
        .catch(err => {
            showError(err.message || 'Unable to compare this image. Please try again.');
        })
        .finally(() => {
            loadingEl.classList.add('d-none');
            scannerLine.classList.add('d-none');
        });
    }

    async function renderResults(data, uploadedFile) {
        productsGrid.innerHTML = '';
        let matchedProducts = Array.isArray(data.products) ? data.products : [];
        let detectedColors = Array.isArray(data.detected_colors) ? data.detected_colors : [];
        let detectedPattern = data.detected_pattern || null;

        if (data.client_visual_verification) {
            const verified = await verifyProductsVisually(
                uploadedFile,
                matchedProducts,
                Number(data.match_threshold || 70)
            );
            matchedProducts = verified.products;
            detectedColors = verified.detectedColors;
            detectedPattern = verified.detectedPattern;
        }

        matchCountEl.innerText = matchedProducts.length;

        // Detected color tags
        detectedColorsEl.innerHTML = '';
        if (detectedColors.length > 0) {
            detectedColors.forEach(col => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-gold text-dark border rounded-pill px-2 py-1 small fw-normal';
                badge.innerText = col;
                detectedColorsEl.appendChild(badge);
            });
        }
        if (detectedPattern) {
            const patternBadge = document.createElement('span');
            patternBadge.className = 'badge bg-dark text-white border rounded-pill px-2 py-1 small fw-normal';
            patternBadge.innerText = `Pattern: ${detectedPattern}`;
            detectedColorsEl.appendChild(patternBadge);
        }

        if (matchedProducts.length > 0) {
            matchedProducts.forEach(p => {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-3';
                col.innerHTML = `
                    <div class="card h-100 border rounded-3 overflow-hidden shadow-sm hover-shadow transition-all">
                        <div class="position-relative">
                            <img src="${escapeHtml(p.image)}" class="card-img-top" alt="${escapeHtml(p.name)}" style="height: 160px; object-fit: cover;">
                            <span class="position-absolute top-0 end-0 m-2 badge bg-success shadow-sm rounded-pill py-1 px-2 small">
                                <i class="fa-solid fa-sparkles me-1"></i> ${p.match_score}% Match
                            </span>
                        </div>
                        <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge bg-light text-muted border small mb-1">${escapeHtml(p.category_name)}</span>
                                <h6 class="font-serif fw-bold text-dark text-truncate mb-1" style="font-size: 0.85rem;" title="${escapeHtml(p.name)}">${escapeHtml(p.name)}</h6>
                                <div class="fw-bold text-gold small">&#8377;${escapeHtml(p.final_price)}</div>
                            </div>
                            <a href="${escapeHtml(p.url)}" class="btn btn-dark btn-sm rounded-pill w-100 mt-2 py-1 small">
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

    async function verifyProductsVisually(uploadedFile, candidates, threshold) {
        const objectUrl = URL.createObjectURL(uploadedFile);
        const queryImage = await loadImage(objectUrl, true);
        const queryDescriptor = buildVisualDescriptor(queryImage);

        const compared = await Promise.all(candidates.map(async product => {
            try {
                const productImage = await loadImage(product.image);
                const productDescriptor = buildVisualDescriptor(productImage);
                return {...product, match_score: visualSimilarity(queryDescriptor, productDescriptor)};
            } catch (error) {
                return null;
            }
        }));

        return {
            products: compared
                .filter(product => product && product.match_score >= threshold)
                .sort((a, b) => b.match_score - a.match_score)
                .slice(0, 8),
            detectedColors: queryDescriptor.colorNames,
            detectedPattern: queryDescriptor.patternType,
        };
    }

    function loadImage(source, revokeAfterLoad = false) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            image.decoding = 'async';
            try {
                if (new URL(source, window.location.href).origin !== window.location.origin) {
                    image.crossOrigin = 'anonymous';
                }
            } catch (error) {
                // Blob URLs and same-origin product routes need no CORS setting.
            }
            image.onload = () => {
                if (revokeAfterLoad) URL.revokeObjectURL(source);
                resolve(image);
            };
            image.onerror = () => {
                if (revokeAfterLoad) URL.revokeObjectURL(source);
                reject(new Error('A catalog image could not be read.'));
            };
            image.src = source;
        });
    }

    function buildVisualDescriptor(image) {
        const size = 48;
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d', {willReadFrequently: true});
        canvas.width = size;
        canvas.height = size;
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, size, size);

        const scale = Math.min(size / image.naturalWidth, size / image.naturalHeight);
        const width = image.naturalWidth * scale;
        const height = image.naturalHeight * scale;
        context.drawImage(image, (size - width) / 2, (size - height) / 2, width, height);

        const pixels = context.getImageData(0, 0, size, size).data;
        const histogram = new Array(108).fill(0);
        const spatial = new Array(16).fill(null).map(() => ({r: 0, g: 0, b: 0, count: 0}));
        const edges = new Array(16).fill(0);
        const texture = new Array(6).fill(0);
        const orientation = new Array(8).fill(0);
        const localPattern = new Array(16).fill(0);
        const grayscale = new Array(size * size).fill(0);
        const garmentMask = new Uint8Array(size * size);
        const hueTotals = new Array(15).fill(0);
        const cornerIndexes = [0, size - 1, size * (size - 1), size * size - 1];
        const background = cornerIndexes.reduce((sum, index) => {
            const offset = index * 4;
            sum.r += pixels[offset];
            sum.g += pixels[offset + 1];
            sum.b += pixels[offset + 2];
            return sum;
        }, {r: 0, g: 0, b: 0});
        background.r /= 4;
        background.g /= 4;
        background.b /= 4;

        for (let index = 0; index < size * size; index++) {
            const offset = index * 4;
            const r = pixels[offset];
            const g = pixels[offset + 1];
            const b = pixels[offset + 2];
            const x = index % size;
            const y = Math.floor(index / size);
            grayscale[index] = (r * 0.299) + (g * 0.587) + (b * 0.114);

            const backgroundDistance = Math.hypot(r - background.r, g - background.g, b - background.b);
            const nearWhite = r > 245 && g > 245 && b > 245;
            const whiteBackground = background.r > 235 && background.g > 235 && background.b > 235;
            const garmentRegion = x > 5 && x < 42 && y > 7 && y < 46;
            const likelyExposedSkin = isLikelySkin(r, g, b) && (y < 26 || x < 14 || x > 34);
            if (!garmentRegion || likelyExposedSkin || backgroundDistance < 24 || (nearWhite && whiteBackground)) continue;

            const [h, s, v] = rgbToHsv(r, g, b);
            const hueBin = Math.min(11, Math.floor(h * 12));
            const saturationBin = Math.min(2, Math.floor(s * 3));
            const valueBin = Math.min(2, Math.floor(v * 3));
            histogram[(hueBin * 9) + (saturationBin * 3) + valueBin]++;
            if (s < 0.15) {
                hueTotals[v > 0.82 ? 12 : (v < 0.22 ? 14 : 13)]++;
            } else {
                hueTotals[hueBin]++;
            }

            const block = (Math.floor(y / 12) * 4) + Math.floor(x / 12);
            spatial[block].r += r;
            spatial[block].g += g;
            spatial[block].b += b;
            spatial[block].count++;
            garmentMask[index] = 1;
        }

        const histogramTotal = Math.max(1, histogram.reduce((sum, value) => sum + value, 0));
        histogram.forEach((value, index) => histogram[index] = value / histogramTotal);

        for (let y = 1; y < size - 1; y++) {
            for (let x = 1; x < size - 1; x++) {
                const index = (y * size) + x;
                if (!garmentMask[index]) continue;
                const gx = grayscale[index + 1] - grayscale[index - 1];
                const gy = grayscale[index + size] - grayscale[index - size];
                const block = (Math.floor(y / 12) * 4) + Math.floor(x / 12);
                const magnitude = Math.min(255, Math.hypot(gx, gy));
                edges[block] += magnitude;
                texture[Math.min(5, Math.floor(magnitude / 43))]++;
                if (magnitude > 18) {
                    let angle = Math.atan2(gy, gx);
                    if (angle < 0) angle += Math.PI;
                    if (angle >= Math.PI) angle -= Math.PI;
                    orientation[Math.min(7, Math.floor((angle / Math.PI) * 8))]++;
                }

                const neighbours = [
                    index - size - 1, index - size, index - size + 1, index + 1,
                    index + size + 1, index + size, index + size - 1, index - 1
                ];
                if (neighbours.every(neighbour => garmentMask[neighbour])) {
                    let code = 0;
                    neighbours.forEach((neighbour, bit) => {
                        if (grayscale[neighbour] >= grayscale[index]) code |= (1 << bit);
                    });
                    localPattern[Math.min(15, Math.floor(code / 16))]++;
                }
            }
        }

        const maxEdge = Math.max(1, ...edges);
        edges.forEach((value, index) => edges[index] = value / maxEdge);
        const textureTotal = Math.max(1, texture.reduce((sum, value) => sum + value, 0));
        texture.forEach((value, index) => texture[index] = value / textureTotal);
        const orientationTotal = Math.max(1, orientation.reduce((sum, value) => sum + value, 0));
        orientation.forEach((value, index) => orientation[index] = value / orientationTotal);
        const patternTotal = Math.max(1, localPattern.reduce((sum, value) => sum + value, 0));
        localPattern.forEach((value, index) => localPattern[index] = value / patternTotal);

        return {
            histogram,
            spatial: spatial.map(block => block.count > 0
                ? [block.r / block.count, block.g / block.count, block.b / block.count]
                : null),
            occupancy: spatial.map(block => block.count / 144),
            edges,
            texture,
            orientation,
            localPattern,
            patternType: detectPatternType(texture, orientation),
            colorNames: dominantColorNames(hueTotals),
        };
    }

    function visualSimilarity(first, second) {
        const histogramScore = first.histogram.reduce(
            (score, value, index) => score + Math.min(value, second.histogram[index]),
            0
        );

        let spatialScore = 0;
        let spatialCount = 0;
        first.spatial.forEach((color, index) => {
            const other = second.spatial[index];
            if (!color || !other) return;
            spatialScore += 1 - (Math.hypot(
                color[0] - other[0], color[1] - other[1], color[2] - other[2]
            ) / 441.67);
            spatialCount++;
        });
        spatialScore = spatialCount ? spatialScore / spatialCount : 0;

        const edgeDifference = first.edges.reduce(
            (difference, value, index) => difference + Math.abs(value - second.edges[index]),
            0
        ) / first.edges.length;
        const edgeScore = Math.max(0, 1 - edgeDifference);
        const textureScore = first.texture.reduce(
            (score, value, index) => score + Math.min(value, second.texture[index]),
            0
        );
        const orientationScore = first.orientation.reduce(
            (score, value, index) => score + Math.min(value, second.orientation[index]),
            0
        );
        const localPatternScore = first.localPattern.reduce(
            (score, value, index) => score + Math.min(value, second.localPattern[index]),
            0
        );
        const occupancyDifference = first.occupancy.reduce(
            (difference, value, index) => difference + Math.abs(value - second.occupancy[index]),
            0
        ) / first.occupancy.length;
        const silhouetteScore = Math.max(0, 1 - occupancyDifference);

        let score = 100 * (
            (histogramScore * 0.18) +
            (spatialScore * 0.07) +
            (edgeScore * 0.10) +
            (textureScore * 0.24) +
            (orientationScore * 0.18) +
            (localPatternScore * 0.16) +
            (silhouetteScore * 0.07)
        );

        const patternMismatch = first.patternType !== second.patternType;
        const solidVsDesigned = [first.patternType, second.patternType].includes('Solid') && patternMismatch;
        if (solidVsDesigned) score -= 18;
        else if (patternMismatch) score -= 9;

        return Math.max(0, Math.round(score));
    }

    function detectPatternType(texture, orientation) {
        const strongTexture = texture.slice(2).reduce((sum, value) => sum + value, 0);
        const dominantDirection = Math.max(...orientation);
        if (strongTexture < 0.12) return 'Solid';
        if (dominantDirection > 0.34) return 'Striped / Linear';
        if (strongTexture > 0.34) return 'Printed / Embroidered';
        return 'Textured';
    }

    function isLikelySkin(r, g, b) {
        const cb = 128 - (0.168736 * r) - (0.331264 * g) + (0.5 * b);
        const cr = 128 + (0.5 * r) - (0.418688 * g) - (0.081312 * b);
        return r > 60 && r > g && g > b && cb >= 77 && cb <= 127 && cr >= 133 && cr <= 173;
    }

    function rgbToHsv(r, g, b) {
        r /= 255; g /= 255; b /= 255;
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        const delta = max - min;
        let hue = 0;
        if (delta !== 0) {
            if (max === r) hue = ((g - b) / delta) % 6;
            else if (max === g) hue = ((b - r) / delta) + 2;
            else hue = ((r - g) / delta) + 4;
            hue /= 6;
            if (hue < 0) hue += 1;
        }
        return [hue, max === 0 ? 0 : delta / max, max];
    }

    function dominantColorNames(hueTotals) {
        const names = ['Red', 'Orange', 'Yellow', 'Green', 'Green', 'Cyan', 'Blue', 'Blue', 'Purple', 'Purple', 'Pink', 'Red', 'White', 'Grey', 'Black'];
        return hueTotals
            .map((count, index) => ({count, name: names[index]}))
            .sort((a, b) => b.count - a.count)
            .filter(item => item.count > 0)
            .map(item => item.name)
            .filter((name, index, all) => all.indexOf(name) === index)
            .slice(0, 3);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function showError(msg) {
        errorEl.innerText = msg;
        errorEl.classList.remove('d-none');
    }
});
</script>
