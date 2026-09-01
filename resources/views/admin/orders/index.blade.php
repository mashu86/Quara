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
    <!-- Today Sales Card -->
    <div class="col-12 col-md-6">
        <div class="card border-0 rounded-4 shadow-sm text-white h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #111111 0%, #2b2b2b 100%);">
            <div class="card-body p-2.5 p-sm-4">
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <span class="text-warning text-uppercase font-mono fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-calendar-day me-1"></i> Today Sale
                    </span>
                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">{{ $todayOrdersCount }} Orders</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="fw-bold mb-0 text-warning fs-4 fs-sm-2">₹{{ number_format($todaySalesAmount, 2) }}</h3>
                        <div class="text-light opacity-90 mt-1" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-box-open text-warning me-1"></i> <strong>{{ $todayProductsCount }}</strong> Products Sold Today
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-20 rounded-3 text-warning flex-shrink-0 ms-2" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-chart-line fs-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly / Selected Period Sales Card -->
    <div class="col-12 col-md-6">
        <div class="card border-0 rounded-4 shadow-sm bg-white border-start border-4 border-warning h-100">
            <div class="card-body p-2.5 p-sm-4">
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <span class="text-muted text-uppercase fw-bold text-truncate me-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-calendar-week text-warning me-1"></i> {{ $periodLabel }}
                    </span>
                    <span class="badge bg-dark text-white rounded-pill px-2 py-0.5 fw-bold flex-shrink-0" style="font-size: 0.68rem;">{{ $periodOrdersCount }} Orders</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="fw-bold mb-0 text-dark fs-4 fs-sm-2">₹{{ number_format($periodSalesAmount, 2) }}</h3>
                        <div class="text-muted mt-1" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-boxes-packing text-warning me-1"></i> <strong>{{ $periodProductsCount }}</strong> Products Sold in Period
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-light border rounded-3 text-dark flex-shrink-0 ms-2" style="width: 38px; height: 38px;">
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
    @media (max-width: 768px) {
        .orders-mobile-card { font-size: 0.78rem; }
        .orders-mobile-card .card-header { padding: 0.5rem 0.75rem !important; }
        .orders-mobile-card .card-body { padding: 0.5rem 0.75rem !important; }
        .orders-mobile-card .card-footer { padding: 0.35rem 0.75rem !important; }
        .orders-mobile-card .btn-circle-mobile { width: 30px !important; height: 30px !important; font-size: 0.7rem !important; }
        .orders-table th, .orders-table td { font-size: 0.7rem !important; padding: 0.4rem 0.3rem !important; }
        .orders-table .btn:not(.rounded-circle) { font-size: 0.65rem !important; padding: 0.2rem 0.4rem !important; }
        .orders-table .badge, .orders-mobile-card .badge { font-size: 0.6rem !important; padding: 0.2em 0.4em !important; }
    }
</style>

<!-- Quick Status Filter Dropdown -->
<div class="card border-0 rounded-4 shadow-sm mb-3 bg-white">
    <div class="card-body p-2 p-sm-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark rounded-circle p-1.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                <i class="fa-solid fa-filter text-warning" style="font-size: 0.75rem;"></i>
            </span>
            <div>
                <label for="quickStatusDropdown" class="fw-bold mb-0 text-dark" style="font-size: 0.78rem;">Filter Orders by Status</label>
                <div class="text-muted" style="font-size: 0.68rem;">Select any status to filter list</div>
            </div>
        </div>
        <div class="w-100 w-md-auto" style="min-width: 240px;">
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

<!-- Orders List Container -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white py-2.5 px-3 px-sm-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="fw-bold mb-0 text-dark font-serif" style="font-size: 0.85rem;">
            <i class="fa-solid fa-list-check text-warning me-1.5"></i> Orders List ({{ $orders->total() }})
        </h5>
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
@endsection
