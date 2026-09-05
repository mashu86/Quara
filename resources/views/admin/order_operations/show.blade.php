@extends('layouts.admin')

@section('title', 'Adjustment #' . $operation->id . ' Details - ' . $siteName . ' Admin')

@section('content')
<style>
    @media (max-width: 576px) {
        .op-show-title { font-size: 1.15rem !important; }
        .op-show-subtitle { font-size: 0.72rem !important; }
        .op-top-btn { font-size: 0.75rem !important; padding: 0.35rem 0.6rem !important; border-radius: 8px !important; }
        .card-body.p-4 { padding: 1rem 0.85rem !important; }
        .card-body h6 { font-size: 0.84rem !important; }
    }
</style>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 op-show-title">Adjustment #{{ $operation->id }} Details</h3>
        <p class="text-muted small mb-0 op-show-subtitle">Product Level Order Adjustment History for Order #{{ $operation->order->order_number }}</p>
    </div>
    <div class="d-flex gap-2 w-100 w-sm-auto justify-content-end align-items-center">
        <span class="badge bg-secondary text-white rounded-3 px-3 py-2 fw-bold shadow-sm op-top-btn">
            <i class="fa-solid fa-lock text-warning me-1"></i> Adjustment Finalized (Locked)
        </span>
        <a href="{{ route('admin.order-operations.create', $operation->order_id) }}" class="btn btn-dark rounded-3 px-3 py-1.5 fw-bold shadow-sm op-top-btn">
            &larr; Back to Order
        </a>
    </div>
</div>

<div class="row g-3 g-md-4">
    <div class="col-lg-8">
        <!-- Operation Header Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4 border-start border-4 border-{{ $operation->status === 'active' ? 'success' : 'secondary' }}">
            <div class="card-body p-3.5 p-md-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <span class="badge bg-{{ $operation->status === 'active' ? 'success' : 'secondary' }} text-uppercase px-3 py-1.5 fs-6">
                                {{ $operation->status }} OPERATION
                            </span>
                            @if($operation->operation_type === 'order_cancelled')
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2.5 py-1">Order / Item Cancelled</span>
                            @elseif($operation->operation_type === 'product_returned')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2.5 py-1">Product Returned</span>
                            @elseif($operation->operation_type === 'product_exchange')
                                <span class="badge bg-info-subtle text-info-emphasis border border-info px-2.5 py-1">Product Exchange</span>
                            @endif
                        </div>
                        <h4 class="fw-bold text-dark mb-1 fs-5 fs-sm-4">{{ $operation->operation_type_label }}</h4>
                        <div class="text-muted small" style="font-size: 0.76rem;">Recorded on {{ $operation->created_at->format('F d, Y \a\t h:i A') }}</div>
                    </div>
                    <form action="{{ route('admin.order-operations.toggle-status', $operation->id) }}" method="POST" class="w-100 w-sm-auto text-end">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-{{ $operation->status === 'active' ? 'outline-secondary' : 'outline-success' }} rounded-pill px-3 py-1.5 fw-bold w-100 w-sm-auto" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-power-off me-1"></i> Toggle to {{ $operation->status === 'active' ? 'INACTIVE' : 'ACTIVE' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Affected Item & Inventory Status -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-box text-warning me-2"></i> Affected Product & Stock Condition</h6>
            </div>
            <div class="card-body p-3 p-sm-4">
                @php
                    $prod = $operation->product;
                    $imageUrl = $prod ? $prod->primary_image_url : \App\Models\Setting::logoUrl();
                    $orderItem = $operation->orderItem;
                @endphp
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $imageUrl }}" alt="{{ $prod ? $prod->name : 'Product' }}" class="rounded-3 border shadow-sm flex-shrink-0" style="width: 52px; height: 65px; object-fit: cover;">
                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">{{ $orderItem ? $orderItem->product_name : ($prod ? $prod->name : 'Product') }}</h6>
                        <div class="small text-muted mb-1" style="font-size: 0.78rem;">
                            @if($orderItem)
                                Size: <span class="badge bg-dark" style="font-size: 0.68rem;">{{ $orderItem->size }}</span> | Original Qty: <strong>{{ $orderItem->quantity }} pcs</strong> | Unit Price: <strong>₹{{ number_format($orderItem->unit_price, 2) }}</strong>
                            @endif
                        </div>
                        <div class="small text-muted" style="font-size: 0.76rem;">
                            Quantity Affected: <strong class="text-dark">{{ $operation->quantity }} pcs</strong>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border" style="font-size: 0.78rem;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="fw-bold text-dark">Inventory Condition:</span>
                        @if($operation->inventory_condition === 'return_to_stock' || $operation->is_product_restored)
                            <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1 font-monospace fs-7">
                                <i class="fa-solid fa-arrow-rotate-left me-1"></i> RESTOCKED (Returned to Inventory & Website)
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2.5 py-1 font-monospace fs-7">
                                <i class="fa-solid fa-snowflake me-1"></i> DO NOT RESTOCK / FROZEN (Defective or kept by customer)
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Replacement Details (if applicable) -->
        @if($operation->operation_type === 'product_exchange' || $operation->replacementProduct)
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4 border-start border-4 border-info">
                <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-right-left text-info me-2"></i> Product Exchange Details</h6>
                </div>
                <div class="card-body p-3 p-sm-4">
                    @php
                        $replProd = $operation->replacementProduct;
                        $replSize = $operation->replacementProductSize;
                        $replImgUrl = $replProd ? $replProd->primary_image_url : \App\Models\Setting::logoUrl();
                    @endphp
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $replImgUrl }}" alt="{{ $replProd ? $replProd->name : 'Replacement' }}" class="rounded-3 border shadow-sm flex-shrink-0" style="width: 52px; height: 65px; object-fit: cover;">
                        <div>
                            <span class="badge bg-info text-dark mb-1" style="font-size: 0.68rem;">REPLACEMENT ITEM</span>
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">{{ $replProd ? $replProd->name : 'Replacement Item' }}</h6>
                            <div class="small text-muted" style="font-size: 0.78rem;">
                                Size: <span class="badge bg-dark">{{ $replSize ? $replSize->size : 'N/A' }}</span> | Qty: <strong>{{ $operation->replacement_quantity ?? 1 }} pcs</strong>
                            </div>
                        </div>
                    </div>
                    <div class="p-2.5 bg-info-subtle text-info-emphasis rounded-3 border border-info small" style="font-size: 0.78rem;">
                        <strong>Price Difference:</strong>
                        @if($operation->price_difference > 0)
                            <span class="text-danger fw-bold">+₹{{ number_format($operation->price_difference, 2) }} (Customer paid extra)</span>
                        @elseif($operation->price_difference < 0)
                            <span class="text-success fw-bold">-₹{{ number_format(abs($operation->price_difference), 2) }} (Refunded difference to customer)</span>
                        @else
                            <span class="fw-bold">₹0.00 (Even exchange)</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Financial Impact Breakdown & Immutable Refund History -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-calculator text-warning me-2"></i> Financial Refund Records (Immutable)</h6>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#addAdditionalRefundModal" style="font-size: 0.72rem;">
                    <i class="fa-solid fa-plus me-1"></i> Add Refund
                </button>
            </div>
            <div class="card-body p-3 p-sm-4">
                <div class="row g-2.5 g-sm-3 mb-3">
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="text-muted small d-block mb-1">Return Date</span>
                            <div class="fw-bold text-dark fs-6">
                                <i class="fa-solid fa-calendar-day text-warning me-1"></i>
                                {{ $operation->return_date ? $operation->return_date->format('M d, Y') : ($operation->created_at ? $operation->created_at->format('M d, Y') : 'N/A') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="text-muted small d-block mb-1">Total Money Refunded</span>
                            <div class="fw-bold text-danger fs-5">
                                ₹{{ number_format($operation->total_refund_amount, 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refund History List -->
                <h6 class="fw-bold text-dark fs-7 mb-2">Immutable Refund History Log</h6>
                @if($operation->refunds->count() > 0)
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.76rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Refund Date</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Ref / Transaction ID</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($operation->refunds as $ref)
                                    <tr>
                                        <td>#{{ $ref->id }}</td>
                                        <td class="fw-bold text-dark">{{ $ref->refund_date ? $ref->refund_date->format('M d, Y') : $ref->created_at->format('M d, Y') }}</td>
                                        <td class="fw-bold text-danger">₹{{ number_format($ref->refund_amount, 2) }}</td>
                                        <td class="text-uppercase">{{ str_replace('_', ' ', $ref->payment_method ?? 'manual') }}</td>
                                        <td class="font-monospace text-muted">{{ $ref->transaction_reference ?: 'N/A' }}</td>
                                        <td>{{ $ref->created_by ?: 'Admin' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-light border text-muted small py-2 px-3 mb-3">
                        <i class="fa-solid fa-info-circle me-1"></i> No monetary refund recorded for this operation yet.
                    </div>
                @endif

                <div class="p-3 bg-warning-subtle rounded-3 border border-warning d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark small">Net Realized Revenue Reduction:</span>
                        <div class="small text-muted" style="font-size: 0.72rem;">
                            Deducted from sales revenue on specific refund date(s)
                        </div>
                    </div>
                    <span class="fs-4 fs-sm-3 fw-bold text-danger">-₹{{ number_format($operation->total_refund_amount, 2) }}</span>
                </div>
            </div>
        </div>

        @if($operation->notes)
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-note-sticky text-warning me-2"></i> Adjustment Notes</h6>
                </div>
                <div class="card-body p-3 p-sm-4 text-dark small">
                    {{ $operation->notes }}
                </div>
            </div>
        @endif
    </div>

    <!-- Modal for Adding Additional Refund -->
    <div class="modal fade" id="addAdditionalRefundModal" tabindex="-1" aria-labelledby="addAdditionalRefundModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                    <h5 class="modal-title font-serif fw-bold fs-6" id="addAdditionalRefundModalLabel">
                        <i class="fa-solid fa-hand-holding-dollar text-danger me-2"></i> Issue Money Refund (#{{ $operation->id }})
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.order-operations.add-refund', $operation->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Refund Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">₹</span>
                                <input type="number" step="0.01" name="refund_amount" class="form-control rounded-end-3" placeholder="0.00" min="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Refund Date <span class="text-danger">*</span></label>
                            <input type="date" name="refund_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                            <div class="form-text small text-muted">Date on which the actual payout occurred. Financial reporting will attribute net sales reduction to this date.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Payment Method</label>
                            <select name="payment_method" class="form-select rounded-3">
                                <option value="bank_transfer" selected>Bank Transfer / UPI</option>
                                <option value="cash">Cash Payout</option>
                                <option value="razorpay">Razorpay Gateway Refund</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Transaction / Reference ID (Optional)</label>
                            <input type="text" name="transaction_reference" class="form-control rounded-3" placeholder="e.g. UTR123456789">
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold text-dark">Notes / Reason (Optional)</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Customer bank refund processed..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                            <i class="fa-solid fa-check me-1"></i> Save Refund
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Order Info Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-receipt text-warning me-2"></i> Associated Order Details</h6>
            </div>
            <div class="card-body p-3 style-small" style="font-size: 0.8rem;">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Order Number:</span>
                    <a href="{{ route('admin.order-operations.create', $operation->order->id) }}" class="fw-bold text-warning text-decoration-none">
                        {{ $operation->order->order_number }}
                    </a>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Customer Name:</span>
                    <span class="fw-bold text-dark">{{ $operation->order->customer_name }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Phone Number:</span>
                    <span class="fw-bold text-dark">{{ $operation->order->customer_phone }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Active Subtotal:</span>
                    <span class="fw-bold text-dark">₹{{ number_format($operation->order->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Shipping Fee:</span>
                    <span class="fw-bold text-dark">₹{{ number_format($operation->order->shipping, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Net Grand Total:</span>
                    <span class="fw-bold text-dark">₹{{ number_format($operation->order->grand_total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Order Status:</span>
                    <span class="badge bg-{{ $operation->order->order_status === 'delivered' ? 'success' : 'warning' }} text-capitalize">
                        {{ $operation->order->order_status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Audit Details Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-user-shield text-warning me-2"></i> Audit Trail</h6>
            </div>
            <div class="card-body p-3 small text-muted" style="font-size: 0.78rem;">
                <div class="mb-2"><strong>Created By:</strong> {{ $operation->created_by ?: 'Admin' }}</div>
                <div class="mb-2"><strong>Created At:</strong> {{ $operation->created_at->format('M d, Y h:i A') }}</div>
                @if($operation->updated_by)
                    <div class="mb-2"><strong>Last Updated By:</strong> {{ $operation->updated_by }}</div>
                    <div><strong>Last Updated At:</strong> {{ $operation->updated_at->format('M d, Y h:i A') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
