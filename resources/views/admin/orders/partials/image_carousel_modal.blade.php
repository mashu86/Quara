<!-- Interactive Order Items Image Preview Carousel Modal -->
<div class="modal fade" id="orderImageCarouselModal" tabindex="-1" aria-labelledby="orderImageModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg bg-dark text-white">
            <div class="modal-header border-secondary py-2.5 px-3 px-sm-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark font-monospace fw-bold" id="orderModalItemBadge">Item 1 of 1</span>
                    <h5 class="modal-title font-serif fw-bold fs-6 text-white mb-0 text-truncate" id="orderImageModalTitle" style="max-width: 500px;">
                        Product Name
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3 p-sm-4 text-center position-relative d-flex align-items-center justify-content-center" style="min-height: 380px; background-color: #0d0d0e;">
                <!-- Left Navigation Button -->
                <button type="button" class="btn btn-dark btn-lg rounded-circle position-absolute start-0 ms-2 ms-sm-3 shadow border border-secondary" id="prevProductImgBtn" onclick="navigateOrderModalImage(-1)" title="Previous Product (Left Arrow)" style="z-index: 10; width: 44px; height: 44px; padding: 0;">
                    <i class="fa-solid fa-chevron-left text-warning fs-5"></i>
                </button>

                <!-- Product Display Image -->
                <div class="d-flex flex-column align-items-center justify-content-center w-100">
                    <img id="orderModalProductImg" src="" alt="Product Image" class="img-fluid rounded-3 border border-secondary shadow" style="max-height: 380px; object-fit: contain; background-color: #1a1a1d;">
                    <div id="orderModalReturnNote" class="d-none mt-2 px-3 py-2 rounded-3 bg-danger text-white fw-bold small">This item was returned from this order.</div>
                    
                    <div class="mt-3 p-3 bg-dark bg-opacity-75 rounded-3 border border-secondary w-100 text-start" style="max-width: 600px;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold fs-6 text-warning" id="orderModalProductName">Product Name</div>
                            <span class="badge bg-warning text-dark" id="orderModalProductStatus">Active</span>
                        </div>
                        <div class="small text-light mt-1" id="orderModalProductMeta"></div>
                        <div class="row g-2 mt-2 small" id="orderModalProductDetails">
                            <div class="col-6"><span class="text-secondary">Category</span><div id="orderModalCategory">—</div></div>
                            <div class="col-6"><span class="text-secondary">Catalog price</span><div id="orderModalCatalogPrice">—</div></div>
                            <div class="col-6"><span class="text-secondary">Discount</span><div id="orderModalDiscount">—</div></div>
                            <div class="col-6"><span class="text-secondary">Line total</span><div id="orderModalSubtotal">—</div></div>
                            <div class="col-12 d-none" id="orderModalMeasurementsWrap"><span class="text-secondary">Measurements (inches)</span><div class="d-flex flex-wrap gap-3 mt-1" id="orderModalMeasurements"></div></div>
                            <div class="col-12 d-none" id="orderModalDescriptionWrap"><span class="text-secondary">Description</span><div class="text-light mt-1" id="orderModalDescription" style="white-space: pre-wrap;"></div></div>
                            <div class="col-12 d-none" id="orderModalComboWrap"><span class="badge bg-info text-dark">Combo offer</span></div>
                        </div>
                    </div>
                </div>

                <!-- Right Navigation Button -->
                <button type="button" class="btn btn-dark btn-lg rounded-circle position-absolute end-0 me-2 me-sm-3 shadow border border-secondary" id="nextProductImgBtn" onclick="navigateOrderModalImage(1)" title="Next Product (Right Arrow)" style="z-index: 10; width: 44px; height: 44px; padding: 0;">
                    <i class="fa-solid fa-chevron-right text-warning fs-5"></i>
                </button>
            </div>

            <div class="modal-footer border-secondary bg-dark rounded-bottom-4 justify-content-between px-3 px-sm-4 py-2">
                <div class="small text-muted" id="orderModalFooterNote">
                    <i class="fa-solid fa-keyboard text-warning me-1"></i> Click Left / Right buttons or use Arrow keys to browse products
                </div>
                <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentModalOrderItems = [];
let currentModalItemIndex = 0;

function openOrderImageModal(itemsJson, startIndex) {
    try {
        currentModalOrderItems = typeof itemsJson === 'string' ? JSON.parse(itemsJson) : itemsJson;
        currentModalItemIndex = startIndex || 0;
        
        if (!currentModalOrderItems || currentModalOrderItems.length === 0) return;

        updateOrderModalDisplay();

        const modalEl = document.getElementById('orderImageCarouselModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    } catch (e) {
        console.error('Error opening order image modal:', e);
    }
}

function updateOrderModalDisplay() {
    if (!currentModalOrderItems || currentModalOrderItems.length === 0) return;

    if (currentModalItemIndex < 0) currentModalItemIndex = currentModalOrderItems.length - 1;
    if (currentModalItemIndex >= currentModalOrderItems.length) currentModalItemIndex = 0;

    const item = currentModalOrderItems[currentModalItemIndex];
    const money = value => `₹${(parseFloat(value) || 0).toFixed(2)}`;

    document.getElementById('orderModalItemBadge').innerText = `Item ${currentModalItemIndex + 1} of ${currentModalOrderItems.length}`;
    document.getElementById('orderImageModalTitle').innerText = item.product_name || 'Product Detail';
    document.getElementById('orderModalProductName').innerText = item.product_name || 'Product Detail';
    document.getElementById('orderModalProductImg').src = item.image_url || '/images/placeholder.jpg';
    const productImage = document.getElementById('orderModalProductImg');
    productImage.classList.toggle('border-danger', !!item.is_returned);
    productImage.classList.toggle('border-secondary', !item.is_returned);
    productImage.style.setProperty('border-width', item.is_returned ? '5px' : '1px', 'important');
    productImage.style.setProperty('border-style', 'solid', 'important');
    productImage.style.setProperty('border-color', item.is_returned ? '#dc3545' : '#6c757d', 'important');
    document.getElementById('orderModalReturnNote').classList.toggle('d-none', !item.is_returned);
    document.getElementById('orderModalProductMeta').innerText = `Size: ${item.size || 'N/A'} • Quantity: ${item.quantity || 1} Pcs • Unit price: ${money(item.final_unit_price || item.unit_price)}`;
    document.getElementById('orderModalCategory').innerText = item.category || '—';
    document.getElementById('orderModalCatalogPrice').innerText = item.catalog_price ? money(item.catalog_price) : '—';
    document.getElementById('orderModalDiscount').innerText = money(item.discount_amount);
    document.getElementById('orderModalSubtotal').innerText = money(item.subtotal || (parseFloat(item.final_unit_price || item.unit_price || 0) * (parseInt(item.quantity, 10) || 1)));
    document.getElementById('orderModalProductStatus').innerText = item.item_status || 'Active';
    const measurementWrap = document.getElementById('orderModalMeasurementsWrap');
    const measurementList = document.getElementById('orderModalMeasurements');
    measurementList.replaceChildren();
    Object.entries(item.measurements || {}).forEach(([label, value]) => {
        const measurement = document.createElement('span');
        measurement.innerText = `${label}: ${value}`;
        measurementList.appendChild(measurement);
    });
    measurementWrap.classList.toggle('d-none', measurementList.childElementCount === 0);
    const descriptionWrap = document.getElementById('orderModalDescriptionWrap');
    descriptionWrap.classList.toggle('d-none', !item.description);
    document.getElementById('orderModalDescription').innerText = item.description || '';
    document.getElementById('orderModalComboWrap').classList.toggle('d-none', !item.is_combo_offer);

    const prevBtn = document.getElementById('prevProductImgBtn');
    const nextBtn = document.getElementById('nextProductImgBtn');

    if (currentModalOrderItems.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
    } else {
        if (prevBtn) prevBtn.style.display = 'flex';
        if (nextBtn) nextBtn.style.display = 'flex';
        if (prevBtn) prevBtn.style.alignItems = 'center';
        if (prevBtn) prevBtn.style.justifyContent = 'center';
        if (nextBtn) nextBtn.style.alignItems = 'center';
        if (nextBtn) nextBtn.style.justifyContent = 'center';
    }
}

function navigateOrderModalImage(direction) {
    currentModalItemIndex += direction;
    updateOrderModalDisplay();
}

document.addEventListener('keydown', function(e) {
    const modalEl = document.getElementById('orderImageCarouselModal');
    if (modalEl && modalEl.classList.contains('show')) {
        if (e.key === 'ArrowLeft') {
            navigateOrderModalImage(-1);
        } else if (e.key === 'ArrowRight') {
            navigateOrderModalImage(1);
        }
    }
});
</script>
