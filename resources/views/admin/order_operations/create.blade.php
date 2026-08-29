@extends('layouts.admin')

@section('title', 'Record Order Operation - Order #' . $order->order_number . ' - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .op-form-title { font-size: 1.15rem !important; }
        .op-form-subtitle { font-size: 0.72rem !important; }
        .op-back-btn { font-size: 0.76rem !important; padding: 0.35rem 0.6rem !important; border-radius: 8px !important; }
        .card-body.p-4 { padding: 1rem 0.85rem !important; }
        .card-body h5 { font-size: 0.92rem !important; margin-bottom: 0.75rem !important; }
        .card-body h6 { font-size: 0.84rem !important; }
        .form-label { font-size: 0.76rem !important; margin-bottom: 0.25rem !important; }
        .form-control, .form-select { font-size: 0.78rem !important; padding: 0.4rem 0.65rem !important; }
        .op-status-btn-group label { font-size: 0.72rem !important; padding: 0.4rem 0.65rem !important; }
        .op-type-card { padding: 0.4rem 0.5rem !important; }
        .op-type-card label { font-size: 0.73rem !important; }
        .op-save-btn { padding: 0.75rem 1rem !important; font-size: 0.85rem !important; }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 op-form-title">Record Order Operation</h3>
        <p class="text-muted small mb-0 op-form-subtitle">Record a return, damage, refund, or post-order adjustment for Order #{{ $order->order_number }}</p>
    </div>
    <a href="{{ route('admin.order-operations.index') }}" class="btn btn-outline-dark rounded-3 px-3 py-1.5 fw-bold shadow-sm op-back-btn">
        &larr; Back<span class="d-none d-md-inline"> to Order Operations</span>
    </a>
</div>

<form action="{{ route('admin.order-operations.store', $order->id) }}" method="POST">
    @csrf
    <div class="row g-3 g-md-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            
            <!-- 1. Operation Status Toggle (ACTIVE / INACTIVE) -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4 border-start border-4 border-warning">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">
                                <i class="fa-solid fa-power-off text-warning me-2"></i> Operation Status
                            </h5>
                            <p class="text-muted small mb-0" style="font-size: 0.76rem;">
                                <strong>ACTIVE:</strong> Real business event included in Profit & Loss.<br>
                                <strong>INACTIVE:</strong> Dummy/testing operation excluded from Profit & Loss calculations.
                            </p>
                        </div>
                        <div class="btn-group op-status-btn-group w-100 w-sm-auto" role="group" aria-label="Status Switch">
                            <input type="radio" class="btn-check" name="status" id="statusActive" value="active" checked>
                            <label class="btn btn-outline-success fw-bold px-3 py-2 text-nowrap" for="statusActive">
                                <i class="fa-solid fa-circle-check me-1"></i> ACTIVE
                            </label>

                            <input type="radio" class="btn-check" name="status" id="statusInactive" value="inactive">
                            <label class="btn btn-outline-secondary fw-bold px-3 py-2 text-nowrap" for="statusInactive">
                                <i class="fa-solid fa-circle-xmark me-1"></i> INACTIVE (Testing)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. What Happened? (Operation Type) -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                        <i class="fa-solid fa-circle-question text-warning me-2"></i> What happened to this product/order?
                    </h5>

                    <div class="row g-2 mb-3">
                        @foreach($operationTypes as $key => $label)
                            <div class="col-6 col-sm-4">
                                <div class="form-check p-2 border rounded-3 bg-light hover-shadow op-type-card d-flex align-items-center">
                                    <input class="form-check-input ms-0 me-2 flex-shrink-0" type="radio" name="operation_type" id="type_{{ $key }}" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} onchange="toggleOtherDescription()">
                                    <label class="form-check-label fw-semibold text-dark small cursor-pointer text-truncate" for="type_{{ $key }}" title="{{ $label }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="otherDescriptionBox" class="mb-2 d-none">
                        <label class="form-label fw-bold small">Specify Other Reason <span class="text-danger">*</span></label>
                        <input type="text" name="other_description" id="otherDescriptionInput" class="form-control rounded-3" placeholder="Enter custom operation details...">
                    </div>
                </div>
            </div>

            <!-- 3. Item Selection & Quantity -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                        <i class="fa-solid fa-box text-warning me-2"></i> Select Item & Quantity
                    </h5>

                    <div class="row g-3">
                        <div class="col-sm-8">
                            <label class="form-label fw-bold small">Purchased Order Item</label>
                            <select name="order_item_id" id="orderItemSelect" class="form-select rounded-3" onchange="onItemChange()">
                                @foreach($order->items as $item)
                                    @php
                                        $itemProd = $item->product;
                                        $itemImg = $itemProd ? $itemProd->primary_image_url : '';
                                    @endphp
                                    <option value="{{ $item->id }}" 
                                            data-qty="{{ $item->quantity }}" 
                                            data-price="{{ $item->price }}"
                                            data-name="{{ $item->product_name }}"
                                            data-size="{{ $item->size }}"
                                            data-image="{{ $itemImg }}">
                                        {{ $item->product_name }} (Size: {{ $item->size }}) - Qty: {{ $item->quantity }} (₹{{ number_format($item->price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label fw-bold small">Affected Quantity (pcs) <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="quantityInput" class="form-control rounded-3" value="1" min="1" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Product Availability / Inventory Restoration -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2 border-bottom pb-2 text-dark">
                        <i class="fa-solid fa-warehouse text-warning me-2"></i> Return Product to Website / Inventory?
                    </h5>
                    <p class="text-muted small mb-3">Should this product item be made available again in website stock?</p>

                    <div class="d-flex flex-column flex-sm-row gap-2.5 gap-sm-4 align-items-start align-items-sm-center">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_product_restored" id="restoreNo" value="0" checked>
                            <label class="form-check-label fw-bold text-dark small" for="restoreNo">
                                <i class="fa-solid fa-ban text-danger me-1"></i> No (Do not restore stock)
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_product_restored" id="restoreYes" value="1">
                            <label class="form-check-label fw-bold text-dark small" for="restoreYes">
                                <i class="fa-solid fa-arrow-rotate-left text-success me-1"></i> Yes (Restore stock to inventory & website)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Refund Details -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2 border-bottom pb-2 text-dark">
                        <i class="fa-solid fa-hand-holding-dollar text-warning me-2"></i> Was Money Refunded?
                    </h5>

                    <div class="d-flex flex-column flex-sm-row gap-2.5 gap-sm-4 align-items-start align-items-sm-center mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_money_refunded" id="refundNo" value="0" checked onchange="toggleRefundBox()">
                            <label class="form-check-label fw-bold text-dark small" for="refundNo">
                                No Refund Issued
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_money_refunded" id="refundYes" value="1" onchange="toggleRefundBox()">
                            <label class="form-check-label fw-bold text-dark small" for="refundYes">
                                Yes (Money Refunded to Customer)
                            </label>
                        </div>
                    </div>

                    <div id="refundDetailsBox" class="p-3 bg-light rounded-3 border d-none">
                        <div class="row g-2.5 g-sm-3">
                            <div class="col-6 col-sm-4">
                                <label class="form-label fw-bold small">Product Refund (₹)</label>
                                <input type="number" step="0.01" name="product_refund_amount" id="productRefundInput" class="form-control rounded-3" value="0.00" min="0" oninput="calculateTotals()">
                            </div>
                            <div class="col-6 col-sm-4">
                                <label class="form-label fw-bold small">Delivery Refund (₹)</label>
                                <input type="number" step="0.01" name="delivery_refund_amount" id="deliveryRefundInput" class="form-control rounded-3" value="0.00" min="0" oninput="calculateTotals()">
                            </div>
                            <div class="col-12 col-sm-4">
                                <label class="form-label fw-bold small">Other Refund (₹)</label>
                                <input type="number" step="0.01" name="other_refund_amount" id="otherRefundInput" class="form-control rounded-3" value="0.00" min="0" oninput="calculateTotals()">
                            </div>
                            <div class="col-12 text-end border-top pt-2 mt-2">
                                <span class="fw-bold small text-dark">Total Refund Amount: </span>
                                <span class="fw-bold fs-5 text-danger" id="totalRefundDisplay">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Additional Expenses -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-receipt text-warning me-2"></i> Additional Expenses
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-bold" style="font-size: 0.76rem;" onclick="addExpenseRow()">
                            <i class="fa-solid fa-plus me-1"></i> Add Expense
                        </button>
                    </div>

                    <div id="expenseRowsContainer">
                        <!-- Dynamic Expense Rows will be inserted here -->
                    </div>
                    <div id="noExpenseNotice" class="text-muted small">No extra operational expenses added yet.</div>
                </div>
            </div>

            <!-- 7. Notes & Save Button -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Operation Notes / Customer Communication</label>
                        <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Enter any specific notes regarding this return or test operation..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-3 shadow-sm text-dark op-save-btn" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> SAVE OPERATION RECORD
                    </button>
                </div>
            </div>

        </div>

        <!-- Right Side: Summary & Live Impact Preview -->
        <div class="col-lg-4">
            <!-- Order & Product Info Summary Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-circle-info text-warning me-2"></i> Order Information</h6>
                </div>
                <div class="card-body p-3 style-small" style="font-size: 0.8rem;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Number:</span>
                        <span class="fw-bold text-warning">{{ $order->order_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Customer Name:</span>
                        <span class="fw-bold text-dark">{{ $order->customer_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Phone Number:</span>
                        <span class="fw-bold text-dark">{{ $order->customer_phone }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Total:</span>
                        <span class="fw-bold text-dark">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Method:</span>
                        <span class="badge bg-light text-dark border text-uppercase">{{ $order->payment_method }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Status:</span>
                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-capitalize">{{ $order->payment_status }}</span>
                    </div>
                </div>
            </div>

            <!-- Live Financial Impact Preview Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4 border-top border-4 border-warning">
                <div class="card-body p-3.5 p-sm-4">
                    <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                        <i class="fa-solid fa-calculator text-warning me-2"></i> P&L Financial Impact Preview
                    </h6>

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Customer Refund:</span>
                        <span class="fw-bold text-danger" id="summaryTotalRefund">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Additional Expenses:</span>
                        <span class="fw-bold text-danger" id="summaryTotalExpense">₹0.00</span>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-dark small">Total Financial Adjustment:</span>
                        <span class="fs-4 fw-bold text-danger" id="summaryTotalAdjustment">-₹0.00</span>
                    </div>

                    <div class="p-2.5 bg-light rounded-3 border" style="font-size: 0.76rem;">
                        <i class="fa-solid fa-circle-info text-info me-1"></i>
                        <span id="pnlImpactNotice" class="text-dark">
                            This adjustment will be <strong>included</strong> in Profit & Loss reporting because the operation is <strong>ACTIVE</strong>.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    let expenseRowIndex = 0;

    function toggleOtherDescription() {
        const selectedType = document.querySelector('input[name="operation_type"]:checked').value;
        const otherBox = document.getElementById('otherDescriptionBox');
        const otherInput = document.getElementById('otherDescriptionInput');
        if (selectedType === 'other') {
            otherBox.classList.remove('d-none');
            otherInput.required = true;
        } else {
            otherBox.classList.add('d-none');
            otherInput.required = false;
        }
    }

    function onItemChange() {
        const select = document.getElementById('orderItemSelect');
        const selectedOpt = select.options[select.selectedIndex];
        if (selectedOpt) {
            const maxQty = parseInt(selectedOpt.getAttribute('data-qty')) || 1;
            const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
            
            const qtyInput = document.getElementById('quantityInput');
            qtyInput.max = maxQty;
            if (parseInt(qtyInput.value) > maxQty) qtyInput.value = maxQty;

            const prodRefundInput = document.getElementById('productRefundInput');
            if (parseFloat(prodRefundInput.value) === 0) {
                prodRefundInput.value = (price * parseInt(qtyInput.value)).toFixed(2);
                calculateTotals();
            }
        }
    }

    function toggleRefundBox() {
        const isRefunded = document.querySelector('input[name="is_money_refunded"]:checked').value === '1';
        const box = document.getElementById('refundDetailsBox');
        if (isRefunded) {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
        calculateTotals();
    }

    function addExpenseRow(desc = '', amount = '') {
        const container = document.getElementById('expenseRowsContainer');
        const notice = document.getElementById('noExpenseNotice');
        if (notice) notice.classList.add('d-none');

        expenseRowIndex++;
        const rowId = `expenseRow_${expenseRowIndex}`;

        const rowHtml = `
            <div class="row g-2 mb-2 align-items-center" id="${rowId}">
                <div class="col-7">
                    <input type="text" name="expenses[${expenseRowIndex}][description]" class="form-control form-control-sm rounded-2" placeholder="e.g. Repair charges / Courier return fee" value="${desc}" oninput="calculateTotals()">
                </div>
                <div class="col-4">
                    <input type="number" step="0.01" name="expenses[${expenseRowIndex}][amount]" class="form-control form-control-sm rounded-2 expense-amount-input" placeholder="0.00" min="0" value="${amount}" oninput="calculateTotals()">
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeExpenseRow('${rowId}')" title="Remove">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>`;

        container.insertAdjacentHTML('beforeend', rowHtml);
        calculateTotals();
    }

    function removeExpenseRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) row.remove();

        const container = document.getElementById('expenseRowsContainer');
        const notice = document.getElementById('noExpenseNotice');
        if (container.children.length === 0 && notice) {
            notice.classList.remove('d-none');
        }
        calculateTotals();
    }

    function calculateTotals() {
        const isRefunded = document.querySelector('input[name="is_money_refunded"]:checked').value === '1';
        let productRefund = 0, deliveryRefund = 0, otherRefund = 0;

        if (isRefunded) {
            productRefund = parseFloat(document.getElementById('productRefundInput').value) || 0;
            deliveryRefund = parseFloat(document.getElementById('deliveryRefundInput').value) || 0;
            otherRefund = parseFloat(document.getElementById('otherRefundInput').value) || 0;
        }

        const totalRefund = productRefund + deliveryRefund + otherRefund;
        document.getElementById('totalRefundDisplay').innerText = '₹' + totalRefund.toFixed(2);
        document.getElementById('summaryTotalRefund').innerText = '₹' + totalRefund.toFixed(2);

        // Additional Expenses Sum
        let totalExpense = 0;
        document.querySelectorAll('.expense-amount-input').forEach(input => {
            totalExpense += parseFloat(input.value) || 0;
        });
        document.getElementById('summaryTotalExpense').innerText = '₹' + totalExpense.toFixed(2);

        const totalAdjustment = totalRefund + totalExpense;
        document.getElementById('summaryTotalAdjustment').innerText = '-₹' + totalAdjustment.toFixed(2);

        // Update active/inactive status notice text
        const isActive = document.querySelector('input[name="status"]:checked').value === 'active';
        const noticeEl = document.getElementById('pnlImpactNotice');
        if (isActive) {
            noticeEl.innerHTML = 'This adjustment will be <strong>included</strong> in Profit & Loss reporting because the operation is <strong>ACTIVE</strong>.';
        } else {
            noticeEl.innerHTML = 'This adjustment will be <strong>EXCLUDED</strong> (₹0 impact) from Profit & Loss because the operation is <strong>INACTIVE</strong>.';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        onItemChange();
        toggleOtherDescription();
        toggleRefundBox();
        
        document.querySelectorAll('input[name="status"]').forEach(r => {
            r.addEventListener('change', calculateTotals);
        });
    });
</script>
@endsection
