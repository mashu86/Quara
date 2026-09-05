@extends('layouts.admin')

@section('title', 'Edit Order #' . $order->order_number . ' - ' . $siteName . ' Admin')

@section('content')
<div class="mb-3 mb-md-4">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
        <h4 class="fw-bold mb-0 text-truncate fs-5 fs-sm-4">
            <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Order: <span class="text-warning">{{ $order->order_number }}</span>
        </h4>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-dark rounded-pill btn-sm px-2.5 px-sm-3 py-1.5 fw-semibold text-nowrap" style="font-size: 0.78rem;">
            &larr; <span class="d-none d-sm-inline">Back to </span>Details
        </a>
    </div>
    <div class="text-muted" style="font-size: 0.75rem;">Placed on {{ $order->created_at->format('F d, Y at h:i A') }}</div>
</div>

@if($errors->any())
    <div class="alert alert-danger rounded-4 mb-4">
        <ul class="mb-0 small ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3 g-md-4">
        <!-- Left Column: Customer & Delivery Info -->
        <div class="col-12 col-lg-8">
            <!-- Customer Information Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-user me-2 text-warning"></i> Customer Information
                    </h6>
                </div>
                <div class="card-body p-3 p-sm-4">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold small text-dark">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control rounded-3" value="{{ old('customer_name', $order->customer_name) }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold small text-dark">Phone Number *</label>
                            <input type="text" name="customer_phone" class="form-control rounded-3" value="{{ old('customer_phone', $order->customer_phone) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Email Address (Optional)</label>
                            <input type="email" name="customer_email" class="form-control rounded-3" value="{{ old('customer_email', $order->customer_email) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping & Delivery Address Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-location-dot me-2 text-warning"></i> Delivery & Shipping Address
                    </h6>
                </div>
                <div class="card-body p-3 p-sm-4">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold small text-dark">House / Building Name / No *</label>
                            <input type="text" name="house_building" class="form-control rounded-3" value="{{ old('house_building', $order->house_building) }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold small text-dark">Street / Road *</label>
                            <input type="text" name="street" class="form-control rounded-3" value="{{ old('street', $order->street) }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold small text-dark">Area / Landmark *</label>
                            <input type="text" name="area" class="form-control rounded-3" value="{{ old('area', $order->area) }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold small text-dark">City / Town *</label>
                            <input type="text" name="city" class="form-control rounded-3" value="{{ old('city', $order->city) }}" required>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label fw-bold small text-dark">District *</label>
                            <input type="text" name="district" class="form-control rounded-3" value="{{ old('district', $order->district) }}" required>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label fw-bold small text-dark">State *</label>
                            <input type="text" name="state" class="form-control rounded-3" value="{{ old('state', $order->state) }}" required>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label fw-bold small text-dark">Pin Code *</label>
                            <input type="text" name="pin_code" class="form-control rounded-3" value="{{ old('pin_code', $order->pin_code) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ordered Items Summary Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-boxes-packing me-2 text-warning"></i> Ordered Items (Read-Only Summary)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="font-size: 0.78rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    @php
                                        $itemOp = $order->operations ? $order->operations->where('status', 'active')->where('order_item_id', $item->id)->first() : null;
                                        $isReturned = ($item->item_status === 'returned' || ($itemOp && in_array($itemOp->operation_type, ['product_returned', 'customer_return', 'wrong_product_sent', 'product_damaged', 'product_lost'])));
                                        $itemUnitPrice = (float) ($item->unit_price ?? $item->final_unit_price ?? $item->price ?? 0);
                                    @endphp
                                    <tr class="{{ $isReturned ? 'table-danger border-start border-4 border-danger' : '' }}">
                                        <td>
                                            <span class="fw-bold text-dark">{{ $item->product_name }}</span>
                                            @if($isReturned || $itemOp)
                                                <div class="mt-1">
                                                    @if($isReturned)
                                                        <span class="badge bg-danger text-white me-1" style="font-size: 0.65rem;">
                                                            <i class="fa-solid fa-rotate-left me-0.5"></i> RETURNED
                                                        </span>
                                                    @endif
                                                    @if($itemOp && $itemOp->is_money_refunded && $itemOp->total_refund_amount > 0)
                                                        <span class="badge bg-danger text-white me-1" style="font-size: 0.65rem;">
                                                            <i class="fa-solid fa-hand-holding-dollar me-0.5"></i> Refund: ₹{{ number_format($itemOp->total_refund_amount, 2) }}
                                                        </span>
                                                    @elseif($itemOp && !$itemOp->is_money_refunded)
                                                        <span class="badge bg-secondary text-white me-1" style="font-size: 0.65rem;">
                                                            <i class="fa-solid fa-ban me-0.5"></i> No Refund
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-dark">{{ $item->size }}</span></td>
                                        <td>₹{{ number_format($itemUnitPrice, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="text-end pe-3 fw-bold">₹{{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Grand Total (Original):</td>
                                    <td class="text-end pe-3 fw-bold text-dark">₹{{ number_format($order->grand_total, 2) }}</td>
                                </tr>
                                @php
                                    $activeOps = $order->operations ? $order->operations->where('status', 'active') : collect();
                                    $totRefund = (float) $activeOps->sum('total_refund_amount');
                                    $netRealized = (float) $order->grand_total - $totRefund;
                                @endphp
                                @if($totRefund > 0)
                                    <tr class="text-danger">
                                        <td colspan="4" class="text-end fw-bold">Refund Deducted:</td>
                                        <td class="text-end pe-3 fw-bold">-₹{{ number_format($totRefund, 2) }}</td>
                                    </tr>
                                    <tr class="table-success border-top border-2">
                                        <td colspan="4" class="text-end fw-bold text-success"><i class="fa-solid fa-wallet me-1"></i> Net Realized Total:</td>
                                        <td class="text-end pe-3 fw-bold text-success fs-6">₹{{ number_format($netRealized, 2) }}</td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Status & Courier Options -->
        <div class="col-12 col-lg-4">
            <!-- Order & Payment Status Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-list-check me-2 text-warning"></i> Order Status & Payment
                    </h6>
                </div>
                <div class="card-body p-3 p-sm-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Order Status *</label>
                        <select name="order_status" class="form-select rounded-3">
                            <option value="pending" {{ old('order_status', $order->order_status) === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                            <option value="confirmed" {{ old('order_status', $order->order_status) === 'confirmed' ? 'selected' : '' }}>✅ Confirmed</option>
                            <option value="processing" {{ old('order_status', $order->order_status) === 'processing' ? 'selected' : '' }}>⚙️ Processing</option>
                            <option value="packed" {{ old('order_status', $order->order_status) === 'packed' ? 'selected' : '' }}>📦 Packed</option>
                            <option value="shipped" {{ old('order_status', $order->order_status) === 'shipped' ? 'selected' : '' }}>🚚 Shipped</option>
                            <option value="delivered" {{ old('order_status', $order->order_status) === 'delivered' ? 'selected' : '' }}>🏠 Delivered</option>
                            <option value="cancelled" {{ old('order_status', $order->order_status) === 'cancelled' ? 'selected' : '' }}>🚫 Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Payment Status *</label>
                        <select name="payment_status" class="form-select rounded-3">
                            <option value="pending" {{ old('payment_status', $order->payment_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ old('payment_status', $order->payment_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ old('payment_status', $order->payment_status) === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ old('payment_status', $order->payment_status) === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Payment Method *</label>
                        <select name="payment_method" class="form-select rounded-3">
                            <option value="cod" {{ old('payment_method', $order->payment_method) === 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                            <option value="online" {{ old('payment_method', $order->payment_method) === 'online' ? 'selected' : '' }}>Razorpay Online</option>
                            <option value="offline_sale" {{ old('payment_method', $order->payment_method) === 'offline_sale' ? 'selected' : '' }}>Offline Sale</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Shipping / Delivery Charge (₹)</label>
                        <input type="number" step="0.01" min="0" name="shipping" class="form-control rounded-3" value="{{ old('shipping', number_format($order->shipping, 2, '.', '')) }}">
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;"><i class="fa-solid fa-truck text-warning me-1"></i> Adjusting shipping charge updates total sales & profit accordingly.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Sale Date (DD-MM-YYYY)</label>
                        <input type="date" name="sale_date" class="form-control rounded-3" value="{{ old('sale_date', $order->sale_date ? $order->sale_date->format('Y-m-d') : $order->created_at->format('Y-m-d')) }}">
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;"><i class="fa-solid fa-circle-info text-warning me-1"></i> Date when the sale actually took place.</small>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_cancellation_disabled" value="1" id="cancellationLockSwitch" {{ old('is_cancellation_disabled', $order->is_cancellation_disabled) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold text-dark" for="cancellationLockSwitch">
                            Lock Order Cancellation (Prevent Cancellation)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Courier Shipping Information Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-truck-fast me-2 text-warning"></i> Courier Dispatch Info
                    </h6>
                </div>
                <div class="card-body p-3 p-sm-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_dispatched_to_courier" value="1" id="courierDispatchSwitch" {{ old('is_dispatched_to_courier', $order->is_dispatched_to_courier) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold text-dark" for="courierDispatchSwitch">
                            Handed Over to Courier Partner
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Courier Partner</label>
                        <input type="text" name="courier_partner" class="form-control rounded-3" placeholder="e.g. DTDC / Speed Post" value="{{ old('courier_partner', $order->courier_partner) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Tracking / AWB Number</label>
                        <input type="text" name="tracking_number" class="form-control rounded-3" placeholder="e.g. D123456789" value="{{ old('tracking_number', $order->tracking_number) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Order Notes / Admin Remarks</label>
                        <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Additional notes...">{{ old('notes', $order->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-3 p-sm-4">
                    <button type="submit" class="btn btn-warning rounded-pill w-100 py-2.5 fw-bold text-dark shadow-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-check me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary rounded-pill w-100 mt-2 py-2 fw-semibold">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
