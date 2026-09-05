@extends('layouts.admin')

@section('title', 'Edit Manual Sale #' . $order->order_number . ' - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .back-offline-btn {
            padding: 0.25rem 0.55rem !important;
            font-size: 0.8rem !important;
            border-radius: 8px !important;
        }
        .page-header-title {
            font-size: 1.1rem !important;
        }
        .page-header-subtitle {
            font-size: 0.7rem !important;
        }
        .card-body.p-4, .card-body.p-3 {
            padding: 0.85rem 0.65rem !important;
        }
        .card-body h5 {
            font-size: 0.88rem !important;
            margin-bottom: 0.5rem !important;
        }
        .form-label {
            font-size: 0.75rem !important;
            margin-bottom: 0.2rem !important;
        }
        .form-control, .form-select {
            font-size: 0.75rem !important;
            padding: 0.35rem 0.5rem !important;
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
            font-size: 0.7rem !important;
        }
        .product-item-card {
            padding: 0.65rem !important;
            overflow-x: hidden !important;
        }
        .item-badge {
            font-size: 0.68rem !important;
            padding: 0.3rem 0.5rem !important;
            white-space: normal !important;
            word-break: break-word !important;
        }
        .filter-stock-btn {
            font-size: 0.72rem !important;
            padding: 0.3rem 0.5rem !important;
        }
        .pick-product-btn {
            font-size: 0.7rem !important;
            padding: 0.35rem 0.5rem !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 page-header-title">Edit Offline Sale #{{ $order->order_number }}</h3>
        <p class="text-muted small mb-0 page-header-subtitle">Update customer details, items, pricing, or payment status.</p>
    </div>
    <a href="{{ route('admin.manual-sales.index') }}" class="btn btn-outline-dark rounded-3 px-2.5 px-md-3 py-1.5 py-md-2 fw-bold shadow-sm back-offline-btn" title="Back to Offline Sales">
        &larr;<span class="d-none d-md-inline"> Back to Offline Sales</span>
    </a>
</div>

<form action="{{ route('admin.manual-sales.update', $order->id) }}" method="POST" id="manualSaleForm">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <!-- TOP: Select Products & Sizes (FULL WIDTH - col-12) -->
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 flex-wrap gap-2">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-boxes-packing text-warning me-2"></i> Select Products & Sizes</h5>
                        
                        <!-- 2 Clean Filter Pills: Available vs Booked -->
                        <div class="btn-group btn-group-sm" role="group" aria-label="Stock Filter">
                            <input type="radio" class="btn-check" name="stock_filter_type" id="filterAvailable" value="available" checked onchange="filterProductsByCategory()">
                            <label class="btn btn-outline-success fw-bold px-2 px-md-3 py-1 py-md-1.5 filter-stock-btn rounded-start-pill" for="filterAvailable">
                                🟢 Available <span class="d-none d-sm-inline">Products</span>
                            </label>

                            <input type="radio" class="btn-check" name="stock_filter_type" id="filterBooked" value="booked" onchange="filterProductsByCategory()">
                            <label class="btn btn-outline-warning text-dark fw-bold px-2 px-md-3 py-1 py-md-1.5 filter-stock-btn rounded-end-pill" for="filterBooked">
                                🔒 Booked <span class="d-none d-sm-inline">Products</span>
                            </label>
                        </div>
                    </div>

                    <!-- Category Filter Checkboxes -->
                    <div class="mb-3 mb-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0 small">Filter Products by Category</label>
                            <div>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none me-2 fw-semibold" onclick="selectAllCategories(true)">Select All</button>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted fw-semibold" onclick="selectAllCategories(false)">Clear All</button>
                            </div>
                        </div>
                        <div class="p-2.5 border rounded-3 bg-light" style="max-height: 110px; overflow-y: auto;">
                            <div class="row g-2">
                                @foreach($categories as $cat)
                                    <div class="col-6 col-sm-4 col-md-3">
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
                        <!-- Populated dynamically by JS -->
                    </div>

                    <!-- Add Another Product Button -->
                    <div class="mb-3 mb-md-4">
                        <button type="button" class="btn btn-outline-warning text-dark fw-bold border-2 w-100 py-2 py-md-2.5 rounded-3 shadow-sm add-product-btn" onclick="addProductRow()" style="border-style: dashed !important;">
                            <i class="fa-solid fa-plus me-1"></i> ADD ANOTHER PRODUCT
                        </button>
                    </div>

                    <!-- Order Summary & Common Delivery Charge -->
                    @php
                        $activeOps = $order->operations ? $order->operations->where('status', 'active') : collect();
                        $totRefund = (float) $activeOps->sum('total_refund_amount');
                    @endphp
                    <div class="p-3 bg-light rounded-4 border">
                        <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-calculator text-warning me-2"></i> Order Pricing Summary</h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Common Delivery Charge (₹)</label>
                                <input type="number" step="0.01" name="delivery_charge" id="deliveryChargeInput" class="form-control rounded-3" value="{{ old('delivery_charge', $order->shipping) }}" min="0" oninput="calcTotals()">
                                <div class="form-text small text-muted">Order-wide shipping fee (Leave 0.00 for counter sales).</div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="small text-muted mb-1">Items Subtotal: <strong id="subtotalDisplay" class="text-dark">₹0.00</strong></div>
                                <div class="small text-muted mb-1">Delivery Charge: <strong id="deliveryDisplay" class="text-dark">₹0.00</strong></div>
                                <div class="fw-bold text-dark small mt-2">Original Grand Total: <span id="grandTotalDisplay" class="fw-bold">₹0.00</span></div>
                                @if($totRefund > 0)
                                    <div class="small text-danger fw-bold mt-1">Refund Deducted: -₹{{ number_format($totRefund, 2) }}</div>
                                    <div class="fw-bold text-success fs-4 mt-1">Net Realized Revenue: <span id="netRealizedDisplay">₹0.00</span></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM LEFT: Payment Details & Selected Items Summary (col-lg-6) -->
        <div class="col-lg-6">
            <!-- Payment Details Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-credit-card text-warning me-2"></i> Payment & Sale Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Sale Date (DD-MM-YYYY) <span class="text-danger">*</span></label>
                            <input type="date" name="sale_date" class="form-control rounded-3" value="{{ old('sale_date', $order->sale_date ? $order->sale_date->format('Y-m-d') : $order->created_at->format('Y-m-d')) }}" required>
                            <small class="text-muted d-block mt-1" style="font-size: 0.73rem;"><i class="fa-solid fa-circle-info text-warning me-1"></i> Date when the sale actually took place (Format: DD-MM-YYYY).</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="upi" {{ old('payment_method', $order->payment_method) === 'upi' ? 'selected' : '' }}>UPI (GPay/PhonePe/Paytm)</option>
                                <option value="bank_transfer" {{ old('payment_method', $order->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / Card</option>
                                <option value="cash" {{ old('payment_method', $order->payment_method) === 'cash' ? 'selected' : '' }}>Cash Payment</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select rounded-3" required>
                                <option value="paid" {{ old('payment_status', $order->payment_status) === 'paid' ? 'selected' : '' }}>Paid (Fully Received)</option>
                                <option value="pending" {{ old('payment_status', $order->payment_status) === 'pending' ? 'selected' : '' }}>Pending (Pay Later)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Notes / Special Instructions</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Walk-in customer discount / Counter sale receipt #42">{{ old('notes', $order->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Items Summary Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-receipt text-warning me-2"></i> Selected Items Summary (<span id="summaryItemCount">0</span>)
                    </h5>
                    <div id="orderItemsSummaryList">
                        <div class="p-3 bg-light rounded-3 text-muted text-center" id="emptySummaryPlaceholder">
                            <i class="fa-solid fa-basket-shopping fa-2x mb-2 text-secondary opacity-50"></i>
                            <p class="mb-0 small fw-bold">No products selected yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM RIGHT: Customer Details & Submit Button (col-lg-6) -->
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-user text-warning me-2"></i> Customer Details</h5>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Customer Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control rounded-3" placeholder="e.g. Anjali Nair" value="{{ old('customer_name', $order->customer_name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mobile Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="customer_phone" class="form-control rounded-3" placeholder="e.g. 9876543210" value="{{ old('customer_phone', $order->customer_phone) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address (Optional)</label>
                        <input type="email" name="customer_email" class="form-control rounded-3" placeholder="customer@gmail.com" value="{{ old('customer_email', $order->customer_email) }}">
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-2">Delivery Address (Optional for shipped orders)</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <input type="text" name="house_building" class="form-control rounded-3 mb-2" placeholder="House / Building Name" value="{{ old('house_building', $order->house_building) }}">
                        </div>
                        <div class="col-6">
                            <input type="text" name="street" class="form-control rounded-3 mb-2" placeholder="Street / Area" value="{{ old('street', $order->street) }}">
                        </div>
                        <div class="col-6">
                            <input type="text" name="city" class="form-control rounded-3 mb-2" placeholder="City / Town" value="{{ old('city', $order->city) }}">
                        </div>
                        <div class="col-6">
                            <input type="text" name="district" class="form-control rounded-3 mb-2" placeholder="District" value="{{ old('district', $order->district) }}">
                        </div>
                        <div class="col-6">
                            <input type="text" name="pin_code" class="form-control rounded-3 mb-2" placeholder="PIN Code" value="{{ old('pin_code', $order->pin_code) }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-3 mt-4 shadow-sm submit-sale-btn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">UPDATE MANUAL SALE</button>
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
                $physicalStock = (int) $prod->sizes->sum('stock');
                $isOut = (bool) $prod->is_out_of_stock;
            @endphp
            {
                id: {{ $prod->id }},
                name: @json($prod->name),
                price: {{ (float) $prod->final_price }},
                image: @json($prod->primary_image_url),
                categories: @json($catIds),
                sizes: @json($prod->sizes),
                isOut: {{ $isOut ? 'true' : 'false' }},
                bookedBy: @json($prod->booked_by),
                physicalStock: {{ $physicalStock }}
            },
        @endforeach
    ];

    // Existing items from this order
    const existingItems = [
        @foreach($order->items as $item)
            @php
                $itemProd = $item->product;
                $itemImg = $itemProd ? $itemProd->primary_image_url : \App\Models\Setting::logoUrl();
                $itemOp = $order->operations ? $order->operations->where('status', 'active')->where('order_item_id', $item->id)->first() : null;
                $isReturned = ($item->item_status === 'returned' || ($itemOp && in_array($itemOp->operation_type, ['product_returned', 'customer_return', 'wrong_product_sent', 'product_damaged', 'product_lost'])));
                $refundAmount = ($itemOp && $itemOp->is_money_refunded) ? (float)$itemOp->total_refund_amount : 0.00;
            @endphp
            {
                productId: {{ $item->product_id ?? 0 }},
                productSizeId: {{ $item->product_size_id ?? 0 }},
                productName: @json($item->product_name),
                sizeName: @json($item->size),
                productImage: @json($itemImg),
                quantity: {{ $item->quantity }},
                unitPrice: {{ (float) $item->unit_price }},
                isReturned: {{ $isReturned ? 'true' : 'false' }},
                refundAmount: {{ $refundAmount }}
            },
        @endforeach
    ];

    // Restore existing order items' stock in productsData for editing mode
    const existingItemQtyPerSize = {};
    existingItems.forEach(item => {
        if (item.productSizeId) {
            existingItemQtyPerSize[item.productSizeId] = (existingItemQtyPerSize[item.productSizeId] || 0) + item.quantity;
        }
    });

    productsData.forEach(prod => {
        let totalStock = 0;
        if (prod.sizes && prod.sizes.length) {
            prod.sizes.forEach(sz => {
                const extraQty = existingItemQtyPerSize[sz.id] || 0;
                sz.stock += extraQty;
                totalStock += sz.stock;
            });
        }
        prod.physicalStock = totalStock;
    });

    let rowIndexCounter = 0;

    function buildProductOptionsHtml(selectedProdId = null) {
        let html = '';
        productsData.forEach(prod => {
            const isSelected = (selectedProdId && parseInt(selectedProdId) === prod.id);
            let labelSuffix = '';
            let disabledAttr = '';
            
            if (prod.isOut && prod.physicalStock > 0) {
                let bookedInfo = prod.bookedBy ? `: ${prod.bookedBy}` : '';
                labelSuffix = ` - 🔒 [BOOKED${bookedInfo}] (Stock: ${prod.physicalStock} pcs)`;
            } else if (prod.physicalStock <= 0) {
                labelSuffix = ' - ⚠️ [0 STOCK AVAILABLE]';
                if (!isSelected) disabledAttr = 'disabled';
            }
            const selectedAttr = isSelected ? 'selected' : '';
            html += `<option value="${prod.id}" data-price="${prod.price}" data-image="${prod.image}" data-categories='${JSON.stringify(prod.categories)}' data-out-of-stock="${prod.isOut ? '1' : '0'}" data-physical-stock="${prod.physicalStock}" ${disabledAttr} ${selectedAttr}>${prod.name} (Price: ₹${prod.price.toFixed(2)})${labelSuffix}</option>`;
        });
        return html;
    }

    function addProductRow(initialData = null) {
        const container = document.getElementById('productItemsContainer');
        const index = rowIndexCounter++;
        const isExisting = initialData !== null;
        const selectedProdId = initialData ? initialData.productId : null;

        let cardHtml = '';

        if (isExisting) {
            const prodName = initialData.productName || 'Purchased Product';
            const sizeName = initialData.sizeName || 'Standard';
            const prodImg = initialData.productImage || '';
            const isReturned = initialData.isReturned || false;
            const refundAmount = initialData.refundAmount || 0;

            let cardStyleClass = isReturned ? 'border-danger border-2 bg-danger-subtle' : 'bg-light';
            let returnedBadges = isReturned ? `
                <span class="badge bg-danger text-white ms-1" style="font-size: 0.68rem;">
                    <i class="fa-solid fa-rotate-left me-1"></i> RETURNED
                </span>
                ${refundAmount > 0 ? `<span class="badge bg-danger text-white ms-1" style="font-size: 0.68rem;"><i class="fa-solid fa-hand-holding-dollar me-1"></i> Refund: ₹${refundAmount.toFixed(2)}</span>` : `<span class="badge bg-secondary text-white ms-1" style="font-size: 0.68rem;"><i class="fa-solid fa-ban me-1"></i> No Refund</span>`}
            ` : '';

            cardHtml = `
                <div class="product-item-card border rounded-3 p-3 mb-3 position-relative shadow-sm existing-item-card ${cardStyleClass}" data-index="${index}">
                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2 flex-wrap gap-1">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="badge bg-dark text-warning fw-bold px-2.5 py-1.5 item-badge">
                                <i class="fa-solid fa-lock me-1 text-warning"></i> Original Purchased Item #${container.children.length + 1} (Read-Only)
                            </span>
                            ${returnedBadges}
                        </div>
                        <span class="badge bg-light text-muted border small fw-semibold d-none d-sm-inline-block">
                            <i class="fa-solid fa-shield-halved me-1 text-success"></i> Preserved Audit Item
                        </span>
                    </div>

                    <div class="row g-2 g-md-3 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="d-flex align-items-center gap-2">
                                <img src="${prodImg}" class="rounded-3 border shadow-sm flex-shrink-0" style="width: 48px; height: 55px; object-fit: cover; cursor: pointer;" onclick="openImagePreviewModal('${prodImg}', '${prodName.replace(/'/g, "\\'")}')" title="Click to view image">
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark fs-6 text-truncate" title="${prodName}">${prodName}</div>
                                    <span class="badge bg-secondary mt-1" style="font-size: 0.7rem;">Size: ${sizeName}</span>
                                    <input type="hidden" class="product-select" value="${selectedProdId}" data-name="${prodName.replace(/"/g, '&quot;')}" data-image="${prodImg}">
                                    <input type="hidden" name="items[${index}][product_size_id]" class="size-select" value="${initialData.productSizeId}" data-size="${sizeName.replace(/"/g, '&quot;')}">
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label fw-bold small text-muted mb-0">Quantity</label>
                            <input type="number" name="items[${index}][quantity]" class="form-control rounded-3 qty-input bg-white" value="${initialData.quantity}" readonly style="font-weight: 600;">
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label fw-bold small text-muted mb-0">Unit Price (₹)</label>
                            <input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control rounded-3 price-input bg-white" value="${initialData.unitPrice.toFixed(2)}" readonly style="font-weight: 600;">
                        </div>

                        <div class="col-12 col-md-3 text-start text-md-end mt-2 mt-md-0">
                            <span class="small text-muted d-block" style="font-size: 0.75rem;">Item Subtotal:</span>
                            <div class="fs-5 fw-bold text-warning item-subtotal-display">₹${(initialData.quantity * initialData.unitPrice).toFixed(2)}</div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            cardHtml = `
                <div class="product-item-card border rounded-3 p-3 mb-3 bg-white position-relative shadow-sm new-item-card" data-index="${index}">
                    <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3 border-bottom pb-2 flex-wrap gap-1">
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1.5 item-badge">
                            <i class="fa-solid fa-plus-circle me-1"></i> New Item #${container.children.length + 1}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 fw-bold remove-item-btn" onclick="removeProductRow(this)" title="Remove new item">
                            <i class="fa-solid fa-trash me-1"></i> Remove Item
                        </button>
                    </div>

                    <div class="row g-2 g-md-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small mb-1">Select Product <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center gap-1.5 gap-md-2">
                                <div class="product-inline-thumb-container d-none flex-shrink-0">
                                    <img class="product-inline-thumb rounded-3 border shadow-sm" src="" alt="Thumb" style="width: 44px; height: 50px; object-fit: cover; cursor: pointer;" onclick="openRowThumbModal(this)" title="Click to view full image">
                                </div>
                                <div class="flex-grow-1 position-relative" style="cursor: pointer;" onclick="openVisualPickerForRow(this)" title="Click to Pick Product with Image">
                                    <input type="text" class="form-control rounded-3 bg-white product-display-input fw-semibold" readonly placeholder="👉 Pick Product Image..." style="cursor: pointer;">
                                    <select class="form-select rounded-3 product-select d-none" required onchange="onRowProductChange(this)">
                                        <option value="">-- Choose Product --</option>
                                        ${buildProductOptionsHtml()}
                                    </select>
                                </div>
                                <button type="button" class="btn btn-warning text-dark border border-warning-subtle fw-bold btn-sm rounded-3 px-2 px-md-3 py-2 flex-shrink-0 shadow-sm pick-product-btn" onclick="openVisualPickerForRow(this)" title="Pick product by photo grid">
                                    <i class="fa-solid fa-images me-1"></i> <span class="d-none d-sm-inline">Pick Product Image</span><span class="d-inline d-sm-none">Pick</span>
                                </button>
                            </div>
                        </div>

                        <div class="col-6 col-md-6">
                            <label class="form-label fw-bold small mb-1">Select Size <span class="text-danger">*</span></label>
                            <select name="items[${index}][product_size_id]" class="form-select rounded-3 size-select" required onchange="onRowSizeChange(this)">
                                <option value="">-- Select Size --</option>
                            </select>
                            <div class="form-text small fw-bold text-success stock-notice" style="font-size: 0.7rem;"></div>
                        </div>

                        <div class="col-6 col-md-6">
                            <label class="form-label fw-bold small mb-1">Quantity (pcs) <span class="text-danger">*</span></label>
                            <input type="number" name="items[${index}][quantity]" class="form-control rounded-3 qty-input" value="1" min="1" required oninput="onRowQtyChange(this)">
                            <div class="form-text small fw-bold text-danger d-none qty-error-notice" style="font-size: 0.7rem;"></div>
                        </div>

                        <div class="col-6 col-md-6">
                            <label class="form-label fw-bold small mb-1">Unit Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control rounded-3 price-input" placeholder="0.00" required oninput="calcTotals()">
                        </div>

                        <div class="col-6 col-md-6 d-flex align-items-end justify-content-end">
                            <div class="text-end">
                                <span class="small text-muted d-block" style="font-size: 0.75rem;">Item Subtotal:</span>
                                <div class="fs-5 fw-bold text-warning item-subtotal-display">₹0.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        container.insertAdjacentHTML('beforeend', cardHtml);

        if (!isExisting) {
            filterProductsByCategory();
            updateRemoveButtons();
            updateAllRowsStockNotices();
        }
    }

    function removeProductRow(btn) {
        const card = btn.closest('.product-item-card');
        if (card && card.classList.contains('new-item-card')) {
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
                if (card.classList.contains('existing-item-card')) {
                    badge.innerHTML = `<i class="fa-solid fa-lock me-1 text-warning"></i> Original Purchased Item #${idx + 1} (Read-Only)`;
                } else {
                    badge.innerHTML = `<i class="fa-solid fa-plus-circle me-1"></i> New Item #${idx + 1}`;
                }
            }
        });
    }

    function updateRemoveButtons() {
        const newCards = document.querySelectorAll('.product-item-card.new-item-card');
        newCards.forEach(card => {
            const removeBtn = card.querySelector('.remove-item-btn');
            if (removeBtn) {
                removeBtn.style.display = 'inline-block';
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
        const cards = document.querySelectorAll('.product-item-card.new-item-card');
        cards.forEach(card => {
            const prodSelect = card.querySelector('.product-select');
            const sizeSelect = card.querySelector('.size-select');
            const qtyInput = card.querySelector('.qty-input');
            const stockNotice = card.querySelector('.stock-notice');
            const qtyNotice = card.querySelector('.qty-error-notice');

            if (!sizeSelect || !sizeSelect.options) return;

            const prodId = parseInt(prodSelect ? prodSelect.value : 0);
            if (!prodId) return;

            const prod = productsData.find(p => p.id === prodId);
            if (!prod) return;

            const currentSelectedSizeId = parseInt(sizeSelect.value || 0);

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
                        if (stockNotice) stockNotice.innerText = `Available Stock: ${remainingStock} pcs (${allocatedOther} allocated in another row)`;
                    } else {
                        if (stockNotice) stockNotice.innerText = `Available Stock: ${szObj.stock} pcs`;
                    }

                    if (qtyInput) {
                        if (remainingStock > 0) {
                            qtyInput.max = remainingStock;
                        } else {
                            qtyInput.removeAttribute('max');
                        }
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

    let activePickerCard = null;

    function openVisualPickerForRow(btn) {
        activePickerCard = btn.closest('.product-item-card');
        let modalEl = document.getElementById('visualProductPickerModal');
        if (!modalEl) {
            const modalHtml = `
                <div class="modal fade" id="visualProductPickerModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-dark text-white py-2.5 px-3">
                                <h5 class="modal-title fs-6 fw-bold">
                                    <i class="fa-solid fa-images text-warning me-2"></i> Select Product by Image
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-3 bg-light" style="max-height: 78vh; overflow-y: auto;">
                                <div class="mb-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                        <input type="text" id="visualPickerSearchInput" class="form-control border-start-0 ps-0 rounded-end-pill py-2" placeholder="Type product name to search..." oninput="renderVisualPickerGrid()">
                                    </div>
                                </div>
                                <div class="row g-2.5" id="visualPickerGrid">
                                    <!-- Rendered dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modalEl = document.getElementById('visualProductPickerModal');
        }

        document.getElementById('visualPickerSearchInput').value = '';
        renderVisualPickerGrid();

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function renderVisualPickerGrid() {
        const grid = document.getElementById('visualPickerGrid');
        if (!grid) return;

        const searchVal = (document.getElementById('visualPickerSearchInput')?.value || '').toLowerCase().trim();
        const otherSelectedProdIds = activePickerCard ? getSelectedProductIdsInOtherRows(activePickerCard) : [];

        const checkboxes = document.querySelectorAll('.category-checkbox:checked');
        const selectedCatIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
        const filterBookedRadio = document.getElementById('filterBooked');
        const isBookedFilter = filterBookedRadio ? filterBookedRadio.checked : false;

        let html = '';
        let matchCount = 0;

        productsData.forEach(prod => {
            const isMatchedCategory = (selectedCatIds.length === 0) || prod.categories.some(id => selectedCatIds.includes(parseInt(id)));
            const isNameMatched = !searchVal || prod.name.toLowerCase().includes(searchVal);

            if (!isMatchedCategory || !isNameMatched) return;

            const isSelectedInOther = otherSelectedProdIds.includes(prod.id);
            const isOut = prod.isOut;
            const physicalStock = prod.physicalStock;

            let badgeHtml = '';

            if (isSelectedInOther) {
                // Completely hide products already selected in another row to prevent conflicts
                return;
            }

            if (physicalStock <= 0) {
                // Completely hide 0 stock products to keep picker clean
                return;
            }

            if (isOut) {
                if (!isBookedFilter) return; // Hide booked when available filter active
                let bookedInfo = prod.bookedBy ? `: ${prod.bookedBy}` : '';
                badgeHtml = `<span class="badge bg-warning text-dark">🔒 Booked${bookedInfo} (${physicalStock} pcs)</span>`;
            } else {
                if (isBookedFilter) return; // Hide available when booked filter active
                badgeHtml = `<span class="badge bg-success">🟢 ${physicalStock} pcs in stock</span>`;
            }

            matchCount++;

            html += `
                <div class="col-6 col-sm-4 col-md-3">
                    <div class="card h-100 border rounded-3 shadow-sm product-picker-card cursor-pointer" onclick="selectProductFromPicker(${prod.id})" style="transition: transform 0.15s ease;">
                        <div class="position-relative bg-white text-center p-1 rounded-top-3">
                            <img src="${prod.image}" class="img-fluid rounded-2" style="height: 110px; width: 100%; object-fit: cover;" alt="${prod.name}">
                        </div>
                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold small text-dark mb-1 text-truncate" title="${prod.name}">${prod.name}</h6>
                                <div class="fw-bold text-success small">₹${prod.price.toFixed(2)}</div>
                                <div class="mt-1">${badgeHtml}</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning text-dark fw-bold w-100 mt-2 py-1" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-check me-1"></i> Select
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        if (matchCount === 0) {
            html = `<div class="col-12 text-center py-4 text-muted"><i class="fa-solid fa-box-open fa-2x mb-2"></i><p class="mb-0 small fw-bold">No matching products found.</p></div>`;
        }

        grid.innerHTML = html;
    }

    function selectProductFromPicker(prodId) {
        if (!activePickerCard) return;
        const selectElem = activePickerCard.querySelector('.product-select');
        if (selectElem) {
            selectElem.value = prodId;
            onRowProductChange(selectElem);
        }

        const modalEl = document.getElementById('visualProductPickerModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    }

    function onRowProductChange(selectElem, targetSizeId = null) {
        const card = selectElem.closest('.product-item-card');
        const prodId = parseInt(selectElem.value);
        const sizeSelect = card.querySelector('.size-select');
        const priceInput = card.querySelector('.price-input');
        const displayInput = card.querySelector('.product-display-input');
        const thumbContainer = card.querySelector('.product-inline-thumb-container');
        const thumbImg = card.querySelector('.product-inline-thumb');

        if (sizeSelect) sizeSelect.innerHTML = '<option value="">-- Choose Size --</option>';
        const stockNotice = card.querySelector('.stock-notice');
        if (stockNotice) stockNotice.innerText = '';

        const prod = productsData.find(p => p.id === prodId);
        if (!prod) {
            if (displayInput) displayInput.value = '';
            if (priceInput && !priceInput.value) priceInput.value = '';
            if (thumbContainer) thumbContainer.classList.add('d-none');
            if (sizeSelect) onRowSizeChange(sizeSelect);
            filterProductsByCategory();
            return;
        }

        if (displayInput) displayInput.value = `${prod.name} (Price: ₹${prod.price.toFixed(2)})`;
        if (priceInput && !priceInput.value) priceInput.value = prod.price.toFixed(2);
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

            if (targetSizeId && parseInt(targetSizeId) === sz.id) {
                opt.selected = true;
            }

            if (sz.stock <= 0 && !opt.selected) {
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
        const filterBookedRadio = document.getElementById('filterBooked');
        const isBookedFilter = filterBookedRadio ? filterBookedRadio.checked : false;

        const productSelects = document.querySelectorAll('select.product-select');
        productSelects.forEach(selectElem => {
            const currentCard = selectElem.closest('.product-item-card');
            if (currentCard && currentCard.classList.contains('existing-item-card')) return;
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
                const physicalStock = parseInt(opt.getAttribute('data-physical-stock') || '0');
                const isSelectedInOtherRow = otherSelectedProdIds.includes(prodId);

                const prodObj = productsData.find(p => p.id === prodId);
                const baseName = prodObj ? prodObj.name : '';
                const priceText = prodObj ? `(Price: ₹${prodObj.price.toFixed(2)})` : '';
                let labelSuffix = '';
                if (isOut && physicalStock > 0) {
                    let bookedInfo = (prodObj && prodObj.bookedBy) ? `: ${prodObj.bookedBy}` : '';
                    labelSuffix = ` - 🔒 [BOOKED${bookedInfo}] (Stock: ${physicalStock} pcs)`;
                } else if (physicalStock <= 0) {
                    labelSuffix = ' - ⚠️ [0 STOCK AVAILABLE]';
                }

                if (isSelectedInOtherRow && prodId !== currentProdId) {
                    // Hide & disable products already selected in another row
                    opt.disabled = true;
                    opt.hidden = true;
                    opt.style.display = 'none';
                    opt.innerText = `${baseName} ${priceText} - ⚠️ [Already Selected in Another Row]`;
                } else if (physicalStock <= 0 && prodId !== currentProdId) {
                    // Always hide 0 physical stock products unless currently selected
                    opt.disabled = true;
                    opt.hidden = true;
                    opt.style.display = 'none';
                    opt.innerText = `${baseName} ${priceText} - ⚠️ [0 STOCK AVAILABLE]`;
                } else if (isBookedFilter && (!isOut || physicalStock <= 0)) {
                    // Show only Booked products with stock > 0 when Booked filter is active
                    opt.disabled = (prodId !== currentProdId);
                    opt.hidden = true;
                    opt.style.display = 'none';
                } else if (!isBookedFilter && isOut) {
                    // Hide Booked products when Available filter is active
                    opt.disabled = (prodId !== currentProdId);
                    opt.hidden = true;
                    opt.style.display = 'none';
                } else if (!isMatchedCategory) {
                    // Filtered out by category
                    opt.disabled = (prodId !== currentProdId);
                    opt.hidden = true;
                    opt.style.display = 'none';
                } else {
                    // Available matching product
                    opt.disabled = false;
                    opt.hidden = false;
                    opt.style.display = '';
                    opt.innerText = `${baseName} ${priceText}${labelSuffix}`;
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

            const isExistingCard = card.classList.contains('existing-item-card');

            if (prodId || isExistingCard) {
                let name = '';
                let img = '';
                let sizeName = '';

                if (isExistingCard) {
                    name = prodSelect ? (prodSelect.getAttribute('data-name') || 'Purchased Product') : 'Purchased Product';
                    img = prodSelect ? (prodSelect.getAttribute('data-image') || '') : '';
                    sizeName = sizeSelect ? (sizeSelect.getAttribute('data-size') || 'Standard') : 'Standard';
                } else {
                    const prod = productsData.find(p => p.id === prodId);
                    name = prod ? prod.name : 'Product';
                    img = prod ? prod.image : '';
                    const sizeOpt = sizeSelect && sizeSelect.selectedIndex > -1 ? sizeSelect.options[sizeSelect.selectedIndex] : null;
                    sizeName = sizeOpt && sizeOpt.value ? (sizeOpt.getAttribute('data-size') || sizeOpt.innerText) : 'Size Pending';
                }

                totalSubtotal += itemSubtotal;
                validItemCount++;

                summaryHtml += `
                    <div class="d-flex align-items-center gap-2 p-2 border-bottom">
                        <img src="${img}" class="rounded border" style="width: 40px; height: 48px; object-fit: cover;">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="small fw-bold text-dark text-truncate">${name}</div>
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

        const netElem = document.getElementById('netRealizedDisplay');
        if (netElem) {
            const totRefund = {{ $totRefund }};
            const netRealized = Math.max(0, grandTotal - totRefund);
            netElem.innerText = '₹' + netRealized.toFixed(2);
        }

        document.getElementById('summaryItemCount').innerText = validItemCount;

        const summaryContainer = document.getElementById('orderItemsSummaryList');
        if (summaryContainer) {
            if (validItemCount > 0) {
                summaryContainer.innerHTML = summaryHtml;
            } else {
                summaryContainer.innerHTML = `
                    <div class="p-3 bg-light rounded-3 text-muted text-center" id="emptySummaryPlaceholder">
                        <i class="fa-solid fa-basket-shopping fa-2x mb-2 text-secondary opacity-50"></i>
                        <p class="mb-0 small fw-bold">No products selected yet.</p>
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
        // Initialize existing items for edit
        if (existingItems && existingItems.length > 0) {
            existingItems.forEach(itemData => {
                addProductRow(itemData);
            });
            updateRemoveButtons();
            calcTotals();
        } else {
            addProductRow();
        }

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
                    const isExisting = card.classList.contains('existing-item-card');

                    if (!isExisting) {
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
                    }

                    const szId = parseInt(sizeSelect ? sizeSelect.value : 0);
                    const qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;
                    if (szId) {
                        totalRequestedPerSize[szId] = (totalRequestedPerSize[szId] ?? 0) + qty;
                    }
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
