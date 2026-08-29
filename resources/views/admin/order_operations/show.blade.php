@extends('layouts.admin')

@section('title', 'Operation #' . $operation->id . ' Details - ' . $siteName . ' Admin')

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
        <h3 class="fw-bold mb-1 op-show-title">Operation #{{ $operation->id }} Details</h3>
        <p class="text-muted small mb-0 op-show-subtitle">Order Operations History for Order #{{ $operation->order->order_number }}</p>
    </div>
    <div class="d-flex gap-2 w-100 w-sm-auto justify-content-end">
        <a href="{{ route('admin.order-operations.edit', $operation->id) }}" class="btn btn-outline-dark rounded-3 px-3 py-1.5 fw-bold shadow-sm op-top-btn" title="Edit Operation">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
        </a>
        <a href="{{ route('admin.order-operations.index') }}" class="btn btn-dark rounded-3 px-3 py-1.5 fw-bold shadow-sm op-top-btn">
            &larr; Back
        </a>
    </div>
</div>

<div class="row g-3 g-md-4">
    <div class="col-lg-8">
        <!-- Operation Status & Header Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4 border-start border-4 border-{{ $operation->status === 'active' ? 'success' : 'secondary' }}">
            <div class="card-body p-3.5 p-md-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                    <div>
                        <span class="badge bg-{{ $operation->status === 'active' ? 'success' : 'secondary' }} text-uppercase px-3 py-1.5 fs-6 mb-2">
                            {{ $operation->status }} OPERATION
                        </span>
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
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-box text-warning me-2"></i> Affected Product & Inventory Action</h6>
            </div>
            <div class="card-body p-3 p-sm-4">
                @php
                    $prod = $operation->product;
                    $imageUrl = $prod ? $prod->primary_image_url : \App\Models\Setting::logoUrl();
                    $orderItem = $operation->orderItem;
                @endphp
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $imageUrl }}" alt="{{ $prod ? $prod->name : 'Product' }}" class="rounded-3 border shadow-sm flex-shrink-0" style="width: 48px; height: 60px; object-fit: cover;">
                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">{{ $orderItem ? $orderItem->product_name : ($prod ? $prod->name : 'Product') }}</h6>
                        <div class="small text-muted mb-1" style="font-size: 0.76rem;">
                            @if($orderItem)
                                Size: <span class="badge bg-dark" style="font-size: 0.68rem;">{{ $orderItem->size }}</span> | Quantity Affected: <strong>{{ $operation->quantity }} pcs</strong>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border" style="font-size: 0.78rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">Returned to Inventory & Website:</span>
                        @if($operation->is_product_restored)
                            <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1 font-monospace">
                                <i class="fa-solid fa-check me-1"></i> YES (Restored)
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1 font-monospace">
                                <i class="fa-solid fa-ban me-1"></i> NO (Not Restored)
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Impact Breakdown -->
        <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
            <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-calculator text-warning me-2"></i> Financial Adjustment Breakdown</h6>
            </div>
            <div class="card-body p-3 p-sm-4">
                <div class="row g-2.5 g-sm-3 mb-3">
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="text-muted small d-block mb-1">Money Refunded to Customer</span>
                            <div class="fw-bold text-dark fs-5">{{ $operation->is_money_refunded ? 'YES' : 'NO' }}</div>
                            @if($operation->is_money_refunded)
                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                    Product: ₹{{ number_format($operation->product_refund_amount, 2) }}<br>
                                    Delivery: ₹{{ number_format($operation->delivery_refund_amount, 2) }}<br>
                                    Other: ₹{{ number_format($operation->other_refund_amount, 2) }}<br>
                                    <strong>Total Refund: ₹{{ number_format($operation->total_refund_amount, 2) }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="text-muted small d-block mb-1">Additional Operational Expenses</span>
                            <div class="fw-bold text-dark fs-5">₹{{ number_format($operation->additional_expense_total, 2) }}</div>
                            @if($operation->expenses->count() > 0)
                                <ul class="list-unstyled mb-0 small text-muted mt-2" style="font-size: 0.75rem;">
                                    @foreach($operation->expenses as $exp)
                                        <li>&bull; {{ $exp->description }}: <strong>₹{{ number_format($exp->amount, 2) }}</strong></li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">No additional expenses logged.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-warning-subtle rounded-3 border border-warning d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark small">Total P&L Financial Adjustment:</span>
                        <div class="small text-muted" style="font-size: 0.72rem;">
                            Status: <strong class="text-uppercase">{{ $operation->status }}</strong> 
                            ({{ $operation->status === 'active' ? 'Included in P&L' : 'Excluded from P&L' }})
                        </div>
                    </div>
                    <span class="fs-4 fs-sm-3 fw-bold text-danger">-₹{{ number_format($operation->total_financial_adjustment, 2) }}</span>
                </div>
            </div>
        </div>

        @if($operation->notes)
            <div class="card border-0 rounded-4 shadow-sm mb-3 mb-md-4">
                <div class="card-header bg-white py-2.5 py-sm-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-note-sticky text-warning me-2"></i> Notes</h6>
                </div>
                <div class="card-body p-3 p-sm-4 text-dark small">
                    {{ $operation->notes }}
                </div>
            </div>
        @endif
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
                    <a href="{{ route('admin.orders.show', $operation->order->id) }}" class="fw-bold text-warning text-decoration-none">
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
                    <span class="text-muted">Grand Total:</span>
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
