@extends('layouts.admin')

@section('title', 'Admin Dashboard - QUARA WALDROP')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 fs-4 fs-sm-3">Dashboard Overview</h3>
        <p class="text-muted small mb-0">Welcome back, {{ auth()->user()->name }}! Here is what's happening today.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
        <a href="{{ route('admin.products.create') }}" class="btn btn-dark rounded-pill btn-sm px-3 py-2 fw-semibold shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Add Product
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-warning rounded-pill btn-sm px-3 py-2 fw-bold text-dark shadow-sm me-1">
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
    <!-- Prominent New Orders Section -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-bell text-warning me-2"></i> Recent / New Orders</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-link btn-sm text-decoration-none">View All Orders &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
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
                                        <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
                                        <div class="small text-muted">{{ $order->customer_phone }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border text-uppercase">{{ $order->payment_method }}</span></td>
                                    <td class="fw-bold">₹{{ number_format($order->grand_total, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'warning') }} text-capitalize">
                                            {{ $order->order_status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-dark btn-sm rounded-pill">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No orders received yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Low Stock Alerts</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($lowStockSizes as $pSize)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $pSize->product->name ?? 'Unknown Product' }}</h6>
                                <span class="badge bg-dark">Size: {{ $pSize->size }}</span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">{{ $pSize->stock }} left</span>
                                <a href="{{ route('admin.products.edit', $pSize->product_id) }}" class="btn btn-link btn-sm p-0 d-block text-decoration-none">Update</a>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted">All sizes have sufficient stock!</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
