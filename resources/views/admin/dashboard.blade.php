@extends('layouts.admin')

@section('title', 'Admin Dashboard - ' . $siteName)

@section('styles')
<style>
    @media (max-width: 576px) {
        .admin-dash-mobile-btn {
            font-size: 0.74rem !important;
            padding: 6px 10px !important;
        }
        .admin-dash-card-title {
            font-size: 0.95rem !important;
        }
        .admin-dash-table th, .admin-dash-table td {
            font-size: 0.8rem !important;
            padding: 0.45rem 0.5rem !important;
        }
        .admin-dash-stock-name {
            font-size: 0.82rem !important;
        }
        .admin-dash-stock-badge {
            font-size: 0.74rem !important;
            padding: 4px 8px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 fs-4 fs-sm-3">Dashboard Overview</h3>
        <p class="text-muted small mb-0">Welcome back, {{ auth()->user()->name }}! Here is what's happening today.</p>
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

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small text-uppercase font-bold">Total Sales</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">₹{{ number_format($totalSales, 2) }}</h3>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small text-uppercase font-bold">Total Orders</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalOrders }}</h3>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small text-uppercase font-bold">Pending Orders</span>
                <h3 class="fw-bold text-warning mb-0 mt-1">{{ $pendingOrders }}</h3>
            </div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small text-uppercase font-bold">Active Products</span>
                <h3 class="fw-bold text-info mb-0 mt-1">{{ $totalProducts }}</h3>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="fa-solid fa-shirt"></i>
            </div>
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
                            <button class="nav-link active rounded-pill px-3 py-1.5 small" id="real-orders-tab" data-bs-toggle="tab" data-bs-target="#real-orders-content" type="button" role="tab">
                                <i class="fa-solid fa-cart-shopping me-1 text-warning"></i> Real Sales Orders ({{ $recentOrders->count() }})
                            </button>
                        </li>
                        @if($dummyOrders->count() > 0)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill px-3 py-1.5 small text-muted" id="dummy-orders-tab" data-bs-toggle="tab" data-bs-target="#dummy-orders-content" type="button" role="tab">
                                    <i class="fa-solid fa-vial me-1"></i> Dummy / Test Purchases ({{ $dummyOrders->count() }})
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
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark admin-dash-stock-name">{{ $order->customer_name }}</div>
                                                <div class="small text-muted" style="font-size: 0.75rem;">{{ $order->customer_phone }}</div>
                                            </td>
                                            <td><span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.72rem;">{{ $order->payment_method }}</span></td>
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
                                            <td colspan="6" class="text-center py-4 text-muted">No real customer orders received yet.</td>
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
                                                <td>
                                                    <div class="fw-bold text-dark admin-dash-stock-name">{{ $order->customer_name }}</div>
                                                    <div class="small text-muted" style="font-size: 0.75rem;">{{ $order->customer_phone }}</div>
                                                </td>
                                                <td><span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.72rem;">{{ $order->payment_method }}</span></td>
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
