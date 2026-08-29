@extends('layouts.admin')

@section('title', 'Order Details - ' . $siteName . ' Admin')

@section('content')
<div class="mb-3 mb-md-4">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
        <h4 class="fw-bold mb-0 text-truncate" style="font-size: 0.95rem;">
            Order Details: <span class="text-warning">{{ $order->order_number }}</span>
            @if($order->is_cancellation_disabled)
                <span class="badge bg-danger ms-1" style="font-size: 0.7rem;"><i class="fa-solid fa-lock me-1"></i> Locked</span>
            @endif
        </h4>
    </div>
    <div class="d-flex justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2" style="gap: 8px;">
            <button type="button" onclick="openWhatsappModal({{ json_encode($order) }})" class="btn btn-sm btn-success text-white rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="WhatsApp Follow-up">
                <i class="fa-brands fa-whatsapp fs-6"></i>
            </button>
            <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Print Invoice">
                <i class="fa-solid fa-print fs-6"></i>
            </a>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-2.5 px-sm-3 py-1.5 fw-semibold text-nowrap" style="font-size: 0.78rem;">&larr; <span class="d-none d-sm-inline">Back to </span>Orders</a>
    </div>
    <div class="text-muted mt-1.5" style="font-size: 0.75rem;">Placed on {{ $order->created_at->format('F d, Y at h:i A') }}</div>
</div>

<div class="row g-3 g-md-4">
    <!-- Left Column: Order Items & Customer Details -->
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;"><i class="fa-solid fa-bag-shopping me-2 text-warning"></i> Ordered Items</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="font-size: 0.78rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Photo</th>
                                <th>Size</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php
                                    $itemProduct = $item->product;
                                    $itemImageUrl = $itemProduct ? $itemProduct->primary_image_url : \App\Models\Setting::logoUrl();
                                    $itemName = $item['product_name'] ?? ($itemProduct ? $itemProduct->name : 'Product');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark mb-0" style="font-size: 0.82rem;">{{ $itemName }}</div>
                                        @if($itemProduct)
                                            <a href="{{ route('admin.products.edit', $itemProduct->id) }}" target="_blank" class="text-muted small text-decoration-none" style="font-size: 0.7rem;">
                                                Master Link <i class="fa-solid fa-arrow-up-right-from-square ms-0.5" style="font-size: 0.65rem;"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <img src="{{ $itemImageUrl }}" alt="{{ $itemName }}" 
                                             class="rounded-3 border shadow-sm item-thumb-img" 
                                             style="width: 48px; height: 60px; object-fit: cover; cursor: pointer; transition: transform 0.2s ease;"
                                             onclick="openImagePreviewModal('{{ addslashes($itemImageUrl) }}', '{{ addslashes($itemName) }}')"
                                             title="Click to view large image">
                                    </td>
                                    <td><span class="badge bg-dark" style="font-size: 0.7rem;">{{ $item['size'] }}</span></td>
                                    <td>₹{{ number_format($item['price'], 2) }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($item['subtotal'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                <td class="text-end fw-bold">₹{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                                <tr class="text-danger">
                                    <td colspan="5" class="text-end fw-bold">Discount:</td>
                                    <td class="text-end fw-bold">-₹{{ number_format($order->discount_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="5" class="text-end fw-bold" style="font-size: 0.85rem;">Grand Total:</td>
                                <td class="text-end fw-bold text-warning" style="font-size: 0.88rem;">₹{{ number_format($order->grand_total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Customer Address -->
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;"><i class="fa-solid fa-location-dot me-2 text-warning"></i> Customer & Shipping Address</h6>
            </div>
            <div class="card-body p-3 p-sm-4" style="font-size: 0.8rem;">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted font-bold text-uppercase d-block" style="font-size: 0.7rem;">Full Name</span>
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $order->customer_name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted font-bold text-uppercase d-block" style="font-size: 0.7rem;">Phone Number</span>
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;"><a href="tel:{{ $order->customer_phone }}" class="text-decoration-none text-dark">{{ $order->customer_phone }}</a></div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted font-bold text-uppercase d-block" style="font-size: 0.7rem;">Email Address</span>
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $order->customer_email ?: 'N/A' }}</div>
                    </div>
                    <div class="col-12 border-top pt-2">
                        <span class="text-muted font-bold text-uppercase d-block" style="font-size: 0.7rem;">Complete Shipping Address</span>
                        <p class="mb-0 fw-semibold text-dark mt-1" style="font-size: 0.8rem;">
                            {{ $order->house_building }}, {{ $order->street }}, {{ $order->area }},<br>
                            {{ $order->city }}, {{ $order->district }}, {{ $order->state }} - {{ $order->pin_code }}
                        </p>
                    </div>
                    @if($order->notes)
                        <div class="col-12 bg-light p-3 rounded-3 mt-2">
                            <span class="text-muted font-bold text-uppercase d-block" style="font-size: 0.7rem;">Delivery Notes:</span>
                            <p class="mb-0 text-dark" style="font-size: 0.78rem;">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Cancellation Restriction Lock & Status Actions -->
    <div class="col-lg-4">
        <!-- Order Cancellation Restriction Lock -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4 border-start border-4 border-{{ $order->is_cancellation_disabled ? 'danger' : 'secondary' }}">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;"><i class="fa-solid fa-user-lock me-2 text-danger"></i> Cancellation Restriction</h6>
            </div>
            <div class="card-body p-3 p-sm-4">
                <form action="{{ route('admin.orders.toggle-cancellation-lock', $order->id) }}" method="POST">
                    @csrf
                    <p class="text-muted mb-3" style="font-size: 0.76rem;">
                        Enable this setting to prohibit order cancellation for this order. When locked, this order cannot be cancelled by anyone.
                    </p>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="cancellationLockSwitch" onchange="this.form.submit()" {{ $order->is_cancellation_disabled ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-{{ $order->is_cancellation_disabled ? 'danger' : 'dark' }}" for="cancellationLockSwitch" style="font-size: 0.78rem;">
                            {{ $order->is_cancellation_disabled ? "Can't Cancel Order (LOCKED)" : "Cancellation Allowed (UNLOCKED)" }}
                        </label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-{{ $order->is_cancellation_disabled ? 'outline-success' : 'outline-danger' }} rounded-pill w-100 py-1.5" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-{{ $order->is_cancellation_disabled ? 'lock-open' : 'lock' }} me-1"></i>
                        {{ $order->is_cancellation_disabled ? 'Unlock Cancellation' : "Lock Order (Can't Cancel Order)" }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Courier Handover & Live Tracking Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4 border-start border-4 border-warning">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;"><i class="fa-solid fa-truck-fast me-2 text-warning"></i> Courier Handover & Live Tracking</h6>
            </div>
            <div class="card-body p-3 p-sm-4">
                <form action="{{ route('admin.orders.update-courier-dispatch', $order->id) }}" method="POST">
                    @csrf
                    <div class="form-check form-switch mb-3 p-3 bg-light rounded-3">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_dispatched_to_courier" role="switch" id="courierHandoverSwitch" {{ $order->is_dispatched_to_courier ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark" for="courierHandoverSwitch" style="font-size: 0.78rem;">
                            Handed Over to Courier Office
                        </label>
                        <div class="form-text mt-1" style="font-size: 0.74rem;">
                            @if($order->is_dispatched_to_courier)
                                <span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Handed over to courier on {{ \Carbon\Carbon::parse($order->dispatched_at)->format('M d, Y h:i A') }}</span>
                            @else
                                <span class="text-muted"><i class="fa-solid fa-box-archive"></i> Status showing to customer: <strong>Packing Process</strong></span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 0.78rem;">Courier Service Partner</label>
                        <input type="text" name="courier_partner" class="form-control rounded-3 py-1.5" style="font-size: 0.8rem;" placeholder="e.g. DTDC / Professional Courier / Speed Post" value="{{ old('courier_partner', $order->courier_partner) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 0.78rem;">Live Tracking Code / AWB Number</label>
                        <input type="text" name="tracking_number" class="form-control rounded-3 py-1.5" style="font-size: 0.8rem;" placeholder="e.g. D123456789IN" value="{{ old('tracking_number', $order->tracking_number) }}">
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-2 shadow-sm text-dark" style="font-size: 0.78rem; background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-paper-plane me-1"></i> SAVE COURIER DISPATCH
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Status Updater -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;"><i class="fa-solid fa-sliders me-2 text-warning"></i> Update Order Status</h6>
            </div>
            <div class="card-body p-3 p-sm-4">
                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 0.78rem;">Order Status</label>
                        <select name="order_status" class="form-select rounded-3 text-capitalize py-1.5" style="font-size: 0.8rem;" {{ $order->order_status === 'cancelled' ? 'disabled' : '' }}>
                            <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $order->order_status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="packed" {{ $order->order_status === 'packed' ? 'selected' : '' }}>Packed</option>
                            <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }} {{ $order->is_cancellation_disabled ? 'disabled' : '' }}>
                                {{ $order->is_cancellation_disabled ? "Cancelled (RESTRICTED - Order Locked)" : "Cancelled (Restores Stock)" }}
                            </option>
                        </select>
                        @if($order->is_cancellation_disabled)
                            <div class="form-text text-danger mt-1 fw-bold" style="font-size: 0.72rem;"><i class="fa-solid fa-ban me-1"></i> Order Cancellation is disabled for this order.</div>
                        @elseif($order->order_status === 'cancelled')
                            <div class="form-text text-danger mt-1" style="font-size: 0.72rem;"><i class="fa-solid fa-triangle-exclamation"></i> Cancelled orders cannot be re-activated. Stock has been restored.</div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" style="font-size: 0.78rem;">Payment Status</label>
                        <select name="payment_status" class="form-select rounded-3 text-capitalize py-1.5" style="font-size: 0.8rem;">
                            <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    @if($order->order_status !== 'cancelled')
                        <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-2 shadow-sm text-dark" style="font-size: 0.78rem; background-color: var(--qw-gold); border-color: var(--qw-gold);">UPDATE ORDER</button>
                    @endif
                </form>
            </div>
        </div>

        <!-- Payment Info Card -->
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;"><i class="fa-solid fa-credit-card me-2 text-warning"></i> Payment Details</h6>
            </div>
            <div class="card-body p-3 p-sm-4" style="font-size: 0.78rem;">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Method:</span>
                    <span class="fw-bold text-uppercase">{{ $order->payment_method }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Status:</span>
                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-capitalize" style="font-size: 0.7rem;">{{ $order->payment_status }}</span>
                </div>

                @if($order->razorpay_payment_id)
                    <div class="border-top pt-2 mt-2">
                        <span class="text-muted font-monospace" style="font-size: 0.72rem;">Razorpay Payment ID:</span>
                        <div class="font-monospace text-dark fw-bold" style="font-size: 0.75rem;">{{ $order->razorpay_payment_id }}</div>
                    </div>
                @endif
                @if($order->razorpay_order_id)
                    <div class="pt-2">
                        <span class="text-muted font-monospace" style="font-size: 0.72rem;">Razorpay Order ID:</span>
                        <div class="font-monospace text-dark fw-bold" style="font-size: 0.75rem;">{{ $order->razorpay_order_id }}</div>
                    </div>
                @endif

                @if($order->payment_method === 'online' || $order->razorpay_total_charge > 0)
                    @php
                        if ($order->razorpay_total_charge <= 0) {
                            $order->calculateRazorpayCharge();
                        }
                    @endphp
                    <div class="border-top pt-3 mt-3 bg-light p-2.5 rounded-3">
                        <span class="fw-bold text-dark d-block mb-2" style="font-size: 0.76rem;">
                            <i class="fa-solid fa-calculator text-warning me-1"></i> Razorpay Fee Breakdown
                        </span>
                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.74rem;">
                            <span class="text-muted">Paid Amount:</span>
                            <span class="fw-bold">₹{{ number_format($order->grand_total, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.74rem;">
                            <span class="text-muted">Gateway Fee ({{ number_format($order->razorpay_fee_percent, 2) }}%):</span>
                            <span>₹{{ number_format($order->razorpay_base_fee, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.74rem;">
                            <span class="text-muted">GST on Fee ({{ number_format($order->razorpay_gst_percent, 2) }}%):</span>
                            <span>₹{{ number_format($order->razorpay_gst_fee, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-1 text-danger fw-bold mb-1" style="font-size: 0.76rem;">
                            <span>Total Gateway Expense:</span>
                            <span>-₹{{ number_format($order->razorpay_total_charge, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-success fw-bold" style="font-size: 0.78rem;">
                            <span>Net Received Amount:</span>
                            <span>₹{{ number_format($order->razorpay_net_amount, 2) }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('admin.orders.partials.whatsapp_modal')

<!-- Large Product Image Preview Modal -->
<div class="modal fade" id="productImagePreviewModal" tabindex="-1" aria-labelledby="productImagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="modal-title font-serif fw-bold text-truncate me-2 mb-0" id="productImagePreviewModalTitle">
                    <i class="fa-solid fa-shirt text-warning me-2"></i> Product Photo
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-light">
                <img id="productImagePreviewModalImg" src="" alt="Product Large View" class="img-fluid rounded-3 shadow-sm" style="max-height: 75vh; width: 100%; object-fit: contain;">
            </div>
            <div class="modal-footer bg-white py-2 px-4 border-top">
                <button type="button" class="btn btn-dark rounded-pill px-4 btn-sm fw-bold ms-auto" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .item-thumb-img:hover {
        transform: scale(1.08);
        border-color: var(--qw-gold) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
</style>

<script>
function openImagePreviewModal(imageUrl, title) {
    var imgEl = document.getElementById('productImagePreviewModalImg');
    var titleEl = document.getElementById('productImagePreviewModalTitle');
    if (imgEl && titleEl) {
        imgEl.src = imageUrl;
        titleEl.innerHTML = '<i class="fa-solid fa-shirt text-warning me-2"></i> ' + title;
        var modal = new bootstrap.Modal(document.getElementById('productImagePreviewModal'));
        modal.show();
    }
}
</script>
@endsection
