@extends('layouts.admin')

@section('title', 'Order Details - QUARA WALDROP Admin')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 fs-4 fs-sm-3">
            Order Details: <span class="text-warning">{{ $order->order_number }}</span>
            @if($order->is_cancellation_disabled)
                <span class="badge bg-danger fs-6 ms-2"><i class="fa-solid fa-lock me-1"></i> Cancellation Locked</span>
            @endif
        </h3>
        <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
            <span class="text-muted small">Placed on {{ $order->created_at->format('F d, Y at h:i A') }}</span>
            <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-warning text-dark rounded-pill btn-sm px-3 py-1 fw-bold shadow-sm ms-1">
                <i class="fa-solid fa-print me-1"></i> Print Invoice
            </a>
        </div>
    </div>
    <div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-dark rounded-pill btn-sm px-3 py-2 fw-semibold">&larr; Back to Orders</a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Order Items & Customer Details -->
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-bag-shopping me-2 text-warning"></i> Ordered Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="fw-bold">{{ $item['product_name'] }}</td>
                                    <td><span class="badge bg-dark">{{ $item['size'] }}</span></td>
                                    <td>₹{{ number_format($item['price'], 2) }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($item['subtotal'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                <td class="text-end fw-bold">₹{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                                <tr class="text-danger">
                                    <td colspan="4" class="text-end fw-bold">Discount:</td>
                                    <td class="text-end fw-bold">-₹{{ number_format($order->discount_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="text-end fw-bold fs-5">Grand Total:</td>
                                <td class="text-end fw-bold fs-5 text-warning">₹{{ number_format($order->grand_total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Customer Address -->
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-location-dot me-2 text-warning"></i> Customer & Shipping Address</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted small font-bold text-uppercase">Full Name</span>
                        <div class="fw-bold text-dark fs-6">{{ $order->customer_name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small font-bold text-uppercase">Phone Number</span>
                        <div class="fw-bold text-dark fs-6"><a href="tel:{{ $order->customer_phone }}" class="text-decoration-none text-dark">{{ $order->customer_phone }}</a></div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small font-bold text-uppercase">Email Address</span>
                        <div class="fw-bold text-dark fs-6">{{ $order->customer_email ?: 'N/A' }}</div>
                    </div>
                    <div class="col-12 border-top pt-2">
                        <span class="text-muted small font-bold text-uppercase">Complete Shipping Address</span>
                        <p class="mb-0 fw-semibold text-dark mt-1">
                            {{ $order->house_building }}, {{ $order->street }}, {{ $order->area }},<br>
                            {{ $order->city }}, {{ $order->district }}, {{ $order->state }} - {{ $order->pin_code }}
                        </p>
                    </div>
                    @if($order->notes)
                        <div class="col-12 bg-light p-3 rounded-3 mt-2">
                            <span class="text-muted small font-bold text-uppercase">Delivery Notes:</span>
                            <p class="mb-0 small text-dark">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Cancellation Restriction Lock & Status Actions -->
    <div class="col-lg-4">
        <!-- Order Cancellation Restriction Lock -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 border-start border-4 border-{{ $order->is_cancellation_disabled ? 'danger' : 'secondary' }}">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-user-lock me-2 text-danger"></i> Cancellation Restriction</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.orders.toggle-cancellation-lock', $order->id) }}" method="POST">
                    @csrf
                    <p class="small text-muted mb-3">
                        Enable this setting to prohibit order cancellation for this order. When locked, this order cannot be cancelled by anyone.
                    </p>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="cancellationLockSwitch" onchange="this.form.submit()" {{ $order->is_cancellation_disabled ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-{{ $order->is_cancellation_disabled ? 'danger' : 'dark' }}" for="cancellationLockSwitch">
                            {{ $order->is_cancellation_disabled ? "Can't Cancel Order (LOCKED)" : "Cancellation Allowed (UNLOCKED)" }}
                        </label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-{{ $order->is_cancellation_disabled ? 'outline-success' : 'outline-danger' }} rounded-pill w-100">
                        <i class="fa-solid fa-{{ $order->is_cancellation_disabled ? 'lock-open' : 'lock' }} me-1"></i>
                        {{ $order->is_cancellation_disabled ? 'Unlock Cancellation' : "Lock Order (Can't Cancel Order)" }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Courier Handover & Live Tracking Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 border-start border-4 border-warning">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-truck-fast me-2 text-warning"></i> Courier Handover & Live Tracking</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.orders.update-courier-dispatch', $order->id) }}" method="POST">
                    @csrf
                    <div class="form-check form-switch mb-3 p-3 bg-light rounded-3">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_dispatched_to_courier" role="switch" id="courierHandoverSwitch" {{ $order->is_dispatched_to_courier ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark" for="courierHandoverSwitch">
                            Handed Over to Courier Office
                        </label>
                        <div class="form-text small mt-1">
                            @if($order->is_dispatched_to_courier)
                                <span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Handed over to courier on {{ \Carbon\Carbon::parse($order->dispatched_at)->format('M d, Y h:i A') }}</span>
                            @else
                                <span class="text-muted"><i class="fa-solid fa-box-archive"></i> Status showing to customer: <strong>Packing Process</strong></span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Courier Service Partner</label>
                        <input type="text" name="courier_partner" class="form-control rounded-3" placeholder="e.g. DTDC / Professional Courier / Speed Post" value="{{ old('courier_partner', $order->courier_partner) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Live Tracking Code / AWB Number</label>
                        <input type="text" name="tracking_number" class="form-control rounded-3" placeholder="e.g. D123456789IN" value="{{ old('tracking_number', $order->tracking_number) }}">
                    </div>

                    <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-2">
                        <i class="fa-solid fa-paper-plane me-1"></i> SAVE COURIER DISPATCH
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Status Updater -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-sliders me-2 text-warning"></i> Update Order Status</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Order Status</label>
                        <select name="order_status" class="form-select rounded-3 text-capitalize" {{ $order->order_status === 'cancelled' ? 'disabled' : '' }}>
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
                            <div class="form-text text-danger small mt-1 fw-bold"><i class="fa-solid fa-ban me-1"></i> Order Cancellation is disabled for this order.</div>
                        @elseif($order->order_status === 'cancelled')
                            <div class="form-text text-danger small mt-1"><i class="fa-solid fa-triangle-exclamation"></i> Cancelled orders cannot be re-activated. Stock has been restored.</div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Payment Status</label>
                        <select name="payment_status" class="form-select rounded-3 text-capitalize">
                            <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    @if($order->order_status !== 'cancelled')
                        <button type="submit" class="btn btn-warning rounded-pill fw-bold w-100 py-2">UPDATE ORDER</button>
                    @endif
                </form>
            </div>
        </div>

        <!-- Payment Info Card -->
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-credit-card me-2 text-warning"></i> Payment Details</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Method:</span>
                    <span class="fw-bold text-uppercase">{{ $order->payment_method }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Status:</span>
                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-capitalize">{{ $order->payment_status }}</span>
                </div>

                @if($order->razorpay_payment_id)
                    <div class="border-top pt-2 mt-2">
                        <span class="text-muted small">Razorpay Payment ID:</span>
                        <div class="font-monospace small text-dark fw-bold">{{ $order->razorpay_payment_id }}</div>
                    </div>
                @endif
                @if($order->razorpay_order_id)
                    <div class="pt-2">
                        <span class="text-muted small">Razorpay Order ID:</span>
                        <div class="font-monospace small text-dark fw-bold">{{ $order->razorpay_order_id }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
