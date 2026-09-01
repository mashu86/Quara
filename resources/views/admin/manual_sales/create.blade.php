@extends('layouts.admin')

@section('title', 'Record Manual Sale - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .back-offline-btn {
            padding: 0.25rem 0.55rem !important;
            font-size: 0.82rem !important;
            border-radius: 8px !important;
        }
        .page-header-title {
            font-size: 1.15rem !important;
        }
        .page-header-subtitle {
            font-size: 0.72rem !important;
        }
        .card-body.p-4 {
            padding: 1rem 0.85rem !important;
        }
        .card-body h5 {
            font-size: 0.92rem !important;
            margin-bottom: 0.75rem !important;
        }
        .form-label {
            font-size: 0.78rem !important;
            margin-bottom: 0.25rem !important;
        }
        .form-control, .form-select {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        .submit-sale-btn {
            padding: 0.65rem 1rem !important;
            font-size: 0.82rem !important;
            margin-top: 1rem !important;
        }
        .add-product-btn {
            padding: 0.35rem 0.65rem !important;
            font-size: 0.76rem !important;
            border-radius: 8px !important;
        }
        .remove-item-btn {
            padding: 0.15rem 0.45rem !important;
            font-size: 0.72rem !important;
        }
        .product-item-card {
            padding: 0.75rem !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 page-header-title">Record New Offline Sale</h3>
        <p class="text-muted small mb-0 page-header-subtitle">Record a walk-in, phone, or direct customer purchase (Multiple products supported).</p>
    </div>
    <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-dark rounded-3 px-2.5 px-md-3 py-1.5 py-md-2 fw-bold shadow-sm back-offline-btn" title="Back to Offline Sales">
        &larr;<span class="d-none d-md-inline"> Back to Offline Sales</span>
    </a>
</div>

<form action="{{ route('admin.manual-sales.store') }}" method="POST" id="manualSaleForm">
    @csrf
    <div class="row g-4">
        <!-- Left Side: Product Selection & Pricing -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-boxes-packing text-warning me-2"></i> Select Products & Sizes</h5>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="hideOutOfStockSwitch" onchange="filterProductsByCategory()">
                            <label class="form-check-label small text-muted fw-semibold" for="hideOutOfStockSwitch">Hide Out of Stock</label>
                        </div>
                    </div>

                    <!-- Category Filter Checkboxes -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Filter Products by Category</label>
                            <div>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none me-2 fw-semibold" onclick="selectAllCategories(true)">Select All</button>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted fw-semibold" onclick="selectAllCategories(false)">Clear All</button>
                            </div>
                        </div>
                        <div class="p-3 border rounded-3 bg-light" style="max-height: 130px; overflow-y: auto;">
                            <div class="row g-2">
                                @foreach($categories as $cat)
                                    <div class="col-6 col-sm-4">
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" value="{{ $cat->id }}" id="cat_{{ $cat->id }}" onchange="filterProductsByCategory()">
                                            <label class="form-check-label text-truncate w-100 small fw-medium" for="cat_{{ $cat->id }}" title="{{ $cat->name }}">
                                                {{ $cat->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Product Items Container -->
                    <div id="productItemsContainer">
                        <!-- Added dynamically by JS -->
                    </div>

                    <!-- Add Another Product Button -->
                    <div class="mb-3 mb-md-4">
                        <button type="button" class="btn btn-outline-warning text-dark fw-bold border-2 w-100 py-2 py-md-2.5 rounded-3 shadow-sm add-product-btn" onclick="addProductRow()" style="border-style: dashed !important;">
                            <i class="fa-solid fa-plus me-1"></i> ADD ANOTHER PRODUCT
                        </button>
                    </div>

                    <!-- Order Summary & Common Delivery Charge -->
                    <div class="p-3 bg-light rounded-4 border">
                        <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-calculator text-warning me-2"></i> Order Pricing Summary</h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Common Delivery Charge (₹)</label>
                                <input type="number" step="0.01" name="delivery_charge" id="deliveryChargeInput" class="form-control rounded-3" value="0.00" min="0" oninput="calcTotals()">
                                <div class="form-text small text-muted">Order-wide shipping fee (Leave 0.00 for counter sales).</div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="small text-muted mb-1">Items Subtotal: <strong id="subtotalDisplay" class="text-dark">₹0.00</strong></div>
                                <div class="small text-muted mb-1">Delivery Charge: <strong id="deliveryDisplay" class="text-dark">₹0.00</strong></div>
                                <div class="fw-bold text-dark fs-6 mt-2">Grand Total Amount:</div>
                                <div class="fs-2 fw-bold text-warning" id="grandTotalDisplay">₹0.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details Card -->
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-credit-card text-warning me-2"></i> Payment Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                                <option value="bank_transfer">Bank Transfer / Card</option>
                                <option value="cash">Cash Payment</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select rounded-3" required>
                                <option value="paid">Paid (Fully Received)</option>
                                <option value="pending">Pending (Pay Later)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Notes / Special Instructions</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Walk-in customer discount / Counter sale receipt #42"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Order Summary & Customer Details -->
        <div class="col-lg-5">
            <!-- Selected Items Order Summary Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-receipt text-warning me-2"></i> Selected Items (<span id="summaryItemCount">0</span>)
                    </h5>
                    <div id="orderItemsSummaryList">
                        <div class="p-3 bg-light rounded-3 text-muted text-center" id="emptySummaryPlaceholder">
                            <i class="fa-solid fa-basket-shopping fa-2x mb-2 text-secondary opacity-50"></i>
                            <p class="mb-0 small fw-bold">No products selected yet. Select products on the left to build the order.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Details Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-user text-warning me-2"></i> Customer Details</h5>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Customer Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control rounded-3" placeholder="e.g. Anjali Nair" value="{{ old('customer_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mobile Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="customer_phone" class="form-control rounded-3" placeholder="e.g. 9876543210" value="{{ old('customer_phone') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address (Optional)</label>
                        <input type="email" name="customer_email" class="form-control rounded-3" placeholder="customer@gmail.com" value="{{ old('customer_email') }}">
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-2">Delivery Address (Optional for shipped orders)</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <input type="text" name="house_building" class="form-control rounded-3 mb-2" placeholder="House / Building Name">
                        </div>
                        <div class="col-6">
                            <input type="text" name="street" class="form-control rounded-3 mb-2" placeholder="Street / Area">
                        </div>
                        <div class="col-6">
                            <input type="text" name="city" class="form-control rounded-3 mb-2" placeholder="City / Town" value="Naduvil">
                        </div>
                        <div class="col-6">
                            <input type="text" name="district" class="form-control rounded-3 mb-2" placeholder="District" value="Kannur">
                        </div>
                        <div class="col-6">
                            <input type="text" name="pin_code" class="form-control rounded-3 mb-2" placeholder="PIN Code" value="670582">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-3 mt-4 shadow-sm submit-sale-btn">RECORD MANUAL SALE</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    // Global Products Array from Laravel
    const productsData = [
        @foreach($products as $prod)
            @php
                $catIds = array_values(array_filter(array_unique(array_merge(
                    [$prod->category_id],
                    $prod->categories->pluck('id')->toArray()
                ))));
                $totalStock = $prod->is_out_of_stock ? 0 : (int) $prod->sizes->sum('stock');
                $isOut = $totalStock <= 0;
            @endphp
            {
                id: {{ $prod->id }},
                name: @json($prod->name),
                price: {{ (float) $prod->final_price }},
                image: @json($prod->primary_image_url),
                categories: @json($catIds),
                sizes: @json($prod->sizes),
                isOut: {{ $isOut ? 'true' : 'false' }}
            },
        @endforeach
    ];

    let rowIndexCounter = 0;

    function buildProductOptionsHtml() {
        let html = '';
        productsData.forEach(prod => {
            const outText = prod.isOut ? ' - ⚠️ [OUT OF STOCK]' : '';
            const disabledAttr = prod.isOut ? 'disabled' : '';
            html += `<option value="${prod.id}" data-price="${prod.price}" data-image="${prod.image}" data-categories='${JSON.stringify(prod.categories)}' data-out-of-stock="${prod.isOut ? '1' : '0'}" ${disabledAttr}>${prod.name} (Price: ₹${prod.price.toFixed(2)})${outText}</option>`;
        });
        return html;
    }

    function addProductRow() {
        const container = document.getElementById('productItemsContainer');
        const index = rowIndexCounter++;

        const cardHtml = `
            <div class="product-item-card border rounded-3 p-3 mb-3 bg-white position-relative shadow-sm" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <span class="badge bg-warning text-dark fw-bold px-2.5 py-1.5 fs-7 item-badge">
                        <i class="fa-solid fa-box me-1"></i> Product #${container.children.length + 1}
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 fw-bold remove-item-btn" onclick="removeProductRow(this)" title="Remove item">
                        <i class="fa-solid fa-trash me-1"></i> Remove Item
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold small mb-1">Select Product <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="product-inline-thumb-container d-none flex-shrink-0">
                                <img class="product-inline-thumb rounded-3 border shadow-sm" src="" alt="Thumb" style="width: 48px; height: 55px; object-fit: cover; cursor: pointer;" onclick="openRowThumbModal(this)">
                            </div>
                            <div class="flex-grow-1">
                                <select class="form-select rounded-3 product-select" required onchange="onRowProductChange(this)">
                                    <option value="">-- Choose Product --</option>
                                    ${buildProductOptionsHtml()}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small mb-1">Select Size <span class="text-danger">*</span></label>
                        <select name="items[${index}][product_size_id]" class="form-select rounded-3 size-select" required onchange="onRowSizeChange(this)">
                            <option value="">-- Select Product First --</option>
                        </select>
                        <div class="form-text small fw-bold text-success stock-notice"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small mb-1">Quantity (pcs) <span class="text-danger">*</span></label>
                        <input type="number" name="items[${index}][quantity]" class="form-control rounded-3 qty-input" value="1" min="1" required oninput="onRowQtyChange(this)">
                        <div class="form-text small fw-bold text-danger d-none qty-error-notice"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small mb-1">Unit Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control rounded-3 price-input" placeholder="0.00" required oninput="calcTotals()">
                    </div>

                    <div class="col-md-6 d-flex align-items-end justify-content-end">
                        <div class="text-end">
                            <span class="small text-muted">Item Subtotal:</span>
                            <div class="fs-5 fw-bold text-warning item-subtotal-display">₹0.00</div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', cardHtml);
        filterProductsByCategory();
        updateRemoveButtons();
        updateAllRowsStockNotices();
    }

    function removeProductRow(btn) {
        const card = btn.closest('.product-item-card');
        if (card) {
            card.remove();
            updateItemBadges();
            updateRemoveButtons();
            filterProductsByCategory();
            updateAllRowsStockNotices();
        }
    }

    function updateItemBadges() {
        const cards = document.querySelectorAll('.product-item-card');
        cards.forEach((card, idx) => {
            const badge = card.querySelector('.item-badge');
            if (badge) {
                badge.innerHTML = `<i class="fa-solid fa-box me-1"></i> Product #${idx + 1}`;
            }
        });
    }

    function updateRemoveButtons() {
        const cards = document.querySelectorAll('.product-item-card');
        cards.forEach(card => {
            const removeBtn = card.querySelector('.remove-item-btn');
            if (removeBtn) {
                removeBtn.style.display = cards.length > 1 ? 'inline-block' : 'none';
            }
        });
    }

    function getSelectedProductIdsInOtherRows(currentCard) {
        const selectedIds = [];
        const cards = document.querySelectorAll('.product-item-card');
        cards.forEach(c => {
            if (c !== currentCard) {
                const prodSelect = c.querySelector('.product-select');
                if (prodSelect && prodSelect.value) {
                    selectedIds.push(parseInt(prodSelect.value));
                }
            }
        });
        return selectedIds;
    }

    function getAllocatedQtyForSizeInOtherRows(currentCard, sizeId) {
        let allocated = 0;
        const cards = document.querySelectorAll('.product-item-card');
        cards.forEach(c => {
            if (c !== currentCard) {
                const sizeSelect = c.querySelector('.size-select');
                const qtyInput = c.querySelector('.qty-input');
                if (sizeSelect && sizeSelect.value && parseInt(sizeSelect.value) === parseInt(sizeId)) {
                    allocated += (parseInt(qtyInput ? qtyInput.value : 0) || 0);
                }
            }
        });
        return allocated;
    }

    function updateAllRowsStockNotices() {
        const cards = document.querySelectorAll('.product-item-card');
        cards.forEach(card => {
            const prodSelect = card.querySelector('.product-select');
            const sizeSelect = card.querySelector('.size-select');
            const qtyInput = card.querySelector('.qty-input');
            const stockNotice = card.querySelector('.stock-notice');
            const qtyNotice = card.querySelector('.qty-error-notice');

            const prodId = parseInt(prodSelect ? prodSelect.value : 0);
            if (!prodId) return;

            const prod = productsData.find(p => p.id === prodId);
            if (!prod) return;

            const currentSelectedSizeId = parseInt(sizeSelect ? sizeSelect.value : 0);

            Array.from(sizeSelect.options).forEach(opt => {
                if (!opt.value) return;
                const szId = parseInt(opt.value);
                const szObj = prod.sizes.find(s => s.id === szId);
                if (!szObj) return;

                const actualStock = szObj.stock;
                const allocatedOther = getAllocatedQtyForSizeInOtherRows(card, szId);
                const effectiveStock = Math.max(0, actualStock - allocatedOther);

                if (effectiveStock <= 0 && szId !== currentSelectedSizeId) {
                    opt.disabled = true;
                    opt.innerText = `Size: ${szObj.size} (0 pcs left - ⚠️ Fully selected in another row)`;
                } else if (effectiveStock <= 0 && szId === currentSelectedSizeId && actualStock > 0) {
                    opt.disabled = false;
                    opt.innerText = `Size: ${szObj.size} (Stock: ${actualStock} pcs - Fully allocated on form)`;
                } else {
                    opt.disabled = (actualStock <= 0);
                    const allocMsg = allocatedOther > 0 ? ` (${allocatedOther} in other row)` : '';
                    opt.innerText = `Size: ${szObj.size} (Available: ${effectiveStock} pcs${allocMsg})`;
                }
            });

            if (currentSelectedSizeId) {
                const szObj = prod.sizes.find(s => s.id === currentSelectedSizeId);
                if (szObj) {
                    const allocatedOther = getAllocatedQtyForSizeInOtherRows(card, currentSelectedSizeId);
                    const remainingStock = Math.max(0, szObj.stock - allocatedOther);

                    if (allocatedOther > 0) {
                        stockNotice.innerText = `Available Stock: ${remainingStock} pcs (${allocatedOther} allocated in another row)`;
                    } else {
                        stockNotice.innerText = `Available Stock: ${szObj.stock} pcs`;
                    }

                    if (qtyInput) {
                        qtyInput.max = remainingStock;
                        let curQty = parseInt(qtyInput.value) || 0;
                        if (remainingStock > 0 && curQty > remainingStock) {
                            qtyInput.value = remainingStock;
                            if (qtyNotice) {
                                qtyNotice.innerText = `Quantity adjusted to remaining stock of ${remainingStock} pcs!`;
                                qtyNotice.classList.remove('d-none');
                            }
                        } else if (remainingStock === 0 && szObj.stock > 0) {
                            if (qtyNotice) {
                                qtyNotice.innerText = `This size is already fully allocated in another row!`;
                                qtyNotice.classList.remove('d-none');
                            }
                        } else {
                            if (qtyNotice) qtyNotice.classList.add('d-none');
                        }
                    }
                }
            }
        });

        calcTotals();
    }

    function onRowProductChange(selectElem) {
        const card = selectElem.closest('.product-item-card');
        const prodId = parseInt(selectElem.value);
        const sizeSelect = card.querySelector('.size-select');
        const priceInput = card.querySelector('.price-input');
        const thumbContainer = card.querySelector('.product-inline-thumb-container');
        const thumbImg = card.querySelector('.product-inline-thumb');

        sizeSelect.innerHTML = '<option value="">-- Choose Size --</option>';
        card.querySelector('.stock-notice').innerText = '';

        const prod = productsData.find(p => p.id === prodId);
        if (!prod) {
            if (priceInput) priceInput.value = '';
            if (thumbContainer) thumbContainer.classList.add('d-none');
            onRowSizeChange(sizeSelect);
            filterProductsByCategory();
            return;
        }

        if (priceInput) priceInput.value = prod.price.toFixed(2);
        if (thumbContainer && thumbImg) {
            thumbImg.src = prod.image;
            thumbContainer.classList.remove('d-none');
        }

        prod.sizes.forEach(sz => {
            const opt = document.createElement('option');
            opt.value = sz.id;
            opt.setAttribute('data-stock', sz.stock);
            opt.setAttribute('data-size', sz.size);
            opt.innerText = `Size: ${sz.size} (Stock: ${sz.stock} pcs)`;
            if (sz.stock <= 0) {
                opt.disabled = true;
                opt.innerText += ' - OUT OF STOCK';
            }
            sizeSelect.appendChild(opt);
        });

        onRowSizeChange(sizeSelect);
        filterProductsByCategory();
    }

    function onRowSizeChange(sizeSelectElem) {
        updateAllRowsStockNotices();
    }

    function onRowQtyChange(qtyInputElem) {
        updateAllRowsStockNotices();
    }

    function filterProductsByCategory() {
        const checkboxes = document.querySelectorAll('.category-checkbox:checked');
        const selectedCatIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
        const hideOutOfStockSwitch = document.getElementById('hideOutOfStockSwitch');
        const hideOutOfStock = hideOutOfStockSwitch ? hideOutOfStockSwitch.checked : false;

        const productSelects = document.querySelectorAll('.product-select');
        productSelects.forEach(selectElem => {
            const currentCard = selectElem.closest('.product-item-card');
            const otherSelectedProdIds = getSelectedProductIdsInOtherRows(currentCard);
            const currentProdId = parseInt(selectElem.value || 0);

            const options = selectElem.options;
            let selectedStillValid = false;

            for (let i = 0; i < options.length; i++) {
                const opt = options[i];
                if (!opt.value) continue;

                const prodId = parseInt(opt.value);
                const catIds = JSON.parse(opt.getAttribute('data-categories') || '[]');
                const isMatchedCategory = (selectedCatIds.length === 0) || catIds.some(id => selectedCatIds.includes(parseInt(id)));
                const isOut = opt.getAttribute('data-out-of-stock') === '1';
                const isSelectedInOtherRow = otherSelectedProdIds.includes(prodId);

                const prodObj = productsData.find(p => p.id === prodId);
                const baseName = prodObj ? prodObj.name : '';
                const priceText = prodObj ? `(Price: ₹${prodObj.price.toFixed(2)})` : '';

                if (isSelectedInOtherRow && prodId !== currentProdId) {
                    // Hide & disable products already selected in another row
                    opt.disabled = true;
                    opt.hidden = true;
                    opt.style.display = 'none';
                    opt.innerText = `${baseName} ${priceText} - ⚠️ [Already Selected in Another Row]`;
                } else if (isOut) {
                    // Out of stock
                    opt.disabled = (prodId !== currentProdId);
                    opt.hidden = hideOutOfStock && (prodId !== currentProdId);
                    opt.style.display = opt.hidden ? 'none' : '';
                    opt.innerText = `${baseName} ${priceText} - ⚠️ [OUT OF STOCK]`;
                    if (opt.selected && !isOut) selectedStillValid = true;
                } else if (!isMatchedCategory) {
                    // Filtered out by category
                    opt.disabled = (prodId !== currentProdId);
                    opt.hidden = true;
                    opt.style.display = 'none';
                } else {
                    // Normal available product
                    opt.disabled = false;
                    opt.hidden = false;
                    opt.style.display = '';
                    opt.innerText = `${baseName} ${priceText}`;
                    if (opt.selected) selectedStillValid = true;
                }
            }

            if (!selectedStillValid && selectElem.value && otherSelectedProdIds.includes(parseInt(selectElem.value))) {
                selectElem.value = '';
                onRowProductChange(selectElem);
            }
        });
    }

    function selectAllCategories(status) {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(cb => cb.checked = status);
        filterProductsByCategory();
    }

    function calcTotals() {
        const cards = document.querySelectorAll('.product-item-card');
        let totalSubtotal = 0;
        let summaryHtml = '';
        let validItemCount = 0;

        cards.forEach((card, idx) => {
            const prodSelect = card.querySelector('.product-select');
            const sizeSelect = card.querySelector('.size-select');
            const priceInput = card.querySelector('.price-input');
            const qtyInput = card.querySelector('.qty-input');
            const subtotalDisplay = card.querySelector('.item-subtotal-display');

            const prodId = parseInt(prodSelect ? prodSelect.value : 0);
            const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
            const qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;
            const itemSubtotal = price * qty;

            if (subtotalDisplay) {
                subtotalDisplay.innerText = '₹' + itemSubtotal.toFixed(2);
            }

            if (prodId) {
                const prod = productsData.find(p => p.id === prodId);
                const sizeOpt = sizeSelect && sizeSelect.selectedIndex > -1 ? sizeSelect.options[sizeSelect.selectedIndex] : null;
                const sizeName = sizeOpt && sizeOpt.value ? sizeOpt.getAttribute('data-size') : 'Size Pending';

                totalSubtotal += itemSubtotal;
                validItemCount++;

                summaryHtml += `
                    <div class="d-flex align-items-center gap-2 p-2 border-bottom">
                        <img src="${prod ? prod.image : ''}" class="rounded border" style="width: 40px; height: 48px; object-fit: cover;">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="small fw-bold text-dark text-truncate">${prod ? prod.name : 'Product'}</div>
                            <div class="small text-muted">Size: ${sizeName} | Qty: ${qty} x ₹${price.toFixed(2)}</div>
                        </div>
                        <div class="fw-bold text-dark fs-7">₹${itemSubtotal.toFixed(2)}</div>
                    </div>
                `;
            }
        });

        const deliveryInput = document.getElementById('deliveryChargeInput');
        const shipping = parseFloat(deliveryInput ? deliveryInput.value : 0) || 0;
        const grandTotal = totalSubtotal + shipping;

        document.getElementById('subtotalDisplay').innerText = '₹' + totalSubtotal.toFixed(2);
        document.getElementById('deliveryDisplay').innerText = '₹' + shipping.toFixed(2);
        document.getElementById('grandTotalDisplay').innerText = '₹' + grandTotal.toFixed(2);

        document.getElementById('summaryItemCount').innerText = validItemCount;

        const summaryContainer = document.getElementById('orderItemsSummaryList');
        if (summaryContainer) {
            if (validItemCount > 0) {
                summaryContainer.innerHTML = summaryHtml;
            } else {
                summaryContainer.innerHTML = `
                    <div class="p-3 bg-light rounded-3 text-muted text-center" id="emptySummaryPlaceholder">
                        <i class="fa-solid fa-basket-shopping fa-2x mb-2 text-secondary opacity-50"></i>
                        <p class="mb-0 small fw-bold">No products selected yet. Select products on the left to build the order.</p>
                    </div>
                `;
            }
        }
    }

    function openRowThumbModal(imgElem) {
        const card = imgElem.closest('.product-item-card');
        const selectElem = card.querySelector('.product-select');
        const prodId = parseInt(selectElem ? selectElem.value : 0);
        const prod = productsData.find(p => p.id === prodId);
        if (prod && prod.image) {
            openImagePreviewModal(prod.image, prod.name);
        }
    }

    function openImagePreviewModal(imageUrl, title) {
        let modalEl = document.getElementById('imagePreviewModal');
        if (!modalEl) {
            const modalHtml = `
                <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-dark text-white py-2.5 px-3">
                                <h5 class="modal-title fs-6 fw-bold text-truncate" id="imagePreviewModalTitle">Product Image Preview</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-3 text-center bg-light">
                                <img id="imagePreviewModalImg" src="" alt="Product Large Image" class="img-fluid rounded-3 border shadow-sm" style="max-height: 80vh; object-fit: contain;">
                            </div>
                        </div>
                    </div>
                </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modalEl = document.getElementById('imagePreviewModal');
        }
        document.getElementById('imagePreviewModalImg').src = imageUrl;
        document.getElementById('imagePreviewModalTitle').textContent = title || 'Product Image Preview';
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize first product row
        addProductRow();

        const form = document.getElementById('manualSaleForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const cards = document.querySelectorAll('.product-item-card');
                let hasError = false;

                const totalRequestedPerSize = {};

                cards.forEach(card => {
                    const qtyInput = card.querySelector('.qty-input');
                    const prodSelect = card.querySelector('.product-select');
                    const sizeSelect = card.querySelector('.size-select');

                    if (!prodSelect || !prodSelect.value) {
                        hasError = true;
                        alert('Please select a product for all rows.');
                        return;
                    }

                    if (!sizeSelect || !sizeSelect.value) {
                        hasError = true;
                        alert('Please select a size for all selected products.');
                        return;
                    }

                    const szId = parseInt(sizeSelect.value);
                    const qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;
                    totalRequestedPerSize[szId] = (totalRequestedPerSize[szId] ?? 0) + qty;
                });

                if (hasError) {
                    e.preventDefault();
                    return false;
                }

                for (const [szId, totalQty] of Object.entries(totalRequestedPerSize)) {
                    let actualStock = 0;
                    let prodName = '';
                    let sizeName = '';

                    for (const prod of productsData) {
                        const szObj = prod.sizes.find(s => s.id === parseInt(szId));
                        if (szObj) {
                            actualStock = szObj.stock;
                            prodName = prod.name;
                            sizeName = szObj.size;
                            break;
                        }
                    }

                    if (totalQty > actualStock) {
                        e.preventDefault();
                        alert(`Total requested quantity (${totalQty} pcs) across rows exceeds available stock (${actualStock} pcs) for ${prodName} (Size ${sizeName}).`);
                        return false;
                    }
                }
            });
        }
    });
</script>
@endsection
