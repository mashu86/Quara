@extends('layouts.admin')

@section('title', 'Adjust Order #' . $order->order_number . ' - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .op-form-title { font-size: 1.15rem !important; }
        .op-form-subtitle { font-size: 0.72rem !important; }
        .op-back-btn { font-size: 0.76rem !important; padding: 0.35rem 0.6rem !important; border-radius: 8px !important; }
        .card-body.p-4 { padding: 1rem 0.85rem !important; }
        .card-body h5 { font-size: 0.92rem !important; margin-bottom: 0.75rem !important; }
        .form-label { font-size: 0.76rem !important; margin-bottom: 0.25rem !important; }
        .form-control, .form-select { font-size: 0.78rem !important; padding: 0.4rem 0.65rem !important; }
    }
    .item-adj-card {
        transition: all 0.2s ease;
        border-left: 4px solid var(--qw-gold) !important;
    }
    .item-adj-card.status-cancelled { border-left-color: #6c757d !important; }
    .item-adj-card.status-returned { border: 2px solid #dc3545 !important; border-left: 6px solid #dc3545 !important; background-color: #fff8f8 !important; }
    .item-adj-card.status-exchanged { border-left-color: #0dcaf0 !important; }
    
    .op-adjust-btn {
        background: linear-gradient(135deg, #f5d061 0%, #c99318 100%) !important;
        color: #0d0d0d !important;
        border: none !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        padding: 0.45rem 1.1rem !important;
        border-radius: 50rem !important;
        box-shadow: 0 4px 10px rgba(201, 147, 24, 0.28) !important;
        transition: all 0.2s ease-in-out !important;
        white-space: nowrap !important;
    }
    .op-adjust-btn:hover {
        background: linear-gradient(135deg, #fce082 0%, #da9f1c 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(201, 147, 24, 0.42) !important;
        color: #000 !important;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 op-form-title">Adjust Order #{{ $order->order_number }}</h3>
        <p class="text-muted small mb-0 op-form-subtitle">Product-level returns, cancellations, exchanges, inventory condition & refund management.</p>
    </div>
    <a href="{{ route('admin.order-operations.index') }}" class="btn btn-outline-dark rounded-3 px-3 py-1.5 fw-bold shadow-sm op-back-btn">
        &larr; Back<span class="d-none d-md-inline"> to Order Adjustments</span>
    </a>
</div>

<!-- TOP SUMMARY BAR: Customer & Order Details -->
<div class="card border-0 rounded-4 shadow-sm mb-4 bg-dark text-white">
    <div class="card-body p-3 p-md-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="small text-warning fw-bold text-uppercase" style="letter-spacing: 0.5px;">Customer Details</div>
                <div class="fw-bold fs-6">{{ $order->customer_name }}</div>
                @if($order->customer_phone)
                    <a href="tel:{{ $order->customer_phone }}" class="text-white-50 text-decoration-none small">
                        <i class="fa-solid fa-phone text-success me-1"></i>{{ $order->customer_phone }}
                    </a>
                @endif
            </div>
            <div class="col-md-4 border-start border-secondary ps-md-4">
                <div class="small text-warning fw-bold text-uppercase" style="letter-spacing: 0.5px;">Order Information</div>
                <div class="small text-white-50">Date: <strong class="text-white">{{ ($order->sale_date ?? $order->created_at)->format('d-m-Y') }}</strong></div>
                <div class="small text-white-50">Payment: <strong class="text-white text-uppercase">{{ str_replace('_', ' ', $order->payment_method) }} ({{ ucfirst($order->payment_status) }})</strong></div>
            </div>
            <div class="col-md-4 border-start border-secondary ps-md-4 text-md-end">
                <div class="small text-warning fw-bold text-uppercase" style="letter-spacing: 0.5px;">Order Net Realized Amount</div>
                @php
                    $totalOrderRefunds = (float) $order->operations->where('status', 'active')->sum('total_refund_amount');
                    $netRealizedAmount = max(0, (float) $order->grand_total - $totalOrderRefunds);
                @endphp
                <div class="fs-3 fw-bold text-warning">₹{{ number_format($netRealizedAmount, 2) }}</div>
                @if($totalOrderRefunds > 0)
                    <div class="small text-danger fw-bold"><i class="fa-solid fa-rotate-left me-1"></i> Total Refunded: ₹{{ number_format($totalOrderRefunds, 2) }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- LEFT COLUMN: Product List & Product-Level Adjustment Buttons (col-lg-8) -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 flex-wrap gap-2">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-boxes-packing text-warning me-2"></i> Ordered Products List ({{ $order->items->count() }})
                    </h5>
                    <!-- Button to open Add Product to Order modal -->
                    <button type="button" class="btn btn-sm btn-dark rounded-pill fw-bold px-3 py-1.5 shadow-sm d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fa-solid fa-plus text-warning"></i> Add Product to Order
                    </button>
                </div>

                <p class="text-muted small mb-3">
                    Click <strong>[Edit / Adjust]</strong> on any product to process return, cancellation, inventory condition, or refund for that specific item.
                </p>

                <!-- Product Items List -->
                <div class="d-flex flex-column gap-3">
                    @foreach($order->items as $item)
                        @php
                            $prod = $item->product;
                            $imgUrl = $prod ? $prod->primary_image_url : \App\Models\Setting::logoUrl();
                            $statusClass = 'status-' . ($item->item_status ?? 'active');
                            $itemOps = $order->operations->where('order_item_id', $item->id);
                        @endphp
                        <div class="card border rounded-3 p-3 bg-white shadow-sm item-adj-card {{ $statusClass }}">
                            <div class="d-flex align-items-start gap-3">
                                <!-- Product Thumb (NON-EDITABLE ORIGINAL INFO) -->
                                <div class="p-0.5 bg-white border rounded flex-shrink-0" style="cursor: pointer;" onclick="openImagePreviewModal('{{ addslashes($imgUrl) }}', '{{ addslashes($item->product_name) }}')" title="Click to view full image">
                                    <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" class="rounded d-block" style="width: 50px; height: 60px; object-fit: cover;">
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    @php
                                        $alreadyAdjusted = ($item->item_status === 'returned' || $item->item_status === 'exchanged' || $item->item_status === 'cancelled' || ($itemOps && $itemOps->where('status', 'active')->count() > 0));
                                        $activeOp = $itemOps ? $itemOps->where('status', 'active')->first() : null;

                                        $itemPayload = [
                                            'id' => $item->id,
                                            'product_name' => $item->product_name,
                                            'size' => $item->size,
                                            'quantity' => (int) $item->quantity,
                                            'unit_price' => (float) $item->unit_price,
                                            'subtotal' => (float) $item->subtotal,
                                            'item_status' => $item->item_status,
                                            'inventory_condition' => $item->inventory_condition,
                                            'refund_amount' => (float) $item->refund_amount,
                                            'product' => $prod ? [
                                                'primary_image_url' => $prod->primary_image_url,
                                            ] : null,
                                        ];

                                        $opPayload = $activeOp ? [
                                            'id' => $activeOp->id,
                                            'operation_type' => $activeOp->operation_type,
                                            'inventory_condition' => $activeOp->inventory_condition,
                                            'total_refund_amount' => (float) $activeOp->total_refund_amount,
                                            'price_difference' => (float) $activeOp->price_difference,
                                            'replacement_quantity' => (int) $activeOp->replacement_quantity,
                                            'created_at' => $activeOp->created_at ? $activeOp->created_at->format('d-m-Y h:i A') : '',
                                            'notes' => $activeOp->notes,
                                            'replacement_product' => $activeOp->replacementProduct ? [
                                                'name' => $activeOp->replacementProduct->name,
                                                'primary_image_url' => $activeOp->replacementProduct->primary_image_url,
                                            ] : null,
                                            'replacement_product_size' => $activeOp->replacementProductSize ? [
                                                'size' => $activeOp->replacementProductSize->size,
                                            ] : null,
                                        ] : null;

                                        $itemB64 = base64_encode(json_encode($itemPayload));
                                        $opB64 = base64_encode(json_encode($opPayload));
                                    @endphp
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.92rem;">{{ $item->product_name }}</h6>
                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                            @if($alreadyAdjusted)
                                                <!-- Return / Adjustment Details Button (Read-Only) -->
                                                <button type="button" class="btn btn-sm btn-info text-dark rounded-pill px-2.5 py-1 font-sans fw-bold d-inline-flex align-items-center gap-1 shadow-xs"
                                                        data-item-b64="{{ $itemB64 }}"
                                                        data-op-b64="{{ $opB64 }}"
                                                        onclick="triggerViewAdjustmentDetails(this)"
                                                        style="font-size: 0.74rem !important; padding: 0.28rem 0.65rem !important;" title="View read-only return & adjustment details">
                                                    <i class="fa-solid fa-file-invoice text-dark"></i>
                                                    <span>Return Details</span>
                                                </button>
                                                <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1.5" style="font-size: 0.72rem;" title="This item has already been adjusted / returned and cannot be edited or adjusted again.">
                                                    <i class="fa-solid fa-lock me-1 text-warning"></i> Locked
                                                </span>
                                            @else
                                                <button type="button" class="btn op-adjust-btn d-inline-flex align-items-center gap-1.5 flex-shrink-0"
                                                        data-item-b64="{{ $itemB64 }}"
                                                        onclick="triggerOpenItemAdjustment(this)"
                                                        style="padding: 0.28rem 0.75rem !important; font-size: 0.74rem !important;">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                    <span>Edit / Adjust</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.78rem;">
                                        <span class="badge bg-dark">Size: {{ $item->size }}</span>
                                        <span class="text-muted fw-semibold">Quantity: {{ $item->quantity }} pcs</span>
                                        <span class="text-muted">Price: ₹{{ number_format($item->unit_price, 2) }}</span>
                                        <span class="fw-bold text-dark">Subtotal: ₹{{ number_format($item->subtotal, 2) }}</span>
                                    </div>
                                    
                                    <!-- Status Badges -->
                                    <div class="mt-2 d-flex align-items-center gap-1.5 flex-wrap">
                                        @if(($item->item_status ?? 'active') === 'cancelled')
                                            <span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> Order Cancelled</span>
                                        @elseif(($item->item_status ?? 'active') === 'returned')
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-rotate-left me-1"></i> Product Returned</span>
                                        @elseif(($item->item_status ?? 'active') === 'exchanged')
                                            <span class="badge bg-info text-dark"><i class="fa-solid fa-right-left me-1"></i> Product Exchanged</span>
                                        @else
                                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Active / Normal</span>
                                        @endif

                                        @if($item->inventory_condition === 'return_to_stock')
                                            <span class="badge bg-success"><i class="fa-solid fa-box-archive me-1"></i> Restocked (+{{ $item->quantity }})</span>
                                        @elseif($item->inventory_condition === 'do_not_restock')
                                            <span class="badge bg-secondary"><i class="fa-solid fa-snowflake me-1"></i> Frozen / Not Restocked</span>
                                        @endif

                                        @if($item->refund_amount > 0)
                                            <span class="badge bg-danger"><i class="fa-solid fa-hand-holding-dollar me-1"></i> Refund: ₹{{ number_format($item->refund_amount, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: Order Summary & Shipping Charge (col-lg-4) -->
    <div class="col-lg-4">
        <!-- Order Financial Summary Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                    <i class="fa-solid fa-calculator text-warning me-2"></i> Order Summary
                </h5>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Product Value:</span>
                    <strong class="text-dark">₹{{ number_format($order->subtotal, 2) }}</strong>
                </div>

                <!-- 1. Common Shipping Charge Editable Form -->
                <form action="{{ route('admin.order-operations.update-shipping', $order->id) }}" method="POST" class="mb-3 p-2.5 bg-light rounded-3 border">
                    @csrf
                    <label class="form-label fw-bold small text-dark mb-1"><i class="fa-solid fa-truck text-warning me-1"></i> Common Shipping Charge (₹)</label>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-white">₹</span>
                        <input type="number" step="0.01" name="shipping" class="form-control" value="{{ old('shipping', $order->shipping) }}" min="0" required>
                        <button type="submit" class="btn btn-dark fw-bold">Update</button>
                    </div>
                    <div class="form-text small text-muted" style="font-size: 0.68rem;">Order-wide shipping fee.</div>
                </form>

                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted">Order Grand Total:</span>
                    <strong class="text-dark fs-6">₹{{ number_format($order->grand_total, 2) }}</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-danger fw-semibold">Refund Total Amount:</span>
                    <strong class="text-danger fs-6">-₹{{ number_format($totalOrderRefunds, 2) }}</strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">Net Realized Revenue:</span>
                    <span class="fs-4 fw-bold text-warning">₹{{ number_format($netRealizedAmount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Recent Adjustments Log Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                    <i class="fa-solid fa-receipt text-warning me-2"></i> Order Adjustments Log ({{ $order->operations->count() }})
                </h5>

                @forelse($order->operations as $op)
                    <div class="p-2.5 bg-light rounded-3 mb-2 border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-{{ $op->status === 'active' ? 'success' : 'secondary' }}" style="font-size: 0.6rem;">{{ strtoupper($op->status) }}</span>
                            <span class="small text-muted" style="font-size: 0.68rem;">{{ $op->created_at->format('d-m-Y h:i A') }}</span>
                        </div>
                        <div class="fw-bold text-dark small">{{ $op->operation_type_label }}</div>
                        @if($op->orderItem)
                            <div class="small text-muted" style="font-size: 0.72rem;">Product: {{ $op->orderItem->product_name }} (Size: {{ $op->orderItem->size }})</div>
                        @endif
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <span class="badge bg-dark" style="font-size: 0.6rem;">Condition: {{ str_replace('_', ' ', $op->inventory_condition ?? 'return_to_stock') }}</span>
                            @if($op->total_refund_amount > 0)
                                <span class="fw-bold text-danger small">Refund: ₹{{ number_format($op->total_refund_amount, 2) }}</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-end gap-1 mt-2 pt-1 border-top">
                            <a href="{{ route('admin.order-operations.show', $op->id) }}" class="btn btn-xs btn-outline-dark px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">
                                <i class="fa-solid fa-eye me-1"></i> View Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-3 small">No adjustments recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 1. PRODUCT-LEVEL ADJUSTMENT MODAL           -->
<!-- ========================================== -->
<div class="modal fade" id="itemAdjustmentModal" tabindex="-1" aria-labelledby="itemAdjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold fs-6" id="itemAdjustmentModalLabel">
                    <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Product-Level Adjustment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.order-operations.store', $order->id) }}" method="POST" id="itemAdjustmentForm">
                @csrf
                <input type="hidden" name="order_item_id" id="modal_order_item_id" value="">

                <div class="modal-body p-4">
                    <!-- Non-editable Product Info Banner -->
                    <div class="p-3 bg-light rounded-3 border mb-3 d-flex align-items-center gap-3">
                        <div class="p-0.5 bg-white border rounded">
                            <img id="modal_product_img" src="" alt="Prod" class="rounded d-block" style="width: 42px; height: 50px; object-fit: cover;">
                        </div>
                        <div>
                            <div class="fw-bold text-dark" id="modal_product_name">Product Name</div>
                            <div class="small text-muted" id="modal_product_details">Size: S | Qty: 1 | Price: ₹0.00</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">What happened to this product? <span class="text-danger">*</span></label>
                        <select name="operation_type" id="modal_operation_type" class="form-select rounded-3" required onchange="onModalActionChange()">
                            <option value="product_returned" selected>Product Returned</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Return Date <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" id="modal_return_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        <div class="form-text small text-muted">The date when the product return took place.</div>
                    </div>

                    <hr>

                    <!-- 2. Inventory Condition -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Inventory Condition <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="inventory_condition" id="invRestock" value="return_to_stock" checked>
                                    <label class="form-check-label fw-semibold text-dark small cursor-pointer" for="invRestock">
                                        <i class="fa-solid fa-box-archive text-success me-1"></i> Return to Stock
                                    </label>
                                </div>
                                <div class="form-text small text-muted">Adds returned quantity back to available inventory.</div>
                            </div>
                            <div class="col-6">
                                <div class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="inventory_condition" id="invDoNotRestock" value="do_not_restock">
                                    <label class="form-check-label fw-semibold text-dark small cursor-pointer" for="invDoNotRestock">
                                        <i class="fa-solid fa-snowflake text-secondary me-1"></i> Do Not Restock / Freeze
                                    </label>
                                </div>
                                <div class="form-text small text-muted">Item remains frozen/non-restocked. Stock does NOT increase.</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- 3. What about refund? (Hidden during Product Exchange) -->
                    <div class="mb-3" id="modalRefundSection">
                        <label class="form-label fw-bold text-dark">What about refund? <span class="text-danger">*</span></label>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <div class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="refund_option" id="refundNone" value="no_refund" checked onchange="onRefundOptionChange()">
                                    <label class="form-check-label fw-semibold text-dark small cursor-pointer" for="refundNone">
                                        <i class="fa-solid fa-hand text-secondary me-1"></i> No Refund
                                    </label>
                                </div>
                                <div class="form-text small text-muted">Money received remains intact. No money refunded.</div>
                            </div>
                            <div class="col-6">
                                <div class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="refund_option" id="refundYes" value="refund" onchange="onRefundOptionChange()">
                                    <label class="form-check-label fw-semibold text-dark small cursor-pointer" for="refundYes">
                                        <i class="fa-solid fa-hand-holding-dollar text-danger me-1"></i> Refund
                                    </label>
                                </div>
                                <div class="form-text small text-muted">Deducts refund amount from order realized revenue.</div>
                            </div>
                        </div>

                        <!-- Refund Amount & Refund Date Input (Shown when Refund option selected) -->
                        <div id="refundAmountBox" class="p-3 bg-light rounded-3 border d-none">
                            <div class="mb-2">
                                <label class="form-label fw-bold small text-dark mb-1">Enter Refund Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">₹</span>
                                    <input type="number" step="0.01" name="refund_amount" id="modal_refund_amount" class="form-control" placeholder="0.00" min="0">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small text-dark mb-1">Refund Date <span class="text-danger">*</span></label>
                                <input type="date" name="refund_date" id="modal_refund_date" class="form-control" value="{{ date('Y-m-d') }}">
                                <div class="form-text small text-muted">Financial reports aggregate refund based on this refund date.</div>
                            </div>
                            <div class="form-text small text-muted">Amount to be deducted from order realized revenue and logged as an immutable refund record.</div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark">Notes / Reason (Optional)</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Size didn't fit customer / Returned via courier..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-check me-1"></i> Save Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. ADD PRODUCT TO ORDER MODAL               -->
<!-- ========================================== -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold fs-6" id="addProductModalLabel">
                    <i class="fa-solid fa-plus text-warning me-2"></i> Add Product to Order #{{ $order->order_number }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.order-operations.add-item', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">Select Product & Size <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <div id="add_prod_thumb_container" class="d-none flex-shrink-0">
                                <img id="add_prod_thumb_img" class="rounded-3 border shadow-sm" src="" alt="Thumb" style="width: 44px; height: 50px; object-fit: cover; cursor: pointer;" onclick="openImagePreviewModal(this.src, 'Product')" title="Click to view full image">
                            </div>
                            <div class="flex-grow-1 position-relative" style="cursor: pointer;" onclick="openVisualPickerForAddProduct()" title="Click to Pick Product with Image">
                                <input type="text" id="add_product_display" class="form-control rounded-3 bg-white fw-semibold" readonly placeholder="👉 Click here to Pick Product with Image..." style="cursor: pointer; font-size: 0.85rem;" required>
                                <select name="add_product_size_id" id="modal_add_product_size_id" class="form-select rounded-3 d-none" required onchange="onAddProductSelect(this)">
                                    <option value="">-- Select Product & Size --</option>
                                    @foreach($allProducts as $p)
                                        @foreach($p->sizes as $sz)
                                            <option value="{{ $sz->id }}" data-product-id="{{ $p->id }}" data-price="{{ $p->final_price }}" data-stock="{{ $sz->stock }}" data-name="{{ $p->name }}" data-size="{{ $sz->size }}" data-image="{{ $p->primary_image_url }}">
                                                {{ $p->name }} - Size: {{ $sz->size }} (Stock: {{ $sz->stock }} pcs) - ₹{{ number_format($p->final_price, 2) }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn btn-warning text-dark border border-warning-subtle fw-bold btn-sm rounded-3 px-2 px-md-3 py-2 flex-shrink-0 shadow-sm pick-product-btn" onclick="openVisualPickerForAddProduct()" title="Pick product by photo grid">
                                <i class="fa-solid fa-images me-1"></i> <span class="d-none d-sm-inline">Pick Product Image</span><span class="d-inline d-sm-none">Pick</span>
                            </button>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="add_quantity" class="form-control rounded-3" value="1" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark">Selling Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="add_unit_price" id="add_unit_price" class="form-control rounded-3" placeholder="0.00" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-plus me-1"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. READ-ONLY RETURN / ADJUSTMENT DETAILS MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="viewAdjustmentDetailsModal" tabindex="-1" aria-labelledby="viewAdjustmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <h5 class="modal-title font-serif fw-bold fs-6" id="viewAdjustmentDetailsModalLabel">
                    <i class="fa-solid fa-receipt text-warning me-2"></i> Product Adjustment Details (Read-Only)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Product Information Banner -->
                <div class="card border rounded-3 shadow-xs mb-3 bg-white">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="p-0.5 bg-white border rounded flex-shrink-0 cursor-pointer" onclick="openImagePreviewModal(document.getElementById('view_detail_product_img').src, document.getElementById('view_detail_product_name').textContent)" title="Click to view large photo">
                            <img id="view_detail_product_img" src="" alt="Product" class="rounded d-block" style="width: 50px; height: 60px; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-bold text-dark mb-1" id="view_detail_product_name">Product Name</h6>
                            <div class="small text-muted" id="view_detail_product_info">Size: - | Qty: - | Price: ₹0.00</div>
                        </div>
                    </div>
                </div>

                <!-- Adjustment Status & Inventory Cards -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="card border rounded-3 h-100 bg-white shadow-xs">
                            <div class="card-body p-3">
                                <div class="small text-uppercase text-muted fw-bold mb-1" style="font-size: 0.68rem;">Adjustment Action</div>
                                <div id="view_detail_status_badge" class="mb-2"></div>
                                <div class="small text-muted" id="view_detail_date">Date: -</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border rounded-3 h-100 bg-white shadow-xs">
                            <div class="card-body p-3">
                                <div class="small text-uppercase text-muted fw-bold mb-1" style="font-size: 0.68rem;">Inventory Condition</div>
                                <div id="view_detail_inventory_badge" class="fw-semibold text-dark mb-1"></div>
                                <div class="small text-muted" id="view_detail_inventory_desc">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refund Details Card -->
                <div class="card border rounded-3 shadow-xs mb-3 bg-white" id="view_detail_refund_card">
                    <div class="card-body p-3">
                        <h6 class="fw-bold text-dark fs-6 mb-2 border-bottom pb-2">
                            <i class="fa-solid fa-hand-holding-dollar text-warning me-1.5"></i> Refund Information
                        </h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small">Refund Status:</span>
                            <span id="view_detail_refund_status" class="fw-bold fs-6 text-danger">₹0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Exchange Replacement Details (Shown if exchanged) -->
                <div class="card border border-info rounded-3 shadow-xs mb-3 bg-info-subtle d-none" id="view_detail_exchange_card">
                    <div class="card-body p-3">
                        <h6 class="fw-bold text-info-emphasis fs-6 mb-2 border-bottom border-info-subtle pb-2">
                            <i class="fa-solid fa-right-left text-info me-1.5"></i> Replacement Product Details
                        </h6>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img id="view_detail_repl_img" src="" alt="Replacement" class="rounded-3 border flex-shrink-0" style="width: 44px; height: 52px; object-fit: cover;">
                            <div>
                                <div class="fw-bold text-dark" id="view_detail_repl_name">Replacement Product</div>
                                <div class="small text-muted" id="view_detail_repl_info">Size: - | Qty: - | Price: ₹0.00</div>
                            </div>
                        </div>
                        <div class="p-2 bg-white rounded border text-center fw-bold small text-dark" id="view_detail_price_diff">
                            Price Difference: ₹0.00
                        </div>
                    </div>
                </div>

                <!-- Reason / Notes -->
                <div class="card border rounded-3 shadow-xs mb-3 bg-white">
                    <div class="card-body p-3">
                        <div class="small text-uppercase text-muted fw-bold mb-1" style="font-size: 0.68rem;">Notes / Reason</div>
                        <div class="small text-dark" id="view_detail_notes">No notes provided.</div>
                    </div>
                </div>

                <!-- Permanent Lock Notice Alert -->
                <div class="alert alert-warning border border-warning-subtle rounded-3 p-2.5 mb-0 d-flex align-items-center gap-2" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-lock text-dark fs-5 flex-shrink-0"></i>
                    <div>
                        <strong class="text-dark">Adjustment Finalized &amp; Locked:</strong>
                        <span class="text-muted">This product adjustment is permanent and read-only. Editing is not permitted (a product can only be adjusted/exchanged once per order).</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white py-2.5 px-4 border-top">
                <button type="button" class="btn btn-dark rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openImagePreviewModal(imageUrl, title) {
        let modalEl = document.getElementById('imagePreviewModal');
        if (!modalEl) {
            const modalHtml = `
                <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 1075;">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-dark text-white py-2.5 px-3">
                                <h5 class="modal-title fs-6 fw-bold text-truncate" id="imagePreviewModalTitle">Product Image Preview</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-2 p-sm-3 text-center bg-dark d-flex align-items-center justify-content-center" style="min-height: 350px;">
                                <img id="imagePreviewModalImg" src="" alt="Product Large Image" class="img-fluid rounded-3 shadow" style="max-height: 80vh; max-width: 100%; object-fit: contain;">
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

    function triggerViewAdjustmentDetails(btn) {
        try {
            const itemB64 = btn.getAttribute('data-item-b64');
            const opB64 = btn.getAttribute('data-op-b64');
            const itemData = itemB64 ? JSON.parse(atob(itemB64)) : null;
            const opData = (opB64 && opB64 !== 'null') ? JSON.parse(atob(opB64)) : null;
            if (itemData) {
                openViewAdjustmentDetailsModal(itemData, opData);
            }
        } catch (e) {
            console.error('Error parsing adjustment details payload:', e);
        }
    }

    function triggerOpenItemAdjustment(btn) {
        try {
            const itemB64 = btn.getAttribute('data-item-b64');
            const itemData = itemB64 ? JSON.parse(atob(itemB64)) : null;
            if (itemData) {
                openItemAdjustmentModal(itemData);
            }
        } catch (e) {
            console.error('Error parsing item data payload:', e);
        }
    }

    function openViewAdjustmentDetailsModal(itemData, opData) {
        // Populate Product Card
        document.getElementById('view_detail_product_name').textContent = itemData.product_name || 'Product';
        document.getElementById('view_detail_product_info').textContent = `Size: ${itemData.size || '-'} | Qty: ${itemData.quantity || 1} pcs | Unit Price: ₹${parseFloat(itemData.unit_price || 0).toFixed(2)} | Subtotal: ₹${parseFloat(itemData.subtotal || 0).toFixed(2)}`;
        
        const prodImg = (itemData.product && itemData.product.primary_image_url) ? itemData.product.primary_image_url : (opData && opData.product && opData.product.primary_image_url ? opData.product.primary_image_url : '{{ \App\Models\Setting::logoUrl() }}');
        document.getElementById('view_detail_product_img').src = prodImg;

        // Status Badge
        const statusBadgeBox = document.getElementById('view_detail_status_badge');
        const status = itemData.item_status || (opData ? opData.operation_type : 'returned');
        if (status === 'returned' || status === 'product_returned') {
            statusBadgeBox.innerHTML = `<span class="badge bg-warning text-dark px-3 py-1.5 fs-6"><i class="fa-solid fa-rotate-left me-1"></i> Product Returned</span>`;
        } else if (status === 'exchanged' || status === 'product_exchange') {
            statusBadgeBox.innerHTML = `<span class="badge bg-info text-dark px-3 py-1.5 fs-6"><i class="fa-solid fa-right-left me-1"></i> Product Exchanged</span>`;
        } else if (status === 'cancelled' || status === 'order_cancelled') {
            statusBadgeBox.innerHTML = `<span class="badge bg-danger px-3 py-1.5 fs-6"><i class="fa-solid fa-ban me-1"></i> Order Cancelled</span>`;
        } else {
            statusBadgeBox.innerHTML = `<span class="badge bg-secondary px-3 py-1.5 fs-6">Adjusted</span>`;
        }

        // Date
        const dateStr = opData ? (opData.created_at || '') : '';
        document.getElementById('view_detail_date').textContent = dateStr ? `Recorded: ${dateStr}` : '';

        // Inventory Condition
        const invCond = itemData.inventory_condition || (opData ? opData.inventory_condition : 'return_to_stock');
        const invBadge = document.getElementById('view_detail_inventory_badge');
        const invDesc = document.getElementById('view_detail_inventory_desc');
        if (invCond === 'return_to_stock') {
            invBadge.innerHTML = `<span class="badge bg-success"><i class="fa-solid fa-box-archive me-1"></i> Restocked (+${itemData.quantity || 1} pcs)</span>`;
            invDesc.textContent = 'Returned quantity added back to available inventory.';
        } else {
            invBadge.innerHTML = `<span class="badge bg-secondary"><i class="fa-solid fa-snowflake me-1"></i> Do Not Restock / Frozen</span>`;
            invDesc.textContent = 'Stock was not restocked into inventory.';
        }

        // Refund
        const refundAmt = parseFloat(itemData.refund_amount || (opData ? opData.total_refund_amount : 0));
        const refundStatusBox = document.getElementById('view_detail_refund_status');
        if (refundAmt > 0) {
            refundStatusBox.className = 'fw-bold fs-6 text-danger';
            refundStatusBox.innerHTML = `<i class="fa-solid fa-hand-holding-dollar me-1"></i> Refunded: ₹${refundAmt.toFixed(2)}`;
        } else {
            refundStatusBox.className = 'fw-bold fs-6 text-secondary';
            refundStatusBox.innerHTML = `<i class="fa-solid fa-hand me-1"></i> No Refund (₹0.00)`;
        }

        // Exchange Card
        const exchangeCard = document.getElementById('view_detail_exchange_card');
        if (status === 'exchanged' || (opData && opData.operation_type === 'product_exchange')) {
            exchangeCard.classList.remove('d-none');
            if (opData && opData.replacement_product) {
                document.getElementById('view_detail_repl_name').textContent = opData.replacement_product.name || 'Replacement Product';
                const replSize = opData.replacement_product_size ? opData.replacement_product_size.size : 'N/A';
                const replQty = opData.replacement_quantity || 1;
                document.getElementById('view_detail_repl_info').textContent = `Size: ${replSize} | Qty: ${replQty} pcs`;
                const replImg = opData.replacement_product.primary_image_url || '{{ \App\Models\Setting::logoUrl() }}';
                document.getElementById('view_detail_repl_img').src = replImg;

                const priceDiff = parseFloat(opData.price_difference || 0);
                const diffBox = document.getElementById('view_detail_price_diff');
                if (priceDiff > 0) {
                    diffBox.className = 'p-2 bg-warning-subtle rounded border text-center fw-bold small text-dark';
                    diffBox.textContent = `Customer paid additional: ₹${priceDiff.toFixed(2)}`;
                } else if (priceDiff < 0) {
                    diffBox.className = 'p-2 bg-success-subtle rounded border text-center fw-bold small text-success';
                    diffBox.textContent = `Customer refunded difference: ₹${Math.abs(priceDiff).toFixed(2)}`;
                } else {
                    diffBox.className = 'p-2 bg-white rounded border text-center fw-bold small text-dark';
                    diffBox.textContent = `Even Exchange (No Price Difference)`;
                }
            }
        } else {
            exchangeCard.classList.add('d-none');
        }

        // Notes
        const notes = itemData.notes || (opData ? opData.notes : '') || 'No additional notes.';
        document.getElementById('view_detail_notes').textContent = notes;

        const modal = new bootstrap.Modal(document.getElementById('viewAdjustmentDetailsModal'));
        modal.show();
    }

    // Global Products Array
    const opProductsData = [
        @foreach($allProducts as $prod)
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

    let activeItemData = null;
    let opPickerContext = 'exchange'; // 'exchange' or 'add_product'

    function openItemAdjustmentModal(itemData) {
        activeItemData = itemData;
        
        document.getElementById('modal_order_item_id').value = itemData.id;
        document.getElementById('modal_product_name').textContent = itemData.product_name;
        document.getElementById('modal_product_details').textContent = `Size: ${itemData.size} | Qty: ${itemData.quantity} pcs | Price: ₹${parseFloat(itemData.unit_price).toFixed(2)}`;
        
        const imgUrl = (itemData.product && itemData.product.primary_image_url) ? itemData.product.primary_image_url : '{{ \App\Models\Setting::logoUrl() }}';
        document.getElementById('modal_product_img').src = imgUrl;

        // Set action type
        const opTypeSelect = document.getElementById('modal_operation_type');
        opTypeSelect.value = 'product_returned';

        // Set inventory condition
        if (itemData.inventory_condition === 'do_not_restock') {
            document.getElementById('invDoNotRestock').checked = true;
        } else {
            document.getElementById('invRestock').checked = true;
        }

        // Set refund option
        const refundAmt = parseFloat(itemData.refund_amount || 0);
        if (refundAmt > 0) {
            document.getElementById('refundYes').checked = true;
            document.getElementById('modal_refund_amount').value = refundAmt.toFixed(2);
        } else {
            document.getElementById('refundNone').checked = true;
            document.getElementById('modal_refund_amount').value = (parseFloat(itemData.subtotal) || 0).toFixed(2);
        }

        onModalActionChange();
        onRefundOptionChange();

        const modal = new bootstrap.Modal(document.getElementById('itemAdjustmentModal'));
        modal.show();
    }

    function onModalActionChange() {
        const refundSection = document.getElementById('modalRefundSection');
        if (refundSection) refundSection.classList.remove('d-none');
    }

    function onRefundOptionChange() {
        const isRefund = document.getElementById('refundYes').checked;
        const refundBox = document.getElementById('refundAmountBox');
        if (isRefund) {
            refundBox.classList.remove('d-none');
        } else {
            refundBox.classList.add('d-none');
        }
    }

    function onExchangeProductChange() {
        const select = document.getElementById('modal_replacement_size_id');
        const selectedOpt = select.options[select.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
            const name = selectedOpt.getAttribute('data-name') || '';
            const size = selectedOpt.getAttribute('data-size') || '';
            const img = selectedOpt.getAttribute('data-image') || '';

            document.getElementById('modal_replacement_price').value = price.toFixed(2);
            document.getElementById('exchange_product_display').value = `${name} (Size: ${size} - ₹${price.toFixed(2)})`;
            if (img) {
                document.getElementById('exchange_thumb_img').src = img;
                document.getElementById('exchange_thumb_container').classList.remove('d-none');
            }
        }
        calcExchangePriceDiff();
    }

    function calcExchangePriceDiff() {
        if (!activeItemData) return;
        const replPrice = parseFloat(document.getElementById('modal_replacement_price').value) || 0;
        const replQty = parseInt(document.getElementById('modal_replacement_qty').value) || 1;
        const replSubtotal = replPrice * replQty;
        const origSubtotal = parseFloat(activeItemData.subtotal) || 0;
        const diff = replSubtotal - origSubtotal;

        const notice = document.getElementById('exchangePriceDiffNotice');
        if (diff > 0) {
            notice.className = 'p-2 bg-warning-subtle rounded border text-center fw-bold small text-dark';
            notice.textContent = `Customer owes additional payment of: ₹${diff.toFixed(2)}`;
        } else if (diff < 0) {
            notice.className = 'p-2 bg-success-subtle rounded border text-center fw-bold small text-success';
            notice.textContent = `Customer is owed a refund of: ₹${Math.abs(diff).toFixed(2)}`;
        } else {
            notice.className = 'p-2 bg-white rounded border text-center fw-bold small text-dark';
            notice.textContent = `Even Exchange (No Price Difference)`;
        }
    }

    function onAddProductSelect(selectElem) {
        const opt = selectElem.options[selectElem.selectedIndex];
        if (opt && opt.value) {
            const price = parseFloat(opt.getAttribute('data-price')) || 0;
            const name = opt.getAttribute('data-name') || '';
            const size = opt.getAttribute('data-size') || '';
            const img = opt.getAttribute('data-image') || '';

            document.getElementById('add_unit_price').value = price.toFixed(2);
            document.getElementById('add_product_display').value = `${name} (Size: ${size} - ₹${price.toFixed(2)})`;
            if (img) {
                document.getElementById('add_prod_thumb_img').src = img;
                document.getElementById('add_prod_thumb_container').classList.remove('d-none');
            }
        }
    }

    function openVisualPickerForExchange() {
        opPickerContext = 'exchange';
        openOpVisualPickerModal();
    }

    function openVisualPickerForAddProduct() {
        opPickerContext = 'add_product';
        openOpVisualPickerModal();
    }

    function openOpVisualPickerModal() {
        let modalEl = document.getElementById('opVisualProductPickerModal');
        if (!modalEl) {
            const modalHtml = `
                <div class="modal fade" id="opVisualProductPickerModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-dark text-white py-2.5 px-3">
                                <h5 class="modal-title fs-6 fw-bold">
                                    <i class="fa-solid fa-images text-warning me-2"></i> Select Product by Image
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-3 bg-light" style="max-height: 78vh; overflow-y: auto;">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <div class="flex-grow-1" style="min-width: 200px;">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                            <input type="text" id="opVisualPickerSearchInput" class="form-control border-start-0 ps-0 rounded-end-pill py-2" placeholder="Type product name to search..." oninput="renderOpVisualPickerGrid()">
                                        </div>
                                    </div>
                                    <!-- Filter Pills: Available vs Booked -->
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="op_stock_filter" id="opFilterAvailable" value="available" checked onchange="renderOpVisualPickerGrid()">
                                        <label class="btn btn-outline-success fw-bold px-2.5 py-1 rounded-start-pill" for="opFilterAvailable">🟢 Available</label>

                                        <input type="radio" class="btn-check" name="op_stock_filter" id="opFilterBooked" value="booked" onchange="renderOpVisualPickerGrid()">
                                        <label class="btn btn-outline-warning text-dark fw-bold px-2.5 py-1 rounded-end-pill" for="opFilterBooked">🔒 Booked</label>
                                    </div>
                                </div>
                                <div class="row g-2.5" id="opVisualPickerGrid">
                                    <!-- Rendered dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modalEl = document.getElementById('opVisualProductPickerModal');
        }

        document.getElementById('opVisualPickerSearchInput').value = '';
        renderOpVisualPickerGrid();

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function renderOpVisualPickerGrid() {
        const grid = document.getElementById('opVisualPickerGrid');
        if (!grid) return;

        const searchVal = (document.getElementById('opVisualPickerSearchInput')?.value || '').toLowerCase().trim();
        const filterBookedRadio = document.getElementById('opFilterBooked');
        const isBookedFilter = filterBookedRadio ? filterBookedRadio.checked : false;

        let html = '';
        let matchCount = 0;

        opProductsData.forEach(prod => {
            const isNameMatched = !searchVal || prod.name.toLowerCase().includes(searchVal);
            if (!isNameMatched) return;

            const isOut = prod.isOut;
            const physicalStock = prod.physicalStock;

            let badgeHtml = '';

            if (physicalStock <= 0) {
                // Hide 0 stock products to keep picker clean
                return;
            }

            if (isOut) {
                if (!isBookedFilter) return; // Hide booked when available filter is active
                let bookedInfo = prod.bookedBy ? `: ${prod.bookedBy}` : '';
                badgeHtml = `<span class="badge bg-warning text-dark">🔒 Booked${bookedInfo} (${physicalStock} pcs)</span>`;
            } else {
                if (isBookedFilter) return; // Hide available when booked filter is active
                badgeHtml = `<span class="badge bg-success">🟢 ${physicalStock} pcs in stock</span>`;
            }

            matchCount++;

            let sizeOptionsHtml = '';
            if (prod.sizes && prod.sizes.length > 0) {
                prod.sizes.forEach(sz => {
                    const disabled = sz.stock <= 0 ? 'disabled' : '';
                    sizeOptionsHtml += `<option value="${sz.id}" data-size="${sz.size}" data-stock="${sz.stock}" ${disabled}>Size: ${sz.size} (Stock: ${sz.stock} pcs)</option>`;
                });
            }

            const safeName = prod.name.replace(/'/g, "\\'");
            html += `
                <div class="col-6 col-sm-4 col-md-3">
                    <div class="card h-100 border rounded-3 shadow-sm product-picker-card">
                        <div class="position-relative bg-white text-center p-1 rounded-top-3" style="cursor: pointer;" onclick="openImagePreviewModal('${prod.image}', '${safeName}')" title="Click to view image">
                            <img src="${prod.image}" class="img-fluid rounded-2" style="height: 110px; width: 100%; object-fit: cover;" alt="${prod.name}">
                            <button type="button" class="btn btn-sm btn-dark text-warning rounded-circle position-absolute top-0 end-0 m-1 shadow-sm p-0 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.65rem;" onclick="event.stopPropagation(); openImagePreviewModal('${prod.image}', '${safeName}')" title="View Image">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold small text-dark mb-1 text-truncate" title="${prod.name}">${prod.name}</h6>
                                <div class="fw-bold text-success small mb-1">₹${prod.price.toFixed(2)}</div>
                                <div class="mb-2">${badgeHtml}</div>
                                <select class="form-select form-select-sm rounded-2 mb-2" id="op_picker_size_${prod.id}" style="font-size: 0.72rem;">
                                    ${sizeOptionsHtml}
                                </select>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-dark fw-bold px-2 py-1 flex-shrink-0" style="font-size: 0.72rem;" onclick="openImagePreviewModal('${prod.image}', '${safeName}')" title="View Image">
                                    <i class="fa-solid fa-eye text-warning"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold flex-grow-1 py-1" style="font-size: 0.72rem;" onclick="selectProductFromOpPicker(${prod.id})">
                                    <i class="fa-solid fa-check me-1"></i> Select
                                </button>
                            </div>
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

    function selectProductFromOpPicker(prodId) {
        const prod = opProductsData.find(p => p.id === prodId);
        if (!prod) return;

        const sizeSelectElem = document.getElementById(`op_picker_size_${prodId}`);
        const sizeId = sizeSelectElem ? parseInt(sizeSelectElem.value) : (prod.sizes[0] ? prod.sizes[0].id : null);

        if (!sizeId) {
            alert('Please select a valid size.');
            return;
        }

        if (opPickerContext === 'exchange') {
            const selectElem = document.getElementById('modal_replacement_size_id');
            if (selectElem) {
                selectElem.value = sizeId;
                onExchangeProductChange();
            }
        } else if (opPickerContext === 'add_product') {
            const selectElem = document.getElementById('modal_add_product_size_id');
            if (selectElem) {
                selectElem.value = sizeId;
                onAddProductSelect(selectElem);
            }
        }

        const modalEl = document.getElementById('opVisualProductPickerModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    }
</script>
@endsection
