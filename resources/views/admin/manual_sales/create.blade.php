@extends('layouts.admin')

@section('title', 'Record Manual Sale - ' . $siteName . ' Admin')

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
        .d-none-mobile-empty {
            display: none !important;
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

                    <!-- Category Multi-Select Dropdown Filter -->
                    <div class="mb-3 mb-md-4">
                        <label class="form-label fw-bold mb-1.5 small text-dark d-block">
                            <i class="fa-solid fa-filter text-warning me-1"></i> Filter Products by Category
                        </label>
                        <div class="dropdown category-multiselect-dropdown position-relative">
                            <button class="btn btn-white border w-100 d-flex align-items-center justify-content-between rounded-3 py-2 px-3 shadow-xs bg-white text-dark" 
                                    type="button" id="categoryDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <span class="small fw-semibold text-truncate" id="categoryDropdownLabel">All Categories</span>
                                <i class="fa-solid fa-chevron-down text-muted small ms-2"></i>
                            </button>
                            <div class="dropdown-menu w-100 p-2 shadow-lg border rounded-3 mt-1" aria-labelledby="categoryDropdownBtn" style="max-height: 280px; overflow-y: auto;">
                                <!-- Top Controls: Select All (Left) & Clear All (Right) -->
                                <div class="d-flex justify-content-between align-items-center px-2 py-1.5 border-bottom mb-2 bg-light rounded-2">
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold text-primary small" onclick="selectAllCategories(true)">Select All</button>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted fw-bold small" onclick="selectAllCategories(false)">Clear All</button>
                                </div>
                                <!-- Category Checkboxes List -->
                                <div class="category-options-list">
                                    @foreach($categories as $cat)
                                        <div class="dropdown-item rounded-2 py-1.5 px-2 mb-0.5 user-select-none">
                                            <div class="form-check m-0 d-flex align-items-center gap-2">
                                                <input class="form-check-input category-checkbox m-0" type="checkbox" value="{{ $cat->id }}" id="cat_{{ $cat->id }}" onchange="filterProductsByCategory()">
                                                <label class="form-check-label small fw-medium text-dark text-truncate flex-grow-1" for="cat_{{ $cat->id }}" title="{{ $cat->name }}" style="cursor: pointer;">
                                                    {{ $cat->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
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

                    <!-- Order Summary & Common Delivery Charge & Discount -->
                    <div class="p-3 p-md-4 bg-light rounded-4 border">
                        <h6 class="fw-bold mb-3 border-bottom pb-2 d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-calculator text-warning me-2"></i> Order Pricing Summary</span>
                            <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.75rem;">Offline Discount Available</span>
                        </h6>

                        <!-- DISCOUNT SELECTION SECTION -->
                        <div class="card border-0 shadow-xs mb-3 rounded-3 bg-white">
                            <div class="card-body p-3">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-tags text-warning me-1.5"></i> Common Discount Options
                                </label>
                                
                                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="discount_option" id="discountOptNone" value="none" checked onchange="toggleDiscountFields()">
                                        <label class="form-check-label small fw-semibold text-dark" for="discountOptNone">
                                            No Discount
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="discount_option" id="discountOptSetTotal" value="set_total" onchange="toggleDiscountFields()">
                                        <label class="form-check-label small fw-semibold text-dark" for="discountOptSetTotal">
                                            Option 1: Set Final Product Price (Grand Subtotal)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="discount_option" id="discountOptCalc" value="calculate_discount" onchange="toggleDiscountFields()">
                                        <label class="form-check-label small fw-semibold text-dark" for="discountOptCalc">
                                            Option 2: Calculate Based on Discount
                                        </label>
                                    </div>
                                </div>

                                <!-- Option 1 Box: Set Final Product Total -->
                                <div id="setTotalBox" class="p-2.5 rounded-3 bg-light border d-none mb-2">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-7">
                                            <label class="form-label small fw-bold text-dark mb-1">Set Desired Product Total (₹) <span class="text-muted fw-normal">(Excluding Shipping)</span></label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white border-end-0 fw-bold">₹</span>
                                                <input type="number" step="0.01" name="desired_subtotal" id="desiredSubtotalInput" class="form-control border-start-0 rounded-end-3" placeholder="e.g. 1000" min="0" oninput="calcTotals()">
                                            </div>
                                            <div class="form-text text-muted" style="font-size: 0.72rem;">Enter total price for products (e.g. Total is ₹1200, enter ₹1000 => ₹200 discount).</div>
                                        </div>
                                        <div class="col-md-5 text-md-end">
                                            <div class="small text-muted">Calculated Discount:</div>
                                            <div class="fw-bold text-danger fs-6" id="setTotalDiscountDisplay">- ₹0.00</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Option 2 Box: Calculate Based on Discount (Input + Currency/Percentage Dropdown) -->
                                <div id="calcDiscountBox" class="p-2.5 rounded-3 bg-light border d-none mb-2">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-7">
                                            <label class="form-label small fw-bold text-dark mb-1">Discount Amount / Percentage</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" name="discount_value" id="discountValueInput" class="form-control rounded-start-3" placeholder="e.g. 300 or 10" min="0" oninput="calcTotals()">
                                                <select name="discount_type" id="discountTypeSelect" class="form-select bg-dark text-warning fw-bold border-0" style="max-width: 85px; cursor: pointer;" onchange="calcTotals()">
                                                    <option value="fixed">₹</option>
                                                    <option value="percentage">%</option>
                                                </select>
                                            </div>
                                            <div class="form-text text-muted" style="font-size: 0.72rem;">Select ₹ for price discount, or % for percentage discount.</div>
                                        </div>
                                        <div class="col-md-5 text-md-end">
                                            <div class="small text-muted">Applied Discount:</div>
                                            <div class="fw-bold text-danger fs-6" id="calcDiscountDisplay">- ₹0.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Charge & Summary Totals -->
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Common Delivery Charge (₹)</label>
                                <input type="number" step="0.01" name="delivery_charge" id="deliveryChargeInput" class="form-control rounded-3" value="0.00" min="0" oninput="calcTotals()">
                                <div class="form-text small text-muted">Order-wide shipping fee (Leave 0.00 for counter sales).</div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="small text-muted mb-1">Product Original Total: <strong id="subtotalDisplay" class="text-dark">₹0.00</strong></div>
                                <div class="small text-danger mb-1 d-none" id="discountRowDisplay">Discount / Savings: <strong id="discountDisplay" class="text-danger">- ₹0.00</strong></div>
                                <div class="small text-muted mb-1">Delivery Charge: <strong id="deliveryDisplay" class="text-dark">₹0.00</strong></div>
                                <div class="fw-bold text-dark fs-6 mt-2">Grand Total Amount:</div>
                                <div class="fs-2 fw-bold text-warning" id="grandTotalDisplay">₹0.00</div>
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
                            <input type="date" name="sale_date" class="form-control rounded-3" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                            <small class="text-muted d-block mt-1" style="font-size: 0.73rem;"><i class="fa-solid fa-circle-info text-warning me-1"></i> Default: Today. Select a past date if sale took place earlier.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                                <option value="bank_transfer">Bank Transfer / Card</option>
                                <option value="cash">Cash Payment</option>
                            </select>
                        </div>

                        <div class="col-md-12">
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

            <!-- Selected Items Order Summary Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-receipt text-warning me-2"></i> Selected Items Summary (<span id="summaryItemCount">0</span>)
                    </h5>
                    <div id="orderItemsSummaryList">
                        <div class="p-3 bg-light rounded-3 text-muted text-center" id="emptySummaryPlaceholder">
                            <i class="fa-solid fa-basket-shopping fa-2x mb-2 text-secondary opacity-50"></i>
                            <p class="mb-0 small fw-bold">No products selected yet. Select products above to build the order.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM RIGHT: Customer Details & Submit Button (col-lg-6) -->
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 flex-wrap gap-2">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-user text-warning me-2"></i> Customer Details</h5>
                        <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.75rem;"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI Address Scanner</span>
                    </div>

                    <!-- AI Address Screenshot / Photo Upload Box -->
                    <div class="card border-2 border-warning-subtle bg-warning-subtle bg-opacity-10 rounded-3 mb-4 p-3 shadow-xs">
                        <div class="d-flex align-items-start gap-2.5">
                            <div class="rounded-circle bg-warning bg-opacity-20 p-2 text-warning flex-shrink-0 d-none d-sm-block mt-1">
                                <i class="fa-solid fa-file-invoice fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1.5 flex-wrap">
                                    <span><i class="fa-solid fa-camera text-warning me-1"></i> Auto-Fill Customer Address from Image / Screenshot</span>
                                    <span class="badge bg-dark text-warning" style="font-size: 0.65rem;">Gemini AI</span>
                                </div>
                                <p class="text-muted mb-2.5" style="font-size: 0.73rem;">
                                    Upload or snap a screenshot/photo of customer's WhatsApp chat, Instagram message, order label, or paper receipt. AI will extract & fill customer name, phone, address, district & PIN code!
                                </p>

                                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                                    <input type="file" id="addressScreenshotInput" accept="image/*" class="d-none" onchange="processAddressScreenshot(this)">
                                    <button type="button" class="btn btn-warning text-dark fw-bold btn-sm rounded-3 px-3 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2" onclick="document.getElementById('addressScreenshotInput').click()" id="scanAddressBtn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i> <span>Scan Screenshot / Image</span>
                                    </button>
                                    
                                    <div id="scanAddressStatus" class="small fw-semibold text-muted d-none align-items-center gap-2">
                                        <span class="spinner-border spinner-border-sm text-warning" role="status" aria-hidden="true"></span>
                                        <span style="font-size: 0.76rem;">Analyzing screenshot with AI...</span>
                                    </div>

                                    <div id="scanAddressPreview" class="d-none align-items-center gap-2">
                                        <img id="scanAddressThumb" src="" class="rounded-2 border shadow-xs" style="width: 36px; height: 36px; object-fit: cover;">
                                        <span class="badge bg-success text-white fw-bold" style="font-size: 0.72rem;"><i class="fa-solid fa-check me-1"></i> Address Auto-Filled!</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Customer Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control rounded-3" placeholder="e.g. Anjali Nair" value="{{ old('customer_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mobile Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="customer_phone" class="form-control rounded-3" placeholder="e.g. 9876543210" value="{{ old('customer_phone') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="email" name="customer_email" class="form-control rounded-3" placeholder="customer@gmail.com (Optional)" value="{{ old('customer_email') }}">
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-2">Customer Address Details <span class="text-danger">*</span></h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small fw-bold mb-1">House / Building Name <span class="text-danger">*</span></label>
                            <input type="text" name="house_building" class="form-control rounded-3 mb-2" placeholder="House / Building Name *" value="{{ old('house_building') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold mb-1">Street / Area <span class="text-danger">*</span></label>
                            <input type="text" name="street" class="form-control rounded-3 mb-2" placeholder="Street / Area *" value="{{ old('street') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold mb-1">City / Town <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control rounded-3 mb-2" placeholder="City / Town *" value="{{ old('city', 'Naduvil') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold mb-1">District <span class="text-danger">*</span></label>
                            <input type="text" name="district" class="form-control rounded-3 mb-2" placeholder="District *" value="{{ old('district', 'Kannur') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold mb-1">PIN Code <span class="text-danger">*</span></label>
                            <input type="text" name="pin_code" class="form-control rounded-3 mb-2" placeholder="PIN Code *" value="{{ old('pin_code', '670582') }}" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-3 mt-4 shadow-sm submit-sale-btn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">RECORD MANUAL SALE</button>
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

    let rowIndexCounter = 0;

    function buildProductOptionsHtml() {
        let html = '';
        productsData.forEach(prod => {
            let labelSuffix = '';
            let disabledAttr = '';
            
            if (prod.isOut && prod.physicalStock > 0) {
                let bookedInfo = prod.bookedBy ? `: ${prod.bookedBy}` : '';
                labelSuffix = ` - 🔒 [BOOKED${bookedInfo}] (Stock: ${prod.physicalStock} pcs)`;
            } else if (prod.physicalStock <= 0) {
                labelSuffix = ' - ⚠️ [0 STOCK AVAILABLE]';
                disabledAttr = 'disabled';
            }
            html += `<option value="${prod.id}" data-price="${prod.price}" data-image="${prod.image}" data-categories='${JSON.stringify(prod.categories)}' data-out-of-stock="${prod.isOut ? '1' : '0'}" data-physical-stock="${prod.physicalStock}" ${disabledAttr}>${prod.name} (Price: ₹${prod.price.toFixed(2)})${labelSuffix}</option>`;
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
                        <div class="product-selection-wrapper d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                            <button type="button" class="btn btn-warning text-dark border border-warning-subtle fw-bold btn-sm rounded-3 px-3 py-2 flex-shrink-0 shadow-sm order-1 order-md-2 pick-product-main-btn w-100 w-md-auto" onclick="openVisualPickerForRow(this)" title="Pick product by photo grid" style="font-size: 0.78rem;">
                                <i class="fa-solid fa-images me-1"></i> Pick Product Image
                            </button>
                            <div class="product-display-container d-flex align-items-center gap-2 flex-grow-1 position-relative order-2 order-md-1 d-none-mobile-empty">
                                <div class="product-inline-thumb-container d-none flex-shrink-0">
                                    <img class="product-inline-thumb rounded-3 border shadow-sm" src="" alt="Thumb" style="width: 52px; height: 58px; object-fit: cover; cursor: pointer;" onclick="openRowThumbModal(this)" title="Click to view full image">
                                </div>
                                <div class="flex-grow-1 position-relative" style="cursor: pointer;" onclick="openVisualPickerForRow(this)" title="Click to Pick Product with Image">
                                    <input type="text" class="form-control rounded-3 bg-white product-display-input fw-semibold" readonly placeholder="👉 Click here to Pick Product with Image..." style="cursor: pointer; font-size: 0.85rem;">
                                    <select class="form-select rounded-3 product-select d-none" required onchange="onRowProductChange(this)">
                                        <option value="">-- Choose Product --</option>
                                        ${buildProductOptionsHtml()}
                                    </select>
                                </div>
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

    function onRowProductChange(selectElem) {
        const card = selectElem.closest('.product-item-card');
        const prodId = parseInt(selectElem.value);
        const sizeSelect = card.querySelector('.size-select');
        const priceInput = card.querySelector('.price-input');
        const displayInput = card.querySelector('.product-display-input');
        const displayContainer = card.querySelector('.product-display-container');
        const thumbContainer = card.querySelector('.product-inline-thumb-container');
        const thumbImg = card.querySelector('.product-inline-thumb');

        sizeSelect.innerHTML = '<option value="">-- Choose Size --</option>';
        card.querySelector('.stock-notice').innerText = '';

        const prod = productsData.find(p => p.id === prodId);
        if (!prod) {
            if (displayInput) displayInput.value = '';
            if (priceInput) priceInput.value = '';
            if (thumbContainer) thumbContainer.classList.add('d-none');
            if (displayContainer) displayContainer.classList.add('d-none-mobile-empty');
            onRowSizeChange(sizeSelect);
            filterProductsByCategory();
            return;
        }

        if (displayInput) displayInput.value = `${prod.name} (Price: ₹${prod.price.toFixed(2)})`;
        if (priceInput) priceInput.value = prod.price.toFixed(2);
        if (thumbContainer && thumbImg) {
            thumbImg.src = prod.image;
            thumbContainer.classList.remove('d-none');
        }
        if (displayContainer) displayContainer.classList.remove('d-none-mobile-empty');

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

        // Auto-select size if there is only 1 valid/available size option (or single size total)
        const validSizeOpts = Array.from(sizeSelect.options).filter(opt => opt.value && !opt.disabled);
        if (validSizeOpts.length === 1) {
            sizeSelect.value = validSizeOpts[0].value;
        } else if (validSizeOpts.length === 0) {
            const allSizeOpts = Array.from(sizeSelect.options).filter(opt => opt.value);
            if (allSizeOpts.length === 1) {
                sizeSelect.value = allSizeOpts[0].value;
            }
        }

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

        updateCategoryDropdownLabel();
    }

    function updateCategoryDropdownLabel() {
        const total = document.querySelectorAll('.category-checkbox').length;
        const checked = document.querySelectorAll('.category-checkbox:checked').length;
        const labelElem = document.getElementById('categoryDropdownLabel');
        if (!labelElem) return;

        if (checked === 0) {
            labelElem.innerText = 'All Categories';
        } else if (checked === total) {
            labelElem.innerText = `All Categories Selected (${total})`;
        } else if (checked === 1) {
            const firstChecked = document.querySelector('.category-checkbox:checked');
            const catLabel = firstChecked ? firstChecked.nextElementSibling.innerText.trim() : '';
            labelElem.innerText = `1 Category Selected (${catLabel})`;
        } else {
            labelElem.innerText = `${checked} Categories Selected`;
        }
    }

    function selectAllCategories(status) {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(cb => cb.checked = status);
        filterProductsByCategory();
    }

    function toggleDiscountFields() {
        const opt = document.querySelector('input[name="discount_option"]:checked')?.value || 'none';
        const setTotalBox = document.getElementById('setTotalBox');
        const calcDiscountBox = document.getElementById('calcDiscountBox');

        if (setTotalBox) {
            setTotalBox.classList.toggle('d-none', opt !== 'set_total');
        }
        if (calcDiscountBox) {
            calcDiscountBox.classList.toggle('d-none', opt !== 'calculate_discount');
        }
        calcTotals();
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

        const discountOpt = document.querySelector('input[name="discount_option"]:checked')?.value || 'none';
        let calculatedDiscount = 0;

        if (discountOpt === 'set_total') {
            const desiredInput = document.getElementById('desiredSubtotalInput');
            if (desiredInput && desiredInput.value !== '') {
                const desiredVal = parseFloat(desiredInput.value) || 0;
                calculatedDiscount = Math.max(0, totalSubtotal - desiredVal);
            }
            const setTotalDisp = document.getElementById('setTotalDiscountDisplay');
            if (setTotalDisp) setTotalDisp.innerText = '- ₹' + calculatedDiscount.toFixed(2);
        } else if (discountOpt === 'calculate_discount') {
            const valInput = document.getElementById('discountValueInput');
            const typeSelect = document.getElementById('discountTypeSelect');
            const discVal = parseFloat(valInput ? valInput.value : 0) || 0;
            const discType = typeSelect ? typeSelect.value : 'fixed';

            if (discType === 'percentage') {
                calculatedDiscount = (totalSubtotal * (discVal / 100));
            } else {
                calculatedDiscount = discVal;
            }
            calculatedDiscount = Math.min(totalSubtotal, Math.max(0, calculatedDiscount));

            const calcDisp = document.getElementById('calcDiscountDisplay');
            if (calcDisp) calcDisp.innerText = '- ₹' + calculatedDiscount.toFixed(2);
        }

        const deliveryInput = document.getElementById('deliveryChargeInput');
        const shipping = parseFloat(deliveryInput ? deliveryInput.value : 0) || 0;
        const grandTotal = Math.max(0, (totalSubtotal - calculatedDiscount) + shipping);

        document.getElementById('subtotalDisplay').innerText = '₹' + totalSubtotal.toFixed(2);
        
        const discRow = document.getElementById('discountRowDisplay');
        const discDisp = document.getElementById('discountDisplay');
        if (discRow && discDisp) {
            if (calculatedDiscount > 0) {
                discDisp.innerText = '- ₹' + calculatedDiscount.toFixed(2);
                discRow.classList.remove('d-none');
            } else {
                discRow.classList.add('d-none');
            }
        }

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
            let isSelectable = true;

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
                let bookedInfo = prod.bookedBy ? ` ${prod.bookedBy}` : '';
                badgeHtml = `<span class="badge bg-warning text-dark d-inline-block text-truncate align-middle" style="font-size: 6px; max-width: 100%;" title="🔒${bookedInfo} (${physicalStock} pcs)">🔒${bookedInfo} (${physicalStock} pcs)</span>`;
            } else {
                if (isBookedFilter) return; // Hide available when booked filter active
                badgeHtml = `<span class="badge bg-success d-inline-block text-truncate align-middle" style="font-size: 7px; max-width: 100%;">🟢 ${physicalStock} pcs in stock</span>`;
            }

            matchCount++;

            html += `
                <div class="col-6 col-sm-4 col-md-3">
                    <div class="card h-100 border rounded-3 shadow-sm product-picker-card ${isSelectable ? 'cursor-pointer' : 'opacity-60'}" ${isSelectable ? `onclick="selectProductFromPicker(${prod.id})"` : ''} style="transition: transform 0.15s ease;">
                        <div class="position-relative bg-white text-center p-1 rounded-top-3">
                            <img src="${prod.image}" class="img-fluid rounded-2" style="height: 110px; width: 100%; object-fit: cover;" alt="${prod.name}">
                        </div>
                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold small text-dark mb-1 text-truncate" title="${prod.name}">${prod.name}</h6>
                                <div class="fw-bold text-success small">₹${prod.price.toFixed(2)}</div>
                                <div class="mt-1">${badgeHtml}</div>
                            </div>
                            <button type="button" class="btn btn-sm ${isSelectable ? 'btn-warning text-dark fw-bold' : 'btn-light text-muted'} w-100 mt-2 py-1" style="font-size: 0.72rem;" ${isSelectable ? '' : 'disabled'}>
                                ${isSelectable ? '<i class="fa-solid fa-check me-1"></i> Select' : 'Unavailable'}
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

    function processAddressScreenshot(input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        const btn = document.getElementById('scanAddressBtn');
        const status = document.getElementById('scanAddressStatus');
        const preview = document.getElementById('scanAddressPreview');
        const thumb = document.getElementById('scanAddressThumb');

        // Show thumbnail preview
        const reader = new FileReader();
        reader.onload = function(e) {
            if (thumb) thumb.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Show loading state
        if (btn) btn.disabled = true;
        if (status) {
            status.classList.remove('d-none');
            status.classList.add('d-flex');
        }
        if (preview) preview.classList.add('d-none');

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('image', file);

        fetch('{{ route('admin.manual-sales.parse-address') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (status) {
                status.classList.add('d-none');
                status.classList.remove('d-flex');
            }

            if (data.success && data.data) {
                const addr = data.data;

                const fieldsMap = {
                    'customer_name': addr.customer_name,
                    'customer_phone': addr.customer_phone,
                    'customer_email': addr.customer_email,
                    'house_building': addr.house_building,
                    'street': addr.street,
                    'city': addr.city,
                    'district': addr.district,
                    'state': addr.state,
                    'pin_code': addr.pin_code
                };

                let filledCount = 0;
                for (const [fieldName, val] of Object.entries(fieldsMap)) {
                    if (val) {
                        const inputElem = document.querySelector(`[name="${fieldName}"]`);
                        if (inputElem) {
                            inputElem.value = val;
                            filledCount++;

                            // Add glowing highlight effect to filled input
                            inputElem.style.transition = 'all 0.4s ease';
                            inputElem.style.backgroundColor = '#fff9e6';
                            inputElem.style.borderColor = '#ffc107';
                            inputElem.style.boxShadow = '0 0 10px rgba(255, 193, 7, 0.5)';
                            setTimeout(() => {
                                inputElem.style.backgroundColor = '';
                                inputElem.style.borderColor = '';
                                inputElem.style.boxShadow = '';
                            }, 2500);
                        }
                    }
                }

                if (preview) {
                    preview.classList.remove('d-none');
                    preview.classList.add('d-flex');
                }

                alert(`✨ AI Address Extracted Successfully!\n\n${filledCount} Customer Detail fields auto-filled from image.\nName: ${addr.customer_name || 'N/A'}\nPhone: ${addr.customer_phone || 'N/A'}\nCity: ${addr.city || 'N/A'}\nDistrict: ${addr.district || 'N/A'}\nPIN: ${addr.pin_code || 'N/A'}`);
            } else {
                alert('⚠️ ' + (data.message || 'AI could not find clear customer address details in this photo. Please try a clearer screenshot.'));
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            if (status) {
                status.classList.add('d-none');
                status.classList.remove('d-flex');
            }
            alert('❌ An unexpected error occurred while scanning the image. Please try again.');
        });
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
