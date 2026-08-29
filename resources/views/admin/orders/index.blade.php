@extends('layouts.admin')

@section('title', 'Order Management - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 fs-4 fs-sm-3">Order Management</h3>
        <p class="text-muted small mb-0">Track, update, filter and fulfill customer orders</p>
    </div>
</div>

@php
    $activeOrderFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('status') ? 1 : 0)
        + (request()->filled('payment_method') ? 1 : 0);
@endphp

<!-- Mobile / Tablet Filter Button Bar (d-lg-none) -->
<div class="d-lg-none mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark rounded-pill px-3 py-2 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#orderFilterModal">
            <i class="fa-solid fa-sliders text-warning"></i>
            <span class="fw-semibold">Filter Orders</span>
            @if($activeOrderFilterCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $activeOrderFilterCount }}</span>
            @endif
        </button>
        @if($activeOrderFilterCount > 0)
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
</div>

<!-- Desktop Search & Filters (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body p-3 p-sm-4">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3">
            <div class="col-12 col-md-4">
                <input type="text" name="search" class="form-control rounded-3" placeholder="Search Order #, Name, Phone..." value="{{ request()->search }}">
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <select name="status" class="form-select rounded-3">
                    <option value="">All Order Statuses</option>
                    <option value="pending" {{ request()->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request()->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ request()->status === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="packed" {{ request()->status === 'packed' ? 'selected' : '' }}>Packed</option>
                    <option value="shipped" {{ request()->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request()->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request()->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <select name="payment_method" class="form-select rounded-3">
                    <option value="">All Payment Methods</option>
                    <option value="cod" {{ request()->payment_method === 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                    <option value="online" {{ request()->payment_method === 'online' ? 'selected' : '' }}>Razorpay Online</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-dark w-100 rounded-pill fw-semibold py-2 shadow-sm">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Order Mobile Filter Modal (d-lg-none) -->
<div class="modal fade d-lg-none" id="orderFilterModal" tabindex="-1" aria-labelledby="orderFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title font-serif fw-bold" id="orderFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Orders
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.orders.index') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Search Order # / Customer / Phone</label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="Search Order #, Name, Phone..." value="{{ request()->search }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Order Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="">All Order Statuses</option>
                            <option value="pending" {{ request()->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request()->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ request()->status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="packed" {{ request()->status === 'packed' ? 'selected' : '' }}>Packed</option>
                            <option value="shipped" {{ request()->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ request()->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request()->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Payment Method</label>
                        <select name="payment_method" class="form-select rounded-3">
                            <option value="">All Payment Methods</option>
                            <option value="cod" {{ request()->payment_method === 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                            <option value="online" {{ request()->payment_method === 'online' ? 'selected' : '' }}>Razorpay Online</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-check me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .orders-table th, .orders-table td { font-size: 0.73rem !important; padding: 0.5rem 0.35rem !important; }
        .orders-table .btn:not(.rounded-circle) { font-size: 0.68rem !important; padding: 0.25rem 0.45rem !important; }
        .orders-table .badge { font-size: 0.63rem !important; padding: 0.2em 0.45em !important; }
    }
</style>

<!-- Table -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table orders-table align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th>Order Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-warning text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
                                <div class="small text-muted"><i class="fa-solid fa-phone me-1"></i> {{ $order->customer_phone }}</div>
                            </td>
                            <td class="small">{{ $order->created_at->format('M d, Y') }}<br><span class="text-muted">{{ $order->created_at->format('h:i A') }}</span></td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase me-1">{{ $order->payment_method }}</span>
                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-capitalize">
                                    {{ $order->payment_status }}
                                </span>
                            </td>
                            <td class="fw-bold">₹{{ number_format($order->grand_total, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning') }} text-capitalize px-3 py-2">
                                    {{ $order->order_status }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex flex-nowrap justify-content-end align-items-center gap-2" style="gap: 8px;">
                                    <button type="button" onclick="openWhatsappModal({{ json_encode($order) }})" class="btn btn-sm btn-success text-white rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="WhatsApp Follow-up">
                                        <i class="fa-brands fa-whatsapp fs-6"></i>
                                    </button>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="View Details">
                                        <i class="fa-solid fa-eye fs-6"></i>
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Print Invoice">
                                        <i class="fa-solid fa-print fs-6"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No orders found matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $orders->links() }}
    </div>
</div>

@include('admin.orders.partials.whatsapp_modal')
@endsection
