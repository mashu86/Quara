@extends('layouts.admin')

@section('title', 'Edit Manual Sale #' . $order->order_number . ' - ' . $siteName . ' Admin')

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
        .card-body h5 i {
            font-size: 0.9rem !important;
        }
        .card-body h6 {
            font-size: 0.82rem !important;
        }
        .form-label {
            font-size: 0.78rem !important;
            margin-bottom: 0.25rem !important;
        }
        .form-control, .form-select {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        .form-text {
            font-size: 0.7rem !important;
        }
        .grand-total-box {
            padding: 0.65rem 0.85rem !important;
            margin-top: 1rem !important;
        }
        .grand-total-label {
            font-size: 0.78rem !important;
        }
        .grand-total-amount {
            font-size: 1.25rem !important;
        }
        .submit-sale-btn {
            padding: 0.65rem 1rem !important;
            font-size: 0.82rem !important;
            margin-top: 1rem !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 page-header-title">Edit Offline Sale #{{ $order->order_number }}</h3>
        <p class="text-muted small mb-0 page-header-subtitle">Update customer details, pricing, stock or payment status.</p>
    </div>
    <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-dark rounded-3 px-2.5 px-md-3 py-1.5 py-md-2 fw-bold shadow-sm back-offline-btn" title="Back to Offline Sales">
        &larr;<span class="d-none d-md-inline"> Back to Offline Sales</span>
    </a>
</div>

<form action="{{ route('admin.manual-sales.update', $order->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <!-- Left Side: Category, Product Selection & Pricing -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-shirt text-warning me-2"></i> Product & Size</h5>

                    <!-- Category Filter Checkboxes -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Filter by Category (Select Multiple)</label>
                            <div>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none me-2 fw-semibold" onclick="selectAllCategories(true)">Select All</button>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted fw-semibold" onclick="selectAllCategories(false)">Clear All</button>
                            </div>
                        </div>
                        <div class="p-3 border rounded-3 bg-light" style="max-height: 140px; overflow-y: auto;">
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
                        <div class="form-text small text-muted">If no category is selected, products from all categories will be shown.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Product <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <div id="productInlineThumbContainer" class="d-none flex-shrink-0">
                                <img id="productInlineThumb" src="" alt="Selected Product" 
                                     class="rounded-3 border shadow-sm" 
                                     style="width: 52px; height: 60px; object-fit: cover; cursor: pointer; transition: transform 0.2s ease;" 
                                     onclick="openInlineProductModal()" 
                                     title="Click to view large preview">
                            </div>
                            <div class="flex-grow-1">
                                <select name="product_id" id="productSelect" class="form-select rounded-3" required onchange="onProductChange()">
                                    <option value="">-- Choose Product --</option>
                                    @foreach($products as $prod)
                                        @php
                                            $catIds = array_values(array_filter(array_unique(array_merge(
                                                [$prod->category_id],
                                                $prod->categories->pluck('id')->toArray()
                                            ))));
                                        @endphp
                                        <option value="{{ $prod->id }}" 
                                                {{ ($firstItem && $firstItem->product_id == $prod->id) ? 'selected' : '' }}
                                                data-price="{{ $prod->final_price }}"
                                                data-image="{{ $prod->primary_image_url }}"
                                                data-categories='@json($catIds)'
                                                data-sizes='@json($prod->sizes)'>
                                            {{ $prod->name }} (Price: ₹{{ number_format($prod->final_price, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Size <span class="text-danger">*</span></label>
                            <select name="product_size_id" id="sizeSelect" class="form-select rounded-3" required onchange="onSizeChange()">
                                <option value="">-- Select Product First --</option>
                            </select>
                            <div class="form-text small fw-bold text-success" id="stockNotice"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Quantity (pcs) <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="qtyInput" class="form-control rounded-3" value="{{ old('quantity', $firstItem->quantity ?? 1) }}" min="1" required oninput="validateQuantity()">
                            <div class="form-text small fw-bold text-danger d-none" id="qtyErrorNotice"></div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Unit Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="unit_price" id="priceInput" class="form-control rounded-3" value="{{ old('unit_price', $firstItem->unit_price ?? 0.00) }}" placeholder="0.00" required oninput="calcTotals()">
                            <div class="form-text small">Override price or discount for this manual sale.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Delivery Charge (₹)</label>
                            <input type="number" step="0.01" name="delivery_charge" id="shippingInput" class="form-control rounded-3" value="{{ old('delivery_charge', $order->shipping ?? 0.00) }}" min="0" oninput="calcTotals()">
                            <div class="form-text small">Delivery fee if applicable.</div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center grand-total-box">
                        <span class="fw-bold text-dark fs-6 grand-total-label">Grand Total Amount:</span>
                        <span class="fs-3 fw-bold text-warning grand-total-amount" id="grandTotalDisplay">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment & Notes -->
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-credit-card text-warning me-2"></i> Payment Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="upi" {{ $order->payment_method === 'upi' ? 'selected' : '' }}>UPI (GPay/PhonePe/Paytm)</option>
                                <option value="bank_transfer" {{ $order->payment_method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / Card</option>
                                <option value="cash" {{ $order->payment_method === 'cash' ? 'selected' : '' }}>Cash Payment</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select rounded-3" required>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid (Fully Received)</option>
                                <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending (Pay Later)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Notes / Special Instructions</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Walk-in customer discount / Counter sale receipt #42">{{ old('notes', $order->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Product Image Confirmation & Customer Details -->
        <div class="col-lg-5">
            <!-- Product Confirmation Image Preview Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4" id="productPreviewCard">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-start">
                        <i class="fa-solid fa-image text-warning me-2"></i> Product Confirmation
                    </h5>
                    <div id="productPreviewContainer">
                        <div class="p-4 bg-light rounded-3 text-muted" id="noProductSelectedPlaceholder">
                            <i class="fa-solid fa-box-open fa-3x mb-2 text-secondary opacity-50"></i>
                            <p class="mb-0 small fw-bold">Select a product to view its image & details for confirmation.</p>
                        </div>
                        <div id="productSelectedContent" class="d-none">
                            <div class="position-relative mb-3 mx-auto" style="max-width: 250px;">
                                <img id="previewImage" src="" alt="Product Image" class="img-fluid rounded-3 border shadow-sm" style="max-height: 220px; object-fit: contain; width: 100%;">
                            </div>
                            <h6 id="previewProductName" class="fw-bold text-dark mb-1"></h6>
                            <div class="text-warning fw-bold fs-5 mb-1" id="previewProductPrice"></div>
                            <div id="previewSizeStockInfo" class="badge bg-light text-dark border px-3 py-2 mt-1"></div>
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
                        <input type="text" name="customer_name" class="form-control rounded-3" value="{{ old('customer_name', $order->customer_name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mobile Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="customer_phone" class="form-control rounded-3" value="{{ old('customer_phone', $order->customer_phone) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address (Optional)</label>
                        <input type="email" name="customer_email" class="form-control rounded-3" value="{{ old('customer_email', $order->customer_email) }}">
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-2">Delivery Address (Optional)</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <input type="text" name="house_building" class="form-control rounded-3 mb-2" value="{{ old('house_building', $order->house_building) }}" placeholder="House / Building Name">
                        </div>
                        <div class="col-6">
                            <input type="text" name="street" class="form-control rounded-3 mb-2" value="{{ old('street', $order->street) }}" placeholder="Street / Area">
                        </div>
                        <div class="col-6">
                            <input type="text" name="city" class="form-control rounded-3 mb-2" value="{{ old('city', $order->city) }}" placeholder="City / Town">
                        </div>
                        <div class="col-6">
                            <input type="text" name="district" class="form-control rounded-3 mb-2" value="{{ old('district', $order->district) }}" placeholder="District">
                        </div>
                        <div class="col-6">
                            <input type="text" name="pin_code" class="form-control rounded-3 mb-2" value="{{ old('pin_code', $order->pin_code) }}" placeholder="PIN Code">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-3 mt-4 shadow-sm submit-sale-btn">UPDATE MANUAL SALE</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    const selectedSizeId = "{{ $firstItem ? $firstItem->product_size_id : '' }}";

    function filterProductsByCategory() {
        const checkboxes = document.querySelectorAll('.category-checkbox:checked');
        const selectedCatIds = Array.from(checkboxes).map(cb => parseInt(cb.value));

        const productSelect = document.getElementById('productSelect');
        const options = productSelect.options;
        let selectedStillValid = false;

        for (let i = 0; i < options.length; i++) {
            const opt = options[i];
            if (!opt.value) continue;

            const catIds = JSON.parse(opt.getAttribute('data-categories') || '[]');
            const isMatched = (selectedCatIds.length === 0) || catIds.some(id => selectedCatIds.includes(parseInt(id)));

            if (isMatched) {
                opt.hidden = false;
                opt.disabled = false;
                opt.style.display = '';
                if (opt.selected) {
                    selectedStillValid = true;
                }
            } else {
                opt.hidden = true;
                opt.disabled = true;
                opt.style.display = 'none';
                if (opt.selected) {
                    opt.selected = false;
                }
            }
        }

        if (!selectedStillValid) {
            productSelect.value = '';
            onProductChange();
        }
    }

    function selectAllCategories(status) {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(cb => cb.checked = status);
        filterProductsByCategory();
    }

    function onProductChange() {
        updateSizes();
        updateProductPreview();
        calcTotals();
    }

    function updateSizes() {
        const select = document.getElementById('productSelect');
        const selectedOption = select.options[select.selectedIndex];
        const sizeSelect = document.getElementById('sizeSelect');

        sizeSelect.innerHTML = '<option value="">-- Choose Size --</option>';
        document.getElementById('stockNotice').innerText = '';

        if (!selectedOption || !selectedOption.value) {
            onSizeChange();
            return;
        }

        const sizes = JSON.parse(selectedOption.getAttribute('data-sizes') || '[]');
        sizes.forEach(sz => {
            const opt = document.createElement('option');
            opt.value = sz.id;
            opt.setAttribute('data-stock', sz.stock);
            opt.setAttribute('data-size', sz.size);
            if (sz.id == selectedSizeId) {
                opt.selected = true;
            }
            opt.innerText = `Size: ${sz.size} (Stock: ${sz.stock} pcs)`;
            if (sz.stock <= 0 && sz.id != selectedSizeId) {
                opt.disabled = true;
                opt.innerText += ' - OUT OF STOCK';
            }
            sizeSelect.appendChild(opt);
        });

        onSizeChange();
    }

    function onSizeChange() {
        updateStockNotice();
        validateQuantity();
        updateProductPreview();
    }

    function updateStockNotice() {
        const sizeSelect = document.getElementById('sizeSelect');
        const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
        const notice = document.getElementById('stockNotice');

        if (selectedOption && selectedOption.value) {
            const stock = selectedOption.getAttribute('data-stock');
            notice.innerText = `Available Stock: ${stock} pcs`;
        } else {
            notice.innerText = '';
        }
    }

    function validateQuantity() {
        const qtyInput = document.getElementById('qtyInput');
        const sizeSelect = document.getElementById('sizeSelect');
        const selectedOption = sizeSelect ? sizeSelect.options[sizeSelect.selectedIndex] : null;
        const qtyNotice = document.getElementById('qtyErrorNotice');

        if (selectedOption && selectedOption.value) {
            const maxStock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
            qtyInput.max = maxStock;

            let qty = parseInt(qtyInput.value) || 0;
            if (maxStock > 0 && qty > maxStock) {
                qtyInput.value = maxStock;
                if (qtyNotice) {
                    qtyNotice.innerText = `Quantity cannot exceed available stock of ${maxStock} pcs!`;
                    qtyNotice.classList.remove('d-none');
                }
            } else if (qty < 1 && maxStock > 0) {
                qtyInput.value = 1;
                if (qtyNotice) qtyNotice.classList.add('d-none');
            } else {
                if (qtyNotice) qtyNotice.classList.add('d-none');
            }
        } else {
            qtyInput.removeAttribute('max');
            if (qtyNotice) qtyNotice.classList.add('d-none');
        }

        calcTotals();
    }

    function updateProductPreview() {
        const productSelect = document.getElementById('productSelect');
        const selectedProductOpt = productSelect.options[productSelect.selectedIndex];

        const placeholder = document.getElementById('noProductSelectedPlaceholder');
        const content = document.getElementById('productSelectedContent');
        const imgElem = document.getElementById('previewImage');
        const nameElem = document.getElementById('previewProductName');
        const priceElem = document.getElementById('previewProductPrice');
        const sizeStockElem = document.getElementById('previewSizeStockInfo');

        const inlineContainer = document.getElementById('productInlineThumbContainer');
        const inlineImg = document.getElementById('productInlineThumb');

        if (selectedProductOpt && selectedProductOpt.value) {
            const imageUrl = selectedProductOpt.getAttribute('data-image') || '';
            const rawText = selectedProductOpt.text;
            const prodName = rawText.split('(Price:')[0].trim();
            const price = parseFloat(selectedProductOpt.getAttribute('data-price')) || 0;

            if (inlineContainer && inlineImg) {
                inlineImg.src = imageUrl;
                inlineContainer.classList.remove('d-none');
            }

            imgElem.src = imageUrl;
            nameElem.innerText = prodName;
            priceElem.innerText = '₹' + price.toFixed(2);

            const sizeSelect = document.getElementById('sizeSelect');
            const selectedSizeOpt = sizeSelect ? sizeSelect.options[sizeSelect.selectedIndex] : null;

            if (selectedSizeOpt && selectedSizeOpt.value) {
                const szName = selectedSizeOpt.getAttribute('data-size');
                const stock = selectedSizeOpt.getAttribute('data-stock');
                sizeStockElem.innerText = `Size: ${szName} | Stock: ${stock} pcs`;
                sizeStockElem.className = "badge bg-success-subtle text-success border border-success px-3 py-2 mt-2";
            } else {
                sizeStockElem.innerText = `Please select a size`;
                sizeStockElem.className = "badge bg-warning-subtle text-warning border border-warning px-3 py-2 mt-2";
            }

            placeholder.classList.add('d-none');
            content.classList.remove('d-none');
        } else {
            if (inlineContainer) {
                inlineContainer.classList.add('d-none');
            }
            placeholder.classList.remove('d-none');
            content.classList.add('d-none');
        }
    }

    function openInlineProductModal() {
        const inlineImg = document.getElementById('productInlineThumb');
        const productSelect = document.getElementById('productSelect');
        const selectedOpt = productSelect.options[productSelect.selectedIndex];
        const name = selectedOpt ? selectedOpt.text.split('(Price:')[0].trim() : 'Product Image';
        if (inlineImg && inlineImg.src) {
            openImagePreviewModal(inlineImg.src, name);
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

    function calcTotals() {
        const priceInput = document.getElementById('priceInput');
        const qtyInput = document.getElementById('qtyInput');
        const shippingInput = document.getElementById('shippingInput');

        const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
        const qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;
        const shipping = parseFloat(shippingInput ? shippingInput.value : 0) || 0;

        const grandTotal = (price * qty) + shipping;
        const grandTotalDisplay = document.getElementById('grandTotalDisplay');
        if (grandTotalDisplay) {
            grandTotalDisplay.innerText = '₹' + grandTotal.toFixed(2);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateSizes();

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const sizeSelect = document.getElementById('sizeSelect');
                const selectedOption = sizeSelect ? sizeSelect.options[sizeSelect.selectedIndex] : null;
                const qtyInput = document.getElementById('qtyInput');

                if (selectedOption && selectedOption.value) {
                    const maxStock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
                    const qty = parseInt(qtyInput.value) || 0;

                    if (qty > maxStock) {
                        e.preventDefault();
                        alert(`Selected quantity (${qty} pcs) exceeds available stock (${maxStock} pcs).`);
                        qtyInput.value = maxStock;
                        qtyInput.focus();
                        return false;
                    }
                }
            });
        }
    });
</script>
@endsection
