@extends('layouts.admin')

@section('title', 'Order Management & Sales Master - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 fs-4 fs-sm-3">
            <i class="fa-solid fa-cart-shopping text-warning me-2"></i> Order Management & Sales Master
        </h3>
        <p class="text-muted small mb-0">Live sales reporting, date-wise analytics & order fulfillment control</p>
    </div>
</div>

@php
    $activeOrderFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('status') ? 1 : 0)
        + (request()->filled('payment_method') ? 1 : 0)
        + (request()->filled('start_date') ? 1 : 0)
        + (request()->filled('end_date') ? 1 : 0);
@endphp

<!-- SALES ANALYTICS KPI SUMMARY CARDS -->
<div class="row g-3 mb-4">
    <!-- Today Sales Card -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #111111 0%, #2b2b2b 100%);">
            <div class="card-body p-3.5 p-sm-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-warning text-uppercase font-mono fw-bold small" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-calendar-day me-1"></i> Today Sale
                    </span>
                    <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small fw-bold">{{ $todayOrdersCount }} Orders</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="fw-bold mb-0 text-warning display-6 fs-3 fs-md-2">₹{{ number_format($todaySalesAmount, 2) }}</h3>
                        <div class="small text-light opacity-90 mt-1" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-box-open text-warning me-1"></i> <strong>{{ $todayProductsCount }}</strong> Products Sold Today
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-20 rounded-3 text-warning flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-chart-line fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly / Selected Period Sales Card -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm bg-white border-start border-4 border-warning h-100">
            <div class="card-body p-3.5 p-sm-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted text-uppercase fw-bold small text-truncate" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-calendar-week text-warning me-1"></i> {{ $periodLabel }}
                    </span>
                    <span class="badge bg-dark text-white rounded-pill px-2.5 py-1 small fw-bold">{{ $periodOrdersCount }} Orders</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="fw-bold mb-0 text-dark display-6 fs-3 fs-md-2">₹{{ number_format($periodSalesAmount, 2) }}</h3>
                        <div class="small text-muted mt-1" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-boxes-packing text-warning me-1"></i> <strong>{{ $periodProductsCount }}</strong> Products Sold in Period
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-light border rounded-3 text-dark flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-bag-shopping fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range & Quick Filter Card -->
    <div class="col-12 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm bg-light h-100">
            <div class="card-body p-3 p-sm-3.5 d-flex flex-column justify-content-center">
                <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2">
                    <div class="col-6">
                        <label class="form-label text-muted small fw-bold mb-1" style="font-size: 0.72rem;">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm rounded-3" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-bold mb-1" style="font-size: 0.72rem;">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm rounded-3" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-12 d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-dark btn-sm rounded-pill flex-grow-1 fw-bold" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-filter me-1 text-warning"></i> Filter Date Range
                        </button>
                        @if(request()->filled('start_date') || request()->filled('end_date'))
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" style="font-size: 0.78rem;" title="Reset Date Filter">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Search</label>
                <input type="text" name="search" class="form-control rounded-3" placeholder="Order #, Name, Phone..." value="{{ request()->search }}">
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Order Status</label>
                <select name="status" class="form-select rounded-3">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request()->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request()->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ request()->status === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="packed" {{ request()->status === 'packed' ? 'selected' : '' }}>Packed</option>
                    <option value="shipped" {{ request()->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request()->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request()->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Payment Method</label>
                <select name="payment_method" class="form-select rounded-3">
                    <option value="">All Methods</option>
                    <option value="cod" {{ request()->payment_method === 'cod' ? 'selected' : '' }}>COD (Cash on Delivery)</option>
                    <option value="online" {{ request()->payment_method === 'online' ? 'selected' : '' }}>Razorpay Online</option>
                    <option value="offline_sale" {{ request()->payment_method === 'offline_sale' ? 'selected' : '' }}>Offline Sale</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Payment Status</label>
                <select name="payment_status" class="form-select rounded-3">
                    <option value="">All Payment Statuses</option>
                    <option value="paid" {{ request()->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request()->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request()->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request()->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark rounded-pill fw-semibold py-2 shadow-sm flex-grow-1">
                    <i class="fa-solid fa-filter me-1 text-warning"></i> Apply Filters
                </button>
                @if($activeOrderFilterCount > 0)
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2" title="Reset All Filters">Reset</a>
                @endif
            </div>
            <div class="col-12 mt-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="include_test_orders" value="1" id="includeTestOrdersSwitch" onchange="this.form.submit()" {{ request()->boolean('include_test_orders') ? 'checked' : '' }}>
                    <label class="form-check-label small text-muted" for="includeTestOrdersSwitch">
                        Include Razorpay Test Orders (Phone: 9544832975)
                    </label>
                </div>
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
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark">Start Date</label>
                            <input type="date" name="start_date" class="form-control rounded-3" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark">End Date</label>
                            <input type="date" name="end_date" class="form-control rounded-3" value="{{ request('end_date') }}">
                        </div>
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
                            <option value="offline_sale" {{ request()->payment_method === 'offline_sale' ? 'selected' : '' }}>Offline Sale</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="include_test_orders" value="1" id="includeTestOrdersSwitchMobile" {{ request()->boolean('include_test_orders') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="includeTestOrdersSwitchMobile">
                                Include Razorpay Test Orders (9544832975)
                            </label>
                        </div>
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
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark font-serif fs-6 fs-md-5">
            <i class="fa-solid fa-list-check text-warning me-2"></i> Orders List ({{ $orders->total() }})
        </h5>
        @if(!request()->boolean('include_test_orders'))
            <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.7rem;">
                <i class="fa-solid fa-user-check text-success me-1"></i> Real Sales Only (Excl. 9544832975)
            </span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table orders-table align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
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
                                <span class="badge bg-light text-dark border small fw-bold">
                                    {{ $order->items->sum('quantity') }} Pcs
                                </span>
                            </td>
                            <td>
                                @if($order->payment_method === 'offline_sale')
                                    <span class="badge bg-purple text-white text-uppercase me-1" style="background-color: #6f42c1;">OFFLINE</span>
                                @elseif($order->payment_method === 'online')
                                    <span class="badge bg-info text-dark text-uppercase me-1">ONLINE</span>
                                @else
                                    <span class="badge bg-light text-dark border text-uppercase me-1">{{ $order->payment_method }}</span>
                                @endif
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
                                    <a href="{{ route('admin.order-operations.create', $order->id) }}" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="Order Operation / Return">
                                        <i class="fa-solid fa-rotate-left fs-6"></i>
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-warning text-dark rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px; background-color: var(--qw-gold); border-color: var(--qw-gold);" title="Print Invoice">
                                        <i class="fa-solid fa-print fs-6"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fs-1 text-muted mb-2 d-block"></i>
                                No real sales orders found for the selected date / filters.
                            </td>
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
