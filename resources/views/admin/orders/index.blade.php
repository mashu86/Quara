@extends('layouts.admin')

@section('title', 'My Sales - ' . $siteName . ' Admin')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
    <div>
        <h3 class="fw-bold mb-1 fs-5 fs-sm-3">
            <i class="fa-solid fa-receipt text-warning me-2"></i> My Sales
        </h3>
        <p class="text-muted small mb-0 d-none d-sm-block">Live sales reporting, date-wise analytics & order fulfillment control</p>
    </div>
</div>

@php
    $activeOrderFilterCount = (request()->filled('search') ? 1 : 0)
        + (request()->filled('status') ? 1 : 0)
        + (request()->filled('payment_method') ? 1 : 0)
        + (request()->filled('sale_channel') ? 1 : 0)
        + (request()->filled('start_date') ? 1 : 0)
        + (request()->filled('end_date') ? 1 : 0);
@endphp

<!-- SALES ANALYTICS KPI SUMMARY CARDS -->
<div class="row g-2 g-sm-3 mb-3 mb-md-4">
    <!-- Today Gross Sales Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm text-white h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #111111 0%, #2b2b2b 100%);">
            <div class="card-body p-2.5 p-sm-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-warning text-uppercase font-mono fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-calendar-day me-1"></i> Today Sales
                    </span>
                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">{{ $todayOrdersCount }} Orders</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-0 text-warning fs-5">₹{{ number_format($todayGrossAmount, 2) }}</h4>
                        <div class="text-light opacity-90 mt-1" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-box-open text-warning me-1"></i> <strong>{{ $todayProductsCount }}</strong> Sold
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-20 rounded-3 text-warning flex-shrink-0 ms-2" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-chart-line fs-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today Refund Amount Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white border-start border-4 border-danger h-100">
            <div class="card-body p-2.5 p-sm-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-danger text-uppercase font-mono fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-rotate-left me-1"></i> Today Refund
                    </span>
                    <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">Actual Paid</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-0 text-danger fs-5">₹{{ number_format($todayRefunds, 2) }}</h4>
                        <div class="text-muted mt-1" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-arrow-down-left text-danger me-1"></i> Based on Refund Date
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-3 text-danger flex-shrink-0 ms-2" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-hand-holding-dollar fs-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today Net Sales Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white border-start border-4 border-success h-100">
            <div class="card-body p-2.5 p-sm-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-success text-uppercase font-mono fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-scale-balanced me-1"></i> Today Net Sales
                    </span>
                    <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">Sales - Refund</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-0 text-success fs-5">₹{{ number_format($todaySalesAmount, 2) }}</h4>
                        <div class="text-muted mt-1" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-shield-halved text-success me-1"></i> Net Business Revenue
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-3 text-success flex-shrink-0 ms-2" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-sack-dollar fs-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly / Selected Period Sales Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white border-start border-4 border-warning h-100">
            <div class="card-body p-2.5 p-sm-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted text-uppercase fw-bold text-truncate me-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-calendar-week text-warning me-1"></i> {{ $periodLabel }}
                    </span>
                    <span class="badge bg-dark text-white rounded-pill px-2 py-0.5 fw-bold flex-shrink-0" style="font-size: 0.65rem;">{{ $periodOrdersCount }} Orders</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark fs-5">₹{{ number_format($periodSalesAmount, 2) }}</h4>
                        <div class="text-muted mt-1" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-boxes-packing text-warning me-1"></i> <strong>{{ $periodProductsCount }}</strong> Sold in Period
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-light border rounded-3 text-dark flex-shrink-0 ms-2" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-bag-shopping fs-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile / Tablet Filter Trigger Bar (d-lg-none) -->
<div class="d-lg-none mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 py-1.5 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2" style="font-size: 0.8rem;" data-bs-toggle="modal" data-bs-target="#orderFilterModal">
            <i class="fa-solid fa-sliders text-warning"></i>
            <span class="fw-semibold">Filter Orders</span>
            @if(request()->filled('status'))
                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-tag me-1"></i>{{ ucfirst(request()->status) }}
                </span>
            @endif
            @if($activeOrderFilterCount > 0)
                <span class="badge bg-warning text-dark rounded-pill py-0.5 px-1.5" style="font-size: 0.65rem;">{{ $activeOrderFilterCount }}</span>
            @endif
        </button>
        @if($activeOrderFilterCount > 0)
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1.5 d-flex align-items-center justify-content-center" title="Clear Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </div>
</div>

<!-- Desktop Search & Unified Filters (d-none d-lg-block) -->
<div class="card border-0 rounded-4 shadow-sm mb-4 d-none d-lg-block">
    <div class="card-body p-3 p-sm-4">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2.5 align-items-end">
            <div class="col-12 col-xl-3">
                <label class="form-label small fw-bold text-muted mb-1">
                    <i class="fa-solid fa-magnifying-glass text-warning me-1"></i> Search
                </label>
                <input type="text" name="search" class="form-control rounded-3" placeholder="Order #, Name, Phone..." value="{{ request()->search }}">
            </div>
            <div class="col-6 col-xl-2">
                <label class="form-label small fw-bold text-muted mb-1">
                    <i class="fa-regular fa-calendar text-warning me-1"></i> Start Date
                </label>
                <input type="date" name="start_date" class="form-control rounded-3 py-1.5" value="{{ request('start_date') }}">
            </div>
            <div class="col-6 col-xl-2">
                <label class="form-label small fw-bold text-muted mb-1">
                    <i class="fa-regular fa-calendar-check text-warning me-1"></i> End Date
                </label>
                <input type="date" name="end_date" class="form-control rounded-3 py-1.5" value="{{ request('end_date') }}">
            </div>
            <div class="col-12 col-sm-4 col-xl-2">
                <label class="form-label small fw-bold text-muted mb-1">
                    <i class="fa-solid fa-tags text-warning me-1"></i> Order Status
                </label>
                <select name="status" class="form-select rounded-3">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request()->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request()->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ request()->status === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="packed" {{ request()->status === 'packed' ? 'selected' : '' }}>Packed</option>
                    <option value="shipped" {{ request()->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request()->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request()->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="returned" {{ request()->status === 'returned' || request()->status === 'has_return' ? 'selected' : '' }}>Returned / Operations ({{ $statusCounts['returned'] ?? 0 }})</option>
                </select>
            </div>
            <div class="col-12 col-sm-4 col-xl-2">
                <label class="form-label small fw-bold text-muted mb-1">
                    <i class="fa-solid fa-cart-flatbed text-warning me-1"></i> Sale Source
                </label>
                <select name="sale_channel" class="form-select rounded-3">
                    <option value="">All Channels</option>
                    <option value="website" {{ request()->sale_channel === 'website' ? 'selected' : '' }}>🌐 Website Sales</option>
                    <option value="manual" {{ request()->sale_channel === 'manual' ? 'selected' : '' }}>📝 Manual / Offline Sales</option>
                </select>
            </div>
            <div class="col-12 col-sm-4 col-xl-2">
                <label class="form-label small fw-bold text-muted mb-1">
                    <i class="fa-solid fa-wallet text-warning me-1"></i> Payment Method
                </label>
                <select name="payment_method" class="form-select rounded-3">
                    <option value="">All Methods</option>
                    <option value="cod" {{ request()->payment_method === 'cod' ? 'selected' : '' }}>COD (Cash on Delivery)</option>
                    <option value="online" {{ request()->payment_method === 'online' ? 'selected' : '' }}>Razorpay Online</option>
                    <option value="offline_sale" {{ request()->payment_method === 'offline_sale' ? 'selected' : '' }}>Offline Sale</option>
                </select>
            </div>
            <div class="col-12 col-sm-4 col-xl-2">
                <label class="form-label small fw-bold text-muted mb-1">
                    <i class="fa-solid fa-credit-card text-warning me-1"></i> Payment Status
                </label>
                <select name="payment_status" class="form-select rounded-3">
                    <option value="">All Payment Statuses</option>
                    <option value="paid" {{ request()->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request()->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request()->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request()->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-12 col-xl-3 d-flex gap-2 ms-auto mt-2 mt-xl-0">
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
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-2.5">
                <h5 class="modal-title font-serif fw-bold fs-6" id="orderFilterModalLabel">
                    <i class="fa-solid fa-sliders text-warning me-2"></i> Filter Orders
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.orders.index') }}" method="GET">
                <div class="modal-body p-3">
                    <div class="mb-2.5">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            <i class="fa-solid fa-magnifying-glass text-warning me-1"></i> Search Order # / Customer / Phone
                        </label>
                        <input type="text" name="search" class="form-control form-control-sm rounded-3" placeholder="Search Order #, Name, Phone..." value="{{ request()->search }}">
                    </div>
                    <div class="row g-2 mb-2.5">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                <i class="fa-regular fa-calendar text-warning me-1"></i> Start Date
                            </label>
                            <input type="date" name="start_date" class="form-control form-control-sm rounded-3" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                <i class="fa-regular fa-calendar-check text-warning me-1"></i> End Date
                            </label>
                            <input type="date" name="end_date" class="form-control form-control-sm rounded-3" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="mb-2.5">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            <i class="fa-solid fa-tags text-warning me-1"></i> Order Status
                        </label>
                        <select name="status" class="form-select form-select-sm rounded-3">
                            <option value="">All Order Statuses</option>
                            <option value="pending" {{ request()->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request()->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ request()->status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="packed" {{ request()->status === 'packed' ? 'selected' : '' }}>Packed</option>
                            <option value="shipped" {{ request()->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ request()->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request()->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="returned" {{ request()->status === 'returned' || request()->status === 'has_return' ? 'selected' : '' }}>Returned / Operations ({{ $statusCounts['returned'] ?? 0 }})</option>
                        </select>
                    </div>
                    <div class="mb-2.5">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            <i class="fa-solid fa-cart-flatbed text-warning me-1"></i> Sale Source
                        </label>
                        <select name="sale_channel" class="form-select form-select-sm rounded-3">
                            <option value="">All Sale Channels</option>
                            <option value="website" {{ request()->sale_channel === 'website' ? 'selected' : '' }}>🌐 Website Sales</option>
                            <option value="manual" {{ request()->sale_channel === 'manual' ? 'selected' : '' }}>📝 Manual / Offline Sales</option>
                        </select>
                    </div>
                    <div class="mb-2.5">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            <i class="fa-solid fa-wallet text-warning me-1"></i> Payment Method
                        </label>
                        <select name="payment_method" class="form-select form-select-sm rounded-3">
                            <option value="">All Payment Methods</option>
                            <option value="cod" {{ request()->payment_method === 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                            <option value="online" {{ request()->payment_method === 'online' ? 'selected' : '' }}>Razorpay Online</option>
                            <option value="offline_sale" {{ request()->payment_method === 'offline_sale' ? 'selected' : '' }}>Offline Sale</option>
                        </select>
                    </div>
                    <div class="mb-2.5">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            <i class="fa-solid fa-credit-card text-warning me-1"></i> Payment Status
                        </label>
                        <select name="payment_status" class="form-select form-select-sm rounded-3">
                            <option value="">All Payment Statuses</option>
                            <option value="paid" {{ request()->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="pending" {{ request()->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request()->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ request()->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="include_test_orders" value="1" id="includeTestOrdersSwitchMobile" {{ request()->boolean('include_test_orders') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="includeTestOrdersSwitchMobile" style="font-size: 0.72rem;">
                                Include Razorpay Test Orders (9544832975)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 py-2.5">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Reset</a>
                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-check me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Sticky Order # & Customer columns on horizontal x-axis scroll */
    .orders-table th:nth-child(1),
    .orders-table td:nth-child(1) {
        position: sticky;
        left: 0;
        z-index: 4;
        background-color: #ffffff;
        min-width: 105px;
    }
    .orders-table thead th:nth-child(1) {
        z-index: 6;
        background-color: #212529 !important;
    }

    .orders-table th:nth-child(2),
    .orders-table td:nth-child(2) {
        position: sticky;
        left: 105px;
        z-index: 4;
        background-color: #ffffff;
        border-right: 2px solid #dee2e6 !important;
        box-shadow: 4px 0 6px -2px rgba(0,0,0,0.06);
        min-width: 140px;
    }
    .orders-table thead th:nth-child(2) {
        z-index: 6;
        background-color: #212529 !important;
        border-right: 2px solid #495057 !important;
    }

    @media (max-width: 768px) {
        .orders-mobile-card { font-size: 0.78rem; }
        .orders-mobile-card .card-header { padding: 0.5rem 0.75rem !important; }
        .orders-mobile-card .card-body { padding: 0.5rem 0.75rem !important; }
        .orders-mobile-card .card-footer { padding: 0.35rem 0.75rem !important; }
        .orders-mobile-card .btn-circle-mobile { width: 28px !important; height: 28px !important; font-size: 0.68rem !important; }
        .orders-table th, .orders-table td { font-size: 0.7rem !important; padding: 0.4rem 0.3rem !important; }
        .orders-table .btn:not(.rounded-circle) { font-size: 0.65rem !important; padding: 0.2rem 0.4rem !important; }
        .orders-table .badge, .orders-mobile-card .badge { font-size: 0.6rem !important; padding: 0.2em 0.4em !important; }
    }
</style>

<!-- Quick Status Filter Dropdown -->
<div class="card border-0 rounded-4 shadow-sm mb-3 bg-white">
    <div class="card-body p-2.5 p-sm-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark rounded-circle p-1.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-filter text-warning" style="font-size: 0.75rem;"></i>
                    </span>
                    <div>
                        <label for="quickStatusDropdown" class="fw-bold mb-0 text-dark" style="font-size: 0.78rem;">Filter Orders by Status</label>
                        <div class="text-muted" style="font-size: 0.68rem;">Select any status to filter list</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                @php
                    $currStatus = request('status') ?: request('order_status');
                    $currPaymentStatus = request('payment_status');
                    
                    $selectedOptionVal = '';
                    if ($currStatus) {
                        $selectedOptionVal = $currStatus;
                    } elseif ($currPaymentStatus === 'pending') {
                        $selectedOptionVal = 'pay_pending';
                    } elseif ($currPaymentStatus === 'paid') {
                        $selectedOptionVal = 'paid';
                    }
                @endphp
                <form action="{{ route('admin.orders.index') }}" method="GET" id="quickStatusForm" class="m-0">
                    @foreach(request()->except(['status', 'order_status', 'payment_status', 'page']) as $k => $v)
                        @if(is_array($v))
                            @foreach($v as $arrV)
                                <input type="hidden" name="{{ $k }}[]" value="{{ $arrV }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endif
                    @endforeach
                    <input type="hidden" name="status" id="quickFormStatusInput" value="{{ in_array($selectedOptionVal, ['pay_pending', 'paid']) ? '' : $selectedOptionVal }}">
                    <input type="hidden" name="payment_status" id="quickFormPaymentStatusInput" value="{{ $selectedOptionVal === 'pay_pending' ? 'pending' : ($selectedOptionVal === 'paid' ? 'paid' : '') }}">

                    <select class="form-select form-select-sm rounded-3 fw-bold border-warning bg-light py-1.5 px-2.5 w-100" style="font-size: 0.78rem;" id="quickStatusDropdown" onchange="handleQuickStatusChange(this.value)">
                        <option value="" {{ $selectedOptionVal === '' ? 'selected' : '' }}>
                            📦 All Orders ({{ $statusCounts['all'] ?? 0 }})
                        </option>
                        <option value="pending" {{ $selectedOptionVal === 'pending' ? 'selected' : '' }}>
                            ⏳ Pending ({{ $statusCounts['pending'] ?? 0 }})
                        </option>
                        <option value="confirmed" {{ $selectedOptionVal === 'confirmed' ? 'selected' : '' }}>
                            ✅ Confirmed ({{ $statusCounts['confirmed'] ?? 0 }})
                        </option>
                        <option value="processing" {{ $selectedOptionVal === 'processing' ? 'selected' : '' }}>
                            ⚙️ Processing ({{ $statusCounts['processing'] ?? 0 }})
                        </option>
                        <option value="packed" {{ $selectedOptionVal === 'packed' ? 'selected' : '' }}>
                            📦 Packed ({{ $statusCounts['packed'] ?? 0 }})
                        </option>
                        <option value="shipped" {{ $selectedOptionVal === 'shipped' ? 'selected' : '' }}>
                            🚚 Shipped ({{ $statusCounts['shipped'] ?? 0 }})
                        </option>
                        <option value="delivered" {{ $selectedOptionVal === 'delivered' ? 'selected' : '' }}>
                            🏠 Delivered ({{ $statusCounts['delivered'] ?? 0 }})
                        </option>
                        <option value="cancelled" {{ $selectedOptionVal === 'cancelled' ? 'selected' : '' }}>
                            🚫 Cancelled ({{ $statusCounts['cancelled'] ?? 0 }})
                        </option>
                        <option value="returned" {{ $selectedOptionVal === 'returned' || $selectedOptionVal === 'has_return' ? 'selected' : '' }}>
                            🔄 Returned / Returns ({{ $statusCounts['returned'] ?? 0 }})
                        </option>
                        <option value="pay_pending" {{ $selectedOptionVal === 'pay_pending' ? 'selected' : '' }}>
                            ⌛ Pay Pending ({{ $statusCounts['payment_pending'] ?? 0 }})
                        </option>
                        <option value="paid" {{ $selectedOptionVal === 'paid' ? 'selected' : '' }}>
                            💳 Payment Paid ({{ $statusCounts['paid'] ?? 0 }})
                        </option>
                    </select>
                </form>
                <script>
                    function handleQuickStatusChange(val) {
                        const form = document.getElementById('quickStatusForm');
                        const statusInput = document.getElementById('quickFormStatusInput');
                        const paymentInput = document.getElementById('quickFormPaymentStatusInput');
                        
                        if (val === 'pay_pending') {
                            statusInput.value = '';
                            paymentInput.value = 'pending';
                        } else if (val === 'paid') {
                            statusInput.value = '';
                            paymentInput.value = 'paid';
                        } else {
                            statusInput.value = val;
                            paymentInput.value = '';
                        }
                        form.submit();
                    }
                </script>
            </div>
        </div>
    </div>
</div>

<!-- Orders List Container -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white py-2.5 px-3 px-sm-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold mb-0 text-dark font-serif" style="font-size: 0.85rem;">
                <i class="fa-solid fa-list-check text-warning me-1.5"></i> Orders List ({{ $orders->total() }})
            </h5>
            <button type="button" onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px;" title="Print Courier Address Label (A5)">
                <i class="fa-solid fa-print text-warning" style="font-size: 0.75rem;"></i>
            </button>
        </div>
        @if(!request()->boolean('include_test_orders'))
            <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.65rem;">
                <i class="fa-solid fa-user-check text-success me-1"></i> Real Sales Only
            </span>
        @endif
    </div>

    <div class="card-body p-0">
        <!-- MOBILE VIEW: Compact Cards Layout (d-block d-md-none) -->
        <div class="d-block d-md-none p-2 bg-light" id="orders-mobile-container">
            @include('admin.orders.partials.mobile_cards')
        </div>

        <!-- DESKTOP VIEW: Scrollable Table Layout (d-none d-md-block) -->
        <div class="d-none d-md-block">
            <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;" id="orders-table-scroll-container">
                <table class="table orders-table align-middle mb-0 position-relative">
                    <thead class="table-dark sticky-top" style="z-index: 5;">
                        <tr>
                            <th class="ps-3">Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Address</th>
                            <th>Order Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orders-desktop-tbody">
                        @include('admin.orders.partials.desktop_rows')
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Infinite Scroll Footer Indicator -->
    <div class="card-footer bg-white py-3 text-center border-top">
        <div id="infinite-scroll-loading" class="d-none text-muted small fw-semibold">
            <span class="spinner-border spinner-border-sm text-warning me-2" role="status" aria-hidden="true"></span>
            Loading more orders...
        </div>
        <div id="infinite-scroll-end" class="{{ $orders->hasMorePages() ? 'd-none' : '' }} text-muted small">
            <i class="fa-solid fa-circle-check text-success me-1"></i> All {{ $orders->total() }} orders loaded
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let nextPageUrl = @json($orders->nextPageUrl());
    let hasMore = @json($orders->hasMorePages());
    let isLoading = false;

    const desktopContainer = document.getElementById('orders-table-scroll-container');
    const desktopTbody = document.getElementById('orders-desktop-tbody');
    const mobileContainer = document.getElementById('orders-mobile-container');
    const loadingSpinner = document.getElementById('infinite-scroll-loading');
    const endNotice = document.getElementById('infinite-scroll-end');

    function checkAndLoadMore() {
        if (isLoading || !hasMore || !nextPageUrl) return;

        let shouldLoad = false;

        // Check Desktop Table Scroll Position
        if (desktopContainer && desktopContainer.offsetParent !== null) {
            const scrollBottom = desktopContainer.scrollHeight - desktopContainer.scrollTop - desktopContainer.clientHeight;
            if (scrollBottom < 150) {
                shouldLoad = true;
            }
        }

        // Check Window / Mobile Scroll Position
        const windowScrollBottom = document.documentElement.scrollHeight - window.innerHeight - window.scrollY;
        if (windowScrollBottom < 300) {
            shouldLoad = true;
        }

        if (shouldLoad) {
            fetchNextPage();
        }
    }

    function fetchNextPage() {
        isLoading = true;
        if (loadingSpinner) loadingSpinner.classList.remove('d-none');
        if (endNotice) endNotice.classList.add('d-none');

        fetch(nextPageUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.desktop_html && desktopTbody) {
                desktopTbody.insertAdjacentHTML('beforeend', data.desktop_html);
            }
            if (data.mobile_html && mobileContainer) {
                mobileContainer.insertAdjacentHTML('beforeend', data.mobile_html);
            }

            nextPageUrl = data.next_page_url;
            hasMore = data.has_more;
            isLoading = false;
            if (loadingSpinner) loadingSpinner.classList.add('d-none');

            if (!hasMore && endNotice) {
                endNotice.classList.remove('d-none');
            }
        })
        .catch(err => {
            console.error('Error fetching more orders:', err);
            isLoading = false;
            if (loadingSpinner) loadingSpinner.classList.add('d-none');
        });
    }

    if (desktopContainer) {
        desktopContainer.addEventListener('scroll', checkAndLoadMore);
    }
    window.addEventListener('scroll', checkAndLoadMore);
});
</script>
</div>

@include('admin.orders.partials.whatsapp_modal')

<!-- Quick Edit Payment Modal for Orders Index -->
<div class="modal fade" id="indexEditPaymentModal" tabindex="-1" aria-labelledby="indexEditPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-2.5">
                <h5 class="modal-title font-serif fw-bold fs-6" id="indexEditPaymentModalLabel">
                    <i class="fa-solid fa-credit-card text-warning me-2"></i> Edit Payment (<span id="quickEditOrderNumber"></span>)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickEditPaymentForm" method="POST" action="">
                @csrf
                <div class="modal-body p-3 p-sm-4">
                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-3" style="font-size: 0.76rem;">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Fix payment status (e.g. Razorpay payment received, but website showing Pending).
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Payment Status</label>
                        <select name="payment_status" id="quick_payment_status" class="form-select rounded-3">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Payment Method</label>
                        <select name="payment_method" id="quick_payment_method" class="form-select rounded-3">
                            <option value="online">Razorpay Online</option>
                            <option value="cod">Cash on Delivery (COD)</option>
                            <option value="offline_sale">Offline Sale</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Razorpay Payment ID (Optional)</label>
                        <input type="text" name="razorpay_payment_id" id="quick_razorpay_payment_id" class="form-control rounded-3" placeholder="e.g. pay_Pxxxxxxxxx">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Razorpay Order ID (Optional)</label>
                        <input type="text" name="razorpay_order_id" id="quick_razorpay_order_id" class="form-control rounded-3" placeholder="e.g. order_Pxxxxxxxxx">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Transaction / Ref ID (Optional)</label>
                        <input type="text" name="transaction_id" id="quick_transaction_id" class="form-control rounded-3" placeholder="e.g. TXN123456">
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="auto_confirm_order" value="1" id="quickAutoConfirmOrderSwitch" checked>
                        <label class="form-check-label small fw-semibold text-dark" for="quickAutoConfirmOrderSwitch">
                            Auto-update Order Status to "Confirmed" (if currently Pending)
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 border-0 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" style="background-color: var(--qw-gold); border-color: var(--qw-gold);">
                        <i class="fa-solid fa-check me-1"></i> Save Payment Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openIndexEditPaymentModal(order) {
    document.getElementById('quickEditOrderNumber').innerText = '#' + order.order_number;
    var form = document.getElementById('quickEditPaymentForm');
    form.action = '/admin/orders/' + order.id + '/payment-details';
    
    document.getElementById('quick_payment_status').value = order.payment_status || 'pending';
    document.getElementById('quick_payment_method').value = order.payment_method || 'online';
    document.getElementById('quick_razorpay_payment_id').value = order.razorpay_payment_id || (order.payment ? order.payment.razorpay_payment_id || '' : '');
    document.getElementById('quick_razorpay_order_id').value = order.razorpay_order_id || (order.payment ? order.payment.razorpay_order_id || '' : '');
    document.getElementById('quick_transaction_id').value = order.payment ? (order.payment.transaction_id || '') : '';
    
    var modal = new bootstrap.Modal(document.getElementById('indexEditPaymentModal'));
    modal.show();
}
</script>

<!-- COURIER ADDRESS PREVIEW MODAL -->
<div class="modal fade" id="courierAddressPreviewModal" tabindex="-1" aria-labelledby="courierAddressPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-2.5">
                <h5 class="modal-title font-serif fw-bold fs-6" id="courierAddressPreviewModalLabel">
                    <i class="fa-solid fa-truck-fast text-warning me-2"></i> Courier Address Label Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white" id="courierAddressPreviewModalBody">
                <!-- Dynamically populated label template -->
            </div>
            <div class="modal-footer bg-light rounded-bottom-4 border-0 px-3 px-sm-4 py-2 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 btn-sm" style="font-size: 0.78rem;" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning rounded-pill px-3 px-sm-4 py-1.5 fw-bold text-dark shadow-sm btn-sm" style="background-color: var(--qw-gold); border-color: var(--qw-gold); font-size: 0.78rem;" id="modalPrintCourierBtn">
                    <i class="fa-solid fa-print me-1"></i>
                    <span class="d-none d-sm-inline">Print / Download Label</span>
                    <span class="d-inline d-sm-none">Print</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatOrderToAddressHtml(order) {
    let lines = [];
    if (order.customer_name) {
        lines.push('<strong style="font-size: 1.05em; color: #111;">' + escapeHtml(order.customer_name) + '</strong>');
    }
    if (order.house_building) lines.push(escapeHtml(order.house_building));
    
    let streetAreaCity = [order.street, order.area, order.city].filter(Boolean).map(s => s.trim()).join(', ');
    if (streetAreaCity) lines.push(escapeHtml(streetAreaCity));
    
    let distState = [order.district, order.state].filter(Boolean).map(s => s.trim()).join(', ');
    let pin = (order.pin_code || order.pincode || '').toString().trim();
    
    let locPinLine = distState;
    if (pin) {
        locPinLine = locPinLine ? (locPinLine + ' - ' + pin) : ('PIN: ' + pin);
    }
    if (locPinLine) lines.push(escapeHtml(locPinLine));

    if (order.customer_phone) {
        lines.push('<span style="font-weight: 700;">Ph: ' + escapeHtml(order.customer_phone) + '</span>');
    }

    if (lines.length === 0) {
        return '<em>No shipping address provided for this order.</em>';
    }

    return lines.join('<br>');
}

function previewCourierAddress(order) {
    const body = document.getElementById('courierAddressPreviewModalBody');
    const toAddress = formatOrderToAddressHtml(order);

    body.innerHTML = `
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #111; font-size: 15.5pt; line-height: 1.8; border: 1px dashed #ccc; padding: 40px 35px 35px 35px; border-radius: 8px; background: #fff; letter-spacing: 0.2px;">
            <div style="margin-bottom: 30px;">
                <div style="font-size: 18pt; font-weight: 700; color: #111; margin-bottom: 8px;">To ,</div>
                <div style="margin-left: 1cm; min-height: 55mm;">
                    ${toAddress}
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end;">
                <div style="width: 88mm;">
                    <div style="font-size: 18pt; font-weight: 700; color: #111; margin-bottom: 8px;">From ,</div>
                    <div style="margin-left: 1cm; font-weight: 500;">
                        <strong style="font-size: 1.05em; color: #111;">Akarsha Bakker</strong><br>
                        TK House, Vilakkannoor<br>
                        Naduvil P.O. - 670582<br>
                        <span style="font-weight: 700;">Ph: 8078037591</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('modalPrintCourierBtn').onclick = function() {
        printOrderCourierLabel(order);
    };

    const modal = new bootstrap.Modal(document.getElementById('courierAddressPreviewModal'));
    modal.show();
}

function printOrderCourierLabel(order) {
    const toAddressHtml = formatOrderToAddressHtml(order);
    const toContainer = document.querySelector('#courier-parcel-print-area .parcel-to-blank');
    if (toContainer) {
        toContainer.innerHTML = `<div class="parcel-from-details" style="font-size: 15.5pt; font-weight: 500; line-height: 1.8;">${toAddressHtml}</div>`;
    }
    window.print();
}
</script>

<!-- COURIER PARCEL ADDRESS PRINT LAYOUT (EXACT PHOTO TEMPLATE STYLE) -->
<div id="courier-parcel-print-area" class="d-none d-print-block">
    <div class="courier-parcel-sheet">
        <!-- TO SECTION -->
        <div class="parcel-section">
            <div class="parcel-label">To ,</div>
            <div class="parcel-indent-box parcel-to-blank">
                <!-- Open blank space for writing customer shipping address by hand -->
            </div>
        </div>

        <!-- FROM SECTION -->
        <div class="parcel-section parcel-from-container">
            <div class="parcel-from-wrapper">
                <div class="parcel-label">From ,</div>
                <div class="parcel-indent-box parcel-from-details">
                    <strong style="font-size: 1.05em; color: #111;">Akarsha Bakker</strong><br>
                    TK House, Vilakkannoor<br>
                    Naduvil P.O. - 670582<br>
                    <span style="font-weight: 700;">Ph: 8078037591</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        @page {
            size: auto;
            margin: 0;
        }

        html, body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
            max-height: 100% !important;
            overflow: hidden !important;
        }

        body * {
            visibility: hidden !important;
        }

        #courier-parcel-print-area, #courier-parcel-print-area * {
            visibility: visible !important;
        }

        #courier-parcel-print-area {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            display: block !important;
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            z-index: 99999 !important;
        }

        .courier-parcel-sheet {
            width: 135mm;
            max-width: 100%;
            box-sizing: border-box;
            border: none !important;
            padding-top: 1.2cm !important;
            padding-left: 1.2cm !important;
            padding-right: 1.2cm !important;
            padding-bottom: 0.8cm !important;
            background: #ffffff !important;
            color: #111111 !important;
            font-family: Arial, "Segoe UI", Helvetica, sans-serif;
        }

        .parcel-section {
            width: 100%;
            margin-bottom: 6mm;
        }

        .parcel-from-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 4mm;
        }

        .parcel-from-wrapper {
            width: 88mm;
        }

        .parcel-label {
            font-size: 18pt;
            font-weight: 700;
            color: #111111;
            margin-bottom: 3.5mm;
        }

        .parcel-indent-box {
            margin-left: 1cm;
            padding-left: 0.2cm;
        }

        .parcel-to-blank {
            min-height: 55mm;
            height: auto;
            background-color: transparent;
        }

        .parcel-from-details {
            font-size: 15.5pt;
            font-weight: 600;
            line-height: 1.8;
            color: #111111;
        }
    }
</style>
@endsection
