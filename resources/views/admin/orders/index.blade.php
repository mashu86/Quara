@extends('layouts.admin')

@section('title', 'Order Management - QUARA WALDROP Admin')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 fs-4 fs-sm-3">Order Management</h3>
        <p class="text-muted small mb-0">Track, update, filter and fulfill customer orders</p>
    </div>
</div>

<!-- Search & Filters -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
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

<!-- Table -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
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
                                <div class="d-flex flex-nowrap justify-content-end align-items-center gap-1">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-2 px-sm-3 py-1 text-nowrap small fw-semibold">
                                        <i class="fa-solid fa-eye me-1"></i> View
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-warning text-dark rounded-pill px-2 px-sm-3 py-1 text-nowrap small fw-bold">
                                        <i class="fa-solid fa-print me-1"></i> Invoice
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
@endsection
