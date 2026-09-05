@extends('layouts.admin')

@section('title', 'Admin Dashboard - ' . $siteName)

@section('styles')
<style>
    .admin-dash-stat-card {
        padding: 8px 12px !important;
        border-radius: 10px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }
    .admin-dash-stat-title {
        font-size: 0.62rem !important;
        letter-spacing: 0.2px !important;
        line-height: 1.15 !important;
        display: block;
    }
    .admin-dash-stat-val {
        font-size: 0.95rem !important;
        margin-top: 2px !important;
        margin-bottom: 2px !important;
        line-height: 1.15 !important;
    }
    .admin-dash-stat-sub {
        font-size: 0.62rem !important;
        line-height: 1.15 !important;
        display: block;
    }
    @media (max-width: 576px) {
        .admin-dash-mobile-btn {
            font-size: 0.74rem !important;
            padding: 6px 10px !important;
        }
        .admin-dash-card-title {
            font-size: 0.88rem !important;
        }
        .admin-dash-table th, .admin-dash-table td {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.45rem !important;
        }
        .admin-dash-stat-card {
            padding: 6px 8px !important;
            border-radius: 8px !important;
        }
        .admin-dash-stat-title {
            font-size: 0.58rem !important;
            line-height: 1.1 !important;
        }
        .admin-dash-stat-val {
            font-size: 0.85rem !important;
            margin-top: 1px !important;
            margin-bottom: 1px !important;
        }
        .admin-dash-stat-sub {
            font-size: 0.58rem !important;
            line-height: 1.1 !important;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 fs-4 fs-sm-3 text-dark text-uppercase" style="letter-spacing: 0.5px;">WELCOME AKARSHA MAHSHOOQUE</h3>
        <p class="text-muted small mb-0">Live store performance, sales & inventory dashboard overview</p>
    </div>
    <div class="d-flex w-100 w-sm-auto gap-2 mt-2 mt-sm-0">
        <a href="{{ route('admin.products.create') }}" class="btn btn-dark rounded-pill btn-sm flex-fill flex-sm-grow-0 px-3 py-2 fw-semibold shadow-sm text-center admin-dash-mobile-btn">
            <i class="fa-solid fa-plus me-1"></i> Add Product
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-warning rounded-pill btn-sm flex-fill flex-sm-grow-0 px-3 py-2 fw-bold text-dark shadow-sm text-center admin-dash-mobile-btn">
            <i class="fa-solid fa-receipt me-1"></i> View Orders
        </a>
    </div>
</div>

<!-- Today Metrics (Ultra-Compact with Card Spacing) -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card admin-dash-stat-card bg-white border h-100">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Today Sales</span>
            <h6 class="admin-dash-stat-val fw-bold text-success">₹{{ number_format($todaySales, 2) }}</h6>
            <span class="admin-dash-stat-sub text-muted">Online & Offline</span>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card admin-dash-stat-card bg-white border h-100">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Today Expenses</span>
            <h6 class="admin-dash-stat-val fw-bold text-danger">₹{{ number_format($todayExpenses, 2) }}</h6>
            <span class="admin-dash-stat-sub text-muted">Expenses & Fees</span>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card admin-dash-stat-card bg-white border h-100">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Today Orders</span>
            <h6 class="admin-dash-stat-val fw-bold text-primary">{{ $todayOrdersCount }}</h6>
            <a href="{{ route('admin.orders.index') }}" class="admin-dash-stat-sub text-primary fw-bold text-decoration-none">View Orders &rarr;</a>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card admin-dash-stat-card bg-white border h-100">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Today Sold Products</span>
            <h6 class="admin-dash-stat-val fw-bold text-info">{{ $todaySoldProductsPcs }} Pcs</h6>
            <span class="admin-dash-stat-sub text-muted">Items Sold Today</span>
        </div>
    </div>
</div>

<!-- Overall Financial Performance Section (Total Revenue, Total Expense, Net Profit / Loss) -->
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="stat-card admin-dash-stat-card bg-white border h-100 border-start border-3 border-success">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Total Revenue</span>
            <h6 class="admin-dash-stat-val fw-bold text-success">₹{{ number_format($allTimeTotalRevenue, 2) }}</h6>
            <a href="{{ route('admin.reports.profit-loss') }}" class="admin-dash-stat-sub text-success fw-bold text-decoration-none">Sales + Incomes &rarr;</a>
        </div>
    </div>

    <div class="col-6 col-md-4">
        <div class="stat-card admin-dash-stat-card bg-white border h-100 border-start border-3 border-danger">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Total Expense</span>
            <h6 class="admin-dash-stat-val fw-bold text-danger">₹{{ number_format($allTimeTotalExpenses, 2) }}</h6>
            <a href="{{ route('admin.expenses.index') }}" class="admin-dash-stat-sub text-danger fw-bold text-decoration-none">COGS, Fees & Expenses &rarr;</a>
        </div>
    </div>

    <div class="col-6 col-md-4">
        <div class="stat-card admin-dash-stat-card bg-white border h-100 border-start border-3 {{ $allTimeIsProfit ? 'border-success' : 'border-danger' }}">
            <div class="d-flex align-items-center justify-content-between">
                <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Profit & Loss</span>
                @if($allTimeIsProfit)
                    <span class="badge bg-success text-white fw-bold px-1 py-0" style="font-size: 0.58rem;">PROFIT</span>
                @else
                    <span class="badge bg-danger text-white fw-bold px-1 py-0" style="font-size: 0.58rem;">LOSS</span>
                @endif
            </div>
            <h6 class="admin-dash-stat-val fw-bold {{ $allTimeIsProfit ? 'text-success' : 'text-danger' }}">
                {{ $allTimeIsProfit ? '+' : '-' }}₹{{ number_format(abs($allTimeNetProfitLoss), 2) }}
            </h6>
            <a href="{{ route('admin.reports.profit-loss') }}" class="admin-dash-stat-sub {{ $allTimeIsProfit ? 'text-success' : 'text-danger' }} fw-bold text-decoration-none">
                View P&L Report &rarr;
            </a>
        </div>
    </div>
</div>

<!-- All-Time & Success Sales Performance Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card admin-dash-stat-card bg-white border h-100 border-start border-3 border-success">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Success Orders</span>
            <h6 class="admin-dash-stat-val fw-bold text-success">{{ $successOrdersCount }} Orders</h6>
            <span class="admin-dash-stat-sub text-muted">₹{{ number_format($successOrdersAmount, 2) }}</span>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card admin-dash-stat-card bg-white border h-100 border-start border-3 border-warning">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Total Sold Products</span>
            <h6 class="admin-dash-stat-val fw-bold text-warning-emphasis">{{ number_format($totalSoldProductsPcs) }} Pcs</h6>
            <span class="admin-dash-stat-sub text-muted">Items Delivered / Sold</span>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card admin-dash-stat-card bg-white border h-100">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Total Sales</span>
            <h6 class="admin-dash-stat-val fw-bold text-dark">₹{{ number_format($totalSales, 2) }}</h6>
            <span class="admin-dash-stat-sub text-muted">Gross Realized</span>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card admin-dash-stat-card bg-white border h-100">
            <span class="admin-dash-stat-title text-muted text-uppercase fw-bold">Total Orders</span>
            <h6 class="admin-dash-stat-val fw-bold text-dark">{{ $totalOrders }}</h6>
            <span class="admin-dash-stat-sub text-muted">Pending: {{ $pendingOrders }}</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Prominent New Orders Section with Tabs -->
    <div class="col-12">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <ul class="nav nav-pills card-header-pills fw-bold" id="dashOrdersTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill px-2.5 px-sm-3 py-1 fw-bold" id="real-orders-tab" data-bs-toggle="tab" data-bs-target="#real-orders-content" type="button" role="tab" style="font-size: 0.76rem;">
                                <i class="fa-solid fa-cart-shopping me-1 text-warning"></i> My Orders ({{ $recentOrders->count() }})
                            </button>
                        </li>
                        @if($dummyOrders->count() > 0)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill px-2.5 px-sm-3 py-1 fw-bold text-muted" id="dummy-orders-tab" data-bs-toggle="tab" data-bs-target="#dummy-orders-content" type="button" role="tab" style="font-size: 0.76rem;">
                                    <i class="fa-solid fa-vial me-1"></i> Dummy Orders ({{ $dummyOrders->count() }})
                                </button>
                            </li>
                        @endif
                    </ul>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-link btn-sm text-decoration-none p-0" style="font-size: 0.78rem;">View All Orders &rarr;</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="tab-content" id="dashOrdersTabContent">
                    <!-- Real Orders Tab -->
                    <div class="tab-pane fade show active" id="real-orders-content" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 admin-dash-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date & Time</th>
                                        <th>Customer</th>
                                        <th>Method</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-warning text-decoration-none">
                                                    {{ $order->order_number }}
                                                </a>
                                                @if($order->order_source === 'manual' || $order->payment_method === 'offline_sale')
                                                    <span class="badge bg-dark text-warning border border-warning ms-1" style="font-size: 0.62rem;">MANUAL</span>
                                                @else
                                                    <span class="badge bg-primary text-white ms-1" style="font-size: 0.62rem;">WEBSITE</span>
                                                @endif
                                            </td>
                                            <small class="d-none"></small>
                                            <td class="small text-nowrap">
                                                <div class="fw-bold text-dark" style="font-size: 0.78rem;">{{ $order->created_at->format('M d, Y') }}</div>
                                                <div class="text-muted" style="font-size: 0.72rem;"><i class="fa-solid fa-clock me-1 text-warning"></i>{{ $order->created_at->format('h:i A') }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark admin-dash-stock-name">{{ $order->customer_name }}</div>
                                                <div class="small text-muted" style="font-size: 0.75rem;">{{ $order->customer_phone }}</div>
                                            </td>
                                            <td><span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.72rem;">{{ str_replace('_', ' ', $order->payment_method) }}</span></td>
                                            <td class="fw-bold">₹{{ number_format($order->grand_total, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning') }} text-capitalize" style="font-size: 0.72rem;">
                                                    {{ $order->order_status }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-dark btn-sm rounded-pill py-0 px-2" style="font-size: 0.74rem;">Manage</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No real customer orders received yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Dummy / Test Orders Tab -->
                    @if($dummyOrders->count() > 0)
                        <div class="tab-pane fade" id="dummy-orders-content" role="tabpanel">
                            <div class="p-2.5 bg-light border-bottom text-muted small px-3">
                                <i class="fa-solid fa-info-circle text-info me-1"></i> These orders were created for Razorpay test checkout / admin testing (Phone: 9544832975). They are excluded from live Sales & P&L.
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 admin-dash-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date & Time</th>
                                            <th>Test Customer / Phone</th>
                                            <th>Method</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dummyOrders as $order)
                                            <tr class="table-warning bg-opacity-10">
                                                <td>
                                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-dark text-decoration-none">
                                                        {{ $order->order_number }}
                                                    </a>
                                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">TEST ORDER</span>
                                                </td>
                                                <td class="small text-nowrap">
                                                    <div class="fw-bold text-dark" style="font-size: 0.78rem;">{{ $order->created_at->format('M d, Y') }}</div>
                                                    <div class="text-muted" style="font-size: 0.72rem;"><i class="fa-solid fa-clock me-1 text-warning"></i>{{ $order->created_at->format('h:i A') }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark admin-dash-stock-name">{{ $order->customer_name }}</div>
                                                    <div class="small text-muted" style="font-size: 0.75rem;">{{ $order->customer_phone }}</div>
                                                </td>
                                                <td><span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.72rem;">{{ str_replace('_', ' ', $order->payment_method) }}</span></td>
                                                <td class="fw-bold text-muted">₹{{ number_format($order->grand_total, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-secondary text-capitalize" style="font-size: 0.72rem;">
                                                        {{ $order->order_status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.74rem;">View Test</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Low Stock Alerts hidden from the admin dashboard.
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-danger admin-dash-card-title"><i class="fa-solid fa-triangle-exclamation me-2"></i> Low Stock Alerts</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($lowStockSizes as $pSize)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark admin-dash-stock-name">{{ $pSize->product->name ?? 'Unknown Product' }}</h6>
                                <span class="badge bg-dark" style="font-size: 0.72rem;">Size: {{ $pSize->size }}</span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger rounded-pill px-2.5 py-1.5 admin-dash-stock-badge">{{ $pSize->stock }} left</span>
                                <a href="{{ route('admin.products.edit', $pSize->product_id) }}" class="btn btn-link btn-sm p-0 d-block text-decoration-none text-muted" style="font-size: 0.75rem;">Update</a>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted">All sizes have sufficient stock!</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    --}}
</div>
@endsection
